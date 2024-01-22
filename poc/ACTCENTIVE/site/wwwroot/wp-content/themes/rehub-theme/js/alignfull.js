//Proper align Full
var rh_resizealign = function(){
    if(jQuery('.alignfulloutside .alignfull').length > 0){
        jQuery('.alignfulloutside .alignfull').each(function() {
            let rtltrue = (jQuery('body').hasClass('rtl')) ? true : false;
            let parent = jQuery(this).parent();
            let ancenstor = jQuery(window);
            let w = ancenstor.width();
            if(rtltrue){
                let bl = - (jQuery(window).width() - (parent.offset().left + parent.outerWidth()));
                if ( bl > 0 ) { right = 0; }
                jQuery(this).css({'width': w,'margin-right': bl });
            }else{
                let bl = - ( parent.offset().left );
                if ( bl > 0 ) { left = 0; }
                jQuery(this).css({'width': w,'margin-left': bl });
            }
        });
    }
};
rh_resizealign(); 
jQuery(window).on("resize", function(){
    rh_resizealign();
});  