(function ($) {
	var $container = $('.ps-js-fields-container'),
		$btn = $('.ps-js-field-new'),
		$dropdown = $('<div class="ps-dropdown__menu" />'),
		types = window.peepsofieldtypes || {},
		html;

	// Check element availability.
	if (!($container.length || $btn.length)) {
		return;
	}

	// Map dropdown option.
	html = '';
	_.each(types, function (text, value) {
		html += '<a href="#" data-value="' + value + '">' + text + '</a>';
	});

	$dropdown.append(html);
	$dropdown.insertAfter($btn);

	// Handle toggle dropdown, override default handler.
	$btn.off('click').on('click', function (e) {
		var evtName = 'click.ps-extended-profile',
			$doc = $(document);

		e.preventDefault();
		e.stopPropagation();

		$doc.off(evtName);

		if ($dropdown.is(':visible')) {
			$dropdown.hide();
		} else {
			$dropdown.show();
			$doc.one(evtName, function () {
				$dropdown.hide();
			});
		}
	});

	// Handle select dropdown option.
	$dropdown.on('click', 'a', function (e) {
		var type = $(this).data('value');

		e.preventDefault();
		e.stopPropagation();

		peepso.postJson('adminextendedprofiles.add_field', { type: type }, function (json) {
			$dropdown.hide();
			if (json.success) {
				$container.append(json.data.html);
				initSortOption();
				$('html, body').animate(
					{
						scrollTop: $(document).height()
					},
					'fast'
				);
			}
		});
	});

	// handle delete field button
	$container.on('click', '.ps-js-field-delete', function (e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('Are you sure want to delete this field?')) {
			var id = $(this).data('id');
			peepso.postJson('adminextendedprofiles.delete_field', { id: id }, function (response) {
				if (response.success) {
					var $el = $container.children('[data-id=' + id + ']');
					$el.slideUp('fast', function () {
						$el.remove();
					});
				}
			});
		}
	});

	// handle duplicate field button
	$container.on('click', '.ps-js-field-duplicate', function (e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('Are you sure want to duplicate this field?')) {
			var id = $(this).data('id');
			peepso.postJson(
				'adminextendedprofiles.duplicate_field',
				{ id: id },
				function (response) {
					if (response.success) {
						console.log(11);
						$container.append(response.data.html);
						initSortOption();
						$('html, body').animate({ scrollTop: $(document).height() }, 'fast');
					} else if (response.errors) {
						console.log(22);
						alert(response.errors[0]);
					}
				}
			);
		}
	});

	// handle add new option button
	$container.on('click', '.ps-js-option-new', function (e) {
		var $ct = $(this).closest('.ps-js-field'),
			$template = $ct.find('.ps-js-option-template'),
			$options = $ct.find('[data-prop-name=select_options]'),
			html = $template.html(),
			id = $ct.data('id'),
			key = 'option_' + id + '_';

		// generate unique id
		for (var i = 1; ; i++) {
			if ($options.filter('[data-prop-key="' + key + i + '"]').length < 1) {
				key = key + i;
				break;
			}
		}

		// replace default key and value
		html = html.replace(/___val___/g, '');
		html = html.replace(/___key___/g, key);
		$template.before(html);
		$ct.find('input[data-prop-key="' + key + '"]').focus();
		saveOptionState(id);
	});

	// handle delete an option
	$container.on('click', '.ps-js-option-delete', function (e) {
		var $el, $ct, id;

		e.preventDefault();
		e.stopPropagation();

		if (confirm('Are you sure want to delete this option?')) {
			$el = $(this);
			$ct = $el.closest('.ps-js-field');
			id = $ct.data('id');
			$el.closest('.ps-js-fieldconf').remove();
			saveOptionState(id);
		}
	});

	// handle reset privacy
	$container.on('click', '.ps-js-reset-privacy', function (e) {
		var $btn, $ct, $sel, $progress;

		e.preventDefault();
		e.stopPropagation();

		// prevent repeated click
		$btn = $(this);
		if ($btn.data('progress')) {
			return;
		}

		$btn.data('progress', 1).css({ opacity: 0.5 });
		$ct = $btn.closest('.ps-js-fieldconf');
		$sel = $ct.find('select');
		$progress = $ct.find('.ps-js-progress');

		$sel.attr('readonly', 'readonly');
		$progress.find('i').stop().hide();
		$progress.find('img').show();

		peepso.postJson(
			'adminExtendedProfiles.reset_privacy',
			{ id: $sel.data('parent-id') },
			function (json) {
				$progress.find('img').hide();
				$progress.find('i').show().delay(800).fadeOut();
				$sel.removeAttr('readonly');
				$btn.removeData('progress').css({ opacity: '' });
			}
		);
	});

	// options drag n' drop functionality
	function initSortOption() {
		$container.find('.ps-js-options').sortable({
			handle: '.ps-js-option-handle',
			update: _.throttle(function () {
				var $ct = $(this).closest('.ps-js-field'),
					id = $ct.data('id');
				saveOptionState(id);
			}, 3000)
		});
	}

	initSortOption();

	var saveOptionXHR = {};

	function saveOptionState(id) {
		var $ct = $container.find('.ps-js-field').filter('[data-id="' + id + '"]'),
			$fields = $ct
				.find('.ps-js-options')
				.children('.ps-js-fieldconf')
				.find('input[type=text]'),
			$loading = $ct.find('.ps-js-options').siblings('.ps-js-loading'),
			url = 'adminConfigFields.set_meta',
			params = {};

		params.id = id;
		params.prop = 'select_options';
		params.value = {};
		params.json = 1;

		$fields.each(function () {
			var $el = $(this);
			params.value[$el.data('prop-key')] = $el.val();
		});

		// json-encode value
		params.value = JSON.stringify(params.value);

		// show loading
		$loading.stop().show();

		saveOptionXHR[id] && saveOptionXHR[id].ret && saveOptionXHR[id].ret.abort();
		saveOptionXHR[id] = peepso.postJson(url, params, function (json) {
			$loading.stop().fadeOut();
		});
	}
})(jQuery);
