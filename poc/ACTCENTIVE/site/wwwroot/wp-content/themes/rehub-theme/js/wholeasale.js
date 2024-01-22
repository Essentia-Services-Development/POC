jQuery(document).ready(function($) {
   'use strict';     

    if ( $('.rh-loop-quantity form.cart').length ) {
        $('.rh-loop-quantity .qty').on('input', function() {
            $(this).closest('.rh-loop-quantity').find('.add_to_cart_button').attr('data-quantity', $(this).val());
        });

        $('.rh-loop-quantity .plus').on('click', function () {            
            $(this).closest('.rh-loop-quantity').find('.add_to_cart_button').attr('data-quantity', $(this).closest('.rh-loop-quantity').find('.qty').val());
        });

        $('.rh-loop-quantity .minus').on('click', function () {
            $(this).closest('.rh-loop-quantity').find('.add_to_cart_button').attr('data-quantity', $(this).closest('.rh-loop-quantity').find('.qty').val());
        });
    }         

});