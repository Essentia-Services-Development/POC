(function ($) {
	var $container = $('.ps-js-reactions-container');

	$container.on('input', '.ps-js-reaction-notiftext', function (e) {
		var $input = $(this),
			$reaction = $input.closest('.ps-js-reaction'),
			$hint = $reaction.find('.ps-js-reaction-notifhint'),
			$hintEmpty = $reaction.find('.ps-js-reaction-notifhint-empty'),
			value = $input.val().trim();

		e.stopPropagation();

		if (value) {
			$hintEmpty.hide();
			$hint.show();
			$hint.find('.ps-js-reaction-notifhint-text').html(value);
		} else {
			$hint.hide();
			$hintEmpty.show();
		}
	});

	$container.find('.ps-js-reaction-notiftext').trigger('input');

	// input handler
	$container
		.on('input', '.ps-js-reaction input[type=text]', function () {
			var $ct = $(this).closest('.ps-js-reactionconf'),
				$btns = $ct.find('.ps-js-btn');
			$btns.show();
		})
		.on('keydown', '.ps-js-reaction input[type=text]', function (e) {
			var $ct, $btn;
			if (e.keyCode === 13) {
				e.preventDefault();
				e.stopPropagation();
				$ct = $(this).closest('.ps-js-reactionconf');
				$btn = $ct.find('.ps-js-save');
				$btn.click();
			}
		})
		.find('.ps-js-reaction input[type=text]')
		.each(function () {
			$(this).data('original-value', this.value);
		});

	$container.on('click', '.ps-js-reaction .ps-js-cancel', function () {
		var $ct = $(this).closest('.ps-js-reactionconf'),
			$btns = $ct.find('.ps-js-btn'),
			$input = $ct.find('input[type=text]');

		$btns.hide();
		$input.val($input.data('original-value')).trigger('input');
		$ct.find('.ps-reaction-hint-inner').html($input.val());
	});

	// text input handler
	$container.on('click', '.ps-js-reaction .ps-js-save', function () {
		var $ct = $(this).closest('.ps-js-reactionconf'),
			$btns = $ct.find('.ps-js-btn'),
			$input = $ct.find('input[type=text]'),
			$progress = $ct.find('.ps-js-progress'),
			url,
			params;

		url = 'adminConfigreactions.update';
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

				// change box title if saving the reaction title
				if ('post_title' == $input.data('prop-name')) {
					$('#reaction-' + params.id + '-box-title').html(params.value);
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
				$progress = $cbx.closest('.ps-js-reactionconf').find('.ps-js-progress'),
				checked = $cbx[0].checked,
				updatevalue = false,
				url,
				params;

			url = 'adminConfigreactions.update';
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
						var $mark = $('#reaction-' + params.id + '-required-mark');
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
				$progress = $sel.closest('.ps-js-reactionconf').find('.ps-js-progress'),
				url,
				params;

			url = 'adminConfigreactions.update';
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
			$('.ps-js-reactions-container .postbox').each(function () {
				id.push($(this).data('id'));
			});

			peepso.postJson(
				'adminConfigreactions.reorder',
				{ id: JSON.stringify(id) },
				function (json) {}
			);
		}, 3000)
	});

	// toggle a reaction
	$container.on('click', '.ps-js-reaction-toggle', function () {
		var $btn = $(this),
			$el = $btn.closest('.postbox'),
			$reaction = $el.find('.ps-js-reaction'),
			id = $el.data('id');

		if ($reaction.is(':visible')) {
			$reaction.slideUp('fast', function () {
				$btn.removeClass('fa-compress').addClass('fa-expand');
				updateToggleAllButton();
				updatereactionVisibility(id, 0);
			});
		} else {
			$reaction.slideDown('fast', function () {
				$btn.removeClass('fa-expand').addClass('fa-compress');
				updateToggleAllButton();
				updatereactionVisibility(id, 1);
			});
		}
	});

	// toggle a reaction
	$container.on('click', '.ps-js-reaction-title', function (e) {
		if (e.target === e.currentTarget) {
			$(this).closest('.ps-postbox__title').find('.ps-js-reaction-toggle').click();
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

	// toggle expand all reactions
	$('.ps-js-reaction-expand-all').on('click', function () {
		$container.find('.ps-js-reaction').slideDown('fast', function () {
			toggleAllCallback(1);
		});
	});

	// toggle collapse all reactions
	$('.ps-js-reaction-collapse-all').on('click', function () {
		$container.find('.ps-js-reaction').slideUp('fast', function () {
			toggleAllCallback(0);
		});
	});

	var toggleAllCallback = _.debounce(function (status) {
		var $reactions = $('.ps-js-reactions-container').children('.postbox');

		if (status === 0) {
			$reactions
				.find('.ps-js-reaction-toggle')
				.removeClass('fa-compress')
				.addClass('fa-expand');
		} else {
			$reactions
				.find('.ps-js-reaction-toggle')
				.removeClass('fa-expand')
				.addClass('fa-compress');
		}

		updateToggleAllButton(status);
		updatereactionVisibility('all', status);
	}, 200);

	function updateToggleAllButton(status) {
		var $btn = $('.ps-js-reaction-toggle-all'),
			$icon = $btn.find('span').first(),
			$label = $btn.find('span').last(),
			len,
			visible;

		if (typeof status === 'undefined') {
			len = 0;
			visible = 0;
			status = 0;
			$('.ps-js-reactions-container')
				.find('.ps-js-reaction')
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

	var updatereactionXHR = {};
	var updatereactionVisibility = _.debounce(function (id, status) {
		var ids = [];
		if (id !== 'all') {
			ids = [id];
		} else {
			$('.ps-js-reactions-container')
				.children('.postbox')
				.each(function () {
					ids.push($(this).data('id'));
				});
		}

		updatereactionXHR[id] && updatereactionXHR[id].ret && updatereactionXHR[id].ret.abort();
		updatereactionXHR[id] = peepso.postJson(
			'adminConfigreactions.update',
			{ prop: 'box_status', id: JSON.stringify(ids), status: status },
			function (json) {
				// Do nothing
			}
		);
	}, 500);

	// add new reaction button
	$('.ps-js-reaction-new').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var $button = $(this);
		$button.removeClass('btn-primary');
		var $oldHtml = $button.html();
		$button.html('<img src="images/wpspin_light.gif">');

		peepso.postJson(
			'adminConfigreactions.create',
			{ type: $(this).data('value') },
			function (response) {
				if (response.success) {
					$button.html($oldHtml);
					$button.addClass('btn-primary');

					$container.append(response.data.html);
					$container.find('.ps-js-reaction-notiftext').trigger('input');

					$('html, body').animate({ scrollTop: $(document).height() }, 'fast');
				}
			}
		);
	});

	// deleting
	$container.on('click', '.ps-js-reaction-delete', function (e) {
		e.preventDefault();
		e.stopPropagation();

		if (confirm('Are you sure want to delete this Reaction?')) {
			$(this).html('<img src="images/wpspin_light.gif">');
			var id = $(this).data('id');
			peepso.postJson('adminConfigreactions.delete', { id: id }, function (response) {
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
	$container.on('click', '.ps-js-reaction-icon', function (e) {
		var id = $(this).data('id');
		$('#icon-picker-current').val(id);
		$('#ps-reactions-icon-picker').slideDown('fast');

		// Hide the contents of the box
		$(this).parent().parent().nextAll().slideUp('fast');
		updateToggleAllButton();
		updatereactionVisibility(id, 0);

		// Hide all boxes below current
		$(this).parent().parent().parent().nextAll().slideUp('fast');
	});

	// Close icon picker
	$('#ps-reactions-icon-picker-toggle').on('click', function () {
		// Hide picker
		$('#ps-reactions-icon-picker').slideUp('fast');

		// Restore all boxes
		$container.children().slideDown('fast');
	});

	// Click picker inner icon
	$('.ps-reactions-icon-picker-item').on('click', function () {
		// Close picker
		$('#ps-reactions-icon-picker-toggle').click();

		// Get icon data to store
		var $icon = $(this).data('id');
		var $icon_url = $(this).data('url');

		// Reaction ID and reaction icon handle
		var $reaction_id = $('#icon-picker-current').val();
		var $img = $('#ps-js-reaction-' + $reaction_id + '-icon');

		// Restore the contents of the box
		$img.parent().parent().parent().nextAll().slideDown('fast');
		updateToggleAllButton();
		updatereactionVisibility($reaction_id, 1);

		// Replace icon with loading gif
		$img.attr('src', 'images/wpspin_light-2x.gif');

		// Send AJAX call
		var params = {
			id: $reaction_id,
			prop: 'post_excerpt',
			value: $icon
		};

		peepso.postJson('adminConfigreactions.update', params, function (response) {
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
	$container.on('click', '.ps-js-reaction-title-text', function () {
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

		url = 'adminConfigreactions.update';
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
					$('#reaction-' + $input.data('parent-id') + '-default-title-notice').fadeOut();
				}

				$editor.hide();
				$label.show();
				$input.data('original-value', params.value);
				$input.data('prop-title-is-default', false);
				$('#reaction-' + params.id + '-box-title').html(params.value);
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
			if (!this.value.trim()) {
				// $btn.attr('disabled', 'disabled');
				$btn.removeAttr('disabled');
			} else {
				$btn.removeAttr('disabled');
			}
		});

	// cycle through option
	$container.on('focus', '.ps-js-focusguard', function () {
		var $guard = $(this),
			$reactions = $guard.closest('.ps-js-options').children('.ps-js-reactionconf');

		if ($guard.data('tag') === 'last') {
			$reactions.find('input').first().focus();
		} else {
			$reactions.find('input').last().focus();
		}
	});

	// float-bar
	$(function () {
		var bar = $('.ps-settings__bar');
		bar.addClass('ps-settings__bar--static');

		$(window).on('scroll', function () {
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

	$('.btn-img-reaction').on('click', function (e) {
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
			$('#ps-reactions-icon-picker-toggle').click();

			// Get icon data to store
			var $icon_url = attachment.url;
			var $icon = 'peepsocustom-' + $icon_url;

			// Reaction ID and reaction icon handle
			var $reaction_id = $('#icon-picker-current').val();
			var $img = $('#ps-js-reaction-' + $reaction_id + '-icon');

			// Restore the contents of the box
			$img.parent().parent().parent().nextAll().slideDown('fast');
			updateToggleAllButton();
			updatereactionVisibility($reaction_id, 1);

			// Replace icon with loading gif
			$img.attr('src', 'images/wpspin_light-2x.gif');

			// Send AJAX call
			var params = {
				id: $reaction_id,
				prop: 'post_excerpt',
				value: $icon
			};

			peepso.postJson('adminConfigreactions.update', params, function (response) {
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
