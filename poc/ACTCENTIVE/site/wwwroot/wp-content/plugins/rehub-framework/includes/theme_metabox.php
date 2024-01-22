<?php
/**
 * Post Image Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//Sanitize meta for REST API
function rh_sanitize_custom_meta( $meta_value, $meta_key, $object_type ){
	$meta_value = wp_strip_all_tags( $meta_value );
	return $meta_value;
}

//Auth meta for REST API
function rh_auth_custom_meta( $false, $meta_key, $postID, $user_id, $cap, $caps ){
	if( ! current_user_can('manage_options') )
		return false;
	return true;
}

/**
 * RH_Meta_Box_Post.
 */
class RH_Meta_Box_Post {

	/**
	 * Is meta boxes saved once?
	 */
	private static $saved_meta_boxes = false;

	/**
	 * Meta box error messages.
	 */
	public static $meta_box_errors = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 35 );
		add_action( 'woocommerce_product_options_pricing', array( $this, 'show_rehub_woo_meta_box_inner' ) ); //Fields for external products
		add_filter( 'woocommerce_product_data_tabs', array($this, 'rh_custom_code_data_tab'));
		add_action('woocommerce_product_data_panels', array($this, 'rh_custom_code_data_fields'));
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2);
		add_action( 'admin_head', array( $this, 'meta_scripts' ));
		//script for panels are loaded in vendor\vafpress\public\js\metabox.min.js, vendor\vafpress\css\metabox.min.css

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) ); //RH metabox framework scripts

		add_action('admin_init',  array( $this, 'rhwoostore_tax_fields'), 1); //Woocommerce taxonomy meta
		add_action('admin_init', array( $this, 'category_tax_fields'), 1); //Category taxonomy meta

		if(REHub_Framework::get_option('enable_brand_taxonomy') == 1){
			add_action('admin_init', array( $this, 'dealstore_tax_fields'), 1); //Affiliate store taxonomy meta
		}

		// Error handling (for showing errors from meta boxes on next page load)
		add_action( 'admin_notices', array( $this, 'output_errors' ) );
		add_action( 'shutdown', array( $this, 'save_errors' ) );

		//Register fields for REST API
		add_action( 'rest_api_init', array($this, 'register_meta_rest'));

	}

	/**
	 * Add an error message.
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$meta_box_errors[] = $text;
	}

	/**
	 * Save errors to an option.
	 */
	public function save_errors() {
		update_option( 'rehub_meta_box_errors', self::$meta_box_errors );
	}

	public function meta_scripts() {
		global $pagenow, $post;
		if ( $pagenow=='post-new.php' || $pagenow=='post.php' ) {
			wp_enqueue_style(
				'flatpickr',
				RH_FRAMEWORK_URL . '/assets/css/flatpickr.css',
				false,
				'4.6.9'
			);
			wp_enqueue_script(
				'flatpickr',
				RH_FRAMEWORK_URL . '/assets/js/flatpickr.js',
				array(),
				'4.6.9',
				true
			);
		    //wp_enqueue_script('jquery-ui-datepicker');
			//wp_enqueue_style('jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css', false, null );
			$output = '<script type="text/javascript">
			jQuery(function() {
				jQuery(".rehubdatepicker").each(function(){jQuery(this).flatpickr({enableTime: true,dateFormat: "Y-m-d H:i"});});

				var imageFrame;jQuery(".meta_box_upload_image_button").click(function(e){e.preventDefault();return $self=jQuery(e.target),$div=$self.closest("div.meta_box_image"),imageFrame?void imageFrame.open():(imageFrame=wp.media({title:"Choose Image",multiple:!1,library:{type:"image"},button:{text:"Use This Image"}}),imageFrame.on("select",function(){selection=imageFrame.state().get("selection"),selection&&selection.each(function(e){console.log(e);{var t=e.attributes.url;e.id}$div.find(".meta_box_preview_image").attr("src",t),$div.find(".meta_box_upload_image").val(t)})}),void imageFrame.open())}),jQuery(".meta_box_clear_image_button").click(function(){var e=jQuery(this).parent().siblings(".meta_box_default_image").text();return jQuery(this).parent().siblings(".meta_box_upload_image").val(""),jQuery(this).parent().siblings(".meta_box_preview_image").attr("src",e),!1});
			});
			</script>';
			echo $output;
		}
	}

	public function load_scripts( $hook_suffix ) {

		$allowed_suffixes = array(
			'edit.php',
			'post.php',
			'post-new.php',
			'page-new.php',
			'page.php',
		);

		if ( ! in_array( $hook_suffix, $allowed_suffixes, true ) ) {
			return;
		}

		// CSS
		wp_enqueue_style(
			'rehub-metabox-css',
			RH_FRAMEWORK_URL . '/assets/css/theme-metabox.css',
			false,
			'2.4'
		);

		wp_enqueue_script(
			'rehub-metabox-js',
			RH_FRAMEWORK_URL . '/assets/js/theme-metabox.js',
			array('jquery'),
			'1.2',
			true
		);
	}

	/**
	 * Show any stored error messages.
	 */
	public function output_errors() {
		$errors = maybe_unserialize( get_option( 'rehub_meta_box_errors' ) );

		if ( ! empty( $errors ) ) {

			echo '<div id="rehub_errors" class="error notice is-dismissible">';

			foreach ( $errors as $error ) {
				echo '<p>' . wp_kses_post( $error ) . '</p>';
			}

			echo '</div>';

			// Clear
			delete_option( 'rehub_meta_box_errors' );
		}
	}


	/********************************
	 * Meta box field output functions
	 ********************************/

	public function rehub_group_field( $field, $value, $post ) {
		if ( ! $field['fields'] || empty( $field['fields'] ) ) {
			return false;
		}

		$label = (!empty( $field[ 'labelsingle' ] )) ? $field[ 'labelsingle' ] : 'Entry';

		$field[ 'index' ] = 0;
		?>
			<div class="rehub-row rehub-repeat-group-wrap" data-fieldtype="group">
				<div class="rehub-td">
					<?php if ( $field['label'] ) : ?>
						<div class="rehub-group-label"><label><?php echo esc_html( $field['label'] ); ?></label></div>
					<?php endif; ?>

					<div data-groupid="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>_repeat" class="rehub-repeatable-group repeatable">
						<?php
						if ( ! empty( $value ) ) :
							foreach ( $value as $field_value ) :
								echo $this->render_group_fields( $field_value, $field, $post );
								$field[ 'index' ]++;
							endforeach;
						else :
							echo $this->render_group_fields( $value, $field, $post );
						endif; ?>
						<div class="rehub-row"><div class="rehub-td"><p class="rehub-add-row"><button type="button" data-selector="<?php echo esc_attr( $field[ 'id' ] ); ?>_repeat" data-grouptitle="<?php echo ''.$label;?> {#}" class="rehub-add-group-row button-secondary">Add Another <?php echo ''.$label;?></button></p></div></div>
					</div>
				</div>
			</div>
		<?php
	}

	public function render_group_fields( $value, $field, $post ) {

		$label = (!empty( $field[ 'labelsingle' ] )) ? $field[ 'labelsingle' ] : 'Entry';

		?>
		<div id="rehub-group-<?php echo esc_attr( $field[ 'id' ] ); ?>-<?php echo esc_attr( $field[ 'index' ] ); ?>"  class="postbox rehub-row rehub-repeatable-grouping" data-iterator="<?php echo esc_attr( $field[ 'index' ] ); ?>">
			<button type="button" data-selector="<?php echo esc_attr( $field[ 'id' ] ); ?>_repeat" data-confirm="" class="dashicons-before dashicons-no-alt rehub-remove-group-row" title="Remove <?php echo ''.$label;?>" data-grouptitle="<?php echo ''.$label;?> {#}"></button>
			<div class="rehub-group-handle" title="Click to toggle"><br></div>
			<h3 class="rehub-group-title rehub-group-handle-title"><?php echo ''.$label;?> <?php echo $field[ 'index' ] + 1;?></h3>
			<div class="inside rehub-td">
				<?php
				$output = '';
				$index = 0;
				foreach ( $field['fields'] as $field_args ) {

					if ( ! $field_args['type'] || ! $field_args['label'] ) {
						continue;
					}

					if ( in_array( $field_args['type'], array( 'date', 'image' ) ) ) {
						continue;
					}
					?>
					<table class="form-table rehub-row rehub-type-<?php echo $field_args['type']; ?> rehub-group-id-<?php echo esc_attr( $field_args['id'] ); ?>-<?php echo esc_attr( $field[ 'index' ] ); ?> rehub-repeat-group-field table-layout">
						<tbody>
							<tr>
								<?php if ( $field_args['label'] ) : ?>
									<th>
										<label for="<?php echo esc_attr( $field[ 'id' ] ); ?>_<?php echo esc_attr( $field[ 'index' ] ); ?>_<?php echo esc_attr( $field_args[ 'id' ] ); ?>">
											<?php echo esc_html( $field_args['label'] ); ?>
										</label>
									</th>
								<?php endif; ?>
								<?php

								// Output field type
								$method = 'rehub_' . $field_args[ 'type' ]. '_field';

								if ( method_exists( $this, $method ) ) {

									$og_field_id = $field_args[ 'id' ];
									$field_args['id'] = esc_attr( $field[ 'id' ] ).'_'. esc_attr( $field[ 'index' ] ).'_'. esc_attr( $og_field_id );
									if($field_args[ 'type' ] == 'radio' || $field_args[ 'type' ] == 'checkbox_group'){
										$field_args['name'] = esc_attr( $field[ 'id' ] ) .'['. esc_attr( $field[ 'index' ] ) .']['. esc_attr( $og_field_id ) .'][]';
									}else{
										$field_args['name'] = esc_attr( $field[ 'id' ] ) .'['. esc_attr( $field[ 'index' ] ) .']['. esc_attr( $og_field_id ) .']';
									}

									if ( isset( $value[ $og_field_id ] ) ) {
										$field_value = $value[ $og_field_id ];
									} else {
										$field_value = '';
									}

									$expand = empty( $field_args[ 'label' ] ) ? ' colspan="2"' : '';

									echo '<td' . $expand . '>';
										echo $this->$method( $field_args, $field_value, $post );
									echo '</td>';
								}?>
							</tr>
						</tbody>
					</table>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}

	public function rehub_select_field( $field, $value, $post ) {
		if ( ! $field['items'] || empty( $field['items'] ) ) {
			return false;
		}

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		if ( is_array( $value ) ) {
			$value = current( $value );
		}

		if(isset( $field['labelsingle'])){
			$defaultlabel = $field['labelsingle'];
		}

		$output = '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="rehub-js-select">';
			$output .= '<option>'.$defaultlabel.'</option>';
			foreach ( $field['items'] as $choice_v => $name ) {
				$selected = selected( $value, $choice_v, false );
				$output .= '<option value="' .  esc_attr( $choice_v ) . '" ' . $selected . '>' . esc_attr( $name ) . '</option>';
			}
		$output .= '</select>';
		return $output;
	}

	public function rehub_select2_field( $field, $value, $post ) {
		if ( ! $field['items'] || empty( $field['items'] ) ) {
			return false;
		}

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		if ( is_array( $value ) ) {
			$value = current( $value );
		}

		$output = '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="rehub-js-select2">';
			foreach ( $field['items'] as $choice_v => $name ) {
				$selected = selected( $value, $choice_v, false );
				$output .= '<option value="' .  esc_attr( $choice_v ) . '" ' . $selected . '>' . esc_attr( $name ) . '</option>';
			}
		$output .= '</select>';
		return $output;
	}

	public function rehub_radio_field( $field, $value, $post ) {
		if ( empty( $field['items'] ) ) {
			return false;
		}

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		$output = '';
	    foreach ( $field['items'] as $choice_v => $name  ) {
	    	$checked = ($value == $choice_v) ? ' checked="checked"' : '';
	        $output .= '<input type="radio" name="'.$field['id'].'" value="'.$choice_v.'" '.$checked.' />
	                <label for="'.$choice_v.'">'.$name.'</label><br />';
	    }
		return $output;
	}

	public function rehub_checkbox_group_field( $field, $value, $post ) {
		if ( empty( $field['items'] ) ) {
			return false;
		}

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		$description = '';
		if ( isset( $field['desc'] ) ) {
			$description = $this->field_description( $field );
		}

		$output = '';
	    foreach ($field['items'] as $choice_v => $name) {
	    	$checked = $value && in_array($choice_v, $value) ? ' checked="checked"' : '';
	       	$output .= '<input type="checkbox" value="'.$choice_v.'" name="'.$field['id'].'[]" id="'.$choice_v.'" '.$checked.' />
	                <label for="'.$choice_v.'">'.$name.'</label><br />';
	    }
	    $output .= $description;
		return $output;
	}

	public function rehub_url_field( $field, $value, $post ) {
		return $this->field_input_markup( $field, $value, $post );
	}

	public function rehub_text_field( $field, $value, $post ) {
		return $this->field_input_markup( $field, $value, $post );
	}

	public function rehub_number_field( $field, $value, $post ) {
		$required    = isset( $field[ 'required' ] ) ? ' required' : '';
		$placeholder = ! empty( $field[ 'placeholder' ] ) ? ' placeholder="' . esc_attr( $field[ 'placeholder' ] ) . '"' : '';
		$description = '';
		if ( isset( $field['desc'] ) ) {
			$description = $this->field_description( $field );
		}

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		if ( $value && is_array( $value ) ) {
			$value = current( $value );
		}

		$min = isset( $field['min'] ) ? $field['min'] : '';
		$max = isset( $field['max'] ) ? $field['max'] : '';
		$step = isset( $field['step'] ) ? $field['step'] : 1;

		$size = '70';
		return sprintf( '<input type="%s" %s %s value="%s" id="%s" name="%s" size="%s" min="%s" max="%s" step="%s">%s', $field['type'], $placeholder, $required, $value, $field['id'], $field['name'], $size, $min, $max, $step, $description );
	}

	public function rehub_range_field( $field, $value, $post ) {
		$required    = isset( $field[ 'required' ] ) ? ' required' : '';
		$description = '';
		if ( isset( $field['desc'] ) ) {
			$description = $this->field_description( $field );
		}

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		if ( $value && is_array( $value ) ) {
			$value = current( $value );
		}
		

		$ticks = '';
		if ( !empty( $field['items'] ) ) {
			$ticks .= '<datalist id="ticks_'.$field['id'].'">';
			foreach ($field['items'] as $choice_v => $name) {
				$ticks .='<option value="'.$choice_v.'" label="'.$name.'">';
			}
			$ticks .='</datalist>';
		}

		$listticks = ($ticks) ? ' list="ticks_'.$field['id'].'"' : '';

		$min = isset( $field['min'] ) ? $field['min'] : '';
		$max = isset( $field['max'] ) ? $field['max'] : '';
		$step = isset( $field['step'] ) ? $field['step'] : 1;

		if(!$value) $value = $min;

		return sprintf( '<div class="rh_metabox_range"><input type="%s" %s %s value="%s" id="%s" name="%s" min="%s" max="%s" step="%s"><span class="rh_metabox_range_val">%s</span>%s%s</div>', $field['type'], $listticks, $required, $value, $field['id'], $field['name'], $min, $max, $step, $value, $description, $ticks );
	}

	public function rehub_textbox_field( $field, $value, $post ) {
		$required    = isset( $field[ 'required' ] ) ? ' required' : '';
		$placeholder = ! empty( $field[ 'placeholder' ] ) ? ' placeholder="' . esc_attr( $field[ 'placeholder' ] ) . '"' : '';
		$rows     = isset ( $field[ 'rows' ] ) ? $field[ 'rows' ] : 4;
		$cols     = isset ( $field[ 'cols' ] ) ? $field[ 'cols' ] : 20;
		$description = '';
		if ( isset( $field['desc'] ) ) {
			$description = $this->field_description( $field );
		}

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		if ( $value && is_array( $value ) ) {
			$value = current( $value );
		}

		$style = 'width:100%';
		return sprintf( '<textarea cols=%s rows=%s class="short" style="%s" %s %s id="%s" name="%s">%s</textarea>%s', $cols, $rows, $style, $placeholder, $required, $field['id'], $field['name'], $value, $description );
	}

	public function rehub_date_field( $field, $value, $post ) {
		$required    = isset( $field[ 'required' ] ) ? ' required' : '';
		$placeholder = ! empty( $field[ 'placeholder' ] ) ? ' placeholder="' . esc_attr( $field[ 'placeholder' ] ) . '"' : '';
		$rows     = isset ( $field[ 'rows' ] ) ? $field[ 'rows' ] : 2;
		$cols     = isset ( $field[ 'cols' ] ) ? $field[ 'cols' ] : 20;
		$description = '';
		if ( isset( $field['desc'] ) ) {
			$description = $this->field_description( $field );
		}

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		if ( $value && is_array( $value ) ) {
			$value = current( $value );
		}

		return sprintf( '<input size=70 class="rehubdatepicker" id="%s" name="%s" value="%s" type="text">%s', $field['id'], $field['name'], $value, $description );
	}

	public function rehub_checkbox_field( $field, $value, $post ) {
		$value    = $value ? true : false;
		$checked  = checked( $value, true, false );
		$description = '';
		if ( isset( $field['desc'] ) ) {
			$description = $this->field_description( $field );
		}

		$label = (!empty( $field[ 'labelsingle' ] )) ? $field[ 'labelsingle' ] : '';

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		if(isset( $field['switch'] )){
			$classname = 'class="switch" ';
		}else{
			$classname = '';
		}

		if ( $value && is_array( $value ) ) {
			$value = current( $value );
		}

		return '<input '.$classname.'id="' . esc_attr( $field[ 'id' ] ) . '" name="' . esc_attr( $field[ 'name' ] ) . '" type="checkbox" ' . $checked . '><label for="'.esc_attr( $field[ 'id' ] ).'">'.$label.'</label>'. $description;
	}

	public function rehub_helper_field( $field, $value, $post ) {
		return sprintf( '<div class="description">%s</div><br />[quick_offer id="%s"]', esc_html__( 'By default, only next Post layouts will show offerbox automatically: Compact, Button in corner, Big post offer block in top, Offer and review score. You can also add next shortcode to render offerbox:', 'rehub-framework' ), $post->ID );
	}

	public function rehub_cesync_field( $field, $value, $post ) {
		$cegg_field_array = REHub_Framework::get_option('save_meta_for_ce');
        $cegg_fields = [];

        if ( empty( $cegg_field_array ) || ! is_array( $cegg_field_array ) ) {
        	return false;
        }

        foreach( $cegg_field_array as $cegg_field ) {
        	if ( $cegg_field == 'none' || $cegg_field == '' ) {
        		continue;
        	}

        	$cegg_field_value = \ContentEgg\application\components\ContentManager::getViewData( $cegg_field, $post->ID );

        	if ( empty( $cegg_field_value ) || ! is_array( $cegg_field_value ) ) {
        		continue;
        	}

        	$cegg_fields += $cegg_field_value;
        }

        if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		if ( is_array( $value ) ) {
			$value = current( $value );
		}

        $output = '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '">';
        	$output .= '<option value="lowest" '. selected( 'lowest', $value, false ).'>'. esc_html__( 'Sync with lowest price offer', 'rehub-framework' ) .'</option>';

        	foreach ( $cegg_fields as $cegg_field_key => $cegg_field_value ) {
        		$currency_code = $offer_price = $domain = $title = '';
        		if ( ! empty( $cegg_field_value['currencyCode'] ) ) {
        			$currency_code = $cegg_field_value['currencyCode'];
        		}

        		if ( ! empty( $cegg_field_value['price'] ) ) {
        			$offer_price = \ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency( $cegg_field_value['price'], $currency_code );
        		}

        		if ( ! empty( $cegg_field_value['domain'] ) ) {
        			$domain = $cegg_field_value['domain'];
        		}

        		if ( ! empty( $cegg_field_value['title'] ) ) {
        			$title = $cegg_field_value['title'];
        		}
				$output .= '<option value="' .  esc_attr( $cegg_field_key ) . '" ' . selected( $cegg_field_key, $value, false ) . '>' . wp_trim_words($title, 10, '...' ).' - '.$offer_price.$currency_code.' - '.$domain . '</option>';
			}

			$output .= '<option value="none" '.selected('none', $value, false).'>Disable synchronization for this post</option>';

        $output .= '</select>';

        return $output;
	}

	public function rehub_image_field( $field, $value, $post ) {
		$df_image = $image = get_template_directory_uri().'/images/default/noimage_100_70.png';

		if ( is_array( $value ) ) {
			$value = array_filter( $value );
		}

		if ( $value ) {
			$image = $value;
		}

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		if ( is_array( $value ) ) {
			$value = current( $value );
		}

		if ( is_array( $image ) ) {
			$image = current( $image );
		}

		ob_start();
		?>
			<div class="meta_box_image">
				<span class="meta_box_default_image" style="display:none"><?php esc_url( $df_image ); ?></span>
				<input name="<?php echo esc_attr( $field['name'] ); ?>" type="text" size="70" class="meta_box_upload_image" value="<?php echo esc_url( $value ); ?>" />
				<a href="#" class="meta_box_upload_image_button button" rel="<?php echo esc_attr( $post->ID ); ?>">
					<?php esc_html_e( 'Choose Image', 'rehub-framework'); ?>
				</a>
				<small>&nbsp;<a href="#" class="meta_box_clear_image_button button">X</a></small>
				<br /><br />
				<img src="<?php echo esc_attr( $image ); ?>" class="meta_box_preview_image" alt="image" style="max-width: 200px; max-height:200px" />
			</div>

		<?php
		$output = ob_get_clean();

		return $output;
	}

	public function field_input_markup( $field, $value, $post ) {
		$required    = isset( $field[ 'required' ] ) ? ' required' : '';
		$placeholder = ! empty( $field[ 'placeholder' ] ) ? ' placeholder="' . esc_attr( $field[ 'placeholder' ] ) . '"' : '';
		$description = '';
		if ( isset( $field['desc'] ) ) {
			$description = $this->field_description( $field );
		}

		if ( isset( $field['name'] ) ) {
			$field['name'] = $field['name'];
		} else {
			$field['name'] = $field['id'];
		}

		if ( $value && is_array( $value ) ) {
			$value = current( $value );
		}
		$icon = '';
		if($field['type'] == 'url'){
			$icon = '<svg class="rh_metabox_icon" width=20 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="#275EFE" d="M301.148 394.702l-79.2 79.19c-50.778 50.799-133.037 50.824-183.84 0-50.799-50.778-50.824-133.037 0-183.84l79.19-79.2a132.833 132.833 0 0 1 3.532-3.403c7.55-7.005 19.795-2.004 20.208 8.286.193 4.807.598 9.607 1.216 14.384.481 3.717-.746 7.447-3.397 10.096-16.48 16.469-75.142 75.128-75.3 75.286-36.738 36.759-36.731 96.188 0 132.94 36.759 36.738 96.188 36.731 132.94 0l79.2-79.2.36-.36c36.301-36.672 36.14-96.07-.37-132.58-8.214-8.214-17.577-14.58-27.585-19.109-4.566-2.066-7.426-6.667-7.134-11.67a62.197 62.197 0 0 1 2.826-15.259c2.103-6.601 9.531-9.961 15.919-7.28 15.073 6.324 29.187 15.62 41.435 27.868 50.688 50.689 50.679 133.17 0 183.851zm-90.296-93.554c12.248 12.248 26.362 21.544 41.435 27.868 6.388 2.68 13.816-.68 15.919-7.28a62.197 62.197 0 0 0 2.826-15.259c.292-5.003-2.569-9.604-7.134-11.67-10.008-4.528-19.371-10.894-27.585-19.109-36.51-36.51-36.671-95.908-.37-132.58l.36-.36 79.2-79.2c36.752-36.731 96.181-36.738 132.94 0 36.731 36.752 36.738 96.181 0 132.94-.157.157-58.819 58.817-75.3 75.286-2.651 2.65-3.878 6.379-3.397 10.096a163.156 163.156 0 0 1 1.216 14.384c.413 10.291 12.659 15.291 20.208 8.286a131.324 131.324 0 0 0 3.532-3.403l79.19-79.2c50.824-50.803 50.799-133.062 0-183.84-50.802-50.824-133.062-50.799-183.84 0l-79.2 79.19c-50.679 50.682-50.688 133.163 0 183.851z"></path></svg>';
		}

		$size = '70';
		return sprintf( '<input type="%s" %s %s value="%s" id="%s" name="%s" size="%s">%s%s', $field['type'], $placeholder, $required, $value, $field['id'], $field['name'], $size, $icon, $description );
	}

	public function field_description( $field ) {
		return sprintf( '<span class="description">%s</span>', $field['desc'] );
	}


	/********************************
	 * Sanitization
	 ********************************/

	//Sanitize arrays
	public function rh_meta_box_array_sanitize( $func, $meta, $sanitizer ) {

		$newMeta = array();
		$meta = array_values( $meta );

		foreach( $meta as $key => $array ) {
			if ( $array == '' )
				continue;
			/**
			 * some values are stored as array, we only want multidimensional ones
			 */
			if ( ! is_array( $array ) ) {
				return array_map( $func, $meta, (array)$sanitizer );
				break;
			}
			/**
			 * the sanitizer will have all of the fields, but the item may only
			 * have valeus for a few, remove the ones we don't have from the santizer
			 */
			$keys = array_keys( $array );
			$newSanitizer = $sanitizer;
			if ( is_array( $sanitizer ) ) {
				foreach( $newSanitizer as $sanitizerKey => $value )
					if ( ! in_array( $sanitizerKey, $keys ) )
						unset( $newSanitizer[$sanitizerKey] );
			}
			/**
			 * run the function as deep as the array goes
			 */
			foreach( $array as $arrayKey => $arrayValue )
				if ( is_array( $arrayValue ) )
					$array[$arrayKey] = $this->rh_meta_box_array_sanitize( $func, $arrayValue, $newSanitizer[$arrayKey] );

			$array = array_map( $func, $array, $newSanitizer );
			$newMeta[$key] = array_combine( $keys, array_values( $array ) );
		}
		return $newMeta;
	}

	//Sanitize values for fields
	public function sanitize_value_for_db( $input, $field, $group_field = [] ) {

		$type = $field[ 'type' ];

		if ( 'text' == $type ) {
			return wp_kses_post( $input );
		} elseif ( 'number' == $type ) {
			return floatval( $input );
		} elseif ( 'range' == $type ) {
			return floatval( $input );
		} elseif ( 'url' == $type ) {
            $input = wp_sanitize_redirect($input);
            $input = filter_var($input, FILTER_SANITIZE_URL);
            return $input;
 		} elseif ( 'textbox' == $type ) {
            $input = wp_kses_post($input);
            return $input;
		} elseif ( 'textarea' == $type ) {
			return sanitize_textarea_field( $input );
		} elseif ( 'checkbox' == $type ) {
			return isset( $input ) ? true : false;
		} elseif ( 'select' == $type ) {
			if ( in_array( $input, $field[ 'items' ] ) || array_key_exists( $input, $field[ 'items' ] ) ) {
				return esc_attr( $input );
			}
		} elseif ( 'multi_select' == $type ) {
			if ( ! is_array( $input ) ) {
				return isset( $field[ 'default' ] ) ? $field[ 'default' ] : array();
			}
			$checks = true;
			foreach( $input as $v ) {
				if ( ! in_array( $v, $field[ 'items' ] ) && ! array_key_exists( $v, $field[ 'items' ] ) ) {
					$checks = false;
					break;
				}
			}
			return $checks ? $input : array();
		} elseif ( 'group' == $type && $group_field ) {
			return $input;
		} elseif ( 'file' == $type ) {
			return esc_url($input);
		} else{
        	if ( is_array( $input) ){
        		$sanitizer = isset( $field['sanitizer'] ) ? $field['sanitizer'] : 'sanitize_text_field';
        		return $this->rh_meta_box_array_sanitize( 'sanitize_text_field', $input, $sanitizer );
        	}else{
        		return sanitize_text_field($input);
        	}
		}
	}


	/********************************
	 * Init variable fields
	 ********************************/

	public static function meta_for_posts() {
		$post_custom_meta_fields = apply_filters('rh_post_custom_meta_fields', array(
		    array(
		        'label'=>  esc_html__('Offer url', 'rehub-framework'),
		        'desc'  => esc_html__('Insert url of offer', 'rehub-framework'),
		        'id'    => 'rehub_offer_product_url',
		        'type'  => 'url'
		    ),
		    array(
		        'label'=>  esc_html__('Name of product', 'rehub-framework'),
		        'desc'  => esc_html__('Insert title or leave blank', 'rehub-framework'),
		        'id'    => 'rehub_offer_name',
		        'type'  => 'text'
		    ),
		    array(
		        'label'=>  esc_html__('Short description of product', 'rehub-framework'),
		        'desc'  => esc_html__('Enter description of product or leave blank', 'rehub-framework'),
		        'id'    => 'rehub_offer_product_desc',
		        'type'  => 'text'
		    ),
		    array(
		        'label'=>  esc_html__('Disclaimer', 'rehub-framework'),
		        'desc'  => esc_html__('Optional. It works in deal lists. HTML and shortcodes are supported', 'rehub-framework'),
		        'id'    => 'rehub_offer_disclaimer',
		        'type'  => 'textbox'
		    ),
		    array(
		        'label'=>  esc_html__('Offer old price', 'rehub-framework'),
		        'desc'  => esc_html__('Insert old price of offer or leave blank', 'rehub-framework'),
		        'id'    => 'rehub_offer_product_price_old',
		        'type'  => 'text'
		    ),
		    array(
		        'label'=>  esc_html__('Offer sale price', 'rehub-framework'),
		        'desc'  => esc_html__('Insert sale price of offer (example, $55)', 'rehub-framework'),
		        'id'    => 'rehub_offer_product_price',
		        'type'  => 'text'
		    ),
		    array(
		        'label'=>  esc_html__('Set coupon code', 'rehub-framework'),
		        'desc'  => esc_html__('Set coupon code or leave blank', 'rehub-framework'),
		        'id'    => 'rehub_offer_product_coupon',
		        'type'  => 'text'
		    ),
			array(
			    'label' => esc_html__('Expiration Date', 'rehub-framework'),
			    'desc'  => esc_html__('Choose expiration date or leave blank', 'rehub-framework'),
			    'id'    => 'rehub_offer_coupon_date',
			    'type'  => 'date'
			),
		    array(
		        'label'=> esc_html__('Mask coupon code?', 'rehub-framework'),
		        'desc'  => esc_html__('If this option is enabled, coupon code will be hidden.', 'rehub-framework'),
		        'id'    => 'rehub_offer_coupon_mask',
		        'labelsingle' => esc_html__('Yes', 'rehub-framework'),
		        'type'  => 'checkbox'
		    ),
		    array(
		        'label'=> esc_html__('Offer is expired?', 'rehub-framework'),
		        'desc'  => esc_html__('It works automatically, but you can force expiration', 'rehub-framework'),
		        'id'    => 're_post_expired',
		        'labelsingle' => esc_html__('Yes', 'rehub-framework'),
		        'type'  => 'checkbox'
		    ),
		    array(
		        'label'=>  esc_html__('Verify label', 'rehub-framework'),
		        'desc'  => esc_html__('Set custom text here to show verification icon', 'rehub-framework'),
		        'id'    => 'rehub_offer_verify_label',
		        'type'  => 'text'
		    ),
		    array(
		        'label'=> esc_html__('Button text', 'rehub-framework'),
		        'desc'  => esc_html__('Insert text (not more than 14 symbols) on button or leave blank to use default text', 'rehub-framework'),
		        'id'    => 'rehub_offer_btn_text',
		        'type'  => 'text'
		    ),
			array(
			    'label'  => esc_html__('Upload thumbnail', 'rehub-framework'),
			    'desc'  => esc_html__('Upload thumbnail of product or leave blank to use post thumbnail', 'rehub-framework'),
			    'id'    => 'rehub_offer_product_thumb',
			    'type'  => 'image'
			),
		    array(
		        'label'=> esc_html__('Brand logo url', 'rehub-framework'),
		        'desc'  => esc_html__('Fallback for brand logo (better to add brand logo in Affiliate store fields)', 'rehub-framework'),
		        'id'    => 'rehub_offer_logo_url',
		        'type'  => 'text'
		    ),
		    array(
		        'label'=>  esc_html__('Discount Tag', 'rehub-framework'),
		        'desc'  => esc_html__('Will be visible in deal, coupon list instead featured image. It shows maximum 5 symbols. Example: $20', 'rehub-framework'),
		        'id'    => 'rehub_offer_discount',
		        'type'  => 'text'
		    ),
		    array(
		        'label'=> esc_html__('Shortcode for this offer section', 'rehub-framework'),
		        'id'    => 'rehub_offer_shortcode_generate',
		        'type'  => 'helper'
		    ),
		));
		if (defined('\ContentEgg\PLUGIN_PATH')){
		    $post_custom_meta_fields[] =  array(
		        'label'=> esc_html__('Synchronization with Content Egg', 'rehub-framework'),
		        'id'    => '_rh_post_offer_sync_ce',
		        'type'  => 'cesync'
		    );
		}
		/* Examples for other

		    ///checkbox group
			array (
			    'label' => 'Checkbox Group',
			    'desc'  => 'A description for the field.',
			    'id'    => 'rehub_checkbox_group',
			    'type'  => 'checkbox_group',
			    'items' => array (
					'one' => 'One',
					'two' => 'Two',
					'three' => 'Three'
			    )
			),


		*/
		return $post_custom_meta_fields;
	}

	public static function meta_for_posts_side_high() {
		$postlayout = apply_filters( 'rehub_post_layouts_array', array(
				'default'=> esc_html__('Simple', 'rehub-framework'),
				'default_full_opt'=> esc_html__('Optimized Full width', 'rehub-framework'),
				'meta_outside'=> esc_html__('Title is outside content', 'rehub-framework'),
				'guten_auto'=> esc_html__('Gutenberg Auto Contents', 'rehub-framework'),
				'gutencustom'=> esc_html__('Customizable Full width', 'rehub-framework'),
				'default_text_opt'=> esc_html__('Optimized for reading with sidebar', 'rehub-framework'),
				'video_block'=> esc_html__('Video Block', 'rehub-framework'),
				'meta_center'=> esc_html__('Center aligned (Rething style)', 'rehub-framework'),
				'meta_compact'=> esc_html__('Compact (Button Block Under Title)', 'rehub-framework'),
				'meta_compact_dir'=> esc_html__('Compact (Button Block Before Title)', 'rehub-framework'),
				'corner_offer'=> esc_html__('Button in corner (Repick style)', 'rehub-framework'),
				'meta_in_image'=> esc_html__('Title Inside image', 'rehub-framework'),
				'meta_in_imagefull'=> esc_html__('Title Inside full image', 'rehub-framework'),
				'big_post_offer'=> esc_html__('Big post offer block in top', 'rehub-framework'),
				'offer_and_review'=> esc_html__('Offer and review score', 'rehub-framework'),
		));
		$post_high_fields = array(
		    array(
		        'label'=> esc_html__('Post Layout', 'rehub-framework'),
		        'id'    => '_post_layout',
		        'type'  => 'select',
			    'items' => $postlayout,
				'labelsingle' => esc_html__('Global from Theme option - General - Post Layout', 'rehub-framework')
		    ),
			array (
			    'label' => esc_html__('Post w/ sidebar or Full width', 'rehub-framework'),
			    'id'    => 'post_size',
			    'type'  => 'radio',
			    'items' => array (
					'normal_post' => esc_html__('Post w/ Sidebar', 'rehub-framework'),
					'full_post' => esc_html__('Full Width Post', 'rehub-framework'),
			    )
			),
			array (
			    'label' => esc_html__('Add badge', 'rehub-framework'),
				'desc' => esc_html__('You can customize badges in theme option', 'rehub-framework'),
			    'id'    => 'is_editor_choice',
			    'type'  => 'radio',
			    'items' => array (
					'0' => esc_html__('No', 'rehub-framework'),
					'1' => (REHub_Framework::get_option('badge_label_1') !='') ? REHub_Framework::get_option('badge_label_1') : esc_html__('Editor choice', 'rehub-framework'),
					'2' => (REHub_Framework::get_option('badge_label_2') !='') ? REHub_Framework::get_option('badge_label_2') : esc_html__('Best seller', 'rehub-framework'),
					'3' => (REHub_Framework::get_option('badge_label_3') !='') ? REHub_Framework::get_option('badge_label_3') : esc_html__('Best value', 'rehub-framework'),
					'4' => (REHub_Framework::get_option('badge_label_4') !='') ? REHub_Framework::get_option('badge_label_4') : esc_html__('Best price', 'rehub-framework'),
			    )
			),
		    array(
		        'label'=> esc_html__('Disable Top Image?', 'rehub-framework'),
		        'desc'  => esc_html__('Check this box to disable Featured Image in top part on post page', 'rehub-framework'),
		        'id'    => 'show_featured_image',
		        'labelsingle' => esc_html__('Yes', 'rehub-framework'),
		        'type'  => 'checkbox',
				'switch' => true,
		    ),
		    array(
		        'label'=> esc_html__('Disable global ads in post?', 'rehub-framework'),
		        'id'    => 'show_banner_ads',
		        'labelsingle' => esc_html__('Yes', 'rehub-framework'),
		        'type'  => 'checkbox',
				'switch' => true,
		    ),
		    array(
		        'label'=>  esc_html__('Custom notice', 'rehub-framework'),
		        'desc'  => esc_html__('Will be used as custom notice, for example, for cashback', 'rehub-framework'),
		        'id'    => '_notice_custom',
		        'type'  => 'text'
		    ),
		);
		if(REHub_Framework::get_option('theme_subset') == 'repick'){
			$post_high_fields[] = 
				array(
					'type' => 'text',
					'id' => 'amazon_search_words',
					'label' => __('Search on amazon keyword', 'rehubchild'),
					'desc' => __('Will be used in top offer block', 'rehubchild'),
				);
			$post_high_fields[] = 		array(
					'type' => 'text',
					'id' => 'ebay_search_words',
					'label' => __('Search on ebay keyword', 'rehubchild'),
					'desc' => __('Will be used in top offer block', 'rehubchild'),
				);
		}
		return $post_high_fields;
	}

	public static function meta_for_page_side() {
		$page_fields = array(
			array (
			    'label' => esc_html__('Type of content area', 'rehub-framework'),
			    'id'    => 'content_type',
			    'type'  => 'radio',
			    'items' => array (
					'def' => esc_html__('Content with sidebar', 'rehub-framework'),
					'full_width' => esc_html__('Full Width Content Box', 'rehub-framework'),
					'full_post_area' => esc_html__('Full width of browser window', 'rehub-framework'),
					'full_gutenberg' => esc_html__('Gutenberg Compact width', 'rehub-framework'),
					'full_gutenberg_reg' => esc_html__('Gutenberg Regular width', 'rehub-framework'),
					'full_gutenberg_ext' => esc_html__('Gutenberg Extended width', 'rehub-framework'),
			    )
			),
			array (
			    'label' => esc_html__('How to show header?', 'rehub-framework'),
			    'id'    => '_header_disable',
			    'type'  => 'radio',
			    'items' => array (
					'0' => esc_html__('Default', 'rehub-framework'),
					'1' => esc_html__('Disable header', 'rehub-framework'),
					'2' => esc_html__('Transparent', 'rehub-framework'),
			    )
			),
		    array(
		        'label'=> esc_html__('Disable title', 'rehub-framework'),
		        'id'    => '_title_disable',
		        'labelsingle' => esc_html__('Yes', 'rehub-framework'),
		        'type'  => 'checkbox',
				'switch' => true,
		    ),
		    array(
		        'label'=> esc_html__('Enable preloader', 'rehub-framework'),
		        'id'    => '_enable_preloader',
		        'labelsingle' => esc_html__('Yes', 'rehub-framework'),
		        'type'  => 'checkbox',
				'switch' => true,
		    ),
			array(
		        'label'=> esc_html__('Enable comments', 'rehub-framework'),
		        'id'    => '_enable_comments',
		        'labelsingle' => esc_html__('Yes', 'rehub-framework'),
		        'type'  => 'checkbox',
				'switch' => true,
		    ),
			array(
		        'label'=> esc_html__('Disable menu', 'rehub-framework'),
		        'id'    => 'menu_disable',
		        'labelsingle' => esc_html__('Yes', 'rehub-framework'),
		        'type'  => 'checkbox',
				'switch' => true,
		    ),
			array(
		        'label'=> esc_html__('Disable footer', 'rehub-framework'),
		        'id'    => '_footer_disable',
		        'labelsingle' => esc_html__('Yes', 'rehub-framework'),
		        'type'  => 'checkbox',
				'switch' => true,
		    ),
		);
		return $page_fields;
	}

	public static function meta_for_products() { //We add some fields directly in Woo panels
		$woo_custom_meta_fields = apply_filters('rh_woo_custom_meta_fields', array(
		    array(
		        'label'=>  esc_html__('Set coupon code', 'rehub-framework'),
		        'desc'  => esc_html__('Set coupon code or leave blank', 'rehub-framework'),
		        'id'    => 'rehub_woo_coupon_code',
		        'type'  => 'text'
		    ),
			array(
			    'label' => esc_html__('Offer End Date', 'rehub-framework'),
			    'desc'  => esc_html__('Choose expiration date of product or leave blank', 'rehub-framework'),
			    'id'    => 'rehub_woo_coupon_date',
			    'type'  => 'date'
			),
		    array(
		        'label'=> esc_html__('Mask coupon code?', 'rehub-framework'),
		        'desc'  => esc_html__('If this option is enabled, coupon code will be hidden.', 'rehub-framework'),
		        'id'    => 'rehub_woo_coupon_mask',
		        'type'  => 'checkbox'
		    ),
			array(
		        'label'=> esc_html__('Additional coupon image url', 'rehub-framework'),
		        'desc'  => esc_html__('Used for printable coupon function. To enable it, you must have any coupon code above', 'rehub-framework'),
		        'id'    => 'rehub_woo_coupon_coupon_img_url',
		        'type'  => 'text'
			),

		));
		return $woo_custom_meta_fields;
	}

	public static function meta_for_brand_cat() { //Brand and store fields
		$rh_woostore_tax_meta = apply_filters('rhwoostore_tax_fields', array(
		    array(
		        'label'=>  esc_html__('Set Heading Title', 'rehub-framework'),
		        'id'    => 'brand_heading',
		        'type'  => 'text'
		    ),
		    array(
		        'label'=>  esc_html__('Set Short description', 'rehub-framework'),
		        'desc'  => esc_html__('Will be in sidebar', 'rehub-framework'),
		        'id'    => 'brand_short_description',
		        'type'  => 'textarea'
		    ),
		    array(
		        'label'=>  esc_html__('Set url of store', 'rehub-framework'),
		        'id'    => 'brand_url',
		        'type'  => 'url'
		    ),
		    array(
		        'label'=>  esc_html__('Set short notice (cashback notice)', 'rehub-framework'),
		        'id'    => 'cashback_notice',
		        'type'  => 'text'
		    ),
		    array(
		        'label'=>  esc_html__('Set bottom description', 'rehub-framework'),
		        'desc'  => esc_html__('Will be in bottom of page', 'rehub-framework'),
		        'id'    => 'brand_second_description',
		        'type'  => 'textarea'
		    ),
		    array(
		        'label'  => esc_html__('Upload logo', 'rehub-framework'),
		        'desc'  => esc_html__('Upload or choose image here for retailer logo or category header banner', 'rehub-framework'),
		        'id'    => 'brandimage',
		        'type'  => 'image'
		    ),
		));
		return $rh_woostore_tax_meta;
	}

	public function meta_for_reviews_products() {
		$post_custom_meta_fields = apply_filters('woo_review_custom_meta_fields', array(
			array(
				'type'      => 'image',
				'id'      	=> '_woo_review_image_bg',
				'label'     => esc_html__('Add Image to review', 'rehub-framework'),
				'desc' 		=> esc_html__('In Full width Photo Layout, this image will be visible in top section. In other layouts - in review box', 'rehub-framework'),
			),
			array(
				'type'      => 'range',
				'id'      	=> '_review_post_score_manual',
				'label'     => esc_html__('Set overall score', 'rehub-framework'),
				'desc' 		=> esc_html__('Enter overall score of review or leave blank to auto calculation based on criterias score', 'rehub-framework'),
				'min'       => 0,
				'max'       => 10,
				'step'      => 0.1,
			),
			array(
				'type'      => 'text',
				'id'      	=> '_review_heading',
				'label'     => esc_html__('Review Heading', 'rehub-framework'),
			),
			array(
				'type'      => 'textbox',
				'id'      	=> '_review_post_summary_text',
				'label'     => esc_html__('Summary Text (optional)', 'rehub-framework'),
			),
			array(
				'type'      => 'textbox',
				'id'      	=> '_review_post_pros_text',
				'label'     => esc_html__('PROS. Place each from separate line (optional)', 'rehub-framework'),
			),
			array(
				'type'      => 'textbox',
				'id'      	=> '_review_post_cons_text',
				'label'     => esc_html__('CONS. Place each from separate line (optional)', 'rehub-framework'),
			),
			array(
		        'label'     => esc_html__('Review Criterias', 'rehub-framework'),
		        'labelsingle'     => esc_html__('Criteria', 'rehub-framework'),
		        'id'    	=> '_review_post_criteria',
		        'type'  	=> 'group',
		        'fields'    => array(
					array(
						'type'      => 'text',
						'id'      	=> 'review_post_name',
						'label'     => esc_html__('Name', 'rehub-framework'),
					),
					array(
						'type'      => 'range',
						'id'      	=> 'review_post_score',
						'label'     => esc_html__('Score', 'rehub-framework'),
						'min'       => 0,
						'max'       => 10,
						'step'      => 0.1,
					),
				)
		    ),

		    array(
		        'label'=> esc_html__('Enable shortcode inserting', 'rehub-framework'),
		        'labelsingle'=> esc_html__('Yes', 'rehub-framework'),
		        'desc'  => esc_html__('If enable you can insert review box in any place of content with shortcode [wpsm_reviewbox regular=1]. If disable - it will be after content.', 'rehub-framework'),
		        'id'    => 'review_woo_shortcode',
		        'type'  => 'checkbox'
		    ),
		));

		return $post_custom_meta_fields;
	}


	/********************************
	 * Init meta panels
	 ********************************/

	public function add_meta_boxes() {

		$def_p_types = rh_get_post_type_formeta();
		add_meta_box( 'post_rehub_offers', esc_html__( "Post Offer", "rehub-framework"  ), array( $this, 'show_post_metabox' ), $def_p_types, 'normal', 'low' );
		$def_p_types[] = 'blog';
		add_meta_box( 'side_rh_post_high', esc_html__( "Post settings", "rehub-framework" ), array($this, 'post_side_output_high'), $def_p_types, 'side', 'high' );
		add_meta_box( 'rehub-post-images', esc_html__( "Post Thumbnails and video", "rehub-framework"  ), array( $this, 'gallery_output' ), $def_p_types, 'side', 'low' );
		add_meta_box( 'side_rh_page', esc_html__( "Page settings", "rehub-framework" ), array($this, 'page_side_output'), 'page', 'side', 'high' );
		add_meta_box( 'side_rh_section', esc_html__( "Section type", "rehub-framework" ), array($this, 'wpblock_side_output'), 'wp_block', 'side', 'high' );

		if(function_exists('rh_review_inner_custom_box')){
			add_meta_box( 'rh_review_section', esc_html__( "Post User Review", "rehub-framework" ), 'rh_review_inner_custom_box', 'comment', 'normal' );
		}

		if(class_exists('WooCommerce')){
			add_meta_box( 'rh-wc-product-video', esc_html__( "360 gallery, video, 3D", "rehub-framework" ), array($this, 'wc_video_output'), 'product', 'side', 'low' );
			add_meta_box( 'side_rh_woo', esc_html__( "Product Layout", "rehub-framework" ), array($this, 'wc_side_output'), 'product', 'side', 'high' );
			if(function_exists('rh_woo_cm_edit_pros_cons')){
				add_meta_box( 'rh_woo_pros_section_edit_comment', esc_html__( "Pros and Cons", "rehub-framework" ), 'rh_woo_cm_edit_pros_cons', 'comment', 'normal' );
			}
			add_meta_box( 'rehub_review_woo', esc_html__( "Editor Review", "rehub-framework" ), array( $this, 'rh_woo_review_inner_custom_box' ), 'product', 'normal', 'low' );
		}

		add_meta_box( 'rh-shortcode-elementor-box', esc_html__( "Shortcode", "rehub-framework" ), array($this, 'rhe_shortcode_box'), 'elementor_library', 'side', 'high' );
	}

	public function show_metabox_form( $post, $fields, $table=true ) {

		echo '<div class="rehub-meta_factory-metabox">';
		foreach ( $fields as $key => $field ) {
			// Defaults
			$defaults = array(
				'label'   => '',
				'id'      => '',
				'type'    => '',
				'desc'    => '',
				'default' => '',
			);

			if($table){
				$wrapper = '<table class="form-table">';
				$wrapperclose = '</table>';
				$titlecodestart = 'th';
				$titlecode = 'th';
				$linecode = 'tr';
				$itemcode = 'td';
			}else{
				$wrapper = '<div class="form-side-rh">';
				$wrapperclose = '</div>';
				$titlecodestart = 'div class="form-side-rh-title"';
				$titlecode = 'div';
				$linecode = 'div';
				$itemcode = 'div';				
			}

			// Parse and extract
			$field = wp_parse_args( $field, $defaults );
			if ( $field['type'] == 'group' )  {
				$value = get_post_meta( $post->ID, $field[ 'id' ], true );
				echo $this->rehub_group_field( $field, $value, $post );
			} else {

				// Get field values
				$custom_field_keys = get_post_custom_keys();
				if ( is_array( $custom_field_keys ) && in_array( $field[ 'id' ], $custom_field_keys ) ) {
					$value = get_post_meta( $post->ID, $field[ 'id' ], true );
				} else {
					$value = $field[ 'default' ];
				}

				?>
					<?php echo ''.$wrapper;?>
						<<?php echo ''.$linecode;?> id="row_<?php echo esc_attr( $field[ 'id' ] ); ?>">
							<?php if ( $field[ 'label' ] ) : ?>
								<<?php echo ''.$titlecodestart;?>><?php echo esc_html( $field[ 'label' ] ); ?></<?php echo ''.$titlecode;?>>
							<?php endif; ?>
							<?php
							// Output field type
							$method = 'rehub_' . $field[ 'type' ]. '_field';

							if ( method_exists( $this, $method ) ) {

								$expand = empty( $field[ 'label' ] ) ? ' colspan="2"' : '';

								echo '<'.$itemcode.' ' . $expand . '>';
									echo $this->$method( $field, $value, $post );
								echo '</'.$itemcode.'>';
							}
							?>
						</<?php echo ''.$linecode;?>>
					<?php echo ''.$wrapperclose;?>
				<?php
			}
		}

		echo '</div>';
	}


	/********************************
	 * Meta box panel output functions
	 ********************************/

	//post type meta
	public function show_post_metabox( $post ) {
		$fields = $this->meta_for_posts();
		$this->show_metabox_form( $post, $fields);
	}
	public function post_side_output_high( $post ) {
		$fields = $this->meta_for_posts_side_high();
		$this->show_metabox_form( $post, $fields, false);
	}
	public function page_side_output( $post ) {
		$fields = $this->meta_for_page_side();
		$this->show_metabox_form( $post, $fields, false);
		wp_nonce_field( 'rehub_post_meta_save', 'rehub_post_meta_nonce' );
	}

	// Woocommerce reviews
	public function rh_woo_review_inner_custom_box( $post ) {
		$fields = $this->meta_for_reviews_products();
		$this->show_metabox_form( $post, $fields);
	}

	//Gallery panel
	public static function gallery_output( $post ) {
		?>
		<div id="rh_post_images_container">
			<ul class="rh_post_images">
				<?php
					if ( metadata_exists( 'post', $post->ID, 'rh_post_image_gallery' ) ) {
						$post_image_gallery = get_post_meta( $post->ID, 'rh_post_image_gallery', true );
					} else {
						// Backwards compat
						$attachment_ids = get_posts( 'post_parent=' . $post->ID . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids&meta_value=0' );
						$attachment_ids = array_diff( $attachment_ids, array( get_post_thumbnail_id() ) );
						$post_image_gallery = implode( ',', $attachment_ids );
					}

					$attachments = array_filter( explode( ',', $post_image_gallery ) );
					$update_meta = false;
					$updated_gallery_ids = array();

					if ( ! empty( $attachments ) ) {
						foreach ( $attachments as $attachment_id ) {
							$attachment = wp_get_attachment_image( $attachment_id, 'thumbnail' );

							// if attachment is empty skip
							if ( empty( $attachment ) ) {
								$update_meta = true;
								continue;
							}

							echo '<li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '">
								' . $attachment . '
								<ul class="actions">
									<li><a href="#" class="delete tips" data-tip="' . esc_attr__( "Delete image", "rehub-framework" ) . '">' . esc_html__( "Delete", "rehub-framework" ) . '</a></li>
								</ul>
							</li>';

							// rebuild ids to be saved
							$updated_gallery_ids[] = $attachment_id;
						}

						// need to update post meta to set new gallery ids
						if ( $update_meta ) {
							update_post_meta( $post->ID, 'rh_post_image_gallery', implode( ',', $updated_gallery_ids ) );
						}
					}
				?>
			</ul>
			<input type="hidden" id="rh_post_image_gallery" name="rh_post_image_gallery" value="<?php echo esc_attr( $post_image_gallery ); ?>" />
			<?php wp_nonce_field( 'rehub_post_meta_save', 'rehub_post_meta_nonce' ); ?>
		</div>
		<p class="rh_add_post_images hide-if-no-js">
			<a href="#" data-choose="<?php esc_attr_e( "Add Images to Post Gallery", "rehub-framework" ); ?>" data-update="<?php esc_attr_e( "Add to gallery", "rehub-framework" ); ?>" data-delete="<?php esc_attr_e( "Delete image", "rehub-framework" ); ?>" data-text="<?php esc_attr_e( "Delete", "rehub-framework" ); ?>"><?php esc_html_e( "Add post gallery images", "rehub-framework" ); ?></a>
		</p>

		<p class="rh_add_post_images hide-if-no-js">
		<small><?php esc_html_e('Add video links, each link from new line. Youtube and vimeo are supported', 'rehub-framework');?></small>
			<textarea id="rh_post_image_videos" rows="3" name="rh_post_image_videos"><?php echo get_post_meta( $post->ID, 'rh_post_image_videos', true );?></textarea>
		</p>
		<p class="rh_add_post_images hide-if-no-js"><small><?php esc_html_e('You can add gallery to post with shortcode [rh_get_post_thumbnails video=1 height=200 justify=1]. video=1 - include also video. Height is maximum height, justify=1 is parameter to show pretty justify gallery. [rh_get_post_videos] will show only videos in full size column', 'rehub-framework');?></small></p>
		<?php
	}

	//Video panel
	public static function wc_video_output( $post ){
		$post_id = $post->ID;
		wp_nonce_field( 'rehub_post_meta_save', 'rehub_post_meta_nonce' );
		?>
		<div id="rehub-post-images">
			<div class="inside">
				<div id="rh_post_images_container">
					<ul class="rh_post_images">
						<?php
							if ( metadata_exists( 'post', $post->ID, 'rh_post_image_gallery' ) ) {
								$post_image_gallery = get_post_meta( $post->ID, 'rh_post_image_gallery', true );
							} else {
								// Backwards compat
								$attachment_ids = get_posts( 'post_parent=' . $post->ID . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids&meta_value=0' );
								$attachment_ids = array_diff( $attachment_ids, array( get_post_thumbnail_id() ) );
								$post_image_gallery = implode( ',', $attachment_ids );
							}
		
							$attachments = array_filter( explode( ',', $post_image_gallery ) );
							$update_meta = false;
							$updated_gallery_ids = array();
		
							if ( ! empty( $attachments ) ) {
								foreach ( $attachments as $attachment_id ) {
									$attachment = wp_get_attachment_image( $attachment_id, 'thumbnail' );
		
									// if attachment is empty skip
									if ( empty( $attachment ) ) {
										$update_meta = true;
										continue;
									}
		
									echo '<li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '">
										' . $attachment . '
										<ul class="actions">
											<li><a href="#" class="delete tips" data-tip="' . esc_attr__( "Delete image", "rehub-framework" ) . '">' . esc_html__( "Delete", "rehub-framework" ) . '</a></li>
										</ul>
									</li>';
		
									// rebuild ids to be saved
									$updated_gallery_ids[] = $attachment_id;
								}
		
								// need to update post meta to set new gallery ids
								if ( $update_meta ) {
									update_post_meta( $post->ID, 'rh_post_image_gallery', implode( ',', $updated_gallery_ids ) );
								}
							}
						?>
					</ul>
					<input type="hidden" id="rh_post_image_gallery" name="rh_post_image_gallery" value="<?php echo esc_attr( $post_image_gallery ); ?>" />
				</div>
				<p class="rh_add_post_images hide-if-no-js">
					<a href="#" data-choose="<?php esc_attr_e( "Add Images to 360 Gallery", "rehub-framework" ); ?>" data-update="<?php esc_attr_e( "Add to 360 gallery", "rehub-framework" ); ?>" data-delete="<?php esc_attr_e( "Delete image", "rehub-framework" ); ?>" data-text="<?php esc_attr_e( "Delete", "rehub-framework" ); ?>"><?php esc_html_e( "Add 360 gallery images", "rehub-framework" ); ?></a>
				</p>
			</div>
		</div>
		<div id="product_video_container" class="hide-if-no-js">
			<textarea id="rh_product_video" rows="3" name="rh_product_video"><?php echo get_post_meta( $post_id, 'rh_product_video', true );?></textarea>
			<p class="howto"><?php esc_html_e('Add video links, each link from new line. Youtube and vimeo are supported', 'rehub-framework'); ?></p>
		</div>
		<div id="product_td_container" class="hide-if-no-js">
			<textarea id="rh_td_model" rows="2" name="rh_td_model"><?php echo get_post_meta( $post_id, 'rh_td_model', true );?></textarea>
			<p class="howto"><?php esc_html_e('Add link to 3d model in GLTF, GLB format. You can upload file in Media panel', 'rehub-framework'); ?></p>
		</div>
		<div id="product_td_usdz_container" class="hide-if-no-js">
			<textarea id="rh_td_model_usdz" rows="2" name="rh_td_model_usdz"><?php echo get_post_meta( $post_id, 'rh_td_model_usdz', true );?></textarea>
			<p class="howto"><?php esc_html_e('Add link to 3d model in USDZ format. This format will be used for IOS AR', 'rehub-framework'); ?></p>
		</div>
		<?php
	}

	//Add Side panel WP block
	public function wpblock_side_output($post){
		$sectionmeta = get_post_meta($post->ID, '_rh_section_type', true);
		$sections = apply_filters( 'rehub_product_sections', array(
			'' => esc_html__('Template', 'rehub-framework'),
			'header' => esc_html__('Header', 'rehub-framework'),
			'footer' => esc_html__('Footer', 'rehub-framework'),
			'woosingle' => esc_html__('Product Single page', 'rehub-framework'),
			'wooarchive' => esc_html__('Product Archive page', 'rehub-framework'),
			)
		);
		echo '<div class="rehub-meta_factory-metabox">';
		foreach ($sections as $key => $value) {
			echo '<input type="radio" id="section_'.$key.'" name="_rh_section_type" value="'.$key.'" '.checked($key, $sectionmeta, false).'><label for="section_'.$key.'">'.$value.'</label><div class="rh-meta-divider"></div>';
		}
		echo '<br /><p>'.__('Check this if you want to use this template as your Header/Footer or Product Single page. After creation, you can select this template in theme options as your Header/Footer/Product layout', 'rehub-framework').'</p></div><style>#side_rh_section .postbox-header{border:none}</style></div>';
		wp_nonce_field( 'rehub_post_meta_save', 'rehub_post_meta_nonce' );
	}

	//Add Side panel Woocommerce
	public function wc_side_output($post){
		$meta = get_post_meta($post->ID, '_rh_woo_product_layout', true);
		echo '<div class="rehub-meta_factory-metabox"><select name="_rh_woo_product_layout" id="_rh_woo_product_layout" style="width:100%; margin: 10px 0">';
			$product_layouts = apply_filters( 'rehub_product_layout_array', array(
				'global' => esc_html__('Global from Theme option - Shop', 'rehub-framework'),
				'default_with_sidebar' => esc_html__('Default with sidebar', 'rehub-framework'),
				'default_full_width' => esc_html__('Default full width 2 column', 'rehub-framework'),
				'default_no_sidebar' => esc_html__('Default full width 3 column', 'rehub-framework'),
				'full_width_extended' => esc_html__('Full width Extended', 'rehub-framework'),
				'full_width_advanced' => esc_html__('Full width Advanced', 'rehub-framework'),
				'marketplace' => esc_html__('Full width Marketplace', 'rehub-framework'),
				'side_block' => esc_html__('Side Block', 'rehub-framework'),
				'side_block_light' => esc_html__('Side Block Light', 'rehub-framework'),
				'side_block_video' => esc_html__('Video Block', 'rehub-framework'),
				'sections_w_sidebar' => esc_html__('Sections with sidebar', 'rehub-framework'),
				'ce_woo_list' => esc_html__('Content Egg List', 'rehub-framework'),
				'ce_woo_sections' => esc_html__('Content Egg Auto Sections', 'rehub-framework'),
				'ce_woo_blocks' => esc_html__('Review with Blocks', 'rehub-framework'),
				'vendor_woo_list' => esc_html__('Compare Prices with shortcode', 'rehub-framework'),
				'compare_woo_list' => esc_html__('Compare Prices by sku', 'rehub-framework'),
				'full_photo_booking' => esc_html__('Full width Photo', 'rehub-framework'),
				'woo_compact' => esc_html__('Compact Style', 'rehub-framework'),
				'woo_directory' => esc_html__('Directory Style', 'rehub-framework'),
				'darkwoo' => esc_html__('Dark Layout', 'rehub-framework'),
				'woostack' => esc_html__('Photo Stack Layout', 'rehub-framework'),
				)
			);
			$productlayouts = get_posts(array(
				'post_type' => 'wp_block',
				'meta_key'   => '_rh_section_type',
				'meta_value' => 'woosingle'
			));
		
			if(!empty($productlayouts)){
				foreach($productlayouts as $layout){
					$product_layouts[$layout->ID] = get_the_title($layout->ID);
				}
			}
			foreach ($product_layouts as $key => $value) {
		    	echo '<option value="'.$key.'" '.selected($key, $meta).'>'.$value.'</option>';
			}
	    echo '</select>';
	    $badgemeta = get_post_meta($post->ID, 'is_editor_choice', true);
		$badges = apply_filters( 'rehub_product_badges', array(
			'no' => esc_html__('No Badge', 'rehub-framework'),
			'1' => (REHub_Framework::get_option('badge_label_1') !='') ? REHub_Framework::get_option('badge_label_1') : esc_html__('Editor choice', 'rehub-framework'),
			'2' => (REHub_Framework::get_option('badge_label_2') !='') ? REHub_Framework::get_option('badge_label_2') : esc_html__('Best seller', 'rehub-framework'),
			'3' => (REHub_Framework::get_option('badge_label_3') !='') ? REHub_Framework::get_option('badge_label_3') : esc_html__('Best value', 'rehub-framework'),
			'4' => (REHub_Framework::get_option('badge_label_4') !='') ? REHub_Framework::get_option('badge_label_4') : esc_html__('Best price', 'rehub-framework'),
			)
		);
		foreach ($badges as $key => $value) {
			echo '<input type="radio" id="badge_'.$key.'" name="is_editor_choice" value="'.$key.'" '.checked($key, $badgemeta, false).'><label for="badge_'.$key.'">'.$value.'</label><div class="rh-meta-divider"></div>';
		}
	    echo '<p>'.__('Check this if you want to show badge. You can customize them in theme option', 'rehub-framework').'</p></div>';
	}

	//Woo external product meta panels
	public function show_rehub_woo_meta_box_inner() {
		global $post;
	    $woo_custom_meta_fields = $this->meta_for_products();
    	// Begin the field table and loop
	    echo '<div class="options_group show_if_external">';
	    foreach ($woo_custom_meta_fields as $field) {
	        // get value of this field if it exists for this post
	        $meta = get_post_meta($post->ID, $field['id'], true);
	        // begin a table row with
	        echo '<p class="form-field rh_woo_meta_'.$field['id'].'">
	                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
	                <td>';
	                switch($field['type']) {
	                    // text
						case 'text':
						    echo '<input class="short" type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="70" />
						        <span class="description">'.$field['desc'].'</span>';
						break;
	                    // url
						case 'url':
						    echo '<input class="short" type="url" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="70" />
						        <span class="description">'.$field['desc'].'</span>';
						break;
						case 'textbox':
						    echo '<textarea cols=20 rows=2 class="short" name="'.$field['id'].'" id="'.$field['id'].'">'.$meta.'</textarea>
						        <span class="description">'.$field['desc'].'</span>';
						break;
						// checkbox
						case 'checkbox':
						    echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
						        <span class="description">'.$field['desc'].'</span>';
						break;
						// date
						case 'date':
							echo '<input class="short rehubdatepicker" type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="70" />
									<span class="description">'.$field['desc'].'</span>';
						break;
	                } //end switch
	        echo '</p>';
	    } // end foreach
	    echo '</div>'; // end table
	}

    //Custom code area Tab Woocommerce
	public function rh_custom_code_data_tab($product_data_tabs){
	    $product_data_tabs['rh-custom-code-tab'] = array(
	        'label' => esc_html__( 'Custom code areas', 'woocommerce' ),
	        'target' => 'rh_custom_code_section',
	    );
	    return $product_data_tabs;
	}

	//custom code area render fields
	public function rh_custom_code_data_fields() {
	    global $post;

	    ?> <div id = 'rh_custom_code_section'
	    class = 'panel woocommerce_options_panel' > <?php
	        ?> <div class = 'options_group' > <?php
			    woocommerce_wp_textarea_input( array( 'id' => 'rh_code_incart', 'class' => 'short', 'label' => esc_html__( 'Custom shortcode', 'rehub-framework' ), 'description' => esc_html__( 'Will be rendered near button', 'rehub-framework' )  ));
			    woocommerce_wp_textarea_input( array( 'id' => 'rehub_woodeals_short', 'class' => 'short', 'label' => esc_html__( 'Custom shortcode', 'rehub-framework' ), 'description' => esc_html__( 'Will be rendered before Content', 'rehub-framework' )  ));
			    woocommerce_wp_textarea_input( array( 'id' => 'woo_code_zone_footer', 'class' => 'short', 'label' => esc_html__( 'Custom shortcode', 'rehub-framework' ), 'description' => esc_html__( 'Will be rendered as Additional Section', 'rehub-framework' )  ));
			   	woocommerce_wp_text_input( array( 'id' => '_woo_code_bg', 'class' => 'short', 'label' => esc_html__( 'Custom background color', 'rehub-framework' ), 'description' => esc_html__( 'Example: #dddddd or lightgrey', 'rehub-framework' )  ));
	        ?> </div>

	    </div><?php
	}


	/********************************
	 * Save Meta functions
	 ********************************/

	//save Metas
	public function save_single_meta_field($field, $post_id){
		if ( $field['type'] === 'group' ) {
			$group_fields = $field['fields'];

			foreach ( $group_fields as $key ) {
				$value    = '';
				$field_id = $field[ 'id' ];

				$prefixed_field_id = isset( $_POST[$field_id] ) ? $field_id : '';

				if ( ! $prefixed_field_id ) {
					continue;
				}

				$value = $this->sanitize_value_for_db( $_POST[ $prefixed_field_id ], $field, $key );

				if ( $value ) {
					update_post_meta( $post_id, $prefixed_field_id, $value );
				} else {
					delete_post_meta( $post_id, $prefixed_field_id );
				}
			}
		}else{
			$old = get_post_meta($post_id, $field['id'], true);
			$input = (!empty($_POST[$field['id']])) ? $_POST[$field['id']] : '';
			if ($input) {
				$new = $this->sanitize_value_for_db( $input, $field);
			}
			else {
			   $new ='';
			}
			if ($new && $new != $old) {
				update_post_meta($post_id, $field['id'], $new);
				if($field['id'] == 're_post_expired'){ // Update Expiration Taxonomy
					wp_set_object_terms($post_id, 'yes', 'offerexpiration', false );
				}
			} elseif ('' == $new && $old) {
				delete_post_meta($post_id, $field['id'], $old);
				if($field['id'] == 're_post_expired'){ // Update Expiration Taxonomy
					wp_set_object_terms($post_id, NULL, 'offerexpiration', false );
				}
			}
		}
	}

	public function save_meta_boxes( $post_id, $post ) {
		// $post_id is required
		if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
			return;
		}

		$posttype = $post->post_type;

		// Dont' save meta boxes for revisions or autosaves
		if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || is_int(wp_is_post_revision($post_id))  ) {
			return $post_id;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return $post_id;
		}

		// Check the nonce
		if ( empty( $_POST['rehub_post_meta_nonce'] ) || !wp_verify_nonce( $_POST['rehub_post_meta_nonce'], 'rehub_post_meta_save' ) ) {
			return $post_id;
		}

		// Check user has permission to edit
	    if ( 'page' == $posttype ) {
	        if (!current_user_can('edit_page', $post_id)) return $post_id;
	    } elseif (!current_user_can('edit_post', $post_id)) {
	        return $post_id;
	    }


		// Check the post type
		$def_p_types = rh_get_post_type_formeta();
		$def_p_types[] = 'blog';

		if (in_array($posttype, $def_p_types)){

			//Saving gallery
			if( !empty($_POST['rh_post_image_gallery']) && !is_array($_POST['rh_post_image_gallery'])){
				$attachment_ids = sanitize_text_field( $_POST['rh_post_image_gallery']);
				$attachment_ids = explode(",", $attachment_ids);
				$attachment_ids = array_filter($attachment_ids);
				$attachment_ids = implode(',', $attachment_ids);
				update_post_meta( $post_id, 'rh_post_image_gallery', $attachment_ids );
			}elseif(isset($_POST['rh_post_image_gallery'])){
				delete_post_meta( $post_id, 'rh_post_image_gallery' );
			}

		    // loop through fields and save the data
		    $post_custom_meta_fields = $this->meta_for_posts();
			$post_high_meta_fields = $this->meta_for_posts_side_high();
			$post_custom_meta_fields = array_merge($post_custom_meta_fields, $post_high_meta_fields);
		    $post_custom_meta_fields[] =  array(
		        'id' => 'rh_post_image_videos', //we add here video field
		        'type' => 'textbox',
		    );
		    foreach ($post_custom_meta_fields as $field) {
				$this->save_single_meta_field($field, $post_id); //save or remove each field
		    } // end foreach

		    self::$saved_meta_boxes = true;

		}elseif($posttype == 'product'){

		    // loop through fields and save the data
		    $woo_custom_meta_fields = $this->meta_for_products();
		    $woo_reviews_custom_meta_fields = $this->meta_for_reviews_products();

		    $woo_custom_meta_fields = array_merge($woo_custom_meta_fields, $woo_reviews_custom_meta_fields);

		    if (isset ($_POST['rh_product_video'])) {
			    $woo_custom_meta_fields[] =  array(
			        'id' => 'rh_product_video', //we add here video field
			        'type' => 'textbox',

			    );
		    }
		    if (isset ($_POST['rh_td_model'])) {
			    $woo_custom_meta_fields[] =  array(
			        'id' => 'rh_td_model', //we add here 3d field
			        'type' => 'textbox',

			    );
		    }
		    if (isset ($_POST['rh_td_model_usdz'])) {
			    $woo_custom_meta_fields[] =  array(
			        'id' => 'rh_td_model_usdz', //we add here 3d field
			        'type' => 'textbox',

			    );
		    }
		    if (isset ($_POST['rh_code_incart'])) {
				$woo_custom_meta_fields[] = array(
			        'id'    => 'rh_code_incart',
			        'type' => 'textbox',
			    );
		    }

		    if (isset ($_POST['_rh_woo_product_layout'])) {
				$woo_custom_meta_fields[] = array(
			        'id'    => '_rh_woo_product_layout',
			        'type' => 'text',
			    );
		    }

		    if (isset ($_POST['is_editor_choice'])) {
		    	if($_POST['is_editor_choice'] == 'no'){
		    		delete_post_meta($post_id, 'is_editor_choice');
		    	}else{
					$woo_custom_meta_fields[] = array(
				        'id'    => 'is_editor_choice',
				        'type' => 'text',
				    );
		    	}
		    }

		    if (isset ($_POST['rehub_woodeals_short'])) {
				$woo_custom_meta_fields[] = array(
			        'id'    => 'rehub_woodeals_short',
			        'type' => 'textbox',
			    );
		    }

		    if (isset ($_POST['woo_code_zone_footer'])) {
				$woo_custom_meta_fields[] = array(
			        'id'    => 'woo_code_zone_footer',
			        'type' => 'textbox',
			    );
		    }
		    if (isset ($_POST['_woo_code_bg'])) {
				$woo_custom_meta_fields[] = array(
			        'id'    => '_woo_code_bg',
			        'type' => 'text',
			    );
		    }

		    foreach ($woo_custom_meta_fields as $field) {
				$this->save_single_meta_field($field, $post_id); //save or remove each field
		    } // end foreach

		    //Save Review of Editor
			$thecriteria = (!empty($_POST['_review_post_criteria'])) ? $_POST['_review_post_criteria'] : array();
			//array_pop($thecriteria);
			$manual_score = $_POST['_review_post_score_manual'];

			$score = 0; $total_counter = 0;
			if (!empty($thecriteria))  {
			    foreach ($thecriteria as $criteria) {
			    	$score += (float) $criteria['review_post_score']; $total_counter ++;
			    }
			}
		    if ($manual_score)  {
		    	$total_score = $manual_score;
		    }
		    else {
				if( !empty( $score ) && !empty( $total_counter ) ) $total_score =  $score / $total_counter ;
				if( empty($total_score) ) $total_score = 0;
				$total_score = round($total_score,1);
			}

			if($total_score){
				update_post_meta($post_id, 'rehub_review_overall_score', $total_score); // save total score of review
				$firstcriteria = (!empty($thecriteria[0]['review_post_name'])) ? $thecriteria[0]['review_post_name'] : '';
				if($firstcriteria) :
					foreach ($thecriteria as $key=>$criteria) {
						$key = $key + 1;
						$metakey = '_review_score_criteria_'.$key;
						update_post_meta($post_id, $metakey, $criteria['review_post_score']);
					}
				endif;
			}
			elseif($manual_score==0){
				delete_post_meta($post_id, 'rehub_review_overall_score');
			}

			//Saving gallery
			if( !empty($_POST['rh_post_image_gallery']) && !is_array($_POST['rh_post_image_gallery'])){
				$attachment_ids = sanitize_text_field( $_POST['rh_post_image_gallery']);
				$attachment_ids = explode(",", $attachment_ids);
				$attachment_ids = array_filter($attachment_ids);
				$attachment_ids = implode(',', $attachment_ids);
				update_post_meta( $post_id, 'rh_post_image_gallery', $attachment_ids );
			}elseif(isset($_POST['rh_post_image_gallery'])){
				delete_post_meta( $post_id, 'rh_post_image_gallery' );
			}


			self::$saved_meta_boxes = true;
		}elseif($posttype == 'wp_block'){
			if (isset ($_POST['_rh_section_type'])) {
				$value = sanitize_text_field( $_POST['_rh_section_type']);
				update_post_meta( $post_id, '_rh_section_type', $value);
		    }
		}

		if($posttype == 'page'){
		    $page_custom_meta_fields = $this->meta_for_page_side();
		    foreach ($page_custom_meta_fields as $field) {
				$this->save_single_meta_field($field, $post_id); //save or remove each field
			}			
		}
	}


	/********************************
	 * Taxonomy fields
	 ********************************/

	// A callback function to edit a custom field to our "deal brand" taxonomy
	public function rhwoostore_tax_fields_edit($term, $taxonomy) {
	    wp_nonce_field( basename( __FILE__ ), 'rhwoostore_nonce' );
	    $rh_woostore_tax_meta = $this->meta_for_brand_cat();
	    if($taxonomy != 'dealstore' && $taxonomy != 'store'){
	        unset($rh_woostore_tax_meta[0]);
	        unset($rh_woostore_tax_meta[1]);
	        unset($rh_woostore_tax_meta[2]);
	        unset($rh_woostore_tax_meta[3]);
	        if($taxonomy != 'product_cat' && $taxonomy != 'category' && $taxonomy != 'product_tag'){
	            unset($rh_woostore_tax_meta[5]);
	        }
	    }
	    if (function_exists('wp_enqueue_media')) {wp_enqueue_media();}
	    ?>
	    <?php foreach ($rh_woostore_tax_meta as $field) :?>
	        <?php $term_meta = get_term_meta( $term->term_id, $field['id'], true );?>
	        <tr class="form-field">
	            <th scope="row" valign="top">
	                <label for="<?php echo ''.$field['id'];?>"><?php echo ''.$field['label'];?></label>
	            </th>
	            <td>
	                <?php if ($field['type'] == 'text') :?>
	                    <input name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" value="<?php echo ''.$term_meta ? $term_meta : ''; ?>" class="wpsm_tax_text_field" type="text" size="40" /><br /><br />
	                <?php elseif ($field['type'] == 'url') :?>
	                    <input name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" value="<?php echo ''.$term_meta ? $term_meta : ''; ?>" class="wpsm_tax_url_field" type="url" size="40" /><br /><br />
	                <?php elseif($field['type'] == 'textarea'):?>
	                    <?php
	                    $meta_content = $term_meta ? wpautop($term_meta) : '';
	                    wp_editor( $meta_content, $field['id'], array(
	                            'wpautop' =>  true,
	                            'media_buttons' => false,
	                            'textarea_name' => $field['id'],
	                            'textarea_rows' => 10,
	                            'teeny' =>  false
	                    ));
	                    ?>
	                    <p class="description"><?php echo ''.$field['desc'];?></p><br /><br />
	                <?php elseif($field['type'] == 'image'):?>
	                    <script>
	                    jQuery(document).ready(function ($) {
	                    //Image helper
	                        var imageFrame;jQuery(".wpsm_tax_helper_upload_image_button").click(function(e){e.preventDefault();return $self=jQuery(e.target),$div=$self.closest("div.wpsm_tax_helper_image"),imageFrame?void imageFrame.open():(imageFrame=wp.media({title:"Choose Image",multiple:!1,library:{type:"image"},button:{text:"Use This Image"}}),imageFrame.on("select",function(){selection=imageFrame.state().get("selection"),selection&&selection.each(function(e){console.log(e);{var t=e.attributes.url;e.id}$div.find(".wpsm_tax_helper_preview_image").attr("src",t),$div.find(".wpsm_tax_helper_upload_image").val(t)})}),void imageFrame.open())}),jQuery(".wpsm_tax_helper_clear_image_button").click(function(){var e='';return jQuery(this).parent().siblings(".wpsm_tax_helper_upload_image").val(""),jQuery(this).parent().siblings(".wpsm_tax_helper_preview_image").attr("src",e),!1});
	                    });
	                    </script>
	                    <div class="wpsm_tax_helper_image">
	                        <img src="<?php echo ''.$term_meta ? esc_url($term_meta) : get_template_directory_uri().'/images/default/noimage_70_70.png'; ?>" class="wpsm_tax_helper_preview_image" alt="image" style="max-height: 80px" />
	                        <p class="description"><?php echo ''.$field['desc'];?></p>
	                        <input type="url" name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" size="25" style="width:60%;" value="<?php echo ''.$term_meta ? esc_url($term_meta) : ''; ?>" class="wpsm_tax_helper_upload_image" />
	                        <a href="#" class="wpsm_tax_helper_upload_image_button button" rel=""><?php esc_html_e('Choose Image', 'rehub-framework'); ?></a>
	                        <small>&nbsp;<a href="#" class="wpsm_tax_helper_clear_image_button button">X</a></small>
	                        <br /><br />
	                    </div>
	                <?php endif;?>
	            </td>
	        </tr>
	    <?php endforeach;?>

	    <?php
	}

	// A callback function to add a custom field to our "deal brand" taxonomy
	public function rhwoostore_tax_fields_new($taxonomy) {
	    wp_nonce_field( basename( __FILE__ ), 'rhwoostore_nonce' );
	    if (function_exists('wp_enqueue_media')) {wp_enqueue_media();}
	    $rh_woostore_tax_meta = $this->meta_for_brand_cat();
	    if($taxonomy != 'dealstore' && $taxonomy != 'store'){
	        unset($rh_woostore_tax_meta[0]);
	        unset($rh_woostore_tax_meta[1]);
	        unset($rh_woostore_tax_meta[2]);
	        unset($rh_woostore_tax_meta[3]);
	        if($taxonomy != 'product_cat' && $taxonomy != 'category' && $taxonomy != 'product_tag'){
	            unset($rh_woostore_tax_meta[5]);
	        }
	    }
	    ?>
	    <?php foreach ($rh_woostore_tax_meta as $field) :?>
	        <div class="form-field">
	            <label for="<?php echo ''.$field['id'];?>"><?php echo ''.$field['label'];?></label>
	            <?php if ($field['type'] == 'text') :?>
	                <input name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" value="" class="wpsm_tax_text_field" type="text" /><br /><br />
	            <?php elseif ($field['type'] == 'url') :?>
	                <input name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" value="" class="wpsm_tax_text_field" type="url" /><br /><br />
	            <?php elseif($field['type'] == 'textarea'):?>
	                <textarea name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" class="wpsm_tax_textarea_field" rows="5" cols="40"></textarea><p class="description"><?php echo ''.$field['desc'];?></p><br /><br />
	            <?php elseif($field['type'] == 'image'):?>
	                <script>
	                jQuery(document).ready(function ($) {
	                //Image helper
	                    var imageFrame;jQuery(".wpsm_tax_helper_upload_image_button").click(function(e){e.preventDefault();return $self=jQuery(e.target),$div=$self.closest("div.wpsm_tax_helper_image"),imageFrame?void imageFrame.open():(imageFrame=wp.media({title:"Choose Image",multiple:!1,library:{type:"image"},button:{text:"Use This Image"}}),imageFrame.on("select",function(){selection=imageFrame.state().get("selection"),selection&&selection.each(function(e){console.log(e);{var t=e.attributes.url;e.id}$div.find(".wpsm_tax_helper_preview_image").attr("src",t),$div.find(".wpsm_tax_helper_upload_image").val(t)})}),void imageFrame.open())}),jQuery(".wpsm_tax_helper_clear_image_button").click(function(){var e='';return jQuery(this).parent().siblings(".wpsm_tax_helper_upload_image").val(""),jQuery(this).parent().siblings(".wpsm_tax_helper_preview_image").attr("src",e),!1});
	                });
	                </script>
	                <div class="wpsm_tax_helper_image">
	                    <img src="<?php echo get_template_directory_uri().'/images/default/noimage_70_70.png';?>" class="wpsm_tax_helper_preview_image" alt="image" style="max-height: 80px" />
	                    <p class="description"><?php echo ''.$field['desc'];?></p>
	                    <input type="url" name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" size="25" style="width:60%;" value="" class="wpsm_tax_helper_upload_image" />
	                    <a href="#" class="wpsm_tax_helper_upload_image_button button" rel=""><?php esc_html_e('Choose Image', 'rehub-framework'); ?></a>
	                    <small>&nbsp;<a href="#" class="wpsm_tax_helper_clear_image_button button">X</a></small>
	                    <br /><br />
	                </div>
	            <?php endif;?>
	        </div>
	    <?php endforeach;?>
	    <?php
	}

	// A callback function to save our extra taxonomy field(s)
	public function rhwoostore_tax_fields_save( $term_id, $tt_id) {
	    $rh_woostore_tax_meta = $this->meta_for_brand_cat();
	    if (!empty($_POST['rhwoostore_nonce'])){
	        $rhwoostore_nonce = $_POST['rhwoostore_nonce'];
	    }else{
	        return;
	    }
	    if ( ! wp_verify_nonce($rhwoostore_nonce, basename( __FILE__ ) ) || !current_user_can('manage_categories'))
	        return;
	    // loop through fields and save the data
	    foreach ($rh_woostore_tax_meta as $field) {
	        $old = get_term_meta($term_id, $field['id'], true);
	        if (isset ($_POST[$field['id']])) {
	            if ($field['type'] == 'image'){
	                $new = esc_url($_POST[$field['id']]);
	            }
	            elseif($field['type'] == 'text'){
	                $new = sanitize_text_field($_POST[$field['id']]);
	            }
	            elseif($field['type'] == 'url'){
	                $new = esc_url($_POST[$field['id']]);
	            }
	            else{
	                $new = wp_kses_post($_POST[$field['id']]);
	            }
	        }
	        else {
	           $new ='';
	        }
	        if ($new && $new != $old) {
	            update_term_meta($term_id, $field['id'], $new);
	        } elseif ('' == $new && $old) {
	            delete_term_meta($term_id, $field['id'], $old);
	        }
	    } // end foreach
	}

	// Init woocommerce taxonomy field
	public function rhwoostore_tax_fields() {
		if(class_exists('Woocommerce')){
		    add_action( 'store_edit_form_fields', array( $this, 'rhwoostore_tax_fields_edit'), 10, 2 );
		    add_action( 'store_add_form_fields', array( $this, 'rhwoostore_tax_fields_new'));
		    add_action( 'edited_store', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
		    add_action( 'create_store', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
		    add_action( 'product_cat_edit_form_fields', array( $this, 'rhwoostore_tax_fields_edit'), 10, 2 );
		    add_action( 'product_cat_add_form_fields', array( $this, 'rhwoostore_tax_fields_new'));
		    add_action( 'edited_product_cat', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
		    add_action( 'create_product_cat', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
		    add_action( 'product_tag_edit_form_fields', array( $this, 'rhwoostore_tax_fields_edit'), 10, 2 );
		    add_action( 'product_tag_add_form_fields', array( $this, 'rhwoostore_tax_fields_new'));
		    add_action( 'edited_product_tag', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
		    add_action( 'create_product_tag', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );

		    add_action( 'blog_category_edit_form_fields', array( $this, 'rhwoostore_tax_fields_edit'), 10, 2 );
		    add_action( 'blog_category_add_form_fields', array( $this, 'rhwoostore_tax_fields_new'));
		    add_action( 'edited_blog_category', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
		    add_action( 'create_blog_category', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
	    }
	}

	// Init Affiliate store taxonomy field
    function dealstore_tax_fields() {
        add_action( 'dealstore_edit_form_fields', array( $this, 'rhwoostore_tax_fields_edit'), 10, 2 );
        add_action( 'dealstore_add_form_fields', array( $this, 'rhwoostore_tax_fields_new'));
        add_action( 'edited_dealstore', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
        add_action( 'create_dealstore', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
    }

	// Init Category taxonomy field
    function category_tax_fields() {
        add_action( 'category_edit_form_fields', array( $this, 'rhwoostore_tax_fields_edit'), 10, 2 );
        add_action( 'category_add_form_fields', array( $this, 'rhwoostore_tax_fields_new'));
        add_action( 'edited_category', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
        add_action( 'create_category', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
    }


	/********************************
	 * Elementor fields
	 ********************************/

	// A callback function for Templates Elementor
	function rhe_shortcode_box($post){
	    ?>
	    <h4 style="margin-bottom:5px;"><?php esc_html_e('Shortcode', 'rehub-framework');?></h4>
	    <input type='text' class='widefat' value='[RH_ELEMENTOR id="<?php echo $post->ID; ?>"]' readonly="">
	    <h4 style="margin-bottom:5px;"><?php esc_html_e('Shortcode with caching (24 hours)', 'rehub-framework');?></h4>
	    <input type='text' class='widefat' value='[RH_ELEMENTOR id="<?php echo $post->ID; ?>" cache=1 expire=24]' readonly="">
	    <h4 style="margin-bottom:5px;"><?php esc_html_e('Ajax loaded on Hover and trigger classes', 'rehub-framework');?></h4>
	    <input type='text'  style="margin-bottom:5px;" class='widefat' value='[RH_ELEMENTOR id="<?php echo $post->ID; ?>" ajax=1]' readonly="">
	    <input type='text' class='widefat' value='rh-el-onhover load-block-<?php echo $post->ID; ?>' readonly="">
	    <input type='text' class='widefat' value='rh-el-onclick load-block-<?php echo $post->ID; ?>' readonly="">
	    <input type='text' class='widefat' value='rh-el-onview load-block-<?php echo $post->ID; ?>' readonly="">
	    <h4 style="margin-bottom:5px;"><?php esc_html_e('Auto render by view', 'rehub-framework');?></h4>
	    <input type='text'  style="margin-bottom:5px;" class='widefat' value='[RH_ELEMENTOR id="<?php echo $post->ID; ?>" ajax=1 render=1 height=100px]' readonly="">
	    <h4 style="margin-bottom:5px;"><?php esc_html_e('Php code', 'rehub-framework');?></h4>
	    <input type='text' class='widefat' value="&lt;?php echo do_shortcode('[RH_ELEMENTOR id=&quot;<?php echo $post->ID; ?>&quot;]'); ?&gt;" readonly="">
	    <?php
	}

	public function register_meta_rest(){
		register_meta( 'post', 'rehub_offer_product_url', array(
			'type'              => 'string',
			'description'       => __('Offer url', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'rehub_offer_disclaimer', array(
			'type'              => 'string',
			'description'       => __('Offer disclaimer', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'rehub_offer_product_desc', array(
			'type'              => 'string',
			'description'       => __('Offer description', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'rehub_offer_product_price_old', array(
			'type'              => 'string',
			'description'       => __('Offer Old price', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'rehub_offer_product_price', array(
			'type'              => 'string',
			'description'       => __('Offer Sale price', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'rehub_offer_product_coupon', array(
			'type'              => 'string',
			'description'       => __('Offer Coupon', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'rehub_woo_coupon_code', array(
			'type'              => 'string',
			'description'       => __('Woocommerce Coupon', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'rehub_offer_coupon_date', array(
			'type'              => 'string',
			'description'       => __('Offer Expiration date', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', '_notice_custom', array(
			'type'              => 'string',
			'description'       => __('Custom notice', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'rehub_offer_verify_label', array(
			'type'              => 'string',
			'description'       => __('Custom notice', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'rehub_views', array(
			'type'              => 'integer',
			'description'       => __('Post views', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'is_editor_choice', array(
			'type'              => 'integer',
			'description'       => __('Post badge', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'post_hot_count', array(
			'type'              => 'integer',
			'description'       => __('Hot counter', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'post_wish_count', array(
			'type'              => 'integer',
			'description'       => __('Wish counter', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
		register_meta( 'post', 'rehub_review_overall_score', array(
			'type'              => 'integer',
			'description'       => __('Review score', 'rehub-framework'),
			'single'            => true,
			'sanitize_callback' => 'rh_sanitize_custom_meta',
			'auth_callback'     => 'rh_auth_custom_meta',
			'show_in_rest'      => true,
		) );
	}

}
new RH_Meta_Box_Post();