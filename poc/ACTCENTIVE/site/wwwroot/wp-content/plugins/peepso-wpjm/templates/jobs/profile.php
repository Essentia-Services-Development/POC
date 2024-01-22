<div class="peepso">
	<div class="ps-page ps-page--jobs">
		<?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

		<div class="ps-jobs">
			<?php 
            
            PeepSoTemplate::exec_template('profile','focus', array('current'=>'jobs')); 

            if (get_current_user_id()) {
                if (PeepSoWPJM_Permissions::user_can_create() && $view_user_id == get_current_user_id()) {
                $user = PeepSoUser::get_instance();

                ?>
                <div class="ps-jobs__header">
                    <div class="ps-jobs__header-inner">
                        <div class="ps-jobs__actions">
                            <a class="ps-btn ps-btn--sm ps-btn--action" href="<?php echo $user->get_profileurl() . PeepSo::get_option('wpjm_navigation_profile_slug','jobs',1).'/create/';?>">
                                <?php echo __('Create', 'peepso-wpjm');?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
                }
                echo do_shortcode('[job_dashboard posts_per_page="-1"]');
            } else {
				PeepSoTemplate::exec_template('general', 'login-profile-tab');
			} ?>
		</div>
	</div>
</div>

<?php PeepSoTemplate::exec_template('activity','dialogs'); ?>
