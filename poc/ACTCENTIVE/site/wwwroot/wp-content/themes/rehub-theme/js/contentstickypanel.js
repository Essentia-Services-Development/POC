jQuery(document).ready(function($) {
'use strict';	

    if( $('#content-sticky-panel').length > 0 && $('.post').length > 0){

        var commenttitle = $('#comments .title_comments').first().html();
        if(commenttitle){
            $('#content-sticky-panel ul').append('<li class="top"><a href="#comments">'+commenttitle+'</a></li>');
            $('#comments .title_comments').waypoint({
                handler: function(direction) {
                    $('#content-sticky-panel a').removeClass('active');
                    $('#content-sticky-panel a[href="#comments"]').addClass('active');
                }, offset: 30
            });         
        }
        $('.kc-gotop').hide();
        $('#content-sticky-panel').on('click', '#mobileactivate', function(){
            $('#content-sticky-panel').toggleClass('mobileactive');

        });
        if ($(window).width() < 1300){
            $('#content-sticky-panel .autocontents' ).css('height', $(window).height()-90);
        }
    }
   		
});