<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
    /* Template Name: Top table constructor*/
?>
<?php 
    $module_cats = get_post_meta($postid, 'top_review_cat');
    $disable_filters = get_post_meta($postid, 'top_review_filter_disable', true);
    $module_tag = get_post_meta($postid, 'top_review_tag', true);
    $module_fetch = intval(get_post_meta($postid, 'top_review_fetch', true));
    $module_width = get_post_meta($postid, 'top_review_width', true);
    $module_ids = get_post_meta($postid, 'manual_ids', true);
    $module_custom_post = get_post_meta($postid, 'top_review_custompost', true);
    $catalog_tax = get_post_meta($postid, 'catalog_tax', true);
    $catalog_tax_slug = get_post_meta($postid, 'catalog_tax_slug', true);   
    $catalog_tax_sec = get_post_meta($postid, 'catalog_tax_sec', true);
    $catalog_tax_slug_sec = get_post_meta($postid, 'catalog_tax_slug_sec', true);  
    $image_width = get_post_meta($postid, 'image_width', true);    
    $image_height = get_post_meta($postid, 'image_height', true); 
    $disable_crop = get_post_meta($postid, 'disable_crop', true);             
    $order_choose = get_post_meta($postid, 'top_review_choose', true);
    $rating_circle = get_post_meta($postid, 'top_review_circle', true);
    $module_pagination = get_post_meta($postid, 'top_review_pagination', true);
    $module_field_sorting = get_post_meta($postid, 'top_review_field_sort', true);
    $module_order = get_post_meta($postid, 'top_review_order', true);
    $first_column_enable = get_post_meta($postid, 'first_column_enable', true);
    $first_column_rank = get_post_meta($postid, 'first_column_rank', true);
    $last_column_enable = get_post_meta($postid, 'last_column_enable', true);
    $first_column_name = (get_post_meta($postid, 'first_column_name', true) !='') ? esc_html(get_post_meta($postid, 'first_column_name', true)) : esc_html__('Product', 'rehub-theme') ;
    $last_column_name = (get_post_meta($postid, 'last_column_name', true) !='') ? esc_html(get_post_meta($postid, 'last_column_name', true)) : '' ;
    $affiliate_link = get_post_meta($postid, 'first_column_link', true);
    $rows = get_post_meta($postid, 'columncontents', true);  //Get the rows  
    if ($module_fetch ==''){$module_fetch = '10';};   
    if ($rating_circle ==''){$rating_circle = '1';};
    $module_after = get_post_meta($postid, 'column_after_block', true);
    $module_enable = get_post_meta($postid, 'shortcode_table_enable', true);    

