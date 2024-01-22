<?php
/**
 * Custom - Results Page.
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-pt-results-wrapper-list <?php echo esc_attr( $gmw['prefix'] ); ?>" data-id="<?php absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">	

	<?php if ( $gmw_form->has_locations() ) : ?>
	
		<?php do_action( 'gmw_search_results_start', $gmw ); ?>

        <div class="geo-pagination">

        	<!-- results message -->
            <div class="pag-count floatleft tabletblockdisplay">
                <?php gmw_results_message( $gmw, false ); ?>
            </div>

            <!-- per page -->
            <?php gmw_per_page( $gmw ); ?>
            <!-- pagination -->
            <div class="pagination-links tabletblockdisplay" id="member-dir-pag-top">
                <?php gmw_pagination( $gmw ); ?>
            </div>
        </div>
        <div class="clear"></div>

			
	    <?php gmw_results_map( $gmw ); ?>
		
		<div class="clear"></div>
		
		<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>
	
		<!--  Results wrapper -->
		<div class="gmw-posts-wrapper">
			
			<!--  this is where wp_query loop begins -->
			<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>

			<?php global $post;?>
			<div class="rh-gmw-post-list clearfix">
				<div class="tabledisplay">		
			    <div class="rh-gmw-list-image">
			    	<div>
			        <figure><?php echo re_badge_create('tablelabel'); ?>
			        <a href="<?php the_permalink();?>">
				        <?php 
				            $showimg = new WPSM_image_resizer();
				            $showimg->use_thumb = true;
				            $height_figure_single = apply_filters( 're_news_figure_height', 138 );
				            $showimg->height = $height_figure_single;
				            $showimg->width = $height_figure_single;
				            $showimg->crop = false;           
				            $showimg->show_resized_image();                                    
				        ?>
				        </a>
			        </figure> 
			        </div>  
					<?php do_action( 'rehub_after_left_list_thumb_figure' ); ?> 
			                               
			    </div>
			    <div class="rh-gmw-list-desc">
			    	<div class="newscom_head">
					    <?php echo rh_expired_or_not($post->ID, 'span');?><h2><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
					    <?php do_action( 'rehub_after_left_list_thumb_title' ); ?>
					    
				        <div class="meta post-meta">
			                <?php if(function_exists('get_favorites_button')) :?><?php the_favorites_button(); ?><?php endif;?>
				            <?php meta_all(false, false, false, true);?>                                                              
				        </div>
			        </div>
				    
				    <p><?php kama_excerpt('maxchar=220'); ?></p>
			    </div>
			    <div class="rh-gmw-list-additional"> 
					<?php if ( ! empty( $gmw['form_values']['lat'] ) ) { ?><span class="radius-dis">(<?php gmw_distance_to_location( $post, $gmw ); ?>)</span><?php } ?>		        
				    <?php do_action( 'rehub_after_left_list_thumb' ); ?>  		           
	    			<!--  Address -->
	    			<div class="wppl-address">
	    				<?php echo ''.$post->address; ?>
	    			</div>
	    			
	    			<!-- Get directions -->	 	
					<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
						<div class="get-directions-link">
							<?php $labels = (!empty($gmw['labels']['search_results']['directions'])) ? $gmw['labels']['search_results']['directions'] : '';?>
	    					<?php gmw_directions_link( $post, $gmw, $labels ); ?>
	    				</div>
	    			<?php } ?>       
			    </div>    
			    </div> 
			</div>

			<?php endwhile; ?>
			<!--  end of the loop -->		
		</div> <!--  results wrapper -->    	
		<?php do_action( 'gmw_search_results_after_loop' , $gmw ); ?>
	
	
	<?php else : ?>

        <div class="gmw-no-results">
            
            <?php do_action( 'gmw_no_results_start', $gmw ); ?>

            <?php gmw_no_results_message( $gmw ); ?>
            
            <?php do_action( 'gmw_no_results_end', $gmw ); ?> 

        </div>

    <?php endif; ?>		
	
	
</div> <!-- output wrapper -->