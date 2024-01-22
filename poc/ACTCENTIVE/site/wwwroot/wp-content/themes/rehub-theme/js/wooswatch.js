jQuery( '.variations_form' ).on( 'click', '.reset_variations', function () {
    var rhswatches = jQuery('.rh-var-selector');
    rhswatches.find('.rh-var-label').removeClass('rhhidden');
    rhswatches.each(function(){
        jQuery(this).find('.rh-var-input').each(function(){
            jQuery(this).prop( "checked", false );
        });
    });
});


jQuery(document).on( 'click', '.rh-var-label', function(e) {
    let radio = jQuery(this).prev();
    radio.click();
    let mainselector = jQuery(this).closest('.rh-var-selector');
    let attr = mainselector.data('attribute');
    var newValue = radio.val();
    let selectNew = jQuery(this).parent().parent().find("select[name="+attr+"]");
    selectNew.val(newValue).trigger("change");
});


jQuery( '.variations_form' ).on( 'woocommerce_update_variation_values', function () {
    var rhswatches = jQuery('.rh-var-selector');
    rhswatches.find('.rh-var-label').removeClass('rhhidden');
    rhswatches.each(function(){
        var variationselect = jQuery(this).prev();
        jQuery(this).find('.rh-var-label').each(function(){
            if (variationselect.find('option[value="'+ jQuery(this).attr("data-value") +'"]').length <= 0) {
                jQuery(this).addClass('rhhidden');
            }
        });
    });
});