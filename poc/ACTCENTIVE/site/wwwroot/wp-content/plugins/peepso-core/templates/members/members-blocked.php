<?php if(!get_current_user_id()) { PeepSo::redirect(PeepSo::get_page('members')); } ?>

<div class="peepso">
    <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>
    <?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>

        <section id="mainbody" class="ps-page-unstyled">
            <section id="component" role="article" class="ps-clearfix">
                <?php PeepSoTemplate::exec_template('general','wsi'); ?>
                <?php PeepSoTemplate::exec_template('members','members-tabs', array('tab'=>'blocked'));?>

                <div class="ps-members ps-js-blocked"></div>
                <div class="ps-scroll ps-js-blocked-triggerscroll">
                    <img class="post-ajax-loader ps-js-blocked-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
                </div>

            </section>
        </section>
</div>

<?php

PeepSoTemplate::exec_template('activity', 'dialogs');
