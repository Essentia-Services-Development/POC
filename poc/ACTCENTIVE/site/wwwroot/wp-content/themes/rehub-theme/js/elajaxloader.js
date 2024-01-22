jQuery(document).ready(function($) {
   'use strict';

    function rh_el_ajax_load($this){
        if($this.hasClass('loaded')) return;
        var post_id = $this.prop('class').match(/load-block-([0-9]+)/)[1];
        var post_id = parseInt(post_id);
        var blockforload = $(".el-ajax-load-block-"+post_id);
        blockforload.addClass("loading re_loadingafter padd20 font200 lightgreycolor"); 
        $.ajax({
            type: "post",
            url: rhscriptvars.ajax_url,
            data: "action=rh_el_ajax_hover_load&security="+rhscriptvars.nonce+"&post_id="+post_id
        }).done(function(response){
            blockforload.removeClass("loading re_loadingafter padd20 lightgreycolor font200");
            $this.addClass("loaded");
            if (response !== 'fail') {
                blockforload.html($(response));
                blockforload.find('.wpsm-bar').each(function(){
                    $(this).find('.wpsm-bar-bar').animate({ width: $(this).attr('data-percent') }, 1500 );
                });                                                                                                    
            }                                   
        });       
    }
    if($( ".rh-el-onhover" ).length > 0){
        $( ".rh-el-onhover" ).mouseenter(function() {
            var $this = $(this); 
            rh_el_ajax_load($this);     
        });
    }
    if($( ".rh-el-onclick" ).length > 0){
        $(document).on('click', '.rh-el-onclick', function() {
            var $this = $(this); 
            rh_el_ajax_load($this);     
        });
    } 
    if($( ".rh-el-onview" ).length > 0){
        $(document).on('inview', '.rh-el-onview', function() {
            var $this = $(this); 
            rh_el_ajax_load($this);     
        });
    } 
}); 