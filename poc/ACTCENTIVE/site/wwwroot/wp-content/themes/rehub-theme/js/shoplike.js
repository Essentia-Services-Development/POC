jQuery(document).ready(function($) {
   'use strict';    

    $(document).on("click", ".rh-user-favor-shop", function(e){
        e.preventDefault();
        var heart = $(this);
        var user_id = heart.data("user_id");
        heart.find(".favorshop_like").html("<i class='rhicon rhi-spinner fa-spin'></i>");
        
        $.ajax({
            type: "post",
            url: rhscriptvars.ajax_url,
            data: "action=rh-user-favor-shop&favornonce="+wooscriptvars.favornonce+"&rh_user_favorite_shop=&user_id="+user_id
        }).done(function(count){
            if( count.indexOf( "already" ) !== -1 )
            {
                var lecount = count.replace("already","");
                if (lecount == 0)
                {
                    var lecount = "0";
                }
                heart.find(".favorshop_like").html("<i class='rhicon rhi-heart'></i>");
                heart.removeClass("alreadyinfavor");
                heart.find(".count").text(lecount);
            }
            else
            {
                heart.find(".favorshop_like").html("<i class='rhicon rhi-heart-solid'></i>");
                heart.addClass("alreadyinfavor");
                heart.find(".count").text(count);
            }
        });
        
        return false;
    }); 

});