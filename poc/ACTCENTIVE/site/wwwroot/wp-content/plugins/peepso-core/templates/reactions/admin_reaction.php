<?php
// If the details are not open, adjust CSS
$open_pref = get_user_meta(get_current_user_id(), 'peepso_admin_reaction_open_'.$reaction->post_id,TRUE);

// Force opening of the newly added reaction
if(isset($force_open)) {
	$open_pref = 1;
}

// get_user_meta might return an empty string
$open = (strlen($open_pref) && 1 == $open_pref) ? FALSE : 'display:none';

// if not published, dim the container
$postbox_muted 			= (0 == $reaction->published) ? 'postbox-muted' : FALSE;

// core - indeletable
// like - icon cannot be changed
$title_after = __('PeepSo Foundation', 'peepso-core');

if(1 == $reaction->custom) {
	$title_after = __('Reactions Custom', 'peepso-core');
}
?>
<div class="postbox ps-postbox--settings no-padd <?php echo $postbox_muted;?>" data-id="<?php echo $reaction->post_id;?>">

	<h3 class="hndle ps-postbox__title ui-sortable-handle ps-js-handle">

		<div class="postbox-sorting">
			<span class="fa fa-arrows"></span>
			<span class="fa fa-<?php echo ($open) ? 'expand' : 'compress' ?> ps-js-reaction-toggle"></span>
		</div>

		<div class="ps-postbox__title-label ps-js-reaction-title">

			<?php ob_start(); ?>

			<img src="<?php echo $reaction->icon_url;?>" id="ps-js-reaction-<?php echo $reaction->post_id;?>-icon">

			<?php
			// trying to avoid whitespace around img tag
//			if(0 != $reaction->id) {
				echo sprintf('<a data-id="%s" href="#" class="ps-js-reaction-icon" onclick="return false;">%s</a>', $reaction->post_id, trim(ob_get_clean()));
