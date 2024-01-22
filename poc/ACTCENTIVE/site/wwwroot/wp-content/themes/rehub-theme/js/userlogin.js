jQuery(document).ready(function($) {
   'use strict';    


   // Add modal on links for non logged in comment form  
   if ($('#respond .must-log-in a').length > 0) {
      if ($('#rehub-login-popup').length > 0) {
         $( "#respond .must-log-in a" ).addClass('act-rehub-login-popup'); 
      }   
   } 

   if ($('#comments .comment-reply-login').length > 0) {
      if ($('#rehub-login-popup').length > 0) {
         $( ".comment-reply-login" ).addClass('act-rehub-login-popup'); 
      }   
   }        

   // Open login/register modal
   $(document).on('click', 'body:not(.logged-in) .act-rehub-login-popup', function(e) {
      e.preventDefault();
      var acttype = $(this).data('type');
      if (acttype == 'login') {
         $.pgwModal({
            titleBar: false,
            target: '#rehub-login-popup',
            mainClassName : 'pgwModal re-user-popup-wrap',
         });
         $('.re-user-popup-wrap .rehub-errors').html('');
      }
      else if(acttype == 'register') {
         $.pgwModal({
            titleBar: false,
            target: '#rehub-register-popup',
            mainClassName : 'pgwModal re-user-popup-wrap',
         });  
         $('.re-user-popup-wrap .rehub-errors').html('');
         $('.re-user-popup-wrap .recaptchamodail').attr('id', 'recaptchamodail');       
      }
      else if(acttype == 'resetpass') {
         $.pgwModal({
            titleBar: false,
            target: '#rehub-reset-popup',
            mainClassName : 'pgwModal re-user-popup-wrap',
         }); 
         $('.re-user-popup-wrap .rehub-errors').html('');        
      } 
      else if(acttype == 'restrict') {
         $.pgwModal({
            titleBar: false,
            target: '#rehub-restrict-login-popup',
            mainClassName : 'pgwModal re-user-popup-wrap',
         });         
      } 
      else if(acttype == 'url') {
        if($(this).attr('href')){
            var gocustomurl = $(this).attr('href');  
        }else{
            var gocustomurl = $(this).data('customurl');
        }
        window.location.href = gocustomurl;
      }       
      else {
         if($('#rehub-custom-login-url').length > 0){
            var gocustomurl = $('#rehub-custom-login-url').data('customloginurl');
            window.location.href = gocustomurl;
         }else{
            if(typeof $(this).data("cashbacknotice") !== "undefined" && typeof $(this).data("merchant") !== "undefined"){
                var cashbacknotice = $(this).data('cashbacknotice');
                var merchant = $(this).data('merchant');
                var murl = $(this).data('url');
                $('#rh-ca-login').removeClass('rhhidden');
                $('#rh-ca-login-n').html(cashbacknotice);
                $('#rh-ca-login-m').html(merchant);
                $('#rh-ca-login-a').attr("href", murl);
            }           
            $.pgwModal({
               titleBar: false,
               target: '#rehub-login-popup',
               mainClassName : 'pgwModal re-user-popup-wrap',
            });
            $('.re-user-popup-wrap .rehub-errors').html('');          
         }
      }                
   });

   // Post login form submit 
   $(document).on('submit','.re-user-popup-wrap #rehub_login_form_modal',function(e){
      e.preventDefault();
      var button = $(this).find('button.rehub_main_btn');
      button.addClass('loading');
      $.post(rhscriptvars.ajax_url, $(this).serialize(), function(data){
         var obj = $.parseJSON(data);
         $('.rehub-login-popup .rehub-errors').html(obj.message);       
         if(obj.error == false){
            if(obj.redirecturl){
              window.setTimeout(function(){window.location.href = obj.redirecturl;},200);
            }
            else{
              window.setTimeout(function(){location.reload()},200);
            }
            button.hide();
         }
         button.removeClass('loading');
      });
   });

   // Post register form
   $(document).on('submit','.re-user-popup-wrap #rehub_registration_form_modal',function(e){
      e.preventDefault();
      var button = $(this).find('button.rehub_main_btn');
      button.addClass('loading');
      $.post(rhscriptvars.ajax_url, $(this).serialize(), function(data){       
         var obj = $.parseJSON(data);
         $('.rehub-register-popup .rehub-errors').html(obj.message);       
         if(obj.error == false){
            $('.rehub-register-popup').addClass('registration-complete');
            if(obj.redirecturl){
                window.setTimeout(function(){window.location.href = obj.redirecturl;},4000);
            }
            else{
                window.setTimeout(function(){location.reload()},4000);
            }
            //button.hide();
         }
         $('.rehub-register-popup').removeClass('registration-complete');
         button.removeClass('loading');       
      });
   });

   // Reset Password
   $(document).on('submit','.re-user-popup-wrap #rehub_reset_password_form_modal',function(e){
      e.preventDefault();
      var button = $(this).find('button.rehub_main_btn');
      button.addClass('loading');
      $.post(rhscriptvars.ajax_url, $(this).serialize(), function(data){
         var obj = $.parseJSON(data);
         $('.rehub-reset-popup .rehub-errors').html(obj.message);    
         if(obj.error == false){
            window.setTimeout(function(){location.reload()},3000);  
         }
         button.removeClass('loading');
      });
   });

   // drop down for user menu
   $(document).on('click', '.user-ava-intop', function(e) {
      e.stopPropagation();
      $( this ).parent().find( '.user-dropdown-intop-menu' ).toggleClass('user-dropdown-intop-open');
      $(this).toggleClass('user-ava-intop-open');
      $(this).closest('.user-dropdown-intop').toggleClass('user-dropdown-intop-open');
   });
   $( document ).on('click', '.user-dropdown-intop-menu', function(e) {
      e.stopPropagation();
   });    
   $( document ).on('click', function() {
      $( '.user-dropdown-intop-menu' ).removeClass('user-dropdown-intop-open');
      $( '.user-dropdown-intop' ).removeClass('user-dropdown-intop-open');
      $( '.user-ava-intop' ).removeClass('user-ava-intop-open');
   }); 
});