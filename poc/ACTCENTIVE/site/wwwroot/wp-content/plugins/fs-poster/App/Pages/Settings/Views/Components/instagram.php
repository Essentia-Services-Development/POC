<?php

namespace FSPoster\App\Pages\Settings\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Settings', 'css/fsp-shared-preview.css' ); ?>">
<link rel="stylesheet" href="<?php echo Pages::asset( 'Settings', 'css/fsp-instagram-preview.css' ); ?>">
<?php if ( $ig_story_custom_font = Helper::getOption( 'instagram_story_custom_font', '' ) ) {
    $ig_story_custom_font_url = str_replace( $_SERVER[ 'DOCUMENT_ROOT' ], site_url() . '/', wp_normalize_path( $ig_story_custom_font ) );
    ?>
    <style>
        @font-face {
            font-family: FS-Poster-ig-font;
            src: url( '<?php echo $ig_story_custom_font_url; ?>' );
            font-weight: normal;
            font-style: normal;
        }

        .fsp-settings-igstory-title, .fsp-settings-igstory-link-text {
            font-family: FS-Poster-ig-font;
        }
    </style>
<?php } ?>

<div class="fsp-settings-row">
    <div class="fsp-settings-col">
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Share Instagram posts on' ); ?></div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'Select where to share posts on Instagram.' ); ?></div>
    </div>
    <div class="fsp-settings-col">
        <select name="fs_instagram_post_in_type" id="fspPostingTypeSelector" class="fsp-form-select">
            <option value="1"<?php echo Helper::getOption( 'instagram_post_in_type', '1' ) == '1' ? ' selected' : ''; ?>>Profile only</option>
            <option value="2"<?php echo Helper::getOption( 'instagram_post_in_type', '1' ) == '2' ? ' selected' : ''; ?>>Story only</option>
            <option value="3"<?php echo Helper::getOption( 'instagram_post_in_type', '1' ) == '3' ? ' selected' : ''; ?>>Profile and Story</option>
        </select>
    </div>
</div>
<div class="fsp-settings-row" id="fspPostingTypeRow">
    <div class="fsp-settings-col">
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Posting type' ); ?></div>
        <div class="fsp-settings-label-subtext">
            <?php echo fsp__( 'Featured image - Shares the featured image and the custom message;<br>All post images - Shares all post images as an album and the custom message.', [], FALSE ); ?>
        </div>
    </div>
    <div class="fsp-settings-col">
        <select name="fs_instagram_posting_type" class="fsp-form-select">
            <option value="1"<?php echo Helper::getOption( 'instagram_posting_type', '1' ) == '1' ? ' selected' : ''; ?>>Featured image</option>
            <option value="2"<?php echo Helper::getOption( 'instagram_posting_type', '1' ) == '2' ? ' selected' : ''; ?>>All post images</option>
        </select>
    </div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Share multiple images on story' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enter how many images from the post you want to share on story (max 10 images)' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<input type="number" name="fs_instagram_story_images_count" class="fsp-form-input" id="fs_instagram_story_images_count" value="<?php echo Helper::getOption( 'instagram_story_images_count', '1' )?>" min="1" max="10">
	</div>
</div>
<div class="fsp-settings-row">
    <div class="fsp-settings-col">
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Upload custom font files' ); ?></div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can upload and use a <a href="https://www.fs-poster.com/documentation/commonly-encountered-issues#issue12" target="_blank">custom font</a> for Instagram stories.', [], FALSE ); ?></div>
    </div>
    <div class="fsp-settings-col">
        <?php if ( $ig_story_custom_font ) { ?>
            <button id="fspIgCustomFontReset" type="button" class="fsp-button fsp-is-red fsp-tooltip" data-title="<?php echo fsp__( 'Reset to default.' ); ?>">
                <?php echo fsp__( 'RESET' ); ?>
            </button>
        <?php } ?>
        <input id="fspIgCustomFontResetInput" name="fs_instagram_story_custom_font_reset" type="number" value="0" class="fsp-hide">
        <button id="fspIgCustomFontButton" type="button" class="fsp-button fsp-is-gray">
            <?php echo fsp__( 'CHOOSE FONT' ); ?>
        </button>
        <input id="fspIgCustomFontInput" name="fs_instagram_story_custom_font" type="file" class="fsp-hide" accept=".ttf, font/ttf">
    </div>
