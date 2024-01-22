<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: Deal Day Widget
 */

add_action( 'widgets_init', 'rehub_deal_daywoo_load_widget' );

function rehub_deal_daywoo_load_widget() {
	register_widget( 'rehub_deal_daywoo_widget' );
}

class rehub_deal_daywoo_widget extends WP_Widget {

    function __construct() {
        $widget_ops = array( 'classname' => 'deal_daywoo woocommerce', 'description' => esc_html__('Displays "deal of the day" (WC Product or Post Offers). Use only in sidebar!', 'rehub-framework') );
        $control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rehub_deal_daywoo' );
        parent::__construct( 'rehub_deal_daywoo', esc_html__( 'ReHub: Deal of day', 'rehub-framework' ), $widget_ops, $control_ops );
    }

/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {
	extract( $args );

	/* Our variables from the widget settings. */
	$title = isset($instance['title']) ? apply_filters( 'widget_title', $instance['title'] ) : '';
	$dealtype = ( !empty( $instance['dealtype'] ) ) ? $instance['dealtype'] : 'product';
	$dealid = (!empty($instance['dealid'])) ? $instance['dealid'] : '';
	$faketimer = (!empty($instance['faketimer'])) ? $instance['faketimer'] : '';
	$fakebar = (!empty($instance['fakebar'])) ? $instance['fakebar'] : '';
	$fakebar_sold = (!empty($instance['fakebar_sold'])) ? $instance['fakebar_sold'] : 12;
	$fakebar_stock = (!empty($instance['fakebar_stock'])) ? $instance['fakebar_stock'] : 16;
	$markettext = ( !empty( $instance['markettext'] ) ) ? $instance['markettext'] : '';
	$carousel = ( !empty( $instance['carousel'] ) ) ? $instance['carousel'] : '';
	$autorotate = ( !empty( $instance['autorotate'] ) ) ? $instance['autorotate'] : '';
	$carouselnumber = ( !empty( $instance['carouselnumber'] ) ) ? $instance['carouselnumber'] : 3;

	if ( $dealid ) {
		$dealidarray = array_map( 'trim', explode( ",", $dealid ) );
		$query = array( 
			'post_status' => 'publish', 
			'ignore_sticky_posts' => 1, 
			'post_type' => $dealtype, 
			'no_found_rows'=>1
		);
		if($carousel){
			$query['post__in'] = $dealidarray;
		}else{
			$query['post__in'] = array_slice($dealidarray, 0, 1);
		}
	} else {
        $query = array(
            'posts_per_page'=>'1',
            'post_type'=> $dealtype, 
            'ignore_sticky_posts' => 1,            
        );
        if($carousel){
        	$query['posts_per_page'] = $carouselnumber;
        }else{
        	$query['posts_per_page'] = '1';
        }
		
		$meta_query = $tax_query = array();
		
		if ( 'product' == $dealtype && class_exists('Woocommerce')) {
			$product_ids_on_sale = wc_get_product_ids_on_sale();
			$tax_query[] = WC()->query->get_tax_query();
		} else {
			$product_ids_on_sale = rh_get_post_ids_on_sale();
		}

	    $query['tax_query'] = $tax_query;
	    $query['post__in'] = array_merge( array( 0 ), $product_ids_on_sale );
	    $query['no_found_rows'] = 1;     	
	}
	$autodata = ($autorotate) ? 'data-auto="1"' : 'data-auto="0"' ;

	$loop = new WP_Query( $query );
	
	/* Before widget (defined by themes). */
	echo ''.$before_widget; $index = 0;
	echo '<div class="deal_daywoo woocommerce position-relative custom-nav-car flowhidden">';
	echo '<style scoped>
		.deal_daywoo .price{ color: #489c08; font-weight: bold;font-size: 22px; line-height: 18px }
		.deal_daywoo figure a{min-height: 250px}
		.deal_daywoo figure img{width: auto !important;}
		.sidebar .deal_daywoo figure img{max-height: 250px !important;}
		.sidebar .deal_daywoo figure a{max-height: 250px !important}
		body .deal_daywoo .title:after{display: none;}
		.deal_daywoo h3, .woo_feat_slider h3{font-size: 18px}
		.deal_daywoo .wpsm-bar-bar, .deal_daywoo .wpsm-bar, .deal_daywoo .wpsm-bar-percent{height: 20px; line-height: 20px}
		</style>
	';
	if ( $loop->have_posts() ) :
		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) : ?><div class="title"><?php echo ''.$title; ?><?php if ($carousel) :?><span class="greycolor font120 cursorpointer mr5 ml5 cus-car-next floatright"><span class="rhicon rhi-arrow-square-right"></span></span><span class="greycolor font120 cursorpointer mr5 ml5 cus-car-prev floatright"><span class="rhicon rhi-arrow-square-left"></span></span><?php endif; ?></div><?php endif; ?>
		<?php if ($carousel) :?>
			<?php wp_enqueue_style('rhcarousel');wp_enqueue_script('owlcarousel'); wp_enqueue_script('owlinit'); ?>
			<div class="loading woo_carousel_block mb0"><div class="woodealcarousel re_carousel" data-showrow="1" <?php echo ''.$autodata;?> data-laizy="0" data-fullrow="3" data-navdisable="1">
		<?php endif; ?>
		<?php while( $loop->have_posts() ) : $loop->the_post(); $index++; ?>
			<div class="woo_spec_widget">
			<?php $post_id = $loop->post->ID; ?>
				<?php if ( 'product' == $dealtype && class_exists('woocommerce')):?>
					<?php $_product = wc_get_product( $post_id ); ?>
					<?php 
						$target_blank = ( $_product->get_type() == 'external' ) ? ' target="_blank" rel="nofollow sponsored"' : '' ;
						$product_link = ( $_product->get_type() == 'external' ) ? $_product->add_to_cart_url() : get_the_permalink($post_id);
						$offer_coupon_date = get_post_meta( $post_id, 'rehub_woo_coupon_date', true );
					?>
				    <figure class="position-relative">
						<?php
							if ( $_product->is_featured() ) :
								echo apply_filters( 'woocommerce_featured_flash', '<span class="onfeatured">' . esc_html__( 'Featured!', 'rehub-framework' ) . '</span>', $loop->post, $_product );
							endif; 
							if ( $_product->is_on_sale() ) : 
								$percentage = 0;
								$featured = ( $_product->is_featured() ) ? ' onsalefeatured' : '';
								if ( $_product->get_regular_price() ) {
									$percentage = round( ( ( $_product->get_regular_price() - $_product->get_price() ) / $_product->get_regular_price() ) * 100 );
								}
								if ( $percentage && $percentage > 0 && !$_product->is_type( 'variable' ) ) {
									$sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="onsale'. $featured .'"><span>- ' . $percentage . '%</span></span>', $loop->post, $_product );
								} else {
									$sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="onsale'. $featured .'">' . esc_html__( 'Sale!', 'rehub-framework' ) . '</span>', $loop->post, $_product );
								}
								echo ''.$sales_html;
							endif; 
						?>
						<a class="img-centered-flex rh-flex-center-align rh-flex-justify-center" href="<?php echo esc_url($product_link) ; ?>"<?php echo ''.$target_blank;?>>
							<?php echo WPSM_image_resizer::show_wp_image('woocommerce_single'); ?>
				        </a>          
				    </figure>
				    <div class="dealdaycont">
					    <div class="woo_loop_desc"> 
					    	<h3>      
						        <a class="<?php echo getHotIconclass($post_id); ?>" href="<?php echo esc_url($product_link) ;?>"<?php echo ''.$target_blank; ?>>
						            	<?php echo rh_expired_or_not($post_id, 'span');?>     
						            	<?php the_title();?>
						        </a>
					        </h3>		         
					        <?php do_action( 'rehub_vendor_show_action' ); ?>            
					    </div>	
				        <div class="woo_spec_price">
							<?php wc_get_template( 'loop/price.php' ); ?>
				        </div>
			        	<?php // stock wpsm_bar
						if ($fakebar) {
							$stock_sold = $fakebar_sold; 
							$stock_available = $fakebar_stock;
							if($index > 1){
								$stock_sold = $stock_sold + ($index * 3);
								$stock_available = $stock_available + ($index * 5);
							}
						} else {
							$stock_sold = ( $total_sales = get_post_meta( get_the_ID(), 'total_sales', true ) ) ? round( $total_sales ) : 0;
							$stock_available = ( $stock = get_post_meta( get_the_ID(), '_stock', true ) ) ? round( $stock ) : 0;			
						} 
						$percentage = ( $stock_available > 0 ) ? round( $stock_sold / $stock_available * 100 ) : '';	
						?>
						
			        	<?php if ( !empty($percentage) ) : ?>        
					        <div class="woo_spec_bar mt30 mb20">
					        	<div class="deal-stock mb10">
					        	<span class="stock-sold floatleft">
					        		<?php esc_html_e( 'Already Sold:', 'rehub-framework' );?> <strong><?php echo esc_html( $stock_sold ); ?></strong>
					        	</span>
					        	<span class="stock-available floatright">
					        		<?php esc_html_e( 'Available:', 'rehub-framework' );?> <strong><?php echo esc_html( $stock_available ); ?></strong>
					        	</span>
					        	</div>
					        	<?php if ( $percentage == 0 ) { $percentage = 10; }?>
					        	<?php echo wpsm_bar_shortcode(array('percentage'=>$percentage));?>
					        </div>	 
				        <?php endif;?>
						<div class="marketing-text mt15 mb15"><?php echo esc_attr($markettext); ?></div>	
			        	<?php if( $faketimer ) : ?>
			        		<?php 
			        			$currenttime = current_time('mysql',0);
			        			$now = new DateTime($currenttime);

			        			$now->modify( '+'.$index.' day' );
			   					$month = $now->format( 'm' );
			   					$year = $now->format( 'Y' );
			   					$day = $now->format( 'd' );
								$hour  = $now->format( 'H' );
								$minute  = $now->format( 'i' ); 
			   				?>
			        	<?php else:?>
				        	<?php 
				        		$sale_price_dates_to = ( $date = get_post_meta( get_the_ID(), '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : $offer_coupon_date;
				        		if ( $sale_price_dates_to ) {
									$timestamp1 = strtotime($sale_price_dates_to);
									if(strpos($sale_price_dates_to, ':') ===false){
										$timestamp1 += 86399;
									}
									$year = date('Y',$timestamp1);
									$month = date('m',$timestamp1);
									$day  = date('d',$timestamp1); 
									$hour  = date('H',$timestamp1); 
									$minute  = date('i',$timestamp1); 
				        		} else {
				        			$year = '';
				        		}
							?>	        	
			        	<?php endif;?>
			        	<?php if( $year ) : ?>
			        		<div class="woo_spec_timer<?php echo (rehub_option('width_layout') =='extended') ? ' gridcountdown' : '';?>">
								<?php echo wpsm_countdown(array('year'=> $year, 'month'=>$month, 'day'=>$day, 'minute'=>$minute, 'hour'=>$hour));?>
			        		</div>
			        	<?php endif;?>
		        	</div>								        			        				    			    									
				<?php else:?>
					<?php 
						$offer_price_old = get_post_meta( $post_id, 'rehub_offer_product_price_old', true );
						$offer_price = get_post_meta( $post_id, 'rehub_offer_product_price', true );
						$offer_coupon_date = get_post_meta( $post_id, 'rehub_offer_coupon_date', true );			
						$target_blank = '';
						$product_link = get_the_permalink( $post_id );
					?>	
				    <figure class="position-relative">
						<?php
							echo re_badge_create('tablelabel');
						?>
						<a class="img-centered-flex rh-flex-center-align rh-flex-justify-center" href="<?php echo esc_url($product_link) ; ?>"<?php echo ''.$target_blank;?>>
				            <?php 
				            $showimg = new WPSM_image_resizer();
				            $showimg->use_thumb = true; 
				            $showimg->width = '300';
				            $showimg->height = '300';                                                
				            $showimg->show_resized_image(); 
							?>
				        </a>         
				    </figure>
				    <div class="dealdaycont">
					    <div class="woo_loop_desc"> 
					    	<h3>      
						        <a class="<?php echo getHotIconclass($post_id); ?>" href="<?php echo esc_url($product_link) ;?>"<?php echo ''.$target_blank; ?>>
						            	<?php echo rh_expired_or_not($post_id, 'span');?>     
						            	<?php the_title();?>
						        </a>
					        </h3>		         
					        <?php do_action( 'rehub_vendor_show_action' ); ?>            
					    </div>	
				        <div class="woo_spec_price">
							<span class="price">
								<del><?php if ( ! empty( $offer_price_old ) ) echo ''.$offer_price_old; ?></del> 
								<ins><?php echo ''.$offer_price; ?></ins>
							</span>
				        </div>
			        	<?php // stock wpsm_bar
						if ( $fakebar ) {
							$stock_sold = $fakebar_sold; 
							$stock_available = $fakebar_stock;
							if($index > 1){
								$stock_sold = $stock_sold + ($index * 3);
								$stock_available = $stock_available + ($index * 5);
							}							
						} else {
							$stock_sold = ( $total_sales = get_post_meta( get_the_ID(), 'total_sales', true ) ) ? round( $total_sales ) : 0;
							$stock_available = ( $stock = get_post_meta( get_the_ID(), '_stock', true ) ) ? round( $stock ) : 0;			
						} 
						$percentage = ( (int)$stock_available > 0 ) ? round( $stock_sold / $stock_available * 100 ) : '';	
						?>
						
			        	<?php if ( !empty($percentage) ) : ?>        
					        <div class="woo_spec_bar mt30 mb20">
					        	<div class="deal-stock mb10">
					        	<span class="stock-sold floatleft">
					        		<?php esc_html_e( 'Already Sold:', 'rehub-framework' );?> <strong><?php echo esc_html( $stock_sold ); ?></strong>
					        	</span>
					        	<span class="stock-available floatright">
					        		<?php esc_html_e( 'Available:', 'rehub-framework' );?> <strong><?php echo esc_html( $stock_available ); ?></strong>
					        	</span>
					        	</div>
					        	<?php if ( $percentage == 0 ) { $percentage = 10; }?>
					        	<?php echo wpsm_bar_shortcode(array('percentage'=>$percentage));?>
					        </div>	 
				        <?php endif;?>
				        <div class="marketing-text mt15 mb15"><?php echo esc_attr($markettext); ?></div>
			        	<?php if( $faketimer ) : ?>
			        		<?php 
			        			$currenttime = current_time('mysql',0);
			        			$now = new DateTime($currenttime);
			        			$now->modify( 'tomorrow' );
			   					$month = $now->format( 'm' );
			   					$year = $now->format( 'Y' );
			   					$day = $now->format( 'd' );
								$hour  = $now->format( 'H' );
								$minute  = $now->format( 'i' ); 
			   				?>
			        	<?php else:?>
				        	<?php 
				        		if ( $offer_coupon_date ) {
									$timestamp1 = strtotime($offer_coupon_date);
									if(strpos($offer_coupon_date, ':') ===false){
										$timestamp1 += 86399;
									}
									$year = date('Y',$timestamp1);
									$month = date('m',$timestamp1);
									$day  = date('d',$timestamp1); 
									$hour  = date('H',$timestamp1); 
									$minute  = date('i',$timestamp1); 
				        		} else {
				        			$year = '';
				        		}
							?>	        	
			        	<?php endif;?>
			        	<?php if( $year ) : ?>
			        		<div class="woo_spec_timer<?php echo (rehub_option('width_layout') =='extended') ? ' gridcountdown' : '';?>">
			        			<?php echo wpsm_countdown(array('year'=> $year, 'month'=>$month, 'day'=>$day, 'minute'=>$minute, 'hour'=>$hour));?>
			        		</div>
			        	<?php endif;?>
		        	</div>				        				        

				<?php endif;?>
			</div>
		<?php endwhile; ?>
		<?php if ($carousel) :?></div></div><?php endif; ?>
	<?php else: ?>
		<?php esc_html_e( 'No products for this criteria.', 'rehub-framework' );  ?>
	<?php endif; ?>
	<?php wp_reset_postdata();

	/* After widget (defined by themes). */
	echo '</div>';
	echo ''.$after_widget;
}


	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['dealtype'] = strip_tags( $new_instance['dealtype'] );
		$instance['faketimer'] = ( isset( $new_instance['faketimer'] ) ) ? strip_tags( $new_instance['faketimer'] ) : '';
		$instance['fakebar'] = ( isset( $new_instance['fakebar'] ) ) ? strip_tags( $new_instance['fakebar'] ) : '';
		$instance['fakebar_sold'] = (int)( $new_instance['fakebar_sold'] );
		$instance['fakebar_stock'] = (int)( $new_instance['fakebar_stock'] );
		$instance['markettext'] = strip_tags( $new_instance['markettext'] );
		$instance['dealid'] = strip_tags( $new_instance['dealid'] );
		$instance['carousel'] = ( isset( $new_instance['carousel'] ) ) ? strip_tags( $new_instance['carousel'] ) : '';
		$instance['autorotate'] = ( isset( $new_instance['autorotate'] ) ) ? strip_tags( $new_instance['autorotate'] ) : '';
		$instance['carouselnumber'] = strip_tags( $new_instance['carouselnumber'] );				

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 
			'title' => esc_html__( 'Deal of the day', 'rehub-framework' ),
			'dealtype' => 'product', 
			'dealid' => '', 
			'faketimer' => '', 
			'fakebar' => '', 
			'fakebar_sold' => 12,
			'fakebar_stock' => 16,
			'markettext' => esc_html__( 'Hurry Up! Offer ends soon.', 'rehub-framework' ),
			'carousel' => '',
			'autorotate' => '',
			'carouselnumber' => '3',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title of widget:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'title' ); ?>" name="<?php echo ''.$this->get_field_name( 'title' ); ?>" value="<?php echo ''.$instance['title']; ?>"  />
		</p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'dealtype' ); ?>"><?php esc_html_e('Post type:', 'rehub-framework'); ?></label>
			<select id="<?php echo ''.$this->get_field_id( 'dealtype' ); ?>" name="<?php echo ''.$this->get_field_name( 'dealtype' ); ?>" style="width:100%;">
				<option value="product" <?php if ( 'product' == $instance['dealtype'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Product', 'rehub-framework'); ?></option>
				<option value="post" <?php if ( 'post' == $instance['dealtype'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Post', 'rehub-framework'); ?></option>
			</select>
		</p>
		
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'dealid' ); ?>"><?php esc_html_e('Ids of product to show:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'dealid' ); ?>" name="<?php echo ''.$this->get_field_name( 'dealid' ); ?>" value="<?php echo ''.$instance['dealid']; ?>" size="3" />
			<small><?php esc_html_e('By default, widget shows latest product which is on sale, you can specify product ID to overwrite this. You can set several ids if you enable carousel. Divide them by commas', 'rehub-framework'); ?></small>
		</p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'faketimer' ); ?>"><?php esc_html_e('Set fake timer:', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'faketimer' ); ?>" name="<?php echo ''.$this->get_field_name( 'faketimer' ); ?>" value="true" <?php if( $instance['faketimer'] ) echo 'checked="checked"'; ?> type="checkbox" />
			<br /><small><?php esc_html_e('By default, widget shows countdown base on Sale price dates of product. You can enable fake timer (always shows 12 hours)', 'rehub-framework'); ?></small>
		</p>						

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'markettext' ); ?>"><?php esc_html_e('Add marketing text:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'markettext' ); ?>" name="<?php echo ''.$this->get_field_name( 'markettext' ); ?>" value="<?php echo ''.$instance['markettext']; ?>"  />
		</p>		

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'fakebar' ); ?>"><?php esc_html_e('Set fake sold bar:', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'fakebar' ); ?>" name="<?php echo ''.$this->get_field_name( 'fakebar' ); ?>" value="true" <?php if( $instance['fakebar'] ) echo 'checked="checked"'; ?> type="checkbox" />
			<br>
			<label for="<?php echo ''.$this->get_field_id( 'fakebar_sold' ); ?>"><?php esc_html_e('Sold:', 'rehub-framework'); ?></label>
			<input class="tiny-text" id="<?php echo ''.$this->get_field_id( 'fakebar_sold' ); ?>" name="<?php echo ''.$this->get_field_name( 'fakebar_sold' ); ?>" type="number" step="1" min="1" value="<?php echo ''.$instance['fakebar_sold']; ?>" size="3">
			<label for="<?php echo ''.$this->get_field_id( 'fakebar_stock' ); ?>"><?php esc_html_e('In Stock:', 'rehub-framework'); ?></label>
			<input class="tiny-text" id="<?php echo ''.$this->get_field_id( 'fakebar_stock' ); ?>" name="<?php echo ''.$this->get_field_name( 'fakebar_stock' ); ?>" type="number" step="1" min="1" value="<?php echo ''.$instance['fakebar_stock']; ?>" size="3">
			<br /><small><?php esc_html_e('By default, widget shows real progress bar based on stock status, you can enable fake bar', 'rehub-framework'); ?></small>
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'carousel' ); ?>"><?php esc_html_e('Enable carousel?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'carousel' ); ?>" name="<?php echo ''.$this->get_field_name( 'carousel' ); ?>" value="true" <?php if( $instance['carousel'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'autorotate' ); ?>"><?php esc_html_e('Enable Auto rotation?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'autorotate' ); ?>" name="<?php echo ''.$this->get_field_name( 'autorotate' ); ?>" value="true" <?php if( $instance['autorotate'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'carouselnumber' ); ?>"><?php esc_html_e('Number of posts in carousel', 'rehub-framework'); ?></label>
			<input  type="number" class="widefat" id="<?php echo ''.$this->get_field_id( 'carouselnumber' ); ?>" name="<?php echo ''.$this->get_field_name( 'carouselnumber' ); ?>" value="<?php echo ''.$instance['carouselnumber']; ?>"  />
		</p>		
	<?php
	}
}
?>