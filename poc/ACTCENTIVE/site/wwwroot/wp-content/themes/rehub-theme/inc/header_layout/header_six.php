<!-- Logo section -->
<?php $icons=0;?>
<div class="logo_section_wrap hideontablet">
    <div class="rh-container">
        <div class="logo-section rh-flex-center-align tabletblockdisplay header_six_style clearfix">
            <div class="logo">
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
            <?php if(rehub_option('header_six_menu') != '') : ?>
                <?php $nav_menu = wp_get_nav_menu_object( rehub_option('header_six_menu') ); // Get menu
                if (!empty ($nav_menu)) :?>
                    <div id="re_menu_near_logo" class="hideontablet flowhidden floatleft">
                        <?php echo rh_generate_incss('menunearlogo');?>
                        <?php wp_nav_menu( array( 'fallback_cb' => '', 'menu' => $nav_menu, 'container' => false  ) );?>
                    </div>
                <?php endif ;?>                                       
            <?php endif; ?>
            <div class="rh-flex-center-align rh-flex-right-align">
            <?php if(rehub_option('header_six_src') == 1) : ?>
                <?php echo wpsm_searchform_shortcode(array('class'=>'position-relative head_search hideontablet mr5 ml5 litesearchstyle'));?>
            <?php endif; ?>                
            <?php if(rehub_option('header_six_btn') == 1) : ?>
                <?php $icons ++;?>
                <?php $rtlclass = (is_rtl()) ? 'mr10' : 'ml10'; ?>
                <?php $btnlink = rehub_option('header_six_btn_url'); ?>
                <?php $btnlabel = rehub_option('header_six_btn_txt'); ?>
                <?php $btn_color = (rehub_option('header_six_btn_color') != '') ? rehub_option('header_six_btn_color') : 'btncolor'; ?>
                <?php $header_six_btn_login = (rehub_option('header_six_btn_login') == 1) ? ' act-rehub-login-popup' : ''; ?>
                <?php $btnclass = 'addsomebtn mobileinmenu '.$rtlclass.$header_six_btn_login;?>
                <?php echo wpsm_shortcode_button(array('icon'=>'plus', 'link'=>$btnlink, 'class'=>$btnclass, 'color'=>$btn_color), $btnlabel);?>
            <?php endif; ?>                
            <?php if(rehub_option('header_six_login') == 1) : ?>
                <?php $icons ++;?>
                <?php $rtlclass = (is_rtl()) ? 'mr10' : 'ml10'; ?>
                <?php $loginurl = (rehub_option('custom_login_url')) ? esc_url(rehub_option('custom_login_url')) : '';?>
                <?php $classmenu = 'mobileinmenu '.$rtlclass;?>
                <?php echo wpsm_user_modal_shortcode(array('as_btn'=> 1, 'class' =>$classmenu, 'loginurl'=>$loginurl));?>
            <?php endif; ?> 
            </div>                        
        </div>
    </div>
</div>
<!-- /Logo section -->  
<!-- Main Navigation -->
<div class="<?php if ($icons < 2):?>header_icons_menu <?php endif;?>search-form-inheader main-nav mob-logo-enabled<?php if (rehub_option('rehub_sticky_nav') ==true){echo ' rh-stickme';}?><?php echo ''.$header_menuline_style;?>">  
    <div class="rh-container<?php if (rehub_option('rehub_sticky_nav') && rehub_option('rehub_logo_sticky_url') !=''){echo ' rh-flex-center-align logo_insticky_enabled';}?>"> 
	    <?php 
	        if (rehub_option('rehub_sticky_nav') && rehub_option('rehub_logo_sticky_url') !='') {
	            echo '<a href="'.get_home_url().'" class="logo_image_insticky"><img src="'.rehub_option('rehub_logo_sticky_url').'" alt="'.get_bloginfo( "name" ).'" /></a>';                
	        }             
	    ?>    
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
</div>
<!-- /Main Navigation -->
<?php if ($icons > 2 || rehub_option('rehub_mobtool_force')):?>
    <div id="rhNavToolWrap" class="rhhidden tabletblockdisplay mb0">
        <?php echo rh_generate_incss('icontoolbar');?>
        <div id="rhNavToolbar" class="rh-flex-align-stretch rh-flex-center-align rh-flex-justify-btw"></div>
    </div>
<?php endif; ?> 