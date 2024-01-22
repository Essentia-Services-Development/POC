<?php

namespace FSPoster\App\Pages\Settings\Views;

use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Share posts automatically' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'When you publish a new post, the plugin shares the post on all active social accounts automatically.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_auto_share_new_posts" class="fsp-toggle-checkbox" id="fs_auto_share_new_posts"<?php echo Helper::getOption( 'auto_share_new_posts', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_auto_share_new_posts"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Share in the background' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Share posts in the background to continue your work on the website.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_share_on_background" class="fsp-toggle-checkbox" id="fs_share_on_background"<?php echo Helper::getOption( 'share_on_background', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_share_on_background"></label>
		</div>
	</div>
</div>
<div id="fspSharingTimerRow" class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'When to share the post after it is published' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Define some time with minutes to share the post after it is published. The default is 0 to share the post immediately.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<input type="number" step="1" min="0" name="fs_share_timer" class="fsp-form-input" value="<?php echo esc_html( Helper::getOption( 'share_timer', '0' ) ); ?>">
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Keep the shared post log' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'If you don\'t want to keep the shared post logs, you need to disable the option. Disabling the option prevents you view your insights and you might encounter duplicate posts when you use the schedule module.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_keep_logs" class="fsp-toggle-checkbox" id="fs_keep_logs"<?php echo Helper::getOption( 'keep_logs', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_keep_logs"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Post interval' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Define an interval between shares for each social account. Please note that this interval is between accounts for a post. The interval is not for between posts.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select name="fs_post_interval" id="fspInterval" class="fsp-form-select">
			<option value="0"<?php echo Helper::getOption( 'post_interval', '0' ) == '0' ? ' selected' : ''; ?>><?php echo fsp__( 'Immediately' ); ?></option>
			<option value="5"<?php echo Helper::getOption( 'post_interval', '0' ) == '5' ? ' selected' : ''; ?>><?php echo fsp__( '5 seconds' ); ?></option>
			<option value="10"<?php echo Helper::getOption( 'post_interval', '0' ) == '10' ? ' selected' : ''; ?>><?php echo fsp__( '10 seconds' ); ?></option>
			<option value="20"<?php echo Helper::getOption( 'post_interval', '0' ) == '20' ? ' selected' : ''; ?>><?php echo fsp__( '20 seconds' ); ?></option>
			<option value="30"<?php echo Helper::getOption( 'post_interval', '0' ) == '30' ? ' selected' : ''; ?>><?php echo fsp__( '30 seconds' ); ?></option>
			<option value="45"<?php echo Helper::getOption( 'post_interval', '0' ) == '45' ? ' selected' : ''; ?>><?php echo fsp__( '45 seconds' ); ?></option>
			<option value="60"<?php echo Helper::getOption( 'post_interval', '0' ) == '60' ? ' selected' : ''; ?>><?php echo fsp__( '1 minute' ); ?></option>
			<option value="120"<?php echo Helper::getOption( 'post_interval', '0' ) == '120' ? ' selected' : ''; ?>><?php echo fsp__( '2 minutes' ); ?></option>
			<option value="180"<?php echo Helper::getOption( 'post_interval', '0' ) == '180' ? ' selected' : ''; ?>><?php echo fsp__( '3 minutes' ); ?></option>
			<option value="240"<?php echo Helper::getOption( 'post_interval', '0' ) == '240' ? ' selected' : ''; ?>><?php echo fsp__( '4 minutes' ); ?></option>
			<option value="300"<?php echo Helper::getOption( 'post_interval', '0' ) == '300' ? ' selected' : ''; ?>><?php echo fsp__( '5 minutes' ); ?></option>
			<option value="600"<?php echo Helper::getOption( 'post_interval', '0' ) == '600' ? ' selected' : ''; ?>><?php echo fsp__( '10 minutes' ); ?></option>
			<option value="900"<?php echo Helper::getOption( 'post_interval', '0' ) == '900' ? ' selected' : ''; ?>><?php echo fsp__( '15 minutes' ); ?></option>
			<option value="1200"<?php echo Helper::getOption( 'post_interval', '0' ) == '1200' ? ' selected' : ''; ?>><?php echo fsp__( '20 minutes' ); ?></option>
			<option value="1500"<?php echo Helper::getOption( 'post_interval', '0' ) == '1500' ? ' selected' : ''; ?>><?php echo fsp__( '25 minutes' ); ?></option>
			<option value="1800"<?php echo Helper::getOption( 'post_interval', '0' ) == '1800' ? ' selected' : ''; ?>><?php echo fsp__( '30 minutes' ); ?></option>
			<option value="2400"<?php echo Helper::getOption( 'post_interval', '0' ) == '2400' ? ' selected' : ''; ?>><?php echo fsp__( '40 minutes' ); ?></option>
			<option value="3000"<?php echo Helper::getOption( 'post_interval', '0' ) == '3000' ? ' selected' : ''; ?>><?php echo fsp__( '50 minutes' ); ?></option>
			<option value="3600"<?php echo Helper::getOption( 'post_interval', '0' ) == '3600' ? ' selected' : ''; ?>><?php echo fsp__( '1 hour' ); ?></option>
			<option value="7200"<?php echo Helper::getOption( 'post_interval', '0' ) == '7200' ? ' selected' : ''; ?>><?php echo fsp__( '2 hours' ); ?></option>
			<option value="10800"<?php echo Helper::getOption( 'post_interval', '0' ) == '10800' ? ' selected' : ''; ?>><?php echo fsp__( '3 hours' ); ?></option>
			<option value="18000"<?php echo Helper::getOption( 'post_interval', '0' ) == '18000' ? ' selected' : ''; ?>><?php echo fsp__( '5 hours' ); ?></option>
		</select>
	</div>
