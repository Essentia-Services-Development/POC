window.onload = function () {
    jQuery(".elementor-widget-image-carousel").each(function(){
        var link = jQuery(this).find(".swiper-slide a").first().attr('href');
        if (typeof link !== 'undefined' && link !== null) {
            var links = link.split(';');
            var elements = jQuery(this).find(".swiper-slide:not(.swiper-slide-duplicate)");
            for (var i = elements.length - 1; i >= 0; i--) {
                if (typeof links[i] !== 'undefined' && links[i] !== null) {
                    jQuery(this).find("[data-swiper-slide-index='" + i + "'] a").attr('href',links[i]);
                } 
            }
        }
    });       
};