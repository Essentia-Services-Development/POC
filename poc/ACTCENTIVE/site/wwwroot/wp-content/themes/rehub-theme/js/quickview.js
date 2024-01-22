jQuery(document).ready(function($) {
   'use strict';     

    //Quick view
    $(document).on('click', '.quick_view_button', function(e){
        e.preventDefault();
        var productID = $(this).data('product_id');
        var quickViewProduct = $(this).closest('quick_view_wrap').find('.quick_view_product');
        var data = {
            'action': 'product_quick_view',
            'product_id': productID,
            'nonce' : quickviewvars.quicknonce,
        };

        $.pgwModal({
            url: rhscriptvars.ajax_url,
            titleBar: false,
            maxWidth: 800,
            loadingContent : '<img src="'+quickviewvars.templateurl+'/images/loaded.gif">',
            mainClassName : 'pgwModal quick_view_product',
            ajaxOptions : {
                data : data,
                success : function(response) {
                    if (response) {
                        $.pgwModal({ pushContent: response });

                        //if ( quickviewvars.ajax_add_to_cart ) {
                            //ajax_add_to_cart();
                        //}
                    } else {
                        $.pgwModal({ pushContent: 'An error has occured' });
                    }
                }
            }
        });
    });          

});