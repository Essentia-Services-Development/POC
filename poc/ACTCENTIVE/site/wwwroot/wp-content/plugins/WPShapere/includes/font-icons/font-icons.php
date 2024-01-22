<?php
/*
 * WPSHAPERE
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
*/

include( WPSHAPERE_PATH . 'includes/font-icons/icons-array.php' );
?>

<select name="custom_admin_menu[menu_icon][<?php echo $value[2];?>]" class="wps-icon-picker">
  <option value=""><?php echo esc_html__( 'Blank', 'wphelpere' ); ?></option>
  <?php
  if( !empty( $wps_icons_array ) ) {
    foreach ( $wps_icons_array as $icon_name => $icon_values) {
      $icon_labels = array( 'dashicons' => 'Dashicons', 'lni' => 'Line Icons', 'fas' => 'FontAwesome Solid', 'fab' => 'FontAwesome Brands' );
      echo '<optgroup label="' . $icon_labels[$icon_name] . '">';

          foreach ( $icon_values as $icon_value) {
            if( $icon_name == 'lni' ) {
              $wpsicon = 'lni lni-' . $icon_value;
            }
            elseif( $icon_name == 'fas' ) {
              $wpsicon = 'fas fa-' . $icon_value;
            }
            elseif( $icon_name == 'fab' ) {
              $wpsicon = 'fab fa-' . $icon_value;
            }
            elseif( $icon_name == 'dashicons' ) {
              $wpsicon = 'dashicons dashicons-' . $icon_value;
            }
            $selected = ( isset( $menu_icon_class ) && $menu_icon_class == $wpsicon ) ? 'selected' : '';
            echo '<option value="' . $wpsicon . '" '. $selected .'>' . $wpsicon .'</option>';
          }

      echo '</optgroup>';
    }
  }
   ?>
</select>
