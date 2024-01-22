window.rehubMetboxes = window.rehubMetboxes || {};

(function(window, document, $, rehub, undefined ) {
	'use strict';

	// localization strings
	var setTimeout = window.setTimeout;
	var $document;
	var $id = function( selector ) {
		return $( document.getElementById( selector ) );
	};

	rehub.$id = $id;
	var defaults = {
		idNumber        : false,
		numRows         : false,
		repeatEls       : 'input:not([type="button"],[id^=filelist]),select,textarea',
		noEmpty         : 'input:not([type="button"]):not([type="radio"]):not([type="checkbox"]),textarea,.meta_box_preview_image',
		repeatUpdate    : 'input:not([type="button"]),select,textarea,label',
		styleBreakPoint : 450,
	};

	rehub.init = function() {

		$document = $( document );

		// if ( jQuery.fn.select2 ) {
		// 	if ( $('.rehub-js-select').length ) {
		// 		$('.rehub-js-select').each( function( e ) {
		// 			$( this ).select2({allowClear: true, placeholder: 'Select Option' });
		// 		});
		// 	}
		// 	if ( $('.rehub-js-select2').length ) {
		// 		$('.rehub-js-select2').each( function( e ) {
		// 			$( this ).select2({allowClear: true, placeholder: 'Select Option', multiple: true });
		// 		});
		// 	}
		// }

		$.extend( rehub, defaults );

		var $metabox     = rehub.metabox();
		var $repeatGroup = $metabox.find('.rehub-repeatable-group');

		$metabox
			.on( 'click', '.rehub-add-group-row', rehub.addGroupRow )
			.on( 'click', '.rehub-remove-group-row', rehub.removeGroupRow )
			.on( 'rehub_remove_row', '.rehub-repeatable-group', rehub.resetTitlesAndIterator )
			.on( 'click', '.rehub-group-handle, .rehub-group-handle + .rehub-group-handle-title', rehub.toggleHandle );

		if ( $repeatGroup.length ) {
			$repeatGroup
				.on( 'rehub_add_row', rehub.emptyValue )
				.on( 'rehub_add_row', rehub.setDefaults );
		}
	};

	rehub.resetTitlesAndIterator = function( evt ) {
		if ( ! evt.group ) {
			return;
		}

		// Loop repeatable group tables
		$( '.rehub-repeatable-group.repeatable' ).each( function() {
			var $table = $( this );
			var groupTitle = $table.find( '.rehub-remove-group-row' ).data( 'grouptitle' );

			// Loop repeatable group table rows
			$table.find( '.rehub-repeatable-grouping' ).each( function( rowindex ) {
				var $row = $( this );
				var $rowTitle = $row.find( 'h3.rehub-group-title' );
				//console.log( $rowTitle.text() );
				// Reset rows iterator
				$row.data( 'iterator', rowindex );
				// Reset rows title
				if ( $rowTitle.length ) {
					// console.log( rowindex + 1 );
					$rowTitle.text( groupTitle.replace( '{#}', ( rowindex + 1 ) ) );
				}
			});
		});
	};

	rehub.toggleHandle = function( evt ) {
		evt.preventDefault();
		rehub.trigger( 'postbox-toggled', $( this ).parent('.postbox').toggleClass('closed') );
	};

	rehub.cleanRow = function( $row, prevNum, group ) {
		var $elements = $row.find( rehub.repeatUpdate );
		if ( group ) {

			var $other = $row.find( '[id]' ).not( rehub.repeatUpdate );

			// Update all elements w/ an ID
			if ( $other.length ) {
				$other.each( function() {
					var $_this = $( this );
					var oldID = $_this.attr( 'id' );
					var newID = oldID.replace( '_'+ prevNum, '_'+ rehub.idNumber );
					var $buttons = $row.find('[data-selector="'+ oldID +'"]');
					$_this.attr( 'id', newID );

					// Replace data-selector vars
					if ( $buttons.length ) {
						$buttons.attr( 'data-selector', newID ).data( 'selector', newID );
					}
				});
			}
		}

		$elements.filter( ':checked' ).removeAttr( 'checked' );
		$elements.find( ':checked' ).removeAttr( 'checked' );
		$elements.filter( ':selected' ).removeAttr( 'selected' );
		$elements.find( ':selected' ).removeAttr( 'selected', false );

		//console.log($elements);

		if ( $row.find('h3.rehub-group-title').length ) {
			$row.find( 'h3.rehub-group-title' ).text( $row.data( 'title' ).replace( '{#}', ( rehub.numRows + 2 ) ) );
		}

		$elements.each( function() {
			rehub.elReplacements( $( this ), prevNum, group );
		} );

		return rehub;
	};

	rehub.elReplacements = function( $newInput, prevNum, group ) {
		var oldFor    = $newInput.attr( 'for' );
		var oldVal    = $newInput.val();
		var type      = $newInput.prop( 'type' );
		var defVal    = rehub.getFieldArg( $newInput, 'default' );
		var newVal    = 'undefined' !== typeof defVal && false !== defVal ? defVal : '';
		var tagName   = $newInput.prop('tagName');
		var checkable = 'radio' === type || 'checkbox' === type ? oldVal : false;
		var attrs     = {};
		var newID, oldID;
		if ( oldFor ) {
			attrs = { 'for' : oldFor.replace( '_'+ prevNum, '_'+ rehub.idNumber ) };
		} else {
			var oldName = $newInput.attr( 'name' );
			var newName;
			oldID = $newInput.attr( 'id' );

			// Handle adding groups vs rows.
			if ( group ) {
				newName = oldName ? oldName.replace( '['+ prevNum +'][', '['+ rehub.idNumber +'][' ) : '';
				newID   = oldID ? oldID.replace( '_' + prevNum + '_', '_' + rehub.idNumber + '_' ) : '';
			} else {
				newName = oldName ? rehub.replaceLast( oldName, '[' + prevNum + ']', '[' + rehub.idNumber + ']' ) : '';
				newID   = oldID ? rehub.replaceLast( oldID, '_' + prevNum, '_' + rehub.idNumber ) : '';
			}

			attrs = {
				id: newID,
				name: newName
			};
		}

		// Clear out textarea values
		if ( 'TEXTAREA' === tagName ) {
			$newInput.html( newVal );
		}

		if ( 'SELECT' === tagName && 'undefined' !== typeof defVal ) {
			var $toSelect = $newInput.find( '[value="'+ defVal + '"]' );
			if ( $toSelect.length ) {
				$toSelect.attr( 'selected', 'selected' ).prop( 'selected', 'selected' );
			}
		}

		if ( checkable ) {
			$newInput.removeAttr( 'checked' );
			if ( 'undefined' !== typeof defVal && oldVal === defVal ) {
				$newInput.attr( 'checked', 'checked' ).prop( 'checked', 'checked' );
			}
		}

		if ( ! group && $newInput[0].hasAttribute( 'data-iterator' ) ) {
			attrs['data-iterator'] = rehub.idNumber;
		}

		$newInput
			.removeClass( 'hasDatepicker' )
			.val( checkable ? checkable : newVal ).attr( attrs );

		return $newInput;
	};

	rehub.newRowHousekeeping = function( $row ) {
		return rehub;
	};

	rehub.updateNameAttr = function () {
		var $this = $( this );
		var name  = $this.attr( 'name' ); // get current name

		// If name is defined
		if ( 'undefined' !== typeof name ) {
			var prevNum = parseInt( $this.parents( '.rehub-repeatable-grouping' ).data( 'iterator' ), 10 );
			var newNum  = prevNum - 1; // Subtract 1 to get new iterator number
			var $newName = name.replace( '[' + prevNum + ']', '[' + newNum + ']' );
			$this.attr( 'name', $newName );
		}
	};

	rehub.emptyValue = function( evt, row ) {
		$( rehub.noEmpty, row ).val( '' );
	};

	rehub.setDefaults = function( evt, row ) {
		$( rehub.noEmpty, row ).each( function() {
			var $el = $(this);
			var defVal = rehub.getFieldArg( $el, 'default' );
			if ( 'undefined' !== typeof defVal && false !== defVal ) {
				$el.val( defVal );
			}
		});
	};

	rehub.addGroupRow = function( evt ) {
		evt.preventDefault();

		var $this = $( this );

		rehub.triggerElement( $this, 'rehub_add_group_row_start', $this );

		var $table   = $id( $this.data('selector') );
		var $oldRow  = $table.find('.rehub-repeatable-grouping').last();
		var prevNum  = parseInt( $oldRow.data('iterator'), 10 );
		rehub.idNumber = parseInt( prevNum, 10 ) + 1;
		rehub.numRows = $oldRow.index();
		var $row     = $oldRow.clone();
		var nodeName = $row.prop('nodeName') || 'div';
		var getRowId = function( id ) {
			id = id.split('-');
			id.splice(id.length - 1, 1);
			id.push( rehub.idNumber );
			return id.join('-');
		};

		while ( $table.find( '.rehub-repeatable-grouping[data-iterator="'+ rehub.idNumber +'"]' ).length > 0 ) {
			rehub.idNumber++;
		}

		rehub.newRowHousekeeping( $row.data( 'title', $this.data( 'grouptitle' ) ) ).cleanRow( $row, prevNum, true );

		var $newRow = $( '<' + nodeName + ' id="'+ getRowId( $oldRow.attr('id') ) +'" class="postbox rehub-row rehub-repeatable-grouping" data-iterator="'+ rehub.idNumber +'">'+ $row.html() +'</' + nodeName + '>' );
		$oldRow.after( $newRow );

		rehub.triggerElement( $table, { type: 'rehub_add_row', group: true }, $newRow );
		rh_init_range_slider();
	};

	rehub.removeGroupRow = function( evt ) {
		evt.preventDefault();

		var $this        = $( this );
		var $table  = $id( $this.data('selector') );
		var $parent = $this.parents('.rehub-repeatable-grouping');
		var number  = $table.find('.rehub-repeatable-grouping').length;
		var groupRepeater = $parent.parents('.rehub-repeatable-group');
		var repeaterGroups = groupRepeater.find('.rehub-repeatable-grouping').length;
		//console.log( repeaterGroups );
		if ( repeaterGroups == 1 ) {
			window.confirm( 'You can not delete one item' );
			return;
		}

		rehub.triggerElement( $table, 'rehub_remove_group_row_start', $this );
		//console.log( $parent );
		$parent.nextAll( '.rehub-repeatable-grouping' ).find( rehub.repeatEls ).each( rehub.updateNameAttr );
		$parent.remove();

		rehub.triggerElement( $table, { type: 'rehub_remove_row', group: true } );
	};

	rehub.resetRow = function( $addNewBtn, $removeBtn ) {
		$addNewBtn.trigger( 'click' );
		$removeBtn.trigger( 'click' );
	};

	rehub.metabox = function() {
		if ( rehub.$metabox ) {
			return rehub.$metabox;
		}
		rehub.$metabox = $('.rehub-meta_factory-metabox');
		return rehub.$metabox;
	};

	rehub.trigger = function( evtName ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		args.push( rehub );
		$document.trigger( evtName, args );
	};

	rehub.triggerElement = function( $el, evtName ) {
		var args = Array.prototype.slice.call( arguments, 2 );
		args.push( rehub );
		$el.trigger( evtName, args );
	};

	rehub.getFieldArg = function( hash, arg ) {
		return rehub.getField( hash )[ arg ];
	};

	rehub.getFields = function( filterCb ) {
		if ( 'function' === typeof filterCb ) {
			var fields = [];
			$.each( l10n.fields, function( hash, field ) {
				if ( filterCb( field, hash ) ) {
					fields.push( field );
				}
			});
			return fields;
		}

		return l10n.fields;
	};

	rehub.getField = function( hash ) {
		var field = {};
		hash = hash instanceof jQuery ? hash.data( 'hash' ) : hash;
		if ( hash ) {
			try {
				if ( l10n.fields[ hash ] ) {
					throw new Error( hash );
				}

				rehub.getFields( function( field ) {
					if ( 'function' === typeof hash ) {
						if ( hash( field ) ) {
							throw new Error( field.hash );
						}
					} else  if ( field.id && field.id === hash ) {
						throw new Error( field.hash );
					}
				});
			} catch( e ) {
				field = l10n.fields[ e.message ];
			}
		}

		return field;
	};

	rehub.replaceLast = function( string, search, replace ) {
		var n = string.lastIndexOf( search );
		return string.slice( 0, n ) + string.slice( n ).replace( search, replace );
	};

	$( rehub.init );

})(window, document, jQuery, window.rehubMetboxes);

