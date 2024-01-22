mailster = (function (mailster, $, window, document) {
	'use strict';

	var uploader,
		uploadinfo = $('.uploadinfo'),
		importstatus = $('.status'),
		importstarttime,
		importidentifier,
		importpaused = false,
		importcanceled = false,
		importoptions = {},
		importstep = 0,
		importpercentage = 0,
		newCustomFields = {};

	mailster.$.document
		.on('submit', '.importer-form', function () {
			var form = $(this);
			var data = form.serialize();
			var type = form.data('type');
			importstatus = form.find('.status');

			form.prop('readonly', true).addClass('loading');

			importstatus
				.addClass('progress spinner')
				.html(mailster.l10n.manage.prepare_import);

			mailster.util.ajax(
				'import_handler',
				{
					type: type,
					data: data,
				},
				function (response) {
					if (response.success) {
						if (response.data.html) {
							form.html(response.data.html);
						}
						if (response.data.identifier) {
							importidentifier = response.data.identifier;
							get_import_data();
						}
					} else {
						importstatus
							.html(response.data.msg)
							.removeClass('spinner');
					}
					form.prop('readonly', false)
						.css('opacity', 1)
						.removeClass('loading');
				},
				function () {
					form.prop('readonly', false)
						.css('opacity', 1)
						.removeClass('loading');
				}
			);

			return false;
		})
		.on('submit', '.importer-quickinstall-form', function () {
			var form = $(this);
			var data = form.serialize();
			var slug = form.data('slug');
			var id = form.data('id');

			form.prop('readonly', true).addClass('loading');

			quickInstall(id, slug, 'install');

			return false;
		})
		.on('change', '.column-selector', function () {
			if ('_new' == $(this).val()) {
				var name = prompt(
					mailster.l10n.manage.define_custom_field,
					$(this).data('for') || mailster.l10n.manage.my_custom_field
				);

				if (!name) return false;

				var id = addCustomField(name);

				$(this).val(id);
			}
		})
		.on('click', '#addlist', function () {
			var val = $('#new_list_name').val();
			if (!val) {
				return false;
			}

			$(
				mailster.util.sprintf(
					'<li><label><input name="_lists[]" value="%s" type="checkbox" checked> %s </label></li>',
					val,
					val
				)
			).appendTo('#section-lists > ul');
			$('#new_list_name').val('');
			return false;
		})
		.on('change', '#signup', function () {
			$('#signupdate').prop('disabled', !$(this).is(':checked'));
		})
		.on('change', '.wordpress-user-roles .list-toggle', function () {
			$('.no-role-cb').prop('checked', false);
		})
		.on('change', 'ul.roles input', function () {
			$('.no-role-cb').prop('checked', false);
		})
		.on('change', '.no-role-cb', function () {
			$(this)
				.parent()
				.parent()
				.parent()
				.parent()
				.find('ul.roles input')
				.add('.wordpress-user-roles .list-toggle')
				.prop('checked', false);
		})
		.on('click', '.do-import', function () {
			var data = $('#subscriber-table').serialize();

			if (!/%5D=email/.test(data)) {
				alert(mailster.l10n.manage.select_emailcolumn);
				return false;
			}
			if (!$('input[name="status"]:checked').length) {
				alert(mailster.l10n.manage.select_status);
				return false;
			}

			if (!confirm(mailster.l10n.manage.confirm_import)) return false;

			var _this = $(this).prop('disabled', true),
				status = $('input[name="status"]:checked').val(),
				loader = $('#import-ajax-loading').css({
					display: 'inline-block',
				}),
				identifier = $('#identifier').val(),
				performance = $('#performance').is(':checked');

			importstarttime = new Date();

			$('.import-process-wrap').show();

			importoptions = {
				identifier: identifier,
				data: data,
				status: status,
				performance: performance,
				customfields: newCustomFields,
			};

			do_import();

			$(this).prop('disabled', false);

			window.onbeforeunload = function () {
				return mailster.l10n.manage.onbeforeunloadimport;
			};
			return false;
		})
		.on('click', '.pause-import', function () {
			$('.import-process').addClass('paused');
			importpaused = true;
			return false;
		})
		.on('click', '.resume-import', function () {
			$('.import-process').removeClass('paused');
			importpaused = false;
			do_import();
			return false;
		})
		.on('click', '.cancel-import', function () {
			if (!confirm(mailster.l10n.manage.cancel_import)) return false;
			importcanceled = true;
			if (importpaused) {
				importpaused = false;
				do_import();
			}
			return false;
		});

	typeof wpUploaderInit == 'object' &&
		mailster.events.push('documentReady', function () {
			uploader = new plupload.Uploader(wpUploaderInit);

			uploader.bind('Init', function (up) {
				var uploaddiv = $('#plupload-upload-ui');

				if (
					up.features.dragdrop &&
					!$(document.body).hasClass('mobile')
				) {
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

				if (up.runtime == 'html4') {
					$('.upload-flash-bypass').hide();
				}
			});

			uploader.bind('FilesAdded', function (up, files) {
				$('#media-upload-error').html('');
				$('#wordpress-users').fadeOut();

				setTimeout(function () {
					up.refresh();
					up.start();
				}, 1);
			});

			uploader.bind('BeforeUpload', function (up, file) {
				uploadinfo.html('uploading');
			});

			uploader.bind('UploadFile', function (up, file) {});

			uploader.bind('UploadProgress', function (up, file) {
				uploadinfo.html(
					mailster.util.sprintf(
						mailster.l10n.manage.uploading,
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
					importidentifier = response.data.identifier;
				} else {
					uploadinfo.html(response.data.message);
					up.refresh();
				}
			});

			uploader.bind('UploadComplete', function (up, files) {
				if (importidentifier) {
					uploadinfo.html(mailster.l10n.manage.prepare_data);
					get_import_data();
				}
			});

			uploader.init();
		});

	function do_import() {
		var percentage = importpercentage,
			p_diff,
			p_delta,
			p_abs1,
			p_abs2,
			finished,
			bar = $('.import-process').find('.bar'),
			$p = $('.import-percentage'),
			t = new Date().getTime();

		if (importpaused) {
			return false;
		}

		if (!importstep) {
			get_stats(0, 0, 0, 0, 0);
			bar.width(0);
		}

		mailster.util.ajax(
			'do_import',
			{
				id: importstep,
				options: importoptions,
				canceled: importcanceled,
			},
			function (response) {
				if (response.success) {
					percentage = response.data.p_total * 100;
					finished =
						response.data.p_total >= 1 || response.data.canceled;

					p_diff = percentage - importpercentage;
					p_delta = importpercentage;
					importpercentage = percentage;

					get_stats(
						response.data.f_imported,
						response.data.f_errors,
						response.data.f_total,
						percentage,
						response.data.memoryusage
					);

					bar.stop(true, true).animate(
						{ width: percentage + '%' },
						{
							duration: new Date().getTime() - t,
							easing: 'linear',
							progress: function (a, p) {
								p_abs1 = Math.floor(p_delta + p_diff * p);
								if (p_abs1 != p_abs2) {
									$p.html(p_abs1 + '%');
									p_abs2 = p_abs1;
								}
							},
							complete: function () {
								if (finished) {
									window.onbeforeunload = null;
									$('.import-result').html(
										response.data.html
									);
									scroll_to_content_top();
									$('.import-process-wrap').hide();
								}
							},
						}
					);
					if (!finished) {
						++importstep;
						do_import();
					}
				} else {
					upload_error_handler(response.data.msg);
				}
			},
			function (jqXHR, textStatus, errorThrown) {
				upload_error_handler(textStatus);
			}
		);
	}

	function upload_error_handler(errormsg) {
		importstatus.removeClass('progress spinner').html(errormsg);
	}

	function scroll_to_content_top(pos) {
		window.scroll({
			top: pos || 125,
			left: 0,
			behavior: 'smooth',
		});
	}

	function get_import_data() {
		mailster.util.ajax(
			'get_import_data',
			{
				identifier: importidentifier,
			},
			function (response) {
				if (response.success) {
					scroll_to_content_top();

					$('.import-result').eq(0).html(response.data.html).show();
					$('.import-wrap').hide();

					$('input.datepicker').datepicker({
						dateFormat: 'yy-mm-dd',
						showAnim: 'fadeIn',
						onClose: function () {},
					});

					$.fn.select2 &&
						$('.tags-input').select2({
							placeholder: mailster.l10n.manage.choose_tags,
							tags: true,
							theme: 'mailster',
						});

					importstatus = $('.import-process').find('.import-status');
				}
			}
		);
	}

	function get_stats(imported, errors, total, percentage, memoryusage) {
		var timepast = new Date().getTime() - importstarttime.getTime(),
			timeleft = Math.ceil(
				((100 - percentage) * (timepast / percentage)) / 1000
			);
		var t = new Date(timeleft * 1000),
			h = t.getHours() - 1,
			m = t.getMinutes(),
			s = t.getSeconds(),
			o =
				(timeleft >= 3600 ? (h < 10 ? '0' + h : h) + ':' : '') +
				((m < 10 ? '0' + m : m) + ':') +
				(s < 10 ? '0' + s : s);

		imported &&
			$('.import-imported').html(
				mailster.util.sprintf(
					mailster.l10n.manage.import_imported,
					imported,
					total
				)
			);
		errors &&
			$('.import-errors').html(
				mailster.util.sprintf(
					mailster.l10n.manage.import_errors,
					errors
				)
			);
		imported && $('.import-memory').html(memoryusage);
		imported && $('.import-time').html(o);
	}

	function quickInstall(id, slug, action, context) {
		var el = $('#manage-import-' + id).find('.manage-import-body');

		mailster.util.ajax(
			'quick_install',
			{
				plugin: slug,
				step: action,
				context: context,
			},
			function (response) {
				if (response.success) {
					if (response.data.next) {
						quickInstall(id, slug, response.data.next, [
							'import_method',
							id,
						]);
					} else if (response.data.content) {
						el.html(response.data.content);
					}
				} else {
				}
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function addCustomField(name) {
		var id = mailster.util.sanitizeTag(name);
		$('#subscriber-table')
			.find('.custom-fields-select')
			.each(function () {
				$('<option value="' + id + '">' + name + '</option>').appendTo(
					$(this)
				);
			});

		newCustomFields[id] = name;

		return id;
	}

	return mailster;
})(mailster || {}, jQuery, window, document);
