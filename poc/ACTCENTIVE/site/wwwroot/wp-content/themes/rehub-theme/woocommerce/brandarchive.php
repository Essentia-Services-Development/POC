<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
    $tagid = get_queried_object()->term_id; 
    $tagobj = get_term_by('id', $tagid, 'store');
    $tagname = $tagobj->name;
    $tagslug = $tagobj->slug;
    $brandimage = get_term_meta( $tagid, 'brandimage', true );
    $brandseconddesc = get_term_meta( $tagid, 'brand_second_description', true );
    $brandshortdesc = get_term_meta( $tagid, 'brand_short_description', true );
    $brandurl = get_term_meta( $tagid, 'brand_url', true ); 
    $brand_heading = get_term_meta( $tagid, 'brand_heading', true ); 
    $taglink = get_term_link( $tagid );
    $tagcat = (!empty($_GET["storecategory"])) ? esc_attr($_GET["storecategory"]) : '';
    $heading = ($brand_heading) ? do_shortcode($brand_heading) : $tagname;  
    $cashback_notice = get_term_meta( $tagid, 'cashback_notice', true ); 
?> 

<!-- CONTENT -->

<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <div class="rh-mini-sidebar-content-area tabletblockdisplay floatright">
            <div class="woo-tax-name">
                <h1 class="mt0 mb15 font150"><?php echo rehub_kses($heading);?></h1>
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
                    if($brandurl){
                        echo '<a class="blockstyle mt15 rehub_main_btn width-100p wpsm-button" data-cashbacknotice="'.$cashback_notice.'" href="'.esc_url($brandurl).'" target="_blank" rel="nofollow" data-url="'.esc_url($brandurl).'" data-merchant="'.$tagname.'"><i class="rhicon rhi-external-link"></i> '.__('Go to shop', 'rehub-theme').'</a>';
                    }
                    if($brandshortdesc){
                        echo '<div class="mt15 font80 lineheight20 text-left-align rtltext-right-align">'.wp_specialchars_decode( $brandshortdesc, ENT_QUOTES ).'</div>';
                    }
                ?>
            </div> 
            <?php
                $catterms = rh_get_crosstaxonomy('store', $tagid, 'product_cat');
                
                if(!empty($catterms)){
                    echo '<div class="rh_category_tab widget rh-cartbox rehub-sec-smooth"><div class="title">'.__('Categories', 'rehub-theme').'</div>'.rh_generate_incss('brandcategory').'<ul class="cat_widget_custom">';

                    foreach ($catterms as $catterm) {
                        $activeclass = ($catterm->tag_id == $tagcat) ? ' active' : '';
                        echo '<li><a href="'.$taglink.'?storecategory='.$catterm->tag_id.'" class="rh-dealstorelink'.$activeclass.'">'.$catterm->tag_name.'</a></li>';
                    }
                    echo '<li><a href="'.$taglink.'" class="rh-dealstorelink">'.__('All categories', 'rehub-theme').'</a></li>';

                    echo '</ul></div>';

                }
            ?>
        </div>             
        <div class="rh-mini-sidebar-content-area floatright tabletblockdisplay" id="content">
            <article class="post" id="page-<?php the_ID(); ?>">
                <?php do_action( 'woocommerce_before_main_content' );?>  
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
                        'filtertitle' => esc_html__('Favorite', 'rehub-theme'),
                        'filtertype' => 'meta',
                        'filterorderby' => 'date',
                        'filterorder'=> 'DESC', 
                        'filterdate' => 'all',
                        'filtermetakey' => 'post_wish_count'                        
                    );                     
                    $prepare_filter[] = array (
                        'filtertitle' => esc_html__('Popular', 'rehub-theme'),
                        'filtertype' => 'meta',
                        'filterorderby' => 'date',
                        'filterorder'=> 'DESC', 
                        'filterdate' => 'all', 
                        'filtermetakey' => 'rehub_views',                                               
                    ); 
                    $prepare_filter[] = array (
                        'filtertitle' => esc_html__('Most rated', 'rehub-theme'),
                        'filtertype' => 'meta',
                        'filterorderby' => 'date',
                        'filterorder'=> 'DESC', 
                        'filterdate' => 'all',
                        'filtermetakey' => '_wc_average_rating',                                                 
                    );  
                    $prepare_filter = urlencode(json_encode($prepare_filter));             
                ?>
                <?php 
                $arg_array = array(
                    'tax_name' => 'store',
                    'tax_slug' => $tagslug,
                    'data_source' => 'cat',
                    'filterpanel' => $prepare_filter,
                    'show'=> 24,
                    'enable_pagination'=> '2',
                );
                if($tagcat) {$arg_array['cat'] = $tagcat;}

                ?>

                <div class="re_filter_instore">
                    <?php echo rh_generate_incss('filterstore');?>
                    <?php   
                        $current_design = rehub_option('woo_design'); 
                        if(rehub_option('width_layout') == 'extended'){
                            $arg_array['columns'] = '4_col';
                        }
                        else{
                            $arg_array['columns'] = '3_col';
                        }
                        if ($current_design == 'grid') { 
                            $arg_array['price_meta'] = rehub_option('price_meta_woogrid');
                            echo wpsm_woogrid_shortcode($arg_array);                  
                        }
                        elseif ($current_design == 'gridtwo') { 
                            $arg_array['gridtype'] = 'compact';
                            $arg_array['price_meta'] = rehub_option('price_meta_woogrid');
                            echo rh_generate_incss('offergrid');
                            echo wpsm_woogrid_shortcode($arg_array);                  
                        }  
                        elseif ($current_design == 'gridrev') { 
                            $arg_array['gridtype'] = 'review';
                            echo wpsm_woogrid_shortcode($arg_array);                  
                        } 
                        elseif ($current_design == 'griddigi') { 
                            $arg_array['gridtype'] = 'digital';
                            echo wpsm_woogrid_shortcode($arg_array);                  
                        }
                        elseif ($current_design == 'deallist') { 
                            echo wpsm_woolist_shortcode($arg_array);                  
                        }
                        elseif ($current_design == 'list'){
                             echo wpsm_woorows_shortcode($arg_array);
                        }             
                        else{
                            echo wpsm_woocolumns_shortcode($arg_array);           
                        }
                    ?>
                </div> 

                <div class="dealstore_tax_second_desc">
                    <?php echo do_shortcode($brandseconddesc);?>
                </div>
                <?php
                    /**
                     * woocommerce_after_main_content hook.
                     *
                     * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
                     */
                    do_action( 'woocommerce_after_main_content' );
                ?>                       
            </article>
        </div>  
        <div class="rh-mini-sidebar tabletblockdisplay floatleft clearfix clearboxleft">                          
            <?php if ( is_active_sidebar( 'woostore-sidebar' ) ) : ?>
                <?php dynamic_sidebar( 'woostore-sidebar' ); ?>
            <?php endif; ?>                                     
        </div>
    </div>
</div>
<!-- /CONTENT -->     