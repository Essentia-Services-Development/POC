jQuery(document).on( 'change', '.rh_woo_drop_cat', function(e) {
   var catid = jQuery(this).val(),
   inputField = jQuery(this).parent().find('.re-ajax-search');
   if(inputField.length){
    inputField.attr("data-catid", catid);
    var inputValue = inputField.val();
        if(inputValue !=''){
            re_ajax_cache.remove(inputValue);
            re_ajax_search.do_ajax_call(inputField);
        }
   }
});