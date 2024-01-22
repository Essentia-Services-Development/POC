<?php if(TRUE === apply_filters('peepso_permissions_post_create', TRUE)) {
    $placeholder = isset($placeholder) ? $placeholder : __(apply_filters('peepso_postbox_message', 'Say what is on your mind...'), 'peepso-core');
    ?>
    <div class="ps-postbox__status ps-postbox-status">
        <div class="ps-postbox__status-wrapper">
            <div class="ps-postbox__status-inner">
                <span class="ps-postbox__status-mirror ps-postbox-mirror"></span>
                <span class="ps-postbox__status-addons ps-postbox-addons"></span>
            </div>
            <div class="ps-postbox__input-wrapper ps-postbox-input ps-inputbox">
                <textarea class="ps-postbox__input ps-textarea ps-postbox-textarea" placeholder="<?php echo $placeholder ?>"></textarea>
            </div>
        </div>
        <div class="ps-postbox__chars-count ps-postbox-charcount ps-js-charcount" style="display:none"><?php echo PeepSo::get_option('site_status_limit', 4000) ?></div>
    </div>
<?php } else { PeepSoTemplate::exec_template('general','postbox-permission-denied'); }// peepso_permissions_post_create ?>
