<div class="wrap">
<h1><?php esc_html_e( 'Add-ons & Integrations for Mailster', 'mailster' ); ?></h1>

<h2 class="screen-reader-text hide-if-no-js"><?php esc_html_e( 'Filter addon list', 'mailster' ); ?></h2>
<div class="wp-filter hide-if-no-js">
	<div class="filter-count">
		<span class="count theme-count"></span>
	</div>

	<ul class="filter-links">
		<li><a href="#" data-sort="delivery"><?php _ex( 'Delivery', 'add-ons', 'mailster' ); ?></a></li>
		<li><a href="#" data-sort="forms"><?php _ex( 'Forms', 'add-ons', 'mailster' ); ?></a></li>
		<li><a href="#" data-sort="ecommerce"><?php _ex( 'Ecommerce', 'add-ons', 'mailster' ); ?></a></li>
		<li><a href="#" data-sort="membership"><?php _ex( 'Membership', 'add-ons', 'mailster' ); ?></a></li>
	</ul>

	<form class="search-form" method="get">
		<input type="hidden" name="tab" value="search">
		<label class="screen-reader-text" for="typeselector"><?php esc_html_e( 'Search Add-ons by', 'mailster' ); ?>:</label>
		<select name="type" id="typeselector">
			<option value="term" selected="selected"><?php esc_html_e( 'Keyword', 'mailster' ); ?></option>
			<option value="author"><?php esc_html_e( 'Author', 'mailster' ); ?></option>
			<option value="tag"><?php esc_html_e( 'Tag', 'mailster' ); ?></option>
			<option value="slug"><?php esc_html_e( 'Slug', 'mailster' ); ?></option>
		</select>
		<label class="screen-reader-text" for="search-addons"><?php esc_html_e( 'Search Add-ons', 'mailster' ); ?></label>
		<input type="search" name="s" id="search-addons" value="" class="wp-filter-search" placeholder="<?php esc_attr_e( 'Search add-ons', 'mailster' ); ?>..." aria-describedby="live-search-desc">
		<input type="submit" id="search-submit" class="button hide-if-js" value="<?php esc_attr_e( 'Search Add-ons', 'mailster' ); ?>">
	</form>

</div>

<div class="notice notice-success notice-alt inline"></div>
<div class="notice notice-alt notice-large inline theme-notice-free"></div>
<div class="notice notice-alt notice-large inline theme-notice-popular"></div>
<div class="notice notice-alt notice-large inline theme-notice-updated"></div>

<h2 class="screen-reader-text hide-if-no-js"><?php esc_html_e( 'Add-on list', 'mailster' ); ?></h2>
<div class="theme-browser content-filterable rendered"></div>

<div class="theme-overlay hidden" tabindex="0" role="dialog" aria-label="Theme Details">
	<div class="theme-backdrop"></div>
	<div class="theme-wrap wp-clearfix" role="document">
		<div class="theme-header">
			<button class="left dashicons dashicons-no"><span class="screen-reader-text"><?php esc_html_e( 'Show previous addon', 'mailster' ); ?></span></button>
			<button class="right dashicons dashicons-no"><span class="screen-reader-text"><?php esc_html_e( 'Show next addon', 'mailster' ); ?></span></button>
			<button class="close dashicons dashicons-no"><span class="screen-reader-text"><?php esc_html_e( 'Close details dialog', 'mailster' ); ?></span></button>
		</div>
		<div class="theme-about wp-clearfix">
			<div class="theme-screenshots">
				<div class="theme-files nav-tab-wrapper nav-tab-small hide-if-no-js"></div>
				<div class="screenshot">
					<img src="" alt="">
				</div>
			</div>
			<div class="theme-info">
				<h2 class="theme-name"></h2>
				<p class="theme-author"></p>
				<p class="theme-description"></p>
				<p class="theme-tags"></p>
			</div>
		</div>
	</div>
</div>

<p class="no-themes"><?php esc_html_e( 'No add-ons found. Try a different search.', 'mailster' ); ?></p>
<span class="spinner"></span>

<p class="clear disclosure description"><?php esc_html_e( 'Disclosure: Some of the links on this page are affiliate links. This means if you click on the link and purchase the item, we may receive an affiliate commission.', 'mailster' ); ?></p>
</div>
