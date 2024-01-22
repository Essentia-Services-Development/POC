<!-- Logo section -->
<div class="logo_section_wrap hideontablet">
    <div class="rh-container">
        <div class="logo-section rh-flex-center-align tabletblockdisplay header_eight_style clearfix">
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
            <div class="rhsloganlogo rehub-main-font lightgreycolor lineheight20 floatleft mr25" style="max-width:440px">
                <?php if(rehub_option('rehub_text_slogan')) : ?>
                    <?php echo rehub_option('rehub_text_slogan'); ?>
                <?php else : ?>
                    <?php bloginfo( 'description' ); ?>
                <?php endif; ?>
            </div>
            <div class="search head_search position-relative rh-flex-right-align"><?php get_search_form(); ?></div>                       
        </div>
    </div>
</div>
<!-- /Logo section -->  
<!-- Main Navigation -->
<div class="search-form-inheader main-nav<?php if (rehub_option('rehub_sticky_nav') ==true){echo ' rh-stickme';}?><?php echo ''.$header_menuline_style;?>">  
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