var rh_init_range_slider = () => {
	document.querySelectorAll(".rh_metabox_range").forEach(box => { 
		let slider = box.querySelector("input");
		let output = box.querySelector(".rh_metabox_range_val");
		output.innerHTML = slider.value;

		slider.oninput = function() {
		  output.innerHTML = this.value;
		}
	});
}
rh_init_range_slider();

jQuery( function( $ ) {
	if ($('#rh_post_images_container').length > 0) { 
		// Product gallery file uploads.
		var post_gallery_frame;
		var $image_gallery_ids = $( '#rh_post_image_gallery' );
		var $post_images    = $( '#rh_post_images_container' ).find( 'ul.rh_post_images' );
		
		$( '.rh_add_post_images' ).on( 'click', 'a', function( event ) {
			var $el = $( this );

			event.preventDefault();

			// If the media frame already exists, reopen it.
			if ( post_gallery_frame ) {
				post_gallery_frame.open();
				return;
			}

			// Create the media frame.
			post_gallery_frame = wp.media.frames.post_gallery = wp.media({
				// Set the title of the modal.
				title: $el.data( 'choose' ),
				button: {
					text: $el.data( 'update' )
				},
				states: [
					new wp.media.controller.Library({
						title: $el.data( 'choose' ),
						filterable: 'all',
						multiple: true
					})
				]
			});

			// When an image is selected, run a callback.
			post_gallery_frame.on( 'select', function() {
				var selection = post_gallery_frame.state().get( 'selection' );
				var attachment_ids = $image_gallery_ids.val();

				selection.map( function( attachment ) {
					attachment = attachment.toJSON();

					if ( attachment.id ) {
						attachment_ids   = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
						var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

						$post_images.append( '<li class="image" data-attachment_id="' + attachment.id + '"><img src="' + attachment_image + '" /><ul class="actions"><li><a href="#" class="delete" title="' + $el.data('delete') + '">' + $el.data('text') + '</a></li></ul></li>' );
					}
				});

				$image_gallery_ids.val( attachment_ids );
			});

			// Finally, open the modal.
			post_gallery_frame.open();
		});

		// Image ordering.
		$post_images.sortable({
			items: 'li.image',
			cursor: 'move',
			scrollSensitivity: 40,
			forcePlaceholderSize: true,
			forceHelperSize: false,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'rh-metabox-sortable-placeholder',
			start: function( event, ui ) {
				ui.item.css( 'background-color', '#f6f6f6' );
			},
			stop: function( event, ui ) {
				ui.item.removeAttr( 'style' );
			},
			update: function() {
				var attachment_ids = '';

				$( '#rh_post_images_container' ).find( 'ul li.image' ).css( 'cursor', 'default' ).each( function() {
					var attachment_id = $( this ).attr( 'data-attachment_id' );
					attachment_ids = attachment_ids + attachment_id + ',';
				});

				$image_gallery_ids.val( attachment_ids );
			}
		});

		// Remove images.
		$( '#rh_post_images_container' ).on( 'click', 'a.delete', function() {
			$( this ).closest( 'li.image' ).remove();

			var attachment_ids = '';

			$( '#rh_post_images_container' ).find( 'ul li.image' ).css( 'cursor', 'default' ).each( function() {
				var attachment_id = $( this ).attr( 'data-attachment_id' );
				attachment_ids = attachment_ids + attachment_id + ',';
			});

			$image_gallery_ids.val( attachment_ids );

			// Remove any lingering tooltips.
			$( '#tiptip_holder' ).removeAttr( 'style' );
			$( '#tiptip_arrow' ).removeAttr( 'style' );

			return false;
		});

	}
	$('#post_rehub_offers').addClass('closed');
});