<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php get_header(); ?>
        <?php 
            $tagid = get_queried_object()->term_id; 
            $tagobj = get_term_by('id', $tagid, 'dealstore');
            $tagname = $tagobj->name;
            $tagslug = $tagobj->slug;
            $brandimage = get_term_meta( $tagid, 'brandimage', true );
            $brandseconddesc = get_term_meta( $tagid, 'brand_second_description', true );
            $brandshortdesc = get_term_meta( $tagid, 'brand_short_description', true );
            $brandurlurl = get_term_meta( $tagid, 'brand_url', true ); 
            $brandurl = apply_filters('rh_post_offer_url_filter', $brandurlurl);
            $brandurl = apply_filters('rehub_create_btn_url', $brandurl, $tagslug);
            $brandurl = str_replace( '#038;', '&', $brandurl);
            $brand_heading = get_term_meta( $tagid, 'brand_heading', true ); 
            $taglink = get_term_link( $tagid );
            $tagcat = (!empty($_GET["dealcategory"])) ? esc_attr($_GET["dealcategory"]) : '';
            $heading = ($brand_heading) ? do_shortcode($brand_heading) : $tagname;  
            $cashback_notice = get_term_meta( $tagid, 'cashback_notice', true ); 
        ?> 

<!-- CONTENT -->