?>
<?php get_header(); ?>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
	    <!-- Main Side -->
        <div class="main-side page clearfix<?php if ($module_width =='1') : ?> full_width<?php endif;?>">
            <div class="title"><h1><?php the_title(); ?></h1></div>
            <?php if (!is_paged()) :?>
                <article class="top_rating_text mb15 post">
                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?><?php the_content(); ?><?php endwhile; endif; ?>
                </article>
                <div class="clearfix"></div>
            <?php endif; ?>

            <?php if ($module_enable !='1') :?>

                <?php 
                    if ( get_query_var('paged') ) { 
                        $paged = get_query_var('paged'); 
                    } 
                    else if ( get_query_var('page') ) {
                        $paged = get_query_var('page'); 
                    } 
                    else {
                        $paged = 1; 
                    }        
                ?>
                <?php if ($order_choose == 'cat_choose') :?>
	                <?php $args = array( 
	                    'cat' => $module_cats, 
	                    'tag' => $module_tag, 
                        'posts_per_page' => $module_fetch, 
                        'paged' => $paged, 
	                    'post_status' => 'publish', 
	                    'ignore_sticky_posts' => 1, 
	                );
	                ?> 
                    <?php if(!empty ($module_field_sorting)) {$args['meta_key'] = $module_field_sorting; $args['orderby'] = 'meta_value_num';} ?>
                    <?php if($module_order =='asc') {$args['order'] = 'ASC';} ?>
            	<?php elseif ($order_choose == 'manual_choose' && $module_ids !='') :?>
	                <?php $args = array( 
	                    'post_status' => 'publish', 
	                    'ignore_sticky_posts' => 1, 
	                    'orderby' => 'post__in',
	                    'post__in' => $module_ids,
                        'posts_per_page'=> -1,

	                );
	                ?>
                <?php elseif ($order_choose == 'custom_post') :?>
                    <?php $args = array(  
                        'posts_per_page' => $module_fetch, 
                        'paged' => $paged, 
                        'post_status' => 'publish', 
                        'ignore_sticky_posts' => 1,
                        'post_type' => $module_custom_post, 
                    );
                    ?> 
                    <?php if (!empty ($catalog_tax_slug) && !empty ($catalog_tax)) : ?>
                        <?php $args['tax_query'] = array (
                            array(
                                'taxonomy' => $catalog_tax,
                                'field'    => 'slug',
                                'terms'    => $catalog_tax_slug,
                            ),
                        );?>
                    <?php endif ?>
                    <?php if (!empty ($catalog_tax_slug_sec) && !empty ($catalog_tax_sec)) : ?>
                        <?php 
                            $args['tax_query']['relation'] = 'AND';
                            $args['tax_query'][] = 
                            array(
                                'taxonomy' => $catalog_tax_sec,
                                'field'    => 'slug',
                                'terms'    => $catalog_tax_slug_sec,
                            );
                        ;?>
                    <?php endif ?>                    
                    <?php if(!empty ($module_field_sorting)) {$args['meta_key'] = $module_field_sorting; $args['orderby'] = 'meta_value_num';} ?>
                    <?php if($module_order =='asc') {$args['order'] = 'ASC';} ?>                                                            
            	<?php else :?>
	                <?php $args = array( 
                        'posts_per_page' => 10, 
	                    'paged' => $paged, 
	                    'post_status' => 'publish', 
	                    'ignore_sticky_posts' => 1, 
	                );
	                ?> 
                    <?php if(!empty ($module_field_sorting)) {$args['meta_key'] = $module_field_sorting; $args['orderby'] = 'meta_value_num';} ?>
                    <?php if($module_order =='asc') {$args['order'] = 'ASC';} ?>                                		
            	<?php endif ;?>	

                <?php 
                $args = apply_filters('rh_module_args_query', $args);
                $wp_query = new WP_Query($args);
                do_action('rh_after_module_args_query', $wp_query);   
                $i=0; if ($wp_query->have_posts()) :?>
                <?php if($disable_filters !=1):?>
                    
                <?php endif;?>
                <?php $sortable_col = ($disable_filters !=1) ? ' data-tablesaw-sortable-col' : '';?>
                <?php $sortable_switch = ($disable_filters !=1) ? ' data-tablesaw-sortable-switch' : '';?>
                <?php wp_enqueue_script('tablesorter');?><?php wp_enqueue_style('tabletoggle'); ?>
                <div class="rh-top-table">
                    <?php if ($image_width || $image_height):?>
                        <style scoped>.rh-top-table .top_rating_item figure > a img{max-height: <?php echo esc_attr($image_height);?>px; max-width: <?php echo esc_attr($image_width);?>px;}.rh-top-table .top_rating_item figure > a, .rh-top-table .top_rating_item figure{height: auto;width: auto; border:none;}</style>
                    <?php endif;?>
                    <table  data-tablesaw-sortable<?php echo ''.$sortable_switch; ?> class="tablesaw top_table_block<?php if ($module_width =='1') : ?> full_width_rating<?php else :?> with_sidebar_rating<?php endif;?> tablesorter" cellspacing="0">
                        <thead> 
                        <tr class="top_rating_heading">
                            <?php if ($first_column_enable):?><th class="product_col_name" data-tablesaw-priority="persist"><?php echo esc_attr($first_column_name); ?></th><?php endif;?>
                            <?php if (!empty ($rows)) {
                                $nameid=0;                       
                                foreach ($rows as $row) {                       
                                $col_name = (!empty($rows[$nameid]['column_name'])) ? $rows[$nameid]['column_name'] : '';
                                echo '<th class="col_name"'.$sortable_col.' data-tablesaw-priority="1">'.esc_html($col_name).'</th>';
                                $nameid++;
                                } 
                            }
                            ?>
                            <?php if ($last_column_enable):?><th class="buttons_col_name"<?php echo ''.$sortable_col; ?> data-tablesaw-priority="1"><?php echo esc_attr($last_column_name); ?></th><?php endif;?>                      
                        </tr>
                        </thead>
                        <tbody>
                    <?php while ($wp_query->have_posts()) : $wp_query->the_post(); $i ++?>     
                        <tr class="top_rating_item" id='rank_<?php echo (int)$i?>'>
                            <?php if ($first_column_enable):?>
                                <td class="product_image_col"><?php echo re_badge_create('tablelabel'); ?>
                                    <figure>   
                                        <?php if (!is_paged() && $first_column_rank) :?><span class="rank_count"><?php if (($i) == '1') :?><i class="rhicon rhi-trophy-alt"></i><?php else:?><?php echo (int)$i?><?php endif ?></span><?php endif ?>                                                                   
                                        <?php $link_on_thumb = ($affiliate_link =='1') ? rehub_create_affiliate_link() : get_the_permalink(); ?>
                                        <?php $link_on_thumb_target = ($affiliate_link =='1') ? ' class="re_track_btn btn_offer_block" target="_blank" rel="nofollow"' : '' ; ?>
                                        <a href="<?php echo esc_url($link_on_thumb);?>"<?php echo ''.$link_on_thumb_target;?>>
                                            <?php 
                                            $showimg = new WPSM_image_resizer();
                                            $showimg->use_thumb = true;
                                            if(!$image_height) $image_height = 120;
                                            $showimg->height =  $image_height;
                                            if($image_width) {
                                                $showimg->width =  $image_width;
                                            }
                                            if($disable_crop) {
                                                $showimg->crop = false;
                                            }else{
                                                $showimg->crop = true;
                                            }                                        
                                            
                                            $showimg->show_resized_image();                                    
                                            ?>                                                                  
                                        </a>
                                    </figure>
                                </td>
                            <?php endif;?>
                            <?php 
                            if (!empty ($rows)) {
                                $pbid=0;                       
                                foreach ($rows as $row) {
                                $centered = (!empty($row['column_center'])) ? ' centered_content' : '' ;
                                echo '<td class="column_'.$pbid.' column_content'.$centered.'">';
                                echo do_shortcode(wp_kses_post($row['column_html']));                       
                                $element = $row['column_type'];
                                    if ($element == 'meta_value') {
                                        include(rh_locate_template('inc/top/metacolumn.php'));
                                    } else if ($element == 'taxonomy_value') {
                                            include(rh_locate_template('inc/top/taxonomyrow.php'));
                                    } else if ($element == 'woo_attribute') {
                                        include(rh_locate_template('inc/top/wooattribute.php'));                 
                                    } else if ($element == 'review_function') {
                                        include(rh_locate_template('inc/top/reviewcolumn.php'));
                                    } else if ($element == 'user_review_function') {
                                        include(rh_locate_template('inc/top/userreviewcolumn.php')); 
                                    } else if ($element == 'woo_review') {
                                        include(rh_locate_template('inc/top/wooreviewrow.php'));
                                    } else if ($element == 'woo_btn') {
                                        include(rh_locate_template('inc/top/woobtn.php')); 
                                    } else if ($element == 'woo_vendor') {
                                        include(rh_locate_template('inc/top/woovendor.php'));                        
                                    } else if ($element == 'static_user_review_function') {
                                        include(rh_locate_template('inc/top/staticuserreviewcolumn.php'));
                                    } else {
                                        
                                    };
                                echo '</td>';
                                $pbid++;
                                } 
                            }
                            ?>
                            <?php if ($last_column_enable):?>
                                <td class="buttons_col">
                                    <?php if ('product' == get_post_type(get_the_ID())):?>
                                        <?php include(rh_locate_template('inc/top/woobtn.php'));?>
                                    <?php else:?>
                                	    <?php rehub_generate_offerbtn('wrapperclass=block_btnblock mobile_block_btnclock mb5');?>
                                    <?php endif ;?>                                
                                </td>
                            <?php endif ;?>
                        </tr>
                    <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?><?php esc_html_e('No posts for this criteria.', 'rehub-theme'); ?>
                <?php endif; ?>
                <?php if ($module_pagination =='1') :?><div class="pagination"><?php rehub_pagination();?></div><?php endif ;?>
                <?php wp_reset_query(); ?>
                <?php if ($module_after !=''):?>
                    <div class="clearfix"></div>
                    <article class="post mt15"><?php echo do_shortcode($module_after);  ?></article>
                <?php endif ;?>

            <?php endif; ?>

		</div>	
        <!-- /Main Side -->  
        <?php if ($module_width !='1') : ?>
        <!-- Sidebar -->
        <?php get_sidebar(); ?>
        <!-- /Sidebar --> 
        <?php endif;?>
    </div>
</div>
<!-- /CONTENT -->     
<!-- FOOTER -->
<?php get_footer(); ?>