</div>
<div class="fsp-settings-row">
    <div class="fsp-settings-col">
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Cut the exceeded part of the custom message' ); ?></div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'Instagram limits the post content length to a maximum of 2200 characters. By enabling the option, the first 2200 characters of your custom message will be shared; otherwise, the post won\'t be shared because of the limit.' ); ?></div>
    </div>
    <div class="fsp-settings-col">
        <div class="fsp-toggle">
            <input type="checkbox" name="fs_instagram_autocut_text" class="fsp-toggle-checkbox" id="fs_instagram_autocut_text" <?php echo Helper::getOption( 'instagram_autocut_text', '1' ) ? 'checked' : ''; ?>>
            <label class="fsp-toggle-label" for="fs_instagram_autocut_text"></label>
        </div>
    </div>
</div>
<div class="fsp-settings-row">
    <div class="fsp-settings-col">
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Update the Instagram Bio link' ); ?></div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to update the Instagram Bio link to the last shared post link. The login&pass method supports the feature.' ); ?></div>
    </div>
    <div class="fsp-settings-col">
        <div class="fsp-toggle">
            <input type="checkbox" name="fs_instagram_update_bio_link" class="fsp-toggle-checkbox" id="fs_instagram_update_bio_link" <?php echo Helper::getOption( 'instagram_update_bio_link', '0' ) ? 'checked' : ''; ?>>
            <label class="fsp-toggle-label" for="fs_instagram_update_bio_link"></label>
        </div>
    </div>
</div>
<div class="fsp-settings-row">
    <div class="fsp-settings-col">
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Post a first comment.' ); ?></div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to share a customized message as a first comment. The login&pass and cookie methods support the first comment feature.' ); ?></div>
    </div>
    <div class="fsp-settings-col">
        <div class="fsp-toggle">
            <input type="checkbox" name="fs_post_allow_first_comment_instagram" class="fsp-toggle-checkbox" id="fspIgAllowComment" <?php echo Helper::getOption( 'post_allow_first_comment_instagram', '0' ) ? 'checked' : ''; ?>>
            <label class="fsp-toggle-label" for="fspIgAllowComment"></label>
        </div>
    </div>
