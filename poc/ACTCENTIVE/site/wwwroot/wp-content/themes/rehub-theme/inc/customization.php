<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php ob_start(); ?>
<style type="text/css">
<?php if (rehub_option('rehub_logo_pad') !='') :?>
	@media (min-width: 1025px){
		header .logo-section{padding: <?php echo (int)rehub_option('rehub_logo_pad') ?>px 0;}		
	}
<?php endif; ?>
<?php if (is_page()) :?>
	<?php 
		global $post; 
		$postID = $post->ID;
		$menu_disable = get_post_meta($postID, "menu_disable", true);
		$content_type = get_post_meta($postID, "content_type", true);
	?>
	<?php if ($menu_disable == '1') :?>nav.top_menu, .responsive_nav_wrap{display: none !important;}<?php endif; ?>
	<?php if ($content_type == 'full_post_area') :?>.rh-boxed-container .rh-outer-wrap{width:100% !important; overflow:hidden; background: transparent; box-shadow: none}<?php endif;?>
<?php endif; ?>	
<?php if (rehub_option('rehub_review_color')) :?>
	.rate-line .filled, .rate_bar_wrap .review-top .overall-score, .rate-bar-bar, .top_rating_item .score.square_score, .radial-progress .circle .mask .fill{background-color: <?php echo rehub_option('rehub_review_color') ?> ;}
	.meter-wrapper .meter, .rate_bar_wrap_two_reviews .score_val{border-color: <?php echo rehub_option('rehub_review_color') ?>;}
<?php endif; ?>	
<?php if (rehub_option('rehub_review_color_user')) :?>
	body .user-review-criteria .rate-bar-bar{background-color: <?php echo rehub_option('rehub_review_color_user') ?> ;}
	.userstar-rating span:before{color: <?php echo rehub_option('rehub_review_color_user') ?>;}
	.rate_bar_wrap_two_reviews .user-review-criteria .score_val{border-color: <?php echo rehub_option('rehub_review_color_user') ?>;}
<?php endif; ?>
<?php if (rehub_option('rehub_enable_menu_shadow') ==1) :?>
	<?php if(rehub_option('rehub_header_style') == 'header_five'):?>
		.logo_section_wrap{box-shadow: 0px 15px 30px 0px rgba(119, 123, 146, 0.1)}
	<?php else:?>
		.main-nav{box-shadow: 0 1px 8px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0, 0, 0, 0.03) !important;}
	<?php endif;?>
<?php endif; ?>	
<?php if (rehub_option('header_menuline_type') == 1) :?>
	nav.top_menu > ul > li > a{padding: 6px 12px 10px 12px; font-size: 14px}
<?php elseif (rehub_option('header_menuline_type') == 2) :?>
	nav.top_menu > ul > li > a{padding: 11px 15px 15px 15px; font-size: 17px}
<?php endif; ?>	
<?php if (rehub_option('rehub_nav_font_custom')) :?>
	nav.top_menu > ul > li > a{font-size: <?php echo rehub_option('rehub_nav_font_custom');?>px}
<?php endif; ?>	
<?php if (rehub_option('rehub_nav_font_upper') =='1') :?>
	nav.top_menu > ul > li > a{text-transform: uppercase;}
<?php endif; ?>	
<?php if (rehub_option('rehub_nav_font_light') == '1') :?>
	nav.top_menu > ul > li > a{font-weight: normal; }
<?php endif; ?>
<?php if (rehub_option('rehub_nav_font_border') =='1') :?>
	nav.top_menu > ul > li, .main-nav.dark_style nav.top_menu>ul>li{border:none;}
<?php endif; ?>
<?php if(rehub_option('rehub_nav_font')) : ?>
	.dl-menuwrapper li a, nav.top_menu > ul > li > a, #re_menu_near_logo li, #re_menu_near_logo li {
		font-family:"<?php echo rehub_option('rehub_nav_font'); ?>", trebuchet ms !important;
		<?php if(rehub_option('rehub_nav_font_weight')) : ?>font-weight:<?php echo rehub_option('rehub_nav_font_weight'); ?>;<?php endif; ?>
		<?php if(rehub_option('rehub_nav_font_style')) : ?>font-style:<?php echo rehub_option('rehub_nav_font_style');?>;<?php endif; ?>		
	}
	:root {
  	--rehub-nav-font: <?php echo ''.rehub_option('rehub_nav_font'); ?>;
	}
<?php endif; ?>	
<?php if(rehub_option('rehub_headings_font')) : ?>
	.rehub_feat_block div.offer_title,
	.rh_wrapper_video_playlist .rh_video_title_and_time .rh_video_title,
	.main_slider .flex-overlay h2,
	.related_articles ul li > a,
	h1,
	h2,
	h3,
	h4,
	h5,
	h6,
	.widget .title,
	.title h1,
	.title h5,
	.related_articles .related_title,
	#comments .title_comments,
	.commentlist .comment-author .fn,
	.commentlist .comment-author .fn a,
	.rate_bar_wrap .review-top .review-text span.review-header,
	.wpsm-numbox.wpsm-style6 span.num,
	.wpsm-numbox.wpsm-style5 span.num,
	.rehub-main-font,
	.logo .textlogo,
	.wp-block-quote.is-style-large,
	.comment-respond h3, 
	.related_articles .related_title,
	.re_title_inmodal{
		font-family:"<?php echo rehub_option('rehub_headings_font'); ?>", trebuchet ms;
		<?php if(rehub_option('rehub_headings_font_style')) : ?>font-style:<?php echo rehub_option('rehub_headings_font_style'); ?>;<?php endif; ?>			
	}
	.main_slider .flex-overlay h2,
	h1,
	h2,
	h3,
	h4,
	h5,
	h6,
	.title h1,
	.title h5,
	.comment-respond h3{
		font-weight: <?php if(rehub_option('rehub_headings_font_weight')) : ?><?php echo rehub_option('rehub_headings_font_weight'); ?><?php else:?>700<?php endif; ?>;
		<?php if(rehub_option('rehub_headings_font_upper') =='1') : ?>text-transform:uppercase;<?php endif; ?>
	}
	:root {
  	--rehub-head-font: <?php echo ''.rehub_option('rehub_headings_font'); ?>;
	}
<?php endif; ?>
<?php if(rehub_option('rehub_btn_font')) : ?>
	.priced_block .btn_offer_block,
	.rh-deal-compact-btn,
	.wpsm-button.rehub_main_btn,
	.woocommerce div.product p.price,
	.btn_more,
	input[type="submit"], 
	input[type="button"], 
	input[type="reset"],
	.vc_btn3,
	.re-compare-destin.wpsm-button,
	.rehub-btn-font,
	.vc_general.vc_btn3,
	.woocommerce a.woo_loop_btn,
	.woocommerce input.button.alt,
	.woocommerce a.add_to_cart_button,
	.woocommerce .single_add_to_cart_button,
	.woocommerce div.product form.cart .button,
	.woocommerce .checkout-button.button,
	#buddypress button.submit,
	.wcv-grid a.button,
	input.gmw-submit,
	#ws-plugin--s2member-profile-submit,
	#rtmedia_create_new_album,
	input[type="submit"].dokan-btn-theme,
	a.dokan-btn-theme:not(.dashicons),
	.dokan-btn-theme:not(.dashicons), 
	.woocommerce .single_add_to_cart_button,
	.woocommerce .woo-button-area .masked_coupon,
	.woocommerce .summary .price, 
	.wvm_plan,
	.wp-block-button .wp-block-button__link,
	.widget_merchant_list .buttons_col a{
		font-family:"<?php echo rehub_option('rehub_btn_font'); ?>", trebuchet ms;
		<?php if(rehub_option('rehub_btn_font_style')) : ?>font-style:<?php echo rehub_option('rehub_btn_font_style'); ?>;<?php endif; ?>
		<?php if(rehub_option('rehub_btn_font_weight')) : ?>font-weight:<?php echo rehub_option('rehub_btn_font_weight'); ?>;<?php endif; ?>

	}
	<?php if(defined( 'WCFMmp_TOKEN' )):?>
		.wcfm_membership_title, #wcfm-main-contentainer input.wcfm_submit_button, #wcfm-main-contentainer button.wcfm_submit_button, #wcfm-main-contentainer a.wcfm_submit_button, #wcfm-main-contentainer .wcfm_add_category_bt, #wcfm-main-contentainer .wcfm_add_attribute, #wcfm-main-contentainer .wcfm_add_attribute_term, #wcfm-main-contentainer input.upload_button, #wcfm-main-contentainer input.remove_button, #wcfm-main-contentainer .dataTables_wrapper .dt-buttons .dt-button, #wcfm_vendor_approval_response_button, #wcfm_bulk_edit_button, #wcfm_enquiry_submit_button{
			font-family:"<?php echo rehub_option('rehub_btn_font'); ?>", trebuchet ms;
		<?php if(rehub_option('rehub_btn_font_style')) : ?>font-style:<?php echo rehub_option('rehub_btn_font_style'); ?>;<?php endif; ?>
		<?php if(rehub_option('rehub_btn_font_weight')) : ?>font-weight:<?php echo rehub_option('rehub_btn_font_weight'); ?>;<?php endif; ?>
	}
	<?php endif; ?>
	:root {
  	--rehub-btn-font: <?php echo ''.rehub_option('rehub_btn_font'); ?>;
	}
