mailster = (function (mailster, $, window, document) {
	'use strict';

	if (typeof mailster_updates == 'undefined') {
		return;
	}

	var $output = $('#output'),
		$error = $('#error-list'),
		finished = [],
		current,
		current_i,
		skip = $(
			'<span>&nbsp;</span><a class="skipbutton button button-small" href title="skip this step">skip</a>'
		),
		skipit = false,
		performance = mailster_updates_performance[0] || 1,
		keys = $.map(mailster_updates, function (element, index) {
			return index;
		});

	$output.on('click', '.skipbutton', function () {
		skipit = true;
		return false;
	});

	mailster.$.document.ajaxError(function () {
		$error.append('Script paused...continues in 5 seconds...<br>');
		setTimeout(function () {
			$error.empty();
			run(current_i, true);
		}, 5000);
	});

	if (mailster_updates_options.autostart) {
		$('#mailster-update-process').show();
		run(0);
	} else {
		$('#mailster-update-info').show();
		$('#mailster-start-upgrade').one('click', function () {
			$('#mailster-update-process').show();
			$('#mailster-update-info').hide();
			run(0);
		});
	}

	function run(i, nooutput) {
		if (!i) {
			window.onbeforeunload = function () {
				return 'You have to finish the update before you can use Mailster!';
			};
		}

		var id = keys[i];

		current_i = i;

		if (!(current = mailster_updates[id])) {
			finish();
			return;
		}

		if (!nooutput) output(id, '<span>' + current + '</span> ...', true, 0);

		do_update(
			id,
			function () {
				setTimeout(function () {
					run(++i);
				}, 1000);
			},
			function () {
				error();
			},
			1
		);
	}

	function do_update(id, onsuccess, onerror, round) {
		mailster.util.ajax(
			'batch_update',
			{
				id: id,
				performance: performance,
			},
			function (response) {
				if (response.data.output) textoutput(response.data.output);

				if (response.success) {
					if (skipit) {
						output(id, ' &times;', false);
						skipit = false;
						onsuccess && onsuccess();
					} else if (response.data[id]) {
						output(id, ' &#10004;', false);
						onsuccess && onsuccess();
					} else {
						output(id, '.', false, round);
						setTimeout(function () {
							do_update(id, onsuccess, onerror, ++round);
						}, 5);
					}
				} else {
					onerror && onerror();
				}
			},
			function (jqXHR, textStatus, errorThrown) {
				textoutput(jqXHR.responseText);
				alert(
					'There was an error while doing the update! Please check the textarea on the right for more info!'
				);
				error();
			}
		);
	}

	function error() {
		window.onbeforeunload = null;

		output('error', 'Error ', true);
	}

	function finish() {
		window.onbeforeunload = null;

		output(
			'finished',
			'<h3>Alright, all updates have been finished!</h3>',
			true,
			0,
			true
		);

		var queryString = window.location.search;
		var urlParams = new URLSearchParams(queryString);

		var href = urlParams.get('redirect_to')
			? urlParams.get('redirect_to')
			: 'admin.php?page=mailster_welcome';

		output(
			'finished_button',
			'<a href="' +
				href +
				'" class="button button-primary button-hero">Ok, fine!</a>',
			true,
			0,
			true
		);

		$('#mailster-post-upgrade').show();
	}

	function output(id, content, newline, round, nobox) {
		if (!$('#output_' + id).length) {
			$(
				'<div class="' +
					(nobox ? '' : 'notice notice-info inline active ' + id) +
					'" style="padding: 0.5em 6px;word-wrap: break-word;"><div id="output_' +
					id +
					'"></div></div>'
			).appendTo($output);
		}

		var el = $('#output_' + id);

		el.append(content);

		if (typeof round === 'undefined') {
			el.parent().removeClass('active');
		}

		round > 50 ? el.append(skip.show()) : skip.hide();
	}

	function textoutput(content) {
		var textarea = $('#textoutput');
		var curr_content = textarea.val();

		content = curr_content + content;

		textarea.val(mailster.util.trim(content) + '\n\n');

		textarea.scrollTop(textarea[0].scrollHeight);
	}

	return mailster;
})(mailster || {}, jQuery, window, document);
