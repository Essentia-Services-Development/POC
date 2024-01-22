<?php

namespace Rehub\Gutenberg;

use WP_REST_Request;
use WP_REST_Server;

defined( 'ABSPATH' ) OR exit;

require_once( 'microdata-parser-master/src/Microdata.php' );
require_once( 'microdata-parser-master/src/MicrodataDOMDocument.php' );
require_once( 'microdata-parser-master/src/MicrodataDOMElement.php' );
require_once( 'microdata-parser-master/src/MicrodataParser.php' );
require_once( 'microdata-parser-master/src/XpathParser.php' );

//require_once( 'vendor/autoload.php' );

use YusufKandemir\MicrodataParser\Microdata;
//use YusufKandemir\MicrodataParser\MicrodataDOMDocument;

class REST {
	private $rest_namespace = 'rehub/v2';

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'action_rest_api_init_trait' ) );
	}

	public function action_rest_api_init_trait() {
		//		if(!((is_user_logged_in() && is_admin()))) {
		//			return;
		//		}

		register_rest_route( $this->rest_namespace . '/posts',
			'/get',
			array(
				array(
					'methods'  => WP_REST_Server::CREATABLE,
					'permission_callback' => function ( WP_REST_Request $request ) {
						return current_user_can( 'editor' ) || current_user_can( 'administrator' );
					},
					'callback' => array( $this, 'rest_get_posts' ),
				)
			)
		);

		register_rest_route(
			$this->rest_namespace,
			"/offer-data/(?P<id>\d+)",
			array(
				'methods'  => WP_REST_Server::READABLE,
				'permission_callback' => function ( WP_REST_Request $request ) {
					return current_user_can( 'editor' ) || current_user_can( 'administrator' );
				},
				'callback' => array( $this, 'rest_offer_data_handler' ),
			)
		);

		register_rest_route(
			$this->rest_namespace,
			"/offer-listing/",
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'permission_callback' => function ( WP_REST_Request $request ) {
					return current_user_can( 'editor' ) || current_user_can( 'administrator' );
				},
				'callback' => array( $this, 'rest_offer_listing_handler' ),
			)
		);

		register_rest_route(
			$this->rest_namespace,
			"/parse-offer/",
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'permission_callback' => function ( WP_REST_Request $request ) {
					return current_user_can( 'editor' ) || current_user_can( 'administrator' );
				},
				'callback' => array( $this, 'rest_parse_offer_handler' ),
			)
		);

		register_rest_route(
			$this->rest_namespace,
			"/metaget/",
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'permission_callback' => function ( WP_REST_Request $request ) {
					return current_user_can( 'editor' ) || current_user_can( 'administrator' );
				},
				'callback' => array( $this, 'rest_parse_metavalue' ),
			)
		);

		register_rest_route(
			$this->rest_namespace,
			"/rehubelement/",
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'permission_callback' => function ( WP_REST_Request $request ) {
					return current_user_can( 'editor' ) || current_user_can( 'administrator' );
				},
				'callback' => array( $this, 'rest_parse_rehub_element' ),
			)
		);

		register_rest_route(
			$this->rest_namespace,
			"/ceelement/",
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'permission_callback' => function ( WP_REST_Request $request ) {
					return current_user_can( 'editor' ) || current_user_can( 'administrator' );
				},
				'callback' => array( $this, 'rest_parse_ceelement' ),
			)
		);
		register_rest_route(
			$this->rest_namespace,
			"/wooday/",
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'permission_callback' => function ( WP_REST_Request $request ) {
					return current_user_can( 'editor' ) || current_user_can( 'administrator' );
				},
				'callback' => array( $this, 'rest_parse_wooday' ),
			)
		);
		register_rest_route(
			$this->rest_namespace,
			"/woocomparebars/",
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'permission_callback' => function ( WP_REST_Request $request ) {
					return current_user_can( 'editor' ) || current_user_can( 'administrator' );
				},
				'callback' => array( $this, 'rest_parse_woocomparebars' ),
			)
		);

		register_rest_route(
			$this->rest_namespace,
			"/product/(?P<id>\d+)",
			array(
				'methods'  => WP_REST_Server::READABLE,
				'permission_callback' => function ( WP_REST_Request $request ) {
					return current_user_can( 'editor' ) || current_user_can( 'administrator' );
				},
				'callback' => array( $this, 'rest_product_handler' ),
			)
		);
	}

	public function rest_get_posts( WP_REST_Request $request ) {
		$params    = array_merge(
			array(
				's'         => '',
				'include'   => '',
				'exclude'   => '',
				'page'      => 1,
				'post_type' => 'post',
			), $request->get_params()
		);
		$isSelect2 = ( $request->get_param( 'typeQuery' ) === 'select2' );

		$args = array(
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'post_type'      => $params['post_type'],
			'paged'          => $params['page'],
		);

		if ( ! empty( $params['s'] ) ) {
			$args['s'] = $params['s'];
		}
		if ( ! empty( $params['include'] ) ) {
			$args['post__in'] = is_array( $params['include'] ) ? $params['include'] : array( $params['include'] );
		}
		if ( ! empty( $params['exclude'] ) ) {
			$args['post__not_in'] = is_array( $params['exclude'] ) ? $params['exclude'] : array( $params['exclude'] );
		}

		$response_array = array();
		$keys           = $isSelect2 ?
			[ 'label' => 'text', 'value' => 'id' ] :
			[ 'label' => 'label', 'value' => 'value' ];

		$posts = new \WP_Query( $args );
		if ( $posts->post_count > 0 ) {
			/* @var \WP_Post $gallery */
			foreach ( $posts->posts as $_post ) {
				$response_array[] = array(
					$keys['label'] => ! empty( $_post->post_title ) ? $_post->post_title : __( 'No Title', '' ),
					$keys['value'] => $_post->ID,
				);
			}
		}
		wp_reset_postdata();

		$return = array(
			'results'    => $response_array,
			'pagination' => array(
				'more' => $posts->max_num_pages >= ++ $params['page'],
			)
		);

		return rest_ensure_response( $return );
	}

	public function rest_offer_data_handler( WP_REST_Request $request ) {
		$id = $request->get_params()['id'];

		$product_url       = get_post_meta( $id, 'rehub_offer_product_url', true );
		$offer_post_url    = apply_filters( 'rehub_create_btn_url', $product_url );
		$offer_url         = apply_filters( 'rh_post_offer_url_filter', $offer_post_url );
		$offer_price       = get_post_meta( $id, 'rehub_offer_product_price', true );
		$offer_price_old   = get_post_meta( $id, 'rehub_offer_product_price_old', true );
		$offer_title       = get_post_meta( $id, 'rehub_offer_name', true );
		$offer_thumb       = get_post_meta( $id, 'rehub_offer_product_thumb', true );
		$offer_btn_text    = get_post_meta( $id, 'rehub_offer_btn_text', true );
		$offer_coupon      = get_post_meta( $id, 'rehub_offer_product_coupon', true );
		$offer_coupon_date = get_post_meta( $id, 'rehub_offer_coupon_date', true );
		$offer_coupon_mask = get_post_meta( $id, 'rehub_offer_coupon_mask', true );
		$offer_desc        = get_post_meta( $id, 'rehub_offer_product_desc', true );
		$disclaimer        = get_post_meta( $id, 'rehub_offer_disclaimer', true );
		$rating            = get_post_meta( $id, 'rehub_review_overall_score', true );
		$offer_mask_text   = '';
		//		$discount          = get_post_meta( $id, 'rehub_offer_discount', true );

		if ( $rating ) {
			$rating = $rating / 2;
		}

		if ( empty( $offer_title ) ) {
			$offer_title = get_the_title( $id );
		}

		if ( empty( $offer_thumb ) ) {
			$offer_thumb = get_the_post_thumbnail_url( $id );
		}

		if ( empty( $offer_btn_text ) ) {
			if ( ! empty( \REHub_Framework::get_option( 'rehub_btn_text' ) ) ) {
				$offer_btn_text = \REHub_Framework::get_option( 'rehub_btn_text' );
			} else {
				$offer_btn_text = 'Buy this item';
			}
		}

		if ( ! empty( \REHub_Framework::get_option( 'rehub_mask_text' ) ) ) {
			$offer_mask_text = \REHub_Framework::get_option( 'rehub_mask_text' );
		} else {
			$offer_mask_text = esc_html__( 'Reveal', 'rehub-framework' );
		}

		$data = array(
			'name'             => $offer_title,
			'description'      => $offer_desc,
			'disclaimer'       => $disclaimer,
			'old_price'        => $offer_price_old,
			'sale_price'       => $offer_price,
			'coupon_code'      => $offer_coupon,
			'expiration_date'  => $offer_coupon_date,
			'mask_coupon_code' => $offer_coupon_mask,
			'mask_coupon_text' => $offer_mask_text,
			'button_url'       => $offer_post_url,
			'button_text'      => $offer_btn_text,
			'thumbnail_url'    => $offer_thumb,
			'rating'           => $rating,
		);
		return rest_ensure_response( $data );
	}

	public function rest_product_handler( WP_REST_Request $request ) {
		$id   = $request->get_params()['id'];
		$data = array();

		if ( empty( $id ) ) {
			return new \WP_Error( 'empty_data', 'Pass empty data', array( 'status' => 404 ) );
		}

		$code_zone            = '';
		$price_label          = '';
		$mask_text            = '';
		$sync_items           = '';
		$video_thumbnails     = array();
		$gallery_images       = array();
		$is_coupon_expired    = false;
		$is_item_sync_enabled = false;
		$product              = wc_get_product( $id );
		$currency_symbol      = get_woocommerce_currency_symbol();
		$product_url          = $product->add_to_cart_url();
		$product_name         = $product->get_title();
		$product_desc         = $product->get_description();
		$image_id             = $product->get_image_id();
		$image_url            = wp_get_attachment_image_url( $image_id, 'full' );
		$gallery_ids          = $product->get_gallery_image_ids();
		$regular_price        = (float) $product->get_regular_price();
		$sale_price           = (float) $product->get_sale_price();
		$product_type         = $product->get_type();
		$product_on_sale      = $product->is_on_sale();
		$product_in_stock     = $product->is_in_stock();
		$add_to_cart_text     = $product->add_to_cart_text();
		$attributes           = $product->get_attributes();
		$product_videos       = get_post_meta( $id, 'rh_product_video', true );
		$coupon_expired_date  = get_post_meta( $id, 'rehub_woo_coupon_date', true );
		$is_expired           = get_post_meta( $id, 're_post_expired', true ) === '1';
		$coupon               = get_post_meta( $id, 'rehub_woo_coupon_code', true );
		$is_coupon_masked     = get_post_meta( $id, 'rehub_woo_coupon_mask', true ) === 'on' && ! empty( $coupon );
		$is_compare_enabled   = \REHub_Framework::get_option( 'compare_page' ) || \REHub_Framework::get_option( 'compare_multicats_textarea' );
		$loop_code_zone       = \REHub_Framework::get_option( 'woo_code_zone_loop' );
		$term_list            = strip_tags( get_the_term_list( $id, 'store', '', ', ', '' ) );

		if ( empty( $image_url ) ) {
			$image_url = rehub_woocommerce_placeholder_img_src( '' );
		}

		if ( ! empty( $product_desc ) ) {
			ob_start();
			kama_excerpt( 'maxchar=150&text=' . $product_desc . '' );
			$product_desc = ob_get_contents();
			ob_end_clean();
		}

		if ( $product_on_sale && $regular_price && $sale_price > 0 && $product_type !== 'variable' ) {
			$sale_proc   = 0 - ( 100 - ( $sale_price / $regular_price ) * 100 );
			$sale_proc   = round( $sale_proc );
			$price_label = $sale_proc . '%';
		}

		if ( $loop_code_zone ) {
			$code_zone = do_shortcode( $loop_code_zone );
		}

		if ( \REHub_Framework::get_option( 'rehub_mask_text' ) != '' ) {
			$mask_text = \REHub_Framework::get_option( 'rehub_mask_text' );
		} else {
			$mask_text = esc_html__( 'Reveal coupon', 'rehub-framework' );
		}

		if ( $coupon_expired_date ) {
			$timestamp1 = strtotime($coupon_expired_date );
			if(strpos($coupon_expired_date , ':') ===false){
				$timestamp1 += 86399;
			}
			$seconds    = $timestamp1 - (int) current_time( 'timestamp', 0 );
			$days       = floor( $seconds / 86400 );
			$seconds    %= 86400;

			if ( $days > 0 ) {
				$coupon_expired_date = $days . ' ' . esc_html__( 'days left', 'rehub-framework' );
				$is_coupon_expired   = false;
			} elseif ( $days == 0 ) {
				$coupon_expired_date = esc_html__( 'Last day', 'rehub-framework' );
				$is_coupon_expired   = false;
			} else {
				$coupon_expired_date = esc_html__( 'Expired', 'rehub-framework' );
				$is_coupon_expired   = true;
			}
		}

		if ( defined( '\ContentEgg\PLUGIN_PATH' ) ) {
			$itemsync = \ContentEgg\application\WooIntegrator::getSyncItem( $id );
			if ( ! empty( $itemsync ) ) {
				$is_item_sync_enabled = true;
				$sync_items           = do_shortcode( '[content-egg-block template=custom/all_offers_logo post_id="' . $id . '"]' );
			}
		}

		if ( ! empty( $attributes ) ) {
			ob_start();
			wc_display_product_attributes( $product );
			$attributes = ob_get_contents();
			ob_end_clean();
		}

		if ( ! empty( $gallery_ids ) ) {
			foreach ( $gallery_ids as $key => $value ) {
				$gallery_images[] = wp_get_attachment_url( $value );
			}
		}

		if ( ! empty( $product_videos ) ) {
			$product_videos = array_map( 'trim', explode( PHP_EOL, $product_videos ) );
			foreach ( $product_videos as $video ) {
				$video_thumbnails[] = parse_video_url( esc_url( $video ), "hqthumb" );
			}
		}

		$data['productUrl']        = $product_url;
		$data['productType']       = $product_type;
		$data['imageUrl']          = $image_url;
		$data['productName']       = $product_name;
		$data['description']       = $product_desc;
		$data['codeZone']          = $code_zone;
		$data['currencySymbol']    = $currency_symbol;
		$data['regularPrice']      = $regular_price;
		$data['salePrice']         = $sale_price;
		$data['priceLabel']        = $price_label;
		$data['coupon']            = $coupon;
		$data['addToCartText']     = $add_to_cart_text;
		$data['maskText']          = $mask_text;
		$data['couponExpiredDate'] = $coupon_expired_date;
		$data['brandList']         = $term_list;
		$data['productAttributes'] = $attributes;
		$data['galleryImages']     = $gallery_images;
		$data['videoThumbnails']   = $video_thumbnails;
		$data['syncItems']         = $sync_items;
		$data['isExpired']         = $is_expired;
		$data['couponMasked']      = $is_coupon_masked;
		$data['isCouponExpired']   = $is_coupon_expired;
		$data['isCompareEnabled']  = $is_compare_enabled;
		$data['isItemSyncEnabled'] = $is_item_sync_enabled;
		$data['productInStock']    = $product_in_stock;

		return json_encode( $data );
	}

	public function rest_offer_listing_handler( WP_REST_Request $request ) {
		$posts_id = $request['posts_id'];
		$data     = array();

		if ( empty( $posts_id ) || count( $posts_id ) === 0 ) {
			return new \WP_Error( 'empty_data', 'Pass empty data', array( 'status' => 404 ) );
		}


		foreach ( $posts_id as $index => $id ) {
			$button_text       = get_post_meta( (int) $id, 'rehub_offer_btn_text', true );
			$mask_text = '';
			$thumbnail_url     = get_the_post_thumbnail_url( (int) $id );
			$coupon_mask       = get_post_meta( (int) $id, 'rehub_offer_coupon_mask', true );
			$offer_coupon_date = get_post_meta( (int) $id, 'rehub_offer_coupon_date', true );
			$is_coupon_expired = false;
			$copy              = get_the_excerpt( (int) $id );

			if ( ! empty( $copy ) ) {
				ob_start();
				kama_excerpt( 'maxchar=120&text=' . $copy . '' );
				$copy = ob_get_contents();
				ob_end_clean();
			}

			if ( empty( $button_text ) ) {
				if ( ! empty( \REHub_Framework::get_option( 'rehub_btn_text' ) ) ) {
					$button_text = \REHub_Framework::get_option( 'rehub_btn_text' );
				} elseif ( $coupon_mask ) {
					$button_text = 'Reveal coupon';
				} else {
					$button_text = 'Buy this item';
				}
			}

			if ( ! empty( $button_text ) ) {
				$mask_text = $button_text;
			} elseif ( \REHub_Framework::get_option( 'rehub_mask_text' ) != '' ) {
				$mask_text = \REHub_Framework::get_option( 'rehub_mask_text' );
			} else {
				$mask_text = esc_html__( 'Reveal coupon', 'rehub-framework' );
			}

			if ( empty( $thumbnail_url ) ) {
				$thumbnail_url = plugin_dir_url( __FILE__ ) . 'assets/icons/noimage-placeholder.png';
			}

			if ( ! empty( $offer_coupon_date ) ) {
				$timestamp = strtotime( $offer_coupon_date ) + 86399;
				$seconds   = $timestamp - (int) current_time( 'timestamp', 0 );
				$days      = floor( $seconds / 86400 );

				if ( $days > 0 ) {
					$is_coupon_expired = false;
				} elseif ( $days == 0 ) {
					$is_coupon_expired = false;
				} else {
					$is_coupon_expired = true;
				}
			}

			
			$data[$index] = array(
				'score'          => get_post_meta( (int) $id, 'rehub_review_overall_score', true ),
				'thumbnail'      => array(
					'url' => $thumbnail_url,
				),
				'title'          => get_the_title( (int) $id ),
				'copy'           => $copy,
				'badge'          => re_badge_create( 'labelsmall', (int) $id ),
				'currentPrice'   => get_post_meta( (int) $id, 'rehub_offer_product_price', true ),
				'oldPrice'       => get_post_meta( (int) $id, 'rehub_offer_product_price_old', true ),
				'button'         => array(
					'text' => $button_text,
					'url'  => get_post_meta( (int) $id, 'rehub_offer_product_url', true ),
				),
				'coupon'         => get_post_meta( (int) $id, 'rehub_offer_product_coupon', true ),
				'maskCoupon'     => $coupon_mask,
				'expirationDate' => $offer_coupon_date,
				'maskCouponText' => $mask_text,
				'offerExpired'   => $is_coupon_expired,
				'readMore'       => 'Read full review',
				'readMoreUrl'    => '',
				'disclaimer'     => get_post_meta( (int) $id, 'rehub_offer_disclaimer', true ),
				'type'=> $request['type']
			);
			if($request['type'] === 'product'){
				$product = wc_get_product( $id );
				$data[$index]['currentPrice'] = $product->get_price();
				$data[$index]['oldPrice'] = $product->get_regular_price();
				$data[$index]['addToCartText'] = $product->add_to_cart_text();
				$data[$index]['priceHtml'] = $product->get_price_html();
			}
		}

		return json_encode( $data );
	}

	public function rest_parse_offer_handler( WP_REST_Request $request ) {
		$url = $request->get_params()['url'];

		if ( empty( $url ) || filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
			return new \WP_Error( 'invalid_url', 'Not valid url', array( 'status' => 404 ) );
		}
		
		$hostName = $this->get_host_name( $url );
		
		$xpathArray = array();
		
		if( $hostName == 'amazon' ){
			$xpathArray = array(
				'name' => '//h1[@id="title"]',
				'image'=> '//img[@id="landingImage"]',
				'description' => '//div[@id="productDescription"]/p',
				'priceCurrency' => '//div[@id="cerberus-data-metrics"]',
				'price' => '//span[@id="priceblock_ourprice"]%DELIMITER%//span[@id="priceblock_dealprice"]%DELIMITER%//div[@id="cerberus-data-metrics"]',
			);
		}
		
		if( !empty( $xpathArray ) ){ //we check if we have xpath ready
			return Microdata::fromXpathFile( $url )->toJSON( $xpathArray );
		}else{
			$args = array( 
				'timeout' => 30,
				'httpversion' => '1.0',
				'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36'
			);
			$request = wp_safe_remote_get($url, $args);
			$html = wp_remote_retrieve_body( $request );
			$reader = new \Brick\StructuredData\Reader\ReaderChain(
				new \Brick\StructuredData\Reader\MicrodataReader(),
				new \Brick\StructuredData\Reader\JsonLdReader()
			);
			$htmlReader = new \Brick\StructuredData\HTMLReader($reader);
			$items = $htmlReader->read($html, $url);
			$itemarray = array();
			foreach ($items as $index => $item) {
				$itemarray['items'][$index]['type'] = $item->getTypes();
				foreach ($item->getProperties() as $name => $values) {
					$name = str_replace(array('http://schema.org/', 'https://schema.org/'), '', $name);
					foreach ($values as $valueindex=>$value) {
						if ($value instanceof \Brick\StructuredData\Item) {
							$itemarray['items'][$index]['properties'][$name][$valueindex]['type'] = $value->getTypes();
							foreach ($value->getProperties() as $innername => $innervalues) {
								$innername = str_replace(array('http://schema.org/', 'https://schema.org/'), '', $innername);
								$itemarray['items'][$index]['properties'][$name][$valueindex]['properties'][$innername] = $innervalues;
							}
		
						}else{
							$itemarray['items'][$index]['properties'][$name][$valueindex] = $value;
						}
					}
				}
			}
			return json_encode($itemarray);
		}
	}

	public function rest_parse_metavalue( WP_REST_Request $request ) {
		$field = sanitize_text_field($request->get_param('field'));
		$postId = (int)$request->get_param('postId');
		$post_type = sanitize_text_field($request->get_param('post_type'));
		$type = sanitize_text_field($request->get_param('type'));
		$show_empty = sanitize_text_field($request->get_param('show_empty'));
		$label = sanitize_text_field($request->get_param('prefix'));
		$posttext = sanitize_text_field($request->get_param('postfix'));
		$icon = sanitize_text_field($request->get_param('icon'));
		$labelblock = sanitize_text_field($request->get_param('labelblock'));
		$showtoggle = sanitize_text_field($request->get_param('showtoggle'));
		if($post_type){
			$latest_cpt = get_posts("post_type='.$post_type.'&numberposts=1");
			$postId = $latest_cpt[0]->ID;
		}
		$value = wpsm_get_custom_value(array('field'=>$field, 'post_id'=>$postId, 'type'=>$type, 'show_empty'=>$show_empty, 'label'=>$label, 'posttext'=>$posttext, 'icon'=>$icon, 'labelblock'=>$labelblock, 'showtoggle'=>$showtoggle, 'spanvalue'=>1, 'post_type'=>$post_type));
		return json_encode($value);
	}

	public function rest_parse_rehub_element( WP_REST_Request $request ) {
		$postId = (int)$request->get_param('postId');
		$type = sanitize_text_field($request->get_param('type'));
		$value = '';
		if($type=='favorite'){
			$wishlistadd = esc_html__('Save', 'rehub-theme');
			$wishlistadded = esc_html__('Saved', 'rehub-theme');
			$wishlistremoved = esc_html__('Removed', 'rehub-theme');      
			$value ='<div class="favour_in_row favour_btn_red">'.RH_get_wishlist($postId, $wishlistadd, $wishlistadded, $wishlistremoved).'</div>';
		}
		else if($type=='share'){   
			$value =rehub_social_share("row");
		}
		else if($type=='sharesquare'){   
			$value =rehub_social_share("square");
		}
		else if($type=='thumb'){   
			$value =getHotThumb($postId, false, true);
		}
		else if($type=='thumbsmall'){   
			$value =getHotThumb($postId, false);
		}
		else if($type=='wishlisticon'){   
			$value =RHF_get_wishlist($postId);
		}
		else if($type=='hot'){   
			$value = RHgetHotLike($postId);
		}
		else if($type=='author'){   
			$imageheight = (int)$request->get_param('imageheight');
			$author_id = get_post_field( 'post_author', $postId );
			$name = get_the_author_meta( 'display_name', $author_id );
			$value = '<span class="admin_meta_el"><a class="admin rh-flex-center-align" href="'.get_author_posts_url( $author_id ).'">'.get_avatar( $author_id, $imageheight,'', $name, array('class'=>'mr10 roundborder50p') ).'<span class="admin-name">'.$name.'</span></a></span>';
		}
		else if($type=='bpbutton'){   
			$author_id = get_post_field( 'post_author', $postId );
			$labeltext = sanitize_text_field($request->get_param('labeltext'));
			if(class_exists( 'BuddyPress' ) &&  bp_is_active( 'messages' )){
				$value = '<div class="priced_block clearfix  fontbold mb0 lineheight25"><a href="#" class="btn_offer_block">'.$labeltext.'</a></div>';
			}else{
				$value = __('Please, enable message addon in Buddypress', 'rehub-framework');
			}

		}
		else if($type=='offerprice'){  
			ob_start();
			rehub_generate_offerbtn('showme=price&wrapperclass=fontbold mb0 lineheight25&postId='.$postId.'');
			$value = ob_get_contents();
			ob_end_clean(); 
		}
		else if($type=='authorbox'){  
			ob_start();
			rh_author_detail_box($postId);
			$value = ob_get_contents();
			ob_end_clean(); 
		}
		else if($type=='reviewcircle'){  
			$value = wpsm_reviewbox(array('compact'=>'circle', 'id'=> $postId)); 
		}
		else if($type=='postgallery'){  
			$imageheight = (int)$request->get_param('imageheight');
			$value = rh_get_post_thumbnails(array('video'=>1, 'columns'=>5, 'height'=>$imageheight, 'postid'=>$postId)); 
			$value = str_replace('data-src', 'src', $value);
		}
		else if($type=='offerbutton'){  
			ob_start();
			rehub_generate_offerbtn('showme=button&wrapperclass=fontbold mb0 lineheight25&updateclean=1&postId='.$postId.'');
			$value = ob_get_contents();
			ob_end_clean(); 
		}
		else if($type=='loginicon'){  
			$value = '<div class="celldisplay login-btn-cell text-center">
				<span class="act-rehub-login-popup rh-header-icon rh_login_icon_n_btn">
					<i class="rhicon rhi-user font95"></i>
				</span>';
			$value .= '<span class="heads_icon_label rehub-main-font login_icon_label">';
				$loginlabel = !empty($request->get_param('labeltext')) ? $request->get_param('labeltext') : '';
				$value .=esc_html($loginlabel);
				$value .='</span>';                                                
			$value .='</div>';
		}
		else if($type=='wishlistpageicon'){  
			$label = !empty($request->get_param('labeltext')) ? $request->get_param('labeltext') : '';
			$url = !empty($request->get_param('urltext')) ? $request->get_param('urltext') : '';
			if($url){
				$value = '<div class="celldisplay text-center">';
				$value .='<a href="#" class="rh-header-icon rh-wishlistmenu-link blockstyle"><span class="rhicon rhi-hearttip position-relative"><span class="rh-icon-notice rehub-main-color-bg">1</span></span></a>';
				$value .= '<span class="heads_icon_label rehub-main-font">';
					$value .=esc_html($label);
				$value .='</span>'; 
				$value .='</div>';
			}else{
				$value = esc_html__('Add url for wishlist page', 'rehub-framework');
			}
		}
		else if($type=='comparisonpageicon'){  
			$label = !empty($request->get_param('labeltext')) ? $request->get_param('labeltext') : '';
			if(rh_compare_icon(array())){
				$value = '<div class="celldisplay rh-comparemenu-link rh-header-icon text-center">';
				$value .= rh_compare_icon(array());
				$value .= '<span class="heads_icon_label rehub-main-font">';
					$value .=esc_html($label);
				$value .='</span>'; 
				$value .='</div>';
			}else{
				$value = sprintf('%s in <span class="fontitalic">%s</span>', esc_html__('Select page for comparison', 'rehub-framework'), esc_html__('Theme Options - Dynamic comparison', 'rehub-framework') );
			}
		}
		else if($type=='loginbutton'){  
			$rtlclass = (is_rtl()) ? 'mr10' : 'ml10';
			$value = '<span class="act-rehub-login-popup wpsm-button white medium mobileinmenu '.$rtlclass.'" data-type="login"><i class="rhicon rhi-sign-in"></i><span>'.esc_html__('Login / Register', 'rehub-framework').'</span></span>';
		}
		else if($type=='searchicon'){  
			$value = '<div class="celldisplay rh-search-icon rh-header-icon text-center"><span class="icon-search-onclick" aria-label="Search"></span></div>';
		}
		else if($type=='searchform'){  
			$value = '<div class="search head_search position-relative">';
			$posttypes = rehub_option('rehub_search_ptypes');
                if( class_exists( 'Woocommerce' ) && empty($posttypes)){ 
					$value .= get_product_search_form(false);
                }else{ 
					$value .=get_search_form(false); 
				}  
			$value .='</div>';
		}
		else if($type=='menu'){  
			$value = '<div class="header_icons_menu">';
				$value .= wp_nav_menu( array( 'container_class' => 'top_menu', 'container' => 'nav', 'theme_location' => 'primary-menu', 'fallback_cb' => 'add_menu_for_blank', 'walker' => new \Rehub_Walker, 'echo'=>false ) ); 
			$value .='</div>';
			if($request->get_param('convertmenumobile')){
				$value .= '<div class="rh_mobile_menu desktopdisplaynone"><div id="dl-menu" class="dl-menuwrapper rh-flex-center-align">';
					$value .= '<button id="dl-trigger" class="dl-trigger" aria-label="Menu">
					<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
						<g>
							<line stroke-linecap="round" id="rhlinemenu_1" y2="7" x2="29" y1="7" x1="3"/>
							<line stroke-linecap="round" id="rhlinemenu_2" y2="16" x2="18" y1="16" x1="3"/>
							<line stroke-linecap="round" id="rhlinemenu_3" y2="25" x2="26" y1="25" x1="3"/>
						</g>
					</svg>
				</button>'; 
				$value .='</div></div>';
			}
		}
		else if($type=='mobilemenu'){  
			$value = '<div class="rh_mobile_menu"><div id="dl-menu" class="dl-menuwrapper rh-flex-center-align">';
				$value .= '<button id="dl-trigger" class="dl-trigger" aria-label="Menu">
				<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
					<g>
						<line stroke-linecap="round" id="rhlinemenu_1" y2="7" x2="29" y1="7" x1="3"/>
						<line stroke-linecap="round" id="rhlinemenu_2" y2="16" x2="18" y1="16" x1="3"/>
						<line stroke-linecap="round" id="rhlinemenu_3" y2="25" x2="26" y1="25" x1="3"/>
					</g>
				</svg>
			</button>'; 
			$value .='</div></div>';
		}
		else if($type=='cart'){  
			ob_start();
			if(class_exists('Woocommerce')){
				$woobtn = $request->get_param('woobtn');
				$cartbtn = $woobtn ? 'rehub-main-btn-bg menu-cart-btn ' : '';
				echo '<div class="celldisplay rh_woocartmenu_cell text-center"><span class="inlinestyle '.$cartbtn.'"><a class="rh-header-icon rh-flex-center-align rh_woocartmenu-link cart-contents cart_count_1" href="'.wc_get_cart_url().'"><span class="rh_woocartmenu-icon"><span class="rh-icon-notice rehub-main-color-bg">1</span></span><span class="rh_woocartmenu-amount">$100.00</span></a></span><div class="woocommerce widget_shopping_cart"></div></div>';
			}
			else{
				esc_html_e('WooCommerce plugin is not active', 'rehub-theme');
			}
			$value = ob_get_contents();
			ob_end_clean(); 
		}

		return json_encode($value);
	}
	public function rest_parse_ceelement( WP_REST_Request $request ) {
		$value = '';
        $offertype = sanitize_text_field($request->get_param('type'));
        $post_id = (int)$request->get_param('postId');
        if(!$post_id){
            $post_id = get_the_ID();
        }
        if($offertype == 'ceoffer'){
            $value = wpsm_get_bigoffer(array('post_id'=> $post_id));
        }else{
            if($offertype == 'cemerchant'){
                $template = 'custom/all_merchant_widget_group';
            }
            else if($offertype == 'cewidget'){
                $template = 'custom/all_logolist_widget';
            }   
            else if($offertype == 'cegrid'){
                $template = 'custom/all_offers_grid';
            }

            else if($offertype == 'celist'){
                $template = 'custom/all_offers_list';
            }               

            else if($offertype == 'celistlogo'){
                $template = 'custom/all_offers_logo_group';
            }

            else if($offertype == 'celistdef'){
                $template = 'offers_list';
            }               

            else if($offertype == 'celistdeflogo'){
                $template = 'offers_logo';
            }               

            else if($offertype == 'cestat'){
                $template = 'price_statistics';
            }   

            else if($offertype == 'cehistory'){
                $template = 'custom/all_pricehistory_full';
            }   

            else if($offertype == 'cealert'){
                $template = 'custom/all_pricealert_full';
            } 
            $atts = array();
            $atts['post_id'] = $post_id;
            $atts['template'] = $template;
            if(defined('\ContentEgg\PLUGIN_PATH')) {
                $value = \ContentEgg\application\BlockShortcode::getInstance()->viewData($atts);
            }
        }  

		return json_encode($value);
	}
	public function rest_parse_wooday( WP_REST_Request $request ) {
		$value = '';
		$settings= array();
        $settings['ids'] = $request->get_param('ids');
        $settings['title'] = sanitize_text_field($request->get_param('title'));
        $settings['faketimer'] = sanitize_text_field($request->get_param('faketimer'));
        $settings['fakebar'] = sanitize_text_field($request->get_param('fakebar'));
        $settings['autorotate'] = sanitize_text_field($request->get_param('autorotate'));
        $settings['markettext'] = sanitize_text_field($request->get_param('markettext'));
        $settings['fakebar_sold'] = sanitize_text_field($request->get_param('fakebar_sold'));
        $settings['fakebar_stock'] = sanitize_text_field($request->get_param('fakebar_stock'));
		$wooblock = new \Rehub\Gutenberg\Blocks\Wooday;
		$value = $wooblock->render($settings);
		return json_encode($value);
	}
	public function rest_parse_woocomparebars( WP_REST_Request $request ) {
		$value = '';
        $ids = sanitize_text_field($request->get_param('ids'));
        $attr = sanitize_text_field($request->get_param('attr'));
        $min = sanitize_text_field($request->get_param('min'));
        $color = sanitize_text_field($request->get_param('color'));
        $markcolor = sanitize_text_field($request->get_param('markcolor'));

        $value = wpsm_woo_versus_function(array('ids'=> $ids, 'attr'=> $attr, 'min'=> $min, 'color'=> $color, 'markcolor'=> $markcolor)); 

		return json_encode($value);
	}
	public function rest_parse_template_part( WP_REST_Request $request ) {
		$postId = (int)$request->get_param('postId');
		$type = sanitize_text_field($request->get_param('type'));
		$value = '';
		if($type=='favorite'){

		}
		else if($type=='offerbutton'){  
			ob_start();
			
			$value = ob_get_contents();
			ob_end_clean(); 
		}

		return json_encode($value);
	}
	
    public function get_host_name( $url ) {
		$domain = strtolower(str_ireplace('www.', '', parse_url($url, PHP_URL_HOST)));
		
		// remove subdomain
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            $domain = $regs['domain'];
        }
		
		$hostData = explode('.', $domain);
		
		return $hostData[0];
    }
}
