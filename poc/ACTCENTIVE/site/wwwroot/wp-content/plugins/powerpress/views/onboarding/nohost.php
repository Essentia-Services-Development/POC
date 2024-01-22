<?php
$GeneralSettings = powerpress_get_settings('powerpress_general');
$creds = get_option('powerpress_creds');
if ((isset($GeneralSettings['blubrry_auth']) && $GeneralSettings['blubrry_auth'] != null) || $creds) {
    $next_page = 'createEpisode';
} else {
    $next_page = 'wantStats';
}
if (isset($_GET['from']) && $_GET['from'] == 'import') {
    $querystring_import = "&from=import";
} else {
    $querystring_import = "";
}
$pp_nonce = powerpress_login_create_nonce();
?>
<div class="wrap">
    <div class="pp_container">
        <h2 class="pp_align-center"><?php echo __('Would you like to host with Blubrry?', 'powerpress'); ?></h2>
        <h5 class="pp_align-center"><?php echo __('Don’t know what a podcast host is?', 'powerpress'); ?> <a style="color:blue" href="https://blubrry.com/manual/internet-media-hosting/"><?php echo __('Learn more', 'powerpress'); ?></a></h5>
        <hr  class="pp_align-center" />

        <section id="one" class="pp_wrapper" style="margin-top:25px;">
            <div class="pp_inner">

                <div class="pp_flex-grid">
                    <div class="pp_col" style="margin-top: -1px;">
                        <div class="pp_box pp_service-container">
                            <div class="pp_image center">
                                <img src="<?php echo powerpress_get_root_url(); ?>images/onboarding/BlubrryBannerLogo.png" alt="" />
                            </div>
                            <div class="pp_content">
                                <div class="btn-caption-container">
                                    <p class="pp_align-center"><?php echo __('Integrated within PowerPress', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('Uploaded audio directly in your episode post', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('Publish your show directly on this website', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('World-class tech support, phone or email', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('Includes Standard Statistics', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('Mobile-ready audio and video player', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('Free file migration', 'powerpress'); ?></p>
                                </div>
                                <div class="pp_button-container">
                                    <a href="<?php echo add_query_arg( '_wpnonce', $pp_nonce, admin_url("admin.php?page={$_GET['page']}&step=blubrrySignup&onboarding_type=hosting$querystring_import")); ?>">
                                        <button type="button" class="pp_button"><span><?php echo __('Free Hosting Trial', 'powerpress'); ?></span></button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pp_col">
                        <div class="pp_box">
                            <div class="pp_image pp_fit center">
                                <img src="<?php echo powerpress_get_root_url(); ?>images/onboarding/self_host.png" alt="" class="" />
                            </div>
                            <div class="pp_content">
                                <!--<div class="pp_align-center">-->
                                    <div class="btn-caption-container">
                                        <p class="pp_align-center"><?php echo __('I don\'t need audio/video file hosting', 'powerpress'); ?></p>
                                    </div>
                                    <div class="pp_button-container">
                                        <a href="<?php echo admin_url("admin.php?page={$_GET['page']}&step=$next_page"); ?>">
                                            <button type="button" class="pp_button_alt skip_blubrry"><span><?php echo __('SKIP', 'powerpress'); ?></span></button>
                                        </a>
                                    </div>
                                <!--</div>-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>