(function($) {
	var $container = $('.ps-js-group-categories-container');

	function setProp(propType, params, callback) {
		var propTypes = ['prop', 'meta'];
		if (-1 === propTypes.indexOf(propType)) {
			propType = propTypes[0];
		}

		var url = 'groupCategoryAjax.set_' + propType;
		peepso.postJson(url, params, function(json) {
			if ('function' === typeof callback) {
				callback(json);
			}
		});
	}

	function setPropByElement(elem, value, callback) {
		var $elem = $(elem),
			$field = $elem.closest('.ps-js-fieldconf'),
			$progress = $field.find('.ps-js-progress'),
			propType = $elem.data('prop-type'),
			params = {
				id: $elem.data('parent-id') || undefined,
				prop: $elem.data('prop-name') || undefined,
				key: $elem.data('prop-key') || undefined
			};

		// Assume a callback if second parameter is a function.
		if ('function' === typeof value) {
			callback = value;
			value = undefined;
		}

		// Get value from the element if it is not provided.
		if ('undefined' === typeof value) {
			if ('checkbox' !== $elem.attr('type')) {
				value = elem.checked ? $elem.val() : $elem.data('disabled-value');
			} else {
				value = $elem.val();
			}
		}

		// Set the value parameter.
		params.value = value;

		// Disable element and show loading indicator.
		$elem.attr('readonly', 'readonly');
		$progress.find('img').show();
		$progress
			.find('i')
			.stop()
			.hide();

		setProp(propType, params, function(json) {
			// Re-enable element and hide loading indicator.
			$elem.removeAttr('readonly');
			$progress.find('img').hide();

			if (json.success) {
				// Show check icon when success.
				$progress
					.find('i')
					.show()
					.delay(800)
					.fadeOut();

				var value = params.value;
				if (json.data && typeof json.data.value !== 'undefined') {
					value = json.data.value;
				}

				// Update element's original-value.
				$elem.data('original-value', value);
			}

			if ('function' === typeof callback) {
				callback(json);
			}
		});
	}

	// checkbox handler
	$container.on(
		'click',
		'input[type=checkbox]',
		_.throttle(function() {
			var $cbx = $(this),
				$progress = $cbx.closest('.ps-js-fieldconf').find('.ps-js-progress'),
				checked = $cbx[0].checked,
				updatevalue = false,
				url,
				params;

			url = 'groupCategoryAjax.set_' + ($cbx.data('prop-type') === 'meta' ? 'meta' : 'prop');
			params = {
				id: $cbx.data('parent-id') || undefined,
				prop: $cbx.data('prop-name') || undefined,
				key: $cbx.data('prop-key') || undefined,
				value: checked ? $cbx.val() : $cbx.data('disabled-value')
			};

			$progress
				.find('i')
				.stop()
				.hide();
			$progress.find('img').show();
			$cbx.attr('readonly', 'readonly');

			peepso.postJson(url, params, function(json) {
				$progress.find('img').hide();
				$progress
					.find('i')
					.show()
					.delay(800)
					.fadeOut();
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
						var $mark = $('#group-category-' + params.id + '-required-mark');
						if (checked) {
							$mark.removeClass('hidden');
						} else {
							$mark.addClass('hidden');
						}
					} else if (params.prop === 'published') {
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

	// input text and textarea handler
	(function($container) {
		var inputSelector = [
			'.ps-js-group-category input[type=text][data-prop-name]',
			'.ps-js-group-category textarea[data-prop-name]'
		].join(', ');

		$container.on('input', inputSelector, function() {
			var $ct = $(this).closest('.ps-js-fieldconf'),
				$btns = $ct.find('.ps-js-btn');

			$btns.show();
		});

		$container.on('keydown', inputSelector, function(e) {
			var $ct, $btn;

			// Trigger save on keydown only on input[text] element.
			if (13 === e.keyCode && 'INPUT' === e.target.tagName) {
				e.preventDefault();
				e.stopPropagation();

				$ct = $(this).closest('.ps-js-fieldconf');
				$btn = $ct.find('.ps-js-save');
				$btn.click();
			}
		});

		$container.on('click', '.ps-js-group-category .ps-js-cancel', function() {
			var $ct = $(this).closest('.ps-js-fieldconf'),
				$btns = $ct.find('.ps-js-btn'),
				$input = $ct.find('input[type=text][data-prop-name], textarea[data-prop-name]');

			$btns.hide();
			$input.val($input.data('original-value'));
		});

		$container.on('click', '.ps-js-group-category .ps-js-save', function() {
			var $ct = $(this).closest('.ps-js-fieldconf'),
				$btns = $ct.find('.ps-js-btn'),
				$input = $ct.find('input[type=text][data-prop-name], textarea[data-prop-name]'),
				$progress = $ct.find('.ps-js-progress'),
				url,
				params;

			url =
				'groupCategoryAjax.set_' + ($input.data('prop-type') === 'meta' ? 'meta' : 'prop');

			params = {
				id: $input.data('parent-id') || undefined,
				prop: $input.data('prop-name') || undefined,
				key: $input.data('prop-key') || undefined,
				value: $input.val()
			};

			$progress.find('img').show();
			$input.attr('readonly', 'readonly');

			peepso.postJson(url, params, function(json) {
				$progress.find('img').hide();
				$progress
					.find('i')
					.show()
					.delay(800)
					.fadeOut();
				$input.removeAttr('readonly');

				if (json.success) {
					$btns.hide();

					var value = params.value;
					if (json.data && typeof json.data.value !== 'undefined') {
						value = json.data.value;
						$input.val(value);
					}

					$input.data('original-value', value);
				} else {
					// TODO
				}
			});
		});

		// Set initial value.
		$container.find(inputSelector).each(function() {
			$(this).data('original-value', this.value);
		});
	})($container);

	// generate slug button
	$container.on('click', '.ps-js-group-category .ps-js-generate-slug', function(e) {
		e.preventDefault();
		e.stopPropagation();

		var $field = $(this).closest('.ps-js-fieldconf'),
			$input = $field.find('input[data-prop-name=slug]'),
			$btns = $field.find('.ps-js-btn'),
			$title = $field
				.closest('.ps-postbox--settings')
				.find('.ps-postbox__title input[data-prop-name]'),
			value = $title.val();

		setPropByElement($input[0], value, function(json) {
			if (json.success) {
				if (json.data && typeof json.data.value !== 'undefined') {
					value = json.data.value;
				}

				$btns.hide();
				$input.val(value);
			}
		});
	});

	// drag n' drop functionality
	$container.sortable({
		handle: '.ps-js-handle',
		update: _.throttle(function() {
			var fields = [];
			$('.ps-js-group-categories-container .postbox').each(function() {
				fields.push($(this).data('id'));
			});

			peepso.postJson(
				'groupCategoryAjax.set_order',
				{ group_category: JSON.stringify(fields) },
				function(json) {}
			);
		}, 3000)
	});

	// toggle a field
	$container.on('click', '.ps-js-group-category-toggle', function() {
		var $btn = $(this),
			$el = $btn.closest('.postbox'),
			$field = $el.find('.ps-js-group-category'),
			id = $el.data('id');

		if ($field.is(':visible')) {
			$field.slideUp('fast', function() {
				$btn.removeClass('fa-compress').addClass('fa-expand');
				updateToggleAllButton();
				updateFieldVisibility(id, 0);
			});
		} else {
			$field.slideDown('fast', function() {
				$btn.removeClass('fa-expand').addClass('fa-compress');
				updateToggleAllButton();
				updateFieldVisibility(id, 1);
			});
		}
	});

	// toggle a field
	$container.on('click', '.ps-js-group-category-title', function(e) {
		if (e.target === e.currentTarget) {
			$(this)
				.closest('.ps-postbox__title')
				.find('.ps-js-group-category-toggle')
				.click();
		}
	});

	// toggle drag-n-drop cursor
	var mousedownTimer;
	$container
		.on('mousedown', '.ps-postbox__title', function(e) {
			var $this = $(e.currentTarget);
			mousedownTimer = setTimeout(function() {
				$this.addClass('ps-js-mousedown');
			}, 200);
		})
		.on('mouseup mouseleave', '.ps-postbox__title', function(e) {
			clearTimeout(mousedownTimer);
			$(e.currentTarget).removeClass('ps-js-mousedown');
		});

	// toggle expand all group categories
	$('.ps-js-group-categories-expand-all').on('click', function() {
		$container.find('.ps-js-group-category').slideDown('fast', function() {
			toggleAllCallback(1);
		});
	});

	// toggle collapse all group categories
	$('.ps-js-group-categories-collapse-all').on('click', function() {
		$container.find('.ps-js-group-category').slideUp('fast', function() {
			toggleAllCallback(0);
		});
	});

	var toggleAllCallback = _.debounce(function(status) {
		var $fields = $('.ps-js-group-categories-container').children('.postbox');

		if (status === 0) {
			$fields
				.find('.ps-js-group-category-toggle')
				.removeClass('fa-compress')
				.addClass('fa-expand');
		} else {
			$fields
				.find('.ps-js-group-category-toggle')
				.removeClass('fa-expand')
				.addClass('fa-compress');
		}

		updateToggleAllButton(status);
		updateFieldVisibility('all', status);
	}, 200);

	function updateToggleAllButton(status) {
		var $btn = $('.ps-js-group-category-toggle-all'),
			$icon = $btn.find('span').first(),
			$label = $btn.find('span').last(),
			len,
			visible;

		if (typeof status === 'undefined') {
			len = 0;
			visible = 0;
			status = 0;
			$('.ps-js-group-categories-container')
				.find('.ps-js-group-category')
				.each(function() {
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

	var updateFieldXHR = {};
	var updateFieldVisibility = _.debounce(function(id, status) {
		var ids = [];
		if (id !== 'all') {
			ids = [id];
		} else {
			$('.ps-js-group-categories-container')
				.children('.postbox')
				.each(function() {
					ids.push($(this).data('id'));
				});
		}

		updateFieldXHR[id] && updateFieldXHR[id].ret && updateFieldXHR[id].ret.abort();
		updateFieldXHR[id] = peepso.postJson(
			'groupCategoryAjax.set_admin_box_status',
			{ id: JSON.stringify(ids), status: status },
			function(json) {
				// Do nothing
			}
		);
	}, 500);

	// add new field button
	$('.ps-js-group-categories-new').on('click', function() {
		peepso.postJson('groupCategoryAjax.create', {}, function(response) {
			if (response.success) {
				var $item = $(response.data.html);
				$container.append($item);
				peepso.util.scrollIntoView($item); // scroll item if needed
			}
		});
	});

	// edit title handler
	$container.on('click', '.ps-postbox__title .fa-edit', function() {
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
	$container.on('click', '.ps-js-group-category-title-text', function() {
		var $ct = $(this).closest('.ps-postbox__title'),
			$btn = $ct.find('.fa-edit');
		$btn.click();
	});

	// cancel edit title handler
	$container.on('click', '.ps-postbox__title .ps-js-cancel', function() {
		var $ct = $(this).closest('.ps-postbox__title'),
			$label = $ct.find('.ps-postbox__title-label'),
			$editor = $ct.find('.ps-postbox__title-editor'),
			$input = $editor.find('input[type=text]');

		$input.val($input.data('original-value'));
		$editor.hide();
		$label.show();
	});

	// save edit title handler
	$container.on('click', '.ps-postbox__title .ps-js-save', function() {
		var $ct = $(this).closest('.ps-postbox__title'),
			$label = $ct.find('.ps-postbox__title-label'),
			$editor = $ct.find('.ps-postbox__title-editor'),
			$input = $editor.find('input[type=text]'),
			$progress = $ct.find('.ps-js-progress'),
			url,
			params;

		url = 'groupCategoryAjax.set_' + ($input.data('prop-type') === 'meta' ? 'meta' : 'prop');
		params = {
			id: $input.data('parent-id') || undefined,
			prop: $input.data('prop-name') || undefined,
			key: $input.data('prop-key') || undefined,
			value: $input.val()
		};

		$progress.find('img').show();
		$input.attr('readonly', 'readonly');

		peepso.postJson(url, params, function(json) {
			$progress.find('img').hide();
			$progress
				.find('i')
				.show()
				.delay(800)
				.fadeOut();
			$input.removeAttr('readonly');

			if (json.success) {
				$editor.hide();
				$label.show();
				$input.data('original-value', params.value);
				$input.data('prop-title-is-default', false);
				$('#group-category-' + params.id + '-box-title').html(params.value);
			} else {
				// TODO
			}
		});
	});

	// save edit title handler on enter
	$container
		.on('keydown', '.ps-postbox__title input[type=text]', function(e) {
			var $btn;
			if (e.keyCode === 13) {
				e.preventDefault();
				e.stopPropagation();
				$btn = $(this).nextAll('.ps-js-save');
				$btn.click();
			}
		})
		.on('input', '.ps-postbox__title input[type=text]', function(e) {
			var $btn = $(this).nextAll('.ps-js-save');
			if (!$.trim(this.value)) {
				$btn.attr('disabled', 'disabled');
			} else {
				$btn.removeAttr('disabled');
			}
		});

	// cycle through option
	$container.on('focus', '.ps-js-focusguard', function() {
		var $guard = $(this),
			$fields = $guard.closest('.ps-js-options').children('.ps-js-fieldconf');

		if ($guard.data('tag') === 'last') {
			$fields
				.find('input')
				.first()
				.focus();
		} else {
			$fields
				.find('input')
				.last()
				.focus();
		}
	});

	// handle delete category button
	$container.on('click', '.ps-js-group-category-delete', function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('Are you sure want to delete this category?')) {
			var id = $(this).data('id');
			peepso.postJson('groupCategoryAjax.delete', { id: id }, function(response) {
				if (response.success) {
					var $el = $container.children('[data-id=' + id + ']');
					$el.slideUp('fast', function() {
						$el.remove();
					});
				}
			});
		}
	});

	// float-bar
	$(function() {
		var bar = $('.ps-settings__bar');
		bar.addClass('ps-settings__bar--static');

		$(window).scroll(function() {
			var bar = $('.ps-settings__bar');
			var scrollVal = $(this).scrollTop();
			if (scrollVal > 50) {
				bar.removeClass('ps-settings__bar--static');
			} else {
				bar.addClass('ps-settings__bar--static');
			}
		});
	});
})(jQuery);
