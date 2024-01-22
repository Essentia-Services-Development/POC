<?php
/**
 * Template Name: PDF Viewer Shortcode Template
 *
 * @package pdf-viewer-for-wordpress
 */

define( 'THEMENCODE_PDF_VIEWER_SC', 'Included' );

if( tnc_pvfw_site_registered_status( false ) ){
    require dirname( __FILE__ ) . '/web/viewer-shortcode.php';
} else {
    echo tnc_pvfw_site_registered_message();
}
