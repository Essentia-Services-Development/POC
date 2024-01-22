(function ($) {
	var $container = $('.ps-js-vipicons-container');

	$container.on('input', '.ps-vipicon-notification', function () {
		$(this).closest('.ps-js-vipiconconf').find('.ps-vipicon-hint-inner').html($(this).val());
	});

	// input handler
	$container
		.on('input', '.ps-js-vipicon input[type=text]', function () {
			var $ct = $(this).closest('.ps-js-vipiconconf'),
				$btns = $ct.find('.ps-js-btn');
			$btns.show();
		})
		.on('keydown', '.ps-js-vipicon input[type=text]', function (e) {
			var $ct, $btn;
			if (e.keyCode === 13) {
				e.preventDefault();
				e.stopPropagation();
				$ct = $(this).closest('.ps-js-vipiconconf');
				$btn = $ct.find('.ps-js-save');
				$btn.click();
			}
		})
		.find('.ps-js-vipicon input[type=text]')
		.each(function () {
			$(this).data('original-value', this.value);
		});

	$container.on('click', '.ps-js-vipicon .ps-js-cancel', function () {
		var $ct = $(this).closest('.ps-js-vipiconconf'),
			$btns = $ct.find('.ps-js-btn'),
			$input = $ct.find('input[type=text]');

		$btns.hide();
		$input.val($input.data('original-value'));
		$input.tooltip('destroy');
		$ct.find('.ps-vipicon-hint-inner').html($input.val());
	});

	// text input handler
	$container.on('click', '.ps-js-vipicon .ps-js-save', function () {
		var $ct = $(this).closest('.ps-js-vipiconconf'),
			$btns = $ct.find('.ps-js-btn'),
			$input = $ct.find('input[type=text]'),
			$progress = $ct.find('.ps-js-progress'),
			url,
			params;

		url = 'adminConfigVipicons.update';
		params = {
			id: $input.data('parent-id') || undefined,
			prop: $input.data('prop-name') || undefined,
			key: $input.data('prop-key') || undefined,
			value: $input.val()
		};

		$progress.find('img').show();
		$input.attr('readonly', 'readonly');

		peepso.postJson(url, params, function (json) {
			$progress.find('img').hide();
			$progress.find('i').show().delay(800).fadeOut();
			$input.removeAttr('readonly');

			if (json.success) {
				$btns.hide();
				$input.data('original-value', params.value);

				// change box title if saving the vipicon title
				if ('post_title' == $input.data('prop-name')) {
					$('#vipicon-' + params.id + '-box-title').html(params.value);
				}
			} else {
				// TODO
			}
		});
	});

	// checkbox handler
	$container.on(
		'click',
		'input[type=checkbox]',
		_.throttle(function () {
			var $cbx = $(this),
				$progress = $cbx.closest('.ps-js-vipiconconf').find('.ps-js-progress'),
				checked = $cbx[0].checked,
				updatevalue = false,
				url,
				params;

			url = 'adminConfigVipicons.update';
			params = {
				id: $cbx.data('parent-id') || undefined,
				prop: $cbx.data('prop-name') || undefined,
				key: $cbx.data('prop-key') || undefined,
				value: checked ? $cbx.val() : $cbx.data('disabled-value')
			};

			// check min-max value
			if (checked && params.prop === 'validation') {
				var rMatch = /^[a-z]+(min|max)$/,
					oMatch = params.key.match(rMatch),
					$tab,
					$value,
					$valuebtn,
					$paircheck,
					$pairvalue,
					pairkey,
					pairval;

				if (oMatch) {
					pairkey = params.key.replace(oMatch[1], oMatch[1] === 'min' ? 'max' : 'min');
					$tab = $cbx.closest('.ps-tab__content');
					$paircheck = $tab.find('[data-prop-key="' + pairkey + '"]');
					$pairvalue = $tab.find('[data-prop-key="' + pairkey + '_value"]');
					// validate with paired value
					if ($paircheck[0].checked) {
						pairval = $pairvalue.data('original-value') || $pairvalue.val();
						if (oMatch[1] === 'min' && +pairval < +params.value) {
							updatevalue = true;
						} else if (oMatch[1] === 'max' && +pairval > +params.value) {
							updatevalue = true;
						}
					}
					// update validation value if necessary
					if (updatevalue) {
						$value = $tab.find('[data-prop-key="' + params.key + '_value"]');
						$valuebtn = $value.nextAll('.ps-js-save');
						$value.val(pairval);
					}
				}
			}

			$progress.find('i').stop().hide();
			$progress.find('img').show();
			$cbx.attr('readonly', 'readonly');

			peepso.postJson(url, params, function (json) {
				$progress.find('img').hide();
				$progress.find('i').show().delay(800).fadeOut();
				$cbx.removeAttr('readonly');

				if (json.success) {
					// update validation value if necessary
					if (updatevalue) {
						$valuebtn.trigger('click');
					}

					$value_container_id = '#' + $cbx[0].id + '-value-container';
					$container_id = '#' + $cbx[0].id + '-container';

					if (checked) {
						$($value_container_id).fadeIn(500);
					} else {
						$($value_container_id).fadeOut(500);
					}

					if (params.prop === 'validation' && params.key === 'required') {
						var $mark = $('#vipicon-' + params.id + '-required-mark');
						if (checked) {
							$mark.removeClass('hidden');
						} else {
							$mark.addClass('hidden');
						}
					} else if (params.prop === 'post_status') {
						if (checked) {
							$cbx.closest('.postbox').removeClass('postbox-muted');
						} else {
							$cbx.closest('.postbox').addClass('postbox-muted');
						}
					}
				} else {
					// TODO
				}
			});
		}, 1000)
	);

	// select handler
	$container.on(
		'change',
		'select',
		_.throttle(function () {
			var $sel = $(this),
				$progress = $sel.closest('.ps-js-vipiconconf').find('.ps-js-progress'),
				url,
				params;

			url = 'adminConfigVipicons.update';
			params = {
				id: $sel.data('parent-id') || undefined,
				prop: $sel.data('prop-name') || undefined,
				key: $sel.data('prop-key') || undefined,
				value: $sel.val()
			};

			$progress.find('i').stop().hide();
			$progress.find('img').show();
			$sel.attr('readonly', 'readonly');

			peepso.postJson(url, params, function (json) {
				$progress.find('img').hide();
				$progress.find('i').show().delay(800).fadeOut();
				$sel.removeAttr('readonly');

				if (json.success) {
					// TODO
				} else {
					// TODO
				}
			});
		}, 1000)
	);

	// drag n' drop functionality
	$container.sortable({
		handle: '.ps-js-handle',
		update: _.throttle(function () {
			var id = [];
			$('.ps-js-vipicons-container .postbox').each(function () {
				id.push($(this).data('id'));
			});

			peepso.postJson(
				'adminConfigVipicons.reorder',
				{ id: JSON.stringify(id) },
				function (json) {}
			);
		}, 3000)
	});

	// toggle a vipicon
	$container.on('click', '.ps-js-vipicon-toggle', function () {
		var $btn = $(this),
			$el = $btn.closest('.postbox'),
			$vipicon = $el.find('.ps-js-vipicon'),
			id = $el.data('id');

		if ($vipicon.is(':visible')) {
			$vipicon.slideUp('fast', function () {
				$btn.removeClass('fa-compress').addClass('fa-expand');
				updateToggleAllButton();
				updatevipiconVisibility(id, 0);
			});
		} else {
			$vipicon.slideDown('fast', function () {
				$btn.removeClass('fa-expand').addClass('fa-compress');
				updateToggleAllButton();
				updatevipiconVisibility(id, 1);
			});
		}
	});

	// toggle a vipicon
	$container.on('click', '.ps-js-vipicon-title', function (e) {
		if (e.target === e.currentTarget) {
			$(this).closest('.ps-postbox__title').find('.ps-js-vipicon-toggle').click();
		}
	});

	// toggle drag-n-drop cursor
	var mousedownTimer;
	$container
		.on('mousedown', '.ps-postbox__title', function (e) {
			var $this = $(e.currentTarget);
			mousedownTimer = setTimeout(function () {
				$this.addClass('ps-js-mousedown');
			}, 200);
		})
		.on('mouseup mouseleave', '.ps-postbox__title', function (e) {
			clearTimeout(mousedownTimer);
			$(e.currentTarget).removeClass('ps-js-mousedown');
		});

	// toggle expand all vipicons
	$('.ps-js-vipicon-expand-all').on('click', function () {
		$container.find('.ps-js-vipicon').slideDown('fast', function () {
			toggleAllCallback(1);
		});
	});

	// toggle collapse all vipicon
	$('.ps-js-vipicon-collapse-all').on('click', function () {
		$container.find('.ps-js-vipicon').slideUp('fast', function () {
			toggleAllCallback(0);
		});
	});

	var toggleAllCallback = _.debounce(function (status) {
		var $vipicons = $('.ps-js-vipicons-container').children('.postbox');

		if (status === 0) {
			$vipicons
				.find('.ps-js-vipicon-toggle')
				.removeClass('fa-compress')
				.addClass('fa-expand');
		} else {
			$vipicons
				.find('.ps-js-vipicon-toggle')
				.removeClass('fa-expand')
				.addClass('fa-compress');
		}

		updateToggleAllButton(status);
		updatevipiconVisibility('all', status);
	}, 200);

	function updateToggleAllButton(status) {
		var $btn = $('.ps-js-vipicon-toggle-all'),
			$icon = $btn.find('span').first(),
			$label = $btn.find('span').last(),
			len,
			visible;

		if (typeof status === 'undefined') {
			len = 0;
			visible = 0;
			status = 0;
			$('.ps-js-vipicons-container')
				.find('.ps-js-vipicon')
				.each(function () {
					len++;
					if ($(this).is(':visible')) {
						visible++;
					}
				});
			if (visible >= len) {
				status = 1;
			}
		}

		if (+status === 0) {
			$btn.data('status', 0);
			$label.html($btn.data('expand-text'));
			$icon.removeClass('fa-compress').addClass('fa-expand');
		} else {
			$btn.data('status', 1);
			$label.html($btn.data('collapse-text'));
			$icon.removeClass('fa-expand').addClass('fa-compress');
		}
	}

	// check button on page-load
	updateToggleAllButton();

	var updatevipiconXHR = {};
	var updatevipiconVisibility = _.debounce(function (id, status) {
		var ids = [];
		if (id !== 'all') {
			ids = [id];
		} else {
			$('.ps-js-vipicons-container')
				.children('.postbox')
				.each(function () {
					ids.push($(this).data('id'));
				});
		}

		updatevipiconXHR[id] && updatevipiconXHR[id].ret && updatevipiconXHR[id].ret.abort();
		updatevipiconXHR[id] = peepso.postJson(
			'adminConfigVipicons.update',
			{ prop: 'box_status', id: JSON.stringify(ids), status: status },
			function (json) {
				// Do nothing
			}
		);
	}, 500);

	// add new vipicon button
	$('.ps-js-vipicon-new').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var $button = $(this);
		$button.removeClass('btn-primary');
		var $oldHtml = $button.html();
		$button.html('<img src="images/wpspin_light.gif">');

		peepso.postJson(
			'adminConfigVipicons.create',
			{ type: $(this).data('value') },
			function (response) {
				if (response.success) {
					$button.html($oldHtml);
					$button.addClass('btn-primary');

					$container.append(response.data.html);
					initSortOption();
					$('html, body').animate({ scrollTop: $(document).height() }, 'fast');
				}
			}
		);
	});

	// deleting
	$container.on('click', '.ps-js-vipicon-delete', function (e) {
		e.preventDefault();
		e.stopPropagation();

		if (confirm('Are you sure want to delete this Icon?')) {
			$(this).html('<img src="images/wpspin_light.gif">');
			var id = $(this).data('id');
			peepso.postJson('adminConfigVipicons.delete', { id: id }, function (response) {
				if (response.success) {
					var $el = $container.children('[data-id=' + id + ']');
					$el.slideUp('fast', function () {
						$el.remove();
					});
				}
			});
		}
	});

	// Show icon picker
	$container.on('click', '.ps-js-vipicon-icon', function (e) {
		var id = $(this).data('id');
		$('#icon-picker-current').val(id);
		$('#ps-vipicons-icon-picker').slideDown('fast');

		// Hide the contents of the box
		$(this).parent().parent().nextAll().slideUp('fast');
		updateToggleAllButton();
		updatevipiconVisibility(id, 0);

		// Hide all boxes below current
		$(this).parent().parent().parent().nextAll().slideUp('fast');
	});

	// Close icon picker
	$('#ps-vipicons-icon-picker-toggle').on('click', function () {
		// Hide picker
		$('#ps-vipicons-icon-picker').slideUp('fast');

		// Restore all boxes
		$container.children().slideDown('fast');
	});

	// Click picker inner icon
	$('.ps-vipicons-icon-picker-item').on('click', function () {
		// Close picker
		$('#ps-vipicons-icon-picker-toggle').click();

		// Get icon data to store
		var $icon = $(this).data('id');
		var $icon_url = $(this).data('url');

		// vipicon ID and vipicon icon handle
		var $vipicon_id = $('#icon-picker-current').val();
		var $img = $('#ps-js-vipicon-' + $vipicon_id + '-icon');

		// Restore the contents of the box
		$img.parent().parent().parent().nextAll().slideDown('fast');
		updateToggleAllButton();
		updatevipiconVisibility($vipicon_id, 1);

		// Replace icon with loading gif
		$img.attr('src', 'images/wpspin_light-2x.gif');

		// Send AJAX call
		var params = {
			id: $vipicon_id,
			prop: 'post_excerpt',
			value: $icon
		};

		peepso.postJson('adminConfigVipicons.update', params, function (response) {
			if (response.success) {
				// Replace image with new icon
				$img.attr('src', $icon_url);
			}
		});
	});

	// edit title handler
	$container.on('click', '.ps-postbox__title .fa-edit', function () {
		var $ct = $(this).closest('.ps-postbox__title'),
			$label = $ct.find('.ps-postbox__title-label'),
			$editor = $ct.find('.ps-postbox__title-editor'),
			$input = $editor.find('input[type=text]'),
			$btn = $input.nextAll('.ps-js-save'),
			isDefault = $input.data('prop-title-is-default'),
			value = $input.val();

		$label.hide();
		$editor.show();
		$input.data('original-value', value).focus();
		$input.val(isDefault ? '' : value).trigger('input');
	});

	// edit title handler
	$container.on('click', '.ps-js-vipicon-title-text', function () {
		var $ct = $(this).closest('.ps-postbox__title'),
			$btn = $ct.find('.fa-edit');
		$btn.click();
	});

	// cancel edit title handler
	$container.on('click', '.ps-postbox__title .ps-js-cancel', function () {
		var $ct = $(this).closest('.ps-postbox__title'),
			$label = $ct.find('.ps-postbox__title-label'),
			$editor = $ct.find('.ps-postbox__title-editor'),
			$input = $editor.find('input[type=text]');

		$input.val($input.data('original-value'));
		$editor.hide();
		$label.show();
	});

	// save edit title handler
	$container.on('click', '.ps-postbox__title .ps-js-save', function () {
		var $ct = $(this).closest('.ps-postbox__title'),
			$label = $ct.find('.ps-postbox__title-label'),
			$editor = $ct.find('.ps-postbox__title-editor'),
			$input = $editor.find('input[type=text]'),
			$progress = $ct.find('.ps-js-progress'),
			url,
			params;

		url = 'adminConfigVipicons.update';
		params = {
			id: $input.data('parent-id') || undefined,
			prop: $input.data('prop-name') || undefined,
			value: $input.val()
		};

		$progress.find('img').show();
		$input.attr('readonly', 'readonly');

		peepso.postJson(url, params, function (json) {
			$progress.find('img').hide();
			$progress.find('i').show().delay(800).fadeOut();
			$input.removeAttr('readonly');

			if (json.success) {
				console.log($input.data('prop-name'));

				if ('post_title' == $input.data('prop-name')) {
					$('#vipicon-' + $input.data('parent-id') + '-default-title-notice').fadeOut();
				}

				$editor.hide();
				$label.show();
				$input.data('original-value', params.value);
				$input.data('prop-title-is-default', false);
				$('#vipicon-' + params.id + '-box-title').html(params.value);
			} else {
				// TODO
			}
		});
	});

	// save edit title handler on enter
	$container
		.on('keydown', '.ps-postbox__title input[type=text]', function (e) {
			var $btn;
			if (e.keyCode === 13) {
				e.preventDefault();
				e.stopPropagation();
				$btn = $(this).nextAll('.ps-js-save');
				$btn.click();
			}
		})
		.on('input', '.ps-postbox__title input[type=text]', function (e) {
			var $btn = $(this).nextAll('.ps-js-save');
			if (!$.trim(this.value)) {
				$btn.attr('disabled', 'disabled');
			} else {
				$btn.removeAttr('disabled');
			}
		});

	// cycle through option
	$container.on('focus', '.ps-js-focusguard', function () {
		var $guard = $(this),
			$vipicons = $guard.closest('.ps-js-options').children('.ps-js-vipiconconf');

		if ($guard.data('tag') === 'last') {
			$vipicons.find('input').first().focus();
		} else {
			$vipicons.find('input').last().focus();
		}
	});

	// float-bar
	$(function () {
		var bar = $('.ps-settings__bar');
		bar.addClass('ps-settings__bar--static');

		$(window).scroll(function () {
			var bar = $('.ps-settings__bar');
			var scrollVal = $(this).scrollTop();
			if (scrollVal > 50) {
				bar.removeClass('ps-settings__bar--static');
			} else {
				bar.addClass('ps-settings__bar--static');
			}
		});
	});

	// media library

	$('.btn-img-vipicon').on('click', function (e) {
		var $button = $(this),
			frame = $button.data('frame'),
			id = $button.data('id');

		if (frame) {
			frame.open();
			return;
		}

		frame = wp.media({
			library: { type: 'image' },
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();

			// Close picker
			$('#ps-vipicons-icon-picker-toggle').click();

			// Get icon data to store
			var $icon_url = attachment.url;
			var $icon = 'peepsocustom-' + attachment.id;
			// var $icon = 'peepsocustom-' + $icon_url;

			// vipicon ID and vipicon icon handle
			var $vipicon_id = $('#icon-picker-current').val();
			var $img = $('#ps-js-vipicon-' + $vipicon_id + '-icon');

			// Restore the contents of the box
			$img.parent().parent().parent().nextAll().slideDown('fast');
			updateToggleAllButton();
			updatevipiconVisibility($vipicon_id, 1);

			// Replace icon with loading gif
			$img.attr('src', 'images/wpspin_light-2x.gif');

			// Send AJAX call
			var params = {
				id: $vipicon_id,
				prop: 'post_excerpt',
				value: $icon
			};

			peepso.postJson('adminConfigVipicons.update', params, function (response) {
				if (response.success) {
					// Replace image with new icon
					$img.attr('src', $icon_url);
				}
			});
		});

		$button.data('frame', frame);
		frame.open();
	});
})(jQuery);
