<?php
/**
 * Attribute Swathes class
 * @version 1.0.4
 */

defined( 'ABSPATH' ) || exit;

class RH_Attribute_Swatches{

	public $meta_color = 'rh_swatch_color';
	public $meta_image = 'rh_swatch_image';
	public $meta_text = 'rh_swatch_text';
	public $option_name = 'rh_wc_swatches';

	function __construct(){
		global $pagenow;

		if( 'edit.php' === $pagenow && isset( $_GET['page'] ) && 'product_attributes' === $_GET['page'] ) $this->init_attribute_hooks();
		
		if( 'term.php' === $pagenow || 'edit-tags.php' === $pagenow || ( isset( $_POST['action'] ) && 'add-tag' === $_POST['action'] ) ) $this->init_term_hooks();
		
		add_action( 'woocommerce_product_option_terms', array( $this, 'add_attribute_to_product' ), 10, 3 );
	}
	
	/* Initialization attribute hooks */
	function init_attribute_hooks(){
		add_filter( 'product_attributes_type_selector', array( $this, 'attribute_swatch_column' ) );
		add_action( 'woocommerce_attribute_added', array( $this, 'save_attribute_fields' ), 10, 2 );
		add_action( 'woocommerce_attribute_updated', array( $this, 'save_attribute_fields' ), 10, 2 );
		add_action( 'woocommerce_before_attribute_delete', array( $this, 'delete_attribute' ), 10, 3 );
	}
	
	/* Initialization attribute term hooks */
	function init_term_hooks(){
		$swatch_options = get_option( $this->option_name, array() );
		
		if ( ! empty( $swatch_options ) ) :
			foreach ( $swatch_options as $attribute_id => $swatch_type ) :
				$taxonomy = wc_attribute_taxonomy_name_by_id( (int) $attribute_id );
				if( $taxonomy ){
					add_action( "{$taxonomy}_add_form_fields", array( $this, "add_{$swatch_type}_field" ) );
					add_action( "{$taxonomy}_edit_form_fields", array( $this, "edit_{$swatch_type}_field" ) );
					add_filter( "manage_edit-{$taxonomy}_columns", array( $this, "swatch_columns" ) );
					add_filter( "manage_{$taxonomy}_custom_column", array( $this, "swatch_{$swatch_type}_column" ), 10, 3 );
				}
			endforeach;
		endif;
		
		add_action( 'created_term', array( $this, 'save_term_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_term_fields' ), 10, 3 );
		add_action( 'pre_delete_term', array( $this, 'delete_term_fields' ), 10, 2 );
	}
	
	/* Adds attribute types */
	function attribute_swatch_column( $type ){
		$type = array( 
			'select' => esc_html__( 'Select', 'rehub-theme' ),
			'color' => esc_html__( 'Color', 'rehub-theme' ), 
			'image' => esc_html__( 'Image', 'rehub-theme' ), 
			'text' => esc_html__( 'Text tag', 'rehub-theme' ) 
		);
		return $type;
	}
	
	/* Saves custom attribute type to options */
	function save_attribute_fields( $attribute_id, $data ){
		$swatch_options = get_option( $this->option_name, array() );
		if( 'select' !== $data['attribute_type'] ){
			$swatch_options[$attribute_id] = $data['attribute_type'];
		}else{
			unset( $swatch_options[$attribute_id] );
		}
		update_option( $this->option_name, $swatch_options );
	}

	/* Adds 'Color' fields to Attribute term creating form */
	function add_color_field(){
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_add_inline_script( 'wp-color-picker', 'jQuery(document).ready(function($){$("#term-color").wpColorPicker();});');
		?>
		<div class="form-field term-color-wrap">
			<label for="term-color"><?php esc_html_e( 'Swatch Color', 'rehub-theme' ); ?></label>
			<input name="swatch_color" id="term-color" type="text" value="" size="40" />
		</div>
		<?php
	}

