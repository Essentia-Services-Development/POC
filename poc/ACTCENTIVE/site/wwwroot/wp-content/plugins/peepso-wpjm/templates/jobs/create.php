<div class="peepso">
	<div class="ps-page ps-page--jobs">
		<?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

		<div class="ps-jobs">
			<?php 
            
            PeepSoTemplate::exec_template('profile','focus', array('current'=>'jobs')); 

            if (get_current_user_id()) {
				echo do_shortcode('[submit_job_form]');
			} else {
				PeepSoTemplate::exec_template('general', 'login-profile-tab');
			} ?>
		</div>
	</div>
</div>

<?php PeepSoTemplate::exec_template('activity','dialogs'); ?>
