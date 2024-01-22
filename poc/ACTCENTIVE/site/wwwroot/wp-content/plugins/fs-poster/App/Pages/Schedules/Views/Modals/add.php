<?php

namespace FSPoster\App\Pages\Schedules\Views;

use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'MODAL' ) or exit;
?>

<?php if ( ! ( isset ( $fsp_params[ 'is_direct_share_tab' ] ) && $fsp_params[ 'is_direct_share_tab' ] === TRUE ) ) { ?>
	<script>
		jQuery( document ).ready( function () {
			FSPoster.load_script( '<?php echo Pages::asset( 'Base', 'js/fsp-metabox.js' ); ?>' );
		} );
	</script>
	<link rel="stylesheet" href="<?php echo Pages::asset( 'Base', 'css/fsp-metabox.css' ); ?>">
<?php } ?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fas fa-plus"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo $fsp_params[ 'title' ]; ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body schedule_popup">
	<input type="hidden" id="fspKeepLogs" value="<?php echo Helper::getOption( 'keep_logs', 1 ) == 1 ? 'on' : 'off'; ?>">
	<input type="hidden" id="fspScheduleID" value="<?php echo isset( $fsp_params[ 'info' ] ) ? $fsp_params[ 'info' ][ 'id' ] : 0; ?>">

	<?php if ( ! ( isset ( $fsp_params[ 'is_direct_share_tab' ] ) && $fsp_params[ 'is_direct_share_tab' ] === TRUE ) ) { ?>
		<div id="fspAddSchedule" class="fsp-modal-tabs">
			<?php if ( ! ( isset( $fsp_params[ 'is_native' ] ) && $fsp_params[ 'is_native' ] === TRUE ) ) { ?>
				<div class="fsp-modal-tab" data-step="1">
					<span><?php echo fsp__( '1' ); ?></span><?php echo fsp__( 'Basic data' ); ?>
				</div>
				<div class="fsp-modal-tab" data-step="2">
					<span><?php echo fsp__( '2' ); ?></span><?php echo fsp__( 'Filters' ); ?>
				</div>
				<div class="fsp-modal-tab" data-step="3">
					<span><?php echo fsp__( '3' ); ?></span><?php echo fsp__( 'Accounts' ); ?>
				</div>
				<div class="fsp-modal-tab" data-step="4">
					<span><?php echo fsp__( '4' ); ?></span><?php echo fsp__( 'Custom messages' ); ?>
				</div>
			<?php } else { ?>
				<div class="fsp-modal-tab" data-step="3">
					<span><?php echo fsp__( '1' ); ?></span><?php echo fsp__( 'Accounts' ); ?>
				</div>
				<div class="fsp-modal-tab" data-step="4">
					<span><?php echo fsp__( '2' ); ?></span><?php echo fsp__( 'Custom messages' ); ?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>

	<?php if ( ! ( isset( $fsp_params[ 'is_native' ] ) && $fsp_params[ 'is_native' ] === TRUE ) ) { ?>
		<div id="fspAddSchedule_1" class="fspAddSchedule-step">
			<div class="fsp-form-group">
				<label><?php echo fsp__( 'Name' ); ?>&emsp;<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'Add a name for your schedule to recognize it in your schedule list.' ); ?>"></i>
				</label>
				<input autocomplete="off" class="fsp-form-input schedule_input_title" value="<?php echo isset( $fsp_params[ 'info' ][ 'title' ] ) ? esc_html( $fsp_params[ 'info' ][ 'title' ] ) : ( isset( $fsp_params[ 'name' ] ) ? esc_html( $fsp_params[ 'name' ] ) : '' ); ?>" placeholder="<?php echo fsp__( 'Enter a name' ); ?>">
			</div>
			<div class="fsp-form-group">
				<label><?php echo fsp__( 'Start date & time' ); ?>&emsp;<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'When the schedule will start?' ); ?>"></i>
				</label>
				<div class="fsp-modal-row">
					<div class="fsp-modal-col">
						<input type="date" autocomplete="off" class="fsp-form-input schedule_input_start_date" placeholder="<?php echo fsp__( 'Select date' ); ?>" value="<?php echo Date::datee( isset( $fsp_params[ 'info' ][ 'start_date' ] ) ? $fsp_params[ 'info' ][ 'start_date' ] : 'now' ); ?>">
						<input type="time" autocomplete="off" class="fsp-form-input schedule_input_start_time" placeholder="<?php echo fsp__( 'Select time' ); ?>" value="<?php echo Date::time( isset( $fsp_params[ 'info' ][ 'share_time' ] ) ? $fsp_params[ 'info' ][ 'share_time' ] : 'now' ); ?>">
					</div>
					<div class="fsp-modal-col">
						<?php echo fsp__( 'Local time: %s', [ Date::dateTime() ] ); ?>
					</div>
				</div>
			</div>
			<div id="fspScheduleHowShareRow" class="fsp-form-group <?php echo isset( $fsp_params[ 'post_ids_count' ] ) && $fsp_params[ 'post_ids_count' ] > 1 ? 'fsp-hide' : ''; ?>">
				<label><?php echo fsp__( 'How you want to share' ); ?>&emsp;<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'Define to share a single post repeatedly or once' ); ?>"></i>
				</label>
				<select class="fsp-form-select post_freq">
					<option value="once" <?php echo isset( $fsp_params[ 'info' ][ 'post_freq' ] ) && $fsp_params[ 'info' ][ 'post_freq' ] === 'once' ? 'selected' : ''; ?>><?php echo fsp__( 'Share once' ); ?></option>
					<option value="repeat" <?php echo isset( $fsp_params[ 'info' ][ 'post_freq' ] ) && $fsp_params[ 'info' ][ 'post_freq' ] === 'repeat' ? 'selected' : ''; ?>><?php echo fsp__( 'Share repeatedly' ); ?></option>
				</select>
			</div>
			<div id="fspSchedulePostEveryRow" class="fsp-form-group">
				<label><?php echo fsp__( 'Post every' ); ?>&emsp;<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'The interval between posts' ); ?>"></i>
				</label>
				<div class="fsp-modal-row">
					<div class="fsp-modal-col">
						<input type="number" class="fsp-form-input interval" min="1" max="1000" step="1" value="<?php echo( isset( $fsp_params[ 'info' ][ 'interval' ] ) ? $fsp_params[ 'info' ][ 'interval' ] : '1' ); ?>">
						<select class="fsp-form-select interval_type">
							<option value="60"<?php echo isset( $fsp_params[ 'info' ][ 'interval_type' ] ) && $fsp_params[ 'info' ][ 'interval_type' ] == '60' ? ' selected' : ''; ?>><?php echo fsp__( 'Hour' ); ?></option>
							<option value="1"<?php echo isset( $fsp_params[ 'info' ][ 'interval_type' ] ) && $fsp_params[ 'info' ][ 'interval_type' ] == '1' ? ' selected' : ''; ?>><?php echo fsp__( 'Minute' ); ?></option>
							<option value="1440"<?php echo isset( $fsp_params[ 'info' ][ 'interval_type' ] ) && $fsp_params[ 'info' ][ 'interval_type' ] == '1440' ? ' selected' : ''; ?>><?php echo fsp__( 'Day' ); ?></option>
						</select>
					</div>
					<div class="fsp-modal-col"></div>
				</div>
			</div>
			<div class="fsp-form-group">
				<div class="fsp-form-checkbox-group">
					<input id="fspScheduleSetSleepTime" type="checkbox" class="fsp-form-checkbox schedule_set_sleep_time" <?php echo isset( $fsp_params[ 'info' ][ 'sleep_time_start' ] ) && ! empty( $fsp_params[ 'info' ][ 'sleep_time_start' ] ) ? ' checked' : ''; ?>>
					<label for="fspScheduleSetSleepTime">
						<?php echo fsp__( 'Set a sleep timer' ); ?>
					</label>
					<span class="fsp-tooltip" data-title="<?php echo fsp__( 'You can set a sleep timer in your schedule. The plugin won\'t share any post during the sleep time.' ); ?>"><i class="far fa-question-circle"></i></span>
				</div>
				<div id="fspScheduleSetSleepTimeContainer" class="fsp-modal-row fsp-hide">
					<div class="fsp-modal-col">
						<input type="time" autocomplete="off" class="fsp-form-input schedule_input_sleep_time_start" value="<?php echo isset( $fsp_params[ 'info' ][ 'sleep_time_start' ] ) && ! empty( $fsp_params[ 'info' ][ 'sleep_time_start' ] ) ? Date::time( $fsp_params[ 'info' ][ 'sleep_time_start' ] ) : ''; ?>">
						<input type="time" autocomplete="off" class="fsp-form-input schedule_input_sleep_time_end" value="<?php echo isset( $fsp_params[ 'info' ][ 'sleep_time_end' ] ) && ! empty( $fsp_params[ 'info' ][ 'sleep_time_end' ] ) ? Date::time( $fsp_params[ 'info' ][ 'sleep_time_end' ] ) : ''; ?>">
					</div>
					<div class="fsp-modal-col"></div>
				</div>
			</div>
			<div id="fspScheduleOrderPostsRow" class="fsp-form-group <?php echo isset( $fsp_params[ 'post_ids_count' ] ) && $fsp_params[ 'post_ids_count' ] == 1 ? 'fsp-hide' : ''; ?>">
				<label><?php echo fsp__( 'Order posts by' ); ?>&emsp;<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'Method for selecting posts.' ); ?>"></i>
				</label>
				<select class="fsp-form-select post_sort">
					<option value="random2" <?php echo isset( $fsp_params[ 'info' ][ 'post_sort' ] ) && $fsp_params[ 'info' ][ 'post_sort' ] === 'random2' ? ' selected' : ''; ?>><?php echo fsp__( 'Randomly without duplicates' ); ?></option>
					<option value="random" <?php echo isset( $fsp_params[ 'info' ][ 'post_sort' ] ) && $fsp_params[ 'info' ][ 'post_sort' ] === 'random' ? ' selected' : ''; ?>><?php echo fsp__( 'Randomly' ); ?></option>
					<option value="old_first" <?php echo isset( $fsp_params[ 'info' ][ 'post_sort' ] ) && $fsp_params[ 'info' ][ 'post_sort' ] === 'old_first' ? ' selected' : ''; ?>><?php echo fsp__( 'Start from the oldest to new posts' ); ?></option>
					<option value="new_first" <?php echo isset( $fsp_params[ 'info' ][ 'post_sort' ] ) && $fsp_params[ 'info' ][ 'post_sort' ] === 'new_first' ? ' selected' : ''; ?>><?php echo fsp__( 'Start from the latest to old posts' ); ?></option>
				</select>
			</div>
			<div class="fsp-form-group <?php echo $fsp_params[ 'isAutoRescheduled' ] === 1 ? 'fsp-hide' : ''; ?>">
				<div class="fsp-form-checkbox-group">
					<input id="fspScheduleAutoReschedule" type="checkbox" class="fsp-form-checkbox schedule_auto_reschedule" <?php echo $fsp_params[ 'isAutoRescheduleEnabled' ] === 1 ? 'checked' : ''; ?>>
					<label for="fspScheduleAutoReschedule"><?php echo fsp__( 'Auto schedule' ); ?></label>
					<span class="fsp-tooltip" data-title="<?php echo fsp__( 'You can automatically repeat the schedule as many times as you want. Set 0 (zero) to repeat the schedule indefinitely.' ); ?>"><i class="far fa-question-circle"></i></span>
				</div>
				<div id="fspScheduleRescheduleCount" class="fsp-modal-row <?php echo $fsp_params[ 'isAutoRescheduleEnabled' ] === 1 ? '' : 'fsp-hide'; ?>">
					<div class="fsp-modal-col">
						<input type="number" min="0" autocomplete="off" class="fsp-form-input schedule_auto_reschedule_count" value="<?php echo $fsp_params[ 'autoRescheduleCount' ]; ?>">
						<span><?php echo fsp__( 'times' ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<div id="fspAddSchedule_2" class="fspAddSchedule-step <?php echo ( ! ( isset ( $fsp_params[ 'is_direct_share_tab' ] ) && $fsp_params[ 'is_direct_share_tab' ] === TRUE ) ) ? '' : 'fsp-hide'; ?>">
			<div id="fspScheduleDateRangeRow" class="fsp-form-group <?php echo isset( $fsp_params[ 'post_ids_count' ] ) && $fsp_params[ 'post_ids_count' ] > 0 ? 'fsp-hide' : ''; ?>">
				<label><?php echo fsp__( 'By the published time of the posts' ); ?>&emsp;<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'Select posts that are published in a specific time.' ); ?>"></i>
				</label>
				<div class="fsp-form-group">
					<select id="fsp_filter_posts_date_range" class="fsp-form-select">
						<option value="all_date_range" <?php echo isset( $fsp_params[ 'is_all_times' ] ) && $fsp_params[ 'is_all_times' ] ? 'selected' : ''; ?>><?php echo fsp__( 'All times' ); ?></option>
						<option value="today"><?php echo fsp__( 'Today' ); ?></option>
						<option value="last_7_days"><?php echo fsp__( 'The last week' ); ?></option>
						<option value="last_15_days"><?php echo fsp__( 'The last 15 days' ); ?></option>
						<option value="last_30_days"><?php echo fsp__( 'The last month' ); ?></option>
						<option value="last_90_days"><?php echo fsp__( 'The last 3 months' ); ?></option>
						<option value="last_180_days"><?php echo fsp__( 'The last 6 months' ); ?></option>
						<option value="last_365_days"><?php echo fsp__( 'The last year' ); ?></option>
						<option value="custom_date_range" <?php echo isset( $fsp_params[ 'is_all_times' ] ) && ! $fsp_params[ 'is_all_times' ] ? 'selected' : ''; ?>><?php echo fsp__( 'Custom date range' ); ?></option>
					</select>
				</div>
				<div id="fsp_filter_posts_custom_date_range_row" class="fsp-modal-row <?php echo isset( $fsp_params[ 'is_all_times' ] ) && $fsp_params[ 'is_all_times' ] ? 'fsp-hide' : ''; ?>">
					<div class="fsp-modal-col">
						<input type="date" autocomplete="off" class="fsp-form-input" id="fsp_filter_posts_date_range_from" placeholder="<?php echo fsp__( 'From' ); ?>" value="<?php echo( isset( $fsp_params[ 'info' ][ 'filter_posts_date_range_from' ] ) ? $fsp_params[ 'info' ][ 'filter_posts_date_range_from' ] : '' ); ?>">
						<input type="date" autocomplete="off" class="fsp-form-input" id="fsp_filter_posts_date_range_to" placeholder="<?php echo fsp__( 'To' ); ?>" value="<?php echo( isset( $fsp_params[ 'info' ][ 'filter_posts_date_range_to' ] ) ? $fsp_params[ 'info' ][ 'filter_posts_date_range_to' ] : '' ); ?>">
					</div>
					<div class="fsp-modal-col"></div>
				</div>
			</div>
			<div id="fspSchedulePostTypeFilterRow" class="fsp-form-group <?php echo isset( $fsp_params[ 'post_ids_count' ] ) && $fsp_params[ 'post_ids_count' ] > 0 ? 'fsp-hide' : ''; ?>">
				<label><?php echo fsp__( 'By post type' ); ?>&emsp;<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'You can select new post types in [ FS Poster > Settings > Share post types ].' ); ?>"></i>
				</label>
				<select class="fsp-form-select schedule_input_post_type_filter">
					<?php
					foreach ( $fsp_params[ 'postTypes' ] as $k => $v )
					{
						echo '<option value="' . esc_html( $k ) . '"' . ( isset( $fsp_params[ 'info' ][ 'post_type_filter' ] ) && $fsp_params[ 'info' ][ 'post_type_filter' ] == esc_html( $k ) ? ' selected' : '' ) . '>' . esc_html( ucfirst( $v ) ) . '</option>';
					}
					?>
				</select>
			</div>
			<div id="fspScheduleOutOfStockRow" class="fsp-form-checkbox-group <?php echo isset( $fsp_params[ 'post_ids_count' ] ) && $fsp_params[ 'post_ids_count' ] == 1 ? 'fsp-hide' : ''; ?>">
				<input id="fspScheduleOutOfStock" type="checkbox" class="fsp-form-checkbox schedule_dont_post_out_of_stock_products" <?php echo isset( $fsp_params[ 'info' ][ 'dont_post_out_of_stock_products' ] ) && $fsp_params[ 'info' ][ 'dont_post_out_of_stock_products' ] == 1 ? 'checked' : '' ?>>
				<label for="fspScheduleOutOfStock">
					<?php echo fsp__( 'Don\'t post products that are out of stock' ) ?>
				</label>
			</div>
			<div id="fspScheduleCategoryFilterRow" class="fsp-form-group <?php echo isset( $fsp_params[ 'post_ids_count' ] ) && $fsp_params[ 'post_ids_count' ] > 0 ? ' fsp-hide' : ''; ?>">
				<label><?php echo fsp__( 'By the post category and tag' ); ?></label>
				<select class="fsp-form-input schedule_input_category_filter select2-init">
					<?php
					$term_id = isset( $fsp_params[ 'info' ][ 'category_filter' ] ) ? $fsp_params[ 'info' ][ 'category_filter' ] : ( isset( $fsp_params[ 'term_id' ] ) ? $fsp_params[ 'term_id' ] : NULL );
					if ( isset( $term_id ) && is_numeric( $term_id ) )
					{
						$term = get_term( ( int ) $term_id ); ?>
						<option value="<?php echo ( int ) $term_id; ?>" selected><?php echo esc_html( isset( $term->name ) ? $term->name : '' ); ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="fsp-form-group">
				<label><?php echo fsp__( 'Specific Post ID(s) (separate by comma)' ); ?></label>
				<input autocomplete="off" class="fsp-form-input schedule_input_post_ids" value="<?php echo isset( $fsp_params[ 'info' ] ) ? $fsp_params[ 'info' ][ 'save_post_ids' ] : $fsp_params[ 'post_ids' ]; ?>">
			</div>
		</div>
	<?php } ?>

	<div id="fspAddSchedule_3" class="fspAddSchedule-step <?php echo ( ! ( isset ( $fsp_params[ 'is_direct_share_tab' ] ) && $fsp_params[ 'is_direct_share_tab' ] === TRUE ) ) ? '' : 'fsp-hide'; ?>">
		<div class="fsp-metabox fsp-is-mini">
			<div class="fsp-card-body">
				<input type="hidden" name="share_checked" value="on">
				<div id="fspMetaboxShareContainer">
					<div class="fsp-metabox-tabs">
						<div data-tab="all" class="fsp-metabox-tab fsp-is-active fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show all accounts' ); ?>">
							<i class="fas fa-grip-horizontal"></i>
						</div>
						<div data-tab="fsp" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show all groups' ); ?>">
							<i class="fas fa-object-group"></i>
						</div>
						<div data-tab="fb" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Facebook accounts' ); ?>">
							<i class="fab fa-facebook-f"></i>
						</div>
                        <div data-tab="instagram" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Instagram accounts' ); ?>">
                            <i class="fab fa-instagram"></i>
                        </div>
                        <div data-tab="threads" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Threads accounts' ); ?>">
							<i class="threads-icon threads-icon-12"></i>
						</div>
                        <div data-tab="twitter" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Twitter accounts' ); ?>">
                            <i class="fab fa-twitter"></i>
                        </div>
                        <div data-tab="planly" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Planly accounts' ); ?>">
                            <i class="planly-icon planly-icon-12"></i>
                        </div>
                        <div data-tab="linkedin" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Linkedin accounts' ); ?>">
							<i class="fab fa-linkedin-in"></i>
						</div>
						<div data-tab="pinterest" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Pinterest accounts' ); ?>">
							<i class="fab fa-pinterest-p"></i>
						</div>
                        <div data-tab="telegram" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Telegram accounts' ); ?>">
                            <i class="fab fa-telegram-plane"></i>
                        </div>
                        <div data-tab="reddit" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Reddit accounts' ); ?>">
							<i class="fab fa-reddit-alien"></i>
						</div>
                        <div data-tab="youtube_community" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Youtube Community accounts' ); ?>">
                            <i class="fab fa-youtube-square"></i>
                        </div>
                        <div data-tab="tumblr" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Tumblr accounts' ); ?>">
							<i class="fab fa-tumblr"></i>
						</div>
						<div data-tab="ok" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Odnoklassniki accounts' ); ?>">
							<i class="fab fa-odnoklassniki"></i>
						</div>
                        <div data-tab="vk" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only VKontakte accounts' ); ?>">
                            <i class="fab fa-vk"></i>
                        </div>
                        <div data-tab="google_b" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Google Business Profiles' ); ?>">
							<i class="fab fa-google"></i>
						</div>
						<div data-tab="blogger" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Blogger accounts' ); ?>">
							<i class="fab fa-blogger"></i>
						</div>
                        <div data-tab="wordpress" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only WordPress websites' ); ?>">
                            <i class="fab fa-wordpress-simple"></i>
                        </div>
                        <div data-tab="webhook" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Webhooks' ); ?>">
                            <i class="fas fa-atlas"></i>
                        </div>
                        <div data-tab="medium" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Medium accounts' ); ?>">
							<i class="fab fa-medium-m"></i>
						</div>
                        <div data-tab="plurk" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Plurk accounts' ); ?>">
                            <i class="fas fa-parking"></i>
                        </div>
                        <div data-tab="xing" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Xing accounts' ); ?>">
							<i class="fab fa-xing"></i>
						</div>
						<div data-tab="discord" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Discord accounts' ); ?>">
							<i class="fab fa-discord"></i>
						</div>
						<div data-tab="mastodon" class="fsp-metabox-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Show only Mastodon accounts' ); ?>">
							<i class="fab fa-mastodon"></i>
						</div>
					</div>
					<div id="fspMetaboxAccounts" class="fsp-metabox-accounts">
						<div class="fsp-metabox-accounts-empty">
							<?php echo fsp__( 'Please select an account.' ); ?>
						</div>
						<?php foreach ( $fsp_params[ 'activeNodes' ] as $node_info )
						{
							$coverPhoto = Helper::profilePic( $node_info );

							if ( $node_info[ 'filter_type' ] === 'no' )
							{
								$titleText = '';
							}
							else
							{
								$titleText = ( $node_info[ 'filter_type' ] == 'in' ? fsp__( 'Share only the posts of the selected categories: ' ) : fsp__( 'Do not share the posts of the selected categories: ' ) );
								$titleText .= str_replace( ',', ', ', $node_info[ 'categories_name' ] );
							}

							$sn_names = [
								'fb'                => fsp__( 'FB' ),
								'instagram'         => fsp__( 'Instagram' ),
								'threads'           => fsp__( 'Threads' ),
								'twitter'           => fsp__( 'Twitter' ),
								'planly'            => fsp__( 'Planly' ),
								'linkedin'          => fsp__( 'Linkedin' ),
								'pinterest'         => fsp__( 'Pinterest' ),
								'telegram'          => fsp__( 'Telegram' ),
								'reddit'            => fsp__( 'Reddit' ),
								'youtube_community' => fsp__( 'Youtube Community' ),
								'tumblr'            => fsp__( 'Tumblr' ),
								'ok'                => fsp__( 'OK' ),
								'vk'                => fsp__( 'VK' ),
								'google_b'          => fsp__( 'GMB' ),
								'medium'            => fsp__( 'Medium' ),
								'wordpress'         => fsp__( 'WordPress' ),
								'webhook'           => fsp__( 'Webhook' ),
								'blogger'           => fsp__( 'Blogger' ),
								'plurk'             => fsp__( 'Plurk' ),
								'xing'              => fsp__( 'Xing' ),
								'discord'           => fsp__( 'Discord' ),
								'mastodon'          => fsp__( 'Mastodon' ),
							];
							$driver   = $sn_names[ $node_info[ 'driver' ] ];

							?>

							<div data-driver="<?php echo $node_info[ 'driver' ]; ?>" class="fsp-metabox-account">
								<input type="hidden" name="share_on_nodes[]" value="<?php echo $node_info[ 'driver' ] . ':' . $node_info[ 'node_type' ] . ':' . $node_info[ 'id' ] . ':' . htmlspecialchars( $node_info[ 'filter_type' ] ) . ':' . htmlspecialchars( $node_info[ 'categories' ] ); ?>">
								<div class="fsp-metabox-account-image">
									<img src="<?php echo $coverPhoto; ?>" onerror="FSPoster.no_photo( this );">
								</div>
								<div class="fsp-metabox-account-label">
									<a <?php echo $node_info[ 'driver' ] == 'webhook' ? '' : 'href="' . Helper::profileLink( $node_info ) . '"'; ?> class="fsp-metabox-account-text">
										<?php echo esc_html( $node_info[ 'name' ] ); ?>
									</a>
									<div class="fsp-metabox-account-subtext">
										<?php echo $node_info[ 'subName' ]; ?>&nbsp;<?php echo empty( $titleText ) ? '' : '<i class="fas fa-filter fsp-tooltip" data-title="' . $titleText . '" ></i>'; ?>
									</div>
								</div>
								<div class="fsp-metabox-account-remove">
									<i class="fas fa-times"></i>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="fsp-card-footer fsp-is-right">
				<button type="button" class="fsp-button fsp-is-gray fsp-metabox-add"><?php echo fsp__( 'ADD' ); ?></button>
				<button type="button" class="fsp-button fsp-is-red fsp-metabox-clear"><?php echo fsp__( 'CLEAR' ); ?></button>
			</div>
		</div>
	</div>
	<div id="fspAddSchedule_4" class="fspAddSchedule-step <?php echo ( ! ( isset ( $fsp_params[ 'is_direct_share_tab' ] ) && $fsp_params[ 'is_direct_share_tab' ] === TRUE ) ) ? '' : 'fsp-hide'; ?>">
		<div class="fsp-custom-messages-container">
			<div class="fsp-card-body">
				<div class="fsp-custom-messages-tabs">
					<div data-tab="fb" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Facebook' ); ?>">
						<i class="fab fa-facebook-f"></i>
					</div>
                    <div data-tab="instagram" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Instagram' ); ?>">
                        <i class="fab fa-instagram"></i>
                    </div>
                    <div data-tab="threads" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Threads' ); ?>">
                        <i class="threads-icon threads-icon-12"></i>
                    </div>
                    <div data-tab="twitter" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Twitter' ); ?>">
						<i class="fab fa-twitter"></i>
					</div>
					<div data-tab="planly" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Planly' ); ?>">
                        <i class="planly-icon planly-icon-12"></i>
                    </div>
                    <div data-tab="linkedin" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Linkedin' ); ?>">
						<i class="fab fa-linkedin-in"></i>
					</div>
					<div data-tab="pinterest" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Pinterest' ); ?>">
						<i class="fab fa-pinterest-p"></i>
					</div>
                    <div data-tab="telegram" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Telegram' ); ?>">
                        <i class="fab fa-telegram-plane"></i>
                    </div>
                    <div data-tab="reddit" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Reddit' ); ?>">
						<i class="fab fa-reddit-alien"></i>
					</div>
                    <div data-tab="youtube_community" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Youtube Community' ); ?>">
                        <i class="fab fa-youtube-square"></i>
                    </div>
                    <div data-tab="tumblr" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Tumblr' ); ?>">
						<i class="fab fa-tumblr"></i>
					</div>
					<div data-tab="ok" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Odnoklassniki' ); ?>">
						<i class="fab fa-odnoklassniki"></i>
					</div>
                    <div data-tab="vk" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for VKontakte' ); ?>">
                        <i class="fab fa-vk"></i>
                    </div>
                    <div data-tab="google_b" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Google Business Profile' ); ?>">
						<i class="fab fa-google"></i>
					</div>
					<div data-tab="medium" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Medium' ); ?>">
						<i class="fab fa-medium-m"></i>
					</div>
					<div data-tab="wordpress" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for WordPress' ); ?>">
						<i class="fab fa-wordpress-simple"></i>
					</div>
                    <div data-tab="blogger" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Blogger' ); ?>">
                        <i class="fab fa-blogger"></i>
                    </div>
                    <div data-tab="plurk" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Plurk' ); ?>">
                        <i class="fas fa-parking"></i>
                    </div>
                    <div data-tab="xing" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Xing' ); ?>">
						<i class="fab fa-xing"></i>
					</div>
					<div data-tab="discord" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Discord' ); ?>">
						<i class="fab fa-discord"></i>
					</div>
					<div data-tab="mastodon" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Mastodon' ); ?>">
						<i class="fab fa-mastodon"></i>
					</div>
                </div>
				<div id="fspCustomMessages" class="fsp-custom-messages">
					<?php foreach ( $fsp_params[ 'sn_list' ] as $sn ) { ?>
						<div data-driver="<?php echo $sn; ?>">
							<?php if ( $sn == 'instagram' ) { ?>
								<div class="fsp-form-checkbox-group">
									<input id="instagram_pin_post" type="checkbox" class="fsp-form-checkbox" <?php echo( $fsp_params[ 'instagramPinThePost' ] === 1 ? 'checked' : '' ) ?>>
									<label for="instagram_pin_post">
										<?php echo fsp__( 'Pin the post' ); ?>
									</label>
								</div>
							<?php } ?>
							<div class="fsp-custom-post">
								<div class="fsp-custom-message-label"><?php echo fsp__( 'Customize post message' ); ?></div>
								<textarea data-sn-id="<?php echo $sn; ?>" name="fs_post_text_message_<?php echo $sn; ?>" class="fsp-form-textarea" rows="4" maxlength="3000"><?php echo esc_html( $fsp_params[ 'customMessages' ][ $sn ] ); ?></textarea>
								<div class="fsp-custom-post-buttons">
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{id}">
										{ID}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post ID' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_regular_price}">
										{PRODUCT_REGULAR_PRICE}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - product price' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{author}">
										{AUTHOR}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post author name' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{content_short_40}">
										{CONTENT_SHORT_40}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'The default is the first 40 characters. You can set the number whatever you want. The plugin will share that number of characters.' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{title}">
										{TITLE}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post title' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{featured_image_url}">
										{FEATURED_IMAGE_URL}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Featured image URL' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{tags}">
										{TAGS}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Tags' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_sale_price}">
										{PRODUCT_SALE_PRICE}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - product sale price' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{terms}">
										{TERMS}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Terms' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{terms_comma}">
										{TERMS_COMMA}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Terms separated by comma' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{terms_space}">
										{TERMS_SPACE}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Terms separated by a space' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{content_full}">
										{CONTENT_FULL}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post full content' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{short_link}">
										{SHORT_LINK}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post short link' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{excerpt}">
										{EXCERPT}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post excerpt' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_description}">
										{PRODUCT_DESCRIPTION}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Product short description' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{categories}">
										{CATEGORIES}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Categories' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{uniq_id}">
										{UNIQ_ID}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Unique ID' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{cf_KEY}">
										{CF_KEY}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Custom fields. Replace KEY with the custom field name.' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{link}">
										{LINK}
										<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post link' ); ?>"></i>
									</button>
									<button type="button" class="fsp-button fsp-is-red fsp-clear-button fsp-tooltip" data-title="<?php echo fsp__( 'Click to clear the textbox' ); ?>">
										<?php echo fsp__( 'CLEAR' ); ?>
									</button>
								</div>
							</div>
							<?php if ( $sn === 'instagram' || $sn === 'fb' ) { ?>
								<div class="fsp-custom-post">
									<div class="fsp-custom-message-label"><?php echo fsp__( 'Customize story message' ); ?></div>
									<textarea data-sn-id="<?php echo $sn . '_h' ?>" name="fs_post_text_message_<?php echo $sn . '_h' ?>" class="fsp-form-textarea" rows="4" maxlength="3000"><?php echo esc_html( $fsp_params[ 'customMessages' ][ $sn . '_h' ] ); ?></textarea>
									<div class="fsp-custom-post-buttons">
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{id}">
											{ID}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post ID' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_regular_price}">
											{PRODUCT_REGULAR_PRICE}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - product price' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{author}">
											{AUTHOR}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post author name' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{content_short_40}">
											{CONTENT_SHORT_40}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'The default is the first 40 characters. You can set the number whatever you want. The plugin will share that number of characters.' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{title}">
											{TITLE}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post title' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{featured_image_url}">
											{FEATURED_IMAGE_URL}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Featured image URL' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{tags}">
											{TAGS}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Tags' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_sale_price}">
											{PRODUCT_SALE_PRICE}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - product sale price' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{terms}">
											{TERMS}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Terms' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{terms_comma}">
											{TERMS_COMMA}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Terms separated by comma' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{terms_space}">
											{TERMS_SPACE}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Terms separated by a space' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{content_full}">
											{CONTENT_FULL}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post full content' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{short_link}">
											{SHORT_LINK}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post short link' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{excerpt}">
											{EXCERPT}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post excerpt' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_description}">
											{PRODUCT_DESCRIPTION}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post excerpt' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{categories}">
											{CATEGORIES}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Categories' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{uniq_id}">
											{UNIQ_ID}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Unique ID' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{cf_KEY}">
											{CF_KEY}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Custom fields. Replace KEY with the custom field name.' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{link}">
											{LINK}
											<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post link' ); ?>"></i>
										</button>
										<button type="button" class="fsp-button fsp-is-red fsp-clear-button fsp-tooltip" data-title="<?php echo fsp__( 'Click to clear the textbox' ); ?>">
											<?php echo fsp__( 'CLEAR' ); ?>
										</button>
									</div>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if ( ! ( isset ( $fsp_params[ 'is_direct_share_tab' ] ) && $fsp_params[ 'is_direct_share_tab' ] === TRUE ) ) { ?>
	<div class="fsp-modal-subfooter schedule_popup">
		<?php echo fsp__( 'Posts matching your filters:' ); ?>&nbsp;<span class="schedule_matches_count"></span>
	</div>
<?php } ?>

<div class="fsp-modal-footer schedule_popup">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'CANCEL' ); ?></button>
	<button id="fspScheduleDeleteBtn" data-info="<?php echo empty( $fsp_params[ 'info' ] ) ? '' : esc_html( json_encode( $fsp_params[ 'info' ] ) ); ?>" class="fsp-button fsp-is-red <?php echo ! ( isset( $fsp_params[ 'is_native' ] ) && $fsp_params[ 'is_native' ] === TRUE ) ? 'fsp-hide' : 'wp_native_schedule_delete_btn'; ?>">
		<i class="far fa-trash-alt"></i>
		<span><?php echo fsp__( "Delete schedule" ); ?></span>
	</button>
	<button id="fspScheduleSaveBtn" data-info="<?php echo empty( $fsp_params[ 'info' ] ) ? '' : esc_html( json_encode( $fsp_params[ 'info' ] ) ); ?>" class="fsp-button <?php echo ! ( isset( $fsp_params[ 'is_native' ] ) && $fsp_params[ 'is_native' ] === TRUE ) ? 'schedule_save_btn' : 'wp_native_schedule_save_btn'; ?>"><?php echo $fsp_params[ 'btn_title' ]; ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( 'https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.10.4/dayjs.min.js', true, function () {
			FSPoster.load_script( '<?php echo Pages::asset( 'Base', 'js/fsp-tabs.js' ); ?>' );
			FSPoster.load_script( '<?php echo Pages::asset( 'Schedules', 'js/fsp-schedule-add.js' ); ?>' );
		} );
	} );
</script>