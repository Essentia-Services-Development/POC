import $ from 'jquery';
import { observer } from 'peepso';

const PostboxDropdown = observer.applyFilters('class_postbox_dropdown', 10, 1);

class PostboxType extends PostboxDropdown {
	/**
	 * Initialize postbox type selector dropdown.
	 *
	 * @param {JQuery} $postbox
	 */
	constructor($postbox) {
		super($postbox.find('#type-tab')[0]);

		// Override this.$postbox value for now because of missing properties and methods required for various actions.
		// TODO: Do not override.
		this.$postbox = $postbox;

		this.defaultIcon = this.$toggle
			.children('a')
			.find('i')
			.attr('class');
		this.defaultText = this.$toggle
			.children('a')
			.find('span')
			.html();

		this.$dropdown.on('click', '[data-option-value]', e => {
			let type = $(e.currentTarget).data('optionValue');

			e.stopPropagation();
			this.select(type);
			this.hide();
		});

		this.$postbox.on('postbox.post_saved', () => {
			let customHandler = observer.applyFilters(
				'peepso_postbox_onsave',
				false,
				this.$postbox
			);
			if ('function' !== typeof customHandler) {
				this.select('status');
			}
		});

		this.select('status');
	}

	/**
	 * Show the dropdown.
	 */
	show() {
		this.$dropdown.show();

		// Add autohide on document-click.
		setTimeout(() => {
			$(document)
				.off(`mouseup.${this.id}`)
				.on(`mouseup.${this.id}`, e => {
					if (this.$container.has(e.target).length === 0) {
						this.hide();
					}
				});
		}, 1);
	}

	/**
	 * Hide the dropdown.
	 */
	hide() {
		this.$dropdown.hide();
		$(document).off(`mouseup.${this.id}`);
	}

	/**
	 * Change the post type.
	 *
	 * @param {string} type
	 */
	select(type) {
		let $source = this.$dropdown.find( `[data-option-value="${ type }"]` ),
			$target = this.$toggle.children( 'a' ),
			$sourceIcon = $source.find( 'i' ),
			sourceSelector  = $sourceIcon.attr( 'class' ).replace(/^.*(gci-[^\s]+).*$/, '$1'),
			$targetIcon = $target.find( 'i' ).filter( `.${ sourceSelector }` );

		if ( $source.length ) {
			// Update active item.
			$source.addClass( 'active' );
			$source.siblings( '.active' ).removeClass( 'active' );
			$targetIcon.addClass( 'active' );
			$targetIcon.siblings( '.active' ).removeClass( 'active' ).css('color', '');

			// Trigger related action hooks.
			observer.doAction( 'postbox_type_set', this.$postbox, type );
		}
	}
}

// Postbox action hook on the main postbox.
observer.addAction(
	'peepso_postbox_addons',
	addons => {
		let wrapper = {
			init() {},
			set_postbox($postbox) {
				if ($postbox.find('#type-tab').length) {
					new PostboxType($postbox);
				}
			}
		};
		addons.push(wrapper);
		return addons;
	},
	10,
	1
);

// Postbox action hook on edit post.
observer.addAction(
	'postbox_init',
	postbox => {
		// Users should not be able to edit post type when editing a post.
		let $postbox = postbox.$el;
		$postbox.find('#type-tab').remove();
	},
	10,
	1
);
