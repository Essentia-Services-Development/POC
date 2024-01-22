import $ from 'jquery';
import { ajax, observer } from 'peepso';
import { rest_url } from 'peepsodata';

function getHtml(id) {
	return new Promise((resolve, reject) => {
		let endpoint = `${rest_url}post_options`;
		let params = { post_id: id };

		ajax.get(endpoint, params, -1)
			.done(json => resolve(json))
			.fail(reject);
	});
}

function initOptions(element, id) {
	let $options = $(element);

	$options.on('click.ps-fetch-options', function () {
		$options.off('click.ps-fetch-options');
		getHtml(id).then(json => {
			let optionsHtml = '';

			// Render the options.
			if (json && json.options instanceof Object) {
				for (let i in json.options) {
					let option = json.options[i];

					// Separator.
					if (option === null) {
						optionsHtml += '__spacer__';
					} else {
						let optClass = option['li-class'] ? ` class="${option['li-class']}"` : '';
						let optExtra = option.extra ? ` ${option.extra}` : '';
						let optClick = '';
						if (option.click) {
							optClick = option.click
								.replace(/\s*return\s+false\s*(;\s*)?$/, '')
								.replace(/\s*;$/, '')
								.replace(/"/g, '&quot;');
							optClick = optClick ? ` onclick="${optClick}; return false"` : '';
						}

						optionsHtml += `<a href="#"${optClass}${optExtra}${optClick}>
							<i class="${option.icon}"></i><span>${option.label}</span>
						</a>`;
					}
				}
			}

			// Removes leading, trailing, and sequential separator placeholders.
			optionsHtml = optionsHtml
				.replace(/^(__spacer__)+/, '')
				.replace(/(__spacer__)+$/, '')
				.replace(/(__spacer__){2,}/, '__spacer__');

			// Replace separator placeholders with actual html.
			optionsHtml = optionsHtml.replace(
				/__spacer__/g,
				`<span class="ps-post__options-sep"></span>`
			);

			// Render the dropdown.
			if (optionsHtml) {
				$options.find('.ps-js-dropdown-menu').html(optionsHtml);

				// Handle toggle child options.
				$options.find('a.child').hide();
				$options.find('a.parent').on('click', function (e) {
					e.preventDefault();
					e.stopPropagation();

					let $parent = $(this);
					let $children = $parent.nextUntil(':not(.child)');
					$children.toggle();
				});
			}
		});
	});
}

function initPost(postElement) {
	let options = postElement.querySelector('.ps-js-post-options');
	if (options) {
		let id = +options.getAttribute('data-id');
		if (id) {
			options.removeAttribute('data-id');
			initOptions(options, id);
		}
	}
}

function init() {
	// Initialize on each activity item added.
	observer.addFilter(
		'peepso_activity',
		$posts =>
			$posts.map(function () {
				if (this.nodeType === 1) {
					initPost(this);
				}
				return this;
			}),
		10,
		1
	);

	// Initialize activity actions.
	observer.addAction(
		'peepso_activity_actions',
		$actions => {
			$actions.map(function () {
				if (this.nodeType === 1) {
					initPost(this);
				}
				return this;
			});
		},
		10,
		1
	);
}

export default { init };
