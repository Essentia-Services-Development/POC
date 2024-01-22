(function($){
   "use strict";
   jQuery(document).ready( function() {
      jQuery('#open_slink').on( "click", function() {
         jQuery('#tnc-share').fadeToggle(300)
      });
   
      jQuery(document).on("click", "#send-to-friend-btn", function(e){
         e.preventDefault();
         var formData = {
            'action'                   : 'tnc_mail_to_friend',
            'yourname'                 : jQuery('input[name=yourname]').val(),
            'friendsname'              : jQuery('input[name=friendsname]').val(),
            'youremailaddress'         : jQuery('input[name=youremailaddress]').val(),
            'friendsemailaddress'      : jQuery('input[name=friendsemailaddress]').val(),
            'email_subject'            : jQuery('input[name=email_subject]').val(),
            'message'                  : jQuery('#message').val(),
            'nonce':                   jQuery('input[name=tnc_nonce]').val(),
         };
   
         jQuery(this).prop('value', 'Sending...');
         
         var ajaxurl = jQuery('input[name=tnc_ajax]').val();
   
         jQuery.ajax({
            type : "POST",
            dataType : "JSON",
            url : ajaxurl,
            data: formData,
            success: function(response) {
               if(response.type == "success") {
                  jQuery("#email-result").html("<span style='color: green'>Email Sent Successfully</span>");
                  jQuery("#send-to-friend-btn").prop('value', 'Send Now');
               } else {
                  jQuery("#email-result").html("<span style='color: red'>Failed to Send Email, Please Try again</span>");
                  jQuery("#send-to-friend-btn").prop('value', 'Send Now');
               }
            }
         }) 
      })
   })
})(jQuery);