//			} else {
//				echo trim(ob_get_clean());
//			}
			?>
			<span id="reaction-<?php echo $reaction->post_id;?>-box-title" class="ps-postbox__title-text ps-js-reaction-title-text">
				<?php echo $reaction->title; ?>
			</span>

			<span class="fa fa-edit"></span>

			<small>
				<?php echo $title_after;?>
			</small>
		</div>

		<div class="ps-postbox__title-editor">
			<input type="text" value="<?php echo $reaction->title; ?>"
				   data-parent-id="<?php echo $reaction->post_id; ?>"
				   data-prop-type="prop"
				   data-prop-name="post_title" <?php echo (TRUE == $reaction->has_default_title) ? 'data-prop-title-is-default="1"':'';?>>

			<button class="button ps-js-btn ps-js-cancel"><?php echo __('Cancel', 'peepso-core'); ?></button>
			<button class="button button-primary ps-js-btn ps-js-save"><?php echo __('Save', 'peepso-core'); ?></button>
			<span class="ps-settings__progress ps-js-progress">
				<img src="images/wpspin_light.gif" style="display:none">
				<i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
			</span>
		</div>
	</h3>

	<div class="ps-js-reaction" data-id="<?php echo $reaction->post_id;?>" style="<?php echo $open;?>">
		<div class="ps-settings">

			<div id="cpf<?php echo $reaction->post_id;?>-tab-1" class="ps-tab__content">

				<!-- Published -->
				<?php if(0 != $reaction->id) { ?>
				<div class="ps-settings__row ps-js-reactionconf" id="reaction-<?php echo $reaction->post_id;?>-published-container">
					<div class="ps-settings__label">
						<?php echo __('Published', 'peepso-core');?>
						<?php if($reaction->has_default_title) {
							echo '<small class="warning" id="reaction-'.$reaction->post_id.'-default-title-notice">('
								. __('This Reaction will not be public until you change the default title', 'peepso-core')
								. ')</small>';
						}
						?>
						<div class="ps-settings__progress ps-js-progress">
							<img src="images/wpspin_light.gif" style="display:none">
							<i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
						</div>
					</div>

					<div class="ps-settings__controls">
						<input type="checkbox" data-prop-name="post_status" data-disabled-value="private" value="publish" admin_value="1" id="reaction-<?php echo $reaction->post_id;?>-published" <?php echo(1==$reaction->published)? 'checked="checked"':'';?> data-parent-id="<?php echo $reaction->post_id;?>" class="ace ace-switch ace-switch-2">
						<label class="lbl" for="reaction-<?php echo $reaction->post_id;?>-published"></label>	</div>
					</div>
				<?php } ?>

                <!-- Notification -->
                <div class="ps-settings__row ps-js-reactionconf" style="" id="reaction-<?php echo $reaction->post_id;?>-desc-container">
                    <div class="ps-settings__label">
                        <?php echo __('Notification text', 'peepso-core');?>
                        <div class="ps-settings__progress ps-js-progress">
                            <img src="images/wpspin_light.gif" style="display:none">
                            <i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
                        </div>
                    </div>

                    <div class="ps-settings__controls">
                        <input type="text" class="ps-reaction-notification ps-js-reaction-notiftext" data-prop-name="post_content" value="<?php echo $reaction->content;?>" id="reaction-<?php echo $reaction->post_id;?>-desc" data-parent-id="<?php echo $reaction->post_id;?>">
                        <button class="button ps-js-btn ps-js-cancel" style="display:none"><?php echo __('Cancel', 'peepso-core');?></button>
                        <button class="button button-primary ps-js-btn ps-js-save" style="display:none"><?php echo __('Save', 'peepso-core');?></button>

                        <p class="ps-reaction-hint ps-js-reaction-notifhint">
                            <?php echo PeepSoUser::get_instance()->get_firstname();?>
                            <strong class="ps-reaction-hint-inner ps-js-reaction-notifhint-text"><?php echo $reaction->content; ?></strong>
                            <?php echo sprintf(__('%s your post', 'peepso-core'), ''); ?>
                        </p>

                        <p class="ps-reaction-hint ps-reaction-hint-empty ps-js-reaction-notifhint-empty">
                            <?php echo PeepSoUser::get_instance()->get_firstname();?> <?php echo __('reacted to your post', 'peepso-core');?>
                        </p>
                    </div>


                </div>

                <?php if(PeepSo::get_option_new('reactions_emotions')) { ?>
                <!-- Emotion -->
                <div class="ps-settings__row ps-js-reactionconf" style="" id="reaction-<?php echo $reaction->post_id;?>-emotion-container">
                    <div class="ps-settings__label">
                        <?php echo __('Emotion', 'peepso-core');?> (BETA)
                        <div class="ps-settings__progress ps-js-progress">
                            <img src="images/wpspin_light.gif" style="display:none">
                            <i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
                        </div>
                    </div>

                    <div class="ps-settings__controls">
                        <select class="ps-reaction-emotion ps-js-reaction-emotiontext" data-prop-name="emotion" id="reaction-<?php echo $reaction->post_id;?>-emotion" data-parent-id="<?php echo $reaction->post_id;?>">
                            <?php
                            $options = [
                                0 => 'Neutral',
                                1 => 'Agree / Upvote',
                                -1 => 'Disagree / Downvote',
                            ];

                            foreach($options as $key=>$label) {
                                $selected = ($key == $reaction->emotion) ? "selected" : "";
                                echo "<option value=\"$key\" $selected>$label</option>";
                            }
                            ?>
                        </select>
                        <button class="button ps-js-btn ps-js-cancel" style="display:none"><?php echo __('Cancel', 'peepso-core');?></button>
                        <button class="button button-primary ps-js-btn ps-js-save" style="display:none"><?php echo __('Save', 'peepso-core');?></button>

                        <p class="ps-reaction-hint ps-reaction-hint-empty ps-js-reaction-notifhint-empty">
                            <?php echo PeepSoUser::get_instance()->get_firstname();?> <?php echo __('reacted to your post', 'peepso-core');?>
                        </p>
                    </div>


                </div>
                <?php } ?>
			</div>

			<?php if(1 == $reaction->custom) { ?>

			<div class="ps-settings__action">
				<a data-id="<?php echo $reaction->post_id; ?>" href="#" class="ps-js-reaction-delete"><i class="fa fa-trash"></i></a>
			</div>
			<?php } ?>

			<input type="hidden" id="reaction-<?php echo $reaction->post_id;?>-id" value="<?php echo $reaction->post_id;?>">
			<input type="hidden" id="reaction-<?php echo $reaction->post_id;?>-order" value="<?php echo $reaction->order;?>">
		</div>
	</div>
</div>
