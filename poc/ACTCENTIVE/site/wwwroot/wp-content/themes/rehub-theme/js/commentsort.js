jQuery(document).ready(function($) {
   'use strict';    

   //Comment filtering
   $(document).on('click', '#rehub-comments-tabs span', function() {
      if(typeof rating_tabs_ajax_send!=='undefined' && rating_tabs_ajax_send)
         return;
      var post_id = $('#rehub-comments-tabs').data('postid');
      var rating_tabs_ajax_send = true;
      var p = $(this).parent().children().removeClass('active');
      $(this).addClass('active');
      if ($(this).data('tabid')==1) {
         $('#loadcomment-list').html('');
         $('#tab-1').show();
         rating_tabs_ajax_send = false;
         return;
      }
      $.ajax({
         type: 'post',
         data: 'action=show_tab&post_id='+post_id+'&tab_number='+$(this).data('tabid')+'&posttype='+$(this).data('posttype')+'&rating_tabs_id='+rhscriptvars.rating_tabs_id,
         url: rhscriptvars.ajax_url,
         beforeSend: function() {
            $('#tab-1').hide();
            $('#loadcomment-list').html('<div class="text-center loadingcomment"><i class="rhicon rhi-sync rhi-spin"></i></div>');
         },
         error: function(jqXHR, textStatus, errorThrown) {
            $('#loadcomment-list').html('error: '+errorThrown);
         },
         success : function(html_data) {
            rating_tabs_ajax_send = false;
            $("#loadcomment-list").html(html_data);
         }
      });
   }); 

});