<div class="ps-giphy__wrapper">
    <div class="ps-giphy__preview ps-js-giphy-preview">
        <img />
        <br>
        <a href="#" class="ps-giphy__change ps-btn ps-btn--sm ps-btn--app ps-js-giphy-change"><?php echo __('Change image', 'peepso-core'); ?></a>
    </div>
    <div class="ps-giphy ps-giphy--slider ps-js-giphy-container">
        <div class="ps-giphy__search">
            <input type="text" class="ps-input ps-input--sm ps-giphy__input ps-js-giphy-query"
            placeholder="<?php echo __('Search...', 'peepso-core') ?>" style="display:none" />
            <div class="ps-giphy__powered">
                <a href="https://giphy.com/" target="_blank"></a>
            </div>
        </div>

        <div class="ps-giphy__loading ps-loading ps-js-giphy-loading">
            <i class="gcis gci-circle-notch gci-spin"></i>
        </div>

        <div class="ps-giphy__slider ps-js-slider">
            <div class="ps-giphy__slides ps-js-giphy-list"></div>

            <script type="text/template" class="ps-js-giphy-list-item">
                <div class="ps-giphy__slide ps-giphy__slides-item ps-js-giphy-item">
                    <img class="ps-giphy__slide-image" src="{{= data.preview }}" data-id="{{= data.id }}" data-url="{{= data.src }}" />
                </div>
            </script>

            <div class="ps-giphy__nav ps-giphy__nav--left ps-js-giphy-nav-left"><i class="gcis gci-chevron-left"></i></div>
            <div class="ps-giphy__nav ps-giphy__nav--right ps-js-giphy-nav-right"><i class="gcis gci-chevron-right"></i></div>
        </div>
    </div>
</div>
