<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
    if(rehub_option('theme_subset') == 'rething') {
        return include(rh_locate_template('rethingsub/inc/parts/query_type3.php'));
    }
    elseif(rehub_option('theme_subset') == 'repick') {
        return include(rh_locate_template('repicksub/inc/parts/query_type3.php'));
    }
?>
<?php 
global $post;
if (isset($aff_link) && $aff_link == '1') {
    $link = rehub_create_affiliate_link ();
    $target = ' rel="nofollow sponsored" target="_blank"';
}
else {
    $link = get_the_permalink();
    $target = '';  
}
?>
<article class="small_post col_item<?php if(is_sticky()) {echo " sticky";} ?>">
    <div class="mb10 position-relative width-100p">
        <div class="cats_def floatleft pr30 rtlpl30">
            <?php
            if(rehub_option('exclude_cat_meta') != 1) {
                if ('post' == get_post_type($post->ID)) {
                    $category = get_the_category();
                    if($category){
                         if ( class_exists( 'WPSEO_Primary_Term' ) ) {
                            $wpseo_primary_term = new WPSEO_Primary_Term( 'category', $post->ID );
                            $wpseo_primary_term = $wpseo_primary_term->get_primary_term();
                            //$termyoast               = get_term( $wpseo_primary_term );
                            if (!is_numeric($wpseo_primary_term )) {
                                $first_cat = $category[0]->term_id;
                            }else{
                                $first_cat = $wpseo_primary_term; 
                            }
                        }else{
                            $first_cat = $category[0]->term_id; 
                        }
                        $output='';
                        $output .= '<a href="'.get_category_link($first_cat ).'" class="font70 fontbold greycolor inlinestyle lineheight15 mb5 mr5 upper-text-trans cat-'.$first_cat.'"';
                        $output .='>'.$category[0]->cat_name.'</a>';               
                        echo ''.$output;
                    }
                }
                elseif ('blog' == get_post_type($post->ID)) {
                    $term_list = get_the_term_list( $post->ID, 'blog_category', '', ' ', '' );
                    if($term_list && !is_wp_error($term_list)){
                        echo ''.$term_list;
                    }
                }                

            }
            ?>
         </div>
        <?php if (rehub_option('exclude_comments_meta') == 0) : ?><?php comments_popup_link( 0, 1, '%', 'comment_two floatright', ''); ?><?php endif ;?>
    </div>
    <h2 class="clearbox flexbasisclear mb10 mt0"><?php if(is_sticky()) {echo "<i class='rhicon rhi-thumbtack'></i>";} ?><a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>><?php the_title();?></a></h2>
    <div class="post-meta flexbasisclear"> <?php meta_all( true, false, true ); ?> </div>
    <?php do_action( 'rehub_after_masonry_grid_meta' ); ?>
    <figure  class="width-100p position-relative mb15">
        <?php if(rehub_option('repick_social_disable') !='1' && function_exists('rehub_social_share')) :?> <?php echo rehub_social_share('minimal'); ?> <?php endif;?>
        <?php echo re_badge_create('ribbonleft'); ?>
        <a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>>
            <?php wpsm_thumb('mediumgrid'); ?>
        </a>
    </figure>                                       
    <?php do_action( 'rehub_after_masonry_grid_figure' ); ?>
    <?php rh_post_code_loop();?>
    <p><?php kama_excerpt('maxchar=200'); ?></p>
    <?php do_action( 'rehub_after_masonry_grid_text' ); ?>
	<?php if(rehub_option('disable_btn_offer_loop')!='1')  : ?><?php rehub_generate_offerbtn('wrapperclass=block_btnblock width-100p mobile_block_btnclock&btn_more=no');?><?php endif; ?>   
</article>