jQuery(document).ready(function($) {
   'use strict';     
        // Woocommerce Cart Quantity
        if ( ! String.prototype.rhgetDecimalNumber ) {
            String.prototype.rhgetDecimalNumber = function () {
                var num = this,
                    match = ('' + num).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
                if ( ! match ) {
                    return 0;
                }

                return Math.max( 0, ( match[1] ? match[1].length : 0 ) - ( match[2] ? + match[2] : 0 ) );
            }
        }

        $(document).on('click', '.rh-custom-quantity .plus-quantity, .rh-custom-quantity .minus-quantity', function () {
            // Get values
            var $qty = $(this).closest('.quantity').find('.qty'),
                currentVal = parseFloat( $qty.val() ),
                max = parseFloat($qty.attr('max')),
                min = parseFloat($qty.attr('min')),
                step = $qty.attr('step');

            // Format values
            if ( ! currentVal || currentVal === '' || currentVal === 'NaN' ) {
                currentVal = 0;
            }

            if ( max === '' || max === 'NaN' ) {
                max = '';
            }

            if ( min === '' || min === 'NaN' ) {
                min = 0;
            }

            if ( step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN' ) {
                step = '1';
            }

            if ( $(this).is('.plus-quantity') ) {
                if ( max && ( currentVal >= max ) ) {
                    $qty.val(max);
                } else {
                    $qty.val( ( currentVal + parseFloat( step ) ).toFixed( step.rhgetDecimalNumber() ) );
                }
            } else {
                if ( min && ( currentVal <= min ) ) {
                    $qty.val(min);
                } else if ( currentVal > 0 ) {
                    $qty.val( ( currentVal - parseFloat( step ) ).toFixed( step.rhgetDecimalNumber() ) );
                }
            }

            $(this).closest('.rh-loop-quantity').find('.ajax_add_to_cart').attr('data-quantity', parseInt($(this).closest('.rh-loop-quantity').find('.qty').val()));

            $qty.trigger('change');
        });

        var Carttimeout;
        $(document).on('change input', '.woocommerce-mini-cart .rh-custom-quantity .qty', function() {
            if ( typeof wc_cart_fragments_params === 'undefined' ) {
                return false;
            }

            var productQty = $(this).val();
            var productID = $(this).parents('.woocommerce-mini-cart-item').prop('class').match(/cartkey-([a-z0-9]+)/)[1];;
            var cart_hash_key = wc_cart_fragments_params.cart_hash_key;
            var fragment_name = wc_cart_fragments_params.fragment_name;

            console.log(productID);

            clearTimeout(Carttimeout);

            Carttimeout = setTimeout( function () {

                $.ajax({
                    url: rhscriptvars.ajax_url,
                    data: {
                        action: 'rh_update_sidebar_cart_item',
                        item_id: productID,
                        qty: productQty
                    },
                    success: function (data) {
                        if ( data && data.fragments ) {
                            $.each( data.fragments, function( key, value ) {
                                if ($(key).hasClass('widget_shopping_cart_content')) {
                                    var dataNewValue = $(value).find('.woocommerce-mini-cart-item.cartkey-' + productID + '');
                                    var FooterValue = $(value).find('.woocommerce-mini-cart__total')
                                    var $selector = $( key ).find('.woocommerce-mini-cart-item.cartkey-' + productID + '');

                                    if ( ! data.cart_hash ) {
                                        $( key ).replaceWith( value );
                                    } else {
                                        $selector.replaceWith(dataNewValue);
                                        $('.woocommerce-mini-cart__total').replaceWith( FooterValue );
                                    }
                                } else {
                                    $( key ).replaceWith( value );
                                }
                            });

                            sessionStorage.setItem( fragment_name, JSON.stringify( data.fragments ) );
                            localStorage.setItem( cart_hash_key, data.cart_hash );
                            sessionStorage.setItem( cart_hash_key, data.cart_hash );

                            if ( data.cart_hash ) {
                                sessionStorage.setItem( 'wc_cart_created', ( new Date() ).getTime() );
                            }
                        }
                    },
                    dataType: 'json',
                    method: 'GET',
                });
            }, 500 );
        });

        if ( $('.rh-loop-quantity form.cart').length ) {
            $('.rh-loop-quantity .qty').on('input', function() {
                $(this).closest('.rh-loop-quantity').find('.ajax_add_to_cart').attr('data-quantity', $(this).val());
            });
        }  
});