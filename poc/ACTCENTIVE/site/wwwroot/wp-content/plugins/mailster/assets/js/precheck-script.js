mailster = (function (mailster, $, window, document) {
	'use strict';

	var api = {},
		id,
		precheck = $('.mailster-precheck'),
		$status = $('.precheck-status'),
		$score = $('.precheck-score'),
		$loader = $('#precheck-ajax-loading'),
		$authentication = $('#precheck-authentication'),
		runbtn = $('.precheck-run-btn'),
		strcturebtn = $('.precheck-toggle-structure'),
		imagebtn = $('.precheck-toggle-images'),
		$iframe = $('.mailster-preview-iframe'),
		$iframebody,
		$hx,
		$hy,
		started = 0,
		images = true,
		structure = false;

	mailster.precheck = mailster.precheck || {};

	precheck
		.on('click', '.precheck-switch', switchPane)
		.on('click', '.precheck-run-btn', initTest)
		.on('click', '.precheck-toggle-images', toggleImages)
		.on('click', '.precheck-toggle-structure', toggleStructure)
		.on('mouseenter', '.assets-table tr', highlightElement)
		.on('mouseleave', '.assets-table tr', highlightElement)
		.on('click', '.change-receiver', showSubscriberInput)
		.on('click', '#precheck-agree', agreeTerms);

	$('.precheck-subscriber')
		.on('focus', function () {
			$(this).select();
		})
		.autocomplete({
			source: function (request, response) {
				mailster.util.ajax(
					'search_subscribers',
					{
						id: mailster.campaign_id,
						term: request.term,
					},
					function (data) {
						response(data);
					},
					function (jqXHR, textStatus, errorThrown) {}
				);
			},
			appendTo: '.precheck-emailheader',
			minLength: 3,
			select: function (event, ui) {
				$('#subscriber_id').val(ui.item.id);
				loadPreview();
			},
			change: function (event, ui) {
				if (!ui.item) {
					$('#subscriber_id').val(0);
					loadPreview();
				}
			},
		});

	function status(id, append) {
		var msg = mailster.l10n.precheck[id] || id;
		$score
			.removeAttr('class')
			.addClass('precheck-score precheck-status-' + id);
		if (append) {
			$status.html($status.html() + msg);
		} else {
			$status.html(msg);
		}
	}

	function error(msg) {
		var box = $(
			'<div class="error"><p><strong>' + msg + '</strong></p></div>'
		)
			.hide()
			.appendTo($('.score-message'))
			.slideDown(200)
			.delay(200)
			.fadeIn()
			.delay(8000)
			.fadeTo(200, 0)
			.delay(1500)
			.slideUp(200, function () {
				box.remove();
			});
		mailster.error(msg);
	}

	function loader(enable) {
		$loader.css('visibility', enable ? 'visible' : 'hidden');
	}

	function switchPane() {
		var dimensions = $(this).data('dimensions');
		$('.device.desktop').width(dimensions.w).height(dimensions.h);
		$('.precheck-resize').find('.button').removeClass('active');
		$(this).addClass('active');
	}

	function initTest() {
		clear();
		loader(true);
		status('sending');
		runbtn.prop('disabled', true);
		started = 0;
		$('.precheck-status-icon').html('');

		mailster.util.ajax(
			'send_test',
			{
				precheck: true,
				subscriber_id: $('#subscriber_id').val(),
				formdata: $('#post').serialize(),
				to: $('#mailster_testmail').val(),
				content: mailster.$.content.val(),
				head: mailster.$.head.val(),
				plaintext: mailster.$.excerpt.val(),
			},
			function (response) {
				if (response.success) {
					id = response.data.id;
					setTimeout(function () {
						status('checking');
						checkTest(1);
					}, 3000);
				} else {
					error(response.data.msg);
					loader(false);
					runbtn.prop('disabled', false);
				}
			},
			function (jqXHR, textStatus, errorThrown) {
				loader(false);
				runbtn.prop('disabled', false);
			}
		);
	}

	function clear() {
		precheck.find('summary').removeAttr('class');
		precheck.find('.precheck-result').empty();
		status('ready');
	}

	function checkTest(tries) {
		if (tries > 10) {
			error(mailster.l10n.precheck.email_not_sent);
			loader(false);
			runbtn.prop('disabled', false);
			return;
		}

		mailster.util.ajax(
			'precheck',
			{
				id: id,
			},
			function (response) {
				if (response.success) {
					if (!response.data.ready) {
						setTimeout(function () {
							checkTest(++tries);
						}, 3000);
					} else {
						status('collecting');
						$('.precheck-status-icon').html(
							mailster.util.sprintf('%s of 100', 100)
						);

						$.when
							.apply($, [
								getResult('blocklist'),
								getResult('spam_report'),
								getResult('authentication'),
								getResult('message'),
								getResult('links', 'tests/links'),
								getResult('images', 'tests/images'),
							])
							.done(function (r) {
								//console.log(r);
								status('finished');
								loader(false);
								runbtn.prop('disabled', false);
							});
					}
				}
				if (response.data.error) {
					error(response.data.error);
					loader(false);
					runbtn.prop('disabled', false);
				}
			},
			function (jqXHR, textStatus, errorThrown) {
				loader(false);
				runbtn.prop('disabled', false);
			}
		);
	}

	function getResult(part, endpoint) {
		var base = $('#precheck-' + part),
			children = base.find('details'),
			child_part,
			promises = [];

		if (children.length) {
			base.find('summary').eq(0).removeAttr('class').addClass('loading');
			for (var i = 0; i < children.length; i++) {
				child_part = children[i].id.replace('precheck-', '');
				if (child_part) {
					endpoint = 'tests/' + child_part;
					promises.push(getEndpoint(child_part, endpoint));
				}
			}
		} else {
			if (!endpoint) endpoint = part;
			promises.push(getEndpoint(part, endpoint));
		}

		return $.when.apply($, promises).done(function () {
			var s,
				statuses = {
					error: 0,
					warning: 0,
					notice: 0,
					success: 0,
				};
			if (typeof arguments[1] != 'string') {
				for (i in arguments) {
					arguments[i] && statuses[arguments[i][0].status]++;
				}
				if (statuses.error) {
					s = 'error';
				} else if (statuses.warning) {
					s = 'warning';
				} else if (statuses.notice) {
					s = 'notice';
				} else {
					s = 'success';
				}
				$('#precheck-' + part)
					.find('summary')
					.eq(0)
					.removeClass('loading')
					.addClass('loaded is-' + s);
			}
		});
	}

	function getEndpoint(part, endpoint) {
		var base = $('#precheck-' + part),
			summary = base
				.find('summary')
				.eq(0)
				.removeAttr('class')
				.addClass('loading'),
			body = base.find('.precheck-result');

		return mailster.util.ajax(
			'precheck_result',
			{
				id: id,
				endpoint: endpoint,
			},
			function (response) {
				if (response.success) {
					summary
						.removeClass('loading')
						.addClass('loaded is-' + response.data.status);
					if ('error' == response.data.status) {
						//base.prop('open', true);
					}
					//summary.find('.precheck-penality').html(response.data.penalty);
					$('.precheck-status-icon').html(
						mailster.util.sprintf('%s of 100', response.data.points)
					);
					body.html(response.data.html);
				}

				if (response.data.error) {
					error(response.data.error);
					loader(false);
					runbtn.prop('disabled', false);
				}
			},
			function (jqXHR, textStatus, errorThrown) {
				loader(false);
				runbtn.prop('disabled', false);
			}
		);
	}

	function showSubscriberInput() {
		$('.precheck-to').hide();
		$('.change-receiver').hide();
		$('.precheck-to-input').show().find('input').focus().select();
	}

	function initFrame() {
		$iframebody = $iframe.contents().find('html,body');
		$hx = $iframebody.find('highlighterx');
		$hy = $iframebody.find('highlightery');
		images = true;
		mailster.trigger('enable');
	}

	function loadPreview(cb) {
		if (!mailster.editor || !mailster.editor.loaded) {
			mailster.events.push('editorLoaded', function () {
				loadPreview(cb);
			});
			return;
		}

		var args = {
				id: mailster.campaign_id,
				subscriber_id: $('#subscriber_id').val(),
				content: mailster.editor.getContent(),
				head: mailster.$.head.val(),
				issue: $('#mailster_autoresponder_issue').val(),
				subject: mailster.details.$.subject.val(),
				preheader: mailster.details.$.preheader.val(),
			},
			title = mailster.$.title.val();

		clear();
		$('.precheck-status-icon').html('');

		mailster.util.ajax(
			'set_preview',
			args,
			function (response) {
				$iframe
					.one('load', initFrame)
					.attr(
						'src',
						ajaxurl +
							'?action=mailster_get_preview&hash=' +
							response.data.hash +
							'&_wpnonce=' +
							response.data.nonce
					);
				imagebtn.addClass('active');
				strcturebtn.removeClass('active');
				mailster.util.tb_show(
					title
						? mailster.util.sprintf(
								mailster.l10n.campaigns.precheck,
								'"' + title + '"'
						  )
						: mailster.l10n.campaigns.preview,
					'#TB_inline?hash=' +
						response.data.hash +
						'&_wpnonce=' +
						response.data.nonce +
						'&width=' +
						Math.min(1440, mailster.$.window.width() - 50) +
						'&height=' +
						(mailster.$.window.height() - 100) +
						'&inlineId=mailster_precheck_wrap',
					null
				);
				$('.precheck-subject').html(response.data.subject);
				$('.precheck-subscriber').val(response.data.to);
				$('.precheck-to').text(response.data.to);
				cb && cb();
			},
			function (jqXHR, textStatus, errorThrown) {
				mailster.trigger('enable');
			}
		);
	}

	function toggleStructure() {
		if (structure) {
			$iframebody.removeClass('precheck-structure-enabled');
		} else {
			$iframebody.addClass('precheck-structure-enabled');
		}
		strcturebtn.toggleClass('active');
		structure = !structure;
	}

	function toggleImages() {
		var img = $iframebody.find('img');

		if (!images) {
			$iframebody.removeClass('precheck-images-hidden');
			$.each(img, function (i, e) {
				$(e).attr('src', $(e).attr('data-src')).removeAttr('data-src');
			});
		} else {
			$iframebody.addClass('precheck-images-hidden');
			$.each(img, function (i, e) {
				$(e).attr('data-src', $(e).attr('src')).attr('src', '');
			});
		}
		imagebtn.toggleClass('active');
		images = !images;
	}

	function highlightElement(event) {
		var t = $(this),
			d = t.data(),
			el,
			type = event.type;

		if (!d.el) {
			var url = d.url,
				tag = d.tag,
				attr = d.attr,
				index = d.index,
				el = $iframe
					.contents()
					.find(tag + '[' + attr + '="' + url + '"]')[index];
			t.data('el', el);
		} else {
			el = d.el;
		}
		if (!el) {
			return;
		}
		if ('mouseleave' == type) {
			$iframebody.removeClass('precheck-highlighter');
			$(el).removeClass('precheck-highlighted');
			return;
		} else {
			$(el).addClass('precheck-highlighted');
			$iframebody.addClass('precheck-highlighter');
		}

		el.scrollIntoView({
			behavior: 'smooth',
			block: 'center',
		});

		var rect = el.getBoundingClientRect();

		$hx.css({
			transform:
				'translate(' +
				rect.x +
				'px, ' +
				(rect.y + $iframebody.scrollTop()) +
				'px)',
			width: rect.width,
			height: rect.height,
		});
		$hy.css({
			transform:
				'translate(' +
				rect.x +
				'px, ' +
				(rect.y + $iframebody.scrollTop()) +
				'px)',
			width: rect.width,
			height: rect.height,
		});
	}

	function agreeTerms() {
		if (!$('#precheck-agree-checkbox').is(':checked')) {
			alert(mailster.l10n.campaigns.agree_precheck_terms);
			return false;
		}
		mailster.util.ajax('precheck_agree', function (response) {
			precheck.addClass('precheck-terms-agreed');
			mailster.precheck.terms_accepted = true;
		});
	}

	mailster.precheck.open = function (cb) {
		mailster.trigger('save');
		mailster.trigger('disable');

		$('.precheck-from').html($('#mailster_from-name').val());
		loadPreview(cb);
	};

	mailster.precheck.start = initTest;
	mailster.precheck.terms_accepted = !!$('.precheck-terms-agreed').length;

	return mailster;
})(mailster || {}, jQuery, window, document);
