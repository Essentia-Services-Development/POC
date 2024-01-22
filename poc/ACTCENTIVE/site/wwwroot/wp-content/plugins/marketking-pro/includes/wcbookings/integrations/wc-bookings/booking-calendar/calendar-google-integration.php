<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*

Profile Settings Page
* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/

$user_id = get_current_user_id();

if ( marketking()->is_vendor_team_member() ) {
	$user_id = marketking()->get_team_member_parent();
}

if ( isset( $_POST[ WC_Marketking_Bookings_Google_Calendar_Integration::instance()->get_field_key() ] ) ) {

	do_action(
		'disconnect_google_account_integration',
		sanitize_text_field( wp_unslash( $_POST[ WC_Marketking_Bookings_Google_Calendar_Integration::instance()->get_field_key() ] ) )
	);
}

?><?php
if ( marketking()->vendor_has_panel( 'profile-settings' ) ) {
	?>
	<div class="nk-content marketking_profile_settings_page">
		<div class="container-fluid">
			<div class="nk-content-inner">
				<div class="nk-content-body">
					<div class="nk-block">
						<div class="card">
							<div class="card-aside-wrap">
								<div class="card-inner card-inner-lg">
									<div class="nk-block-head nk-block-head-lg">
										<div class="nk-block-between">
											<div class="nk-block-head-content">
												<h4 class="nk-block-title"><?php esc_html_e( 'Google Calendar Integration', 'marketking' ); ?></h4>
											</div>
											<div class="nk-block-head-content align-self-start d-lg-none">
												<a href="#" class="toggle btn btn-icon btn-trigger mt-n1" data-target="userAside">
													<em class="icon ni ni-menu-alt-r"></em>
												</a>
											</div>
										</div>
									</div>

									<div class="nk-block-head nk-block-head-sm">
										<div class="nk-block-head-content">
											<h6><?php esc_html_e( 'Calendar Connection', 'marketking' ); ?></h6>
											<p><?php esc_html_e( 'Sync one or both ways between your Store and Google Calendar', 'marketking' ); ?></p>
										</div>
									</div><!-- .nk-block-head -->
									<div class="nk-block-content">
										<div class="gy-3">
											<div class="g-item">
												<?php

												//												WC_Marketking_Bookings_Google_Calendar_Connection::generate_form_html();
												?>
												<form method="post" id="mainform" action=""
												      enctype="multipart/form-data">
													<table class="form-table">
														<tbody>
														<?php

														WC_Marketking_Bookings_Google_Calendar_Integration::generate_form_html();

														?>
														</tbody>
													</table>
												</form>
											</div>

										</div>
									</div><!-- .nk-block-content -->
									<br><br>

								</div>
								<?php

								include( apply_filters( 'marketking_dashboard_template', 'profile-sidebar.php' ) );

								?>
							</div><!-- .card-inner -->
						</div><!-- .card-aside-wrap -->
					</div><!-- .nk-block -->
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>