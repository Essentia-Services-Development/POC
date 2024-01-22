<?php
/**
 * Register a meta box using a class.
 */

  
 /**
  *        Review Metabox 
  **/

 function tnc_pvfw_review_meta_box(){
    add_meta_box("tnc_pvfw_metabox_review", esc_html__( 'TNC PDF Review', 'pdf-viewer-for-wordpress' ), 'tnc_pvfw_review_meta_box_markup', "pdfviewer", "side", 'default');
  }

  add_action('add_meta_boxes','tnc_pvfw_review_meta_box');


   function tnc_pvfw_review_meta_box_markup() {
    $tnc_pvfw_review_url = 'https://codecanyon.net/item/pdf-viewer-for-wordpress/reviews/8182815';
    ?>
      <div class="tnc_pvfw_review_wrapper">
        <div class="tnc_pvfw_review_col">
          <div class="tnc_pvfw_review_title">
            <h4> <?php _e("Enjoying?", 'pdf-viewer-for-wordpress');?></h4>
          </div>
          <div class="tnc-review-heading">
             <h3> <?php _e("TNC FlipBook - PDF viewer for WordPress?", 'pdf-viewer-for-wordpress');?> </h3>
          </div>
          <div class="tnc_pvfw_review_subtitle">
            <p><?php _e("Share your review. Your feedback helps us to improve even more.", 'pdf-viewer-for-wordpress');?></p>
          </div>
          <div class="tnc_pvfw_review_rating">
            <ul>
              <li><span class="dashicons dashicons-star-filled"></span></li>
              <li><span class="dashicons dashicons-star-filled"></span></li>
              <li><span class="dashicons dashicons-star-filled"></span></li>
              <li><span class="dashicons dashicons-star-filled"></span></li>
              <li><span class="dashicons dashicons-star-filled"></span></li>
            </ul>
          </div>
          <div class="tnc_pvfw_review_btn">
            <a target="_blank" href="<?php echo esc_url($tnc_pvfw_review_url);?>"><?php _e("Share Your Experience", 'pdf-viewer-for-wordpress');?></a>
          </div>
        </div>
      </div>
    <?php
  }

/** Wp file acess metabox **/

function  tnc_pvfw_wp_file_meta_box(){

  add_meta_box("tnc_pvfw_metabox_wp_file", esc_html__( 'WP FIle Acess', 'pdf-viewer-for-wordpress' ), 'tnc_pvfw_wp_file_meta_box_markup', "pdfviewer", "side", 'default');

}
add_action('add_meta_boxes','tnc_pvfw_wp_file_meta_box');


function tnc_pvfw_wp_file_meta_box_markup(){
  $tnc_pvfw_wp_file_url = 'https://codecanyon.net/item/wp-file-access-manager/26430349';
  ?>
    <div class="tnc_pvfw_wp_file_wrapper">
      <div class="tnc_pvfw_wp_file_col">
        <div class="tnc_pvfw_wp_file_title">
          <h4> <?php _e("WP File Access Manager", 'pdf-viewer-for-wordpress');?></h4>
        </div>
        <div class="tnc_pvfw_wp_file_subtitle">
          <p><?php _e("Restrict unauthorized access to your WordPress files easily.", 'pdf-viewer-for-wordpress');?></p>
        </div>
        <div class="tnc_pvfw_wp_file_btn">
          <a target="_blank" href="<?php echo esc_url($tnc_pvfw_wp_file_url);?>"><?php _e("Get it now", 'pdf-viewer-for-wordpress');?></a>
        </div>
      </div>
    </div>
  <?php
}


/** display addon metabox **/

function  tnc_pvfw_display_meta_box(){

  add_meta_box("tnc_pvfw_metabox_display", esc_html__( 'Display PDF', 'pdf-viewer-for-wordpress' ), 'tnc_pvfw_display_meta_box_markup', "pdfviewer", "side", 'default');

}
add_action('add_meta_boxes','tnc_pvfw_display_meta_box');


function  tnc_pvfw_display_meta_box_markup(){
  $tnc_pvfw_display_url = 'https://portal.themencode.com/downloads/display-pdf-viewer-for-wordpress/';
  $tnc_pvfw_display_logo = plugin_dir_url(__FILE__).'../images/display-logo.webp';
  ?>
    <div class="tnc_pvfw_display_wrapper">
      <div class="tnc_pvfw_display_col">
        <div class="tnc_pvfw_display_title">
           <div class="tnc-display-logo"><img src="<?php echo esc_url($tnc_pvfw_display_logo)?>" alt=""></div>
        </div>
        <div class="tnc_pvfw_display_subtitle">
          <p><?php _e("Showcase your PDF files with a Bookshelf or List/Grid of items.", 'pdf-viewer-for-wordpress');?></p>
        </div>
        <div class="tnc_pvfw_display_btn">
          <a target="_blank" href="<?php echo esc_url($tnc_pvfw_display_url);?>"><?php _e("Get it now", 'pdf-viewer-for-wordpress');?></a>
        </div>
      </div>
    </div>
  <?php
}









