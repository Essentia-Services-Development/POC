jQuery( function( $ ) {
	'use strict';
	
	if( !marketking_stripe_split_pay_params.key ) return;

	var stripe = Stripe( marketking_stripe_split_pay_params.key );

	var stripe_elements_options = marketking_stripe_split_pay_params.elements_options.length ? marketking_stripe_split_pay_params.elements_options : {},
		elements = stripe.elements( stripe_elements_options ),
		stripe_card_data,
		stripe_card,
		stripe_exp,
		stripe_cvc;


	/**
	 * Object to handle Stripe elements payment form.
	 */

	var marketking_stripe_split_pay_form = {

		/**
		 * Get WC AJAX endpoint URL.
		 *
		 * @param  {String} endpoint Endpoint.
		 * @return {String}
		 */
		getAjaxURL: function( endpoint ) {
			return marketking_stripe_split_pay_params.ajaxurl
				.toString()
				.replace( '%%endpoint%%', 'wc_stripe_' + endpoint );
		},

		unmountElements: function() {
			stripe_card.unmount( '#marketking-stripe-split-pay-card-element' );
			stripe_exp.unmount( '#marketking-stripe-split-pay-exp-element' );
			stripe_cvc.unmount( '#marketking-stripe-split-pay-cvc-element' );
		},

		mountElements: function() {
			if ( ! $( '#marketking-stripe-split-pay-card-element' ).length ) {
				return;
			}
			stripe_card.mount( '#marketking-stripe-split-pay-card-element' );
			stripe_exp.mount( '#marketking-stripe-split-pay-exp-element' );
			stripe_cvc.mount( '#marketking-stripe-split-pay-cvc-element' );
		},

		createElements: function() {
			var elementStyles = {
				base: {
					iconColor: '#666EE8',
					color: '#31325F',
					fontSize: '15px',
					'::placeholder': {
				  		color: '#CFD7E0',
					}
				}
			};

			var elementClasses = {
				focus: 'focused',
				empty: 'empty',
				invalid: 'invalid',
			};

			elementStyles  = marketking_stripe_split_pay_params.elements_styling ? marketking_stripe_split_pay_params.elements_styling : elementStyles;
			elementClasses = marketking_stripe_split_pay_params.elements_classes ? marketking_stripe_split_pay_params.elements_classes : elementClasses;

			stripe_card = elements.create( 'cardNumber', { style: elementStyles, classes: elementClasses } );
			stripe_exp  = elements.create( 'cardExpiry', { style: elementStyles, classes: elementClasses } );
			stripe_cvc  = elements.create( 'cardCvc', { style: elementStyles, classes: elementClasses } );

			stripe_card.addEventListener( 'change', function( event ) {
				marketking_stripe_split_pay_form.onCCFormChange();

				marketking_stripe_split_pay_form.updateCardBrand( event.brand );

				if ( event.error ) {
					$( document.body ).trigger( 'stripeError', event );
				}
			} );

			stripe_exp.addEventListener( 'change', function( event ) {
				marketking_stripe_split_pay_form.onCCFormChange();

				if ( event.error ) {
					$( document.body ).trigger( 'stripeError', event );
				}
			} );

			stripe_cvc.addEventListener( 'change', function( event ) {
				marketking_stripe_split_pay_form.onCCFormChange();

				if ( event.error ) {
					$( document.body ).trigger( 'stripeError', event );
				}
			} );
			
			// Saved Cards Processing
			if( jQuery('input[name=marketking_stripe_customer_id]').length > 0 ) {
				marketking_stripe_split_pay_form.form.on('change', 'input[name=marketking_stripe_customer_id]', function() {
					if ( jQuery('input[name=marketking_stripe_customer_id]:checked').val() == 'new' ) {
						jQuery('div.marketking_stripe_new_card').slideDown( 200 );
					} else {
						jQuery('div.marketking_stripe_new_card').slideUp( 200 );
					}
					marketking_stripe_split_pay_form.onCCFormChange();
				} );
			}

		
			/**
			 * Only in checkout page we need to delay the mounting of the
			 * card as some AJAX process needs to happen before we do.
			 */
			if ( 'yes' === marketking_stripe_split_pay_params.is_checkout ) {
				$( document.body ).on( 'updated_checkout', function() {
					// Don't mount elements a second time.
					if ( stripe_card ) {
						marketking_stripe_split_pay_form.unmountElements();
					}

					marketking_stripe_split_pay_form.mountElements();
				} );
			} else if ( 'yes' === marketking_stripe_split_pay_params.is_pay_for_order_page ) {
				marketking_stripe_split_pay_form.mountElements();
			}
		},

		updateCardBrand: function( brand ) {
			var brandClass = {
				'visa': 'stripe-visa-brand',
				'mastercard': 'stripe-mastercard-brand',
				'amex': 'stripe-amex-brand',
				'discover': 'stripe-discover-brand',
				'diners': 'stripe-diners-brand',
				'jcb': 'stripe-jcb-brand',
				'unknown': 'stripe-credit-card-brand'
			};

			var imageElement = $( '.stripe-card-brand' ),
				imageClass = 'stripe-credit-card-brand';

			if ( brand in brandClass ) {
				imageClass = brandClass[ brand ];
			}

			// Remove existing card brand class.
			$.each( brandClass, function( index, el ) {
				imageElement.removeClass( el );
			} );

			imageElement.addClass( imageClass );
		},

		/**
		 * Initialize event handlers and UI state.
		 */
		init: function() {

			// Stripe Checkout.
			this.stripe_checkout_submit = false;

			// checkout page
			if ( $( 'form.woocommerce-checkout' ).length ) {
				this.form = $( 'form.woocommerce-checkout' );
			}

			$( 'form.woocommerce-checkout' )
				.on(
					'checkout_place_order_marketking_stripe_gateway',
					this.onSubmit
				);

			// pay order page
			if ( $( 'form#order_review' ).length ) {
				this.form = $( 'form#order_review' );
			}
			
			$( 'form#order_review' )
				.on(
					'submit',
					this.onSubmit
				);

			$( 'form.woocommerce-checkout' )
				.on(
					'change',
					this.reset
				);

			$( document )
				.on(
					'stripeError',
					this.onError
				)
				.on(
					'checkout_error',
					this.reset
				);

			marketking_stripe_split_pay_form.createElements();
			
			// Listen for hash changes in order to handle payment intents
			if( marketking_stripe_split_pay_params.is_3d_secure ) {
				window.addEventListener( 'hashchange', marketking_stripe_split_pay_form.onHashChange );
				marketking_stripe_split_pay_form.maybeConfirmIntent();
			}
		},

		// Check to see if Stripe in general is being used for checkout.
		isStripeChosen: function() {
			return $( '#payment_method_marketking_stripe_gateway' );
		},

		hasSource: function() {
			return 0 < $( 'input.stripe-source' ).length;
		},

		// Legacy
		hasToken: function() {
			return 0 < $( 'input.stripe-token' ).length;
		},

		isMobile: function() {
			if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
				return true;
			}

			return false;
		},

		block: function() {
			if ( ! marketking_stripe_split_pay_form.isMobile() ) {
				marketking_stripe_split_pay_form.form.block( {
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				} );
			}
		},

		unblock: function() {
			marketking_stripe_split_pay_form.form.unblock();
		},

		getSelectedPaymentElement: function() {
			return $( '.payment_methods input[name="payment_method"]:checked' );
		},

		getOwnerDetails: function() {
			var first_name = $( '#billing_first_name' ).length ? $( '#billing_first_name' ).val() : marketking_stripe_split_pay_params.billing_first_name,
				last_name  = $( '#billing_last_name' ).length ? $( '#billing_last_name' ).val() : marketking_stripe_split_pay_params.billing_last_name,
				extra_details = { owner: { name: '', address: {}, email: '', phone: '' } };

			extra_details.owner.name = first_name;

			if ( first_name && last_name ) {
				extra_details.owner.name = first_name + ' ' + last_name;
			} else {
				extra_details.owner.name = $( '#marketking-stripe-split-pay-payment-data' ).data( 'full-name' );
			}

			extra_details.owner.email = $( '#billing_email' ).val();
			extra_details.owner.phone = $( '#billing_phone' ).val();

			/* Stripe does not like empty string values so
			 * we need to remove the parameter if we're not
			 * passing any value.
			 */
			if ( typeof extra_details.owner.phone !== 'undefined' && 0 >= extra_details.owner.phone.length ) {
				delete extra_details.owner.phone;
			}

			if ( typeof extra_details.owner.email !== 'undefined' && 0 >= extra_details.owner.email.length ) {
				delete extra_details.owner.email;
			}

			if ( typeof extra_details.owner.name !== 'undefined' && 0 >= extra_details.owner.name.length ) {
				delete extra_details.owner.name;
			}

			if ( $( '#billing_address_1' ).length > 0 ) {
				extra_details.owner.address.line1       = $( '#billing_address_1' ).val();
				extra_details.owner.address.line2       = $( '#billing_address_2' ).val();
				extra_details.owner.address.state       = $( '#billing_state' ).val();
				extra_details.owner.address.city        = $( '#billing_city' ).val();
				extra_details.owner.address.postal_code = $( '#billing_postcode' ).val();
				extra_details.owner.address.country     = $( '#billing_country' ).val();
			} else if ( marketking_stripe_split_pay_params.billing_address_1 ) {
				extra_details.owner.address.line1       = marketking_stripe_split_pay_params.billing_address_1;
				extra_details.owner.address.line2       = marketking_stripe_split_pay_params.billing_address_2;
				extra_details.owner.address.state       = marketking_stripe_split_pay_params.billing_state;
				extra_details.owner.address.city        = marketking_stripe_split_pay_params.billing_city;
				extra_details.owner.address.postal_code = marketking_stripe_split_pay_params.billing_postcode;
				extra_details.owner.address.country     = marketking_stripe_split_pay_params.billing_country;
			}

			return extra_details;
		},

		createSource: function() {
			var extra_details = marketking_stripe_split_pay_form.getOwnerDetails();
			stripe.createSource( stripe_card, extra_details ).then( marketking_stripe_split_pay_form.sourceResponse );
		},
		
		createToken: function() {
			for(var i = 0; i < marketking_stripe_split_pay_params.no_of_vendor; i++) {
				stripe.createToken(stripe_card).then( marketking_stripe_split_pay_form.tokenResponse );
			}
		},
		
		savedCardToken: function() {
			stripe_card_data = {
											number:     jQuery('input[name=marketking_stripe_customer_id]:checked').data('last4'),
											cvc:        jQuery('input[name=marketking_stripe_customer_id]:checked').data('cvv'),
											exp_month:  jQuery('input[name=marketking_stripe_customer_id]:checked').data('exp_month'),
											exp_year:   jQuery('input[name=marketking_stripe_customer_id]:checked').data('exp_year'),
											name:       marketking_stripe_split_pay_params.billing_first_name + ' ' + marketking_stripe_split_pay_params.billing_last_name,
											address_line1: marketking_stripe_split_pay_params.billing_address_1,
											address_line2: marketking_stripe_split_pay_params.billing_address_2,
											address_state: marketking_stripe_split_pay_params.billing_state,
											address_city: marketking_stripe_split_pay_params.billing_city,
											address_zip: marketking_stripe_split_pay_params.billing_postcode,
											address_country: marketking_stripe_split_pay_params.billing_country
									};
			for(var i = 0; i < marketking_stripe_split_pay_params.no_of_vendor; i++) {
				stripe.createToken(stripe_card_data).then( marketking_stripe_split_pay_form.tokenResponse );
			}
		},

		sourceResponse: function( response ) {
			if ( response.error ) {
				$( document.body ).trigger( 'stripeError', response );
			} else {

				marketking_stripe_split_pay_form.processStripeResponse( response.source );
			}
		},

		tokenResponse: function( response ) {
			if ( response.error ) {
				$( document.body ).trigger( 'stripeError', response );
			} else {
				marketking_stripe_split_pay_form.processStripeTokenResponse( response.token );
			}
		},
		
		processStripeResponse: function( source ) {
			marketking_stripe_split_pay_form.reset();
			
			// Insert the Source into the form so it gets submitted to the server.
			marketking_stripe_split_pay_form.form.append( "<input type='hidden' class='stripe-source' name='stripe_source' value='" + source.id + "'/>" );
			marketking_stripe_split_pay_form.form.submit();
		},
		
		processStripeTokenResponse: function( token ) {
			// Insert the Token into the form so it gets submitted to the server.
			marketking_stripe_split_pay_form.form.append( "<input type='hidden' class='stripe-token' name='stripe_token[]' value='" + token.id + "'/>" );
		},

		onSubmit: function( e ) {
			if ( ! marketking_stripe_split_pay_form.isStripeChosen() ) {
				return;
			}

			if( jQuery('input[name=marketking_stripe_customer_id]').length > 0 ) {

				if ( jQuery('input[name=marketking_stripe_customer_id]:checked').val() == 'new' ) {

					if( ! marketking_stripe_split_pay_form.hasSource() || ! marketking_stripe_split_pay_form.hasToken() ) {

						e.preventDefault();
	
						marketking_stripe_split_pay_form.block();
						marketking_stripe_split_pay_form.createSource();
						marketking_stripe_split_pay_form.createToken();
						
						// Prevent form submitting
						return false;
					}
				} else {
					e.preventDefault();

					marketking_stripe_split_pay_form.block();
					marketking_stripe_split_pay_form.form.append( "<input type='hidden' class='stripe-source' name='stripe_source' value='" + jQuery('input[name=marketking_stripe_customer_id]:checked').val() + "'/>" );
					marketking_stripe_split_pay_form.createToken();
					
					// Prevent form submitting
					//return false;
				}
			} else {

				if( ! marketking_stripe_split_pay_form.hasSource() || ! marketking_stripe_split_pay_form.hasToken() ) {

					e.preventDefault();

					marketking_stripe_split_pay_form.block();
					marketking_stripe_split_pay_form.createSource();
					marketking_stripe_split_pay_form.createToken();
					
					// Prevent form submitting
					return false;
				}
			}
		},

		onCCFormChange: function() {
			marketking_stripe_split_pay_form.reset();
		},

		reset: function() {
			$( '.wc-stripe-error, .stripe-source, .stripe_token' ).remove();

			// Stripe Checkout.
			if ( 'yes' === marketking_stripe_split_pay_params.is_stripe_checkout ) {
				marketking_stripe_split_pay_form.stripe_submit = false;
			}
		},

		onError: function( e, result ) {
			var message = result.error.message;
			var	errorContainer = marketking_stripe_split_pay_form.getSelectedPaymentElement().parents( 'li' ).eq(0).find( '.marketking-stripe-split-pay-source-errors' );

			/*
			 * Customers do not need to know the specifics of the below type of errors
			 * therefore return a generic localizable error message.
			 */
			if (
				'invalid_request_error' === result.error.type ||
				'api_connection_error'  === result.error.type ||
				'api_error'             === result.error.type ||
				'authentication_error'  === result.error.type ||
				'rate_limit_error'      === result.error.type
			) {
				message = marketking_stripe_split_pay_params.invalid_request_error;
			}

			if ( 'card_error' === result.error.type && marketking_stripe_split_pay_params.hasOwnProperty( result.error.code ) ) {
				message = marketking_stripe_split_pay_params[ result.error.code ];
			}

			if ( 'validation_error' === result.error.type && marketking_stripe_split_pay_params.hasOwnProperty( result.error.code ) ) {
				message = marketking_stripe_split_pay_params[ result.error.code ];
			}

			marketking_stripe_split_pay_form.reset();
			$( '.woocommerce-NoticeGroup-checkout' ).remove();
			console.log( result.error.message ); // Leave for troubleshooting.
			$( errorContainer ).html( '<ul class="woocommerce_error woocommerce-error wc-stripe-error"><li>' + result.error.message + '</li></ul>' );

			if ( $( '.wc-stripe-error' ).length ) {
				$( 'html, body' ).animate({
					scrollTop: ( $( '.wc-stripe-error' ).offset().top - 200 )
				}, 200 );
			}
			marketking_stripe_split_pay_form.unblock();
		},
		
		/**
		 * Handles changes in the hash in order to show a modal for PaymentIntent confirmations.
		 *
		 * Listens for `hashchange` events and checks for a hash in the following format:
		 * #confirm-pi-<intentClientSecret>:<successRedirectURL>
		 *
		 * If such a hash appears, the partials will be used to call `stripe.handleCardPayment`
		 * in order to allow customers to confirm an 3DS/SCA authorization.
		 *
		 * Those redirects/hashes are generated in `WC_Gateway_Stripe::process_payment`.
		 */
		onHashChange: function() {
			var partials = window.location.hash.match( /^#?confirm-pi-([^:]+):(.+)$/ );

			if ( ! partials || 3 > partials.length ) {
				return;
			}

			var intentClientSecret = partials[1];
			var redirectURL        = decodeURIComponent( partials[2] );

			// Cleanup the URL
			window.location.hash = '';

			marketking_stripe_split_pay_form.openIntentModal( intentClientSecret, redirectURL );
		},

		maybeConfirmIntent: function() {
			if ( ! $( '#stripe-intent-id' ).length || ! $( '#stripe-intent-return' ).length ) {
				return;
			}

			var intentSecret = $( '#stripe-intent-id' ).val();
			var returnURL    = $( '#stripe-intent-return' ).val();

			marketking_stripe_split_pay_form.openIntentModal( intentSecret, returnURL, true );
		},

		/**
		 * Opens the modal for PaymentIntent authorizations.
		 *
		 * @param {string}  intentClientSecret The client secret of the intent.
		 * @param {string}  redirectURL        The URL to ping on fail or redirect to on success.
		 * @param {boolean} alwaysRedirect     If set to true, an immediate redirect will happen no matter the result.
		 *                                     If not, an error will be displayed on failure.
		 */
		openIntentModal: function( intentClientSecret, redirectURL, alwaysRedirect ) {
			stripe.handleCardPayment( intentClientSecret )
			.then( function( response ) {
				if ( response.error ) {
					throw response.error;
				}

				if ( 'requires_capture' !== response.paymentIntent.status && 'succeeded' !== response.paymentIntent.status ) {
					return;
				}

				window.location = redirectURL;
			} )
			.catch( function( error ) {
				if ( alwaysRedirect ) {
					return window.location = redirectURL;
				}

				$( document.body ).trigger( 'stripeError', { error: error } );
				marketking_stripe_split_pay_form.form && marketking_stripe_split_pay_form.form.removeClass( 'processing' );

				// Report back to the server.
				$.get( redirectURL + '&is_ajax' );
			} );
		}
	};

	marketking_stripe_split_pay_form.init();
} );