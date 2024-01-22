jQuery(document).ready(function($) {
   'use strict';
    //Thumb and hot function
    $(document).on("click", ".us-rev-vote-up:not(.alreadycomment)", function(e){
        e.preventDefault();
        var post_id = $(this).data("post_id");  
        var informer = $(this).data("informer");
        $(this).addClass('alreadycomment').parent().find('.us-rev-vote-down').addClass('alreadycomment');
        $('#commhelp' + post_id + ' .rhi-thumbs-up').removeClass('rhi-thumbs-up').addClass('rhi-spinner fa-spin');            
        $.ajax({
            type: "post",
            url: rhscriptvars.ajax_url,
            data: "action=commentplus&cplusnonce="+rhscriptvars.helpnotnonce+"&comm_help=plus&post_id="+post_id
        }).done(
            function(count){
                $('#commhelp' + post_id + ' .rhi-spinner').removeClass('rhi-spinner fa-spin').addClass('rhi-thumbs-up');        
                informer=informer+1;
                $('#commhelpplus' + post_id + '').text(informer);         
            }
        );
      
        return false;
    });

    $(document).on("click", ".us-rev-vote-down:not(.alreadycomment)", function(e){
        e.preventDefault();
        var post_id = $(this).data("post_id");  
        var informer = $(this).data("informer");
        $(this).addClass('alreadycomment').parent().find('.us-rev-vote-up').addClass('alreadycomment');
        $('#commhelp' + post_id + ' .rhi-thumbs-down').removeClass('rhi-thumbs-down').addClass('rhi-spinner fa-spin');
        $.ajax({
            type: "post",
            url: rhscriptvars.ajax_url,
            data: "action=commentplus&cplusnonce="+rhscriptvars.helpnotnonce+"&comm_help=minus&post_id="+post_id
        }).done(
            function(count){
                $('#commhelp' + post_id + ' .rhi-spinner').removeClass('rhi-spinner fa-spin').addClass('rhi-thumbs-down');            
                informer=informer+1;
                $('#commhelpminus' + post_id + '').text(informer);           
            }
        );
        return false;
    });

    $(document).on("click", ".alreadycomment", function(e){
      $(this).parent().find('.already_commhelp').fadeIn().fadeOut(1000);
    });
}); 