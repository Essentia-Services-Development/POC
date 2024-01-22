jQuery(document).ready(function($) {
   'use strict';
   /* review woo tabs */
   $('.rehub_woo_tabs_menu').on('click', 'li:not(.current)', function() {
      $(this).addClass('current').siblings().removeClass('current').parents('.rehub_woo_review').find('.rehub_woo_review_tabs').hide().eq($(this).index()).fadeIn(700);     
   });
   $('.rehub_woo_tabs_menu li:first-child').trigger('click');
   $('.btn_offer_block.choose_offer_woo').click(function(event){     
      event.preventDefault();
      $('.rehub_woo_tabs_menu li.woo_deals_tab').trigger('click');
   });   
}); 