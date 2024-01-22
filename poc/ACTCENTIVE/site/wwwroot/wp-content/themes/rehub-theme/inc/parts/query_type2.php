<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $post;?>
<article class="blog_string mb30 rh-cartbox clearfix<?php if(is_sticky()) {echo " sticky";} ?>"> 
    <div class="blog_string_container">
        <h2 class="mt0 mb20"><?php if(is_sticky()) {echo "<i class='rhicon rhi-thumbtack'></i>";} ?><a href="<?php the_permalink();?>"><?php the_title();?></a>
        </h2>
    </div>         
    <figure class="mb20 position-relative text-center">
        <div class="abdposleftbot"><?php rh_post_header_cat('post', false);?></div>
        <a href="<?php the_permalink();?>"><?php WPSM_image_resizer::show_static_resized_image(array('thumb'=> true, 'crop'=> true, 'width'=> 800, 'height'=> 400));?></a> 
        <div class="rev-in-blog-circle abdposright mt10 mr10 ml10">
            <?php $reviewscore = wpsm_reviewbox(array('compact'=>'circleaverage', 'id'=> $post->ID));?><?php echo ''.$reviewscore;?>
        </div>               
    </figure>
    <div class="blog_string_info clearbox">
        <div class="meta post-meta-big mt0 mb0 greycolor clearfix">
            <?php rh_post_header_meta_big();?> 
        </div> 
        <div class="blog_string_holder clearbox mt10">  
            <p class="greycolor"><?php kama_excerpt('maxchar=300'); ?></p>
            <?php do_action( 'rehub_after_blog_list_text' ); ?>
            <?php if(rehub_option('disable_btn_offer_loop')!='1')  : ?><?php rehub_create_btn('yes') ;?><?php endif; ?>
            <?php do_action( 'rehub_after_blog_list' ); ?>                        
        </div>
        <?php rh_post_code_loop();?>
    </div>                                     
</article>