(function () {
	'use strict';

	var forms = document.querySelectorAll('.mailster-ajax-form');

	Array.prototype.forEach.call(forms, function (form, i) {
		form.addEventListener('submit', function (event) {
			event.preventDefault();

			var data = serializeForm(form),
				c,
				info = form.querySelector('.mailster-form-info');

			if (!info) {
				info = document.createElement('div');
				info.classList.add('mailster-form-info');
			}

			if ('function' === typeof window.mailster_pre_submit) {
				c = window.mailster_pre_submit.call(this, data);
				if (c === false) return false;
				if (typeof c !== 'undefined') data = c;
			}

			form.classList.add('loading');
			form.setAttribute('disabled', true);

			fetch(form.getAttribute('action'), {
				method: 'POST',
				headers: {
					'x-requested-with': 'XMLHttpRequest', // backwards compatibility
					'Content-Type':
						'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: data,
			})
				.then(function (response) {
					if (response.ok) {
						return response.json();
					}
					return Promise.reject(response);
				})
				.then(handlerResponse)
				.catch(function (error) {
					var response;
					try {
						response = JSON.parse(error);
						if (!response.data.html) {
							response = {
								data: {
									html:
										'There was an error with the response:<br><code>[' +
										response.data.code +
										'] ' +
										response.data.message +
										'</code>',
								},
								success: false,
							};
						}
					} catch (err) {
						response = {
							data: {
								html:
									'There was an error while parsing the response:<br><code>' +
									err +
									'</code>',
							},
							success: false,
						};
					}
					handlerResponse(response);
				});

			function handlerResponse(response) {
				form.classList.remove('loading');
				form.classList.remove('has-errors');
				form.removeAttribute('disabled');
				form.querySelector('.submit-button').removeAttribute(
					'disabled'
				);

				[].forEach.call(
					document.querySelectorAll('div.mailster-wrapper'),
					function (wrapper) {
						wrapper.classList.remove('error');
					}
				);

				info.remove();
				info.classList.remove('error');
				info.classList.remove('success');

				if ('function' === typeof window.mailster_post_submit) {
					c = window.mailster_post_submit.call(form[0], response);
					if (c === false) return false;
					if (typeof c !== 'undefined') response = c;
				}

				if (response.data.html) {
					info.innerHTML = response.data.html;
				}

				if (
					(window.pageYOffset || document.documentElement.scrollTop) <
					form.getBoundingClientRect().top
				) {
					form.insertBefore(info, form.firstChild);
				} else {
					form.insertBefore(info, form.lastChild);
				}

				if (response.success) {
					// for css transition use timeout
					setTimeout(function () {
						info.classList.add('success');
					}, 0);

					if (response.data.redirect) {
						window.location.href = response.data.redirect;
						return;
					}

					if (!form.classList.contains('is-profile')) {
						form.classList.add('completed');
						form.reset();
					}
				} else {
					if (response.data.fields) {
						form.classList.add('has-errors');
						Object.keys(response.data.fields).forEach(function (
							fieldid
						) {
							var field = form.querySelector(
								'.mailster-' + fieldid + '-wrapper'
							);
							field && field.classList.add('error');
						});
					}
					// for css transition use timeout
					setTimeout(function () {
						info.classList.add('error');
					}, 0);
				}
			}
		});
	});

	function serializeForm(form) {
		var obj = {};
		var formData = new FormData(form);
		for (var key of formData.keys()) {
			obj[key] = formData.getAll(key).slice(-1)[0]; // get the latest element from that array
		}

		return Object.keys(obj)
			.map(function (k) {
				return encodeURIComponent(k) + '=' + encodeURIComponent(obj[k]);
			})
			.join('&');
	}
})();
