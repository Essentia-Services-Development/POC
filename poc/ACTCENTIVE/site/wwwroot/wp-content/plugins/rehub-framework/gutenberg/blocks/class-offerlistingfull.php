<?php

namespace Rehub\Gutenberg\Blocks;
defined('ABSPATH') OR exit;

class OfferListingFull{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

	public function init_handler(){
		register_block_type(__DIR__ . '/offerlistingfull', array(
			'attributes'      => $this->attributes,
			'render_callback' => array( $this, 'render_block' ),
		));
	}

	public $attributes = array(
		'titleTag'        => array(
			'type'    => 'string',
			'default' => 'h3',
		),
		'titleColor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'btnColor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'btntColor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'priceColor'      => array(
			'type'    => 'string',
			'default' => '#de1513',
		),
		'readmorecolor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'highlightcolor'      => array(
			'type'    => 'string',
			'default' => '#334dfe',
		),
		'numbercolor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'numberbgcolor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'expandcolor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'expandbg'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'disclaimercolor'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'disclaimerbg'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'expandlabel'      => array(
			'type'    => 'string',
			'default' => 'More info +',
		),
		'enableschema'      => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'tabletmobile'      => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'enableexpand'      => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'enableScore'      => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'enableNumber'      => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'enableScoreIcon'      => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'colorScore'      => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'pricecontent'      => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'reviewcolor'      => array(
			'type'    => 'string',
			'default' => '#334dfe',
		),
		'offers' => array(
			'type'    => 'array',
			'default' => array(
				array(
					'score'          => '',
					'scoretext'          => '',
					'enableBadge'    => true,
					'highlight'    => false,
					'thumbnail'      => array(
						'url'    => '',
						'width'  => '',
						'height' => '',
						'alt'    => '',
					),
					'title'          => '',
					'copy'           => '',
					'expandcontent'  => '',
					'customBadge'    => array(
						'text'            => '',
						'textColor'       => '#fff',
						'backgroundColor' => '#334dfe'
					),
					'currentPrice'   => '',
					'oldPrice'       => '',
					'button'         => array(
						'text' => 'Buy Now',
						'url'  => '',
						'noFollow' => true,
						'newTab' => true,
					),
					'couponCode'         => '',
					'expirationDate' => '',
					'offerExpired'   => false,
					'moretext' => '',
					'disclaimer'     => ''
				)
			),
		),
	);

	public function render_block($settings = array(), $inner_content = ''){
		extract($settings);

		$html   = '';
		if ( empty( $offers ) || count( $offers ) === 0 ) {
			return;
		}
		$alignclass = (!empty($settings['align'])) ? ' align'.esc_attr($settings['align']).' ' : '';
		$tabletclass = (!empty($settings['tabletmobile'])) ? ' tabletmobilestyle' : '';
		$schemarender = ($enableschema) ? ' itemtype="http://schema.org/ItemList" itemscope' : '';
		$schemaoffer = ($enableschema) ? ' itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"' : '';
		$schemaurl = ($enableschema) ? ' itemprop="url"' : '';
		$btnstyles = ($btnColor || $btntColor) ? 'style="background-color:' . $btnColor . '; color:' . $btntColor . ';"' : '';
		$titlestyles = ($titleColor) ? 'style="color:' . $titleColor . ';"' : '';
		$pricestyles = ($priceColor) ? 'style="color:' . $priceColor . ';"' : '';
		$scoreicon = ($enableScoreIcon) ? '<svg height="512" viewBox="0 0 512 512" width="512" xmlns="https://www.w3.org/2000/svg"><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="256" x2="256" y1="512" y2="0"><stop offset="0" stop-color="#fd5900"/><stop offset="1" stop-color="#ffde00"/></linearGradient><linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="256" x2="256" y1="392.353" y2="91"><stop offset="0" stop-color="#ffe59a"/><stop offset="1" stop-color="#ffffd5"/></linearGradient><g id="Star"><g><g><circle cx="256" cy="256" fill="url(#SVGID_1_)" r="256"/></g></g><g><g><path d="m412.924 205.012c-1.765-5.43-6.458-9.388-12.108-10.209l-90.771-13.19-40.594-82.252c-2.527-5.12-7.742-8.361-13.451-8.361s-10.924 3.241-13.451 8.362l-40.594 82.252-90.771 13.19c-5.65.821-10.345 4.779-12.109 10.209s-.292 11.391 3.796 15.376l65.683 64.024-15.506 90.404c-.965 5.627 1.348 11.315 5.967 14.671 4.62 3.356 10.743 3.799 15.797 1.142l81.188-42.683 81.188 42.683c5.092 2.676 11.212 2.189 15.797-1.142 4.619-3.356 6.933-9.043 5.968-14.671l-15.506-90.404 65.682-64.024c4.088-3.986 5.559-9.947 3.795-15.377z" fill="url(#SVGID_2_)"/></g></g></g></svg>' : '';

		$html .= '<div class="gc-offer-listing'.$alignclass.$tabletclass.'"'.$schemarender.'>';

		foreach ( $offers as $index=>$offer ) {
			$score             = !empty($offer['score']) ? $offer['score'] : '';
			$offer_url         = !empty($offer['button']['url']) ? $offer['button']['url'] : '';
			$offer_url 		   = apply_filters('rh_post_offer_url_filter', $offer_url );
			$imageid             = !empty($offer['thumbnail']['id']) ? $offer['thumbnail']['id'] : '';
			$image_url         = !empty($offer['thumbnail']['url']) ? $offer['thumbnail']['url'] : '';
			$image_alt         = !empty($offer['thumbnail']['alt']) ? $offer['thumbnail']['alt'] : '';
			$image_width         = !empty($offer['thumbnail']['width']) ? $offer['thumbnail']['width'] : '';
			$image_height         = !empty($offer['thumbnail']['height']) ? $offer['thumbnail']['height'] : '';
			$title             = !empty($offer['title']) ? $offer['title'] : '';
			$copy              = !empty($offer['copy']) ? $offer['copy'] : '';
			$current_price     = !empty($offer['currentPrice']) ? $offer['currentPrice'] : '';
			$old_price         = !empty($offer['oldPrice']) ? $offer['oldPrice'] : '';
			$button_text       = !empty($offer['button']['text']) ? $offer['button']['text'] : esc_html__( 'Buy Now', 'rehub-framework' );
			$moretext      	   = !empty($offer['moretext']) ? $offer['moretext'] : '';
			$disclaimer        = !empty($offer['disclaimer']) ? $offer['disclaimer'] : '';
			$expandcontent        = !empty($offer['expandcontent']) ? $offer['expandcontent'] : '';
			$enable_badge      = !empty($offer['enableBadge']) ? $offer['enableBadge'] : '';
			$badge             = !empty($offer['customBadge']) ? $offer['customBadge'] : '';
			$badgebg	       = !empty($badge['backgroundColor']) ? $badge['backgroundColor'] : '';
			$badgetxcolor	   = !empty($badge['textColor']) ? $badge['textColor'] : '';
			$badge_styles      = 'background-color:' . $badgebg . '; color:' . $badgetxcolor . ';';
			$coupon_code      = !empty($offer['couponCode']) ? $offer['couponCode'] : '';
			$offer_coupon_date = !empty($offer['expirationDate']) ? $offer['expirationDate'] : '';
			$coupon_style      = '';
			$scoretext = !empty($offer['scoretext']) ? $offer['scoretext'] : __('EXCELLENT', 'rehub-framework');
			$button_nofollow       = !empty($offer['button']['noFollow']) ? ' rel="nofollow sponsored"' : '';
			$button_target       = !empty($offer['button']['newTab']) ? ' target="_blank""' : '';

			if ( empty( $image_url ) ) {
				$image_url = plugin_dir_url( __DIR__ ) . '/assets/icons/noimage-placeholder.png';
			}

			$coupon_text = '';

			if ( ! empty( $offer_coupon_date ) ) {
				$timestamp1 = strtotime($offer_coupon_date);
				if(strpos($offer_coupon_date, ':') ===false){
					$timestamp1 += 86399;
				}
				$seconds    = $timestamp1 - (int) current_time( 'timestamp', 0 );
				$days       = floor( $seconds / 86400 );
				$seconds    %= 86400;

				if ( $days > 0 ) {
					$coupon_text = $days.' '.esc_html__('days left', 'rehub-framework');
					$coupon_style = '';
					$expired      = 'no';
				} elseif ( $days == 0 ) {
					$coupon_text = esc_html__('Last day', 'rehub-framework');
					$coupon_style = '';
					$expired      = 'no';
				} else {
					$coupon_text  = esc_html__( 'Expired', 'rehub-framework' );
					$coupon_style = ' expired_coupon';
					$expired      = '1';
				}
			}
			$highlightstyle = (!empty($offer['highlight'])) ? ' style="box-shadow: inset 0 0 0 3px '.esc_attr($highlightcolor).';"' : '';

			$html .= '<div class="gc-expandable-wrapper gc-offer-listing-item"'.$schemaoffer.'>';
				$position = $index+1;
				if($enableschema){
					$html .= '<meta itemprop="position" content="'.$position.'" />';
				}
				$html .= '<div class="gc-offer-listing-item__wrapper'.$coupon_style.'"'.$highlightstyle.'>';
					$html .= '<div class="gc-offer-listing-image">';
						if ( $enable_badge && !empty($badge['text']) ) {
							$html .= '<span class="gc-list-badge" style="' . esc_attr( $badge_styles ) . '">';
							$html .= '	<span class="gc-list-badge-title">';
							$html .= '      <span>' . esc_html( $badge['text'] ) . '</span>';
							$html .= '	</span>';
							$html .= '  <span class="gc-list-badge-arrow" style="border-top-color:'.esc_attr($badgebg).'"></span>';
							$html .= '</span>';
						}
						if ( $enableNumber) {
							$html .= '<div class="gc-offer-listing-number" style="background-color:'.esc_attr($numberbgcolor).';color: '.$numbercolor.';">'.$position.'</div>';
						}
						$html .= '<figure>';
							if($imageid && class_exists('WPSM_image_resizer')){
								$html .= \WPSM_image_resizer::show_wp_image('smallgrid', $imageid, array('nofeatured'=>true));
							}else{
								$html .= '<img class="lazyload" data-skip-lazy="" data-src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_alt ) . '" src="'.get_template_directory_uri() . '/images/default/blank.gif"';
								$html .= 'width="' . esc_attr( $image_width ) . '" height="' . esc_attr( $image_height ) . '"/>';
							}
						$html .= '</figure>';
					$html .= '</div>';
					$html .= '<div class="gc-offer-listing-contwrap">';
						$html .= '<div class="gc-offer-listing-content">';
							$html .= '<'.$titleTag . ' class="gc-offer-listing__title">';
								$html .= '<a href="' . esc_url( $offer_url ) . '" class="re_track_btn" '.$button_target.$button_nofollow.$titlestyles.'>';
									$html .= '' . wp_kses_post( $title ) . '';
								$html .= '</a>';
							$html .= '</' . $titleTag . '>';
							if ( $current_price && $pricecontent ) {
								$html .= '<div class="gc-offer-listing-price" style="margin-bottom:15px">';
									$html .= '<span class="rh_regular_price" '.$pricestyles.'>' . esc_html( trim( $current_price ) ) . '</span>';
									if ( $old_price && $old_price !== $current_price ) {
										$html .= '<del class="">' . esc_html( trim( $old_price ) ) . '</del>';
									}
								$html .= '	</div>';
							}
							$html .= '<div class="gc-offer-listing__copy">' .trim( $copy ). '</div>';
							if($enableexpand && $expandcontent ){
								$html .='<span class="gc-listing-expand-label gc-expandable-trigger">'.esc_attr($expandlabel).'</span>';
							}
						$html .= '</div>';
						if ( $enableScore ) {
							$html .= '<div class="gc-offer-listing-score">';
								if(!$colorScore){
									$html .= '<div class="gc-lrating">';
										$html .= '<div class="gc-lrating-body">'.$scoreicon.'<span>'.esc_attr($offer['score']).'</span></div>';
										$html .= '<div class="gc-lrating-bottom"><span>'.esc_attr($scoretext).'</span></div>';
									$html .= '</div>';
								}
								if($colorScore){
									$html .= '<div class="gc-colorrating" style="background-color: '.esc_attr($reviewcolor).'">';
										$html .= ''.$scoreicon.'<span>'.esc_attr($offer['score']).'</span>';
									$html .= '</div>';
								}
							$html .= '</div>';
						}
					$html .= '</div>';
					$html .= '<div class="gc-offer-listing-cta">';
						if ( $current_price && !$pricecontent ) {
							$html .= '<div class="gc-offer-listing-price">';
								$html .= '<span class="rh_regular_price" '.$pricestyles.'>' . esc_html( trim( $current_price ) ) . '</span>';
								if ( $old_price && $old_price !== $current_price ) {
									$html .= '<del class="">' . esc_html( trim( $old_price ) ) . '</del>';
								}
							$html .= '	</div>';
						}
						if ( $offer_url ) {
							$html .= '<div class="priced_block priced_block--sm">';
								$html .= '<a href="' . esc_url( $offer_url ) . '" class="btn_offer_block gc_track_btn" '.$button_target.$button_nofollow.$btnstyles.$schemaurl.'>';
									$html .= $button_text;
								$html .= '</a>';
							$html .= '</div>';
						}
						if ($coupon_code){
							wp_enqueue_script('zeroclipboard');
							$html .= '<div class="rehub_offer_coupon not_masked_coupon';
							if ( ! empty( $offer_coupon_date ) ) {
								$html .= $coupon_style;
							}
							$html .= '" data-clipboard-text="'.esc_html($coupon_code).'">';
							$html .= '<span class="coupon_text">'. esc_html($coupon_code) .'</span>';
							$html .= '<i class="rhicon rhi-cut fa-rotate-180"></i>';
							$html .= '</div>';
							$html .= '<div class="time_offer">'.$coupon_text.'</div>';
						}
						if ( $moretext ) {
							$html .= '<span class="gc-offer-listing__read-more">';
								$html .= wp_kses_post( $moretext );
							$html .= '</span>';
						}

					$html .= '</div>';
				$html .= '</div>';
				if ( $disclaimer ) {
					$html .= '<div class="gc-offer-listing-disclaimer" style="background-color:'.$disclaimerbg.';color:'.$disclaimercolor.';">';
					$html .= wp_kses_post( $disclaimer );
					$html .= '</div>';
				}
				if ( $enableexpand && $expandcontent  ) {
					$html .= '<div class="gc-listing-expand gc-expandable-content" style="background-color:'.$expandbg.';color:'.$expandcolor.';display:none">';
					$html .= wp_kses_post( $expandcontent  );
					$html .= '</div>';
				}	
			$html .= ' </div>';
		}
		$html .= '</div>';

		return $html;

	}
}