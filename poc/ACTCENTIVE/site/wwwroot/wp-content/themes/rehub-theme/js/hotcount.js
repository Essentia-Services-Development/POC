jQuery(document).ready(function($) {
   'use strict';
    //Thumb and hot function
    $(document).on("click", ".hotcountbtn:not(.alreadyhot)", function(e){
        e.preventDefault();
        var $this = $(this);
        if ($this.hasClass('restrict_for_guests')) {
            return false;
        }       
        var post_id = $(this).data("post_id");  
        var informer = parseInt($(this).attr("data-informer"));       
        
        if($this.hasClass('thumbplus')){
            var actioncounter = 'hot';
            $this.addClass("loading");
            $this.addClass('alreadyhot').parent().find('.thumbminus').addClass('alreadyhot');
        }else if($this.hasClass('hotplus')){
            var actioncounter = 'hot';
            $this.addClass('alreadyhot').parent().parent().find('.hotminus').addClass('alreadyhot');
            $('#textinfo' + post_id + '').html("<i class='rhicon rhi-spinner fa-spin'></i>"); 
        }else if($this.hasClass('thumbminus')){
            var actioncounter = 'cold';
            $this.addClass("loading");
            $this.addClass('alreadyhot').parent().find('.thumbplus').addClass('alreadyhot');
        }else if($this.hasClass('hotminus')){
            var actioncounter = 'cold';
            $this.addClass('alreadyhot').parent().parent().find('.hotplus').addClass('alreadyhot');
            $('#textinfo' + post_id + '').html("<i class='rhicon rhi-spinner fa-spin'></i>");
        }
        $.ajax({
            type: "post",
            url: rhscriptvars.ajax_url,
            data: "action=hot-count&hotnonce="+rhscriptvars.hotnonce+"&hot_count="+actioncounter+"&post_id="+post_id
        }).done(
            function(count){
            if($this.hasClass('thumbplus')){
                $this.removeClass("loading");      
                informer=informer+1;
                $this.closest('.post_thumbs_wrap').find('#thumbscount' + post_id + '').text(informer);
                $this.attr("data-informer",informer); 
            }else if($this.hasClass('hotplus')){
                $('#textinfo' + post_id + '').html('');       
                informer=informer+1;
                $('#temperatur' + post_id + '').text(informer+"°"); 
                if(informer>rhscriptvars.max_temp){ informer=rhscriptvars.max_temp; } 
                if(informer<rhscriptvars.min_temp){ informer=rhscriptvars.min_temp; }            
                if(informer>=0){ 
                   $('#scaleperc' + post_id + '').css("width", informer / rhscriptvars.max_temp * 100+'%').removeClass('cold_bar');
                   $('#temperatur' + post_id + '').removeClass('cold_temp'); 
                }
                else {
                   $('#scaleperc' + post_id + '').css("width", informer / rhscriptvars.min_temp * 100+'%');
                }
            }else if($this.hasClass('thumbminus')){
                $this.removeClass("loading");       
                informer=informer-1;
                $this.closest('.post_thumbs_wrap').find('#thumbscount' + post_id + '').text(informer);
            }else if($this.hasClass('hotminus')){
                $('#textinfo' + post_id + '').html('');          
                informer=informer-1;
                $('#temperatur' + post_id + '').text(informer+"°");
                if(informer<rhscriptvars.min_temp){ informer=rhscriptvars.min_temp; } 
                if(informer>rhscriptvars.max_temp){ informer=rhscriptvars.max_temp; } 
                if(informer<0){ 
                   $('#scaleperc' + post_id + '').css("width", informer / rhscriptvars.min_temp * 100+'%').addClass('cold_bar');
                   $('#temperatur' + post_id + '').addClass('cold_temp'); 
                }
                else {
                   $('#scaleperc' + post_id + '').css("width", informer / rhscriptvars.max_temp * 100+'%');
                } 
            }        
        });    
        return false;
    });
}); 