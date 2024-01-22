<?php
/**
 * Custom - Results Page.
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-pt-results-wrapper-woo <?php echo esc_attr( $gmw['prefix'] ); ?>" data-id="<?php absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">	

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
		<div class="gmw-posts-wrapper woocommerce">
		
			<div class="woogridrev products col_wrap_fourth">	
				<?php $columns = '4_col';?>
				<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>

					<?php include(rh_locate_template('inc/parts/woogridrev.php')); ?>

				<?php endwhile; ?>
			<!--  end of the loop -->
			</div>			
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