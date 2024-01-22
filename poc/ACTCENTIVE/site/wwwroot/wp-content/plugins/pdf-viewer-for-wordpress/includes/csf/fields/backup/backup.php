<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: backup
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'PVFWOF_Field_backup' ) ) {
  class PVFWOF_Field_backup extends PVFWOF_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $unique = $this->unique;
      $nonce  = wp_create_nonce( 'pvfwof_backup_nonce' );
      $export = add_query_arg( array( 'action' => 'pvfwof-export', 'unique' => $unique, 'nonce' => $nonce ), admin_url( 'admin-ajax.php' ) );

      echo $this->field_before();

      echo '<textarea name="pvfwof_import_data" class="pvfwof-import-data"></textarea>';
      echo '<button type="submit" class="button button-primary pvfwof-confirm pvfwof-import" data-unique="'. esc_attr( $unique ) .'" data-nonce="'. esc_attr( $nonce ) .'">'. esc_html__( 'Import', 'pvfwof' ) .'</button>';
      echo '<hr />';
      echo '<textarea readonly="readonly" class="pvfwof-export-data">'. esc_attr( json_encode( get_option( $unique ) ) ) .'</textarea>';
      echo '<a href="'. esc_url( $export ) .'" class="button button-primary pvfwof-export" target="_blank">'. esc_html__( 'Export & Download', 'pvfwof' ) .'</a>';
      echo '<hr />';
      echo '<button type="submit" name="pvfwof_transient[reset]" value="reset" class="button pvfwof-warning-primary pvfwof-confirm pvfwof-reset" data-unique="'. esc_attr( $unique ) .'" data-nonce="'. esc_attr( $nonce ) .'">'. esc_html__( 'Reset', 'pvfwof' ) .'</button>';

      echo $this->field_after();

    }

  }
}
