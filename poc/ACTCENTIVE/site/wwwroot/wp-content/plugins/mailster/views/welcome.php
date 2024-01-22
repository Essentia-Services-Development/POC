<div class="wrap mailster-welcome-wrap">

	<h1><?php printf( esc_html__( 'Welcome to %s', 'mailster' ), 'Mailster 3.0' ); ?></h1>

	<div class="about-text">
		<?php esc_html_e( 'Send Beautiful Email Newsletters in WordPress.', 'mailster' ); ?><br>
	</div>

	<div class="mailster-badge"><?php printf( esc_html__( 'Version %s', 'mailster' ), MAILSTER_VERSION ); ?></div>

	<div class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'admin.php?page=mailster_welcome' ); ?>" class="nav-tab nav-tab-active"><?php esc_html_e( 'What\'s New', 'mailster' ); ?></a>
		<?php if ( current_user_can( 'mailster_manage_templates' ) ) : ?>
		<a href="<?php echo admin_url( 'edit.php?post_type=newsletter&page=mailster_templates' ); ?>" class="nav-tab"><?php esc_html_e( 'Templates', 'mailster' ); ?></a>
		<?php endif; ?>
		<?php if ( current_user_can( 'mailster_manage_addons' ) ) : ?>
		<a href="<?php echo admin_url( 'edit.php?post_type=newsletter&page=mailster_addons' ); ?>" class="nav-tab"><?php esc_html_e( 'Add Ons', 'mailster' ); ?></a>
		<?php endif; ?>
	</div>


	<div class="feature-section two-col">
		<div class="col">
			<div class="media-container">
				<img src="https://static.mailster.co/welcome/icons.png">
			</div>
			<h3>New Icons</h3>
			<p>We have updated our UI to give Mailster a more unique and modern look. Goodbye Font Icons &ndash; Hello SVG!</p>
			<div class="return-to-dashboard"></div>
		</div>
		<div class="col">
			<div class="media-container">
				<img src="https://static.mailster.co/welcome/precheck.png">
			</div>
			<h3>Test your Email Quality.</h3>
			<p>You can give your email campaign a thorough pre-check before sending it out. Mailster gives you feedback on what you should improve.</p>
			<div class="return-to-dashboard"></div>
		</div>
	</div>

	<div class="feature-section two-col">
		<div class="col">
			<div class="media-container">
				<img src="https://static.mailster.co/welcome/subscriber-tags.png">
			</div>
			<h3>Tags</h3>
			<p>Next to lists and custom fields you can now tag your subscribers to organize them even better.</p>
			<div class="return-to-dashboard"></div>
		</div>
		<div class="col">
			<div class="media-container">
				<img src="https://static.mailster.co/welcome/default-template.png">
			</div>
			<h3>New Default Template</h3>
			<p>We created a <strong>brand new email template</strong> exclusively for Mailster. With 15 modules and 8 color variations you get a great starter.</p>
			<div class="return-to-dashboard"><a href="<?php echo admin_url( 'edit.php?post_type=newsletter&page=mailster_templates&search=mailster&type=slug' ); ?>">Download now</a></div>
		</div>
	</div>


	<div class="feature-section two-col">
		<div class="col">
			<div class="media-container">
				<img src="https://static.mailster.co/welcome/templates.png">
			</div>
			<h3>Templates</h3>
			<p>You can browse now over <strong>400 supported email templates</strong> right in the plugin.</p>
			<div class="return-to-dashboard"><a href="<?php echo admin_url( 'edit.php?post_type=newsletter&page=mailster_templates&browse=new' ); ?>">Browse Templates</a></div>
		</div>
		<div class="col">
			<div class="media-container">
				<img src="https://static.mailster.co/welcome/addons.png">
			</div>
			<h3>Add Ons</h3>
			<p>Next to the tempate section we have also updated the add ons page. Find great extensions is now easier.</p>
			<div class="return-to-dashboard"><a href="<?php echo admin_url( 'edit.php?post_type=newsletter&page=mailster_addons&browse=all' ); ?>">Browse Add Ons</a></div>
		</div>
	</div>

	<div class="changelog">
		<h2>Further Improvements</h2>

		<div class="feature-section under-the-hood three-col">
			<div class="col">
				<h4>Security</h4>
				<p>We help you prevent false signups out of the box. It's now easy to block certain bots right from the <a href="<?php echo admin_url( 'edit.php?post_type=newsletter&page=mailster_settings#security' ); ?>">settings page</a>.</p>
			</div>
			<div class="col">
				<h4>PHP 8 Support.</h4>
				<p>Mailster now officially supports PHP 8 which brings an additional speed boost to your website.</p>
			</div>
			<div class="col">
				<h4>Auto Cron Settings.</h4>
				<p>Mailster can calculate your sending rate <strong>automatically</strong> which increases the average throughput about 25%.</p>
			</div>
		<div class="feature-section under-the-hood three-col">
			<div class="col">
				<h4>Improved Table Structure.</h4>
				<p>Mailster 3.0 introduces a few new tables which speeds up database queries and queue processing.</p>
			</div>
			<div class="col">
				<h4>Action Hook Campaigns.</h4>
				<p>You can now let Mailster create campaigns on custom action hooks for more flexibility with custom coding.</p>
			</div>
			<div class="col">
				<h4>Event more under the hood.</h4>
				<p>We have refactored many parts of the code to make Mailster your reliable partner in the future.</p>
			</div>
		</div>

	</div>
	<div class="clear"></div>

	<div class="return-to-dashboard">
		<a href="<?php echo admin_url( 'admin.php?page=mailster_dashboard' ); ?>">Back to Dashboard</a>
	</div>

<div class="clear"></div>

<div id="ajax-response"></div>
<br class="clear">
</div>
