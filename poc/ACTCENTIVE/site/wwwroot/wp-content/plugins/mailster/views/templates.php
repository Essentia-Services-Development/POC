<?php echo mailster()->beacon( array( '611bba8d6ffe270af2a99968', '611bbdd6b55c2b04bf6df15f', '63fbb76e52af714471a17409' ), true ); ?>
<div class="wrap">
<h1><?php esc_html_e( 'Templates', 'mailster' ); ?> <a class="page-title-action upload-template"> <?php esc_html_e( 'Upload Template', 'mailster' ); ?> </a></h1>

<div class="upload-field"><?php mailster( 'templates' )->media_upload_form(); ?></div>

<h2 class="screen-reader-text hide-if-no-js"><?php esc_html_e( 'Filter template list', 'mailster' ); ?></h2>
<div class="wp-filter hide-if-no-js">
	<div class="filter-count">
		<span class="count theme-count"></span>
	</div>

	<ul class="filter-links">
		<li><a href="#" data-sort="installed"><?php _ex( 'Installed', 'templates', 'mailster' ); ?></a></li>
		<li><a href="#" data-sort="free"><?php _ex( 'Free', 'templates', 'mailster' ); ?></a></li>
		<li><a href="#" data-sort="featured"><?php _ex( 'Featured', 'templates', 'mailster' ); ?></a></li>
		<li><a href="#" data-sort="popular"><?php _ex( 'Popular', 'templates', 'mailster' ); ?></a></li>
		<li><a href="#" data-sort="new"><?php _ex( 'Latest', 'templates', 'mailster' ); ?></a></li>
		<li><a href="#" data-sort="updated"><?php _ex( 'Recently Updated', 'templates', 'mailster' ); ?></a></li>
	</ul>

	<form class="search-form" method="get">
		<input type="hidden" name="tab" value="search">
		<label class="screen-reader-text" for="typeselector"><?php esc_html_e( 'Search Templates by', 'mailster' ); ?>:</label>
		<select name="type" id="typeselector">
			<option value="term" selected="selected"><?php esc_html_e( 'Keyword', 'mailster' ); ?></option>
			<option value="author"><?php esc_html_e( 'Author', 'mailster' ); ?></option>
			<option value="tag"><?php esc_html_e( 'Tag', 'mailster' ); ?></option>
			<option value="slug"><?php esc_html_e( 'Slug', 'mailster' ); ?></option>
		</select>
		<label class="screen-reader-text" for="search-templates"><?php esc_html_e( 'Search Templates', 'mailster' ); ?></label>
		<input type="search" name="s" id="search-templates" value="" class="wp-filter-search" placeholder="<?php esc_attr_e( 'Search templates', 'mailster' ); ?>..." aria-describedby="live-search-desc">
		<input type="submit" id="search-submit" class="button hide-if-js" value="<?php esc_attr_e( 'Search Templates', 'mailster' ); ?>">
	</form>

</div>

<div class="notice notice-success notice-alt inline"></div>
<div class="notice notice-alt notice-large inline theme-notice-free"></div>
<div class="notice notice-alt notice-large inline theme-notice-popular"></div>
<div class="notice notice-alt notice-large inline theme-notice-updated"></div>

<h2 class="screen-reader-text hide-if-no-js"><?php esc_html_e( 'Template list', 'mailster' ); ?></h2>
<div class="theme-browser content-filterable _single-theme rendered"></div>

<div class="theme-overlay hidden" tabindex="0" role="dialog" aria-label="Theme Details">
	<div class="theme-backdrop"></div>
	<div class="theme-wrap wp-clearfix" role="document">
		<div class="theme-header">
			<button class="left dashicons dashicons-no"><span class="screen-reader-text"><?php esc_html_e( 'Show previous template', 'mailster' ); ?></span></button>
			<button class="right dashicons dashicons-no"><span class="screen-reader-text"><?php esc_html_e( 'Show next template', 'mailster' ); ?></span></button>
			<button class="close dashicons dashicons-no"><span class="screen-reader-text"><?php esc_html_e( 'Close details dialog', 'mailster' ); ?></span></button>
		</div>
		<div class="theme-about wp-clearfix">
			<div class="theme-screenshots">
				<div class="theme-files nav-tab-wrapper nav-tab-small hide-if-no-js"></div>
				<div class="screenshot">
					<div class="codeeditor">
						<h3></h3>
						<textarea></textarea>
					</div>
					<img src="" alt="">
					<iframe src="" allowTransparency="true" frameBorder="0" sandbox="allow-presentation"></iframe>
				</div>
			</div>
			<div class="theme-info">
				<h2 class="theme-name"></h2>
				<p class="theme-author"></p>
				<p class="theme-description"></p>
				<p class="theme-tags"></p>
			</div>
		</div>

		<div class="theme-actions">
			<div>
				<a href="" class="button default"><?php esc_html_e( 'Use as default', 'mailster' ); ?></a>
				<a href="" class="button button-primary save"><?php esc_html_e( 'Save File', 'mailster' ); ?></a>
				<a href="" class="button edit"><?php esc_html_e( 'Edit File', 'mailster' ); ?></a>
				<a href="<?php echo admin_url( 'post-new.php?post_type=newsletter' ); ?>" class="button button-primary campaign"><?php esc_html_e( 'Create Campaign', 'mailster' ); ?></a>
			</div>
			<a class="button delete-theme"><?php esc_html_e( 'Delete', 'mailster' ); ?></a>
		</div>
	</div>
</div>

<p class="no-themes"><?php esc_html_e( 'No templates found. Try a different search.', 'mailster' ); ?></p>
<span class="spinner"></span>

<p class="clear disclosure description"><?php esc_html_e( 'Disclosure: Some of the links on this page are affiliate links. This means if you click on the link and purchase the item, we may receive an affiliate commission.', 'mailster' ); ?></p>
</div>
