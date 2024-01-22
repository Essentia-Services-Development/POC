jQuery(document).ready(function($) {
   'use strict';    

   $('.progress-animate-onclick').on("click", ".trigger-progress-bar", function(e){
      $(this).closest('.progress-animate-onclick').find('.cssProgress').addClass('active');
      $(this).closest('.progress-animate-onclick').find('.cssProgress-bar').animate({ width: '100%' }, 18000);    
   }); 

});