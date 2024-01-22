(function ($, peepso, factory) {
	peepso.album = new (factory($, peepso))();
})(jQuery, peepso, function ($, peepso) {
	/**
	 * Photo album management.
	 * @class Album
	 */
	function Album() {
		this.ajax = {};
	}

	peepso.npm.objectAssign(
		Album.prototype,
		/** @lends Album.prototype */ {
			/**
			 * Show form for edit album name.
			 */
			edit_name: function (album_id, owner_id, elem) {
				var $ct = $(elem).closest('.ps-js-album-name'),
					$text = $ct.find('.ps-js-album-name-text'),
					$editor = $ct.find('.ps-js-album-name-editor'),
					$trigger = $ct.find('.ps-js-album-name-trigger'),
					$input,
					$submit,
					value;

				if ($editor.is(':visible')) {
					return;
				}

				$text.hide();
				$trigger.hide();
				$editor.show();

				$submit = $editor.find('.ps-js-submit');
				$input = $editor.find('input');
				$input.data('original-value', (value = $input.val())); // save original value
				$input.focus().val('').val(value); // focus

				$editor.off('click input');

				// handle cancel button
				$editor.on('click', '.ps-js-cancel', function () {
					$input.val(value);
					$editor.off('click').hide();
					$text.show();
					$trigger.show();
				});

				// handle save button
				$editor.on(
					'click',
					'.ps-js-submit',
					$.proxy(function (e) {
						this.save_name(album_id, owner_id, $input.val(), e.currentTarget);
					}, this)
				);

				// handle text input
				$editor.on(
					'input',
					'input',
					_.throttle(function (e) {
						var value = $.trim(e.target.value);
						if (value) {
							$submit.removeAttr('disabled');
						} else {
							$submit.attr('disabled', 'disabled');
						}
					}, 500)
				);
				$input.trigger('input');
			},

			/**
			 * Save album name.
			 */
			save_name: function (album_id, owner_id, name, elem) {
				var flag = 'save_name',
					$loading;

				if (this.ajax[flag]) {
					return;
				}

				this.ajax[flag] = true;
				name = $.trim(name);
				$loading = $(elem).find('.ps-js-loading');
				$loading.show();

				peepso.postJson(
					'photosajax.set_album_name',
					{
						album_id: album_id,
						user_id: owner_id,
						name: name,
						_wpnonce: psdata_photos_album.nonce_set_album_name
					},
					$.proxy(function (json) {
						var $ct = $(elem).closest('.ps-js-album-name'),
							$text = $ct.find('.ps-js-album-name-text'),
							$editor = $ct.find('.ps-js-album-name-editor'),
							$trigger = $ct.find('.ps-js-album-name-trigger'),
							$input = $editor.find('input');

						this.ajax[flag] = false;
						$loading.hide();

						if (json.success) {
							$input.val(name);
							$editor.off('click').hide();
							$text.text(name).show();
							$trigger.show();
						} else {
							peepso.dialog(json.errors[0], { error: true }).show().autohide();
						}
					}, this)
				);
			},

			/**
			 * Show form for edit album description.
			 */
			edit_desc: function (album_id, user_id, owner_id, elem) {
				var $ct = $(elem).closest('.ps-js-album-desc'),
					$text = $ct.find('.ps-js-album-desc-text'),
					$placeholder = $ct.find('.ps-js-album-desc-placeholder'),
					$editor = $ct.find('.ps-js-album-desc-editor'),
					$textarea,
					value;

				if ($editor.is(':visible')) {
					return;
				}

				$text.hide();
				$placeholder.hide();
				$editor.show();

				$textarea = $editor.find('textarea');
				$textarea.data('original-value', (value = $textarea.val())); // save original value
				$textarea.focus().val('').val(value); // focus

				$editor.off('click');

				// handle cancel button
				$editor.on('click', '.ps-js-cancel', function () {
					$textarea.val(value);
					$editor.off('click').hide();
					if ($.trim(value)) {
						$text.show();
						$placeholder.hide();
					} else {
						$text.hide();
						$placeholder.show();
					}
				});

				// handle save button
				$editor.on(
					'click',
					'.ps-js-submit',
					$.proxy(function (e) {
						this.save_desc(
							album_id,
							user_id,
							owner_id,
							$textarea.val(),
							e.currentTarget
						);
					}, this)
				);
			},

			/**
			 * Save album description.
			 */
			save_desc: function (album_id, user_id, owner_id, description, elem) {
				var flag = 'save_desc',
					$loading;

				if (this.ajax[flag]) {
					return;
				}

				this.ajax[flag] = true;
				description = $.trim(description);
				$loading = $(elem).find('.ps-js-loading');
				$loading.show();

				peepso.postJson(
					'photosajax.set_album_description',
					{
						album_id: album_id,
						user_id: user_id,
						owner_id: owner_id,
						description: description,
						_wpnonce: psdata_photos_album.nonce_set_album_description
					},
					$.proxy(function (json) {
						var $ct = $(elem).closest('.ps-js-album-desc'),
							$text = $ct.find('.ps-js-album-desc-text'),
							$placeholder = $ct.find('.ps-js-album-desc-placeholder'),
							$editor = $ct.find('.ps-js-album-desc-editor'),
							$textarea = $editor.find('textarea');

						this.ajax[flag] = false;
						$loading.hide();

						if (json.success) {
							$textarea.val(description);
							$editor.off('click').hide();
							if (description) {
								$text.text(description).show();
								$placeholder.hide();
							} else {
								$text.text(description).hide();
								$placeholder.show();
							}
						} else {
							peepso.dialog(json.errors[0], { error: true }).show().autohide();
						}
					}, this)
				);
			},

			/**
			 * Change album privacy.
			 */
			change_acc: function (album_id, owner_id, acc, elem) {
				peepso.postJson(
					'photosajax.set_album_access',
					{
						album_id: album_id,
						user_id: owner_id,
						acc: acc,
						_wpnonce: psdata_photos_album.nonce_set_album_access
					},
					$.proxy(function (json) {
						if (json.success) {
							// do nothing
						} else {
							peepso.dialog(json.errors[0], { error: true }).show().autohide();
						}
					}, this)
				);
			}
		}
	);

	return Album;
});
