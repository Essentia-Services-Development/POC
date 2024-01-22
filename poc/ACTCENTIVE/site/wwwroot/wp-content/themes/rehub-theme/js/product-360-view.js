jQuery(document).ready(function($) {
   'use strict';

        $('.rh-360-view:not(.rh-product-360)').each(function() {
            rhT360init($(this));
        });

        $('.rh-product-360-galleries .rh-360-gallery-btn').on('click', function(e) {
            e.preventDefault();
            rhT360init($('.rh-360-view.rh-product-360'));
        });

        function rhT360init($this) {
            var data = $this.data('args');

            if (!data || $this.hasClass('rh-360-view-inited')) {
                return false;
            }

            $this.ThreeSixty({
                totalFrames : data.frames_count,
                endFrame    : data.frames_count,
                currentFrame: 1,
                imgList     : '.rh-360-view-images',
                progress    : '.spinner',
                imgArray    : data.images,
                height      : data.height,
                width       : data.width,
                responsive  : true,
                navigation  : true,
                position    : 'bottom-center' 
            });

            $this.addClass('rh-360-view-inited');
        }
});