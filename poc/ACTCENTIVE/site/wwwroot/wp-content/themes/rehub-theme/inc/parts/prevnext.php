<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!-- PAGER SECTION -->
<div class="float-posts-nav" id="float-posts-nav">
    <div class="postNavigation prevPostBox">
        <?php $prev_post = get_previous_post(); if (!empty( $prev_post )): ?>
            <div class="postnavprev">
                <div class="inner-prevnext">
                    <div class="thumbnail">
                        <?php         
                            $image_id = get_post_thumbnail_id($prev_post->ID); 
                            if($image_id){
                                $image_url = wp_get_attachment_image_src($image_id,'full');
                                $thumb = (!empty($image_url)) ? $image_url[0] : '';                                
                            }else{
                                $thumb = '';
                            }
                        ?>                    
                        <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $thumb, 'height'=> 70, 'width'=>70, 'crop'=>true, 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_70_70.png'));?>
                    </div>
                    <div class="headline"><span><?php esc_html_e('Previous', 'rehub-theme'); ?></span><h4><a href="<?php echo get_permalink( $prev_post->ID ); ?>"><?php echo ''.$prev_post->post_title; ?></a></h4></div>
                    </div>
            </div>                          
        <?php endif; ?>
    </div>
    <div class="postNavigation nextPostBox">
        <?php $next_post = get_next_post(); if (!empty( $next_post )): ?>
            <div class="postnavprev">
                <div class="inner-prevnext">
                    <div class="thumbnail">
                        <?php         
                            $image_id = get_post_thumbnail_id($next_post->ID); 
                            if($image_id){
                                $image_url = wp_get_attachment_image_src($image_id,'full');
                                $thumb = (!empty($image_url)) ? $image_url[0] : '';                                
                            }else{
                                $thumb = '';
                            }
                        ?>                    
                        <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $thumb, 'height'=> 70, 'width'=>70, 'crop'=>true, 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_70_70.png'));?>
                    </div>
                    <div class="headline"><span><?php esc_html_e('Next', 'rehub-theme'); ?></span><h4><a href="<?php echo get_permalink( $next_post->ID ); ?>"><?php echo ''.$next_post->post_title; ?></a></h4></div>
                </div> 
            </div>                        
        <?php endif; ?>
    </div>                        
</div>
<!-- /PAGER SECTION -->
