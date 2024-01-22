<?php

namespace Rehub\Gutenberg;

defined('ABSPATH') OR exit;

final class Assets {

	protected $is_rest = false;

	/** @var \stdClass $assets */
	protected $assets = null;

	public function __construct(){
		add_action('enqueue_block_editor_assets', array( $this, 'editor_gutenberg' ));
		add_action('init', array( $this, 'init' ));
		add_filter('render_block', array( $this, 'guten_render_assets' ), 10, 2); //conditional assets loading

		$this->assets             = new \stdClass();
		$this->assets->path       = __DIR__.'/';
		$this->assets->path_css   = $this->assets->path.'assets/css/';
		$this->assets->path_js    = $this->assets->path.'assets/js/';
		$this->assets->path_image = $this->assets->path.'assets/images/';
		$this->assets->url        = plugins_url('/', __FILE__);
		$this->assets->url_css    = $this->assets->url.'assets/css/';
		$this->assets->url_js     = $this->assets->url.'assets/js/';
		$this->assets->url_image  = $this->assets->url.'assets/images/';

	}

	public function init(){
		$this->is_rest = defined('REST_REQUEST');
		wp_register_style('rh-gutenberg-admin',$this->assets->url_css.'editor.css', array(), '16.2');
		wp_register_style('rhgutslider', $this->assets->url_css . 'slider.css', array(), '1.2');
		wp_register_style('rhgutreviewheading', $this->assets->url_css . 'review-heading.css', array(), '1.0');
		wp_register_style('rhgutcomparison', $this->assets->url_css . 'comparison-table.css', array(), '1.3');
		wp_register_style('rhgutswiper', $this->assets->url_css . 'swiper-bundle.min.css', array(), '1.1');
		wp_register_style( 'rhpb-video',  $this->assets->url_css . 'rhpb-video.css', array(), '1.1' );
		wp_register_style( 'rhpb-lightbox',  $this->assets->url_css . 'simpleLightbox.min.css', array(), '1.0' );
		wp_register_style( 'rhpb-howto',  $this->assets->url_css . 'howto.css', array(), '1.0' );
		wp_register_style( 'rhpb-toc',  $this->assets->url_css . 'toc.css', array(), '1.0' );
		wp_register_style( 'rhofferlistingfull',  $this->assets->url_css . 'offerlistingfull.css', array(), '1.2' );
		wp_register_style( 'rhcountdownblock',  $this->assets->url_css . 'countdown.css', array(), '1.0' );
		wp_register_style( 'rhcolortitlebox',  $this->assets->url_css . 'colortitlebox.css', array(), '1.0' );
		wp_register_style('rhcontenttoggler', $this->assets->url_css . 'contenttoggler.css', array(), '1.0');
		wp_register_style('rhscorebox', $this->assets->url_css . 'scorebox.css', array(), '1.0');

		wp_register_script('rhgutslider', $this->assets->url_js . 'slider.js', array(), '1.1');
		wp_register_script('rhgutswiper', $this->assets->url_js . 'swiper-bundle.min.js', array(), true, '1.1');
		wp_register_script('rhgutequalizer', $this->assets->url_js . 'equalizer.js', array(), true, '1.2');	
		wp_register_script( 'rhpb-video',  $this->assets->url_js . 'rhpb-video.js', array(), true, '1.0' );
		wp_register_script( 'rhpb-lightbox',  $this->assets->url_js . 'simpleLightbox.min.js', array(), '1.0' );
		wp_register_script('lazysizes', $this->assets->url_js . 'lazysizes.js', array('jquery'), '5.2');
		wp_register_script( 'gctoggler',  $this->assets->url_js.'toggle.js', array(), '1.1', true );
		wp_register_script( 'rhcountdownblock',  $this->assets->url_js.'countdown.js', array(), '1.0', true );
		wp_register_script( 'contenttoggler',  $this->assets->url_js.'contenttoggler.js', array(), '1.0', true );
		wp_register_script( 'rh-flexslider',  $this->assets->url_js.'jquery.flexslider-min.js', array(), '1.0', true );

		wp_register_script(
			'rehub-block-format',
			$this->assets->url_js . 'format.js',
			array('wp-rich-text', 'wp-element', 'wp-editor'),
			null,
			true
		);

		add_action( 'wp_ajax_check_youtube_url', array( $this, 'check_youtube_url') );

		//Register core block styles
		wp_register_style('rhcoreblock_halfbackground', $this->assets->url_css . 'coreblock_halfbackground.css', array(), '1.0');
		wp_register_style('rhcoreblock_borderquery', $this->assets->url_css . 'coreblock_borderquery.css', array(), '1.0');
		wp_register_style('rhcoreblock_bordernopaddquery', $this->assets->url_css . 'coreblock_bordernopaddquery.css', array(), '1.0');
		wp_register_style('rhcoreblock_borderpaddradius', $this->assets->url_css . 'coreblock_borderpaddradius.css', array(), '1.0');
		wp_register_style('rhcoreblock_smartscrollposts', $this->assets->url_css . 'coreblock_smartscrollposts.css', array(), '1.0');
		wp_register_style('rhcoreblock_shadow1', $this->assets->url_css . 'coreblock_shadow1.css', array(), '1.0');
		wp_register_style('rhcoreblock_shadow2', $this->assets->url_css . 'coreblock_shadow2.css', array(), '1.0');
		wp_register_style('rhcoreblock_shadow3', $this->assets->url_css . 'coreblock_shadow3.css', array(), '1.0');

        //Add style to blocks
		register_block_style(
			'core/list',
			array(
				'name'  => 'nounderline',
				'label' => __('Unstyled view', 'rehub-framework'),
			)
		);
        register_block_style('core/post-featured-image', [
            'name' => 'halfbackground',
            'label' => __('Half white background under image', 'rehub-framework'),
        ]);
		register_block_style('core/query', [
			'name' => 'rhborderquery',
			'label' => __('Bordered block', 'rehub-framework'),
		]);
		register_block_style('core/query', [
			'name' => 'rhbordernopaddquery',
			'label' => __('No padding for image', 'rehub-framework'),
		]);
		register_block_style('core/query', [
			'name' => 'brdnpaddradius',
			'label' => __('Rounded border box', 'rehub-framework'),
		]);
		register_block_style('core/query', [
			'name' => 'smartscrollposts',
			'label' => __('Smart scroll carousel', 'rehub-framework'),
		]);
		register_block_style('core/group', [
			'name' => 'rhelshadow1',
			'label' => __('Light shadow', 'rehub-framework'),
		]);
		register_block_style('core/group', [
			'name' => 'rhelshadow2',
			'label' => __('Middle shadow', 'rehub-framework'),
		]);
		register_block_style('core/group', [
			'name' => 'rhelshadow3',
			'label' => __('Smooth shadow', 'rehub-framework'),
		]);

		register_post_meta( 'wp_block', 'rh_template_section', array(
			'show_in_rest' => true,
			'single' => true,
			'type' => 'string',
		) );

	}

