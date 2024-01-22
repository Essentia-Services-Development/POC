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
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Only custom message – shares only the custom message;<br>Custom message and link – shares the custom message and the link;<br>Custom message and featured image - Shares the custom message and the featured image link. Plurk fetches the featured image from the link;<br>Custom message and all images - Shares the custom message and all post image links. Plurk fetches the post images from the links.', [], FALSE ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select name="fs_plurk_posting_type" id="fspPlurkPostingType" class="fsp-form-select">
			<option value="1" <?php echo Helper::getOption( 'plurk_posting_type', '2' ) == '1' ? 'selected' : ''; ?>><?php echo fsp__( 'Only custom message' ); ?></option>
			<option value="2" <?php echo Helper::getOption( 'plurk_posting_type', '2' ) == '2' ? 'selected' : ''; ?>><?php echo fsp__( 'Custom message and link' ); ?></option>
			<option value="3" <?php echo Helper::getOption( 'plurk_posting_type', '2' ) == '3' ? 'selected' : ''; ?>><?php echo fsp__( 'Custom message and featured image' ); ?></option>
			<option value="4" <?php echo Helper::getOption( 'plurk_posting_type', '2' ) == '4' ? 'selected' : ''; ?>><?php echo fsp__( 'Custom message and all images' ); ?></option>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Plurk qualifiers' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'The Qualifier comes before the post content.', [], FALSE ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select name="fs_plurk_qualifier" class="fsp-form-select">
			<option value=":" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == ':' ? 'selected' : ''; ?>><?php echo fsp__( 'none' ); ?></option>
			<option value="shares" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'shares' ? 'selected' : ''; ?>><?php echo fsp__( 'shares' ); ?></option>
			<option value="plays" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'plays' ? 'selected' : ''; ?>><?php echo fsp__( 'plays' ); ?></option>
			<option value="buys" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'buys' ? 'selected' : ''; ?>><?php echo fsp__( 'buys' ); ?></option>
			<option value="sells" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'sells' ? 'selected' : ''; ?>><?php echo fsp__( 'sells' ); ?></option>
			<option value="loves" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'loves' ? 'selected' : ''; ?>><?php echo fsp__( 'loves' ); ?></option>
			<option value="likes" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'likes' ? 'selected' : ''; ?>><?php echo fsp__( 'likes' ); ?></option>
			<option value="hates" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'hates' ? 'selected' : ''; ?>><?php echo fsp__( 'hates' ); ?></option>
			<option value="wants" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'wants' ? 'selected' : ''; ?>><?php echo fsp__( 'wants' ); ?></option>
			<option value="wishes" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'wishes' ? 'selected' : ''; ?>><?php echo fsp__( 'wishes' ); ?></option>
			<option value="needs" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'needs' ? 'selected' : ''; ?>><?php echo fsp__( 'needs' ); ?></option>
			<option value="has" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'has' ? 'selected' : ''; ?>><?php echo fsp__( 'has' ); ?></option>
			<option value="will" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'will' ? 'selected' : ''; ?>><?php echo fsp__( 'will' ); ?></option>
			<option value="hopes" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'hopes' ? 'selected' : ''; ?>><?php echo fsp__( 'hopes' ); ?></option>
			<option value="asks" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'asks' ? 'selected' : ''; ?>><?php echo fsp__( 'asks' ); ?></option>
			<option value="wonders" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'wonders' ? 'selected' : ''; ?>><?php echo fsp__( 'wonders' ); ?></option>
			<option value="feels" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'feels' ? 'selected' : ''; ?>><?php echo fsp__( 'feels' ); ?></option>
			<option value="thinks" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'thinks' ? 'selected' : ''; ?>><?php echo fsp__( 'thinks' ); ?></option>
			<option value="draws" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'draws' ? 'selected' : ''; ?>><?php echo fsp__( 'draws' ); ?></option>
			<option value="is" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'is' ? 'selected' : ''; ?>><?php echo fsp__( 'is' ); ?></option>
			<option value="says" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'says' ? 'selected' : ''; ?>><?php echo fsp__( 'says' ); ?></option>
			<option value="eats" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'eats' ? 'selected' : ''; ?>><?php echo fsp__( 'eats' ); ?></option>
			<option value="writes" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'writes' ? 'selected' : ''; ?>><?php echo fsp__( 'writes' ); ?></option>
			<option value="whispers" <?php echo Helper::getOption( 'plurk_qualifier', ':' ) == 'whispers' ? 'selected' : ''; ?>><?php echo fsp__( 'whispers' ); ?></option>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Limit the custom message characters count' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Plurk limits a post length to a maximum of 360 characters. By enabling the option, the first 360 characters of your custom message will be shared; otherwise, the post won\'t be shared because of the limit. Note that the image links are also counted.', [], FALSE ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_plurk_auto_cut_plurks" class="fsp-toggle-checkbox" id="fs_plurk_auto_cut_plurks"<?php echo Helper::getOption( 'plurk_auto_cut_plurks', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_plurk_auto_cut_plurks"></label>
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
				<textarea name="fs_post_text_message_plurk" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_text_message_plurk', "{title}\n\n{featured_image_url}\n\n{content_short_200}" ) ); ?></textarea>
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
					<span id="fspCustomPostPreview" class="fsp-settings-preview-body-text"><?php echo esc_html( Helper::getOption( 'post_text_message_twitter', "{title}" ) ); ?></span>
					<div class="fsp-settings-preview-image"></div>
				</div>
			</div>
		</div>
	</div>
</div>