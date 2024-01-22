<?php
/**
 * The WooCommerce Attribute Groups class
 */

class REHub_WC_Group_Attributes {

	public $version;
	
	/*  */
	public function __construct( $version ) {
		$this->version = $version;
	}

	/*  */
	public function init() {
		$this->register_attribute_group();
		$this->register_attribute_group_taxonomy();
		$this->init_hooks();
	}
	
	/*  */
	private function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'manage_attribute_group_posts_columns', array( $this, 'columns_head' ) );
		add_action( 'manage_attribute_group_posts_custom_column', array( $this, 'columns_content' ) );
		add_action( 'woocommerce_product_options_attributes', array( $this, 'show_attribute_group_toolbar' ) );
		add_action( 'wp_ajax_get_attributes_by_attribute_group_id', array( $this, 'get_attributes_by_attribute_group_id' ) );
		add_action( 'pre_get_posts', array( $this, 'attribute_group_order' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_custom_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_custom_metaboxes' ) );
		// Enable Attribute Groups frontend
		add_filter( 'wc_get_template', array($this, 'modify_attribute_template'), 10, 2 );
	}

	/*  */
	public function register_attribute_group() {
		$labels = array(
			'name'                => __( 'Attribute Groups', 'rehub-framework' ),
			'singular_name'       => __( 'Attribute Group', 'rehub-framework' ),
			'add_new'             => __( 'Add New Attribute Group', 'rehub-framework' ),
			'add_new_item'        => __( 'Add New Attribute Group', 'rehub-framework' ),
			'edit_item'           => __( 'Edit Attribute Group', 'rehub-framework' ),
			'new_item'            => __( 'New Attribute Group', 'rehub-framework' ),
			'view_item'           => __( 'View Attribute Group', 'rehub-framework' ),
			'search_items'        => __( 'Search Attribute Groups', 'rehub-framework' ),
			'not_found'           => __( 'No Attribute Groups found', 'rehub-framework' ),
			'not_found_in_trash'  => __( 'No Attribute Groups found in Trash', 'rehub-framework' ),
			'parent_item_colon'   => __( 'Parent Attribute Group:', 'rehub-framework' ),
			'menu_name'           => __( 'Attribute Groups', 'rehub-framework' ),
		);

		$args = array(
	      'public' => false,
	      'labels' => $labels,
	      'show_ui' => true,
	      'supports' => array('title'),
	      'show_in_menu' => 'edit.php?post_type=product',
	      'supports' => array('title', 'page-attributes'),
	      'hierarchical' => false,	      
	    );

	    register_post_type( 'attribute_group', $args );
	}

	/*  */
	public function register_attribute_group_taxonomy() {

        $singular = __('Attribute Group Category', 'rehub-framework');
        $plural = __('Attribute Group Categories', 'rehub-framework');

        $labels = array(
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => sprintf(__('Search %s', 'rehub-framework'), $plural),
            'all_items' => __('All Categories', 'rehub-framework'),
            'parent_item' => sprintf(__('Parent %s', 'rehub-framework'), $singular),
            'parent_item_colon' => sprintf(__('Parent %s:', 'rehub-framework'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'rehub-framework'), $singular),
            'update_item' => sprintf(__('Update %s', 'rehub-framework'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'rehub-framework'), $singular),
            'new_item_name' => sprintf(__('New %s Name', 'rehub-framework'), $singular),
            'menu_name' => $plural,
        );

        $args = array(
                'labels' => $labels,
                'public' => false,
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
        );

        register_taxonomy('attribute_group_categories', 'attribute_group', $args);
	}

	/*  */
    public function enqueue_styles() {
		
        $screen = get_current_screen();
        if ( $screen->post_type != 'attribute_group' ) {
            return;
        }
		
		wp_enqueue_style( 'rehub-framework-select2', RH_FRAMEWORK_URL .'/assets/css/select2.min.css', array(), '3.5.4', 'all' );
        wp_enqueue_style( 'rehub-framework-select2-sortable', RH_FRAMEWORK_URL .'/assets/css/select2.sortable.min.css', array(), $this->version, 'all' );
    }

	/*  */
    public function enqueue_scripts() {
		wp_enqueue_script('woo-group-attributes-admin', RH_FRAMEWORK_URL .'/assets/js/woo-group-attributes-admin.js', array('jquery'), $this->version, true);

        $screen = get_current_screen();
        if ( $screen->post_type != 'attribute_group' ) {
            return;
        }

        wp_enqueue_script( 'rehub-framework-select2', RH_FRAMEWORK_URL .'/assets/js/select2.min.js', array('jquery'), '3.5.4', true );
        wp_enqueue_script( 'rehub-framework-select2-sortable', RH_FRAMEWORK_URL .'/assets/js/select2.sortable.min.js', array('jquery'), $this->version, true );
        wp_enqueue_script( 'rehub-framework-html5-sortable', RH_FRAMEWORK_URL .'/assets/js/html.sortable.min.js', array('jquery'), $this->version, true );
    }

	/*  */
	public function columns_head( $columns ) {
		$output = array();
		$columns['menu_order'] = 'Order';
		
		foreach( $columns as $column => $name ){
			$output[$column] = $name;
			if( $column === 'title' ){
				$output['attributes'] = esc_html__( 'Attributes', 'rehub-framework' );
			}
		}
		
		return $output;
	}

	/*  */
	public function columns_content( $column_name ) {
		global $post;

		if($column_name == 'menu_order'){
	      	$order = $post->menu_order;
     		echo $order;
		}

		if($column_name !== 'attributes'){
			return;
		}
		
		$argss = array('type' =>'select_advanced', 'multiple' => true);
		$attribute_groups = get_post_meta($post->ID, 'woocommerce_group_attributes_attributes');
		
		if(isset($attribute_groups[0]) && is_array($attribute_groups[0])) {
			$attribute_groups = $attribute_groups[0];
		} else {
			$attribute_groups = $attribute_groups;
		}
		
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		foreach( $attribute_groups as $attribute_group ){
			$id = $attribute_group;
			$name = "";
			
			foreach ( $attribute_taxonomies as $key => $value ) {
				if($value->attribute_id == $id) {
					$name = $value->attribute_label;
				}
			}
			
			echo "<strong>" . $name .'</strong></br>';
		}
	}

	/*  */
	public function attribute_group_order($query) {
		if('attribute_group' != $query->get( 'post_type' )) {
			return false;
		}
 		$query->set( 'orderby', 'menu_order');
	}

    /**
     * Add custom ticket metaboxes
     */
    public function add_custom_metaboxes() {
        add_meta_box('woocommerce_group_attributes_metabox', esc_html__( 'Attributes', 'rehub-framework' ), array($this, 'attributes'), 'attribute_group', 'normal', 'high');
    }

    /**
     * Display Metabox Short Information
     */
    public function attributes() {
        global $post;

        wp_nonce_field(basename(__FILE__), 'woocommerce_group_attributes_meta_nonce');

        $prefix = 'woocommerce_group_attributes_';

		/* $image = get_post_meta($post->ID, $prefix .'image', true); */
        $attributes = get_post_meta($post->ID, $prefix .'attributes');

        if(isset($attributes[0]) && !empty($attributes[0])) {
        	$attributes = $attributes[0];
        } else {
        	$attributes = array();
        }

        $possibleAttributes = wc_get_attribute_taxonomies();
		/* echo '<label for="'. $prefix .'attributes">'. esc_html__( 'Attributes', 'rehub-framework' ) .':</label><br/>'; */
        $order = "";
		
        if( !empty( $attributes ) ) {
        	$order = 'data-order="'. implode( ',', $attributes ) .'"';
        }

        echo '<select name="'. $prefix .'attributes[]" multiple="multiple" style="height: 100%;" '. $order .' size=30>';

        foreach ( $possibleAttributes as $possibleAttribute ) {
        	$selected = "";
        	if( !empty( $attributes ) ) {
        		foreach ( $attributes as $attribute ) {
        			echo $attribute;
        			if( $attribute == $possibleAttribute->attribute_id ) {
        				$selected = 'selected="selected"';
        			}
        		}
        	}
        	echo '<option '. $selected .'value="'. $possibleAttribute->attribute_id .'">'. $possibleAttribute->attribute_label .'</option>';
        }
        echo '</select>';
		
        // Icon of the Group
		/* 		
		echo '<br/><br/><label for="'. $prefix .'image">Image:</label><br/>';
		echo '<input name="'. $prefix .'image" value="'. $image .'" type="url">'; 
		*/
    }

    /**
     * Save Custom Metaboxes
     */
    public function save_custom_metaboxes( $post_id ) {
		
		// Allow Attributes to be in multiple attribute groups. E.g. the color attribute can be in more than 1 attribute group!
    	$multiple_attributes_in_groups = apply_filters( 'rh_multiple_attributes_in_groups', false );

    	if( get_post_type( $post_id ) !== "attribute_group" ) {
    		return;
    	}

        // Is the user allowed to edit the post or page?
        if( !current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if( !isset( $_POST['woocommerce_group_attributes_meta_nonce'] ) || !wp_verify_nonce($_POST['woocommerce_group_attributes_meta_nonce'], basename(__FILE__))) {
            return;
        }

        $prefix = 'woocommerce_group_attributes_';
        $attribute_group_meta[$prefix .'attributes'] = isset($_POST[$prefix .'attributes']) ? $_POST[$prefix .'attributes'] : array();
        /* $attribute_group_meta[$prefix .'image'] = isset($_POST[$prefix .'image']) ? $_POST[$prefix .'image'] : ''; */

		if( $multiple_attributes_in_groups == false ){

			$args = array( 'posts_per_page' => -1, 'post_type' => 'attribute_group', 'post_status' => 'publish', 'exclude' => $post_id);
			$attribute_groups = get_posts( $args );
			$already_grouped = array();

			foreach ($attribute_groups as $attribute_group) {
				$attributes_in_group = get_post_meta($attribute_group->ID, $prefix .'attributes');
				foreach ($attributes_in_group as $attribute_in_group) {
					$already_grouped[] = $attribute_in_group;
				}
			}

			$temp = array();
			foreach ($attribute_group_meta[$prefix .'attributes'] as $attribute) {
				if(!in_array($attribute, $already_grouped)){
					$temp[$attribute] = $attribute;
				}
			}
			$attribute_group_meta[$prefix .'attributes'] = $temp;
		}
		
        // Add values of $attribute_group_meta as custom fields
        foreach( $attribute_group_meta as $key => $value ) {
            if ( get_post_type( $post_id ) == 'revision' ) {
                return;
            }
            update_post_meta( $post_id, $key, $value );
        }
    }

    /**
     * Show attribute group toolbar in a Product
     */
    public function show_attribute_group_toolbar() {
		add_thickbox(); 

		$attribute_groups = get_posts(array(
			'post_type' => 'attribute_group',
			'post_status' => 'publish',
			'posts_per_page' => -1
		));
		?>
		<div class="toolbar">
			<h3 class="attribute_groups_tiltle"><?php esc_html_e( 'Product Attribute Groups', 'rehub-framework' ); ?></h3>
			<?php
			if( apply_filters( 'rh_enable_attribute_group_categories', true ) ):
				$attribute_group_categories = get_terms( array(
					'taxonomy' => 'attribute_group_categories',
					'hide_empty' => true,
				) );
			?>
			<button type="button" id="load_attribute_group_category" class="button button-primary" style="float: right;margin: 0 0 0 6px;"><?php esc_html_e( 'Load', 'rehub-framework' ); ?></button>
			<select id="woocommerce_attribute_group_categories" name="woocommerce_attribute_group_categories" class="woocommerce_attribute_group_categories" style="float: right;margin: 0 0 0 6px;">
				<option value=""><?php esc_html_e( 'Groups Categories', 'rehub-framework' ); ?></option>
				<?php 
				foreach ($attribute_group_categories as $attribute_group_category) {
					$attribute_groups_in_category = get_posts(
					    array(
					        'posts_per_page' => -1,
					        'post_type' => 'attribute_group',
					        'fields' => 'ids',
					        'tax_query' => array(
					            array(
					                'taxonomy' => 'attribute_group_categories',
					                'field' => 'term_id',
					                'terms' => $attribute_group_category->term_id,
					            )
					        )
					    )
					);
					if( empty( $attribute_groups_in_category ) ) { continue; }
					echo '<option value="'. $attribute_group_category->term_id .'" data-attribute-groups="'. implode( ',', $attribute_groups_in_category ) .'">'. $attribute_group_category->name .'</option>';
				}
				?>
			</select>
			<?php endif; ?>
			<button type="button" id="load_attribute_group" class="button button-primary" style="float: right;margin: 0 0 0 6px;"><?php esc_html_e( 'Load', 'rehub-framework' ); ?></button>
			<select id="woocommerce_attribute_groups" name="woocommerce_attribute_groups" class="woocommerce_attribute_groups" style="float: right;margin: 0 0 0 6px;">
				<option value=""><?php esc_html_e( 'Current Groups', 'rehub-framework' ); ?></option>
				<?php 
				foreach ($attribute_groups as $attribute_group) {
					echo '<option value="'. $attribute_group->ID .'">'. $attribute_group->post_title .'</option>';
				}
				?>
			</select>
			<a href="<?php echo admin_url( 'edit.php?post_type=attribute_group' ); ?>" class="button" onclick="return confirm('<?php esc_html_e( 'Are you sure you want to navigate away.', 'rehub-framework' ); ?>');"><?php esc_html_e( 'Manage Groups', 'rehub-framework' ); ?></a>
		</div>
		<?php
    }

	/**
	* Loading Attribute Group to Product in the Editor 
	*/
    public function get_attributes_by_attribute_group_id() {

    	$attribute_group_id = (isset($_POST['attribute_group_id']) && !empty($_POST['attribute_group_id'])) ? $_POST['attribute_group_id'] : "";
    	if(empty($attribute_group_id)) {
    		die('no id given!');
    	}

    	$attributes = get_post_meta( $attribute_group_id, 'woocommerce_group_attributes_attributes' );
		
    	if( !empty( $attributes ) ) {
    		$temp = array();
    		foreach( $attributes[0] as $attribute_id ) {
    			$attribute = wc_get_attribute( $attribute_id );
    			$temp[] = array(
    				'taxonomy' => $attribute->slug,
    				'i' => $attribute_id
    			);
    		}
    		$attributes = $temp;
    	}
    	die( json_encode( $attributes ) );
    }

	/** 
	* Frontend Product Attributes template 
	*/
	public function modify_attribute_template( $located, $template_name) {
		if('single-product/product-attributes.php' === $template_name){
			global $product;
			$attributes_group = rh_get_attributes_group($product);
			
			if(empty($attributes_group))
				return $located;
			
			return apply_filters( 'rh_woo_group_attributes_layout', RH_FRAMEWORK_ABSPATH .'/inc/templates/woo-group-attributes-public.php' );
		}
		return $located;
	}
}