	/* Adds 'Color' fields to Attribute term editing form */
	function edit_color_field( $tag ){
		$hex_color = get_term_meta( $tag->term_id, $this->meta_color, true );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_add_inline_script( 'wp-color-picker', 'jQuery(document).ready(function($){$("#term-color").wpColorPicker();});');
		?>
			<tr class="form-field term-color-wrap">
				<th scope="row"><label for="term-color"><?php esc_html_e( 'Swatch Color', 'rehub-theme' ); ?></label></th>
				<td><input name="swatch_color" id="term-color" type="text" value="<?php echo esc_html($hex_color); ?>" size="40" /></td>
			</tr>
		<?php
	}
	
	/* Adds 'Image' fields to Attribute term creating form */
	function add_image_field(){
		wp_enqueue_media();
		?>
		<div class="form-field term-image-wrap">
			<label><?php esc_html_e( 'Swatch image', 'rehub-theme' ); ?></label>
			<div id="term-image-preview" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width="60px" height="60px" /></div>
			<div style="line-height: 60px;">
				<input type="hidden" id="term-image" name="swatch_image" />
				<button type="button" class="upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'rehub-theme' ); ?></button>
				<button type="button" class="remove_image_button button"><?php esc_html_e( 'Remove image', 'rehub-theme' ); ?></button>
			</div>
			<script type="text/javascript">
				if ( ! jQuery( '#term-image' ).val() ) {
					jQuery( '.remove_image_button' ).hide();
				}
				var file_frame;
				jQuery( document ).on( 'click', '.upload_image_button', function( event ) {
					event.preventDefault();
					if ( file_frame ) {
						file_frame.open();
						return;
					}
					file_frame = wp.media.frames.downloadable_file = wp.media({
						title: '<?php esc_html_e( 'Choose an image', 'rehub-theme' ); ?>',
						button: {
							text: '<?php esc_html_e( 'Use image', 'rehub-theme' ); ?>'
						},
						multiple: false
					});
					file_frame.on( 'select', function() {
						var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
						var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;
						jQuery( '#term-image' ).val( attachment.id );
						jQuery( '#term-image-preview' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
						jQuery( '.remove_image_button' ).show();
					});
					file_frame.open();
				});
				jQuery( document ).on( 'click', '.remove_image_button', function() {
					jQuery( '#term-image-preview' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
					jQuery( '#term-image' ).val( '' );
					jQuery( '.remove_image_button' ).hide();
					return false;
				});
				jQuery( document ).ajaxComplete( function( event, request, options ) {
					if ( request && 4 === request.readyState && 200 === request.status
						&& options.data && 0 <= options.data.indexOf( 'action=add-tag' ) ) {
						var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
						if ( ! res || res.errors ) {
							return;
						}
						jQuery( '#term-image-preview' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
						jQuery( '#term-image' ).val( '' );
						jQuery( '.remove_image_button' ).hide();
						return;
					}
				} );
			</script>
			<div class="clear"></div>
		</div>
		<?php
	}
	
	/* Adds 'Image' fields to Attribute term editing form */
	function edit_image_field( $tag ){
		$image_id = absint( get_term_meta( $tag->term_id, $this->meta_image, true ) );
		$image = ( $image_id ) ? wp_get_attachment_thumb_url( $image_id ) : wc_placeholder_img_src();
		wp_enqueue_media();
		?>
		<tr class="form-field term-image-wrap">
			<th scope="row" valign="top"><label><?php esc_html_e( 'Swatch image', 'rehub-theme' ); ?></label></th>
			<td>
				<div id="term-image-preview" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( $image ); ?>" width="60px" height="60px" /></div>
				<div style="line-height: 60px;">
					<input type="hidden" id="term-image" name="swatch_image" value="<?php echo esc_html($image_id); ?>" />
					<button type="button" class="upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'rehub-theme' ); ?></button>
					<button type="button" class="remove_image_button button"><?php esc_html_e( 'Remove image', 'rehub-theme' ); ?></button>
				</div>
				<script type="text/javascript">
					if ( '0' === jQuery( '#term-image' ).val() ) {
						jQuery( '.remove_image_button' ).hide();
					}
					var file_frame;
					jQuery( document ).on( 'click', '.upload_image_button', function( event ) {
						event.preventDefault();
						if ( file_frame ) {
							file_frame.open();
							return;
						}
						file_frame = wp.media.frames.downloadable_file = wp.media({
							title: '<?php esc_html_e( 'Choose an image', 'rehub-theme' ); ?>',
							button: {
								text: '<?php esc_html_e( 'Use image', 'rehub-theme' ); ?>'
							},
							multiple: false
						});
						file_frame.on( 'select', function() {
							var attachment = file_frame.state().get( 'selection' ).first().toJSON();
							var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;
							jQuery( '#term-image' ).val( attachment.id );
							jQuery( '#term-image-preview' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
							jQuery( '.remove_image_button' ).show();
						});
						file_frame.open();
					});
					jQuery( document ).on( 'click', '.remove_image_button', function() {
						jQuery( '#term-image-preview' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
						jQuery( '#term-image' ).val( '' );
						jQuery( '.remove_image_button' ).hide();
						return false;
					});
				</script>
				<div class="clear"></div>
			</td>
		</tr>
		<?php
	}

	/* Adds 'Text' fields to Attribute term creating form */
	function add_text_field(){
		?>
		<div class="form-field term-text-wrap">
			<label for="term-text"><?php esc_html_e( 'Swatch Text', 'rehub-theme' ); ?></label>
			<input name="swatch_text" id="term-text" type="text" value="" size="40" />
		</div>
		<?php
	}

	/* Adds 'Text' fields to Attribute term editing form */
	function edit_text_field( $tag ){
		$text = get_term_meta( $tag->term_id, $this->meta_text, true );
		?>
			<tr class="form-field term-text-wrap">
				<th scope="row"><label for="term-text"><?php esc_html_e( 'Swatch Text', 'rehub-theme' ); ?></label></th>
				<td><input name="swatch_text" id="term-text" type="text" value="<?php echo esc_html($text); ?>" size="40" /></td>
			</tr>
		<?php
	}

	/* Saves term swatch fiekds */
	function save_term_fields( $term_id, $tt_id = '', $taxonomy = '' ){
		if ( isset( $_POST['swatch_color'] ) ) {
			update_woocommerce_term_meta( $term_id, $this->meta_color, sanitize_hex_color( $_POST['swatch_color'] ) );
		}
		if ( isset( $_POST['swatch_image'] ) ) {
			update_woocommerce_term_meta( $term_id, $this->meta_image, absint( $_POST['swatch_image'] ) );
		}
		if ( isset( $_POST['swatch_text'] ) ) {
			update_woocommerce_term_meta( $term_id, $this->meta_text, sanitize_text_field( $_POST['swatch_text'] ) );
		}
	}
	
	/* Deletes term swatch meta during term deleting */
	function delete_term_fields( $term_id, $taxonomy ){
		if( taxonomy_exists( $taxonomy ) ){
			$attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
			$attribute = wc_get_attribute( $attribute_id );
			if( $attribute && 'select' !== $attribute->type ){
				$this->delete_term_meta( $attribute, $term_id );
			}
		}
	}
	
	/* Deletes term swatch meta during attribute deleting */
	function delete_attribute( $attribute_id, $name, $taxonomy ){
		$attribute = wc_get_attribute( $attribute_id );
		
		if ( taxonomy_exists( $taxonomy ) && 'select' !== $attribute->type ) {
			$terms = get_terms( $taxonomy, 'orderby=name&hide_empty=0' );
			foreach ( $terms as $term ) {
				$this->delete_term_meta( $attribute, $term->term_id );
			}
			
			$swatch_options = get_option( $this->option_name, array() );
			
			if( array_key_exists( $attribute_id, $swatch_options ) ){
				unset( $swatch_options[$attribute_id] );
				update_option( $this->option_name, $swatch_options );
			}
		}
	}
	
	/* Loop function for deleting term swatch metas */
	function delete_term_meta( $attribute, $term_id ){
		if( is_object( $attribute ) ) :
			switch( $attribute->type ){
				case 'color':
					$meta_name = $this->meta_color;
					break;
				case 'image':
					$meta_name = $this->meta_image;
					break;
				case 'text':
					$meta_name = $this->meta_text;
					break;
				default:
					$meta_name = '';
			}
			if( !empty( $meta_name ) ) {
				delete_woocommerce_term_meta( $term_id, $meta_name );
				return true;
			}
		endif;
	}
	
	/* Adds Swatch header column to term table list */
	public function swatch_columns( $columns ) {
		$new_columns = array();
		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
			unset( $columns['cb'] );
		}
		$new_columns['swatch'] = esc_html__( 'Swatch', 'rehub-theme' );
		$columns = array_merge( $new_columns, $columns );
		$columns['handle'] = '';
		return $columns;
	}
	
	/* Adds Swatch Image column to term table list */
	function swatch_image_column( $columns, $column, $term_id ) {
		if ( 'swatch' === $column ) {
			$thumbnail_id = get_term_meta( $term_id, $this->meta_image, true );
			if ( $thumbnail_id ) {
				$image = wp_get_attachment_thumb_url( $thumbnail_id );
			} else {
				$image = wc_placeholder_img_src();
			}
			$image    = str_replace( ' ', '%20', $image );
			$columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Swatch', 'rehub-theme' ) . '" class="wp-post-image" height="48" width="48" />';
		}
		if ( 'handle' === $column ) {
			$columns .= '<input type="hidden" name="term_id" value="' . esc_attr( $term_id ) . '" />';
		}
		return $columns;
	}

	/* Adds Swatch Color column to term table list */
	function swatch_color_column( $columns, $column, $term_id ) {
		if ( 'swatch' === $column ) {
			$color = get_term_meta( $term_id, $this->meta_color, true );
			$color = trim( $color );
			$columns .= '<div style="background-color:'. esc_attr( $color ) .';border:1px solid #ddd;height:48px;width:48px"></div>';
		}
		if ( 'handle' === $column ) {
			$columns .= '<input type="hidden" name="term_id" value="' . esc_attr( $term_id ) . '" />';
		}
		return $columns;
	}
	
	/* Adds Swatch Text column to term table list */
	function swatch_text_column( $columns, $column, $term_id ) {
		if ( 'swatch' === $column ) {
			$text = get_term_meta( $term_id, $this->meta_text, true );
			$text = trim( $text );
			$columns .= '<div style="font-weight:700;text-align:center;border:1px solid #ddd;width:48px">'. esc_html( $text ) .'</div>';
		}
		if ( 'handle' === $column ) {
			$columns .= '<input type="hidden" name="term_id" value="' . esc_attr( $term_id ) . '" />';
		}
		return $columns;
	}
	
	/* Adds atributes to Product */
	function add_attribute_to_product( $attribute_taxonomy, $i, $attribute ){
		global $post;
		$options = array();
		$taxonomy = $attribute->get_taxonomy();
		$options = $attribute->get_options();
		$options = ! empty( $options ) ? $options : array();

		if ( 'select' !== $attribute_taxonomy->attribute_type ) {
			?>
			<select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'rehub-theme' ); ?>" class="multiselect attribute_values wc-enhanced-select" name="attribute_values[<?php echo esc_attr( $i ); ?>][]">
				<?php
				$args = array(
					'orderby'    => 'name',
					'hide_empty' => 0,
				);
				$terms = get_terms( $taxonomy, apply_filters( 'woocommerce_product_attribute_terms', $args ) );
				if ( $terms ) {
					foreach ( $terms as $term ) {
						echo '<option value="' . esc_attr( $term->term_id ) . '"' . wc_selected( $term->term_id, $options ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
					}
				}
				?>
			</select>
			<button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'rehub-theme' ); ?></button>
			<button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'rehub-theme' ); ?></button>
			<button class="button fr plus add_new_attribute"><?php esc_html_e( 'Add new', 'rehub-theme' ); ?></button>
			<?php
		}
	}
}

new RH_Attribute_Swatches();