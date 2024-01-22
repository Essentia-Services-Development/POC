mailster = (function (mailster, $, window, document) {
	'use strict';

	var filterbar = $('.wp-filter'),
		addonbrowser = $('.theme-browser'),
		filterlinks = filterbar.find('.filter-links a'),
		searchform = filterbar.find('.search-form'),
		searchfield = filterbar.find('.wp-filter-search'),
		typeselector = filterbar.find('#typeselector'),
		searchdelay,
		currentfilter,
		lastsearchquery = '',
		lastsearchtype = '',
		currentpage = 1,
		total = 0,
		currentdisplayed = 0,
		searchquery = searchfield.val(),
		searchtype = typeselector.val(),
		addons = [],
		busy = false,
		current_slug = false;

	filterlinks.on('click', function (event) {
		event.preventDefault();
		if (!busy) setFilter($(this).data('sort'));
		return;
	});

	searchform.on('submit', function (event) {
		event.preventDefault();
		searchdelay && clearTimeout(searchdelay);
		search();
	});

	searchfield.on('keyup change', function (event) {
		if (13 == event.keyCode) {
			return;
		}
		searchdelay && clearTimeout(searchdelay);
		searchdelay = setTimeout(search, 1000);
	});
	typeselector.on('change', function (event) {
		searchdelay && clearTimeout(searchdelay);
		search();
	});

	addonbrowser
		.on('click', '.theme-screenshot, .more-details', function () {
			overlay.open($(this).closest('.theme'));
		})
		.on('click', '.update', function (event) {
			event.preventDefault();
			$(this).addClass('updating-message');
			updateTemplateFromUrl(
				this.href,
				$(this).closest('.theme').data('slug')
			);
		})
		.on('click', '.download', function (event) {
			event.preventDefault();
			$(this).addClass('updating-message');
			installAddonFromUrl(
				this.href,
				$(this).closest('.theme').data('slug')
			);
		})
		.on('click', '.install', function (event) {
			event.preventDefault();
			$(this).addClass('updating-message');
			installAddon($(this).closest('.theme').data('slug'));
		})
		.on('click', '.activate', function (event) {
			event.preventDefault();
			$(this).addClass('updating-message');
			activateAddon($(this).closest('.theme').data('slug'));
		})
		.on('click', '.deactivate', function (event) {
			event.preventDefault();
			$(this).addClass('updating-message');
			deactivateAddon($(this).closest('.theme').data('slug'));
		})
		.on('click', '.popup', function () {
			var href = this.href;

			if (!/^https?/.test(href)) return true;

			var dimensions = $(this).data(),
				dualScreenLeft =
					window.screenLeft != undefined
						? window.screenLeft
						: screen.left,
				dualScreenTop =
					window.screenTop != undefined
						? window.screenTop
						: screen.top,
				width = mailster.$.window.width(),
				height = mailster.$.window.height(),
				left,
				top,
				newWindow;

			if (/%/.test(dimensions.width)) {
				dimensions.width =
					width * (parseInt(dimensions.width, 10) / 100);
			}

			if (/%/.test(dimensions.height)) {
				dimensions.height =
					height * (parseInt(dimensions.height, 10) / 100);
			}

			left = width / 2 - dimensions.width / 2 + dualScreenLeft;
			top = height / 2 - dimensions.height / 2 + dualScreenTop;
			newWindow = window.open(
				href,
				'mailster_themebrowser',
				'scrollbars=auto,resizable=1,menubar=0,toolbar=0,location=0,directories=0,status=0, width=' +
					dimensions.width +
					', height=' +
					dimensions.height +
					', top=' +
					top +
					', left=' +
					left
			);

			if (window.focus) newWindow.focus();

			return false;
		});

	$('.upload-addon').on('click', function () {
		$('.upload-field').toggle();
	});

	mailster.$.window.on('click', '.upload-addon', function () {
		$('.upload-field').show();
	});

	mailster.events.push('documentReady', function () {
		mailster.$.window.on(
			'scroll.mailster',
			mailster.util.throttle(maybeLoadTemplates, 500)
		);
	});

	var overlay = (function () {
		if (this === window) return new overlay();

		var overlay = $('.theme-overlay'),
			currentTemplate = null,
			prevTemplate = null,
			nextTemplate = null,
			current = null,
			nextbtn = overlay.find('.right'),
			prevbtn = overlay.find('.left'),
			closebtn = overlay.find('.close'),
			defaultbtn = overlay.find('.default'),
			campaignbtn = overlay.find('.campaign'),
			deletebtn = overlay.find('.delete-theme'),
			data = {};

		var open = function (addon) {
				if ('string' === typeof addon) {
					addon = $('[data-slug="' + addon + '"]');
				}
				if (!addon || !addon.length) return false;
				overlay.find('.theme-screenshots img').hide();
				overlay.find('.theme-screenshots iframe').hide();
				overlay.removeAttr('class');
				overlay.addClass('theme-overlay loading');
				currentTemplate = addon;
				data = addon.data('item');
				overlay
					.find('.theme-name')
					.html(
						data.name +
							'<span class="theme-version">' +
							(data.version ? data.version : '') +
							'</span>'
					);
				overlay
					.find('.theme-author')
					.html(mailster.util.sprintf('By ' + data.author));
				overlay.find('.theme-description').html(data.description);
				overlay
					.find('.theme-tags')
					.html(
						data.tags && data.tags.length
							? '<span>Tags:</span> ' + data.tags.join(', ')
							: ''
					);

				if (data.src) {
					overlay
						.find('.theme-screenshots iframe')
						.show()
						.attr('src', data.src + '?nocache=' + +new Date());
				} else {
					overlay
						.find('.theme-screenshots img')
						.attr('src', data.image_full)
						.attr(
							'srcset',
							data.image_full +
								' 1x, ' +
								data.image_fullx2 +
								' 2x'
						);
				}
				if (data.update_available) overlay.addClass('has-update');
				if (data.installed) overlay.addClass('is-installed');
				if (data.is_default) overlay.addClass('is-default');
				if (data.download) overlay.addClass('has-download');
				if (data.purchase_url) overlay.addClass('has-purchase');

				var files = '';
				if (data.files) {
					files +=
						'<label>Template Files: <select class="theme-file-selector">';
					for (var key in data.files) {
						if (data.files.hasOwnProperty(key))
							files +=
								'<option value="' +
								key +
								'">' +
								data.files[key].label +
								' (' +
								key +
								')</option>';
					}
					files += '</select></label>';
				}
				overlay.find('.theme-files').html(files);
				prevTemplate = currentTemplate.prev();
				nextTemplate = currentTemplate.next();
				prevbtn
					.prop('disabled', !prevTemplate.length)
					[!prevTemplate.length ? 'addClass' : 'removeClass'](
						'disabled'
					);
				nextbtn
					.prop('disabled', !nextTemplate.length)
					[!nextTemplate.length ? 'addClass' : 'removeClass'](
						'disabled'
					);
				overlay.show();
				setQueryStringParameter('addon', data.slug);
				overlay.removeClass('loading');
			},
			close = function () {
				removeQueryStringParameter('addon');
				overlay.hide();
			},
			next = function () {
				open(currentTemplate.next());
			},
			prev = function () {
				open(currentTemplate.prev());
			},
			remove = function () {
				var file = $('.theme-file-selector').val();

				if (
					'index.html' == file &&
					confirm(
						mailster.util.sprintf(
							mailster.l10n.addons.confirm_delete,
							'"' + data.name + '"'
						)
					)
				) {
					deleteTemplate(data.slug);
					close();
				} else if (
					confirm(
						mailster.util.sprintf(
							mailster.l10n.addons.confirm_delete_file,
							'"' + file + '"',
							'"' + data.name + '"'
						)
					)
				) {
					deleteTemplate(data.slug, file, function () {
						$('.theme-file-selector')
							.val('index.html')
							.trigger('change')
							.find('option[value="' + file + '"]')
							.remove();
					});
				}
				return false;
			},
			makedefault = function () {
				if (
					confirm(
						mailster.util.sprintf(
							mailster.l10n.addons.confirm_default,
							'"' + data.name + '"'
						)
					)
				) {
					var addon = $('[data-slug="' + data.slug + '"]');

					busy = true;

					mailster.util.ajax(
						'default_addon',
						{
							slug: data.slug,
						},
						function (response) {
							if (response.success) {
								setFilter('installed', function () {
									$('[data-slug="' + data.slug + '"]')
										.find('.notice-success')
										.html(
											'<p>' + response.data.msg + '</p>'
										);
								});
								addon.find('.notice-error').empty();
							} else {
								addon
									.find('.notice-error')
									.html('<p>' + response.data.msg + '</p>');
							}

							addon
								.removeClass('loading')
								.find('.updating-message')
								.removeClass('updating-message');
							busy = false;
							close();
						},
						function (jqXHR, textStatus, errorThrown) {}
					);
				}
				return false;
			},
			campaign = function () {
				document.location =
					this.href +
					'&addon=' +
					data.slug +
					'&file=' +
					overlay.find('.theme-file-selector').val();

				return false;
			},
			file = function () {
				overlay
					.find('.theme-screenshots iframe')
					.attr('src', data.files[$(this).val()].src);
			},
			init = function () {
				nextbtn.on('click', next);
				prevbtn.on('click', prev);
				closebtn.on('click', close);
				deletebtn.on('click', remove);
				defaultbtn.on('click', makedefault);
				campaignbtn.on('click', campaign);
				overlay.on('change', '.theme-file-selector', file);

				overlay
					.find('.theme-screenshots iframe')
					.on('load', function () {
						overlay.removeClass('loading');
					});
				overlay.find('.theme-screenshots img').on('load', function () {
					$(this).show();
				});
			};

		init();

		return {
			open: open,
			close: close,
			next: next,
			prev: prev,
			delete: remove,
		};
	})();

	function init() {
		searchfield.val(getQueryStringParameter('search'));
		typeselector.val(getQueryStringParameter('type') || 'term');
		currentfilter = getQueryStringParameter('browse') || 'all';
		if (getQueryStringParameter('search')) {
			search();
		} else {
			setFilter(currentfilter);
			overlay.open(getQueryStringParameter('addon'));
		}
	}

	function maybeLoadTemplates() {
		var bottom = mailster.util.top() + mailster.$.window.height();

		if (
			!busy &&
			bottom > Math.round(document.documentElement.scrollHeight * 0.9) &&
			total > currentdisplayed
		) {
			currentpage++;
			query();
		}
	}

	function setFilter(filter, cb) {
		currentfilter &&
			$('body').removeClass('browse-' + currentfilter) &&
			filterlinks
				.filter('[data-sort="' + currentfilter + '"]')
				.removeClass('current');
		currentfilter = filter || false;
		removeQueryStringParameter('addon');
		if (currentfilter) {
			resetSearch();
			filterlinks
				.filter('[data-sort="' + currentfilter + '"]')
				.addClass('current');
			setQueryStringParameter('browse', currentfilter);
			$('body').addClass('browse-' + currentfilter);
			query(cb);
		}
	}

	function resetFilter() {
		currentfilter &&
			$('body').removeClass('browse-' + currentfilter) &&
			filterlinks
				.filter('[data-sort="' + currentfilter + '"]')
				.removeClass('current');
		removeQueryStringParameter('browse');
		removeQueryStringParameter('addon');
		currentfilter = false;
		currentpage = 1;
		addons = [];
	}

	function resetSearch() {
		removeQueryStringParameter('search');
		removeQueryStringParameter('type');
		lastsearchtype = '';
		lastsearchquery = '';
		searchfield.val('');
		typeselector.val('term');
		currentpage = 1;
		addons = [];
	}

	function search() {
		searchquery = mailster.util.trim(searchfield.val());
		// Escape the term string for RegExp meta characters.
		searchquery = searchquery.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');

		searchtype = typeselector.val();

		if (
			searchquery &&
			(lastsearchquery != searchquery || lastsearchtype != searchtype)
		) {
			lastsearchtype = searchtype;
			lastsearchquery = searchquery;
			resetFilter();
			query();
			setQueryStringParameter('search', searchquery);
			setQueryStringParameter('type', searchtype);
		}
		return;
	}

	function installAddon(slug) {
		var addon = $('[data-slug="' + slug + '"]');

		busy = true;

		addon.addClass('loading');
		addon
			.find('.notice-warning')
			.addClass('updating-message')
			.html('<p>' + mailster.l10n.addons.installing + '</p>');

		mailster.util.ajax(
			'quick_install',
			{
				plugin: slug,
				step: 'install',
			},
			function (response) {
				if (response.success) {
					addon
						.addClass('is-installed')
						.find('.notice-warning')
						.removeClass('updating-message notice-warning')
						.addClass('notice-success')
						.html('<p>' + mailster.l10n.addons.installed + '</p>');
					addon.find('.notice-error').empty();
				} else {
					addon
						.find('.notice-error')
						.html('<p>' + response.data.msg + '</p>');
				}

				addon
					.removeClass('loading')
					.find('.updating-message')
					.removeClass('updating-message');
				busy = false;
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function installAddonFromUrl(url, slug) {
		var addon = $('[data-slug="' + slug + '"]');

		console.log(addon);

		busy = true;

		addon
			.addClass('loading')
			.find('.request-download')
			.addClass('updating-message');
		addon
			.find('.notice-warning')
			.addClass('updating-message')
			.html('<p>' + mailster.l10n.addons.installing + '</p>');
		return;
		mailster.util.ajax(
			'quick_install',
			{
				url: url,
				slug: slug,
			},
			function (response) {
				if (response.success) {
					addon
						.addClass('is-installed')
						.find('.notice-warning')
						.removeClass('updating-message notice-warning')
						.addClass('notice-success')
						.html('<p>' + mailster.l10n.addons.installed + '</p>');
					addon.find('.notice-error').empty();
				} else {
					addon
						.find('.notice-error')
						.html('<p>' + response.data.msg + '</p>');
				}

				addon
					.removeClass('loading')
					.find('.updating-message')
					.removeClass('updating-message');
				busy = false;
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function activateAddon(slug) {
		var addon = $('[data-slug="' + slug + '"]');

		busy = true;

		addon.addClass('loading');
		addon
			.find('.notice-warning')
			.addClass('updating-message')
			.html('<p>' + mailster.l10n.addons.activating + '</p>');

		mailster.util.ajax(
			'quick_install',
			{
				plugin: slug,
				step: 'activate',
			},
			function (response) {
				if (response.success) {
					addon
						.addClass('active')
						.find('.notice-warning')
						.removeClass('updating-message notice-warning')
						.addClass('notice-success')
						.html('<p>' + mailster.l10n.addons.activated + '</p>');
					addon.find('.notice-error').empty();
				} else {
					addon
						.find('.notice-error')
						.html('<p>' + response.data.msg + '</p>');
				}

				addon
					.removeClass('loading')
					.find('.updating-message')
					.removeClass('updating-message');
				busy = false;
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function deactivateAddon(slug) {
		var addon = $('[data-slug="' + slug + '"]');

		busy = true;

		addon.addClass('loading');
		addon
			.find('.notice-warning')
			.addClass('updating-message')
			.html('<p>' + mailster.l10n.addons.deactivating + '</p>');

		mailster.util.ajax(
			'quick_install',
			{
				plugin: slug,
				step: 'deactivate',
			},
			function (response) {
				if (response.success) {
					addon
						.removeClass('active')
						.find('.notice-warning')
						.removeClass('updating-message notice-warning')
						.addClass('notice-success')
						.html(
							'<p>' + mailster.l10n.addons.deactivated + '</p>'
						);
					addon.find('.notice-error').empty();
				} else {
					addon
						.find('.notice-error')
						.html('<p>' + response.data.msg + '</p>');
				}

				addon
					.removeClass('loading')
					.find('.updating-message')
					.removeClass('updating-message');
				busy = false;
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function updateTemplateFromUrl(url, slug) {
		var addon = $('[data-slug="' + slug + '"]');

		busy = true;

		addon
			.addClass('loading')
			.find('.request-download')
			.addClass('updating-message');
		addon
			.find('.notice-warning')
			.addClass('updating-message')
			.html('<p>' + mailster.l10n.addons.updating + '</p>');
		mailster.util.ajax(
			'download_addon',
			{
				url: url,
				slug: slug,
			},
			function (response) {
				if (response.success) {
					addon
						.find('.notice-warning')
						.removeClass('updating-message notice-warning')
						.addClass('notice-success')
						.html('<p>' + mailster.l10n.addons.updated + '</p>');
					addon.find('.notice-error').empty();
					var updatebadge = $('#menu-posts-newsletter')
						.find('.current')
						.find('.update-plugins');
					if (updatebadge) {
						if (updatebadge.text() > 1) {
							updatebadge
								.find('.update-count')
								.text(updatebadge.text() - 1);
						} else {
							updatebadge.remove();
						}
					}
				} else {
					addon
						.find('.notice-error')
						.html('<p>' + response.data.msg + '</p>');
				}

				addon
					.removeClass('loading')
					.find('.updating-message')
					.removeClass('updating-message');
				busy = false;
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function deleteTemplate(slug, file, cb) {
		var addon = $('[data-slug="' + slug + '"]');

		busy = true;

		addon.addClass('loading');

		mailster.util.ajax(
			'delete_addon',
			{
				slug: slug,
				file: file || null,
			},
			function (response) {
				if (cb) {
					addon.removeClass('loading');
					cb();
				} else {
					addon.animate(
						{
							opacity: 0,
						},
						function () {
							addon.animate(
								{
									width: 0,
									'margin-right': 0,
								},
								function () {
									addon.remove();
									busy = false;
								}
							);
						}
					);
				}
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function errorTemplate(slug, errormsg) {
		var addon = $('[data-slug="' + slug + '"]');

		addon
			.find('.notice-error')
			.html('<p>' + $('<div>' + errormsg + '</div>').text() + '</p>');
	}

	function query(cb) {
		if (currentpage == 1) {
			$('body').removeClass('no-results');
			$('body').addClass('loading-content');
			addonbrowser.html('');
			addons = [];
		}
		busy = true;

		mailster.util.ajax(
			'query_addons',
			{
				search: searchfield.val(),
				type: typeselector.val(),
				browse: getQueryStringParameter('browse'),
				page: currentpage,
			},
			function (response) {
				if (currentpage == 1) {
					$('body').removeClass('loading-content');
					$('.theme-count').html(response.data.total);
					total = response.data.total;
				}
				addons.concat(response.data.addons);
				addonbrowser.append(response.data.html);
				currentdisplayed = $('.theme').length;

				if (response.data.error) {
					alert(response.data.error);
					$('body').addClass('no-results');
				} else if (!currentdisplayed) {
					$('body').addClass('no-results');
				}

				busy = false;

				cb && cb();
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function getQueryStringParameter(name) {
		var params = new URLSearchParams(window.location.search);
		return params.get(name);
	}

	function setQueryStringParameter(name, value) {
		var params = new URLSearchParams(window.location.search);
		params.set(name, value);
		window.history.pushState(
			{},
			'',
			decodeURIComponent(window.location.pathname + '?' + params)
		);
	}

	function removeQueryStringParameter(name) {
		var params = new URLSearchParams(window.location.search);
		params.delete(name);
		window.history.pushState(
			{},
			'',
			decodeURIComponent(window.location.pathname + '?' + params)
		);
	}

	init();

	mailster.addons = mailster.addons || {};
	mailster.addons.install = installAddon;
	mailster.addons.installFromUrl = installAddonFromUrl;
	mailster.addons.delete = deleteTemplate;
	mailster.addons.error = errorTemplate;

	return mailster;
})(mailster || {}, jQuery, window, document);
