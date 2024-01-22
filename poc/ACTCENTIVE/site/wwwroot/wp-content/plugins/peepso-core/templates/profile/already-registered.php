<div class="peepso">
    <section id="mainbody" class="ps-page ps-page--register">

        <section id="component" role="article" class="ps-clearfix">
            <div class="ps-page-register cRegister">
                <h4 class="ps-page-title"><?php echo __('You are already registered and logged in', 'peepso-core'); ?></h4>
            </div><!--end cRegister-->
            <?php echo sprintf(__('Visit <a href="%s">community</a> or <a href="%s">your profile</a>', 'peepso-core'), PeepSo::get_page('activity'), PeepSoUser::get_instance()->get_profileurl()); ?>
        </section><!--end component-->

    </section>
</div>