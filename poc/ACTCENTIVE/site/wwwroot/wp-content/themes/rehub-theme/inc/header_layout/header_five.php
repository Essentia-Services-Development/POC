<!-- Logo section -->
<?php $icons=0; $woocart = '';?>
<div class="<?php if (rehub_option('rehub_sticky_nav') ==true){echo 'rh-stickme ';}?>header_five_style logo_section_wrap header_one_row">
    <div class="rh-container tabletblockdisplay mb0 disabletabletpadding">
        <div class="logo-section rh-flex-center-align tabletblockdisplay disabletabletpadding mb0">
            <div class="logo hideontablet">
                <?php if(rehub_option('rehub_logo')) : ?>
                    <a href="<?php echo home_url(); ?>" class="logo_image"><img src="<?php echo rehub_option('rehub_logo'); ?>" alt="<?php bloginfo( 'name' ); ?>" height="<?php echo rehub_option( 'rehub_logo_retina_height' ); ?>" width="<?php echo rehub_option( 'rehub_logo_retina_width' ); ?>" /></a>
                <?php elseif (rehub_option('rehub_text_logo')) : ?>
                <div class="textlogo pb10 fontbold rehub-main-color"><?php echo rehub_option('rehub_text_logo'); ?></div>
                <div class="sloganlogo lineheight15">
                    <?php if(rehub_option('rehub_text_slogan')) : ?><?php echo rehub_option('rehub_text_slogan'); ?><?php else : ?><?php bloginfo( 'description' ); ?><?php endif; ?>
                </div> 
                <?php else : ?>
                    <div class="textlogo pb10 fontbold rehub-main-color"><?php bloginfo( 'name' ); ?></div>
                    <div class="sloganlogo lineheight15"><?php bloginfo( 'description' ); ?></div>
                <?php endif; ?>       
            </div> 
            <!-- Main Navigation -->
            <div class="main-nav mob-logo-enabled <?php if(rehub_option('header_five_menucenter')) : ?>rh-flex-grow1 rh-flex-center-align rh-flex-justify-center<?php else:?>rh-flex-right-align<?php endif;?> <?php echo ''.$header_menuline_style;?>">      
                <?php wp_nav_menu( array( 'container_class' => 'top_menu', 'container' => 'nav', 'theme_location' => 'primary-menu', 'fallback_cb' => 'add_menu_for_blank', 'walker' => new Rehub_Walker ) ); ?>
                <div class="responsive_nav_wrap rh_mobile_menu">
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
                            <button class='icon-search-onclick' aria-label='Search'><i class='rhicon rhi-search'></i></button>
                        </div>
                    </div>
                    <?php do_action('rh_mobile_menu_panel'); ?>
                </div>
            </div>  
            <?php if(rehub_option('header_six_src') == 1) : ?>
              <div class="rh-flex-grow1 mr20 ml20 hideontablet">
                <div class="head_search position-relative"><?php get_search_form(); ?></div>
              </div>
            <?php endif; ?> 
            <?php if(rehub_option('header_six_menu') != '') : ?>
                <?php $nav_menu = wp_get_nav_menu_object( rehub_option('header_six_menu') ); // Get menu
                if (!empty ($nav_menu)) :?>
                    <div id="re_menu_near_logo" class="hideontablet flowvisible floatleft">
                        <?php echo rh_generate_incss('menunearlogo');?>
                        <?php wp_nav_menu( array( 'fallback_cb' => '', 'menu' => $nav_menu, 'container_class' => 'top_menu', 'container' => 'nav'  ) );?>
                    </div>
                <?php endif ;?>                                       
            <?php endif; ?>        
            <div class="header-actions-logo">
                <div class="rh-flex-center-align">
                    <?php if(rehub_option('header_seven_more_element')) : ?>
                        <?php $custom_element = rehub_option('header_seven_more_element'); ?>
                        <div class="celldisplay link-add-cell">
                            <?php echo do_shortcode($custom_element);?>
                        </div>
                    <?php endif; ?>
                    <?php if(rehub_option('header_six_login')) : ?>
                        <?php $icons ++;?>
                        <?php $rtlclass = (is_rtl()) ? 'mr10 ml5' : 'ml10 mr5'; ?>
                        <?php $loginurl = (rehub_option('custom_login_url')) ? esc_url(rehub_option('custom_login_url')) : '';?>
                        <?php $classmenu = 'rh-flex-right-align mobileinmenu '.$rtlclass;?>
                        <?php echo wpsm_user_modal_shortcode(array('as_btn'=> 1, 'class' =>$classmenu, 'loginurl'=>$loginurl));?> 
                    <?php endif; ?>
                    <?php if(rehub_option('header_six_btn')) : ?>
                        <?php $icons ++;?>
                        <?php $rtlclass = (is_rtl()) ? 'mr10 ml5' : 'ml10 mr5'; ?>
                        <?php $btnlink = rehub_option('header_six_btn_url'); ?>
                        <?php $btnlabel = rehub_option('header_six_btn_txt'); ?>
                        <?php $btn_color = (rehub_option('header_six_btn_color') != '') ? rehub_option('header_six_btn_color') : 'btncolor'; ?>
                        <?php $header_six_btn_login = (rehub_option('header_six_btn_login') == 1) ? ' act-rehub-login-popup' : ''; ?>
                        <?php $btnclass = 'rh-flex-right-align addsomebtn mobileinmenu '.$rtlclass.$header_six_btn_login;?>
                        <?php echo wpsm_shortcode_button(array('icon'=>'plus', 'link'=>$btnlink, 'class'=>$btnclass, 'color'=>$btn_color), $btnlabel);?>  
                    <?php endif; ?> 
                    <?php if(rehub_option('header_src_icon')) : ?>
                        <div class="celldisplay rh-search-icon rh-header-icon text-center">
                            <span class="icon-search-onclick cursorpointer"></span>
                        </div>
                    <?php endif; ?>  
                    <?php if(rehub_option('header_seven_wishlist')):?>
                        <?php $icons ++;?>
                        <div class="celldisplay text-center">
                            <?php 
                            $likedposts = '';       
                            if ( is_user_logged_in() ) { // user is logged in
                                global $current_user;
                                $user_id = $current_user->ID; // current user
                                $likedposts = get_user_meta( $user_id, "_wished_posts", true);
                            }
                            else{
                                $ip = rehub_get_ip(); // user IP address
                                $likedposts = get_transient('re_guest_wishes_' . $ip);
                            } 
                            ?>
                            <a href="<?php echo esc_url(rehub_option('header_seven_wishlist'));?>" class="rh-header-icon mobileinmenu rh-wishlistmenu-link" aria-label="Wishlist"  data-wishcount="<?php echo (!empty($likedposts) ? count($likedposts) : 0);?>">
                                <?php  
                                    $wishnotice = (!empty($likedposts)) ? '<span class="rh-icon-notice rehub-main-color-bg">'.count($likedposts).'</span>' : '<span class="rh-icon-notice rhhidden rehub-main-color-bg"></span>';
                                ?>
                                <span class="rhicon rhi-hearttip position-relative">
                                    <?php echo ''.$wishnotice;?>
                                </span>
                            </a>                           
                        </div>
                    <?php endif;?>                                                           
                    <?php if(rehub_option('header_seven_compare_btn')):?>
                        <?php $icons ++;?>
                        <div class="celldisplay mobileinmenu rh-comparemenu-link rh-header-icon text-center">
                        <?php echo rh_compare_icon(array());?>
                        </div>
                    <?php endif;?>
                    <?php 
                    if (rehub_option('header_seven_cart')){
                        global $woocommerce;
                        if ($woocommerce){
                        $icons ++;
                        $woocart = true;
                        $cartbtn = rehub_option('header_seven_cart_as_btn') ? 'rehub-main-btn-bg rehub-main-smooth menu-cart-btn ' : 'menu-cart-btn';
                        echo '<div class="celldisplay rh_woocartmenu_cell text-center"><span class="inlinestyle '.$cartbtn.'"><a class="rh-header-icon rh-flex-center-align rh_woocartmenu-link cart-contents cart_count_'.$woocommerce->cart->cart_contents_count.'" href="'.wc_get_cart_url().'"><span class="rh_woocartmenu-icon"><span class="rh-icon-notice rehub-main-color-bg">'.$woocommerce->cart->cart_contents_count.'</span></span><span class="rh_woocartmenu-amount">'.$woocommerce->cart->get_total().'</span></a></span><div class="woocommerce widget_shopping_cart"></div></div>';
                        }                            
                    }?> 
                </div> 
            </div>                        
            <!-- /Main Navigation -->                                                        
        </div>
    </div>
</div>
<!-- /Logo section -->  
<?php if ($icons > 2 || rehub_option('rehub_mobtool_force') ):?>
    <div id="rhNavToolWrap" class="rhhidden tabletblockdisplay mb0">
        <?php echo rh_generate_incss('icontoolbar');?>
        <div id="rhNavToolbar" class="rh-flex-align-stretch rh-flex-center-align rh-flex-justify-btw"></div>
    </div>
<?php endif; ?>
