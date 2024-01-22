<?php

namespace FSPoster\App\Pages\Settings\Views;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Enable Open Graph tags' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Open Graph meta tags display post images when shared on social media. If you have tags added to posts by other plugins, such as SEO plugins, you can disable the option.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_meta_tags_enable_open_graph" class="fsp-toggle-checkbox" id="fspEnableOpenGraph" <?php echo Helper::getOption( 'meta_tags_enable_open_graph', 0 ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fspEnableOpenGraph"></label>
		</div>
	</div>
</div>

<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Enable Twitter Card meta tags' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Twitter Card meta tags display post images when shared on Twitter. If you have tags added to posts by other plugins, such as SEO plugins, you can disable the option.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_meta_tags_enable_twitter_tags" class="fsp-toggle-checkbox" id="fspEnableTwitterTags" <?php echo Helper::getOption( 'meta_tags_enable_twitter_tags', 0 ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fspEnableTwitterTags"></label>
		</div>
	</div>
</div>

<div id="fspMetaTagsAllowedPostTypesRow" class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Allowed post types' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Add post types that you want to add meta tags.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select class="fsp-form-input select2-init" id="fs_meta_tags_allowed_post_types" name="fs_meta_tags_allowed_post_types[]" multiple>
			<?php
			$selectedTypes = explode( '|', Helper::getOption( 'meta_tags_allowed_post_types', 'post|page|product' ) );
			foreach ( get_post_types( [], 'object' ) as $post_type )
			{
				echo '<option value="' . htmlspecialchars( $post_type->name ) . '"' . ( in_array( $post_type->name, $selectedTypes ) ? ' selected' : '' ) . '>' . htmlspecialchars( $post_type->label ) . '</option>';
			}
			?>
		</select>
	</div>
</div>
