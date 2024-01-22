<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: icon
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'PVFWOF_Field_icon' ) ) {
  class PVFWOF_Field_icon extends PVFWOF_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $args = wp_parse_args( $this->field, array(
        'button_title' => esc_html__( 'Add Icon', 'pvfwof' ),
        'remove_title' => esc_html__( 'Remove Icon', 'pvfwof' ),
      ) );

      echo $this->field_before();

      $nonce  = wp_create_nonce( 'pvfwof_icon_nonce' );
      $hidden = ( empty( $this->value ) ) ? ' hidden' : '';

      echo '<div class="pvfwof-icon-select">';
      echo '<span class="pvfwof-icon-preview'. esc_attr( $hidden ) .'"><i class="'. esc_attr( $this->value ) .'"></i></span>';
      echo '<a href="#" class="button button-primary pvfwof-icon-add" data-nonce="'. esc_attr( $nonce ) .'">'. $args['button_title'] .'</a>';
      echo '<a href="#" class="button pvfwof-warning-primary pvfwof-icon-remove'. esc_attr( $hidden ) .'">'. $args['remove_title'] .'</a>';
      echo '<input type="hidden" name="'. esc_attr( $this->field_name() ) .'" value="'. esc_attr( $this->value ) .'" class="pvfwof-icon-value"'. $this->field_attributes() .' />';
      echo '</div>';

      echo $this->field_after();

    }

    public function enqueue() {
      add_action( 'admin_footer', array( 'PVFWOF_Field_icon', 'add_footer_modal_icon' ) );
      add_action( 'customize_controls_print_footer_scripts', array( 'PVFWOF_Field_icon', 'add_footer_modal_icon' ) );
    }

    public static function add_footer_modal_icon() {
    ?>
      <div id="pvfwof-modal-icon" class="pvfwof-modal pvfwof-modal-icon hidden">
        <div class="pvfwof-modal-table">
          <div class="pvfwof-modal-table-cell">
            <div class="pvfwof-modal-overlay"></div>
            <div class="pvfwof-modal-inner">
              <div class="pvfwof-modal-title">
                <?php esc_html_e( 'Add Icon', 'pvfwof' ); ?>
                <div class="pvfwof-modal-close pvfwof-icon-close"></div>
              </div>
              <div class="pvfwof-modal-header">
                <input type="text" placeholder="<?php esc_html_e( 'Search...', 'pvfwof' ); ?>" class="pvfwof-icon-search" />
              </div>
              <div class="pvfwof-modal-content">
                <div class="pvfwof-modal-loading"><div class="pvfwof-loading"></div></div>
                <div class="pvfwof-modal-load"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php
    }

  }
}
