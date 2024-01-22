jQuery(document).ready(function($) {
   'use strict';
    //wishlist function
    $(document).on("click", ".heart_thumb_wrap .heartplus", function(e){
        e.preventDefault();
        var $this = $(this);
        if ($this.hasClass("restrict_for_guests")) {
            return false;
        }      
        var post_id = $(this).data("post_id");  
        var informer = parseInt($(this).attr("data-informer"));      
        $(this).addClass("loading");
        if($this.hasClass('alreadywish')){
            var wishlink = $this.data("wishlink");
            if (typeof $this.data("wishlink") !== "undefined" && $this.data("wishlink") !='' && $('.re-favorites-posts').length == 0) {
                window.location.href= $this.data("wishlink");
                return false;
            }
            var actionwishlist = 'remove';
        }else{
            var actionwishlist = 'add';
        }

        $.ajax({
            type: "post",
            url: rhscriptvars.ajax_url,
            data: "action=rhwishlist&wishnonce="+rhscriptvars.wishnonce+"&wish_count="+actionwishlist+"&post_id="+post_id
        }).done(
            function(count){
                $this.removeClass("loading"); 

                if($this.hasClass('alreadywish')){
                    $this.removeClass('alreadywish');      
                    informer=informer-1;
                    $this.closest('.heart_thumb_wrap').find('#wishcount' + post_id + '').text(informer);
                    if($('.rh-wishlistmenu-link .rh-icon-notice').length){
                        var overallcount = parseInt($('.rh-wishlistmenu-link .rh-icon-notice').html());
                        $('.rh-wishlistmenu-link .rh-icon-notice').text(overallcount - 1);
                    }
                    $this.attr("data-informer",informer);
                    if($('#wishremoved' + post_id + '').length > 0){
                       $.simplyToast($('#wishremoved' + post_id + '').html(), 'danger');
                    } 
                }else{
                    $this.addClass('alreadywish');      
                    informer=informer+1;
                    $this.closest('.heart_thumb_wrap').find('#wishcount' + post_id + '').text(informer);
                    if($('.rh-wishlistmenu-link .rh-icon-notice').length){
                        if($('.rh-wishlistmenu-link .rh-icon-notice').hasClass('rhhidden')){
                            $('.rh-wishlistmenu-link .rh-icon-notice').removeClass('rhhidden');
                            $('.rh-wishlistmenu-link .rh-icon-notice').text(1);
                        }else{
                            var overallcount = parseInt($('.rh-wishlistmenu-link .rh-icon-notice').html());
                            $('.rh-wishlistmenu-link .rh-icon-notice').text(overallcount + 1);
                        }
                    }
                    $this.attr("data-informer",informer); 
                    if($('#wishadded' + post_id + '').length > 0){
                       $.simplyToast($('#wishadded' + post_id + '').html(), 'success');
                    } 
                }                     
            }
        );
        return false;
    }); 

    //Wishlist fallback for cached sites
    if(typeof wishcached !== 'undefined'){
        var favListed = $(".heartplus");
        if(favListed.length !=0){
            $.ajax({
                type: "get",
                url: wishcached.rh_ajax_url,
                data: "action=refreshwishes&userid="+wishcached.userid,
                cache:!1
            }).done(
                function(data){
                    var wishlistids = data.wishlistids.split(',');
                    if(wishlistids.length !=0){
                        favListed.each(function(){
                            var postID = $(this).attr("data-post_id");
                            if($.inArray(postID, wishlistids) !=-1 ){
                                if($(this).hasClass('alreadywish') == false){
                                    $(this).addClass('alreadywish'); 
                                    var informer = parseInt($(this).attr("data-informer"));
                                    informer=informer+1;
                                    $(this).attr("data-informer", informer); 
                                    $(this).closest('.heart_thumb_wrap').find('#wishcount' + postID + '').text(informer);
                                }
                            }
                        });
                        if($('.rh-wishlistmenu-link .rh-icon-notice').length){
                            if($('.rh-wishlistmenu-link .rh-icon-notice').hasClass('rhhidden')){
                                $('.rh-wishlistmenu-link .rh-icon-notice').removeClass('rhhidden');
                            }
                            $('.rh-wishlistmenu-link .rh-icon-notice').text(data.wishcounter);
                        }
                    }
                }
            );
        }
    }   
}); 