<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Info box Widget class.
 *
 * 'NumHead' shortcode
 *
 * @since 1.0.0
 */
class Widget_ThemeElements extends Widget_Base {

	/* Widget Name */
	public function get_name() {
		return 'wpsm-themelements';
	}

	/* Widget Title */
	public function get_title() {
		return esc_html__('Theme Elements', 'rehub-theme');
	}

	/* Widget Icon */
	public function get_icon() {
		return 'eicon-layout-settings';
	}

	/* Theme Category */
	public function get_categories() {
		return [ 'helpler-modules' ];
	}

	/* Widget Keywords */
	public function get_keywords() {
		return [ 'header' ];
	}

	/* Widget Controls */
	protected function register_controls() {

		$this->start_controls_section(
			'section_control_ThemeElements',
			[
				'label' => esc_html__('Control', 'rehub-theme'),
			]
		);	
		$this->add_control(
			'themelement',
			[
				'label' => esc_html__('Theme Elements', 'rehub-theme'),
				'type' => Controls_Manager::SELECT,
				'default' => 'logo',
				'options' => [
					'logo' => esc_html__('Website logo', 'rehub-theme'),
					'cart' => esc_html__('Cart button', 'rehub-theme'),
					'loginicon' => esc_html__('Login icon', 'rehub-theme'),
					'loginbtn' => esc_html__('Login button', 'rehub-theme'), 
					'wishlist' => esc_html__('Wishlist icon', 'rehub-theme'),
					'comparison' => esc_html__('Comparision icon', 'rehub-theme'),
					'search' => esc_html__('Search form', 'rehub-theme'),
					'menu' => esc_html__('Desktop Menu', 'rehub-theme'),
					'mobilemenu' => esc_html__('Mobile Menu', 'rehub-theme'),
					'searchicon' => esc_html__('Search Icon', 'rehub-theme'),
				]
			]
		);
        $this->add_control( 'wishlist_url', [
            'label' => esc_html__( 'Url on wishlist page', 'rehub-theme' ),
            'description' => esc_html__('Set url on your page where you have [rh_get_user_favorites] shortcode', 'rehub-theme'),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => [
                'themelement' => 'wishlist',
            ],
        ]); 
        $this->add_control( 'wishlist_label', [
            'label' => esc_html__( 'Label under icon', 'rehub-theme' ),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => [
                'themelement' => ['wishlist'],
            ],
        ]);
	    $this->add_control( 'iconcolor', [
	        'label' => esc_html__( 'Set icon color', 'rehub-theme' ),
	        'type' => \Elementor\Controls_Manager::COLOR,
	        'selectors' => [
	            '{{WRAPPER}} .rh-header-icon' => 'color: {{VALUE}}',
	            '{{WRAPPER}} .heads_icon_label' => 'color: {{VALUE}}',
	        ],
            'condition' => [
                'themelement' => ['wishlist', 'comparison', 'loginicon'],
            ],
	    ]); 
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'menutypo',
                'label' => esc_html__( 'Title Typography', 'rehub-theme' ),
                'selector' => '{{WRAPPER}} nav.top_menu ul li a',
	            'condition' => [
	                'themelement' => 'menu',
	            ],
            ]
        ); 
	    $this->add_control( 'menucolor', [
	        'label' => esc_html__( 'Set text color', 'rehub-theme' ),
	        'type' => \Elementor\Controls_Manager::COLOR,
	        'selectors' => [
	            '{{WRAPPER}} nav.top_menu ul li a' => 'color: {{VALUE}}',
	        ],
            'condition' => [
                'themelement' => 'menu',
            ],
	    ]); 
        $this->add_control( 'mobilesliding', [
            'type'        => \Elementor\Controls_Manager::WYSIWYG,
            'label'       => esc_html__( 'Content', 'rehub-theme' ),
            'label_block'  => true,
            'condition' => [ 'themelement' => 'mobilemenu' ]
        ]);
	    $this->add_control( 'menuiconcolor', [
	        'label' => esc_html__( 'Set icon color', 'rehub-theme' ),
	        'type' => \Elementor\Controls_Manager::COLOR,
	        'selectors' => [
	            '{{WRAPPER}} .dl-menuwrapper button svg line' => 'stroke: {{VALUE}}',
	        ],
            'condition' => [
                'themelement' => 'mobilemenu',
            ],
	    ]); 
        $this->add_control( 'login_url', [
            'label' => esc_html__( 'Custom login url', 'rehub-theme' ),
            'description' => esc_html__('Set custom url where you have registration form or leave blank to trigger login popup on click', 'rehub-theme'),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => [
                'themelement' => ['loginicon', 'loginbtn'],
            ],
        ]); 
        $this->add_control( 'login_label', [
            'label' => esc_html__( 'Label under icon', 'rehub-theme' ),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => [
                'themelement' => ['loginicon'],
            ],
        ]);
        $this->add_control(
            'woobtn',
            array(
                'label'        => esc_html__( 'Show as button?', 'rehub-theme' ),
            	'description' => esc_html__('Colors can be set in Theme option - Appearance', 'rehub-theme'),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'rehub-theme' ),
                'label_off'    => esc_html__( 'No', 'rehub-theme' ),
                'return_value' => 'true',
	            'condition' => [
	                'themelement' => 'cart',
	            ],
            )
        );
        $this->add_control( 'compare_label', [
            'label' => esc_html__( 'Label under icon', 'rehub-theme' ),
            'label_block'  => true,
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => [
                'themelement' => ['comparison'],
            ],
        ]);
	    $this->add_control( 'searchiconcolor', [
	        'label' => esc_html__( 'Set icon color', 'rehub-theme' ),
	        'type' => \Elementor\Controls_Manager::COLOR,
	        'selectors' => [
	            '{{WRAPPER}} .icon-search-onclick' => 'color: {{VALUE}}',
	        ],
            'condition' => [
                'themelement' => 'searchicon',
            ],
	    ]);

		$this->end_controls_section();
	}
	
	/* Widget output Rendering */
	protected function render() {
		$settings = $this->get_settings_for_display();
	?>
	<?php if( $settings['themelement'] == 'logo' ): ?>
	<div class="logo">
		<?php if(rehub_option('rehub_logo')): /*  website logo  */ ?>
			<a href="<?php echo home_url(); ?>" class="logo_image"><img src="<?php echo rehub_option('rehub_logo'); ?>" alt="<?php bloginfo( 'name' ); ?>" height="<?php echo rehub_option( 'rehub_logo_retina_height' ); ?>" width="<?php echo rehub_option( 'rehub_logo_retina_width' ); ?>" /></a>
		<?php else : ?>
			<?php printf('%s in <span class="fontitalic">%s</span>', esc_html__('Upload Logo', 'rehub-theme'), esc_html__('Theme Options - Logo & favicon', 'rehub-theme') ); ?>
		<?php endif; ?>
	</div>
	<?php elseif( $settings['themelement'] == 'cart' ): /*  WC cart icon  */ ?>
		<?php if(class_exists('Woocommerce')): ?>
			<?php
				global $woocommerce;
				if ($woocommerce){
					if($woocommerce->cart){
						$cartbtn = !empty($settings['woobtn']) ? 'pt10 pb10 pr15 pl15 rehub-main-btn-bg rehub-main-smooth menu-cart-btn ' : '';
						echo '<div class="celldisplay rh_woocartmenu_cell text-center"><span class="inlinestyle '.$cartbtn.'"><a class="rh-header-icon rh-flex-center-align rh_woocartmenu-link cart-contents cart_count_'.$woocommerce->cart->cart_contents_count.'" href="'.wc_get_cart_url().'"><span class="rh_woocartmenu-icon"><span class="rh-icon-notice rehub-main-color-bg">'.$woocommerce->cart->cart_contents_count.'</span></span><span class="rh_woocartmenu-amount">'.$woocommerce->cart->get_total().'</span></a></span><div class="woocommerce widget_shopping_cart"></div></div>';
					}
				}
			?>
		<?php else: ?>
			<?php esc_html_e('WooCommerce plugin is not active', 'rehub-theme'); ?>
		<?php endif; ?>
	<?php elseif( $settings['themelement'] == 'loginicon' ): /*  login icon  */ ?>
		<div class="celldisplay login-btn-cell text-center">
			<?php $loginurl = (!empty($settings['login_url'])) ? esc_url($settings['login_url']) : '';?>
			<?php $classmenu = 'rh-header-icon rh_login_icon_n_btn ';?>
			<?php echo wpsm_user_modal_shortcode(array('class' =>$classmenu, 'loginurl'=>$loginurl, 'icon'=> 'rhicon rhi-user font95')); ?>
			<span class="heads_icon_label rehub-main-font login_icon_label">
				<?php $loginlabel = !empty($settings['login_label']) ? $settings['login_label'] : '';?>
				<?php echo esc_html($loginlabel); ?>
			</span>                                                   
		</div>
	<?php elseif( $settings['themelement'] == 'loginbtn' ): /*  login icon  */ ?>
        <?php $rtlclass = (is_rtl()) ? 'mr10' : 'ml10'; ?>
        <?php $loginurl = (!empty($settings['login_url'])) ? esc_url($settings['login_url']) : '';?>
        <?php $classmenu = $rtlclass;?>
        <?php echo wpsm_user_modal_shortcode(array('as_btn'=> 1, 'class' =>$classmenu, 'loginurl'=>$loginurl)); ?>                             
	<?php elseif( $settings['themelement'] == 'wishlist' ): /*  wishlist icon  */?>
		<?php if(!empty($settings['wishlist_url'])): ?>
			<?php $wishlist_page = $settings['wishlist_url']; ?>
			<div class="celldisplay text-center">
				<a href="<?php echo esc_url($wishlist_page);?>" class="rh-header-icon rh-wishlistmenu-link blockstyle">
					<?php  
						$likedposts = '';       
						if (is_user_logged_in()) { // user is logged in
							global $current_user;
							$user_id = $current_user->ID; // current user
							$likedposts = get_user_meta( $user_id, "_wished_posts", true);
						} else{
							$ip = rehub_get_ip(); // user IP address
							$likedposts = get_transient('re_guest_wishes_' . $ip);
						} 
						$wishnotice = (!empty($likedposts)) ? '<span class="rh-icon-notice rehub-main-color-bg">'.count($likedposts).'</span>' : '<span class="rh-icon-notice rhhidden rehub-main-color-bg"></span>';
					?>
					<span class="rhicon rhi-hearttip position-relative"><?php echo ''. $wishnotice; ?></span>
				</a>
				<?php $wishlistlabel = !empty($settings['wishlist_label']) ? $settings['wishlist_label'] : '';?>
				<span class="heads_icon_label rehub-main-font"><?php echo esc_html($wishlistlabel); ?></span>
			</div>
		<?php else: ?>
			<?php echo esc_html__('Select page for wishlist', 'rehub-theme'); ?>
		<?php endif; ?>
	<?php elseif( $settings['themelement'] == 'comparison' ): /* comparision icon */ ?>
		<?php if(rh_compare_icon(array())): ?>
		<div class="celldisplay rh-comparemenu-link rh-header-icon text-center">
			<?php echo rh_compare_icon(array()); ?>
			<?php $comparelabel = !empty($settings['compare_label']) ? $settings['compare_label'] : '';?>
			<span class="heads_icon_label rehub-main-font"><?php echo esc_html($comparelabel); ?></span>
		</div>
		<?php else: ?>
			<?php printf('%s in <span class="fontitalic">%s</span>', esc_html__('Select page for comparison', 'rehub-theme'), esc_html__('Theme Options - Dynamic comparison', 'rehub-theme') ); ?>
		<?php endif; ?>
	<?php elseif( $settings['themelement'] == 'search' ): /* search form */ ?>
        <div class="search head_search position-relative">
            <?php 
				$posttypes = rehub_option('rehub_search_ptypes');
                if( class_exists( 'Woocommerce' ) && empty($posttypes)){ 
					get_product_search_form();
                }else{ 
					get_search_form(); 
				}  
            ?>
        </div>
    <?php elseif( $settings['themelement'] == 'menu' ): /* menu */ ?>
		<!-- Main Navigation -->
            <div class="header_icons_menu">      
                <?php wp_nav_menu( array( 'container_class' => 'top_menu', 'container' => 'nav', 'theme_location' => 'primary-menu', 'fallback_cb' => 'add_menu_for_blank', 'walker' => new \Rehub_Walker ) ); ?>
            </div>
		<!-- /Main Navigation -->
    <?php elseif( $settings['themelement'] == 'searchicon' ): ?>
    	<div class="celldisplay rh-search-icon rh-header-icon text-center">
    	<span class="icon-search-onclick" aria-label="Search"></span>
		</div>
	<?php elseif( $settings['themelement'] == 'mobilemenu' ): /* mobile menu */ ?>
        <div class="rh_mobile_menu">
            <div id="dl-menu" class="dl-menuwrapper rh-flex-center-align">
                <button id="dl-trigger" class="dl-trigger" aria-label="Menu">
                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <g>
                            <line stroke-linecap="round" id="rhlinemenu_1" y2="7" x2="29" y1="7" x1="3"/>
                            <line stroke-linecap="round" id="rhlinemenu_2" y2="16" x2="18" y1="16" x1="3"/>
                            <line stroke-linecap="round" id="rhlinemenu_3" y2="25" x2="26" y1="25" x1="3"/>
                        </g>
                    </svg>
                </button>
                <div id="mobile-menu-icons" class="rh-flex-center-align rh-flex-right-align">
                    
                </div>
            </div>
            <?php do_action('rh_mobile_menu_panel'); ?>
        </div>
        <?php $mobilesliding = rh_check_empty_index($settings, 'mobilesliding');?>
        <?php if ($mobilesliding) echo '<div id="rhmobpnlcustom" class="rhhidden">'.rehub_kses(do_shortcode($mobilesliding)).'</div>';?>
	<?php endif; ?>
	<?php
	}
}
Plugin::instance()->widgets_manager->register( new Widget_ThemeElements );