</div>
<div id="fspIgFirstComment">
    <div class="fsp-settings-row fsp-is-collapser">
        <div class="fsp-settings-collapser">
            <div class="fsp-settings-label-text"><?php echo fsp__( 'Customize the first comment.' ); ?>
                <i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
            </div>
        </div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the first comment as you like by using the available keywords.' )?></div>
        <div class="fsp-settings-collapse">
            <div class="fsp-settings-col">
                <div class="fsp-settings-col-title"><?php echo fsp__( 'Text' ); ?></div>
                <div class="fsp-custom-post" data-preview="fspCustomPostPreview1">
                    <textarea name="fs_post_first_comment_instagram" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_first_comment_instagram', "" ) ); ?></textarea>
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
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Custom post message' ); ?>
            <i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
        </div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the text of the shared post as you like by using the available keywords. You can add the keywords to the custom message section easily by clicking on the keyword. Please read the <a href="https://www.fs-poster.com/documentation/instagram-empty-caption" target="_blank">Instagram content rules</a> before setting your custom message. Instagram removes captions from the images when you exceed Instagram limits.', [], FALSE ); ?></div>
    </div>
    <div class="fsp-settings-collapse">
        <div class="fsp-settings-col">
            <div class="fsp-settings-col-title"><?php echo fsp__( 'Text' ); ?></div>
            <div class="fsp-custom-post" data-preview="fspCustomPostPreview1">
                <textarea name="fs_post_text_message_instagram" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_text_message_instagram', "{title}" ) ); ?></textarea>
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
                    <i class="fas fa-ellipsis-h fsp-settings-preview-dots"></i>
                </div>
                <div class="fsp-settings-preview-image"></div>
                <div class="fsp-settings-preview-footer">
                    <div class="fsp-settings-preview-controls">
                        <i class="far fa-heart"></i>
                        <i class="far fa-comment"></i>
                        <i class="far fa-paper-plane"></i>
                    </div>
                    <div class="fsp-settings-preview-info">
                        <span class="fsp-settings-preview-info-title"><?php echo get_bloginfo( 'name' ); ?></span>
                        <span id="fspCustomPostPreview1" class="fsp-settings-preview-info-text"><?php echo esc_html( Helper::getOption( 'post_text_message_instagram', "{title}" ) ); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="fsp-settings-row fsp-is-collapser">
    <div class="fsp-settings-collapser">
        <div class="fsp-settings-label-text"><?php echo fsp__( 'Customize story' ); ?>
            <i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
        </div>
        <div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the text of the shared story as you like by using the available keywords. You can add the keywords to the custom message section easily by clicking on the keyword. You can also customize the appearance of the story using available options. You need to use the <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-auto-publish-wordpress-posts-to-instagram" target="_blank">Login&Password method</a>  to share links to stories.', [], FALSE ); ?></div>
    </div>
    <div class="fsp-settings-collapse">
        <div id="fspSettingsIgStoryMessageRow" class="fsp-settings-col fsp-flex-grow-1">
            <div class="fsp-settings-igstory-tabs">
                <div id="fspSettingsIgStoryMessageTab" class="fsp-settings-igstory-tab fsp-is-active" data-tab="message">
                    <?php echo fsp__( 'Customize message', [], FALSE ); ?>
                </div>
                <div id="fspSettingsIgStoryAppearanceTab" class="fsp-settings-igstory-tab" data-tab="appearance">
                    <?php echo fsp__( 'Customize appearance', [], FALSE ); ?>
                </div>
            </div>
            <div class="fsp-settings-col-title"><?php echo fsp__( 'Text' ); ?></div>
            <div class="fsp-custom-post" data-preview="fspStoryTitle">
                <textarea name="fs_post_text_message_instagram_h" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_text_message_instagram_h', "{title}" ) ); ?></textarea>
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
        <div id="fspSettingsIgStoryAppearanceRow" class="fsp-settings-col fsp-flex-grow-1 fsp-hide">
            <div class="fsp-settings-igstory-tabs">
                <div class="fsp-settings-igstory-tab" data-tab="message">
                    <?php echo fsp__( 'Customize message', [], FALSE ); ?>
                </div>
                <div class="fsp-settings-igstory-tab fsp-is-active" data-tab="appearance">
                    <?php echo fsp__( 'Customize appearance', [], FALSE ); ?>
                </div>
            </div>
            <div class="fsp-settings-igcontrols">
                <div class="fsp-settings-igstory-tabs">
                    <div class="fsp-settings-igstory-sub-tab fsp-is-active" data-sub-tab="title">
                        <?php echo fsp__( 'Title', [], FALSE ); ?>
                    </div>
                    <div class="fsp-settings-igstory-sub-tab" data-sub-tab="link">
                        <?php echo fsp__( 'Link', [], FALSE ); ?>
                    </div>
                    <div class="fsp-settings-igstory-sub-tab" data-sub-tab="hashtag">
                        <?php echo fsp__( 'Hashtag', [], FALSE ); ?>
                    </div>
                </div>
                <div id="fspInstagramStoryTitleTab">
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Story background color:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span id="fspStoryBgInput">#</span>
                            <input data-jscolor="{ previewElement: '' }" data-type="story-background" autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_background" value="<?php echo Helper::getOption( 'instagram_story_background', 'ED5458' ); ?>">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Title background:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span id="fspStoryTitleBgInput">#</span>
                            <input autocomplete="off" data-jscolor="{ previewElement: '' }" class="fsp-form-input iginput jscolor" name="fs_instagram_story_title_background" value="<?php echo Helper::getOption( 'instagram_story_title_background', 'FFFFFF' ); ?>" data-type="title-background-color">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Title background opacity:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>%</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_title_background_opacity" value="<?php echo Helper::getOption( 'instagram_story_title_background_opacity', '30' ); ?>" data-type="title-background-opacity">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Title color:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span id="fspStoryTitleColorInput">#</span>
                            <input autocomplete="off" data-jscolor="{ previewElement: '' }" class="fsp-form-input iginput" name="fs_instagram_story_title_color" value="<?php echo Helper::getOption( 'instagram_story_title_color', 'FFFFFF' ); ?>" data-type="title-color">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Title top offset:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_title_top" value="<?php echo Helper::getOption( 'instagram_story_title_top', '125' ); ?>" data-type="title-top">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Title left offset:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_title_left" value="<?php echo Helper::getOption( 'instagram_story_title_left', '30' ); ?>" data-type="title-left">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Title width:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_title_width" value="<?php echo Helper::getOption( 'instagram_story_title_width', '660' ); ?>" data-type="title-width">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Title font size:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_title_font_size" value="<?php echo Helper::getOption( 'instagram_story_title_font_size', '30' ); ?>" data-type="title-font-size">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-form-checkbox-group">
                            <input id="fspRTLDirection" type="checkbox" class="fsp-form-checkbox" name="fs_instagram_story_title_rtl" <?php echo Helper::getOption( 'instagram_story_title_rtl', 'off' ) == 'on' ? 'checked' : ''; ?>>
                            <label for="fspRTLDirection">
                                <?php echo fsp__( 'RTL direction' ); ?>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="fspInstagramStoryLinkTab" class="fsp-hide">
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Link background:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span id="fspStoryLinkBgInput">#</span>
                            <input autocomplete="off" data-jscolor="{ previewElement: '' }" class="fsp-form-input iginput jscolor" name="fs_instagram_story_link_background" value="<?php echo Helper::getOption( 'instagram_story_link_background', 'FFFFFF' ); ?>" data-type="link-background-color">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Link background opacity:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>%</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_link_background_opacity" value="<?php echo Helper::getOption( 'instagram_story_link_background_opacity', '100' ); ?>" data-type="link-background-opacity">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Link color:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span id="fspStoryLinkColorInput">#</span>
                            <input autocomplete="off" data-jscolor="{ previewElement: '' }" class="fsp-form-input iginput" name="fs_instagram_story_link_color" value="<?php echo Helper::getOption( 'instagram_story_link_color', '#1A91D0' ); ?>" data-type="link-color">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Link top offset:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_link_top" value="<?php echo Helper::getOption( 'instagram_story_link_top', '1000' ); ?>" data-type="link-top">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Link left offset:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_link_left" value="<?php echo Helper::getOption( 'instagram_story_link_left', '30' ); ?>" data-type="link-left">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Link width:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_link_width" value="<?php echo Helper::getOption( 'instagram_story_link_width', '660' ); ?>" data-type="link-width">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Link font size:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_link_font_size" value="<?php echo Helper::getOption( 'instagram_story_link_font_size', '50' ); ?>" data-type="link-font-size">
                        </div>
                    </div>
                </div>

                <div id="fspInstagramStoryHashtagTab" class="fsp-hide">
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Hashtag text:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span id="fspStoryHashtagTextInput">#</span>
                            <input id="fspStoryHashtagInput" type="text" autocomplete="off" class="fsp-form-input" name="fs_story_hashtag_text_instagram" value="<?php echo Helper::getOption( 'story_hashtag_text_instagram', '' ); ?>">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Hashtag background:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span id="fspStoryHashtagBgInput">#</span>
                            <input autocomplete="off" data-jscolor="{ previewElement: '' }" class="fsp-form-input iginput jscolor" name="fs_instagram_story_hashtag_background" value="<?php echo Helper::getOption( 'instagram_story_hashtag_background', 'FFFFFF' ); ?>" data-type="hashtag-background-color">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Hashtag background opacity:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>%</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_hashtag_background_opacity" value="<?php echo Helper::getOption( 'instagram_story_hashtag_background_opacity', '100' ); ?>" data-type="hashtag-background-opacity">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Hashtag color:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span id="fspStoryHashtagColorInput">#</span>
                            <input autocomplete="off" data-jscolor="{ previewElement: '' }" class="fsp-form-input iginput" name="fs_instagram_story_hashtag_color" value="<?php echo Helper::getOption( 'instagram_story_hashtag_color', '#1A91D0' ); ?>" data-type="hashtag-color">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Hashtag top offset:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_hashtag_top" value="<?php echo Helper::getOption( 'instagram_story_hashtag_top', '700' ); ?>" data-type="hashtag-top">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Hashtag left offset:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_hashtag_left" value="<?php echo Helper::getOption( 'instagram_story_hashtag_left', '30' ); ?>" data-type="hashtag-left">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Hashtag width:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_hashtag_width" value="<?php echo Helper::getOption( 'instagram_story_hashtag_width', '660' ); ?>" data-type="hashtag-width">
                        </div>
                    </div>
                    <div class="fsp-settings-igcontrol">
                        <div class="fsp-settings-igcontrol-label"><?php echo fsp__( 'Hashtag font size:' ); ?></div>
                        <div class="fsp-settings-igcontrol-input">
                            <span>px</span>
                            <input autocomplete="off" class="fsp-form-input iginput" name="fs_instagram_story_hashtag_font_size" value="<?php echo Helper::getOption( 'instagram_story_hashtag_font_size', '50' ); ?>" data-type="hashtag-font-size">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fsp-settings-col fsp-settings-igstory-container">
            <div id="fspStory" class="fsp-settings-igstory">
                <div id="fspStoryTitle" class="fsp-settings-igstory-title"><?php echo esc_html( Helper::getOption( 'post_text_message_instagram_h', "{title}" ) ); ?></div>
                <div id="fspStoryHashtagText" class="fsp-settings-igstory-hashtag-text"><?php echo esc_html( Helper::getOption( 'story_hashtag_text_instagram', '' ) ); ?></div>
                <div id="fspStoryLinkText" class="fsp-settings-igstory-link-text"><?php echo esc_html( Helper::getOption( 'story_link_text_instagram', "{link}" ) ); ?></div>
                <div class="fsp-settings-igstory-image"></div>
            </div>
        </div>
    </div>
</div>