</div>
<div id="fspIntervalLimit" class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Use the post interval option for only the same social networks' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'It prevents sharing a post on the same social network accounts at once to avoid spamming. ' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_post_interval_type" class="fsp-toggle-checkbox" id="fs_post_interval_type"<?php echo Helper::getOption( 'post_interval_type', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_post_interval_type"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Taxonomies as hashtags' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Select taxonomies that you want to share as social network hashtags. Adding the {terms} keyword to the custom message section will share selected terms as hashtags.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select class="fsp-form-input select2-init" id="fs_hashtag_taxonomies" name="fs_hashtag_taxonomies[]" multiple>
			<?php
			$selectedTaxonomies = explode( '|', Helper::getOption( 'hashtag_taxonomies', 'post_tag|category' ) );

			foreach ( get_taxonomies(['public'=>TRUE]) as $taxonomy )
			{
				echo '<option value="' . htmlspecialchars( $taxonomy ) . '"' . ( in_array( $taxonomy, $selectedTaxonomies ) ? ' selected' : '' ) . '>' . htmlspecialchars( get_taxonomy($taxonomy)->label ) . '</option>';
			}
			?>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Replace the multiple blank lines with a single blank line' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to replace multiple blank lines inside posts with a single blank line while sharing on social networks.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_multiple_newlines_to_single" class="fsp-toggle-checkbox" id="fs_multiple_newlines_to_single" <?php echo Helper::getOption( 'multiple_newlines_to_single', '0' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_multiple_newlines_to_single"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Replace "-", "&" characters and spaces in the tags with underscores' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to replace "-", "&" characters and spaces with underscores in keyword of {tags} and {categories} ( e.g., "Sport & Tech News" will be transformed to "#sport_tech_news" ). Otherwise they will be removed. ( e.g., "Sport & Tech News" will be transformed to "#sporttechnews" )' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_replace_whitespaces_with_underscore" class="fsp-toggle-checkbox" id="fs_replace_whitespaces_with_underscore"<?php echo Helper::getOption( 'replace_whitespaces_with_underscore', '0' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_replace_whitespaces_with_underscore"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
    <div class="fsp-settings-col">
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Uppercase hashtags' ); ?></div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to share social network hashtags with uppercase characters.' ); ?></div>
    </div>
    <div class="fsp-settings-col">
        <div class="fsp-toggle">
            <input type="checkbox" name="fs_uppercase_hashtags" class="fsp-toggle-checkbox" id="fs_uppercase_hashtags"<?php echo Helper::getOption( 'uppercase_hashtags', '0' ) ? ' checked' : ''; ?>>
            <label class="fsp-toggle-label" for="fs_uppercase_hashtags"></label>
        </div>
    </div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'WordPress shortcodes' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Configure how you want to share WordPress shortcodes.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select name="fs_replace_wp_shortcodes" class="fsp-form-select">
			<option value="off" <?php echo Helper::getOption( 'replace_wp_shortcodes', 'off' ) == 'off' ? 'selected' : ''; ?>><?php echo fsp__( 'Keep shortcodes as it is' ); ?></option>
			<option value="on" <?php echo Helper::getOption( 'replace_wp_shortcodes', 'off' ) == 'on' ? 'selected' : ''; ?>><?php echo fsp__( 'Replace shortcodes to their values' ); ?></option>
			<option value="del" <?php echo Helper::getOption( 'replace_wp_shortcodes', 'off' ) == 'del' ? 'selected' : ''; ?>><?php echo fsp__( 'Remove shortcodes from the post' ); ?></option>
		</select>
	</div>
</div>
