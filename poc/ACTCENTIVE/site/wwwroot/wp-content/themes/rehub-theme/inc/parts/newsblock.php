<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $post;?>
<?php $small = (isset($small) && $small !== 'false') ? $small : '';?>
<?php $secondtype = (isset($secondtype)) ? $secondtype : '';?>
<div class="news_out_thumb">
	<figure class="position-relative mb20">
        <?php if(!$small):?>
            <?php $category_echo = '';	
            if ('post' == get_post_type($post->ID) && rehub_option('exclude_cat_meta') != 1) {
                $category = get_the_category();
                if ( class_exists( 'WPSEO_Primary_Term' ) ) {
                    $wpseo_primary_term = new WPSEO_Primary_Term( 'category', $post->ID );
                    $wpseo_primary_term = $wpseo_primary_term->get_primary_term();
                    //$termyoast               = get_term( $wpseo_primary_term );
                    if (!is_numeric($wpseo_primary_term )) {
                        $category_id = $category[0]->term_id;
                    }else{
                        $category_id = $wpseo_primary_term; 
                    }
                }else{
                    $category_id = $category[0]->term_id; 
                }
				$category_link = get_category_link($category_id);
				$category_name = get_cat_name($category_id);
				$category_echo = '<span class="news_cat abdposleftbot"><a href="'.esc_url( $category_link ).'" class="rh-label-string">'.$category_name.'</a></span>';
				if($secondtype != '3'){echo ''.$category_echo;}                   	
            }
            ?>	
        <?php endif;?>    	
        <?php echo re_badge_create('ribbon'); ?>
	    <a href="<?php the_permalink();?>">
            <?php WPSM_image_resizer::show_static_resized_image(array('thumb'=> true, 'crop'=> true, 'width'=> 444, 'height'=> 250, 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_444_250.png'));?>   
        </a>
    </figure>
    <div class="text_out_thumb">
    	<?php echo (!empty($small)) ? '<h3 class="lineheight25 font115 mt10 mb10">' : '<h2 class="font150 mt20 mb20 lineheight25">' ;?><a href="<?php the_permalink();?>"><?php the_title();?></a><?php echo (!empty($small))  ? '</h3>' : '</h2>' ;?>
    	<div class="post-meta mb20"> <?php meta_small( true, false, true ); ?> </div> 
    	<?php if('post' == get_post_type($post->ID) && rehub_option('exclude_cat_meta') != 1 && !$small):?>
            <p class="lineheight20"><?php kama_excerpt('maxchar=150'); ?></p> 
            <?php if($secondtype == '3'){
                echo '<a class="blockstyle mt20" href="'.esc_url( $category_link ).'">'.$category_name.' <i class="rhicon rhi-arrow-right ml5"></i></a>';
            }
            ?>
        <?php endif;?>            
    </div> 
</div> 