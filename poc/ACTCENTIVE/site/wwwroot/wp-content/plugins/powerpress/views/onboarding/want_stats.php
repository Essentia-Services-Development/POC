<?php
$pp_nonce = powerpress_login_create_nonce();
?>
<div class="wrap">
    <div class="pp_container">
        <h2 class="pp_align-center"><?php echo __('Gain access to free tools', 'powerpress'); ?></h2>
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
                                <!--<div class="pp_align-center">-->
                                <div class="btn-caption-container">
                                    <p class="pp_align-center"><?php echo __('60,000 podcasters trust Blubrry', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('Measure your audience', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('Geographic data', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('Apps and device comparison', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('Show and episode numbers', 'powerpress'); ?></p>
                                    <p class="pp_align-center"><?php echo __('Date range analysis', 'powerpress'); ?></p>
                                    <p>
                                        <img class="iab_img" src="<?php echo powerpress_get_root_url(); ?>images/onboarding/iab_badge.png" alt="IAB Certified" />
                                    </p>
                                </div>
                                <div class="pp_button-container">
                                    <a href="<?php echo add_query_arg( '_wpnonce', $pp_nonce, admin_url("admin.php?page={$_GET['page']}&step=blubrrySignup&onboarding_type=stats")); ?>">
                                        <button type="button" class="pp_button" style="margin-top: 1em;"><span><?php echo __('Free Stats', 'powerpress');?> <br /> <?php echo __('Sign up today', 'powerpress'); ?></span></button>
                                    </a>
                                </div>
                                <!--</div>-->
                            </div>
                        </div>
                    </div>
                    <div class="pp_col">
                        <div class="pp_box">
                            <div class="pp_image pp_fit center">
                                <img src="<?php echo powerpress_get_root_url(); ?>images/onboarding/free_tools.png" alt="" class="" />
                            </div>
                            <div class="pp_content">
                                <!--<div class="pp_align-center">-->
                                <div class="btn-caption-container">
                                    <p class="pp_align-center"><?php echo __('I don\'t need free, accurate statistics', 'powerpress'); ?></p>
                                </div>
                                <div class="pp_button-container">
                                    <a href="<?php echo admin_url("admin.php?page={$_GET['page']}&step=createEpisode"); ?>">
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