jQuery(document).ready(function($) {
   'use strict';     

    var rh_ajax_add_to_cart = function() {

        if ( 'undefined' === typeof wc_add_to_cart_params ) {
            // The add to cart params are not present.
            return false;
        }

        $.fn.DataserializeArray = function () {
            var rdata = /\r?\n/g;
            return this.map(function () {
                return this.elements ? jQuery.makeArray(this.elements) : this;
            }).map(function (i, elem) {
                var val = jQuery(this).val();
                if (val == null) {
                    return val == null
                } else if (this.type == "checkbox" && this.checked == false) {
                    return {name: this.name, value: this.checked ? this.value : ''}
                } else {
                    return jQuery.isArray(val) ?
                    jQuery.map(val, function (val, i) {
                        return {name: elem.name, value: val.replace(rdata, "\r\n")};
                    }) : {name: elem.name, value: val.replace(rdata, "\r\n")};
                }
            }).get();
        };

        $(document).on('click', '.wooquickviewbtn .single_add_to_cart_button:not(.disabled)', function (e) {
            e.preventDefault();

            var $thisbutton = $(this),
            $form = $thisbutton.closest('form.cart'),
            data = $form.find('input:not([name="product_id"]), select, button, textarea').DataserializeArray() || 0;

            $.each(data, function (i, item) {
                if ( item.name == 'add-to-cart') {
                    item.name = 'product_id';
                    item.value = $form.find('input[name=variation_id]').val() || $thisbutton.val();
                }
            });

            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ),
                data: data,
                beforeSend: function (response) {
                    $thisbutton.removeClass('added').addClass('loading');
                },
                complete: function (response) {
                    $thisbutton.addClass('added').removeClass('loading');
                },
                success: function (response) {

                    if ( response.error & response.product_url ) {
                        window.location = response.product_url;
                        return;
                    }

                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton] );
                },
                dataType: 'json'
            });
       });
    }; 

    rh_ajax_add_to_cart();
});

function rh_ajax_woo_cart_loading (el){
    if(typeof wc_cart_fragments_params === 'undefined'){
        return false;
    }
    var widgetCartContent = el.find(".widget_shopping_cart");
    widgetCartContent.addClass("loaded re_loadingbefore");
    jQuery.ajax({
        type: "post",
        url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
        data: {
            time: new Date().getTime()
        },
        timeout: wc_cart_fragments_params.request_timeout
    }).done(function(data){
        if (data && data.fragments) {
            widgetCartContent.html(data.fragments["div.widget_shopping_cart_content"]);
            widgetCartContent.removeClass("re_loadingbefore");
        }                                   
    });  
}
jQuery(document).on( 'added_to_cart', function ( event, fragments, cart_hash ) {
    var widget = jQuery('#rh-woo-cart-panel');
    let errorspage = jQuery('.ajaxerrors').length;
    let nosliding = jQuery('.no_cart_sliding').length;
    if ( ! widget.hasClass( 'active' ) && errorspage < 1 && nosliding < 1 ) {
        widget.addClass( 'active' );
        rh_ajax_woo_cart_loading(widget); 
        if(document.getElementById('pgwModal') != null){
            $.pgwModal('close');
        }
    }
    if(errorspage < 1 && nosliding > 0){
        jQuery.simplyToast(rhscriptvars.addedcart, 'success');
    }
});
jQuery(document).on("click", ".menu-cart-btn", function(e){
    e.preventDefault();
    var widget = jQuery('#rh-woo-cart-panel');
    if ( ! widget.hasClass( 'active' ) ) {
        widget.addClass( 'active' );
        rh_ajax_woo_cart_loading(widget); 
    }   
})