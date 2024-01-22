<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php if (class_exists('Woocommerce')):?>
	<?php if (defined('wcv_plugin_dir')):?>
		<?php if(WCV_Vendors::is_vendor( $creator )):?>
			<a href="<?php echo WCV_Vendors::get_vendor_shop_page( $creator );?>" class="mb10 inlinestyle"><?php esc_html_e('See all products'. 'rehub-theme');?></a>
		<?php endif;?>
	<?php endif;?>
	<div id="posts-list" class="bp-post-wrapper posts">

		<?php 
			$containerid = 'rh_woocolumn_' . mt_rand();  
			$infinitescrollwrap = ' re_aj_pag_clk_wrap';    
			$show = $ajaxoffset = 8;	
			$columns = '4_col';
			$additional_vars = array();
			$additional_vars['columns'] = $columns;
			$args = array(
				'post_type' => 'product',
				'posts_per_page' => 8,
				'author' => $creator,
				);
		    $loop = new WP_Query($args);
		?>
		<?php if ( $loop->have_posts() ) : ?>
			<?php 
				$jsonargs = json_encode($args);
				$json_innerargs = json_encode($additional_vars);
			?> 
			<div class="woocommerce">
			<div class="column_woo products col_wrap_fourth <?php  echo esc_attr($infinitescrollwrap);?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="woocolumnpart" data-innerargs='<?php echo ''.$json_innerargs.'';?>' id="<?php echo esc_attr($containerid);?>">

				<?php while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
					<?php include(rh_locate_template('inc/parts/woocolumnpart.php')); ?>
				<?php endwhile; ?>
				<?php wp_enqueue_script('rhajaxpagination');?>
				<div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      

			</div>
			</div>
			<div class="clearfix"></div>
		<?php endif; wp_reset_query(); ?>

	</div><!--/.posts-->	
<?php endif;?>