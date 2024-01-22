jQuery(document).ready(function () {
    var $container = jQuery('.affegg-masonry');
    $container.imagesLoaded(function () {
        $container.masonry({
            itemSelector: '.item',
            columnWidth: 230,
            isAnimated: true
        });
    });
});