	public function check_youtube_url(){
		$url = $_POST['url'];
		$max = wp_safe_remote_head($url);
		wp_send_json_success( wp_remote_retrieve_response_code($max) );
	}

	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 */
	function editor_gutenberg(){
		static $loaded = false;
		if($loaded) {
			return;
		}
		$loaded = true;

		global $pagenow;

		if ( 'widgets.php' !== $pagenow &&  'customize.php' !== $pagenow) {
			//add common editor js
			wp_enqueue_script(
				'rehub-blocks-editor',
				$this->assets->url_js.'editor.js',
				array('wp-api'),
				filemtime($this->assets->path_js.'editor.js'),
				true
			);
			wp_localize_script('rehub-blocks-editor','RehubGutenberg', array(
				'pluginDirUrl' => trailingslashit(plugin_dir_url( __DIR__ )),
				'isRtl' => is_rtl(),
			));

			//initialiation of editor styles are in blocks/video/block.json 
			wp_style_add_data( 'rh-gutenberg-admin', 'rtl', true );

			//add formatting
			wp_enqueue_script( 'rehub-block-format' );

			//add editor block scripts
			wp_enqueue_script(
				'rehub-block-script',
				$this->assets->url_js . 'backend.js',
				array('wp-api'),
				null,
				true
			);
			wp_enqueue_script('lazysizes');
			wp_enqueue_script('rh-flexslider');
		}

	}

