<?php
/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */
global $product, $woocommerce_loop;
// Store column count for displaying the grid

$classes = array();
if ( empty( $woocommerce_loop['columns'] ) ) {
	if(rehub_option('woo_columns') == '4_col' || rehub_option('woo_columns') == '4_col_side') {
		$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
	}
	elseif(rehub_option('woo_columns') == '5_col_side') {
		$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 5 );
	}	
	else {
		$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 3 );	
	}
}
$current_design = rehub_option('woo_design');
if ($current_design == 'list' || $current_design == 'deallist'  || $current_design == 'compactlist') {

}
elseif($woocommerce_loop['columns'] == 4) {
	$classes[] = 'col_wrap_fourth';
}
elseif($woocommerce_loop['columns'] == 5) {
	$classes[] = 'col_wrap_fifth';
}
elseif($woocommerce_loop['columns'] == 6) {
	$classes[] = 'col_wrap_six';
}
else {
	$classes[] = 'col_wrap_three';
}

if ($current_design == 'grid') {
	$classes[] = 'rh-flex-eq-height grid_woo';
}
elseif ($current_design == 'gridmart') {
    echo rh_generate_incss('gridmart');
	$classes[] = 'grid_mart rh-flex-eq-height';
}
elseif ($current_design == 'gridtwo') {
    echo rh_generate_incss('offergrid');
	$classes[] = 'eq_grid pt5 rh-flex-eq-height';
}
elseif ($current_design == 'gridrev' || $current_design == 'griddigi') {
	$classes[] = 'rh-flex-eq-height woogridrev';
}
elseif ($current_design == 'dealwhite' || $current_design == 'dealdark') {
	$classes[] = 'rh-flex-eq-height woodealgrid';
}
elseif ($current_design == 'list' || $current_design == 'deallist'  || $current_design == 'compactlist') {
	$classes[] = 'list_woo';
	if ($current_design == 'deallist') {
		$classes[] = 'woo_offer_list';
	}
}
else {
	$classes[] = 'column_woo';
}
?>

<div class="columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?> products <?php echo implode(' ',$classes);?>">