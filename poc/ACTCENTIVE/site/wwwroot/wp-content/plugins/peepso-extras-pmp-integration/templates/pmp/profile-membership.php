<?php
$user = PeepSoUser::get_instance(PeepSoProfileShortcode::get_instance()->get_view_user_id());
if( get_current_user_id() != $user->get_id()) {
    PeepSo::redirect($user->get_profileurl());
}
?>
    <div class="peepso ps-page-profile">
        <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

        <?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>'pmp')); ?>

        <section id="mainbody" class="ps-page-unstyled">
            <section id="component" role="article" class="ps-clearfix">
                <?php
                echo do_shortcode('[pmpro_account]');
                ?>
            </section>
        </section>
    </div>

<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>