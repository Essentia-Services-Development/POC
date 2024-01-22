<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php $no_featured_image_layout = (isset($no_featured_image_layout)) ? $no_featured_image_layout : '';?>
<?php $disableimage = get_post_meta($post->ID, 'show_featured_image', true);?>
<?php if($disableimage)  : ?>
<?php else : ?>
	<?php $postformat = get_post_meta($post->ID, 'rehub_framework_post_type', true);?>
	<?php if($postformat == 'video') : ?>		
		<?php $videoarray = get_post_meta($post->ID, 'video_post', true);?>
		<?php if ( !empty($videoarray[0]['video_post_schema']) ): ?>
			<?php                                                                               
				$video_schema_title = ( !empty($videoarray[0]['video_post_schema_title']) ) ? $videoarray[0]['video_post_schema_title'] : '';
				$video_schema_desc = ( !empty($videoarray[0]['video_post_schema_desc']) ) ? $videoarray[0]['video_post_schema_desc'] : '';
			?>
			<?php if(!empty($videoarray[0]['video_post_embed_url']) ) : ?>	
				<?php                                                                               
					$video_url = $videoarray[0]['video_post_embed_url'];
				?>
				<div class="media_video text-center clearfix" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">			
				<?php if(has_post_thumbnail()) : ?>
					<?php $image_id = get_post_thumbnail_id($post->ID);  $image_url = wp_get_attachment_url($image_id);?>
					<meta itemprop="thumbnailURL" content="<?php echo ''.$image_url; ?>">
					<meta itemprop="uploadDate" content="<?php the_time( 'c' ); ?>" />
					<meta itemprop="embedURL" content="<?php echo esc_url($video_url); ?>" />
				<?php else :?>
					<meta itemprop="thumbnailURL" content="<?php echo parse_video_url($video_url, 'hqthumb');?>">
					<meta itemprop="uploadDate" content="<?php the_time( 'c' ); ?>" />
					<meta itemprop="embedURL" content="<?php echo esc_url($video_url); ?>" />    
				<?php endif ;?> 
					<div class="border-lightgrey clearfix inner padd20 rh-shadow3 pb0">
						<div class="video-container">
							<?php $postsize = get_post_meta($post->ID, 'post_size', true);?>
							<?php if ($postsize == 'full_post') : ?>
								<?php echo parse_video_url($video_url, 'embed', '1068', '600');?>
							<?php else : ?>
								<?php echo parse_video_url($video_url, 'embed', '840', '430');?>
							<?php endif ;?> 
						</div>
						<h4 itemprop="name">
							<?php if (($video_schema_title) !='') :?>
								<?php echo esc_attr($video_schema_title) ;?>
							<?php else :?>
								<?php the_title(); ?>
							<?php endif ;?>    
						</h4>
						<p itemprop="description">
							<?php if (($video_schema_desc) !='') :?>
								<?php echo esc_attr($video_schema_desc) ;?>
							<?php else :?>
								<?php kama_excerpt('maxchar=250'); ?>
							<?php endif ;?>
						</p>
					</div>
				</div>
			<?php else : ?>		
				<?php if ( has_post_thumbnail() ) { ?>
					<figure class="top_featured_image"><?php the_post_thumbnail('full'); ?></figure>                                    
				<?php } ?>		
			<?php endif; ?>
		<?php else : ?>
			<?php if(!empty($videoarray[0]['video_post_embed_url']) ) : ?>
				<?php $video_url = $videoarray[0]['video_post_embed_url']?>
                    <div class="video-container">
						<?php $postsize = get_post_meta($post->ID, 'post_size', true);?>
                        <?php if ($postsize == 'full_post') : ?>
                            <?php echo parse_video_url($video_url, 'embed', '1150', '635');?>
                        <?php else : ?>
                            <?php echo parse_video_url($video_url, 'embed', '840', '430');?>
                        <?php endif ;?> 
                    </div>	
			<?php else : ?>		
				<?php if ( has_post_thumbnail() ) { ?>
					<figure class="top_featured_image"><?php the_post_thumbnail('full'); ?></figure>                                    
				<?php } ?>		
			<?php endif; ?>		
		<?php endif ?>									  
	<?php elseif($postformat == 'gallery') : ?>
		<?php $galleryarray = get_post_meta($post->ID, 'gallery_post', true);?>
		<?php  wp_enqueue_script('flexslider'); wp_enqueue_script('flexinit');wp_enqueue_style('flexslider'); ?>
		<?php 
			$gallery_images = ( !empty($galleryarray[0]['gallery_post_images']) ) ? $galleryarray[0]['gallery_post_images'] : ''; 
			$resizer = ( !empty($galleryarray[0]['gallery_post_images_resize']) ) ? $galleryarray[0]['gallery_post_images_resize'] : ''; 
		?>
		<div class="post_slider flexslider media_slider<?php if ($resizer =='1') :?> blog_slider<?php else :?> gallery_top_slider<?php endif ;?> loading">	
		    <i class="rhicon rhi-spinner fa-pulse"></i>
			<ul class="slides">		
				<?php 
					foreach ($gallery_images as $gallery_img) {
				?>
					<?php $postsize = get_post_meta($post->ID, 'post_size', true);?>
					<?php if($postsize == 'full_post') : ?>
                        <?php if (!empty ($gallery_img['gallery_post_video'])) :?>
                            <li data-thumb="<?php echo parse_video_url($gallery_img['gallery_post_video'], 'hqthumb'); ?>" class="play3">
                                <?php echo parse_video_url($gallery_img['gallery_post_video'], 'embed', '1150', '604');?>
                            </li>                                            
                        <?php else : ?>
	 						<li data-thumb="<?php $params = array( 'width' => 116, 'height' => 116, 'crop' => true  ); echo bfi_thumb($gallery_img['gallery_post_image'], $params); ?>">
								<?php if (!empty ($gallery_img['gallery_post_image_caption'])) :?><div class="bigcaption"><?php echo esc_attr($gallery_img['gallery_post_image_caption']); ?></div><?php endif;?>
								<img src="<?php if ($resizer =='1') {$params = array( 'width' => 1150);} else {$params = array( 'width' => 1150, 'height' => 604,  'crop' => true );}; echo bfi_thumb($gallery_img['gallery_post_image'], $params); ?>" alt="<?php if (!empty ($gallery_img['gallery_post_image_caption'])) :?><?php echo esc_attr($gallery_img['gallery_post_image_caption']); ?><?php endif;?>" />
							</li>                                           
                        <?php endif; ?>						                                               
					<?php else : ?>
                        <?php if (!empty ($gallery_img['gallery_post_video'])) :?>
                            <li data-thumb="<?php echo parse_video_url($gallery_img['gallery_post_video'], 'hqthumb'); ?>" class="play3">
                                <?php echo parse_video_url($gallery_img['gallery_post_video'], 'embed', '840', '478');?>
                            </li>                                            
                        <?php else : ?>
							<li data-thumb="<?php $params = array( 'width' => 80, 'height' => 80, 'crop' => true ); echo bfi_thumb($gallery_img['gallery_post_image'], $params); ?>">
								<?php if (!empty ($gallery_img['gallery_post_image_caption'])) :?><div class="bigcaption"><?php echo esc_attr($gallery_img['gallery_post_image_caption']); ?></div><?php endif;?>
								<img src="<?php if ($resizer =='1') {$params = array( 'width' => 840);} else {$params = array( 'width' => 840, 'height' => 478, 'crop' => true   );}; echo bfi_thumb($gallery_img['gallery_post_image'], $params); ?>" alt="<?php if (!empty ($gallery_img['gallery_post_image_caption'])) :?><?php echo esc_attr($gallery_img['gallery_post_image_caption']); ?><?php endif;?>" />
							</li>                                            
                        <?php endif; ?> 						                                                
					<?php endif; ?>
				<?php
					}
				?>
			</ul>
		</div>
	<?php else : ?>
		<?php if($no_featured_image_layout != 1) :?>
			<?php if ( (has_post_thumbnail()) && rehub_option('rehub_disable_feature_thumb') !='1'  ) { ?>
				<figure class="top_featured_image"><?php the_post_thumbnail('full'); ?></figure>   
			<?php } ?>
		<?php endif;?>
	<?php endif; ?>
    <?php if($postformat == 'music') : ?>
		<?php $musicarray = get_post_meta($post->ID, 'music_post', true);?>
	    <?php if($musicarray[0]['music_post_source'] == 'music_post_soundcloud') : ?>
	        <div class="music_soundcloud mb15">
	            <?php echo ''.$musicarray[0]['music_post_soundcloud_embed']; ?>
	        </div>                        
	    <?php else : ?>
	        <div class="music_spotify mb15">
	            <iframe src="https://embed.spotify.com/?uri=<?php echo ''.$musicarray[0]['music_post_spotify_embed']; ?>" width="100%" height="80" frameborder="0" allowtransparency="true"></iframe>
	        </div>
	    <?php endif; ?>
	<?php endif; ?>                    
<?php endif; ?>