<?php endif; ?>
<?php if(rehub_option('rehub_btn_font_upper_dis')) : ?>
.priced_block .btn_offer_block,
.wpsm-button.rehub_main_btn,
.priced_block .button,
.woocommerce .single_add_to_cart_button,
.woocommerce .woo-button-area .masked_coupon,
.wc_vendors_dash_links a.button,
.woocommerce a.button,
.woocommerce-page a.button,
.woocommerce button.button,
.woocommerce-page button.button,
.woocommerce input.button,
.woocommerce-page input.button,
.woocommerce a.woo_loop_btn,
.woocommerce a.add_to_cart_button,
.woocommerce-page a.add_to_cart_button,
.wcv-grid a.button{
	text-transform: none;
}
<?php endif; ?>	
<?php if(rehub_option('rehub_body_font')) : ?>
	.sidebar, .rehub-body-font, body {
		font-family:"<?php echo rehub_option('rehub_body_font'); ?>", arial !important;
		<?php if(rehub_option('rehub_body_font_weight')) : ?>font-weight:<?php echo rehub_option('rehub_body_font_weight'); ?>;<?php endif; ?>
		<?php if(rehub_option('rehub_body_font_style')) : ?>font-style:<?php echo rehub_option('rehub_body_font_style'); ?>;<?php endif; ?>			
	}
	:root {
  	--rehub-body-font: <?php echo ''.rehub_option('rehub_body_font'); ?>;
	}
<?php endif; ?>	
<?php if(rehub_option('body_font_size')) : ?>
	<?php 
		$sizearray = array_map( 'trim', explode( ":", rehub_option('body_font_size') ) );
	?>
	.post, body .post-readopt .post-inner, body .post-readopt:not(.main-side), body .post-readopt .post, .post p {
		font-size:<?php echo intval($sizearray[0]);?>px;
		line-height: <?php echo (!empty($sizearray[1])) ? intval($sizearray[1]) : intval($sizearray[0])+12;?>px;	
	}
<?php endif; ?>		
<?php if(rehub_option('rehub_custom_color_nav') !='') : ?>
	header .main-nav, .main-nav.dark_style, .header_one_row .main-nav{
		background: none repeat scroll 0 0 <?php echo rehub_option('rehub_custom_color_nav'); ?>!important;
		box-shadow: none;			
	}
	.main-nav{ border-bottom: none;border-top: none;}
	.dl-menuwrapper .dl-menu{margin: 0 !important}
<?php endif; ?>	
<?php if(rehub_option('rehub_custom_color_top') !='') : ?>
	.header_top_wrap{
		background: none repeat scroll 0 0 <?php echo rehub_option('rehub_custom_color_top'); ?>!important;			
	}
	.header-top, .header_top_wrap{ border: none !important}
<?php endif; ?>	
<?php if(rehub_option('rehub_custom_color_top_font') !='') : ?>
	.header_top_wrap .user-ava-intop:after, .header-top .top-nav > ul > li > a, .header-top a.cart-contents, .header_top_wrap .icon-search-onclick:before, .header-top .top-social, .header-top .top-social a{
		color: <?php echo rehub_option('rehub_custom_color_top_font'); ?> !important;			
	}
	.header-top .top-nav li{border: none !important;}
<?php endif; ?>			
<?php if(rehub_option('rehub_custom_color_nav_font') !='') : ?>
	nav.top_menu > ul > li > a{
		color: <?php echo rehub_option('rehub_custom_color_nav_font'); ?> !important;			
	}
	nav.top_menu > ul > li > a:hover{box-shadow: none;}
	<?php if(rehub_option('rehub_mobile_header_color') =='') : ?>
		.responsive_nav_wrap .user-ava-intop:after, .dl-menuwrapper button i, .responsive_nav_wrap .rh-header-icon{
			color: <?php echo rehub_option('rehub_custom_color_nav_font'); ?> !important;			
		}
		.dl-menuwrapper button svg line{stroke:<?php echo rehub_option('rehub_custom_color_nav_font'); ?> !important;}
	<?php endif; ?>
<?php endif; ?>	
<?php if(rehub_option('rehub_mobile_header_bg') !='') : ?>
	.responsive_nav_wrap {
		background: none repeat scroll 0 0 <?php echo rehub_option('rehub_mobile_header_bg'); ?>!important;
		box-shadow: none;			
	}
	.main-nav{ border-bottom: none;border-top: none;}
	.dl-menuwrapper .dl-menu{margin: 0 !important}
<?php endif; ?>	
<?php if(rehub_option('rehub_mobile_header_color') !='') : ?>
	.responsive_nav_wrap .user-ava-intop:after, .dl-menuwrapper button i, .responsive_nav_wrap .rh-header-icon{
		color: <?php echo rehub_option('rehub_mobile_header_color'); ?> !important;			
	}
	.dl-menuwrapper button svg line{stroke:<?php echo rehub_option('rehub_mobile_header_color'); ?> !important;}
<?php endif; ?>

<?php if(rehub_option('rehub_mobtool_bg') !='') : ?>
	#rhNavToolWrap {
		background: none repeat scroll 0 0 <?php echo rehub_option('rehub_mobtool_bg'); ?>!important;			
	}
<?php endif; ?>	
<?php if(rehub_option('rehub_mobtool_color') !='') : ?>
	#rhNavToolWrap .user-ava-intop:after, #rhNavToolbar .rh-header-icon{
		color: <?php echo rehub_option('rehub_mobtool_color'); ?> !important;			
	}
<?php endif; ?>
<?php if(rehub_option('rehub_mobtool_top')) : ?>
	body #rhNavToolWrap {position:relative;box-shadow:none !important}
	#rhNavToolWrap .user-dropdown-intop-menu{bottom:auto !important; top:100%; margin-top:0}
<?php endif; ?>
<?php if (rehub_option('rehub_header_color_background') !='') :?>
	#main_header, .is-sticky .logo_section_wrap, .sticky-active.logo_section_wrap{background-color: <?php echo rehub_option('rehub_header_color_background'); ?> !important }
	.main-nav.white_style{border-top:none}
	nav.top_menu > ul:not(.off-canvas) > li > a:after{top:auto; bottom:0}
	.header-top{border: none;}
<?php endif; ?>
<?php if (rehub_option('rehub_header_background_image') !='') :?>
	<?php $bg_header_url = rehub_option('rehub_header_background_image'); ?>
	<?php $bg_header_position = (rehub_option('rehub_header_background_position') !='') ? rehub_option('rehub_header_background_position') : 'left'; ?>
	<?php $bg_header_repeat = (rehub_option('rehub_header_background_repeat') !='') ? rehub_option('rehub_header_background_repeat') : 'repeat'; ?>
	#main_header {background-image: url("<?php echo ''.$bg_header_url ?>") ; background-position: <?php echo ''.$bg_header_position ?> top; background-repeat: <?php echo ''.$bg_header_repeat ?>}
<?php endif; ?>			
<?php if(rehub_option('rehub_sidebar_left') =='1') : ?>
	<?php if(is_rtl()):?>
		.main-side {float:left;}
		.sidebar{float: right}
	<?php else:?>
		.main-side {float:right;}
		.sidebar{float: left}		
	<?php endif; ?>
<?php endif; ?>
<?php if(rehub_option('rehub_sidebar_left_shop') =='1') : ?>
	<?php if(is_rtl()):?>
		.left-sidebar-archive .main-side {float:left;}
		.left-sidebar-archive .sidebar{float: right}
	<?php else:?>
		.left-sidebar-archive .main-side {float:right;}
		.left-sidebar-archive .sidebar{float: left}		
	<?php endif; ?>
<?php endif; ?>	
<?php if (rehub_option('footer_color_background') !='') :?>
	.footer-bottom{background-color: <?php echo rehub_option('footer_color_background'); ?> !important }
	.footer-bottom .footer_widget{border: none !important}
<?php endif; ?>	
<?php if (rehub_option('footer_background_image') !='') :?>
	<?php $bg_footer_url = rehub_option('footer_background_image'); ?>
	<?php $bg_footer_position = (rehub_option('footer_background_position') !='') ? rehub_option('footer_background_position') : 'left'; ?>
	<?php $bg_footer_repeat = (rehub_option('footer_background_repeat') !='') ? rehub_option('footer_background_repeat') : 'repeat'; ?>
	.footer-bottom{background-image: url("<?php echo ''.$bg_footer_url ?>") ; background-position: <?php echo ''.$bg_footer_position ?> bottom; background-repeat: <?php echo ''.$bg_footer_repeat ?>}
<?php endif; ?>	

/**********MAIN COLOR SCHEME*************/
<?php 
	if (rehub_option('rehub_custom_color')) {
		$maincolor = rehub_option('rehub_custom_color');
	} 
	else {
		$maincolor = REHUB_MAIN_COLOR;
	}
?>
.widget .title:after{border-bottom: 2px solid <?php echo ''.$maincolor; ?>;}

