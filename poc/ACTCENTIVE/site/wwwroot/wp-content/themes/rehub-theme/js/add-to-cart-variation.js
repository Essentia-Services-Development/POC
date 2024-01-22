/* Wc add to cart version 2.2 */
jQuery( function( $ ) {

	// wc_add_to_cart_params is required to continue, ensure the object exists
	if ( typeof wc_add_to_cart_params === 'undefined' )
		return false;
	
	// Ajax add to cart
	$( document ).on( 'click', '.variations_form .single_add_to_cart_button', function(e) {
		
		e.preventDefault();
		e.stopPropagation();
		
		$variation_form = $( this ).closest( '.variations_form' );
		var var_id = $variation_form.find( 'input[name=variation_id]' ).val();
		var product_id = $variation_form.find( 'input[name=product_id]' ).val();
		var quantity = $variation_form.find( 'input[name=quantity]' ).val();
		
		//attributes = [];
		$( '.ajaxerrors' ).remove();
		var item = {},
			check = true;
			
			variations = $variation_form.find( 'select[name^=attribute]' );
			
			/* Updated code to work with radio button - mantish - WC Variations Radio Buttons - 8manos */ 
			if ( !variations.length) {
				variations = $variation_form.find( '[name^=attribute]:checked' );
			}
			
			/* Backup Code for getting input variable */
			if ( !variations.length) {
    			variations = $variation_form.find( 'input[name^=attribute]' );
			}
		
		variations.each( function() {
		
			var $this = $( this ),
				attributeName = $this.attr( 'name' ),
				attributevalue = $this.val(),
				index,
				attributeTaxName;
		
			$this.removeClass( 'error' );
		
			if ( attributevalue.length === 0 ) {
				index = attributeName.lastIndexOf( '_' );
				attributeTaxName = attributeName.substring( index + 1 );
		
				$this
					.addClass( 'required error' )
					.before( '<div class="ajaxerrors redcolor font80">Please select ' + attributeTaxName + '</div>' )
		
				check = false;
			} else {
				item[attributeName] = attributevalue;
			}
		
		} );
		
		if ( !check ) {
			return false;
		}
		
		var $thisbutton = $( this );

		if ( $thisbutton.is( '.variations_form .single_add_to_cart_button' ) ) {

			$thisbutton.removeClass( 'added' );
			$thisbutton.addClass( 'loading' );

			var data = {
				action: 'woocommerce_add_to_cart_variable_rh',
			};

			$variation_form.serializeArray().map(function (attr) {
				if (attr.name !== 'add-to-cart') {
				    if (attr.name.endsWith('[]')) {
				        let name = attr.name.substring(0, attr.name.length - 2);
				        if (!(name in data)) {
				            data[name] = [];
				        }
				        data[name].push(attr.value);
				    } else {
				        data[attr.name] = attr.value;
				    }
				}
			});

			// Trigger event
			$( 'body' ).trigger( 'adding_to_cart', [ $thisbutton, data ] );

			// Ajax action
			$.post( wc_add_to_cart_params.ajax_url, data, function( response ) {

				if ( ! response ) {
					return;
				}

				if ( response.error && response.product_url ) {
					window.location = response.product_url;
					return;
				}

				// Redirect to cart option
				if ( wc_add_to_cart_params.cart_redirect_after_add === 'yes' ) {
					window.location = wc_add_to_cart_params.cart_url;
					return;
				}

				// Trigger event so themes can refresh other areas.
				$( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, $thisbutton ] );

			});

			return false;

		} else {
			
			return true;
		}

	});


});