<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <div class="rh-mini-sidebar-content-area tabletblockdisplay floatright">
            <div class="woo-tax-name">
                <h1 class="mt0 mb15 font150"><?php echo do_shortcode($heading);?></h1>
                <?php
                    $description = term_description();
                    if ( $description && !is_paged() ) {
                        echo '<div class="term-description post">' . $description . '</div>';
                    }
                ?>                
            </div>            
        </div> 
        <div class="rh-mini-sidebar floatleft tabletblockdisplay mb20">
            <div class="text-center rh-cartbox woo-tax-logo mb20">       
                <?php 
                if (!empty ($brandimage)) { 
                    $showbrandimg = new WPSM_image_resizer();
                    $showbrandimg->height = '120';
                    $showbrandimg->src = $brandimage; 
                    $showbrandimg->title = $tagname;                                  
                    $showbrandimg->show_resized_image();
                }
                ?>
                <?php if($cashback_notice):?>                
                    <h3 class="mt0 mb15 font130 rehub-main-color rh_custom_notice"><?php echo esc_html($cashback_notice);?></h3>
                <?php endif;?>                
                <?php echo rehub_get_user_rate('admin', 'tax');?> 
                <?php             
                    if($brandurlurl){
                        echo '<a class="blockstyle mt15 rehub_main_btn width-100p wpsm-button re_track_btn" href="'.esc_url($brandurl).'" target="_blank" rel="nofollow" data-url="'.esc_url($brandurl).'" data-merchant="'.$tagname.'"><i class="rhicon rhi-external-link"></i> '.esc_html__('Go to shop', 'rehub-theme').'</a>';
                    }
                    if($brandshortdesc){
                        echo '<div class="mt15 font80 lineheight20 text-left-align rtltext-right-align">'.do_shortcode( rehub_kses($brandshortdesc)).'</div>';
                    }
                ?>
            </div> 
            <?php
                $catterms = rh_get_crosstaxonomy('dealstore', $tagid, 'category');
                
                if(!empty($catterms)){
                    echo '<div class="rh_category_tab widget rh-cartbox rehub-sec-smooth"><div class="title">'.esc_html__('Categories', 'rehub-theme').'</div>'.rh_generate_incss('brandcategory').'
                    <ul class="cat_widget_custom">';

                    foreach ($catterms as $catterm) {
                        $activeclass = ($catterm->tag_slug == $tagcat) ? ' active' : '';
                        echo '<li><a href="'.$taglink.'?dealcategory='.$catterm->tag_slug.'" class="rh-dealstorelink'.$activeclass.'">'.$catterm->tag_name.'</a></li>';
                    }
                    echo '<li><a href="'.$taglink.'" class="rh-dealstorelink">'.esc_html__('All categories', 'rehub-theme').'</a></li>';

                    echo '</ul></div>';

                }
            ?>
        </div>             
        <div class="rh-mini-sidebar-content-area floatright tabletblockdisplay">
            <article class="post"> 
                <?php $prepare_filter = array();?>
                <?php 
                    $prepare_filter[] = array (
                        'filtertitle' => esc_html__('All', 'rehub-theme'),
                        'filtertype' => 'all',
                        'filterorderby' => 'date',
                        'filterorder'=> 'DESC', 
                        'filterdate' => 'all',                        
                    );
                    $prepare_filter[] = array (
                        'filtertitle' => esc_html__('Deals', 'rehub-theme'),
                        'filtertype' => 'deals',
                        'filterorderby' => 'date',
                        'filterorder'=> 'DESC', 
                        'filterdate' => 'all',                        
                    );                    
                    $prepare_filter[] = array (
                        'filtertitle' => esc_html__('Coupons', 'rehub-theme'),
                        'filtertype' => 'coupons',
                        'filterorderby' => 'date',
                        'filterorder'=> 'DESC', 
                        'filterdate' => 'all',                        
                    ); 
                    $prepare_filter[] = array (
                        'filtertitle' => esc_html__('Sales', 'rehub-theme'),
                        'filtertype' => 'sales',
                        'filterorderby' => 'date',
                        'filterorder'=> 'DESC', 
                        'filterdate' => 'all',                        
                    ); 
                    if( rehub_option('rehub_post_exclude_expired') != '1'){
                        $prepare_filter[] = array (
                            'filtertitle' => esc_html__('Expired', 'rehub-theme'),
                            'filtertype' => 'expired',
                            'filterorderby' => 'date',
                            'filterorder'=> 'DESC', 
                            'filterdate' => 'all',                        
                        );  
                    }
                    $prepare_filter = urlencode(json_encode($prepare_filter));             
                ?>
                <?php 
                $arg_array = array(
                    'tax_name' => 'dealstore',
                    'tax_slug' => $tagslug,
                    'data_source' => 'cat',
                    'filterpanel' => $prepare_filter,
                    'show'=> 30,
                    'enable_pagination'=> '2',
                );
                if($tagcat) {$arg_array['cat_name'] = $tagcat;}
                if (rehub_option('brand_taxonomy_layout') == 'compact_grid' && rehub_option('width_layout') != 'extended'){$arg_array['show'] = 32;}
                $aff_link = (rehub_option('disable_inner_links') == 1) ? 1 : 0;
                $arg_array['aff_link'] = $aff_link;                

                ?>

                <?php if (rehub_option('rehub_post_exclude_expired')):?>
                    <?php $arg_array['show_coupons_only'] = '3';?>
                <?php endif;?>

                <?php if (rehub_option('disable_grid_actions')):?>
                    <?php $arg_array['disable_act'] = 1;?>
                <?php endif;?>

                <div class="re_filter_instore">
                    <?php echo rh_generate_incss('filterstore');?>
                    <?php if (rehub_option('brand_taxonomy_layout') == 'regular_list'):?>
                        <?php echo wpsm_small_thumb_loop_shortcode($arg_array);?>
                    <?php elseif (rehub_option('brand_taxonomy_layout') == 'deal_grid' || rehub_option('brand_taxonomy_layout') == 'compact_grid' || rehub_option('brand_taxonomy_layout') == 'mobilegrid'):?>
                        <?php 
                            if(rehub_option('width_layout') == 'extended'){
                                if(rehub_option('brand_taxonomy_layout') == 'compact_grid'){
                                    $arg_array['gridtype'] = 'compact';
                                    $arg_array['columns'] = '5_col'; 
                                }
                                else if(rehub_option('brand_taxonomy_layout') == 'mobilegrid'){
                                    $arg_array['gridtype'] = 'mobile';
                                    $arg_array['columns'] = '5_col'; 
                                }
                                else{
                                   $arg_array['columns'] = '4_col';
                                }
                            }
                            else{
                                if(rehub_option('brand_taxonomy_layout') == 'compact_grid'){
                                    $arg_array['gridtype'] = 'compact';
                                    $arg_array['columns'] = '4_col'; 
                                }
                                else if(rehub_option('brand_taxonomy_layout') == 'mobilegrid'){
                                    $arg_array['gridtype'] = 'mobile';
                                    $arg_array['columns'] = '4_col'; 
                                }
                                else{
                                   $arg_array['columns'] = '3_col';
                                }                                
                            }
                            $arg_array['price_meta'] = rehub_option('price_meta_grid');
                        ?> 
                        <?php echo wpsm_compactgrid_loop_shortcode($arg_array);?>
                    <?php elseif (rehub_option('brand_taxonomy_layout') == 'regular_grid'):?>
                        <?php 
                            if(rehub_option('width_layout') == 'extended'){
                                $arg_array['columns'] = '4_col';
                            }
                            else{
                                $arg_array['columns'] = '3_col';
                            }
                        ?> 
                        <?php echo wpsm_columngrid_loop_shortcode($arg_array);?>
                    <?php else:?>                    
                        <?php echo wpsm_offer_list_loop_shortcode($arg_array);?>
                    <?php endif;?>
                </div>

                <div class="dealstore_tax_second_desc">
                    <?php echo wpautop( do_shortcode($brandseconddesc));?>
                </div>       
            </article>
        </div>  
        <div class="rh-mini-sidebar tabletblockdisplay floatleft clearfix clearboxleft">                          
            <?php if ( is_active_sidebar( 'dealstore-sidebar' ) ) : ?>
                <?php dynamic_sidebar( 'dealstore-sidebar' ); ?>
            <?php endif; ?>                                     
        </div>
    </div>
</div>
<!-- /CONTENT -->     

<!-- FOOTER -->
<?php get_footer(); ?>