   public function guten_render_assets($html, $block){
        static $renderedrh_styles = [];
		$block_style = '';
		if(isset( $block['blockName'] )){
			if ( $block['blockName'] === 'rehub/comparison-table' ) {
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'comparison-table.css');
				$block_style = $styles;
				if(!$block_style){
					wp_enqueue_style('rhgutcomparison');
				}
				wp_enqueue_script('rhgutequalizer');
				if(isset( $block['attrs']['responsiveView']) && $block['attrs']['responsiveView'] == 'slide'){
					wp_enqueue_style('rhgutswiper');
					wp_enqueue_script('rhgutswiper');
				}
				
			}
			if ( $block['blockName'] === 'rehub/review-heading' ) {
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'review-heading.css');
				$block_style = $styles;
				if(!$block_style){
					wp_enqueue_style('rhgutreviewheading');
				}
				
			}
			if ( $block['blockName'] === 'rehub/contenttoggler' ) {
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'contenttoggler.css');
				$block_style = $styles;
				wp_enqueue_script('contenttoggler');
				if(!$block_style){
					wp_enqueue_style('rhcontenttoggler');
				}
			}
			if ( $block['blockName'] === 'rehub/howto' ) {
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'howto.css');
				$block_style = $styles;
				if(!$block_style){
					wp_enqueue_style('rhpb-howto');
				}
			}
			if ( $block['blockName'] === 'rehub/toc' ) {
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'toc.css');
				$block_style = $styles;
				if(!$block_style){
					wp_enqueue_style('rhpb-toc');
				}
			}
			if ( $block['blockName'] === 'rehub/colortitlebox' ) {
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'colortitlebox.css');
				$block_style = $styles;
				if(!$block_style){
					wp_enqueue_style('rhcolortitlebox');
				}
			}
			if ( $block['blockName'] === 'rehub/scorebox' ) {
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'scorebox.css');
				$block_style = $styles;
				if(!$block_style){
					wp_enqueue_style('rhscorebox');
				}
			}
			if ( $block['blockName'] === 'rehub/slider' ) {
				wp_enqueue_script('rhgutslider');
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'slider.css');
				$block_style = $styles;
				if(!$block_style){
					wp_enqueue_style('rhgutslider');
				}
			}
			if ( $block['blockName'] === 'rehub/offerlistingfull' ) {
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'offerlistingfull.css');
				$block_style = $styles;
				wp_enqueue_script('gctoggler');
				if(!$block_style){
					wp_enqueue_style('rhofferlistingfull');
				}
			}
			if ( $block['blockName'] === 'rehub/countdown' ) {
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'countdown.css');
				$block_style = $styles;
				wp_enqueue_script('rhcountdownblock');
				if(!$block_style){
					wp_enqueue_style('rhcountdownblock');
				}
			}
			if( $block['blockName'] === 'rehub/video' ){
				$styles = rh_filesystem('get_content', $this->assets->url_css . 'rhpb-video.css');
				$block_style = $styles;	
				if(!$block_style){
					wp_enqueue_style('rhpb-video');
				}
				wp_enqueue_script( 'rhpb-video');
				if( !empty($block['attrs']['provider']) && $block['attrs']['provider'] === "vimeo" ){
					wp_enqueue_script( 'vimeo-player', 'https://player.vimeo.com/api/player.js', array(), true, '1.0' );
				}				
				if( isset($block['attrs']['overlayLightbox']) && $block['attrs']['overlayLightbox'] ){
					wp_enqueue_style( 'rhpb-lightbox');
					wp_enqueue_script( 'rhpb-lightbox' );
				}
				$width = isset($block['attrs']['width']) ? $block['attrs']['width'] : '';
				$height = isset($block['attrs']['height']) ? $block['attrs']['height'] : '';
				$block_style .= "#rhpb-video-" . $block['attrs']['blockId']. "{";
					if(!empty($width) && $width['desktop']['size'] > 0){
						$block_style .= "width: " . $width['desktop']['size'] . $width['desktop']['unit'] .";";
					}
					if(!empty($height) && $height['desktop']['size'] > 0){
						$block_style .= "height: " . $height['desktop']['size'] . $height['desktop']['unit'] .";";
					}
				$block_style .= "} @media (min-width: 1024px) and (max-width: 1140px) {";
				$block_style .= "#rhpb-video-" . $block['attrs']['blockId']. "{";
					if(!empty($width) && $width['landscape']['size'] > 0){
						$block_style .= "width: " . $width['landscape']['size'] . $width['landscape']['unit'] .";";
					}
					if(!empty($height) && $height['landscape']['size'] > 0){
						$block_style .= "height: " . $height['landscape']['size'] . $height['landscape']['unit'] .";";
					}
				$block_style .= "}";
				$block_style .= "} @media (min-width: 768px) and (max-width: 1023px) {";
				$block_style .= "#rhpb-video-" . $block['attrs']['blockId']. "{";
					if(!empty($width) && $width['tablet']['size'] > 0){
						$block_style .= "width: " . $width['tablet']['size'] . $width['tablet']['unit'] .";";
					}
					if(!empty($height) && $height['tablet']['size'] > 0){
						$block_style .= "height: " . $height['tablet']['size'] . $height['tablet']['unit'] .";";
					}
				$block_style .= "}";
				$block_style .= "} @media (max-width: 767px) {";
				$block_style .= "#rhpb-video-" . $block['attrs']['blockId']. "{";
					if(!empty($width) && $width['mobile']['size'] > 0){
						$block_style .= "width: " . $width['mobile']['size'] . $width['mobile']['unit'] .";";
					}
					if(!empty($height) && $height['mobile']['size'] > 0){
						$block_style .= "height: " . $height['mobile']['size'] . $height['mobile']['unit'] .";";
					}
				$block_style .= "} }";
				//wp_add_inline_style( 'rhpb-video', $block_style );
			}
			if ( $block['blockName'] === 'rehub/wc-featured-section' && !empty($block['attrs']['feat_type']) && $block['attrs']['feat_type'] == '1' ) {
				wp_enqueue_script('rh-flexslider');
			}
			if ( $block['blockName'] === 'rehub/featured-section' && !empty($block['attrs']['feat_type']) && $block['attrs']['feat_type'] == '2' ) {
				wp_enqueue_script('rh-flexslider');
			}       
			if(!empty( $block['attrs']['className'])){
				if(str_contains($block['attrs']['className'], 'is-style-halfbackground') !== false){
					$block_style = '.is-style-halfbackground::before {content: "";position: absolute;left: 0;bottom: 0;height: 50%;background-color: white;width:100vw;margin-left: calc(-100vw / 2 + 100% / 2);margin-right: calc(-100vw / 2 + 100% / 2);}.is-style-halfbackground, .is-style-halfbackground img{position:relative; margin-top:0; margin-bottom:0}';
				}
				elseif(str_contains($block['attrs']['className'], 'is-style-rhborderquery') !== false){
					$block_style = '.is-style-rhborderquery > ul > li{border:1px solid #eee; padding:15px;box-sizing: border-box; margin-bottom:1.25em}.is-style-rhborderquery figure{margin-top:0}';
				}
				elseif(str_contains($block['attrs']['className'], 'is-style-rhbordernopaddquery') !== false){
					$block_style = '.is-style-rhbordernopaddquery > ul > li{border:1px solid #eee; padding:15px;box-sizing: border-box;margin-bottom:1.25em}.editor-styles-wrapper .is-style-rhbordernopaddquery figure.wp-block-post-featured-image, .is-style-rhbordernopaddquery figure.wp-block-post-featured-image{margin:-15px -15px 12px -15px !important}';
				}
				else if ($block['blockName'] == 'core/list') {
					if (str_contains($block['attrs']['className'], 'is-style-nounderline') !== false) {
						$block_style .= 'ul.is-style-nounderline {margin:0; padding:0;list-style:none}ul.is-style-nounderline a{text-decoration:none}ul.is-style-nounderline li{list-style:none}';
					}
				} 
				elseif(str_contains($block['attrs']['className'], 'is-style-brdnpaddradius') !== false){
					$block_style = '.is-style-brdnpaddradius > ul > li{background:#fff;border-radius:8px; padding:15px;box-sizing: border-box;box-shadow:-2px 3px 10px 1px rgb(202 202 202 / 26%);margin-bottom:1.25em}.editor-styles-wrapper .is-style-brdnpaddradius figure.wp-block-post-featured-image, .is-style-brdnpaddradius figure.wp-block-post-featured-image{margin:-15px -15px 12px -15px !important}.is-style-brdnpaddradius figure.wp-block-post-featured-image img{border-radius:8px 8px 0 0}';
				}
				elseif(str_contains($block['attrs']['className'], 'is-style-smartscrollposts') !== false){
					$block_style = '.is-style-smartscrollposts{overflow-x: auto !important;overflow-y: hidden;white-space: nowrap; -webkit-overflow-scrolling: touch;scroll-behavior: smooth;scroll-snap-type: x mandatory;}.is-style-smartscrollposts > ul{flex-wrap: nowrap !important;}.is-style-smartscrollposts > ul > li{border-radius:8px; padding:15px;box-sizing: border-box;border:1px solid #eee;margin-bottom:1.25em; min-width:230px;display: inline-block;margin: 0 13px 0px 0 !important;white-space: normal !important;scroll-snap-align: start;}.editor-styles-wrapper .is-style-smartscrollposts figure.wp-block-post-featured-image, .is-style-smartscrollposts figure.wp-block-post-featured-image{margin:-15px -15px 12px -15px !important}.is-style-smartscrollposts figure.wp-block-post-featured-image img{border-radius:8px 8px 0 0}.is-style-smartscrollposts::-webkit-scrollbar-track{background-color:transparent;border-radius:20px}.is-style-smartscrollposts::-webkit-scrollbar-thumb{background-color:transparent;border-radius:20px;border:1px solid transparent}.is-style-smartscrollposts:hover::-webkit-scrollbar-thumb{background-color:#ddd;}.is-style-smartscrollposts:hover{scrollbar-color: #ddd #fff;}';
				}
				elseif(str_contains($block['attrs']['className'], 'is-style-rhelshadow1') !== false){
					$block_style = '.is-style-rhelshadow1{box-shadow: 0px 5px 20px 0 rgb(0 0 0 / 3%);}';
				}
				elseif(str_contains($block['attrs']['className'], 'is-style-rhelshadow2') !== false){
					$block_style = '.is-style-rhelshadow2{box-shadow: 0 5px 21px 0 rgb(0 0 0 / 7%);}';
				}
				elseif(str_contains($block['attrs']['className'], 'is-style-rhelshadow3') !== false){
					$block_style = '.is-style-rhelshadow3{box-shadow: 0 5px 23px rgb(188 207 219 / 35%);border-top: 1px solid #f8f8f8;}';
				}
			}        
		}
		if($block_style){
			$html = '<style scoped>'.$block_style.'</style>'.$html;
		}

        return $html;
    }
}