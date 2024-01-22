jQuery(document).ready(function($) {
   'use strict';	

   function GetURLParameter(sParam){
      var sPageURL = window.location.search.substring(1);
      var sURLVariables = sPageURL.split('&');
      for (var i = 0; i < sURLVariables.length; i++) 
      {
         var sParameterName = sURLVariables[i].split('=');
         if (sParameterName[0] == sParam) 
         {
            return sParameterName[1];
         }
      }
   }    

   var affcoupontrigger = GetURLParameter("codetext");
   if(affcoupontrigger){
      var $change_mecode = $(".rehub_offer_coupon.masked_coupon:not(.expired_coupon)[data-codetext='" + affcoupontrigger +"']");
      var couponcode = $change_mecode.data('clipboard-text'); 
      var coupondestination = $change_mecode.data('dest');
      $change_mecode.removeClass('masked_coupon woo_loop_btn coupon_btn btn_offer_block wpsm-button').addClass('not_masked_coupon').html( '<i class="rhicon rhi-scissors fa-rotate-180"></i><span class="coupon_text">'+ decodeURIComponent(couponcode) +'</span>' );
      $change_mecode.closest('.reveal_enabled').removeClass('reveal_enabled');      
      $.pgwModal({
         titleBar: false,
         mainClassName : 'pgwModal coupon-reveal-popup',
         content: '<div class="coupon_code_in_modal text-center"><div class="coupon_top_part violetgradient_bg padd20"><div class="re_title_inmodal rehub-main-font whitecolor font150 pt5 pb15">' + coupvars.coupontextready + '</div><div class="add_modal_coupon font80"><span class="text_copied_coupon pinkLcolor">' + coupvars.coupontextcopied + '</span></div><div class="coupon_modal_coupon mb30 position-relative"><div class="cpn_modal_container text-center position-relative roundborder8 inlinestyle"><input type="text" size=20 class="code text-center" value="' + decodeURIComponent(couponcode) + '" readonly=""></div></div></div><a href="' + coupondestination + '" target="_blank" rel="nofollow sponsored" class="cpn_btn_inner font150 pb10 pl30 pr30 pt10 rehub-main-btn-bg rehub_main_btn cpn_btn_inner position-relative">' + coupvars.coupongotowebsite + '</a><div class="cpn_info_bl padd20 flowhidden text-center">' + coupvars.couponorcheck + '</div></div>',
      });
   };

});