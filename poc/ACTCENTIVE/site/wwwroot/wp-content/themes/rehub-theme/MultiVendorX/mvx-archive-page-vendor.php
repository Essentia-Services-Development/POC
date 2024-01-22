<?php
defined('ABSPATH') || exit;

get_header('shop');
wp_enqueue_style('rhwoocomments');

/**
 * Hook: mvx_before_main_content.
 *
 */

do_action('mvx_before_main_content');

global $MVX;

?>
<!-- CONTENT -->
<div class="rh-container">
<style scope>
	.mvx-middle-sec{min-width:69%}
	.mvx-bannerleft{display: flex;flex-direction: column;justify-content: flex-end;}
	.mvx-tab-header{margin-bottom: 20px}
	.mvx-tablink{font-family:inherit;padding: 10px 30px;}
	.mvx-theme01 .mvx-banner-below{padding:20px}
	.mvx-contact-deatil{flex-wrap:wrap; opacity:0.7}
	.mvx-contact-deatil i{color:#2196f3}
	.mvx-tab-header{padding:0}
	.mvx-tablink.active::before{width:100%; height:2px}
	@media (min-width:768px){
		.mvx_bannersec_start, .banner-img-cls .mvx-imgcls{height: auto;}
	}
	.mvx-theme01 .mvx-heading{margin-top:0}
	.mvx-quick-info-wrapper form#respond input, .mvx-quick-info-wrapper form#respond textarea{font-size: 90%; width: 100%; margin-bottom: 10px}
	.mvx-quick-info-wrapper p{text-align: center;font-size: 90%}
	.mvx_widget_vendor_product_categories ul li a{color: #111; text-decoration: none}
	.mvx_vendor_rating{text-align: right;}
	.mvx_total_rating_number{display: none;}
	.sidebar-box{
		box-shadow: rgb(0 0 0 / 15%) 0px 1px 2px;
		overflow: hidden;
		background: #fff;
		padding: 20px;
		position: relative;
		transition: all .35s cubic-bezier(.39,.58,.57,1);
		border-top: 1px solid #efefef;
	}
	.sidebar_heading {
    font-weight: 700;
    font-size: 16px;
    line-height: 18px;
    text-align: center;
    margin: -20px -20px 20px;
    padding: 12px;
    color: #000;
    background: #f7f7f7;
	}
	.sidebar_heading .widget-title{margin:0 !important}
</style>
	<div class="rh-content-wrap clearfix">
		<!-- Main Side -->
		<div class="main-side woocommerce page clearfix full_width">
			<article class="mvxpost" id="page-<?php the_ID(); ?>">
				<header class="woocommerce-products-header">

					<?php
					/**
					 * Hook: mvx_archive_description.
					 *
					 */
					do_action('mvx_archive_description');
					?>
				</header>
				<?php /**
				 * Hook: mvx_store_tab_contents.
				 *
				 * Output mvx store widget
				 */

				do_action('mvx_store_tab_widget_contents');
				?>

			</article>
		</div>
		<!-- /Main Side -->

	</div>
</div>
<!-- /CONTENT -->

<?php


/**
 * Hook: mvx_after_main_content.
 *
 */
do_action('mvx_after_main_content');

/**
 * Hook: mvx_sidebar.
 *
 */
// deprecated since version 3.0.0 with no alternative available
// do_action( 'mvx_sidebar' );

get_footer('shop');