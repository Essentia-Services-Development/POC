mailster = (function (mailster, $, window, document) {
	'use strict';

	var filterbar = $('.wp-filter'),
		templatebrowser = $('.theme-browser'),
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
		templates = [],
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

	templatebrowser
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
			downloadTemplateFromUrl(
				this.href,
				$(this).closest('.theme').data('slug')
			);
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

	$('.upload-template').on('click', function () {
		$('.upload-field').toggle();
	});

	mailster.$.window.on('click', '.upload-template', function () {
		$('.upload-field').show();
	});

	mailster.events.push('documentReady', function () {
		uploader_init();
		mailster.$.window.on(
			'scroll.mailster',
			mailster.util.throttle(maybeLoadTemplates, 500)
		);
	});
	mailster.events.push('windowLoad', function () {
		//fixes update nag with Newspaper theme
		$('body').off('mousedown');
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
			editbtn = overlay.find('.edit'),
			savebtn = overlay.find('.save'),
			deletebtn = overlay.find('.delete-theme'),
			codeeditor,
			codecontent = overlay.find('.codeeditor textarea'),
			data = {};

		var open = function (template) {
				if ('string' === typeof template) {
					template = $('[data-slug="' + template + '"]');
				}
				if (!template || !template.length) return false;
				overlay.addClass('loading');
				overlay.find('.theme-screenshots img').hide();
				overlay.find('.theme-screenshots iframe').hide();
				overlay.removeAttr('class');
				overlay.addClass('theme-overlay loading');
				currentTemplate = template;
				data = template.data('item');
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
				setQueryStringParameter('template', data.slug);
			},
			close = function () {
				removeQueryStringParameter('template');
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
							mailster.l10n.templates.confirm_delete,
							'"' + data.name + '"'
						)
					)
				) {
					deleteTemplate(data.slug);
					close();
				} else if (
					confirm(
						mailster.util.sprintf(
							mailster.l10n.templates.confirm_delete_file,
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
							mailster.l10n.templates.confirm_default,
							'"' + data.name + '"'
						)
					)
				) {
					var template = $('[data-slug="' + data.slug + '"]');

					busy = true;

					mailster.util.ajax(
						'default_template',
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
								template.find('.notice-error').empty();
							} else {
								template
									.find('.notice-error')
									.html('<p>' + response.data.msg + '</p>');
							}

							template
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
					'&template=' +
					data.slug +
					'&file=' +
					overlay.find('.theme-file-selector').val();

				return false;
			},
			edit = function () {
				mailster.util.ajax(
					'load_template_file',
					{
						template: data.slug,
						file: overlay.find('.theme-file-selector').val(),
					},
					function (response) {
						overlay.addClass('is-editor');
						overlay
							.find('.codeeditor h3')
							.html(
								mailster.util.sprintf(
									mailster.l10n.templates.editing,
									overlay.find('.theme-file-selector').val(),
									'"' + data.name + '"'
								)
							);
						$('.CodeMirror').remove();
						codecontent.val(response.data.html);
						if (wp.codeEditor) {
							codeeditor = wp.codeEditor.initialize(codecontent, {
								codemirror: mailster.util.codemirrorargs,
							});
						} else {
							codeeditor = {
								codemirror: window.CodeMirror.fromTextArea(
									codecontent.get(0),
									mailster.util.codemirrorargs
								),
							};
						}
					},
					function (jqXHR, textStatus, errorThrown) {}
				);

				return false;
			},
			save = function () {
				mailster.util.ajax(
					'set_template_html',
					{
						content: codeeditor.codemirror.getValue(),
						slug: data.slug,
						file: overlay.find('.theme-file-selector').val(),
					},
					function (response) {
						overlay.removeClass('is-editor');
						$('.CodeMirror').remove();
						codecontent.val('');
						overlay
							.find('.theme-screenshots iframe')
							.attr(
								'src',
								overlay
									.find('.theme-screenshots iframe')
									.attr('src')
							);
					},
					function (jqXHR, textStatus, errorThrown) {}
				);

				return false;
			},
			file = function () {
				overlay.addClass('loading');
				overlay
					.find('.theme-screenshots iframe')
					.attr('src', data.files[$(this).val()].src);
				if (overlay.is('.is-editor')) {
					edit();
				}
			},
			init = function () {
				nextbtn.on('click', next);
				prevbtn.on('click', prev);
				closebtn.on('click', close);
				deletebtn.on('click', remove);
				defaultbtn.on('click', makedefault);
				campaignbtn.on('click', campaign);
				editbtn.on('click', edit);
				savebtn.on('click', save);
				overlay.on('change', '.theme-file-selector', file);
				overlay.on('click', '.theme-description a', function (event) {
					event.preventDefault();
					window.open(this.href);
					return false;
				});
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
		currentfilter = getQueryStringParameter('browse') || 'installed';
		if (getQueryStringParameter('search')) {
			search();
		} else {
			setFilter(currentfilter);
			overlay.open(getQueryStringParameter('template'));
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
		removeQueryStringParameter('template');
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
		removeQueryStringParameter('template');
		currentfilter = false;
		currentpage = 1;
		templates = [];
	}

	function resetSearch() {
		removeQueryStringParameter('search');
		removeQueryStringParameter('type');
		lastsearchtype = '';
		lastsearchquery = '';
		searchfield.val('');
		typeselector.val('term');
		currentpage = 1;
		templates = [];
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

	function downloadTemplate(slug) {
		var template = $('[data-slug="' + slug + '"]');

		busy = true;

		template.addClass('loading');

		template
			.find('.notice-warning')
			.addClass('updating-message')
			.html('<p>Updating...</p>');

		mailster.util.ajax(
			'download_template',
			{
				slug: slug,
			},
			function (response) {
				// template.animate({width:0, 'margin-right':0}, function(){
				// 	template.remove();
				// 	busy = false;
				// });
				busy = false;
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function downloadTemplateFromUrl(url, slug) {
		var template = $('[data-slug="' + slug + '"]');

		busy = true;

		template
			.addClass('loading')
			.find('.request-download')
			.addClass('updating-message');
		template
			.find('.notice-warning')
			.addClass('updating-message')
			.html('<p>' + mailster.l10n.templates.downloading + '</p>');
		mailster.util.ajax(
			'download_template',
			{
				url: url,
				slug: slug,
			},
			function (response) {
				if (response.success) {
					template
						.addClass('is-installed')
						.find('.notice-warning')
						.removeClass('updating-message notice-warning')
						.addClass('notice-success')
						.html(
							'<p>' + mailster.l10n.templates.downloaded + '</p>'
						);
					template.find('.notice-error').empty();
				} else {
					template
						.find('.notice-error')
						.html('<p>' + response.data.msg + '</p>');
				}

				template
					.removeClass('loading')
					.find('.updating-message')
					.removeClass('updating-message');
				busy = false;
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function updateTemplateFromUrl(url, slug) {
		var template = $('[data-slug="' + slug + '"]');

		busy = true;

		template
			.addClass('loading')
			.find('.request-download')
			.addClass('updating-message');
		template
			.find('.notice-warning')
			.addClass('updating-message')
			.html('<p>' + mailster.l10n.templates.updating + '</p>');
		mailster.util.ajax(
			'download_template',
			{
				url: url,
				slug: slug,
			},
			function (response) {
				if (response.success) {
					template
						.find('.notice-warning')
						.removeClass('updating-message notice-warning')
						.addClass('notice-success')
						.html('<p>' + mailster.l10n.templates.updated + '</p>');
					template.find('.notice-error').empty();
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
					template
						.find('.notice-error')
						.html('<p>' + response.data.msg + '</p>');
				}

				template
					.removeClass('loading')
					.find('.updating-message')
					.removeClass('updating-message');
				busy = false;
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function deleteTemplate(slug, file, cb) {
		var template = $('[data-slug="' + slug + '"]');

		busy = true;

		template.addClass('loading');

		mailster.util.ajax(
			'delete_template',
			{
				slug: slug,
				file: file || null,
			},
			function (response) {
				if (cb) {
					template.removeClass('loading');
					cb();
				} else {
					template.animate(
						{
							opacity: 0,
						},
						function () {
							template.animate(
								{
									width: 0,
									'margin-right': 0,
								},
								function () {
									template.remove();
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
		var template = $('[data-slug="' + slug + '"]');

		template
			.find('.notice-error')
			.html('<p>' + $('<div>' + errormsg + '</div>').text() + '</p>');
	}

	function query(cb) {
		if (currentpage == 1) {
			$('body').removeClass('no-results');
			$('body').addClass('loading-content');
			templatebrowser.html('');
			templates = [];
		}
		busy = true;

		mailster.util.ajax(
			'query_templates',
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
				templates.concat(response.data.templates);
				templatebrowser.append(response.data.html);
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

	function uploader_init() {
		var uploader = new plupload.Uploader(wpUploaderInit),
			uploadinfo = $('.uploadinfo');

		uploader.bind('Init', function (up) {
			var uploaddiv = $('#plupload-upload-ui');

			if (up.features.dragdrop && !mailster.util.isTouchDevice) {
				uploaddiv.addClass('drag-drop');
				$('#drag-drop-area')
					.bind('dragover.wp-uploader', function () {
						// dragenter doesn't fire right :(
						uploaddiv.addClass('drag-over');
					})
					.bind(
						'dragleave.wp-uploader, drop.wp-uploader',
						function () {
							uploaddiv.removeClass('drag-over');
						}
					);
			} else {
				uploaddiv.removeClass('drag-drop');
				$('#drag-drop-area').unbind('.wp-uploader');
			}

			if (up.runtime == 'html4') $('.upload-flash-bypass').hide();
		});

		uploader.init();

		uploader.bind('FilesAdded', function (up, files) {
			setTimeout(function () {
				up.refresh();
				up.start();
			}, 1);
		});

		uploader.bind('BeforeUpload', function (up, file) {});

		uploader.bind('UploadFile', function (up, file) {});

		uploader.bind('UploadProgress', function (up, file) {
			uploadinfo.html(
				mailster.util.sprintf(
					mailster.l10n.templates.uploading,
					file.percent + '%'
				)
			);
		});

		uploader.bind('Error', function (up, err) {
			uploadinfo.html(err.message);
			up.refresh();
		});

		uploader.bind('FileUploaded', function (up, file, response) {
			response = JSON.parse(response.response);
			if (response.success) {
				location.reload();
			} else {
				uploadinfo.html(response.data.error);
			}
		});

		uploader.bind('UploadComplete', function (up, files) {});
	}

	init();

	mailster.templates = mailster.templates || {};
	mailster.templates.download = downloadTemplate;
	mailster.templates.downloadFromUrl = downloadTemplateFromUrl;
	mailster.templates.delete = deleteTemplate;
	mailster.templates.error = errorTemplate;

	return mailster;
})(mailster || {}, jQuery, window, document);
