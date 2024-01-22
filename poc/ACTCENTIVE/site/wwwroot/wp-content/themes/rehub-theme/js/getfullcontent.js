jQuery(document).ready(function($) {
   'use strict';    

    //AJAX GET FULL CONTENT
    $('body').on('click', '.showmefulln', function(e){
        e.preventDefault();
        var $this = $(this);        
        var postid = $this.data('postid'); 
        var aj_get_full_enabled = $this.attr('data-enabled');      
        var data = {
            'action': 're_getfullcontent',          
            'postid': postid,    
            'security' : rhscriptvars.nonce
        };
        var newshead = $this.parent().find('.newsimage');
        var newscont = $this.parent().find('.newsdetail');      
        var newsheadfull = $this.parent().find('.newscom_head_ajax');
        var newscontfull = $this.parent().find('.newscom_content_ajax');    
        var newsbtn = $this.parent().find('.newsbtn').html();   
        var headcontent = $this.parent().find('.newstitleblock').html();                    

        if(aj_get_full_enabled==1) {
            newsheadfull.fadeOut(500, function() {
                newshead.fadeIn(500);
                $this.attr('data-enabled', 2).removeClass('compress');                  
            });
            newscontfull.fadeOut(500, function() {
                newscont.fadeIn(500);
            });                             
        }
        else if (aj_get_full_enabled==2){
            newshead.hide(10);
            newscont.hide(10);
            newsheadfull.fadeIn(1000);
            newscontfull.fadeIn(1000);
            $this.attr('data-enabled', 1).addClass('compress');
        }
        else {
            $this.addClass('re_loadingafter');
            $.ajax({
                type: "POST",
                url: rhscriptvars.ajax_url,
                data: data,
                success: function(response){
                    if (response !== 'fail') {
                        newscont.hide(10);
                        newshead.hide(10);
                        newscontfull.html($(response).hide().fadeIn(1000).append(newsbtn));
                        newsheadfull.html($(headcontent).hide().fadeIn(1000));      
                        newscontfull.find('.rate-bar').each(function(){
                            $(this).find('.rate-bar-bar').animate({ width: $(this).attr('data-percent') }, 1500 );
                        });                                                         
                    }   
                    $this.attr('data-enabled', 1).removeClass('re_loadingafter').addClass('compress');     
                }
            });                         
        }       
    });

});