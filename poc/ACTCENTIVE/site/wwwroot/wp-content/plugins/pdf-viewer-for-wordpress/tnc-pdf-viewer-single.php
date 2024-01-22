<?php
/**
 * Template Name: PDF Viewer Single Template
 *
 * @package pdf-viewer-for-wordpress
 */

define( 'THEMENCODE_PDF_VIEWER_SINGLE', 'Included' );
if( tnc_pvfw_site_registered_status( false ) ){
    require_once dirname( __FILE__ ) . '/web/pdf-viewer-single.php';
} else {
    echo tnc_pvfw_site_registered_message();
}

