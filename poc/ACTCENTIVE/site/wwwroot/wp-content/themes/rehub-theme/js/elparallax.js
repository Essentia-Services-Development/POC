(function($) {
   'use strict';
    function multiParallax() {       
        //BG parallax
        if($('.rh-parallax-bg-true').length > 0){
            var scrollTop = $(window).scrollTop();
            $('.rh-parallax-bg-true').each(function() {
                var paralasicValue = $(this).prop('class').match(/rh-parallax-bg-speed-([0-9]+)/)[1];
                var paralasicValue = parseInt(paralasicValue)/100;
                var backgroundPos = $(this).css('backgroundPosition').split(" ");
                if (backgroundPos[0] == '100%'){
                    var bgx = 'right';
                }
                else if (backgroundPos[0] == '50%'){
                    var bgx = 'center';
                }
                else if (backgroundPos[0] == '0%'){
                    var bgx = 'left';
                }else{
                    var bgx = backgroundPos[0];
                } 
                if (backgroundPos[1] == '0%'){
                    var bgy = 'top';
                }
                else if (backgroundPos[1] == '50%'){
                    var bgy = 'center';
                }
                else if (backgroundPos[1] == '100%'){
                    var bgy = 'bottom';
                } 
                else{
                    var bgy = backgroundPos[1];
                }                                                              
                $(this).css('background-position', ''+bgx+' '+bgy+' -' + scrollTop * paralasicValue + 'px');
            }); 
        }       
    }   
    $(window).on('load scroll', function() {
        multiParallax();
    }); 
})(jQuery);