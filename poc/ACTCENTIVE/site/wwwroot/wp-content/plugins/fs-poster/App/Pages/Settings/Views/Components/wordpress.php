<?php

namespace FSPoster\App\Pages\Settings\Views;

use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Post status' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Define the post status on the remote website.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select name="fs_wordpress_post_status" id="fs_wordpress_post_status" class="fsp-form-select">
			<option value="publish" <?php echo Helper::getOption( 'wordpress_post_status', 'publish' ) === 'publish' ? 'selected' : ''; ?>><?php echo fsp__( 'Publish' ); ?></option>
			<option value="private" <?php echo Helper::getOption( 'wordpress_post_status', 'publish' ) === 'private' ? 'selected' : ''; ?>><?php echo fsp__( 'Private' ); ?></option>
			<option value="draft" <?php echo Helper::getOption( 'wordpress_post_status', 'publish' ) === 'draft' ? 'selected' : ''; ?>><?php echo fsp__( 'Draft' ); ?></option>
			<option value="pending" <?php echo Helper::getOption( 'wordpress_post_status', 'publish' ) === 'pending' ? 'selected' : ''; ?>><?php echo fsp__( 'Pending' ); ?></option>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'The post type availability' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'The post type will be shared as it is on the remote website. In the case, the post type is not available on the remote website, if the option is enabled, the post type will be "Post". If the option is disabled, the post will fail.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_wordpress_posting_type" class="fsp-toggle-checkbox" id="fspWordpressPostingType" <?php echo Helper::getOption( 'wordpress_posting_type', '1' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fspWordpressPostingType"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Post with categories' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to include the categories to the target blog post.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_wordpress_post_with_categories" class="fsp-toggle-checkbox" id="fs_wordpress_post_with_categories"<?php echo Helper::getOption( 'wordpress_post_with_categories', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_wordpress_post_with_categories"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Post with tags' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to include the tags to the target blog post.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_wordpress_post_with_tags" class="fsp-toggle-checkbox" id="fs_wordpress_post_with_tags"<?php echo Helper::getOption( 'wordpress_post_with_tags', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_wordpress_post_with_tags"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Post title' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the title of the post as you like by using the keywords.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<input autocomplete="off" class="fsp-form-input" name="fs_post_title_wordpress" value="<?php echo esc_html( Helper::getOption( 'post_title_wordpress', "{title}" ) ); ?>">
	</div>
</div>
<div class="fsp-settings-row fsp-is-collapser">
	<div class="fsp-settings-collapser">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Post excerpt' ); ?>
			<i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
		</div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the excerpt of the shared post as you like by using the available keywords. You can add the keywords to the custom message section easily by clicking on the keyword.' ); ?></div>
	</div>
	<div class="fsp-settings-collapse">
		<div class="fsp-settings-col">
			<div class="fsp-settings-col-title"><?php echo fsp__( 'Text' ); ?></div>
			<div class="fsp-custom-post" data-preview="fspCustomPostPreview1">
				<textarea name="fs_post_excerpt_wordpress" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_excerpt_wordpress', "{excerpt}" ) ); ?></textarea>
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
<div class="fsp-settings-row fsp-is-collapser">
	<div class="fsp-settings-collapser">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Post content' ); ?>
			<i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
		</div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the text of the shared post as you like by using the available keywords. You can add the keywords to the custom message section easily by clicking on the keyword.' ); ?></div>
	</div>
	<div class="fsp-settings-collapse">
		<div class="fsp-settings-col">
			<div class="fsp-settings-col-title"><?php echo fsp__( 'Text' ); ?></div>
			<div class="fsp-custom-post" data-preview="fspCustomPostPreview2">
				<textarea name="fs_post_text_message_wordpress" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_text_message_wordpress', "{content_full}" ) ); ?></textarea>
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
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key='<img src="{featured_image_url}">'>
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
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key='<a href="{short_link}">{short_link}</a>'>
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
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key='<a href="{link}">{link}</a>'>
						{LINK}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post link' ); ?>"></i>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>