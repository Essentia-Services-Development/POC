jQuery(document).ready(function($) {
   'use strict';
    //Woocommerce and post better categories
    if($('.product-categories .show-all-toggle').length > 0){
        $('.product-categories .show-all-toggle').each(function(){
            if( $(this).siblings('ul').length > 0 ) {
                var $toggleIcon = $('<span class="floatright font120 ml5 mr5 toggle-show-icon"><i class="rhicon rhi-angle-right"></i></span>');

                $(this).siblings('ul').hide();
                if($(this).siblings('ul').is(':visible')){
                    $toggleIcon.addClass( 'open' );
                    $toggleIcon.html('<i class="rhicon rhi-angle-up"></i>');
                }

                $(this).on( 'click', function(){
                    $(this).siblings('ul').toggle( 'fast', function(){
                        if($(this).is(':visible')){
                            $toggleIcon.addClass( 'open' );
                            $toggleIcon.closest('.closed-woo-catlist').removeClass('closed-woo-catlist');
                            $toggleIcon.html('<i class="rhicon rhi-angle-up"></i>');
                        }else{
                            $toggleIcon.removeClass( 'open' );
                            $toggleIcon.html('<i class="rhicon rhi-angle-right"></i>');
                        }
                    });
                    return false;
                });
                $(this).append($toggleIcon);
            }
        });
    }
}); 