// User Rate functions
jQuery(document).on('mouseenter', '.user-rate-active .starrate' , function (e) {
    var rated = jQuery(this);
    var rateditem = jQuery(this).closest('.user-rate');
    var current_rated_count = rated.attr('data-ratecount');
    if( rateditem.hasClass('rated-done') ){
      return false;
    }
    rateditem.find('.starrate').removeClass('active');
    for (i = 1; i <= current_rated_count; i++) {
        rateditem.find('.starrate'+i).addClass('active');
    }
});
jQuery(document).on('mouseleave', '.user-rate-active .starrate' , function (e) {
    var rated = jQuery(this);
    var rateditem = jQuery(this).closest('.user-rate');
    var current_rateddiv = rateditem.attr('data-rate');
    if( rateditem.hasClass('rated-done') ){
      return false;
    }
    rateditem.find('.starrate').removeClass('active');
    for (i = 1; i <= current_rateddiv; i++) {
        rateditem.find('.starrate'+i).addClass('active');
    }
});
jQuery(document).on('click', '.user-rate-active .starrate' , function (e) {
    var rated = jQuery(this);
    var rateditem = jQuery(this).closest('.user-rate');
    var current_rated_count = rated.attr('data-ratecount');    
    if( rateditem.hasClass('rated-done') ){
      return false;
    }
    rateditem.find('.post-norsp-rate').hide();
    rateditem.append('<span class="rehub-rate-load"><i class="rhicon rhi-circle-notch rhi-spin"></i></span>');
    var post_id = rateditem.attr('data-id');
    var rate_type = rateditem.attr('data-ratetype');
    var numVotes = rateditem.parent().find('.userrating-count').text();
    jQuery.post(rhscriptvars.ajax_url, { action:'rehub_rate_post' , post:post_id , type:rate_type , value:current_rated_count, security: rhscriptvars.nonce}, function(data) {
        if(data){
            var post_rateed = '.rate-post-'+post_id;
            jQuery( post_rateed ).addClass('rated-done').attr('data-rate',data);
            for (i = 1; i <= current_rated_count; i++) {
                rateditem.find('.starrate'+i).addClass('active');
            }     
            jQuery(".rehub-rate-load").fadeOut(function () {
                rateditem.parent().find('.userrating-score').html( current_rated_count );
                if( (jQuery(rateditem.parent().find('.userrating-count'))).length > 0 ){
                    numVotes =  parseInt(numVotes)+1;
                    rateditem.parent().find('.userrating-count').html(numVotes);
                }else{
                    rateditem.parent().find('small').hide();
                }
                rateditem.parent().find('strong').html(rhscriptvars.your_rating);
                rateditem.find('.post-norsp-rate').fadeIn();
            });
        }
    }, 'html');
    return false;
});