jQuery(document).ready(function($) {
   'use strict';    

    // Send form data
    $('body').on('click', '#rehub_add_offer_form_modal .rehub_main_btn', function(e){
        e.preventDefault();
        var error;
        var button = $(this);
        var ref = button.closest('form').find('input.required');
        var data = button.closest('form').find('input, select');
        
        // Validate form
        $(ref).each(function() {
            if ($(this).val() == '') {
                var errorfield = $(this);
                $(this).addClass('error').parent('.re-form-group').prepend('<div class="redcolor"><i class="rhicon rhi-exclamation-triangle" aria-hidden="true"></i></div>');
                error = 1;
                $(":input.error:first").focus();
                return;
            }
        });
        
        if(!(error==1)) {
            button.addClass('loading');
            $.ajax({
                type: 'POST',
                url: rhscriptvars.ajax_url,
                data: data,
                success: function() {
                    setTimeout(function(){ button.removeClass('loading'); }, 500);
                    $('.rehub-offer-popup').toggleClass('rhhidden');
                    $('.rehub-offer-popup-ok').toggleClass('rhhidden');
                    setTimeout(function(){location.reload()},500);
                },
                error: function(xhr, str) {
                    alert('Error: ' + xhr.responseCode);
                }
            });
        }
        return false;       
    }); 

});