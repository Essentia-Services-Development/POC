jQuery(document).ready(function($) {

   'use strict';
    if($('.rh-video-canvas').length){
        $('.rh-video-canvas').each(function(){
            let el = $(this);
            var inittype = (typeof el.data("loaditer") !=='undefined') ? el.data("loaditer") : '';
            if(inittype){
                const body = document.body;
                body.addEventListener("mouseover", rhloadVideo(el), {once:true});
                body.addEventListener("touchmove", rhloadVideo(el), {once:true});
                body.addEventListener("scroll", rhloadVideo(el), {once:true});
                body.addEventListener("keydown", rhloadVideo(el), {once:true});
            }else{
                $(document).on('inview', '.rh-video-canvas', function(e){
                    rhloadVideo(el);
                });
            }
        });
        // Play video when page resizes
        $(window).on("resize", function() {
            $('.rh-video-canvas').each(function(){
                let el = $(this);
                rhloadVideo(el);
            });
        });
    }
    function rhloadVideo(el) {
        var videocurrent = el;

        var mainbreakpoint = (typeof videocurrent.data("breakpoint") !=='undefined') ? parseInt(videocurrent.data("breakpoint")) : 300;
        var tabletbreakpoint = 1024;
        var mobilebreakpoint = 768;

        var fallbackposter = (typeof videocurrent.data("fallback") !=='undefined') ? videocurrent.data("fallback") : '';
        var tabletposter = (typeof videocurrent.data("fallback-tablet") !=='undefined') ? videocurrent.data("fallback-tablet") : '';
        var mobileposter = (typeof videocurrent.data("fallback-mobile") !=='undefined') ? videocurrent.data("fallback-mobile") : '';     

        var mp4source = (typeof videocurrent.data("mp4") !=='undefined') ? videocurrent.data("mp4") : '';
        var ogvsource = (typeof videocurrent.data("ogv") !=='undefined') ? videocurrent.data("ogv") : '';
        var webmsource = (typeof videocurrent.data("webm") !=='undefined') ? videocurrent.data("webm") : '';

        var isgsaptrigger = (typeof videocurrent.parent().attr("data-videoplay") !=='undefined') ? true : false;

        // Add source tags if not already present
        if ($(window).width() > mainbreakpoint) {
            if (videocurrent.find('source').length < 1) {
                if(mp4source){
                    var source1 = document.createElement('source');
                    source1.setAttribute('src', mp4source);
                    source1.setAttribute('type', 'video/mp4');
                    videocurrent.append(source1);                           
                }

                if(webmsource){
                    var source2 = document.createElement('source');
                    source2.setAttribute('src', webmsource);
                    source2.setAttribute('type', 'video/webm');
                    videocurrent.append(source2);                           
                }

                if(ogvsource){
                    var source3 = document.createElement('source');
                    source3.setAttribute('src', ogvsource);
                    source3.setAttribute('type', 'video/ogg');
                    videocurrent.append(source3);                           
                }                                               
            }

        }

        // Remove existing source tags for mobile
        if ($(window).width() <= mainbreakpoint) {
            videocurrent.find('source').remove();
            if(fallbackposter){
                videocurrent.attr('poster', fallbackposter);
            }
        }               

        if(tabletposter && $(window).width() <= tabletbreakpoint){
            videocurrent.attr('poster', tabletposter);
        }
        if(mobileposter && $(window).width() <= mobilebreakpoint){
            videocurrent.attr('poster', mobileposter);
        }               
    }

});