jQuery(document).ready(function($) {
   'use strict';
   $(document).on('click', '.vertical-menu > a', function(e){
      e.preventDefault();
      e.stopPropagation();
      var vertmenu = $(this).closest('.vertical-menu');
      if(vertmenu.hasClass('hovered')){
         vertmenu.removeClass('hovered').removeClass('vmenu-opened');
      }else{
         vertmenu.toggleClass("vmenu-opened");
      }     
   });   
}); 