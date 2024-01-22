<?php

namespace FSPoster\App\Pages\Settings\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Settings', 'css/fsp-shared-preview.css' ); ?>">
<link rel="stylesheet" href="<?php echo Pages::asset( 'Settings', 'css/fsp-facebook-preview.css' ); ?>">

<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Posting type' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Link card view – share the post link with preview and up to 300 characters custom message as title;<br>Title + text - share the post title and the custom message as the text of the Reddit post;<br>Custom message + featured image - share up to 300 characters custom message and the image.<br>All post images - Shares all post images as an album and the custom message.', [], FALSE ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select name="fs_reddit_posting_type" id="fspRedditPostingType" class="fsp-form-select">
			<option value="1" <?php echo Helper::getOption( 'reddit_posting_type', '1' ) == '1' ? 'selected' : ''; ?>><?php echo fsp__( 'Link card view' ); ?></option>
			<option value="2" <?php echo Helper::getOption( 'reddit_posting_type', '1' ) == '2' ? 'selected' : ''; ?>><?php echo fsp__( 'Title + text' ); ?></option>
			<option value="3" <?php echo Helper::getOption( 'reddit_posting_type', '1' ) == '3' ? 'selected' : ''; ?>><?php echo fsp__( 'Custom message + image' ); ?></option>
			<option value="4" <?php echo Helper::getOption( 'reddit_posting_type', '1' ) == '4' ? 'selected' : ''; ?>><?php echo fsp__( 'All post images' ); ?></option>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Cut the exceeded part of the post title' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Reddit limits the post title length to a maximum of 300 characters.  By enabling the option, the first 300 characters of your post title will be shared; otherwise, the post won\'t be shared because of the limit.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_reddit_autocut_title" class="fsp-toggle-checkbox" id="fs_reddit_autocut_title" <?php echo Helper::getOption( 'reddit_autocut_title', '1' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_reddit_autocut_title"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
    <div class="fsp-settings-col">
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Post a first comment (Personal App only)' ); ?></div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to share a customized message as a first comment. You need <a href="documentation/fs-poster-schedule-share-wordpress-posts-to-reddit-automatically" target="_blank">to create your Personal App</a> and add your account to the plugin using this App to use this feature for Reddit.', [], FALSE ); ?></div>
    </div>
    <div class="fsp-settings-col">
        <div class="fsp-toggle">
            <input type="checkbox" name="fs_post_allow_first_comment_reddit" class="fsp-toggle-checkbox" id="fspRedditAllowComment" <?php echo Helper::getOption( 'post_allow_first_comment_reddit', '0' ) ? 'checked' : ''; ?>>
            <label class="fsp-toggle-label" for="fspRedditAllowComment"></label>
        </div>
    </div>
</div>
<div id="fspRedditFirstComment">
    <div class="fsp-settings-row fsp-is-collapser">
        <div class="fsp-settings-collapser">
            <div class="fsp-settings-label-text"><?php echo fsp__( 'Customize the first comment' ); ?>
                <i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
            </div>
        </div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the first comment as you like by using the available keywords.' )?></div>
        <div class="fsp-settings-collapse">
            <div class="fsp-settings-col">
                <div class="fsp-settings-col-title"><?php echo fsp__( 'Text' ); ?></div>
                <div class="fsp-custom-post" data-preview="fspCustomPostPreview1">
                    <textarea name="fs_post_first_comment_reddit" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_first_comment_reddit', "" ) ); ?></textarea>
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
</div>
<div class="fsp-settings-row fsp-is-collapser">
	<div class="fsp-settings-collapser">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Custom message' ); ?>
			<i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
		</div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the text of the shared post as you like by using the available keywords. You can add the keywords to the custom message section easily by clicking on the keyword.' ); ?></div>
	</div>
	<div class="fsp-settings-collapse">
		<div class="fsp-settings-col">
			<div class="fsp-settings-col-title"><?php echo fsp__( 'Text' ); ?></div>
			<div class="fsp-custom-post" data-preview="fspCustomPostPreview">
				<textarea name="fs_post_text_message_reddit" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_text_message_reddit', "{title}" ) ); ?></textarea>
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
					<span id="fspCustomPostPreview" class="fsp-settings-preview-body-text"><?php echo esc_html( Helper::getOption( 'post_text_message_reddit', "{title}" ) ); ?></span>
					<div class="fsp-settings-preview-image"></div>
				</div>
				<div class="fsp-settings-preview-footer">
					<div class="fsp-settings-preview-footer-icon">
						<i class="far fa-comment"></i>
					</div>
					<div class="fsp-settings-preview-footer-icon">
						<i class="fas fa-share"></i>
					</div>
					<div class="fsp-settings-preview-footer-icon">
						<i class="far fa-bookmark"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
