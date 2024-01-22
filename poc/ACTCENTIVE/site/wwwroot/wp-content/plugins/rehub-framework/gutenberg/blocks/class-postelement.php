<?php


namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') or exit;


class Postelement extends Basic
{

	protected $name = 'postelement';

	protected $attributes = array(
		'align'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'blockId'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'color'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'type' => array(
			'type'    => 'string',
			'default' => 'favorite',
		),
		'labeltext' => array(
			'type'    => 'string',
			'default' => '',
		),
		'urltext' => array(
			'type'    => 'string',
			'default' => '',
		),
		'loading' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'woobtn' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'fontSize'      => array(
			'type'    => 'number',
		),
		'labelfontSize'      => array(
			'type'    => 'number',
		),
		'pSide'      => array(
			'type'    => 'number',
			'default' => 12
		),
		'pTop'      => array(
			'type'    => 'number',
			'default' => 8
		),
		'bradius'      => array(
			'type'    => 'number',
		),
		'imageheight'      => array(
			'type'    => 'number',
			'default' => 20
		),
		'avatarblock' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'nomenuborder' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'convertmenumobile' => array(
			'type'    => 'boolean',
			'default' => false,
		),
	);

	protected function render($settings = array(), $inner_content = '')
	{
		extract($settings);
		global $post;
		if(!is_object($post)){
			$postId = 0;
		}else{
			$postId = $post->ID;
		}
		$id = 'rh-postel-' . mt_rand();
		$alignflex = '';
		if ($align === 'left') {
			$alignflex = 'start';
		} else if ($align === 'right') {
			$alignflex = 'end';
		} else if ($align === 'center') {
			$alignflex = 'center';
		}
		$out = $class = $textalign = '';
		$class = 'rh-flex-center-align rh-flex-justify-' . $alignflex;
		$out .= '<div id="' . $id . '" class="' . $class . '"' . $textalign . '>
		<style scoped>
			#' . $id . ' .heart_thumb_wrap .heartplus{
				' . ((isset($fontSize) && $type == 'favorite') ? "font-size:" . $fontSize . "px;" : "") . '
			}
			#' . $id . ' .price_count{
				' . ((isset($fontSize) && $type == 'offerprice') ? "font-size:" . $fontSize . "px;" : "") . '
			}
			#' . $id . ' a.admin{
				' . ((isset($fontSize) && $type == 'author') ? "font-size:" . $fontSize . "px;" : "") . '
				' . (($color  && $type == 'author') ? "color:" . $color . ";" : "") . '
				' . (($type == 'author') ? "text-decoration:none;" : "") . '
				' . (($avatarblock && $type == 'author') ? "display:block;" : "") . '
			}
			#' . $id . ' .admin-name{
				' . (($avatarblock && $type == 'author') ? "display:block;" : "") . '
			}
			#' . $id . ' a.admin img{
				' . (($avatarblock && $type == 'author') ? "margin:0 !important;" : "") . '
			}
			#' . $id . ' nav.top_menu > ul > li{
				' . (($nomenuborder && $type == 'menu') ? "border:none;" : "") . '
			}
			#' . $id . ' .admin_meta_el{
				' . (($avatarblock && $type == 'author') ? "text-align:center;" : "") . '
				' . (($type == 'author') ? "text-decoration:none;" : "") . '
			}
			#' . $id . ' .priced_block .btn_offer_block{
				' . ((isset($fontSize) && ($type == 'offerbutton' || $type == 'bpbutton')) ? "font-size:" . (int)$fontSize . "px;" : "") . '
				' . ((isset($pTop) && ($type == 'offerbutton' || $type == 'bpbutton')) ? "padding-top:" . (int)$pTop . "px;padding-bottom:" . (int)$pTop . "px;" : "") . '
				' . ((isset($pSide) && ($type == 'offerbutton' || $type == 'bpbutton')) ? "padding-left:" . (int)$pSide . "px;padding-right:" . (int)$pSide . "px;" : "") . '
				' . ((isset($bradius) && ($type == 'offerbutton' || $type == 'bpbutton')) ? "border-radius:" . (int)$bradius . "px !important;" : "") . '
			}
			#' . $id . ' .wpsm-button.medium{
				' . ((isset($fontSize) && ($type == 'loginbutton')) ? "font-size:" . (int)$fontSize . "px;" : "") . '
				' . ((isset($pTop) && ($type == 'loginbutton')) ? "padding-top:" . (int)$pTop . "px;padding-bottom:" . (int)$pTop . "px;" : "") . '
				' . ((isset($pSide) && ($type == 'loginbutton')) ? "padding-left:" . (int)$pSide . "px;padding-right:" . (int)$pSide . "px;" : "") . '
				' . ((isset($bradius) && ($type == 'loginbutton')) ? "border-radius:" . (int)$bradius . "px !important;" : "") . '
			}
			#' . $id . ' .menu-cart-btn{
				' . ((isset($pTop) && ($type == 'cart')) ? "padding-top:" . (int)$pTop . "px;padding-bottom:" . (int)$pTop . "px;" : "") . '
				' . ((isset($pSide) && ($type == 'cart')) ? "padding-left:" . (int)$pSide . "px;padding-right:" . (int)$pSide . "px;" : "") . '
				' . ((isset($bradius) && ($type == 'cart')) ? "border-radius:" . (int)$bradius . "px !important;" : "") . '
			}
			#' . $id . ' .price_count .rh_regular_price{
				' . (($color  && $type == 'offerprice') ? "color:" . esc_attr($color) . ";" : "") . '
			}
			#' . $id . ' .price_count del{
				' . ((isset($fontSize) && $type == 'offerprice') ? "font-size:" . (int)$fontSize * 0.8 . "px;" : "") . '
				' . (($type == 'offerprice') ? "opacity:0.2;" : "") . '
			}
			#' . $id . ' .rh-header-icon{
				' . ((isset($fontSize) && ($type == 'cart' || $type == 'loginicon' || $type == 'wishlistpageicon' || $type === 'comparisonpageicon' || $type === 'searchicon')) ? "font-size:" . (int)$fontSize . "px;" : "") . '
				' . ((isset($color) && ($type == 'loginicon' || $type == 'wishlistpageicon' || $type === 'comparisonpageicon' || $type === 'searchicon')) ? "color:" . esc_attr($color) . ";" : "") . '
			}
			#' . $id . ' nav.top_menu > ul > li > a{
				' . ((isset($fontSize) && ($type === 'menu')) ? "font-size:" . (int)$fontSize . "px;" : "") . '
				' . ((isset($color) && ($type == 'menu')) ? "color:" . esc_attr($color) . ";" : "") . '
				' . ((isset($pTop) && $type == 'menu') ? "padding-top:" . (int)$pTop . "px;padding-bottom:" . (int)$pTop . "px;" : "") . '
				' . ((isset($pSide) && $type == 'menu') ? "padding-left:" . (int)$pSide . "px;padding-right:" . (int)$pSide . "px;" : "") . '
			}
			#' . $id . ' .dl-menuwrapper button svg line{
				' . ((isset($color) && ($type == 'mobilemenu' || $type == 'menu')) ? "stroke:" . esc_attr($color) . ";" : "") . '
			}
			#' . $id . ' .dl-menuwrapper button{
				' . ((isset($fontSize) && ($type === 'mobilemenu')) ? "width:" . (int)$fontSize . "px;" : "") . '
				' . ((isset($fontSize) && ($type === 'mobilemenu')) ? "height:" . (int)($fontSize + 5) . "px;" : "") . '
			}
			#' . $id . ' nav.top_menu ul.sub-menu > li > a{
				' . ((isset($labelfontSize) && ($type === 'menu')) ? "font-size:" . (int)$labelfontSize . "px;" : "") . '
				' . ((isset($labelfontSize) && $type == 'menu') ? "padding-top:" . (int)($labelfontSize - 7) . "px;padding-bottom:" . (int)($labelfontSize - 5) . "px;" : "") . '
				' . ((isset($labelfontSize) && $type == 'menu') ? "padding-left:" . (int)($labelfontSize + 10) . "px;padding-right:" . (int)($labelfontSize + 10) . "px;" : "") . '
			}
			#' . $id . ' .heads_icon_label{
				' . ((isset($labelfontSize) && ($type == 'loginicon' || $type == 'wishlistpageicon' || $type === 'comparisonpageicon')) ? "font-size:" . (int)$labelfontSize . "px;" : "") . '
				' . ((isset($color) && ($type == 'loginicon' || $type == 'wishlistpageicon' || $type === 'comparisonpageicon')) ? "color:" . esc_attr($color) . ";" : "") . '
			}
			#' . $id . ' .rh_woocartmenu-amount{
				' . ((isset($fontSize) && $type == 'cart') ? "font-size:" . (int)$fontSize . "px;" : "") . '
			}
			#' . $id . ' .row_social_inpost span.share-link-image{
				' . ((isset($bradius) && $type == 'share') ? "border-radius:" . (int)$bradius . "px;" : "") . '
			}
			#' . $id . ' .favour_btn_red .heart_thumb_wrap{
				' . ((isset($pTop) && $type == 'favorite') ? "padding-top:" . (int)$pTop . "px;padding-bottom:" . (int)$pTop . "px;" : "") . '
				' . ((isset($pSide) && $type == 'favorite') ? "padding-left:" . (int)$pSide . "px;padding-right:" . (int)$pSide . "px;" : "") . '
				' . ((isset($bradius) && $type == 'favorite') ? "border-radius:" . (int)$bradius . "px;" : "") . '
			}
			#' . $id . ' .favour_in_row{
				' . (($type == 'favorite') ? "margin-right:0px !important;" : "") . '
			}
		</style>';
		if ($type == 'favorite') {
			$wishlistadd = esc_html__('Save', 'rehub-theme');
			$wishlistadded = esc_html__('Saved', 'rehub-theme');
			$wishlistremoved = esc_html__('Removed', 'rehub-theme');
			$out .= '<div class="favour_in_row favour_btn_red">' . RH_get_wishlist($postId, $wishlistadd, $wishlistadded, $wishlistremoved) . '</div>';
		} else if ($type == 'share') {
			$out .= rehub_social_share("row");
		} else if ($type == 'sharesquare') {
			$out .= rehub_social_share("square");
		} else if ($type == 'thumb') {
			$out .= getHotThumb($postId, false, true);
		} else if ($type == 'thumbsmall') {
			$out .= getHotThumb($postId, false);
		} else if ($type == 'wishlisticon') {
			$out .= RHF_get_wishlist($postId);
		} else if ($type == 'hot') {
			$out .= RHgetHotLike($postId);
		} else if ($type == 'searchicon') {
			$out .= '<div class="celldisplay rh-search-icon rh-header-icon text-center"><span class="icon-search-onclick cursorpointer"></span></div>';
		} else if ($type == 'searchform') {
			$out .= '<div class="search head_search position-relative">';
			$posttypes = rehub_option('rehub_search_ptypes');
			if (class_exists('Woocommerce') && empty($posttypes)) {
				$out .= get_product_search_form(false);
			} else {
				$out .= get_search_form(false);
			}
			$out .= '</div>';
		} else if ($type == 'author') {
			$author_id = get_post_field('post_author', $postId);
			$name = get_the_author_meta('display_name', $author_id);
			$out .= '<span class="admin_meta_el"><a class="admin rh-flex-center-align" href="' . get_author_posts_url($author_id) . '">' . get_avatar($author_id, $imageheight, '', $name, array('class' => 'mr10 roundborder50p')) . '<span class="admin-name">' . $name . '</span></a></span>';
		} else if ($type == 'bpbutton') {
			$author_id = get_post_field('post_author', $postId);
			if (class_exists('BuddyPress') &&  bp_is_active('messages')) {
				$class_show = 'btn_offer_block';
				$link = (is_user_logged_in()) ? wp_nonce_url(bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username($author_id) . '&ref=' . urlencode(get_permalink($postId))) : '#';
				$class_show = (!is_user_logged_in() && rehub_option('userlogin_enable') == '1') ? $class_show . ' act-rehub-login-popup' : $class_show;
				$out .= '<div class="priced_block clearfix  fontbold mb0 lineheight25"><a href="' . $link . '" class="' . $class_show . '">' . $labeltext . '</a></div>';
			} else {
				$out .= __('Please, enable message addon in Buddypress', 'rehub-framework');
			}
		} else if ($type == 'mobilemenu') {
			$out .= '<div class="rh_mobile_menu"><div id="dl-menu" class="dl-menuwrapper rh-flex-center-align">';
			$out .= '<button id="dl-trigger" class="dl-trigger" aria-label="Menu">
				<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
					<g>
						<line stroke-linecap="round" id="rhlinemenu_1" y2="7" x2="29" y1="7" x1="3"/>
						<line stroke-linecap="round" id="rhlinemenu_2" y2="16" x2="18" y1="16" x1="3"/>
						<line stroke-linecap="round" id="rhlinemenu_3" y2="25" x2="26" y1="25" x1="3"/>
					</g>
				</svg>
			</button>
			';
			$out .= '</div>' . do_action('rh_mobile_menu_panel') . '</div>';
			$mobilesliding = rh_check_empty_index($settings, 'mobilesliding');
			if ($mobilesliding) $out .= '<div id="rhmobpnlcustom" class="rhhidden">' . rehub_kses(do_shortcode($mobilesliding)) . '</div>';
		} else if ($type == 'offerprice') {
			ob_start();
			rehub_generate_offerbtn('showme=price&wrapperclass=fontbold mb0 lineheight25&postId=' . $postId . '');
			$out .= ob_get_contents();
			ob_end_clean();
		} else if ($type == 'loginicon') {
			$value = '<div class="celldisplay login-btn-cell text-center">';
			$loginurl = (!empty($urltext)) ? esc_url($urltext) : '';
			$classmenu = 'rh-header-icon rh_login_icon_n_btn ';
			$value .= wpsm_user_modal_shortcode(array('class' => $classmenu, 'loginurl' => $loginurl, 'icon' => 'rhicon rhi-user font95'));
			$value .= '<span class="heads_icon_label rehub-main-font login_icon_label">';
			$loginlabel = !empty($labeltext) ? $labeltext : '';
			$value .= esc_html($loginlabel);
			$value .= '</span>';
			$value .= '</div>';
			$out .= $value;
		} else if ($type == 'wishlistpageicon') {
			if ($urltext) {
				$value = '<div class="celldisplay text-center">';
				$likedposts = '';
				if (is_user_logged_in()) { // user is logged in
					global $current_user;
					$user_id = $current_user->ID; // current user
					$likedposts = get_user_meta($user_id, "_wished_posts", true);
				} else {
					$ip = rehub_get_ip(); // user IP address
					$likedposts = get_transient('re_guest_wishes_' . $ip);
				}
				$wishnotice = (!empty($likedposts)) ? '<span class="rh-icon-notice rehub-main-color-bg">' . count($likedposts) . '</span>' : '<span class="rh-icon-notice rhhidden rehub-main-color-bg"></span>';
				$value .= '<a href="' . esc_url($urltext) . '" class="rh-header-icon rh-wishlistmenu-link blockstyle"><span class="rhicon rhi-hearttip position-relative">' . $wishnotice . '</span></a>';
				$value .= '<span class="heads_icon_label rehub-main-font">';
				$value .= esc_html($labeltext);
				$value .= '</span>';
				$value .= '</div>';
			} else {
				$value = esc_html__('Add url for wishlist page', 'rehub-framework');
			}
			$out .= $value;
		} else if ($type == 'comparisonpageicon') {
			if (rh_compare_icon(array())) {
				$value = '<div class="celldisplay rh-comparemenu-link rh-header-icon text-center">';
				$value .= rh_compare_icon(array());
				$value .= '<span class="heads_icon_label rehub-main-font">';
				$value .= esc_html($labeltext);
				$value .= '</span>';
				$value .= '</div>';
			} else {
				$value = sprintf('%s in <span class="fontitalic">%s</span>', esc_html__('Select page for comparison', 'rehub-framework'), esc_html__('Theme Options - Dynamic comparison', 'rehub-framework'));
			}
			$out .= $value;
		} else if ($type == 'loginbutton') {
			$rtlclass = (is_rtl()) ? 'mr10' : 'ml10';
			$loginurl = (!empty($urltext)) ? esc_url($urltext) : '';
			$out .= wpsm_user_modal_shortcode(array('as_btn' => 1, 'class' => $rtlclass, 'loginurl' => $loginurl));;
		} else if ($type == 'menu') {
			$out .= '<div class="header_icons_menu">';
			$out .= wp_nav_menu(array('container_class' => 'top_menu', 'container' => 'nav', 'theme_location' => 'primary-menu', 'fallback_cb' => 'add_menu_for_blank', 'walker' => new \Rehub_Walker, 'echo' => false));
			$out .= '</div>';
			if($convertmenumobile){
				$out .= '<div class="rh_mobile_menu desktopdisplaynone"><div id="dl-menu" class="dl-menuwrapper rh-flex-center-align">';
				$out .= '<button id="dl-trigger" class="dl-trigger" aria-label="Menu">
					<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
						<g>
							<line stroke-linecap="round" id="rhlinemenu_1" y2="7" x2="29" y1="7" x1="3"/>
							<line stroke-linecap="round" id="rhlinemenu_2" y2="16" x2="18" y1="16" x1="3"/>
							<line stroke-linecap="round" id="rhlinemenu_3" y2="25" x2="26" y1="25" x1="3"/>
						</g>
					</svg>
				</button>
				';
				$out .= '</div>' . do_action('rh_mobile_menu_panel') . '</div>';
				$mobilesliding = rh_check_empty_index($settings, 'mobilesliding');
				if ($mobilesliding) $out .= '<div id="rhmobpnlcustom" class="rhhidden">' . rehub_kses(do_shortcode($mobilesliding)) . '</div>';
			}
		} else if ($type == 'cart') {
			ob_start();
			if (class_exists('Woocommerce')) {
				global $woocommerce;
				if ($woocommerce) {
					if ($woocommerce->cart) {
						$cartbtn = $woobtn ? 'pt10 pb10 pr15 pl15 rehub-main-btn-bg rehub-main-smooth menu-cart-btn ' : '';
						echo '<div class="celldisplay rh_woocartmenu_cell text-center"><span class="inlinestyle ' . $cartbtn . '"><a class="rh-header-icon rh-flex-center-align rh_woocartmenu-link cart-contents cart_count_' . $woocommerce->cart->cart_contents_count . '" href="' . wc_get_cart_url() . '"><span class="rh_woocartmenu-icon"><span class="rh-icon-notice rehub-main-color-bg">' . $woocommerce->cart->cart_contents_count . '</span></span><span class="rh_woocartmenu-amount">' . $woocommerce->cart->get_total() . '</span></a></span><div class="woocommerce widget_shopping_cart"></div></div>';
					}
				}
			} else {
				esc_html_e('WooCommerce plugin is not active', 'rehub-theme');
			}
			$out .= ob_get_contents();
			ob_end_clean();
		} else if ($type == 'authorbox') {
			ob_start();
			rh_author_detail_box($postId);
			$out .= ob_get_contents();
			ob_end_clean();
		} else if ($type == 'postgallery') {
			$out .= rh_get_post_thumbnails(array('video' => 1, 'columns' => 5, 'height' => $imageheight, 'post_id' => $postId));
		} else if ($type == 'offerbutton') {
			ob_start();
			rehub_generate_offerbtn('showme=button&wrapperclass=fontbold mb0 lineheight25&updateclean=1&postId=' . $postId . '');
			$out .= ob_get_contents();
			ob_end_clean();
		}
		$out .= '</div>';

		return $out;
	}
}
