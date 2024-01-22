<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) or exit;

class OfferListing extends Basic {
	protected $name = 'offer-listing';

	protected $attributes = array(
		'titleTag'        => array(
			'type'    => 'string',
			'default' => 'h3',
		),
		'offers' => array(
			'type'    => 'object',
			'default' => array(
				array(
					'score'          => 10,
					'enableBadge'    => true,
					'enableScore'    => true,
					'thumbnail'      => array(
						'url'    => '',
						'width'  => '',
						'height' => '',
						'alt'    => '',
					),
					'title'          => 'Post name',
					'copy'           => 'Content',
					'customBadge'    => array(
						'text'            => 'Best Values',
						'textColor'       => '#fff',
						'backgroundColor' => '#77B21D'
					),
					'currentPrice'   => '',
					'oldPrice'       => '',
					'button'         => array(
						'text' => 'Buy this item',
						'url'  => '',
						'noFollow' => true,
						'newTab' => true,
					),
					'coupon'         => '',
					'maskCoupon'     => false,
					'maskCouponText' => '',
					'expirationDate' => '',
					'offerExpired'   => false,
					'readMore'       => 'Read full review',
					'readMoreUrl'    => '',
					'readMoreExt'    => false,
					'disclaimer'     => 'Disclaimer text...'
				)
			),
		),
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		$html   = '';
		$offers = $settings['offers'];

		$title_tag        = $settings['titleTag'];

		if ( empty( $offers ) || count( $offers ) === 0 ) {
			return;
		}

		$html .= '<div class="rh_list_builder rh-shadow4 disablemobileshadow mb30">';

		foreach ( $offers as $offer ) {
			$score             = !empty($offer['score']) ? $offer['score'] : '';
			$offer_url         = !empty($offer['button']['url']) ? $offer['button']['url'] : '';
			$offer_url 		   = apply_filters('rh_post_offer_url_filter', $offer_url );
			$image             = !empty($offer['thumbnail']) ? $offer['thumbnail'] : '';
			$imageid             = !empty($offer['thumbnail']['id']) ? $offer['thumbnail']['id'] : '';
			$image_url         = !empty($offer['thumbnail']['url']) ? $offer['thumbnail']['url'] : '';
			$image_alt         = !empty($offer['thumbnail']['alt']) ? $offer['thumbnail']['alt'] : '';
			$image_width         = !empty($offer['thumbnail']['width']) ? $offer['thumbnail']['width'] : '';
			$image_height         = !empty($offer['thumbnail']['height']) ? $offer['thumbnail']['height'] : '';
			$title             = !empty($offer['title']) ? $offer['title'] : '';
			$copy              = !empty($offer['copy']) ? $offer['copy'] : '';
			$current_price     = !empty($offer['currentPrice']) ? $offer['currentPrice'] : '';
			$old_price         = !empty($offer['oldPrice']) ? $offer['oldPrice'] : '';
			$button_text       = !empty($offer['button']['text']) ? $offer['button']['text'] : '';
			$read_more_text    = !empty($offer['readMore']) ? $offer['readMore'] : '';
			$button_nofollow       = !empty($offer['button']['noFollow']) ? ' rel="nofollow sponsored"' : '';
			$button_target       = !empty($offer['button']['newTab']) ? ' target="_blank""' : '';
			$read_more_url     = !empty($offer['readMoreUrl']) ? $offer['readMoreUrl'] : '';
			$read_more_ext     = !empty($offer['readMoreExt']) ? $offer['readMoreExt'] : '';
			$disclaimer        = !empty($offer['disclaimer']) ? $offer['disclaimer'] : '';
			$enable_badge      = !empty($offer['enableBadge']) ? $offer['enableBadge'] : '';
			$enable_score      = !empty($offer['enableScore']) ? $offer['enableScore'] : '';
			$badge             = !empty($offer['customBadge']) ? $offer['customBadge'] : '';
			$badgebg	       = !empty($badge['backgroundColor']) ? $badge['backgroundColor'] : '';
			$badgetxcolor	   = !empty($badge['textColor']) ? $badge['textColor'] : '';
			$badge_styles      = 'background-color:' . $badgebg . '; color:' . $badgetxcolor . ';';
			$offer_coupon      = !empty($offer['coupon']) ? $offer['coupon'] : '';
			$offer_coupon_date = !empty($offer['expirationDate']) ? $offer['expirationDate'] : '';
			$offer_coupon_mask = !empty($offer['maskCoupon']) ? $offer['maskCoupon'] : '';
			$mask_text         = !empty($offer['maskCouponText']) ? $offer['maskCouponText'] : '';
			$coupon_style      = '';
			$expired           = '';

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

			$coupon_mask_enabled = ( ! empty( $offer_coupon ) && $offer_coupon_mask && $expired != '1' ) ? '1' : '';
			$reveal_enabled      = ( $coupon_mask_enabled == '1' ) ? ' reveal_enabled' : '';

			$html .= '<div class="top_table_list_item border-lightgrey whitebg">';
			$html .= '	<div class="rh-flex-eq-height mobileblockdisplay'.$coupon_style.'">';
			if($imageid || $image_url){
				$html .= '		<div class="listbuild_image border-right listitem_column text-center rh-flex-center-align position-relative pt15 pb15 pr20 pl20">';

				if ( $enable_score ) {
					$html .= '         <div class="colored_rate_bar abdposright mt15">';
					$html .= '             <div class="review-small-circle mb10 fontbold text-center whitecolor mr10 floatleft rtlml10 r_score_' . round( $offer['score'] ) . '">';
					$html .= '                 <div class="overall-score">';
					$html .= '                   <span class="overall">' . esc_html( $score ) . '</span>';
					$html .= '                 </div>';
					$html .= '             </div>';
					$html .= '         </div>';
				}

				$html .= '           <figure class="position-relative margincenter width-150">';
				$html .= '              <a target="_blank" rel="nofollow sponsored" class="img-centered-flex rh-flex-center-align rh-flex-justify-center" href="' . esc_url( $offer_url ) . '">';

				if($imageid && class_exists('WPSM_image_resizer')){
					$html .= \WPSM_image_resizer::show_wp_image('smallgrid', $imageid, array('nofeatured'=>true));
				}else{
					$html .= '                  <img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_alt ) . '"';
					$html .= '                       width="' . esc_attr( $image_width ) . '" height="' . esc_attr( $image_height ) . '"/>';
				}
				$html .= '              </a>';
				$html .= '           </figure>';
				$html .= '		</div>';
			}
			$html .= '      <div class="rh-flex-grow1 border-right listitem_title listitem_column pt15 pb15 pr20 pl20">';
			$html .= '          <' . $title_tag . ' class="font120 mb10 mt0 list_heading fontbold">';
			$html .= '            <a href="' . esc_url( $offer_url ) . '" class="rehub-main-color"'.$button_target.$button_nofollow.'>';
			$html .= '              ' . wp_kses_post( $title ) . '';
			$html .= '            </a>';

			if ( $enable_badge ) {
				$html .= '<span class="blockstyle">';
				$html .= '	<span class="re-line-badge re-line-badge--default" style="' . esc_attr( $badge_styles ) . '">';
				$html .= '      <span>' . esc_html( $badge['text'] ) . '</span>';
				$html .= '	</span>';
				$html .= '</span>';
			}

			$html .= '          </' . $title_tag . '>';
			$html .= '          <div class="lineheight20">' .trim( $copy ). '</div>';
			$html .= '		</div>';
			$html .= '      <div class="listbuild_btn listitem_column text-center rh-flex-center-align pt15 pb15 pr20 pl20 rh-flex-justify-center">';
			$html .= '        <div class="width-100p">';
			$html .= '            <div class="priced_block clearfix block_btnblock mobile_block_btnclock mb5 ' . esc_attr( $reveal_enabled ) . ' ' . esc_attr( $coupon_style ) . '">';

			if ( $current_price ) {
				$html .= '<span class="rh_price_wrapper">';
				$html .= '	<span class="price_count">';
				$html .= '      <span class="rh_regular_price">' . wp_kses_post( trim( $current_price ) ) . '</span>';

				if ( $old_price && $old_price !== $current_price ) {
					$html .= '<del class="ml5 mr5">' . wp_kses_post( trim( $old_price ) ) . '</del>';
				}

				$html .= '	</span>';
				$html .= '</span>';
			}

			if ( $offer_url ) {
				$html .= '<span class="rh_button_wrapper">';
				$html .= '	<a href="' . esc_url( $offer_url ) . '" class="btn_offer_block re_track_btn"'.$button_target.$button_nofollow.'>';

				if ( $button_text ) {
					$html .= wp_kses_post( trim( $button_text ) );
				} elseif ( \REHub_Framework::get_option( 'rehub_btn_text' ) != '' ) {
					$html .= \REHub_Framework::get_option( 'rehub_btn_text' );
				} else {
					$html .= esc_html__( 'Buy It Now', 'rehub-framework' );
				}

				$html .= '	</a>';
				$html .= '</span>';
			}

			if(!empty($offer_coupon)){
				if ( $coupon_mask_enabled == '1' ) {
					$html .= '<div class="blockstyle">';
					$html .= '	<span class="coupon_btn re_track_btn btn_offer_block rehub_offer_coupon masked_coupon ';

					if ( ! empty( $offer_coupon_date ) ) {
						$html .= $coupon_style;
					}
					wp_enqueue_script('zeroclipboard');
					wp_enqueue_script('affegg_coupons');
					$html .= '" data-clipboard-text="'.rawurlencode(esc_html($offer_coupon)).'" data-codetext="'.rawurlencode(esc_html($offer_coupon)).'" data-dest="'.esc_url($offer_url).'">';

					if ( ! empty( $mask_text ) ) {
						$html .= esc_html( $mask_text );
					} elseif ( \REHub_Framework::get_option( 'rehub_mask_text' ) != '' ) {
						$html .= \REHub_Framework::get_option( 'rehub_mask_text' );
					} else {
						$html .= esc_html__( 'Reveal coupon', 'rehub-framework' );
					}

					$html .= '	</span>';
					$html .= '</div>';
				} else {
					wp_enqueue_script('zeroclipboard');
					if ( ! empty( $offer_coupon ) ) {
						$html .= '<div class="rehub_offer_coupon not_masked_coupon';
						if ( ! empty( $offer_coupon_date ) ) {
							$html .= $coupon_style;
						}
						$html .= '" data-clipboard-text="'.esc_html($offer_coupon).'">';
						$html .= '<span class="coupon_text">'. esc_html($offer_coupon) .'</span>';
						$html .= '<i class="rhicon rhi-cut fa-rotate-180"></i>';
						$html .= '</div>';
					}
				}
                if(!empty($offer_coupon_date)){
                    $html .= '<div class="time_offer">'.$coupon_text.'</div>';
                }
			}

			$readmoreext = ($read_more_ext) ? 'rel="nofollow sponsored" target="_blank"' : '';

			if ( $read_more_url ) {
				$html .= '<a href="' . esc_url( $read_more_url ) . '" class="read_full font85" '.$readmoreext.'>';
				if ( $read_more_text ) {
					$html .= esc_html( trim( $read_more_text ) );
				} elseif ( \REHub_Framework::get_option( 'rehub_readmore_text' ) != '' ) {
					$html .= strip_tags( \REHub_Framework::get_option( 'rehub_readmore_text' ) );
				} else {
					$html .= esc_html__( 'Read full review', 'rehub-framework' );
				}

				$html .= '</a>';
			}

			$html .= '            </div>';
			$html .= '        </div>';
			$html .= '      </div>';
			$html .= '	</div>';
			$html .= '</div>';

			if ( $disclaimer ) {
				$html .= '<div class="rev_disclaimer lightbluebg font70 lineheight15 pt10 pb10 pl15 pr15 flowhidden">';
				$html .= wp_kses( $offer['disclaimer'], 'post' );
				$html .= '</div>';
			}
		}
		$html .= '</div>';

		echo $html;
	}
}
