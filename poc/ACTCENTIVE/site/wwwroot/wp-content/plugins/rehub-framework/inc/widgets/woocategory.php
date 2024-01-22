<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: News Widget
 */

add_action( 'widgets_init', 'rehub_better_woocat_load_widget' );

function rehub_better_woocat_load_widget() {
	register_widget( 'rehub_better_woocat_widget' );
}

class rehub_better_woocat_widget extends WP_Widget {

    function __construct() {
        $widget_ops = array( 'classname' => 'better_woocat padd20 whitebg border-lightgrey-double', 'description' => esc_html__('Better categories. Use only in sidebar!', 'rehub-framework') );
        $control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rehub_better_woocat' );
        parent::__construct('rehub_better_woocat', esc_html__('ReHub: Better categories', 'rehub-framework'), $widget_ops, $control_ops);
    }

/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {

	/* Our variables from the widget settings. */
	$hideempty = (!empty($instance['hideempty'])) ? $instance['hideempty'] : '';
	$woocount = (!empty($instance['woocount'])) ? $instance['woocount'] : '';
	$showAllLabel = (!empty($instance['showAllLabel'])) ? esc_html($instance['showAllLabel']) : esc_html__( 'Show All Categories', 'rehub-framework' );
	$browseAllLabel = (!empty($instance['browseAllLabel'])) ? esc_html($instance['browseAllLabel']) : esc_html__( 'Browse Categories', 'rehub-framework' );
	$post_type = (!empty($instance['post_type'])) ? $instance['post_type'] : 'woo' ;

	$el_class = '';		
	global $wp_query, $post;

	if($post_type == 'post'){
		$curtaxonomy = 'category';
		$cursingular = 'post';
	}
	elseif($post_type == 'blog'){
		$curtaxonomy = 'blog_category';
		$cursingular = 'blog';
	}
	else{
		$curtaxonomy = 'product_cat';
		$cursingular = 'product';
	}

	$list_args	= array(
		'show_count' => $woocount,
		'taxonomy' => $curtaxonomy,
		'orderby' => 'id',
		'echo' => false,
		'hide_empty' => $hideempty
	);

	$current_category   = false;
	$current_parent_category = false;

	if ( is_tax( $curtaxonomy) || ($post_type == 'post' && is_category())) {

		$current_category   = $wp_query->queried_object;
		$current_parent_category = $current_category->parent;



	} elseif ( is_singular( $cursingular ) ) {

		$current_page_id = $wp_query->get_queried_object_id();
		if($post_type == 'post' || $post_type == 'blog'){
			$product_category = wp_get_post_terms( $current_page_id, $curtaxonomy, array( 'orderby' => 'parent' ) );
		}else{
			$product_category = wc_get_product_terms( $current_page_id, 'product_cat', array( 'orderby' => 'parent' ) );
		}
		

		if ( $product_category ) {
			$current_category   = end( $product_category );
			$current_parent_category = $current_category->parent;
		}

	}

	if ( $current_category ) {

		$el_class = 'category-single';

		// Top level is needed
		$top_level = wp_list_categories( array(
			'title_li'     => sprintf( '<span class="show-all-toggle blockstyle border-grey-bottom cursorpointer pb15">%1$s</span>', $showAllLabel ),
			'taxonomy'     => $curtaxonomy,
			'parent'       => 0,
			'hierarchical' => true,
			'hide_empty'   => false,
			'exclude'      => $current_category->term_id,
			'show_count'   => $woocount,
			'hide_empty'   => $hideempty,
			'echo'         => false,
			'use_desc_for_title' => false
		) );

		$list_args['title_li'] = '<ul class="show-all-cat closed-woo-catlist">' . $top_level . '</ul>';

		// Direct children are wanted
		$direct_children = get_terms(
			$curtaxonomy,
			array(
				'fields'       => 'ids',
				'child_of'     => $current_category->term_id,
				'hierarchical' => true,
				'hide_empty'   => false
			)
		);

		$siblings = array();
		if( $current_parent_category ) {
			// Siblings are wanted
			$siblings = get_terms(
				$curtaxonomy,
				array(
					'fields'       => 'ids',
					'child_of'     => $current_parent_category,
					'hierarchical' => true,
					'hide_empty'   => false
				)
			);
		}

		$include = array_merge( array( $current_category->term_id, $current_parent_category ), $direct_children, $siblings );

		$list_args['include']     = implode( ',', $include );
		$list_args['depth']       = 3;

		if ( empty( $include ) ) {
			return;
		}

	} else {
		$list_args['title_li']         = sprintf( '<span class="blockstyle border-grey-bottom browse-categories-label pb20 fontbold">%1$s:</span>', $browseAllLabel );
		$list_args['depth']            = 2;
		$list_args['child_of']         = 0;
		$list_args['hierarchical']     = 1;
	}

	$list_args['pad_counts']                 = 1;
	$list_args['show_option_none']           = esc_html__('No product categories exist.', 'rehub-framework' );
	$list_args['current_category']           = ( $current_category ) ? $current_category->term_id : '';
	$list_args['use_desc_for_title'] = false;

	wp_enqueue_script('rhbettercategory', get_template_directory_uri() . '/js/bettercategory.js');
	echo ''.$args['before_widget'];

	echo '<style scoped>
		.widget.better_woocat ul li:first-child, .better_woocat .category-single .show-all-cat>li, .widget.better_woocat .category-single>li {border: none;}
		.widget.better_woocat ul{margin: 0}
		.widget.better_woocat ul li {border-top: 1px solid #ddd; list-style: none; margin:0;}
		.widget.better_woocat ul li>a {color: #333;padding: 12px 0;display: inline-block;} 
		.widget.better_woocat ul li ul.children{padding-left: 20px}
		.widget.better_woocat ul li ul.children li{font-size: 90%}
		.widget.better_woocat .category-single>li>ul:last-child li .children li:first-child {border-top: 1px solid #ddd;}
		.widget.better_woocat li .count{font-size: 90%; opacity: 0.7}
		.widget.better_woocat li.current-cat > a{font-weight: bold;}
		.closed-woo-catlist ul{display: none;}
	</style>
	';

	$output = wp_list_categories( $list_args );
	$output = str_replace('</a> (', '</a> <span class="count">(', $output);
	$output = str_replace(')', ')</span>', $output);

	echo '<ul class="product-categories ' . esc_attr( $el_class ) . '">';

	echo ''.$output;

	echo '</ul>';

	echo ''.$args['after_widget'];
}


	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['showAllLabel'] = strip_tags( $new_instance['showAllLabel'] );
		$instance['hideempty'] = (!empty($new_instance['hideempty'])) ? strip_tags($new_instance['hideempty']) : '';
		$instance['woocount'] = (!empty($new_instance['woocount'])) ? strip_tags($new_instance['woocount']) : '';
		$instance['browseAllLabel'] = strip_tags($new_instance['browseAllLabel']);
		$instance['post_type'] = $new_instance['post_type'];

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'showAllLabel' => 'Show all categories', 'hideempty' => '','woocount' => '','browseAllLabel' => 'Browse Categories','post_type' => 'woo');
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
		<label for="<?php echo ''.$this->get_field_id('post_type'); ?>"><?php esc_html_e('Widget is based on:', 'rehub-framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('post_type'); ?>" name="<?php echo ''.$this->get_field_name('post_type'); ?>" style="width:100%;">
			<option value="woo" <?php if ( 'woo' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Woocommerce', 'rehub-framework');?></option>	
			<option value="post" <?php if ( 'post' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Posts', 'rehub-framework');?></option>	
			<option value="blog" <?php if ( 'blog' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Blog posts', 'rehub-framework');?></option>		
		</select>
		</p>		

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'showAllLabel' ); ?>"><?php esc_html_e('Label for show all category text:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'showAllLabel' ); ?>" name="<?php echo ''.$this->get_field_name( 'showAllLabel' ); ?>" value="<?php echo ''.$instance['showAllLabel']; ?>"  />
		</p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'browseAllLabel' ); ?>"><?php esc_html_e('Label for browse categories text:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'browseAllLabel' ); ?>" name="<?php echo ''.$this->get_field_name( 'browseAllLabel' ); ?>" value="<?php echo ''.$instance['browseAllLabel']; ?>"  />
		</p>		

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'hideempty' ); ?>"><?php esc_html_e('Hide empty categories', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'hideempty' ); ?>" name="<?php echo ''.$this->get_field_name( 'hideempty' ); ?>" value="true" <?php if( $instance['hideempty'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>		

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'woocount' ); ?>"><?php esc_html_e('Show count of products', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'woocount' ); ?>" name="<?php echo ''.$this->get_field_name( 'woocount' ); ?>" value="true" <?php if( $instance['woocount'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>			

	<?php
	}
}

?>