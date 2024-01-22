<?php

namespace FSPoster\App\Pages\Settings\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Settings', 'css/fsp-telegram-preview.css' ); ?>">

<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Select what to share on Telegram' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Define what you need to share on Telegram as a message.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select id="fspTelegramPostingType"  name="fs_telegram_type_of_sharing" class="fsp-form-select">
			<option value="1"<?php echo Helper::getOption( 'telegram_type_of_sharing', '1' ) == '1' ? ' selected' : ''; ?>><?php echo fsp__( 'Custom message + Post Link' ); ?></option>
			<option value="2"<?php echo Helper::getOption( 'telegram_type_of_sharing', '1' ) == '2' ? ' selected' : ''; ?>><?php echo fsp__( 'Custom message' ); ?></option>
			<option value="3"<?php echo Helper::getOption( 'telegram_type_of_sharing', '1' ) == '3' ? ' selected' : ''; ?>><?php echo fsp__( 'Featured image + Custom message' ); ?></option>
			<option value="4"<?php echo Helper::getOption( 'telegram_type_of_sharing', '1' ) == '4' ? ' selected' : ''; ?>><?php echo fsp__( 'Featured image + Custom message + Post Link' ); ?></option>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
    <div class="fsp-settings-col">
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Use the Read more button' ); ?></div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to add a Read more button to the Telegram messages.' ); ?></div>
    </div>
    <div class="fsp-settings-col">
        <div class="fsp-toggle">
            <input type="checkbox" name="fs_telegram_use_read_more_button" class="fsp-toggle-checkbox" id="useReadMoreButton" <?php echo Helper::getOption( 'telegram_use_read_more_button', '1' ) ? 'checked' : ''; ?>>
            <label class="fsp-toggle-label" for="useReadMoreButton"></label>
        </div>
    </div>
</div>
<div id="fspUseCustomButton" class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Telegram Read More button' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can change the Read More button to your language or leave the input empty to use the default button name.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<input type="text" name="fs_telegram_custom_button_text" class="fsp-form-input" value="<?php echo Helper::getOption( 'telegram_custom_button_text', '' )?>">
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Cut the exceeded part of the custom message' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Telegram limits the message length to a maximum of 4096 characters (1024 characters when a media is attached to the message). By enabling the option, the first 4096 (1024) characters of your custom message will be shared; otherwise, the post won\'t be shared because of the limit.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_telegram_autocut_text" class="fsp-toggle-checkbox" id="fs_telegram_autocut_text" <?php echo Helper::getOption( 'telegram_autocut_text', '1' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_telegram_autocut_text"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Send without sound' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to send messages without notification sound' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_telegram_silent_notifications" class="fsp-toggle-checkbox" id="fs_telegram_silent_notifications" <?php echo Helper::getOption( 'telegram_silent_notifications', '0' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_telegram_silent_notifications"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row fsp-is-collapser">
	<div class="fsp-settings-collapser">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Custom message' ); ?>
			<i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
		</div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the text of the shared post as you like by using the available keywords. You can add the keywords to the custom message section easily by clicking on the keyword.<br>&lt;b&gt;<b>bold</b>&lt;/b&gt;, &lt;u&gt;<u>underlined</u>&lt;/u&gt;, &lt;i&gt;<i>italic</i>&lt;/i&gt;, &lt;a href=&quot;link&quot;&gt;link text&lt;/a&gt; tags are supported.', [], FALSE ); ?></div>
	</div>
	<div class="fsp-settings-collapse">
		<div class="fsp-settings-col">
			<div class="fsp-settings-col-title"><?php echo fsp__( 'Text' ); ?></div>
			<div class="fsp-custom-post" data-preview="fspCustomPostPreview">
				<textarea name="fs_post_text_message_telegram" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_text_message_telegram', "{title}" ) ); ?></textarea>
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
		<div class="fsp-settings-col">
			<div class="fsp-settings-col-title"><?php echo fsp__( 'Preview' ); ?></div>
			<div class="fsp-settings-preview">
				<div class="fsp-settings-preview-header">
					<img src="#" onerror="FSPoster.no_photo( this );">
					<div class="fsp-settings-preview-header-title"><?php echo get_bloginfo( 'name' ); ?></div>
					<i class="fas fa-ellipsis-v fsp-settings-preview-dots"></i>
				</div>
				<div class="fsp-settings-preview-body">
					<div class="fsp-settings-preview-body-msg">
						<div class="fsp-settings-preview-body-image">
							<img src="<?php echo Pages::asset( 'Settings', 'img/story.svg' ); ?>">
						</div>
						<span id="fspCustomPostPreview" class="fsp-settings-preview-body-text"><?php echo esc_html( Helper::getOption( 'post_text_message_telegram', "{title}" ) ); ?></span>
					</div>
				</div>
				<div class="fsp-settings-preview-footer">
					<div class="fsp-settings-preview-footer-icon">
						<i class="fas fa-paperclip"></i>
					</div>
					<div class="fsp-settings-preview-footer-msg">
						Message...
					</div>
					<div class="fsp-settings-preview-footer-icon">
						<i class="far fa-smile"></i>
					</div>
					<div class="fsp-settings-preview-footer-icon">
						<i class="far fa-paper-plane"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
