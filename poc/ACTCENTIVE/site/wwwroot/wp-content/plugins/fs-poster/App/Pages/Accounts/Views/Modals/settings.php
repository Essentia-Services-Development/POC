<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'MODAL' ) or exit;
?>
<link rel="stylesheet" href="<?php echo Pages::asset( 'Settings', 'css/fsp-settings.css' ); ?>">
<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fas fa-sliders-h"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Custom settings' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
    <div class="fsp-modal-notification-container"><div class="fsp-modal-notification"><div class="fsp-notification-info"><div class="fsp-notification-icon fsp-is-warning"></div><div class="fsp-notification-text"><div class="fsp-notification-message"><?php echo fsp__('Note that the custom settings for the account override configuration in the Settings menu.'); ?></div></div></div></div></div>

	<div id="fspComponent" class="fsp-layout-right fsp-col-12 fsp-col-md-8 fsp-col-lg-9">
		<form id="fspSettingsForm" class="fsp-settings">
			<input type="hidden" name="fs_node_id" value="<?php echo $fsp_params['node_id'] ?>">
			<input type="hidden" name="fs_node_type" value="<?php echo $fsp_params['node_type'] ?>">
            <input type="hidden" name="fs_node_driver" value="<?php echo $fsp_params[ 'driver' ] ?>">
            <?php if ( ! empty( $fsp_params[ 'node_data' ] ) ) { ?>
				<?php if ( $fsp_params[ 'node_data' ][ 'posting_type' ] === 'blogger_posting_type' || $fsp_params[ 'node_data' ][ 'posting_type' ] === 'wordpress_posting_type' ) { ?>
					<div class="fsp-settings-row">
						<div class="fsp-settings-col">
							<div class="fsp-settings-label-text"><?php echo $fsp_params[ 'node_data' ][ 'text' ]; ?></div>
							<div class="fsp-settings-label-subtext"><?php echo $fsp_params[ 'node_data' ][ 'subtext' ]; ?></div>
						</div>
						<div class="fsp-settings-col">
							<div class="fsp-toggle">
								<input type="checkbox" name="fs_checkbox_posting_type" class="fsp-toggle-checkbox" id="fspPostingType" <?php echo Helper::getCustomSetting( 'posting_type', Helper::getOption( $fsp_params[ 'node_data' ][ 'posting_type' ], $fsp_params[ 'node_data' ][ 'posting_type' ] === 'wordpress_posting_type' ? '1' : '0' ), $fsp_params[ 'node_type' ], $fsp_params[ 'node_id' ] ) ? 'checked' : ''; ?>>
								<label class="fsp-toggle-label" for="fspPostingType"></label>
							</div>
						</div>
					</div>
				<?php } else { ?>
					<div class="fsp-settings-row">
						<div class="fsp-settings-col">
							<div class="fsp-settings-label-text"><?php echo $fsp_params[ 'node_data' ][ 'title' ]; ?></div>
							<div class="fsp-settings-label-subtext"><?php echo $fsp_params[ 'node_data' ][ 'text' ]; ?></div>
						</div>
						<div class="fsp-settings-col">
							<select id="fspPostingType"  name="fs_posting_type" class="fsp-form-select">
								<?php foreach ( $fsp_params[ 'node_data' ][ 'options' ] as $key => $option ) { ?>
									<option value="<?php echo $key; ?>"<?php echo Helper::getCustomSetting( 'posting_type', Helper::getOption( $fsp_params[ 'node_data' ][ 'posting_type' ], '1' ), $fsp_params[ 'node_type' ], $fsp_params[ 'node_id' ] ) == $key ? ' selected' : ''; ?>><?php echo $option; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				<?php } ?>
			<?php } ?>

            <?php if( $fsp_params[ 'supportsInstagramBioLink' ] ){ ?>
                <div class="fsp-settings-row">
                    <div class="fsp-settings-col">
                        <div class="fsp-settings-label-text"><?php echo fsp__( 'Update the Instagram Bio link' ); ?></div>
                        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to update the Instagram Bio link to the last shared post link.' ); ?></div>
                    </div>
                    <div class="fsp-settings-col">
                        <div class="fsp-toggle">
                            <input type="checkbox" name="fs_instagram_update_bio_link" class="fsp-toggle-checkbox" id="fs_instagram_update_bio_link" <?php echo Helper::getCustomSetting( 'update_bio_link', Helper::getOption( $fsp_params[ 'driver' ] . '_update_bio_link' ), $fsp_params['node_type'], $fsp_params['node_id'] ) ? 'checked' : ''; ?>>
                            <label class="fsp-toggle-label" for="fs_instagram_update_bio_link"></label>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if ( $fsp_params[ 'supportsFetchingComments' ] ) { ?>
                <div class="fsp-settings-row">
                    <div class="fsp-settings-col">
                        <div class="fsp-settings-label-text"><?php echo fsp__( 'Fetch Facebook comments' ); ?></div>
                        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to fetch Facebook comments as WordPress post comments. The App method supports the feature, and the fetching happens every 12 hours.' ); ?></div>
                    </div>
                    <div class="fsp-settings-col">
                        <div class="fsp-toggle">
                            <input type="checkbox" name="fs_fetch_facebook_comments" class="fsp-toggle-checkbox" id="fs_fetch_facebook_comments" <?php echo Helper::getCustomSetting( 'fetch_facebook_comments', Helper::getOption( 'fetch_fb_comments' ), $fsp_params['node_type'], $fsp_params['node_id']) ? 'checked' : ''; ?>>
                            <label class="fsp-toggle-label" for="fs_fetch_facebook_comments"></label>
                        </div>
                    </div>
                </div>
            <?php } ?>

			<div class="fsp-settings-row">
				<div class="fsp-settings-col">
					<div class="fsp-settings-label-text"><?php echo fsp__( 'Unique post link' ); ?></div>
					<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Sharing the same post on numerous communities might be accepted as a duplicated post by social networks; consequently, the post might be blocked. By enabling the option, the ending of each link gets random URL characters to become unique.' ); ?></div>
				</div>
				<div class="fsp-settings-col">
					<div class="fsp-toggle">
						<input type="checkbox" name="fs_unique_link" class="fsp-toggle-checkbox" id="fs_unique_link" <?php echo Helper::getCustomSetting( 'unique_link', '0', $fsp_params['node_type'], $fsp_params['node_id']) ? 'checked' : ''; ?>>
						<label class="fsp-toggle-label" for="fs_unique_link"></label>
					</div>
				</div>
			</div>
			<div class="fsp-settings-row">
				<div class="fsp-settings-col">
					<div class="fsp-settings-label-text"><?php echo fsp__( 'URL shortener' ); ?></div>
					<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to shorten your post URLs automatically.' ); ?></div>
				</div>
				<div class="fsp-settings-col">
					<div class="fsp-toggle">
						<input type="checkbox" name="fs_url_shortener" class="fsp-toggle-checkbox" id="fspURLShortener" <?php echo Helper::getCustomSetting( 'url_shortener', '0', $fsp_params['node_type'], $fsp_params['node_id'] ) ? 'checked' : ''; ?>>
						<label class="fsp-toggle-label" for="fspURLShortener"></label>
					</div>
				</div>
			</div>
			<div id="fspShortenerRow">
				<div class="fsp-settings-row">
					<div class="fsp-settings-col">
						<div class="fsp-settings-label-text"><?php echo fsp__( 'URL shortener service' ); ?></div>
						<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Select a URL shortener service to shorten your post URLs.' ); ?></div>
					</div>
					<div class="fsp-settings-col">
						<select id="fspShortenerSelector" name="fs_shortener_service" class="fsp-form-select">
							<option value="tinyurl" <?php echo Helper::getCustomSetting( 'shortener_service', '', $fsp_params['node_type'], $fsp_params['node_id']) == 'tinyurl' ? 'selected' : '' ?>>TinyURL</option>
							<option value="bitly" <?php echo Helper::getCustomSetting( 'shortener_service','', $fsp_params['node_type'], $fsp_params['node_id']) == 'bitly' ? 'selected' : '' ?>>Bitly</option>
							<option value="yourls" <?php echo Helper::getCustomSetting( 'shortener_service','', $fsp_params['node_type'], $fsp_params['node_id']) == 'yourls' ? 'selected' : '' ?>>Yourls</option>
							<option value="polr" <?php echo Helper::getCustomSetting( 'shortener_service','', $fsp_params['node_type'], $fsp_params['node_id']) == 'polr' ? 'selected' : '' ?>>Polr</option>
							<option value="shlink" <?php echo Helper::getCustomSetting( 'shortener_service','', $fsp_params['node_type'], $fsp_params['node_id']) == 'shlink' ? 'selected' : '' ?>>Shlink</option>
							<option value="rebrandly" <?php echo Helper::getCustomSetting( 'shortener_service','', $fsp_params['node_type'], $fsp_params['node_id']) == 'rebrandly' ? 'selected' : '' ?>>Rebrandly</option>
						</select>
					</div>
				</div>
				<div id="fspBitly" class="fsp-settings-row">
					<div class="fsp-settings-col">
						<div class="fsp-settings-label-text"><?php echo fsp__( 'Bitly access token' ); ?></div>
						<div class="fsp-settings-label-subtext"><?php echo fsp__( '<a href="https://bitly.com/a/sign_up" target="_blank">Register</a> on the Bitly service and <a href="https://bitly.is/accesstoken" target="_blank">get a new access token.</a>', [], FALSE ); ?></div>
					</div>
					<div class="fsp-settings-col">
						<input type="text" autocomplete="off" name="fs_url_short_access_token_bitly" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'url_short_access_token_bitly', '100', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
					</div>
				</div>
				<div id="fspYourlsApiUrl" class="fsp-settings-row">
					<div class="fsp-settings-col">
						<div class="fsp-settings-label-text"><?php echo fsp__( 'Yourls API URL' ); ?></div>
						<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Example: https://your-yourls-installation.com/yourls-api.php', [], FALSE ); ?></div>
					</div>
					<div class="fsp-settings-col">
						<input type="text" autocomplete="off" name="fs_url_short_api_url_yourls" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'url_short_api_url_yourls', '', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
					</div>
				</div>
				<div id="fspYourlsApiToken" class="fsp-settings-row">
					<div class="fsp-settings-col">
						<div class="fsp-settings-label-text"><?php echo fsp__( 'Yourls secret signature token' ); ?></div>
						<div class="fsp-settings-label-subtext"><?php echo fsp__( 'The secret signature token can be found in the Yourls installation admin > tools page', [], FALSE ); ?></div>
					</div>
					<div class="fsp-settings-col">
						<input type="text" autocomplete="off" name="fs_url_short_api_token_yourls" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'url_short_api_token_yourls', '', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
					</div>
				</div>
				<div id="fspPolrApiUrl" class="fsp-settings-row">
					<div class="fsp-settings-col">
						<div class="fsp-settings-label-text"><?php echo fsp__( 'Polr API URL' ); ?></div>
						<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Example: https://your-polr-installation.com/api/v2', [], FALSE ); ?></div>
					</div>
					<div class="fsp-settings-col">
						<input type="text" autocomplete="off" name="fs_url_short_api_url_polr" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'url_short_api_url_polr', '', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
					</div>
				</div>
				<div id="fspPolrApiKey" class="fsp-settings-row">
					<div class="fsp-settings-col">
						<div class="fsp-settings-label-text"><?php echo fsp__( 'Polr API key' ); ?></div>
						<div class="fsp-settings-label-subtext"><?php echo fsp__( 'The API key can be found at https://your-polr-installation.com/admin#developer', [], FALSE ); ?></div>
					</div>
					<div class="fsp-settings-col">
						<input type="text" autocomplete="off" name="fs_url_short_api_key_polr" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'url_short_api_key_polr', '', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
					</div>
				</div>
				<div id="fspShlinkApiUrl" class="fsp-settings-row">
					<div class="fsp-settings-col">
						<div class="fsp-settings-label-text"><?php echo fsp__( 'Shlink API URL' ); ?></div>
						<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Example: https://your-shlink-installation.com/rest/v2', [], FALSE ); ?></div>
					</div>
					<div class="fsp-settings-col">
						<input type="text" autocomplete="off" name="fs_url_short_api_url_shlink" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'url_short_api_url_shlink', '', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
					</div>
				</div>
				<div id="fspShlinkApiKey" class="fsp-settings-row">
					<div class="fsp-settings-col">
						<div class="fsp-settings-label-text"><?php echo fsp__( 'Shlink API key' ); ?></div>
						<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Please enter the Shlink API key', [], FALSE ); ?></div>
					</div>
					<div class="fsp-settings-col">
						<input type="text" autocomplete="off" name="fs_url_short_api_key_shlink" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'url_short_api_key_shlink', '', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
					</div>
				</div>
				<div id="fspRebrandlyDomain" class="fsp-settings-row">
					<div class="fsp-settings-col">
						<div class="fsp-settings-label-text"><?php echo fsp__( 'Rebrandly domain' ); ?></div>
						<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Rebrandly domain', [], FALSE ); ?></div>
					</div>
					<div class="fsp-settings-col">
						<input type="text" autocomplete="off" name="fs_url_short_domain_rebrandly" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'url_short_domain_rebrandly', '', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
					</div>
				</div>
				<div id="fspRebrandlyApiKey" class="fsp-settings-row">
					<div class="fsp-settings-col">
						<div class="fsp-settings-label-text"><?php echo fsp__( 'Rebrandly API key' ); ?></div>
						<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Please enter the Rebrandly API key', [], FALSE ); ?></div>
					</div>
					<div class="fsp-settings-col">
						<input type="text" autocomplete="off" name="fs_url_short_api_key_rebrandly" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'url_short_api_key_rebrandly', '', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
					</div>
				</div>
			</div>
			<div class="fsp-settings-row">
				<div class="fsp-settings-col">
					<div class="fsp-settings-label-text"><?php echo fsp__( 'Share a custom URL instead of the WordPress post URL' ); ?></div>
					<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to define and type your custom post URL using specific keywords.' ); ?></div>
				</div>
				<div class="fsp-settings-col">
					<div class="fsp-toggle">
						<input type="checkbox" name="fs_share_custom_url" class="fsp-toggle-checkbox" id="fspCustomURL" <?php echo Helper::getCustomSetting( 'share_custom_url', '0', $fsp_params['node_type'], $fsp_params['node_id'] ) ? 'checked' : ''; ?>>
						<label class="fsp-toggle-label" for="fspCustomURL"></label>
					</div>
				</div>
			</div>
			<div id="fspCustomURLRow_1" class="fsp-settings-row fsp-is-collapser">
				<div class="fsp-settings-collapser">
					<div class="fsp-settings-label-text"><?php echo fsp__( 'Additional URL parameters' ); ?>
						<i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
					</div>
					<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the URL as you like by using the current keywords.' ); ?></div>
				</div>
				<div class="fsp-settings-collapse">
					<div class="fsp-col-12">
						<div class="fsp-custom-post">
							<input autocomplete="off" name="fs_url_additional" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'url_additional', '', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
							<div class="fsp-custom-post-buttons">
								<button type="button" class="fsp-button fsp-is-red" id="fspUseGA">
									<?php echo fsp__( 'Use Google Analytics template' ); ?>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{post_id}">
									{POST_ID}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post ID' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{network_name}">
									{NETWORK_NAME}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Social network name' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{network_code}">
									{NETWORK_CODE}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Social network code' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{account_name}">
									{ACCOUNT_NAME}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Account name' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{post_title}">
									{POST_TITLE}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post title' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{site_name}">
									{SITE_NAME}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Site name' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{uniq_id}">
									{UNIQ_ID}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Unique ID' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-red fsp-clear-button fsp-tooltip" data-title="<?php echo fsp__( 'Click to clear the textbox' ); ?>">
									<?php echo fsp__( 'CLEAR' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="fspCustomURLRow_2" class="fsp-settings-row fsp-is-collapser">
				<div class="fsp-settings-collapser">
					<div class="fsp-settings-label-text"><?php echo fsp__( 'Custom URL' ); ?>
						<i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
					</div>
					<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the URL as you like by using the current keywords. If you want to share the post URL as it is, don\'t enable the option.' ); ?></div>
				</div>
				<div class="fsp-settings-collapse">
					<div class="fsp-col-12">
						<div class="fsp-custom-post">
							<input autocomplete="off" name="fs_custom_url_to_share" class="fsp-form-input" value="<?php echo esc_html( Helper::getCustomSetting( 'custom_url_to_share', '{site_url}/?p={post_id}&feed_id={feed_id}', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?>">
							<div class="fsp-custom-post-buttons">
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{post_id}">
									{POST_ID}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post ID' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{feed_id}">
									{FEED_ID}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Feed ID' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{network_name}">
									{NETWORK_NAME}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Social network name' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{network_code}">
									{NETWORK_CODE}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Social network code' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{account_name}">
									{ACCOUNT_NAME}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Account name' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{post_title}">
									{POST_TITLE}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post title' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{post_slug}">
									{POST_SLUG}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post name' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{site_name}">
									{SITE_NAME}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Site name' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{site_url}">
									{SITE_URL}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Site URL' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{site_url_encoded}">
									{SITE_URL_ENCODED}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Site URL encoded' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{post_url}">
									{POST_URL}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post URL' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{post_url_encoded}">
									{POST_URL_ENCODED}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post URL encoded' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{uniq_id}">
									{UNIQ_ID}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Unique ID' ); ?>"></i>
								</button>
								<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{cf_KEY}">
									{CF_KEY}
									<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Custom fields. Replace KEY with the custom field name.' ); ?>"></i>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
            <?php if ( $fsp_params[ 'supportsFirstComment' ] ) { ?>
                <div class="fsp-settings-row">
                    <div class="fsp-settings-col">
                        <div class="fsp-settings-label-text"><?php echo fsp__( 'Post a first comment.' ); ?></div>
                        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to share a customized message as a first comment.' ); ?></div>
                    </div>
                    <div class="fsp-settings-col">
                        <div class="fsp-toggle">
                            <input type="checkbox" name="fs_post_allow_first_comment" class="fsp-toggle-checkbox" id="fspAllowCommentCustomSetting" <?php echo Helper::getCustomSetting( 'post_allow_first_comment', '0', $fsp_params['node_type'], $fsp_params['node_id'] ) ? 'checked' : ''; ?>>
                            <label class="fsp-toggle-label" for="fspAllowCommentCustomSetting"></label>
                        </div>
                    </div>
                </div>
                <div id="fspFirstCommentCustomSetting">
                    <div class="fsp-settings-row fsp-is-collapser">
                        <div class="fsp-settings-collapser">
                            <div class="fsp-settings-label-text"><?php echo fsp__( 'Customize the first comment.' ); ?>
                                <i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
                            </div>
                        </div>
                        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the first comment as you like by using the available keywords.' )?></div>
                        <div class="fsp-settings-collapse">
                            <div class="fsp-custom-post" data-preview="fspCustomPostPreview1">
                                    <textarea name="fs_post_first_comment" class="fsp-form-textarea"><?php echo esc_html( Helper::getCustomSetting( 'post_first_comment', '', $fsp_params['node_type'], $fsp_params['node_id'] ) ); ?></textarea>
                                    <div class="fsp-custom-post-buttons">
                                        <button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{id}">
                                            {ID}
                                            <i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post ID' ); ?>"></i>
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
                                        <button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_regular_price}">
                                            {PRODUCT_REGULAR_PRICE}
                                            <i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - product price' ); ?>"></i>
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
                                        <button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_sale_price}">
                                            {PRODUCT_SALE_PRICE}
                                            <i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - product sale price' ); ?>"></i>
                                        </button>
                                        <button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_current_price}">
                                            {PRODUCT_CURRENT_PRICE}
                                            <i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - the current price of product' ); ?>"></i>
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
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="fsp-settings-row fsp-is-collapser">
                <div class="fsp-settings-collapser">
                    <div class="fsp-settings-label-text"><?php echo fsp__( 'Customize post message' ); ?>
                        <i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
                    </div>
                    <div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the text of the shared post as you like by using the available keywords. You can add the keywords to the custom message section easily by clicking on the keyword.' ); ?></div>
                </div>
                <div class="fsp-settings-collapse">
                    <div class="fsp-col-12">
                        <div class="fsp-custom-post">
                            <textarea name="fs_account_post_message" class="fsp-form-textarea" rows="2" maxlength="3000"><?php echo esc_html( $fsp_params[ 'post_message' ] ); ?></textarea>
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
                    </div>
                </div>
            </div>
        </form>
	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Close' ); ?></button>
    <button id="fspResetToDefault" class="fsp-button fsp-is-info"><?php echo fsp__( 'Reset to default' ); ?></button>
	<button id="fspSaveSettings" class="fsp-button"><?php echo fsp__( 'Save Settings' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-custom-settings.js' ); ?>' );
	} );
</script>
