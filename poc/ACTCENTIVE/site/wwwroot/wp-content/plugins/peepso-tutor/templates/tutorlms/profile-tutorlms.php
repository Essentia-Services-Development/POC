<div class="peepso ps-page-profile">
    <?php PeepSoTemplate::exec_template('general','navbar'); ?>

    <?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>PeepSo::get_option('tutor_navigation_profile_slug', 'courses', TRUE))); ?>

    <section id="mainbody" class="ps-page-unstyled ps-tutorlms-profile">
    <section id="component" role="article" class="ps-clearfix">


            <?php
            if(get_current_user_id()) {?>

                <div class="ps-tutorlms <?php echo PeepSo::get_option('tutor_profile_two_column_enable', 0) ? 'ps-tutorlms--half': '' ?> ps-js-tutorlms ps-js-tutorlms--<?php echo apply_filters('peepso_user_profile_id', 0); ?>"
                    style="margin-bottom:10px"></div>

                <div class="ps-scroll ps-clearfix ps-js-tutorlms-triggerscroll">
                    <img class="post-ajax-loader ps-js-tutorlms-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
                </div>

            <?php

            } else {
                PeepSoTemplate::exec_template('general','login-profile-tab');
            }
            ?>

    </section><!--end component-->
    </section><!--end mainbody-->
</div><!--end row-->
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>




