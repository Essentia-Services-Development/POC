<?php
/**
 * Template Name: PDF Viewer Template
 *
 * @package pdf-viewer-for-wordpress
 */

define( 'THEMENCODE_PDF_VIEWER', 'Included' );
if( tnc_pvfw_site_registered_status( false ) ){
    require_once dirname( __FILE__ ) . '/web/viewer.php';
} else {
    echo tnc_pvfw_site_registered_message();
}

