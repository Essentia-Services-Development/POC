jQuery(document).ready(function($) {
    'use strict'; 
    $(document).on('click', '.expand_all_offers', function() {
        var $expand = $(this).closest('.widget_merchant_list');
        if($expand.hasClass('expandme')){
            $expand.removeClass('expandme');
            $(this).find('.expandme').html('-');
        }
        else{
            $expand.addClass('expandme');
            $(this).find('.expandme').html('+');
        }
    }); 
});