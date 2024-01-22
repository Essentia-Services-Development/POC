<?php
$PeepSoActivity = PeepSoActivity::get_instance();
$user = PeepSoUser::get_instance(PeepSoProfileShortcode::get_instance()->get_view_user_id());

$can_edit = FALSE;
if($user->get_id() == get_current_user_id() || current_user_can('edit_users')) {
	$can_edit = TRUE;
}

$args = array('post_status'=>'publish');
$user->profile_fields->load_fields($args, 'profile');
$fields = $user->profile_fields->get_fields();
$stats = $user->profile_fields->profile_fields_stats;
?>

<div class="peepso">
	<div class="ps-page ps-page--profile ps-page--profile-about">
		<?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

		<div class="ps-profile ps-profile--edit ps-profile--about">
			<?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>'about')); ?>

			<div class="ps-profile__edit">
				<?php if($can_edit) { PeepSoTemplate::exec_template('profile', 'profile-about-tabs', array('tabs'=>$tabs, 'current_tab'=> 'about')); } ?>

				<?php if( $can_edit ) { ?>
				<div class="ps-profile__progress ps-completeness-info ps-js-profile-completeness"

                    <?php if( $stats['completeness'] >= 100 && $stats['missing_required'] <= 0) { ?>
                        style="display:none"
                    <?php } ?>

                    <?php if( PeepSo::get_option_new('profile_completeness_hide_no_required_missing') && $stats['missing_required'] <= 0 ) { ?>
                        style="display:none"
                    <?php } ?>
                >
					<div class="ps-profile__progress-message ps-completeness-status ps-js-status
						<?php if(1 === PeepSo::get_option('force_required_profile_fields',0) && $stats['filled_required'] < $stats['fields_required']) { ?>
							ps-profile__progress-message--required
						<?php } ?>"

                        <?php if( $stats['completeness'] >= 100 && $stats['missing_required'] <= 0) { ?>
							style="display:none"
						<?php } ?>>

                        <?php if( PeepSo::get_option_new('profile_completeness_hide_no_required_missing') && $stats['missing_required'] <= 0 ) { ?>
                            style="display:none"
                        <?php } ?>

						<?php echo $stats['completeness_message']; ?>

						<?php
						if(isset($stats['completeness_message_detail'])) {
							echo $stats['completeness_message_detail'];
						}

						do_action('peepso_action_render_profile_completeness_message_after', $stats);
						?>
					</div>

					<div class="ps-profile__progress-bar ps-completeness-bar ps-js-progressbar" <?php if( $stats['completeness'] >= 100) { ?>style="display:none"<?php } ?>>
						<span style="width:<?php echo $stats['completeness']; ?>%"></span>
					</div>

					<div class="ps-profile__progress-required ps-missing-required-message ps-js-required" <?php if( $stats['missing_required'] <= 0) { ?>style="display:none"<?php } ?>><?php echo $stats['missing_required_message']; ?></div>
				</div>
				<?php } ?>

				<div class="ps-profile__edit-tab ps-profile__edit-tab--about" data-ps-section="profile/about">
					<div class="ps-profile__about">
						<?php if( $can_edit ) { ?>
						<div class="ps-profile__about-header">
							<div class="ps-profile__about-header-title">
								<?php echo __('Profile fields', 'peepso-core'); ?>
							</div>

							<div class="ps-profile__about-header-actions">
								<button class="ps-btn ps-btn--sm ps-btn--app ps-js-btn-edit-all"><i class="gcis gci-user-edit"></i><?php echo __('Edit All', 'peepso-core'); ?></button>
								<button class="ps-btn ps-btn ps-btn--sm ps-btn--action ps-js-btn-save-all" style="display:none"><?php echo __('Save All', 'peepso-core'); ?></button>
							</div>
						</div>
						<?php } ?>

						<div class="ps-profile__about-fields ps-js-profile-list">
							<?php if( count($fields) ) { ?>
								<?php foreach ($fields as $key => $field) {

                                    //var_dump($field->prop('meta','user_admin_editable_only'));

                                    ?>
									<?php $field_can_edit = ($can_edit && !isset($field::$user_disable_edit) && (PeepSo::is_admin() || !$field->prop('meta','user_admin_editable_only')) ); ?>
									<div class="ps-profile__about-field <?php if (TRUE == $field_can_edit) : ?> ps-profile__about-field--me <?php endif; ?> ps-js-profile-item">
										<div class="ps-profile__about-field-row ps-list-info-content">
											<div class="ps-profile__about-field-header">
												<?php if(!isset($field::$user_hide_title)) : ?>
												<div class="ps-profile__about-field-title" id="field-title-<?php echo $field->id; ?>">
													<span><?php echo __($field->title, 'peepso-core'); ?></span>
													<?php if(TRUE == $field_can_edit &&  1 == $field->prop('meta','validation','required' )) { ?>
													<span class="ps-profile__about-field-required">*</span>
													<?php } ?>
												</div>
												<?php endif;?>

												<?php if (TRUE == $field_can_edit) : ?>
												<div class="ps-profile__about-field-edit ps-list-info-content-text">
													<?php $field->render_access(); ?>
													<button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit"
														aria-label="<?php echo __('Edit ' . $field->title, 'peepso-core') ?>">
														<?php echo __('Edit', 'peepso-core'); ?>
													</button>
												</div>
												<?php endif; ?>

												<div class="ps-profile__about-field-actions ps-list-info-content-form" style="display:none">
													<button id="btn-cancel-<?php echo $field->id; ?>" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"
														role="button" aria-labelledby="btn-cancel-<?php echo $field->id; ?> field-title-<?php echo $field->id; ?>">
														<?php echo __('Cancel', 'peepso-core'); ?>
													</button>
													<button id="btn-save-<?php echo $field->id; ?>" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-save"
														role="button" aria-labelledby="btn-save-<?php echo $field->id; ?> field-title-<?php echo $field->id; ?>">
														<?php echo __('Save', 'peepso-core'); ?>
														<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" style="display:none" />
													</button>
												</div>
											</div>

											<div class="ps-profile__about-field-static ps-list-info-content-text">
												<div class="ps-profile__about-field-data ps-list-info-content-data">
													<?php $field->render(); ?>
												</div>
											</div>

											<?php if (TRUE == $field_can_edit) : ?>
											<div class="ps-profile__about-field-form ps-list-info-content-form">
												<?php do_action('peepso_action_render_profile_field_edit_before', $field); ?>
												<?php $field->render_input(); ?>

												<?php

												$field->render_validation();

												if ($field->prop('meta','privacywarning')) {
													PeepSoTemplate::exec_template('general', 'safety-warning', array(
														'message' => $field->prop('meta','privacywarningtext'),
														'id' => $field->prop('id')
													));
												}
												?>

												<div role="alert" class="ps-alert ps-alert--sm ps-alert--abort ps-list-info-content-error"></div>
											</div>
											<?php endif; ?>
										</div>
									</div>
								<?php } ?>
							<?php } else {
								echo '<div class="ps-alert">' . __('Sorry, no data to show', 'peepso-core') . '</div>';
							} ?>
						</div>

						<?php if( $can_edit ) { ?>
						<div class="ps-profile__about-footer">
							<div class="ps-profile__about-footer-actions">
								<button class="ps-btn ps-btn--sm ps-btn--app ps-js-btn-edit-all"><i class="gcis gci-user-edit"></i><?php echo __('Edit All', 'peepso-core'); ?></button>
								<button class="ps-btn ps-btn ps-btn--sm ps-btn--action ps-js-btn-save-all" style="display:none"><?php echo __('Save All', 'peepso-core'); ?></button>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="ps-dialogs" style="display:none">
	<?php $PeepSoActivity->dialogs(); // give add-ons a chance to output some HTML ?>
	<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
</div>