.rehub-main-color-border, nav.top_menu > ul > li.vertical-menu.border-main-color .sub-menu, .rh-main-bg-hover:hover, .wp-block-quote, ul.def_btn_link_tabs li.active a, .wp-block-pullquote{border-color: <?php echo ''.$maincolor; ?>;}
.wpsm_promobox.rehub_promobox { border-left-color: <?php echo ''.$maincolor; ?>!important; }
.color_link{ color: <?php echo ''.$maincolor; ?> !important;}
.featured_slider:hover .score, .top_chart_controls .controls:hover, article.post .wpsm_toplist_heading:before{border-color:<?php echo ''.$maincolor; ?>;}
.btn_more:hover, .tw-pagination .current { border: 1px solid <?php echo ''.$maincolor; ?>; color: #fff }
.rehub_woo_review .rehub_woo_tabs_menu li.current { border-top: 3px solid <?php echo ''.$maincolor; ?>; }
.gallery-pics .gp-overlay {  box-shadow: 0 0 0 4px <?php echo ''.$maincolor; ?> inset; }
.post .rehub_woo_tabs_menu li.current, .woocommerce div.product .woocommerce-tabs ul.tabs li.active{ border-top:2px solid <?php echo ''.$maincolor; ?>;}
.rething_item a.cat{border-bottom-color: <?php echo ''.$maincolor; ?>}
nav.top_menu ul li ul.sub-menu { border-bottom: 2px solid <?php echo ''.$maincolor; ?>; }
.widget.deal_daywoo, .elementor-widget-wpsm_woofeatured .deal_daywoo{border: 3px solid <?php echo ''.$maincolor; ?>; padding: 20px; background: #fff; }
.deal_daywoo .wpsm-bar-bar{background-color: <?php echo ''.$maincolor; ?> !important}

/*BGS*/
#buddypress div.item-list-tabs ul li.selected a span,
#buddypress div.item-list-tabs ul li.current a span,
#buddypress div.item-list-tabs ul li a span,
.user-profile-div .user-menu-tab > li.active > a,
.user-profile-div .user-menu-tab > li.active > a:focus,
.user-profile-div .user-menu-tab > li.active > a:hover,
.news_in_thumb:hover a.rh-label-string,
.news_out_thumb:hover a.rh-label-string,
.col-feat-grid:hover a.rh-label-string,
.carousel-style-deal .re_carousel .controls,
.re_carousel .controls:hover,
.openedprevnext .postNavigation .postnavprev,
.postNavigation .postnavprev:hover,
.top_chart_pagination a.selected,
.flex-control-paging li a.flex-active,
.flex-control-paging li a:hover,
.btn_more:hover,
body .tabs-menu li:hover,
body .tabs-menu li.current,
.featured_slider:hover .score,
#bbp_user_edit_submit,
.bbp-topic-pagination a,
.bbp-topic-pagination a,
.custom-checkbox label.checked:after,
.slider_post .caption,
ul.postpagination li.active a,
ul.postpagination li:hover a,
ul.postpagination li a:focus,
.top_theme h5 strong,
.re_carousel .text:after,
#topcontrol:hover,
.main_slider .flex-overlay:hover a.read-more,
.rehub_chimp #mc_embed_signup input#mc-embedded-subscribe, 
#rank_1.rank_count, 
#toplistmenu > ul li:before,
.rehub_chimp:before,
.wpsm-members > strong:first-child,
.r_catbox_btn,
.wpcf7 .wpcf7-submit,
.wpsm_pretty_hover li:hover,
.wpsm_pretty_hover li.current,
.rehub-main-color-bg,
.togglegreedybtn:after,
.rh-bg-hover-color:hover a.rh-label-string,
.rh-main-bg-hover:hover,
.rh_wrapper_video_playlist .rh_video_currently_playing, 
.rh_wrapper_video_playlist .rh_video_currently_playing.rh_click_video:hover,
.rtmedia-list-item .rtmedia-album-media-count,
.tw-pagination .current,
.dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.active,
.dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li:hover,
.dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.dokan-common-links a:hover,
#ywqa-submit-question,
.woocommerce .widget_price_filter .ui-slider .ui-slider-range,
.rh-hov-bor-line > a:after, nav.top_menu > ul:not(.off-canvas) > li > a:after, .rh-border-line:after,
.wpsm-table.wpsm-table-main-color table tr th,
.rh-hov-bg-main-slide:before,
.rh-hov-bg-main-slidecol .col_item:before,
.mvx-tablink.active::before{ background: <?php echo ''.$maincolor;?>;}
@media (max-width: 767px) {
	.postNavigation .postnavprev{ background: <?php echo ''.$maincolor; ?>; }
}
.rh-main-bg-hover:hover, .rh-main-bg-hover:hover .whitehovered, .user-profile-div .user-menu-tab > li.active > a{color: #fff !important}

/*color*/
a, 
.carousel-style-deal .deal-item .priced_block .price_count ins, 
nav.top_menu ul li.menu-item-has-children ul li.menu-item-has-children > a:before, 
.top_chart_controls .controls:hover,
.flexslider .fa-pulse,
.footer-bottom .widget .f_menu li a:hover,
.comment_form h3 a,
.bbp-body li.bbp-forum-info > a:hover,
.bbp-body li.bbp-topic-title > a:hover,
#subscription-toggle a:before,
#favorite-toggle a:before,
.aff_offer_links .aff_name a,
.rh-deal-price,
.commentlist .comment-content small a,
.related_articles .title_cat_related a,
article em.emph,
.campare_table table.one td strong.red,
.sidebar .tabs-item .detail p a,
.footer-bottom .widget .title span,
footer p a,
.welcome-frase strong, 
article.post .wpsm_toplist_heading:before, 
.post a.color_link,
.categoriesbox:hover h3 a:after,
.bbp-body li.bbp-forum-info > a,
.bbp-body li.bbp-topic-title > a,
.widget .title i,
.woocommerce-MyAccount-navigation ul li.is-active a,
.category-vendormenu li.current a,
.deal_daywoo .title,
.rehub-main-color,
.wpsm_pretty_colored ul li.current a,
.wpsm_pretty_colored ul li.current,
.rh-heading-hover-color:hover h2 a,
.rh-heading-hover-color:hover h3 a,
.rh-heading-hover-color:hover h4 a,
.rh-heading-hover-color:hover h5 a,
.rh-heading-hover-color:hover h3,
.rh-heading-hover-color:hover h2,
.rh-heading-hover-color:hover h4,
.rh-heading-hover-color:hover h5,
.rh-heading-hover-color:hover .rh-heading-hover-item a,
.rh-heading-icon:before,
.widget_layered_nav ul li.chosen a:before,
.wp-block-quote.is-style-large p,
ul.page-numbers li span.current, 
ul.page-numbers li a:hover,  
ul.page-numbers li.active a, 
.page-link > span:not(.page-link-title),
blockquote:not(.wp-block-quote) p,
span.re_filtersort_btn:hover, 
span.active.re_filtersort_btn,
.deal_daywoo .price,
div.sortingloading:after { color: <?php echo ''.$maincolor; ?>; }

<?php if (rehub_option('rehub_color_link')) :?>
	a{color: <?php echo rehub_option('rehub_color_link') ?>;}
<?php endif; ?>

/**********SECONDARY COLOR SCHEME*************/
<?php 
	if (rehub_option('rehub_sec_color')) {
		$secondarycolor = rehub_option('rehub_sec_color');
	} 
	else {
		$secondarycolor = REHUB_SECONDARY_COLOR;
	}
?>
 .page-link > span:not(.page-link-title), 
.widget.widget_affegg_widget .title, 
.widget.top_offers .title, 
.widget.cegg_widget_products .title,
header .header_first_style .search form.search-form [type="submit"], 
header .header_eight_style .search form.search-form [type="submit"],
.filter_home_pick span.active, 
.filter_home_pick span:hover, 
.filter_product_pick span.active,
.filter_product_pick span:hover,
.rh_tab_links a.active, 
.rh_tab_links a:hover, 
.wcv-navigation ul.menu li.active, 
.wcv-navigation ul.menu li:hover a, 
form.search-form [type="submit"],
.rehub-sec-color-bg,
input#ywqa-submit-question,
input#ywqa-send-answer, 
.woocommerce button.button.alt,
.tabsajax span.active.re_filtersort_btn,
.wpsm-table.wpsm-table-sec-color table tr th,
.rh-slider-arrow,
.rh-hov-bg-sec-slide:before,
.rh-hov-bg-sec-slidecol .col_item:before{ background: <?php echo ''.$secondarycolor ?> !important; color: #fff !important; outline: 0}
.widget.widget_affegg_widget .title:after, .widget.top_offers .title:after, .widget.cegg_widget_products .title:after{border-top-color: <?php echo ''.$secondarycolor ?> !important;}  
.page-link > span:not(.page-link-title){border: 1px solid <?php echo ''.$secondarycolor ?>;}  
.page-link > span:not(.page-link-title), .header_first_style .search form.search-form [type="submit"] i{color:#fff !important;}
.rh_tab_links a.active,
.rh_tab_links a:hover,
.rehub-sec-color-border,
nav.top_menu > ul > li.vertical-menu.border-sec-color > .sub-menu,
body .rh-slider-thumbs-item--active{border-color: <?php echo ''.$secondarycolor ?>}
.rh_wrapper_video_playlist .rh_video_currently_playing, .rh_wrapper_video_playlist .rh_video_currently_playing.rh_click_video:hover {background-color: <?php echo ''.$secondarycolor; ?>;box-shadow: 1200px 0 0 <?php echo ''.$secondarycolor; ?> inset;}	
.rehub-sec-color{color: <?php echo ''.$secondarycolor ?>}	
<?php if (rehub_option('theme_subset') == 'repick'):?>
.rehub_chimp{background-color: <?php echo ''.$secondarycolor; ?> !important;border-color: <?php echo ''.$secondarycolor; ?> !important;}
.rehub_chimp h3{color: #fff}
.rehub_chimp p.chimp_subtitle, .rehub_chimp p{color: #eaeaea !important}
<?php endif;?>

/**********BUTTON COLOR SCHEME*************/
<?php 
	$boxshadow = $boxshadowhover = '';
	if (rehub_option('rehub_btnoffer_color')) {
		$btncolor = rehub_option('rehub_btnoffer_color');
	} 	
	else {
		$btncolor = REHUB_BUTTON_COLOR;
	}
	if (rehub_option('rehub_btnoffer_color_hover')) {
		$btncolorhover = rehub_option('rehub_btnoffer_color_hover');
	}else{
		$btncolorhover = $btncolor;
	}
	if (rehub_option('rehub_btnoffer_color_text')) {
		$btncolortext = rehub_option('rehub_btnoffer_color_text');
	}else{
		$btncolortext = REHUB_BUTTON_COLOR_TEXT;
	}
	if (rehub_option('rehub_btnofferhover_color_text')) {
		$btncolorhovertext = rehub_option('rehub_btnofferhover_color_text');
	}else{
		$btncolorhovertext = $btncolortext;
	}	
	$boxshadow = hex2rgba($btncolor, 0.2);		
?>
<?php if (rehub_option('enable_smooth_btn') == 1):?>
	<?php $boxshadow = hex2rgba($btncolor, 0.25);?>
	<?php $boxshadowhover = hex2rgba($btncolorhover, 0.35);?>
	.price_count, .rehub_offer_coupon, #buddypress .dir-search input[type=text], .gmw-form-wrapper input[type=text], .gmw-form-wrapper select, .rh_post_layout_big_offer .priced_block .btn_offer_block, #buddypress a.button, .btn_more, #main_header .wpsm-button, #rh-header-cover-image .wpsm-button, #wcvendor_image_bg .wpsm-button, .rate-bar-bar, .rate-bar, .rehub-main-smooth, .re_filter_instore span.re_filtersort_btn:hover, .re_filter_instore span.active.re_filtersort_btn, .head_search .search-form, .head_search form.search-form input[type="text"], form.search-form input[type="text"]{border-radius: 100px}
	.news .priced_block .price_count, .blog_string  .priced_block .price_count, .main_slider .price_count{margin-right: 5px}
	.right_aff .priced_block .btn_offer_block, .right_aff .priced_block .price_count{border-radius: 0 !important}
	form.search-form.product-search-form input[type="text"]{border-radius: 100px 0 0 100px;}
	form.search-form [type="submit"]{border-radius: 0 100px 100px 0;}
	.rtl form.search-form.product-search-form input[type="text"]{border-radius: 0 100px 100px 0;}
	.rtl form.search-form [type="submit"]{border-radius: 100px 0 0 100px;}
	.woocommerce .products.grid_woo .product, .rh_offer_list .offer_thumb .deal_img_wrap, .rehub_chimp #mc_embed_signup input.email, #mc_embed_signup input#mc-embedded-subscribe, .grid_onsale, .def_btn, input[type="submit"], input[type="button"], input[type="reset"], .wpsm-button, #buddypress div.item-list-tabs ul li a, #buddypress .standard-form input[type=text], #buddypress .standard-form textarea, .blacklabelprice{border-radius: 5px}
	.news-community, .review-top .overall-score, .rate_bar_wrap, .rh_offer_list, .woo-tax-logo, #buddypress form#whats-new-form, #buddypress div#invite-list, #buddypress #send-reply div.message-box, .rehub-sec-smooth, #wcfm-main-contentainer #wcfm-content, .wcfm_welcomebox_header{border-radius: 8px}
	.review-top .overall-score span.overall-text{border-radius: 0 0 8px 8px}
	.coupon_btn:before{display: none;}
	#rhSplashSearch form.search-form input[type="text"], #rhSplashSearch form.search-form [type="submit"]{border-radius: 0 !important}
<?php elseif (rehub_option('enable_smooth_btn') == 2):?>
	<?php $boxshadowhover = hex2rgba($btncolorhover, 0.4);?>
	form.search-form input[type="text"]{border-radius: 4px}
	.news .priced_block .price_count, .blog_string  .priced_block .price_count, .main_slider .price_count{margin-right: 5px}	
	.right_aff .priced_block .btn_offer_block, .right_aff .priced_block .price_count{border-radius: 0 !important}
	form.search-form.product-search-form input[type="text"]{border-radius: 4px 0 0 4px;}
	form.search-form [type="submit"]{border-radius: 0 4px 4px 0;}
	.rtl form.search-form.product-search-form input[type="text"]{border-radius: 0 4px 4px 0;}
	.rtl form.search-form [type="submit"]{border-radius: 4px 0 0 4px;}
	.price_count, .rehub_offer_coupon, #buddypress .dir-search input[type=text], .gmw-form-wrapper input[type=text], .gmw-form-wrapper select, #buddypress a.button, .btn_more, #main_header .wpsm-button, #rh-header-cover-image .wpsm-button, #wcvendor_image_bg .wpsm-button, input[type="text"], textarea, input[type="tel"], input[type="password"], input[type="email"], input[type="url"], input[type="number"], .def_btn, input[type="submit"], input[type="button"], input[type="reset"], .rh_offer_list .offer_thumb .deal_img_wrap, .grid_onsale, .rehub-main-smooth, .re_filter_instore span.re_filtersort_btn:hover, .re_filter_instore span.active.re_filtersort_btn, #buddypress .standard-form input[type=text], #buddypress .standard-form textarea, .blacklabelprice{border-radius: 4px}
	.news-community, .woocommerce .products.grid_woo .product, .rehub_chimp #mc_embed_signup input.email, #mc_embed_signup input#mc-embedded-subscribe, .rh_offer_list, .woo-tax-logo, #buddypress div.item-list-tabs ul li a, #buddypress form#whats-new-form, #buddypress div#invite-list, #buddypress #send-reply div.message-box, .rehub-sec-smooth, .rate-bar-bar, .rate-bar, #wcfm-main-contentainer #wcfm-content, .wcfm_welcomebox_header{border-radius: 5px}
	#rhSplashSearch form.search-form input[type="text"], #rhSplashSearch form.search-form [type="submit"]{border-radius: 0 !important}
<?php endif;?>
/*woo style btn*/
.woocommerce .woo-button-area .masked_coupon,
.woocommerce a.woo_loop_btn,
.woocommerce .button.checkout,
.woocommerce input.button.alt,
.woocommerce a.add_to_cart_button:not(.flat-woo-btn),
.woocommerce-page a.add_to_cart_button:not(.flat-woo-btn),
.woocommerce .single_add_to_cart_button,
.woocommerce div.product form.cart .button,
.woocommerce .checkout-button.button,
.priced_block .btn_offer_block,
.priced_block .button, 
.rh-deal-compact-btn, 
input.mdf_button, 
#buddypress input[type="submit"], 
#buddypress input[type="button"], 
#buddypress input[type="reset"], 
#buddypress button.submit,
.wpsm-button.rehub_main_btn,
.wcv-grid a.button,
input.gmw-submit,
#ws-plugin--s2member-profile-submit,
#rtmedia_create_new_album,
input[type="submit"].dokan-btn-theme, a.dokan-btn-theme, .dokan-btn-theme,
#wcfm_membership_container a.wcfm_submit_button,
.woocommerce button.button,
.rehub-main-btn-bg,
.woocommerce #payment #place_order,
.wc-block-grid__product-add-to-cart.wp-block-button .wp-block-button__link
{ background: none <?php echo ''.$btncolor ?> !important; 
	color: <?php echo ''.$btncolortext ?> !important; 
	fill: <?php echo ''.$btncolortext ?> !important;
	border:none !important;
	text-decoration: none !important; 
	outline: 0; 
	<?php 
		if($boxshadow){
			echo 'box-shadow: -1px 6px 19px '.$boxshadow.' !important;';
		}
	?>		
	<?php 
		if(rehub_option('enable_smooth_btn') == 1){
			echo 'border-radius: 100px !important;';
		}
		elseif (rehub_option('enable_smooth_btn') == 2){
			echo 'border-radius: 4px !important;';
		}
		else{
			echo 'border-radius: 0 !important;';
		}
	?>
}
.rehub-main-btn-bg > a{color: <?php echo ''.$btncolortext ?> !important;}

.woocommerce a.woo_loop_btn:hover,
.woocommerce .button.checkout:hover,
.woocommerce input.button.alt:hover,
.woocommerce a.add_to_cart_button:not(.flat-woo-btn):hover,
.woocommerce-page a.add_to_cart_button:not(.flat-woo-btn):hover,
.woocommerce a.single_add_to_cart_button:hover,
.woocommerce-page a.single_add_to_cart_button:hover,
.woocommerce div.product form.cart .button:hover,
.woocommerce-page div.product form.cart .button:hover,
.woocommerce .checkout-button.button:hover,
.priced_block .btn_offer_block:hover, 
.wpsm-button.rehub_main_btn:hover, 
#buddypress input[type="submit"]:hover, 
#buddypress input[type="button"]:hover, 
#buddypress input[type="reset"]:hover, 
#buddypress button.submit:hover, 
.small_post .btn:hover,
.ap-pro-form-field-wrapper input[type="submit"]:hover,
.wcv-grid a.button:hover,
#ws-plugin--s2member-profile-submit:hover,
.rething_button .btn_more:hover,
#wcfm_membership_container a.wcfm_submit_button:hover,
.woocommerce #payment #place_order:hover,
.woocommerce button.button:hover,
.rehub-main-btn-bg:hover,
.rehub-main-btn-bg:hover > a,
.wc-block-grid__product-add-to-cart.wp-block-button .wp-block-button__link:hover{ 
	background: none <?php echo ''.$btncolorhover ?> !important;
	color: <?php echo ''.$btncolorhovertext ?> !important; 
	border-color: transparent;
	<?php 
		if($boxshadowhover){
			echo 'box-shadow: -1px 6px 13px '.$boxshadowhover.' !important;';
		}else{
			echo 'box-shadow: -1px 6px 13px #d3d3d3 !important;';
		}
	?>
}
.rehub_offer_coupon:hover{border: 1px dashed <?php echo ''.$btncolorhover ?>; }
.rehub_offer_coupon:hover i.far, .rehub_offer_coupon:hover i.fal, .rehub_offer_coupon:hover i.fas{ color: <?php echo ''.$btncolorhover ?>}
.re_thing_btn .rehub_offer_coupon.not_masked_coupon:hover{color: <?php echo ''.$btncolorhover ?> !important}

.woocommerce a.woo_loop_btn:active,
.woocommerce .button.checkout:active,
.woocommerce .button.alt:active,
.woocommerce a.add_to_cart_button:not(.flat-woo-btn):active,
.woocommerce-page a.add_to_cart_button:not(.flat-woo-btn):active,
.woocommerce a.single_add_to_cart_button:active,
.woocommerce-page a.single_add_to_cart_button:active,
.woocommerce div.product form.cart .button:active,
.woocommerce-page div.product form.cart .button:active, 
.woocommerce .checkout-button.button:active,
.wpsm-button.rehub_main_btn:active, 
#buddypress input[type="submit"]:active, 
#buddypress input[type="button"]:active, 
#buddypress input[type="reset"]:active, 
#buddypress button.submit:active,
.ap-pro-form-field-wrapper input[type="submit"]:active,
.wcv-grid a.button:active,
#ws-plugin--s2member-profile-submit:active,
.woocommerce #payment #place_order:active,
input[type="submit"].dokan-btn-theme:active, a.dokan-btn-theme:active, .dokan-btn-theme:active,
.woocommerce button.button:active,
.rehub-main-btn-bg:active,
.wc-block-grid__product-add-to-cart.wp-block-button .wp-block-button__link:active{ 
	background: none <?php echo ''.$btncolor ?> !important; 
	box-shadow: 0 1px 0 #999 !important; 
	top:2px;
	color: <?php echo ''.$btncolorhovertext ?> !important;
}

.rehub_btn_color, .rehub_chimp_flat #mc_embed_signup input#mc-embedded-subscribe {background-color: <?php echo ''.$btncolor ?>; border: 1px solid <?php echo ''.$btncolor ?>; color: <?php echo ''.$btncolortext ?>; text-shadow: none}
.rehub_btn_color:hover{color: <?php echo ''.$btncolorhovertext ?>;background-color: <?php echo ''.$btncolorhover ?>;border: 1px solid <?php echo ''.$btncolorhover ?>;}
.rething_button .btn_more{border: 1px solid <?php echo ''.$btncolor ?>;color: <?php echo ''.$btncolor ?>;}
.rething_button .priced_block.block_btnblock .price_count{color: <?php echo ''.$btncolor ?>; font-weight: normal;}
.widget_merchant_list .buttons_col{background-color: <?php echo ''.$btncolor ?> !important;}
.widget_merchant_list .buttons_col a{color: <?php echo ''.$btncolortext ?> !important;}
.rehub-svg-btn-fill svg{fill:<?php echo ''.$btncolor ?>;}
.rehub-svg-btn-stroke svg{stroke:<?php echo ''.$btncolor ?>;}
@media (max-width: 767px){
	#float-panel-woo-area{border-top: 1px solid <?php echo ''.$btncolor ?>}
}

:root {
  	--rehub-main-color: <?php echo ''.$maincolor; ?>;
	--rehub-sec-color: <?php echo ''.$secondarycolor; ?>;
	--rehub-main-btn-bg: <?php echo ''.$btncolor; ?>;
	<?php if (rehub_option('rehub_color_link')):?>
		--rehub-link-color: <?php echo rehub_option('rehub_color_link');?>;
	<?php elseif($maincolor):?>
		--rehub-link-color: <?php echo ''.$maincolor;?>;
	<?php endif; ?>
}

<?php if(rehub_option('width_layout') =='compact') : ?>
	@media screen and (min-width: 1140px) {
	.rh-boxed-container .rh-outer-wrap{width: 1120px}
	.rh-container, .content{width: 1080px; }
	.centered-container .vc_col-sm-12 > * > .wpb_wrapper, .vc_section > .vc_row, body .elementor-section.elementor-section-boxed > .elementor-container, .wp-block-cover__inner-container{max-width: 1080px} 
	.vc_row.vc_rehub_container > .vc_col-sm-8, .main-side:not(.full_width){width: 755px}
	.vc_row.vc_rehub_container>.vc_col-sm-4, .sidebar, .side-twocol{width: 300px}
	.side-twocol .columns {height: 200px}
	.main_slider.flexslider .slides .slide{ height: 418px; line-height: 418px}
	.main_slider.flexslider{height: 418px}	
	.main-side, .gallery-pics{width:728px;}
	.main_slider.flexslider{width: calc(100% - 325px);}
	.main_slider .flex-overlay h2{ font-size: 36px; line-height: 34px}
	.offer_grid .offer_thumb img, .offer_grid figure img, figure.eq_figure img{height: 130px}
	header .logo { max-width: 300px;}	
	.rh_video_playlist_column_full .rh_container_video_playlist{ width: 320px !important}
  	.rh_video_playlist_column_full .rh_wrapper_player {width: calc(100% - 320px) !important;}
  	.rehub_chimp h3{font-size: 20px}
	.outer_mediad_left{margin-left:-690px !important}
	.outer_mediad_right{margin-left:570px  !important}

	}
<?php elseif(rehub_option('width_layout') =='mini') : ?>
	@media screen and (min-width: 1140px) {
	.rh-boxed-container .rh-outer-wrap{width: 1030px}
	.rh-container, .content{width: 1000px; }
	.centered-container .vc_col-sm-12 > * > .wpb_wrapper, .vc_section > .vc_row, body .elementor-section.elementor-section-boxed > .elementor-container, .wp-block-cover__inner-container{max-width: 1000px} 
	.vc_row.vc_rehub_container > .vc_col-sm-8, .main-side:not(.full_width){width: 700px}
	.vc_row.vc_rehub_container>.vc_col-sm-4, .sidebar, .side-twocol{width: 275px}
	.side-twocol .columns {height: 200px}
	.main_slider.flexslider .slides .slide{ height: 418px; line-height: 418px}
	.main_slider.flexslider{height: 418px}	
	.main-side, .gallery-pics{width:700px;}
	.main_slider.flexslider{width: calc(100% - 275px);}
	.main_slider .flex-overlay h2{ font-size: 36px; line-height: 34px}
	.offer_grid .offer_thumb img, .offer_grid figure img, figure.eq_figure img{height: 130px}
	header .logo { max-width: 275px;}	
	.rh_video_playlist_column_full .rh_container_video_playlist{ width: 275px !important}
  	.rh_video_playlist_column_full .rh_wrapper_player {width: calc(100% - 275px) !important;}
  	.rehub_chimp h3{font-size: 20px}
	.outer_mediad_left{margin-left:-650px !important}
	.outer_mediad_right{margin-left:530px  !important}

	}
<?php elseif(rehub_option('width_layout') =='extended') : ?>
	.compare-full-thumbnails a{width:18%;}
	@media (min-width:1400px){ 
		nav.top_menu > ul > li.vertical-menu > ul > li.inner-700 > .sub-menu{min-width: 850px;}
		.postimagetrend.two_column .wrap img{min-height: 120px}.postimagetrend.two_column .wrap{height: 120px}
		.rh-boxed-container .rh-outer-wrap{width: 1380px}
		.rh-container, .content{width:1330px;} 
		.calcposright{right: calc((100% - 1330px)/2);}
		.rtl .calcposright{left: calc((100% - 1330px)/2); right:auto;}
		.centered-container .vc_col-sm-12 > * > .wpb_wrapper, .vc_section > .vc_row, .wcfm-membership-wrapper, body .elementor-section.elementor-section-boxed > .elementor-container, .wp-block-cover__inner-container{max-width:1330px;}
		.sidebar, .side-twocol, .vc_row.vc_rehub_container > .vc_col-sm-4{ width: 300px} 
		.vc_row.vc_rehub_container > .vc_col-sm-8, .main-side:not(.full_width), .main_slider.flexslider{width:1000px;} 
	}
	@media (min-width:1600px){
		.rehub_chimp h3{font-size: 20px} 
		.rh-boxed-container .rh-outer-wrap{width: 1580px}
		.rh-container, .content{width:1530px;} 
		.calcposright{right: calc((100% - 1530px)/2);}
		.rtl .calcposright{left: calc((100% - 1530px)/2); right:auto;}
		.rh-container.wide_width_restricted{width:1330px;}
		.rh-container.wide_width_restricted .calcposright{right: calc((100% - 1330px)/2);}
		.rtl .rh-container.wide_width_restricted .calcposright{left: calc((100% - 1330px)/2); right:auto;}
		.centered-container .vc_col-sm-12 > * > .wpb_wrapper, .vc_section > .vc_row, .wcfm-membership-wrapper, body .elementor-section.elementor-section-boxed > .elementor-container, .wp-block-cover__inner-container{max-width:1530px;}
		.sidebar, .side-twocol, .vc_row.vc_rehub_container > .vc_col-sm-4{ width: 300px} 
		.vc_row.vc_rehub_container > .vc_col-sm-8, .main-side:not(.full_width), .main_slider.flexslider{width:1200px;} 
	}
<?php endif; ?>	

<?php if(rehub_option('badge_color_1') !='') : ?>
	.re-line-badge.badge_1, .re-ribbon-badge.badge_1 span{background: <?php echo rehub_option('badge_color_1')?>;}
	.re-line-badge.re-line-table-badge.badge_1:before{border-top-color: <?php echo rehub_option('badge_color_1')?>}
	.re-line-badge.re-line-table-badge.badge_1:after{border-bottom-color: <?php echo rehub_option('badge_color_1')?>}
<?php endif;?>
<?php if(rehub_option('badge_color_2') !='') : ?>
	.re-line-badge.badge_2, .re-ribbon-badge.badge_2 span{background: <?php echo rehub_option('badge_color_2')?>;}
	.re-line-badge.re-line-table-badge.badge_2:before{border-top-color: <?php echo rehub_option('badge_color_2')?>}
	.re-line-badge.re-line-table-badge.badge_2:after{border-bottom-color: <?php echo rehub_option('badge_color_2')?>}
<?php endif;?>
<?php if(rehub_option('badge_color_3') !='') : ?>
	.re-line-badge.badge_3, .re-ribbon-badge.badge_3 span{background: <?php echo rehub_option('badge_color_3')?>;}
	.re-line-badge.re-line-table-badge.badge_3:before{border-top-color: <?php echo rehub_option('badge_color_3')?>}
	.re-line-badge.re-line-table-badge.badge_3:after{border-bottom-color: <?php echo rehub_option('badge_color_3')?>}
<?php endif;?>
<?php if(rehub_option('badge_color_4') !='') : ?>
	.re-line-badge.badge_4, .re-ribbon-badge.badge_4 span{background: <?php echo rehub_option('badge_color_4')?>;}
	.re-line-badge.re-line-table-badge.badge_4:before{border-top-color: <?php echo rehub_option('badge_color_4')?>}
	.re-line-badge.re-line-table-badge.badge_4:after{border-bottom-color: <?php echo rehub_option('badge_color_4')?>}
<?php endif;?>

<?php if (rehub_option('rehub_color_background') ) :?>
	<?php $bg_url = (rehub_option('rehub_background_image') !='') ? 'background-image: url("'.rehub_option('rehub_background_image').'");' : 'background-image:none';?>
	<?php $bg_repeat = (rehub_option('rehub_background_repeat') !='') ? 'background-repeat:'.rehub_option('rehub_background_repeat').';' : '';?>
	<?php $bg_position = (rehub_option('rehub_background_position') !='') ? rehub_option('rehub_background_position') : 'left';?>		
	<?php $bg_fixed = (rehub_option('rehub_background_fixed') !='') ? 'background-attachment:fixed;' : '';?>	
	<?php $bg_color = rehub_option('rehub_color_background') ?>	
	body, body.dark_body{background-color: <?php echo ''.$bg_color ?>; background-position: <?php echo ''.$bg_position ?> top; <?php echo ''.$bg_repeat; ?><?php echo ''.$bg_url; ?><?php echo ''.$bg_fixed; ?>}
<?php endif; ?>	
<?php if (rehub_option('rehub_branded_bg_url') ) :?>
	#branded_bg {height: 100%;left: 0;position: fixed;top: 0;width: 100%;z-index: 0;}
	footer, .top_theme, .content, .footer-bottom, header { position: relative; z-index: 1 }
<?php endif; ?>	
<?php if(rehub_option('rehub_bpheader_image') !='') : ?>
	#bprh-full-header-image{background: url("<?php echo rehub_option('rehub_bpheader_image'); ?>") no-repeat center top !important;background-size:cover;}
<?php endif; ?>
<?php if(defined( 'WCFMmp_TOKEN' )){
	$wcfm_store_color_settings = get_option( 'wcfm_store_color_settings', array() );
	$headerbg = (isset($wcfm_store_color_settings['header_background'])) ? $wcfm_store_color_settings['header_background'] : '#ffffff';
	$headerbgopacity = hex2rgba($headerbg, 0.97);
	echo '#wcfmmp-store #wcfm_store_header{
    background: '.$headerbgopacity.' !important;}';
}?>
<?php if(class_exists('Woocommerce')):?>
	<?php if(rehub_option('wooloop_heading_color') !='') : ?>
		.woocommerce .products h3 a{color: <?php echo rehub_option('wooloop_heading_color');?>}
	<?php endif;?>
	<?php if(rehub_option('wooloop_heading_size') !='') : ?>
		body .woocommerce .products .product h3{font-size: <?php echo rehub_option('wooloop_heading_size');?>px}
	<?php endif;?>
	<?php if(rehub_option('wooloop_price_color') !='') : ?>
		body .woocommerce .products .product .price, ul.product_list_widget li span.amount{color: <?php echo rehub_option('wooloop_price_color');?> !important}
	<?php endif;?>
	<?php if(rehub_option('wooloop_price_size') !='') : ?>
		body .woocommerce .products .product .price{font-size: <?php echo rehub_option('wooloop_price_size');?>px !important}
	<?php endif;?>
	<?php if(rehub_option('wooloop_sale_color') !='') : ?>
		.woocommerce .onsale{background-color: <?php echo rehub_option('wooloop_sale_color');?>}
	<?php endif;?>				
<?php endif;?>
<?php if(rehub_option('dark_theme')) : ?>
.dark_body .rh-fullbrowser .top_chart li > div,
.dark_body .rh-fullbrowser .top_chart_controls .controls,
.dark_body.woocommerce .widget_layered_nav ul li a,
.dark_body.woocommerce .widget_layered_nav ul li span,
.dark_body .main-side .wpsm-title *,
.dark_body .main-side .top_rating_text > *,
.dark_body .main-side .top_rating_text,
.dark_body .main-side .top_rating_text a,
.dark_body .main-side .title h1,
.dark_body .main-side .title h5,
.dark_body .main-side #infscr-loading,
.dark_body .sidebar .widget .title,
.dark_body .widget_search i,
.dark_body .sidebar .widget,
.dark_body .sidebar .widget a,
.dark_body .home_picker_next i,
.dark_body .filter_home_pick,
.dark_body .filter_home_pick li span,
.dark_body .woocommerce-result-count,
.dark_body .no_more_posts,
.dark_body .rh_post_layout_outside .title_single_area h1,
.dark_body .sidebar .rh-deal-name a,
.dark_body ul.page-numbers li a,
.dark_body div.sortingloading:after,
.dark_body .re_filter_panel ul.re_filter_ul li span:not(.active),
.dark_body .arc-main-title,
.dark_body .post-readopt .title_single_area h1,
.dark_body .post-readopt .wp-block-quote,
.dark_body .post-readopt .review-top .review-text span.review-header,
.dark_body .post-readopt .rate_bar_wrap_two_reviews .score_val,
.dark_body .post-readopt .rate_bar_wrap_two_reviews .l_criteria span.score_tit,
.dark_body .post-readopt .related_articles,
.dark_body .post-readopt .related_articles h3 a,
.dark_body a.redopt-aut-link,
.dark_body a.redopt-aut-link:hover,
.dark_body a.redopt-aut-link:active,
.dark_body .full_gutenberg .post > h2,
.dark_body .full_gutenberg .post > h1,
.dark_body .full_gutenberg .post > h3,
.dark_body .full_gutenberg .post > h4,
.dark_body .full_gutenberg .post > h5,
.dark_body .full_gutenberg .post > h6,
.dark_body .post-readopt .post-inner-wrapper > h2,
.dark_body .post-readopt .post-inner-wrapper > h3,
.dark_body .post-readopt .post-inner-wrapper > h4,
.dark_body .post-readopt .post-inner-wrapper > h3 a, 
.dark_body .post-readopt .post-inner-wrapper > h2 a,
.dark_body .post-readopt .rh-review-heading h2, 
.dark_body .post-readopt .rh-review-heading h3,
.dark_body .post-readopt .rh-review-heading h4,
.dark_body .post-readopt .wpsm-versus-item,
.dark_body #rh_p_l_fullwidth_opt .title_single_area h1,
.dark_body #rh_p_l_fullwidth_opt .rh-post-excerpt,
body.dark_body .products .col_item h3 a, .errorpage h2{color: #fff;}

.dark_body .woocommerce-breadcrumb, 
.dark_body .woocommerce-breadcrumb a,
.dark_body .rh_post_layout_outside .breadcrumb,
.dark_body .rh_post_layout_outside .breadcrumb a,
.dark_body .rh_post_layout_outside .post-meta span a, 
.dark_body .rh_post_layout_outside .post-meta a.admin, 
.dark_body .rh_post_layout_outside .post-meta a.cat,
.dark_body .top_theme a,
.dark_body .top_theme,
.dark_body .widget .tabs-item .detail h5 a, 
.dark_body .rh_post_layout_outside .title_single_area .post-meta span,
.dark_body .sidebar .price del,
.dark_body .post-readopt .post-inner .post-inner-wrapper > p,
.dark_body .post-readopt .post-inner .post-inner-wrapper > ul,
.dark_body .post-readopt .wp-block-column > p,
.dark_body .post-readopt .review-top .review-text p,
.dark_body .post-readopt .rate-bar-title span,
.dark_body .post-readopt .rate-bar-percent,
.dark_body .post-readopt .wpsm_pros ul li, .post-readopt .wpsm_cons ul li,
.dark_body .post-readopt.single .rh-star-ajax .title_star_ajax,
.dark_body .post-readopt .title_single_area .post-meta,
.dark_body .post-readopt .post-meta span a,
.dark_body .post-readopt .post-meta a.admin,
.dark_body .post-readopt .post-meta a.cat,
.dark_body .post-readopt .post-meta-big a,
.dark_body .post-readopt .date_time_post,
.dark_body .post-readopt .wpsm_pretty_list,
.dark_body #rh_p_l_fullwidth_opt .title_single_area .post-meta,
.dark_body .full_gutenberg .post{color: #eee}
.dark_body .products .product, .dark_body .post-readopt .wpsm-titlebox{background-color: #fff}
.rh_post_layout_outside .title_single_area, .dark_body .widget.better_woocat{border-bottom: 1px solid #8c8c8c;margin-bottom: 0;}
.dark_body .main-side .rh-post-wrapper .title h1{color: #111}
.dark_body .sidebar .widget p, .dark_body .sidebar .widget .post-meta, .dark_body .sidebar .widget .post-meta a { color: #999}
.dark_body .sidebar .widget, .sidebar .widget.tabs, .sidebar .widget.better_menu{ padding: 0; background: none transparent; border:none;}
.dark_body .sidebar .widget, .dark_body .sidebar .widget.tabs, .dark_body .sidebar .widget.better_menu{box-shadow: none;}
.dark_body .sidebar .widget.top_offers, .dark_body .sidebar .widget.cegg_widget_products{ border: none; padding: 0 }
.dark_body .widget.widget_affegg_widget .egg_widget_grid.tabs-item figure{ background-color: #fff!important; padding: 10px !important}
.dark_body .sidebar .widget.widget_affegg_widget{ padding: 0 !important; border: none !important}
.dark_body .wrap_thing{background-color: #EFF3F6}
.dark_body .rh_grey_tabs_span span{color:#a5a5a5;}
.dark_body .hover_anons:after { background-image: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 0%, #EFF3F6 100%); background-image: -o-linear-gradient(top, rgba(255, 255, 255, 0) 0%, #EFF3F6 100%); background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, #EFF3F6 100%); background-repeat: repeat-x; }
.dark_body .repick_item.small_post, .dark_body .post-readopt #toplistmenu ul, .dark_body .post-readopt .priced_block .btn_offer_block, .dark_body .post-readopt .row_social_inpost span.share-link-image{box-shadow: none !important;}
.main-side .title h1{margin-top: 10px}
.dark_body .post-readopt #toplistmenu ul li, .dark_body .post-readopt .bigofferblock{background:#fff;}
.dark_body .widget.widget_affegg_widget .tabs-item figure{padding: 5px;background-color: #fff;}
.dark_body .sidebar .widget.widget-meta-data-filter, .dark_body .sidebar .widget.widget-meta-data-filter h4.data-filter-section-title{padding: 0 !important; border: none !important;background: none transparent}
.dark_body .widget.top_offers .tabs-item, .dark_body .widget.cegg_widget_products .tabs-item{border: 1px solid #7c7c7c;}
.dark_body .sidebar .tabs-item > div, .dark_body .footer-bottom .tabs-item > div, .dark_body .sidebar .tabs-item .lastcomm-item, .dark_body .sidebar .color_sidebar.border-lightgrey{border-color:#5f5f5f;}
.dark_body .main-side, .dark_body .vc_row.vc_rehub_container > .vc_col-sm-8, .no_bg_wrap.main-side, .dark_body .masonry_grid_fullwidth .small_post, .dark_body .repick_item.small_post, .dark_body .tabsajax .re_filter_panel {border:none; box-shadow: none;}
.dark_body .postNavigation .postnavprev { box-shadow: none;}
body.dark_body.noinnerpadding .rh-post-wrapper {background:#fff; border: 1px solid #e3e3e3;padding: 25px;box-shadow: 0 2px 2px #ECECEC;}
.dark_body .widget.tabsajax .re_filter_panel ul.re_filter_ul li span:not(.active){color:#111;}
.post-readopt .rate_bar_wrap{padding:0; box-shadow:none;}
.post-readopt .related_articles.border-top{border-color:#444;}
body.dark_body .rh-post-wrapper .alignwide.rh-color-heading, body.dark_body .rh-post-wrapper .alignfull.rh-color-heading{padding:0 30px;}
@media screen and (max-width: 1023px){
	body.dark_body #rh_woo_mbl_sidebar .sidebar{background:#000}
}

body.dark_body .products .button_action{background:transparent}
body.dark_body .products .col_item{background: #2B2B2F !important;border: none;box-shadow: none;}
<?php endif;?>

<?php $themesubset = rehub_option('theme_subset'); if($themesubset =='redeal') : ?>
	.litesearchstyle form.search-form [type="submit"]{height:40px; line-height:40px; padding: 0 16px}
	.litesearchstyle form.search-form input[type="text"]{padding-left:15px; height:40px}
	header .search{max-width:500px; width:100% !important}
	.header_six_style .head_search{min-width:300px}
	.logo_section_wrap .wpsm-button.medium{padding:12px 16px; font-size:16px}
<?php elseif($themesubset =='redirect') : ?>
	#main_header .rh-container{width: 100%; padding: 0 20px}
	@media (max-width: 500px){#main_header .rh-container{width: 100%; padding: 0 12px}}
<?php elseif($themesubset =='relearn') : ?>
	#main_header .rh-container{width: 100%; padding: 0 20px}
	.logo-section form.search-form{max-width: 800px; margin-left:auto}
	.logo-section form.search-form input[type=text], .logo-section .product-search-form .nice-select, .logo-section form.search-form [type=submit]{height:48px !important;}
	.logo-section form.search-form.product-search-form input[type=text]{padding:2px 20px;}
	.logo-section .product-search-form .nice-select, .logo-section form.search-form [type=submit]{line-height:48px !important; height:48px; background: #efefef !important; color: #111 !important;}
	@media (max-width: 500px){#main_header .rh-container{width: 100%; padding: 0 12px}}
	.tutor-dashboard{margin:35px auto}
	.tutor-single-course-meta.tutor-lead-meta, .tutor-mycourse-content .mycourse-footer .tutor-mycourses-stats, .tutor-course-loop-meta,.tutor-loop-author{font-size:90%}
	.tutor-instructor-pending-wrapper{margin: 35px auto;box-shadow: 0 0 20px #eee;padding: 40px;border-radius: 10px;}
	.tutor-container, .tutor-course-filter-wrapper{max-width:1340px !important}
	.tutor-course-filter-wrapper, .tutor-wrap{margin-top:35px !important; margin-bottom: 35px !important;}
	.tutor-course-filter-wrapper .tutor-wrap{margin-top:0 !important}
	.tutor-instructor-list .tutor-instructor-name{font-size: 21px; margin: 10px 0}
	.tutor-user-public-profile .photo-area .pp-area .profile-name>span{color:#c1c1c1}
	.tutor-row{align-items: flex-start;}
	.tutor-col-4{position:sticky; top:70px}
	.tutor-course-filter-wrapper>div:first-child h4{margin: 30px 0}
	.tutor-course-filter-wrapper>div:first-child label{font-size:15px}
<?php elseif($themesubset =='rething') : ?>
	.rething_item a.cat {font: 12px Arial;text-transform: uppercase;color: #666 !important;text-decoration: none !important;}
	.rething_item.small_post{ overflow: hidden; float: left;   padding: 0; text-align: center;}
	.rething_item.small_post .cat_link_meta:before{ display: none;}
	.rething_item.small_post .priced_block.block_btnblock .btn_offer_block, .rething_item.small_post .post_offer_anons{display: block; }
	.rething_item.small_post .priced_block.block_btnblock .btn_offer_block{padding: 13px 22px;}
	.rething_item.small_post .priced_block.block_btnblock .rh_button_wrapper{margin: 10px 0 0 0;}
	.small_post .re-line-badge.re-line-table-badge{left: 0}
	.small_post .re-line-badge.re-line-table-badge span::before{display: none;}
	.wrap_thing { padding: 20px 30px;}
	.hover_anons {position: relative;overflow: hidden;min-height: 220px;max-height: 220px;margin: 0px auto 5px;max-width: 900px;display: block;}
	.hover_anons:after {content: " ";display: block;position: absolute;border-bottom: 0;left: 0;bottom: 0px;width: 100%;height: 70px;
	background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, #ffffff 100%);
	background-repeat: repeat-x;}
	.thing-post-like{transition: all 0.4s ease 0s; background-color: rgba(255, 255, 255, 0.9); width: 60px; height: 55px; text-align: center; position: absolute; bottom: -55px; left: 50%; margin-left: -30px; z-index: 9}
	figure:hover .thing-post-like{bottom:0;}
	.thing-post-like .thumbscount{ color: #111; font-size: 14px; margin: 0 auto; display: block;}
	.thing-post-like .wishaddwrap, .thing-post-like .wishaddedwrap, .thing-post-like .wishremovedwrap{display: none;}
	.thing-post-like .thumbplus:before{color: red}
	.rething_item.small_post figure{ margin: 0; overflow: hidden;}
	.rething_item.small_post .priced_block { margin: 0}
	.rething_item.small_post h2{ letter-spacing: 1px; margin-bottom: 15px}
	.featured_mediad_wrap{ float: right; margin: 35px 0 15px 55px; width: 300px; height: 250px}
	.rething_button .btn_more{background-color: transparent;display: inline-block; padding: 10px 22px;font-size: 13px;line-height: 1.33333;text-transform: uppercase; position: relative;  text-decoration: none !important;}
<?php elseif($themesubset =='repick') : ?>
	.filter_home_pick .re_filter_panel{box-shadow:none;}
	.repick_item.small_post { padding: 0; overflow: visible;  }
	.repick_item figure { min-height: 300px; overflow: hidden; text-align: center; }
	.repick_item figure img {transition: opacity 0.5s ease;}
	.repick_item.centered_im_grid figure img { 
	height: auto !important; 
	position: relative;
	top: 50%; 
	transform: translateY(-50%);
	-ms-transform: translateY(-50%); 
	-webkit-transform: translateY(-50%); 
	-o-transform: translateY(-50%); }
	.repick_item.contain_im_grid figure img { height: auto !important; width: 100% !important; }
	.repick_item figure.pad_wrap { padding: 20px; }
	.repick_item figure.pad_wrap img { max-height: 100%; max-width: 100%;  }
	.masonry_grid_fullwidth.loaded{padding-top: 10px}
	.small_post .onsale, .small_post .onfeatured{display: none;}
	@media (min-width: 400px) {
	  figure.mediad_wrap_pad{ padding: 20px}
	}
	.repick_item figure.h_reduce img { transform: none; position: static; }
	.hover_anons { position: relative; overflow: hidden; min-height: 150px; max-height: 150px; margin: 0px auto 5px; max-width: 900px; display: block; }
	.hover_anons.meta_enabled{min-height: 210px; max-height: 210px}
	.hover_anons:after { content: " "; display: block; position: absolute; border-bottom: 0; left: 0; bottom: 0px; width: 100%; height: 70px; background-image: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 0%, #ffffff 100%); background-image: -o-linear-gradient(top, rgba(255, 255, 255, 0) 0%, #ffffff 100%); background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, #ffffff 100%); background-repeat: repeat-x; }
	.repick_item.small_post figure { margin: 0 !important }
	.repick_item.small_post .priced_block { margin: 0 }
	.repick_item a.cat{ font: 12px Arial; text-transform: uppercase; color: #111; text-decoration: none !important }
	.wrap_thing { padding: 20px 20px 50px 20px; position: relative; overflow: hidden;  }
	.repick_item .wrap_thing p { font-size: 15px; line-height: 21px; margin-bottom: 0 }
	.repick_item .priced_block .price_count { position: absolute; bottom: 0; left: 0; font-size: 14px; padding: 7px 14px; line-height: 14px; border-radius: 0 !important}
	.repick_item .priced_block .price_count del { display: none; }
	.repick_item .priced_block .btn_offer_block, .repick_item .btn_more, .repick_item .rehub_offer_coupon, .repick_item .priced_block .button { position: absolute; bottom: 0; right: 0; padding: 10px 18px !important; border-radius: 0 !important }
	.repick_item .rehub_offer_coupon.not_masked_coupon{display: none;}
	.repick_item .priced_block .btn_offer_block:hover { padding: 10px 20px }
	.repick_item .priced_block .btn_offer_block:active { top: auto; }
	.repick_item .price_count { background: #F9CC50; color: #111 }
	.repick_item .btn_more { border: none; }
	.repick_item .hotmeter_wrap { position: absolute; bottom: 0; left: 0; z-index: 9; padding: 18px; background-color: rgba(255, 255, 255, 0.82); }
	.repick_item .priced_block .btn_offer_block { font-size: 15px; }
	.repick_item .coupon_btn:before{display: none;}
	.repick_grid_meta{ margin: 15px 0; overflow: hidden; }
	.repick_grid_meta .admin_meta_grid{font: 12px/29px Arial; color: #aaa; float: left; margin-right: 15px}
	.repick_grid_meta .admin_meta_grid img{border-radius: 50%; margin-right: 8px; vertical-align: middle;}
	.repick_grid_meta .post_thumbs_comm{margin-right: 15px}
	.repick_grid_meta .admin_meta_grid a{color: #aaa}
	.repick_grid_meta .thumbscount{color:#67A827}
	.repick_grid_meta .thumbscount.cold_temp{color: #D10000;}
	.repick_item.centered_im_grid figure{height: 310px}
	.repick_item.centered_im_grid figure > a img{width: auto;}
	body .woocommerce .products.grid_woo .product{padding: 0}
	@media only screen and (min-width: 480px) and (max-width: 767px) {
		.repick_item figure{min-height: 250px}
		.repick_item.centered_im_grid figure{ height: 250px}
	}
<?php elseif($themesubset =='recash') : ?>
	.widget.tabs > ul{border: none;}
	.widget.better_menu .bordered_menu_widget, .sidebar .widget.tabs, .widget.outer_widget{border: none; padding: 0; background-color: transparent; box-shadow: none;}
	.postNavigation .postnavprev{ background-color: #868686}

	.showmefulln{position: absolute;bottom: 20px;left: 20px; margin-top: 15px; line-height: 12px; font-size: 12px; font-weight: normal !important; float: right;}
	.rtl .showmefulln{right:20px; left:auto}
	.showmefulln:after{ font-family: rhicons; content: "\f107"; margin: 0 3px; display: inline-block; }
	.showmefulln.compress:after{content: "\f106";}
	.newscom_content_ajax .post_carousel_block, .newscom_content_ajax .countdown_dashboard, .newscom_content_ajax .post_slider{display: none !important}
	.showmefulln.compress{position: static;}

	@media screen and (max-width: 767px){
		.showmefulln{position: static;}
		.carousel-style-3 .controls.prev { left: 10px;  }
		.carousel-style-3 .controls.next { right: 10px; }	
	}
	.widget.tabsajax .title:before{font-family: rhicons;content:"\e90d"; color:#fa9e19; margin-right:8px;}
	body .sidebar .wpsm_recent_posts_list .item-small-news, body .elementor-widget-sidebar .wpsm_recent_posts_list .item-small-news {border-bottom: 1px solid #E4E4E4;padding: 10px 0;background: radial-gradient(ellipse at top, rgba(255,255,255,0.75), rgba(255,255,255,0) 75%);}
<?php elseif ($themesubset == 'regame'):?>
.logo-section .search-form{border:1px solid #fff}
.logo-section .search form.search-form input[type="text"]{    background-color: transparent;border: none;color: #fff !important;}
.logo-section .search form.search-form input[type="text"]::placeholder{color: #f1f1f1;}
.logo-section form.search-form .nice-select{    border-width: 0 1px;background: transparent;}
.logo-section form.search-form .nice-select .current{color: #fff;}
.logo-section form.search-form [type="submit"] {position: static;background: transparent !important;}
.heart_thumb_wrap .heartplus:before, .heart_thumb_wrap:hover .heartplus.alreadywish:not(.wishlisted):before, header .rhi-hearttip:before{content:"\e90a"}
	.heart_thumb_wrap .heartplus.alreadywish:before{content:"\e9d2"}
<?php elseif($themesubset =='redigit') : ?>
	form.search-form input[type=text], .product-search-form .nice-select, form.search-form [type=submit]{height:45px !important;}
	form.search-form.product-search-form input[type=text]{padding:2px 20px;}
	.product-search-form .nice-select, form.search-form [type=submit]{line-height:43px !important;}
	.heart_thumb_wrap .heartplus:before, .heart_thumb_wrap:hover .heartplus.alreadywish:not(.wishlisted):before, header .rhi-hearttip:before{content:"\e90a"}
	.heart_thumb_wrap .heartplus.alreadywish:before{content:"\e9d2"}
<?php elseif($themesubset =='remart') : ?>
	form.search-form input[type="text"], .product-search-form .nice-select{border-color:white}
	form.search-form [type="submit"]{background:#F6F7F9 !important}
	form.search-form [type="submit"] i{color:#111 !important}
	.footer-bottom.white_style {border-top: 1px solid #f6f6f6;}
	.footer_widget .widget .title, .footer_widget .widget h2{font-size: 16px}
	.rehub_chimp_flat #mc_embed_signup input.email{border: 1px solid #f4f8fb;}
	.rehub_chimp_flat #mc_embed_signup input#mc-embedded-subscribe{background: none #f5f7fa;color: #000 !important;font-weight: normal;font-size: 14px;}
	.main-nav.white_style{border-bottom:none}

<?php endif;?>


</style>
<?php 
	$dynamic_css = ob_get_contents();
	ob_end_clean();
	echo '<link rel="preload" href="'.get_template_directory_uri().'/fonts/rhicons.woff2?3oibrk" as="font" type="font/woff2" crossorigin="crossorigin">';
	if (function_exists('rehub_quick_minify')) {
		echo rehub_quick_minify($dynamic_css);
	}
	else {echo ''.$dynamic_css;}
	if(rehub_option('rehub_custom_css')){
		echo '<style>'.rehub_option('rehub_custom_css').'</style>';
	}
	if(rehub_option('rehub_analytics_header')){
		echo rehub_option('rehub_analytics_header');
	}
?>