jQuery(document).ready(function($) {
    /* tabs */
    $('.tabs-menu').on('click', 'li:not(.current)', function() {
        var tabcontainer = $(this).closest('.tabs');
        if(tabcontainer.length == 0) {
            var tabcontainer = $(this).closest('.elementor-widget-wrap');
        }
        $(this).addClass('current elementor-active').siblings().removeClass('current elementor-active');

        tabcontainer.find('.tabs-item').hide().removeClass('stuckMoveDownOpacity').eq($(this).index()).show().addClass('stuckMoveDownOpacity');   
   });
   $('.tabs-menu li:first-child').trigger('click');
});