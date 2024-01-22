<?php
add_action( 'admin_menu', 'tnc_pdf_menu' );
function tnc_pdf_menu() {
    add_submenu_page( 'edit.php?post_type=pdfviewer', 'Import PDF File - TNC FlipBook - PDF viewer for WordPress', 'Import PDF File', 'upload_files', 'themencode-pdf-viewer-import-file', 'tnc_import_pdf_file', 4);
    add_submenu_page( 'edit.php?post_type=pdfviewer', 'Activation & Updates', 'Activation & Updates', 'manage_options', 'themencode-pdf-viewer-updates', 'tnc_pdf_viewer_updates', 5);
    add_submenu_page( 'edit.php?post_type=pdfviewer', 'Migrate Settings', 'Migrate Settings', 'manage_options', 'themencode-pdf-viewer-migrate-settings', 'tnc_pdf_viewer_migrate_settings', 6);
}

function tnc_import_pdf_file(){
    if ( !current_user_can( 'upload_files' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    include dirname(__FILE__)."/import-pdf-file.php";
}
function tnc_pdf_viewer_updates(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    include dirname(__FILE__)."/update-registration.php";
}

function tnc_pdf_viewer_migrate_settings(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    include dirname(__FILE__)."/migrate-settings.php";
}



/*
*    Add  Addons & Integrations
*/

add_action('admin_menu', 'tnc_pvfw_addon_register_submenu_page');
 
function tnc_pvfw_addon_register_submenu_page() {
    add_submenu_page( 
		'edit.php?post_type=pdfviewer',
        'Addons & Integrations',
        'Addons & Integrations',
        'manage_options',
        'pdf-addon-integration-page',
        'tnc_pvfw_addon_integration_content',
    );
}
function tnc_pvfw_addon_integration_content() { 

    $divi_url = "https://portal.themencode.com/downloads/divi-pdf-viewer-for-wordpress/";
    $divi_image =  plugin_dir_url(__FILE__).'../images/divi-pdf.png';
    $avada_url = "https://codecanyon.net/item/avada-pdf-viewer-for-wordpress-addon/43992846";
    $avada_image = plugin_dir_url(__FILE__).'../images/avada-pdf.png';
    $elementor_url = "https://codecanyon.net/item/elementor-pdf-viewer-for-wordpress-addon/27575246";
    $elementor_image = plugin_dir_url(__FILE__).'../images/elementor-pdf.png';
    $display_url = "https://portal.themencode.com/downloads/display-pdf-viewer-for-wordpress/";
    $display_image = plugin_dir_url(__FILE__).'../images/displa-pdf.png';
    $wpbakery_url = "https://codecanyon.net/item/pdf-viewer-for-wordpress-visual-composer-addon/17334228";
    $wpbakery_image = plugin_dir_url(__FILE__).'../images/wpbakery-pdf.png';
    $wpfile_url = "https://codecanyon.net/item/wp-file-access-manager/26430349";
    $wpfile_image = plugin_dir_url(__FILE__).'../images/wpfile-pdf.png';
    $navigative_url  = "https://codecanyon.net/item/navigative-pdf-viewer-for-wordpress-adoon/19393796";
    $navigative_image =  plugin_dir_url(__FILE__).'../images/navigative.png';
    $preview_url  = "https://portal.themencode.com/downloads/preview-pdf-viewer-for-wordpress-addon/";
    $preview_image = plugin_dir_url(__FILE__).'../images/Preview-Icon.png';
    
    
    ?>

    <div class="addon-title-wrapper">
        <div class="addon-title-container">
             <h2> <?php _e( get_admin_page_title(), 'pdf-viewer-for-wordpress');?></h2>
             <p> <?php _e( 'Here are the available addons and integrations that work with TNC FlipBook - PDF viewer for WordPress.', 'pdf-viewer-for-wordpress');?> </p>
        </div>
    </div>
    <div class="addon-integration-wrapper">   <?php _e( '', 'pdf-viewer-for-wordpress');?>
        <div class="addon-integration-container">
             <div class="addon-integration-grid">
                  <div class="addon-integration-item">
                       <div class="image-wrap">
                          <img src="<?php echo $divi_image; ?>" alt="">
                        </div>
                        <div class="item-content">
                              <h3> <?php _e('Divi PDF viewer for WordPress', 'pdf-viewer-for-wordpress');?> </h3>
                              <p> <?php _e( 'Life-saver for Divi users. Get some amazing Divi module which will help you to embed FlipBooks easily right from you Divi builder. Check this out now.', 'pdf-viewer-for-wordpress');?></p>
                        </div>
                        <div class="item-btn">
                             <a target="_blank" href="<?php echo esc_url($divi_url);?>"><?php _e( 'Get it Now', 'pdf-viewer-for-wordpress');?></a>
                         </div>
                  </div>
                  <div class="addon-integration-item">
                       <div class="image-wrap">
                          <img src="<?php echo $avada_image; ?>" alt=""> 
                        </div>
                        <div class="item-content">
                              <h3><?php _e( 'Avada – TNC FlipBook – PDF viewer for WordPress Addon', 'pdf-viewer-for-wordpress');?> </h3>
                              <p>  <?php _e( 'Use this addon and you will get several elements to show the PDF viewer in many ways. Embed FlipBooks or create a link or image link and many more.', 'pdf-viewer-for-wordpress');?></p>
                        </div>
                        <div class="item-btn">
                            <a target="_blank" href="<?php echo esc_url($avada_url);?>"><?php _e( 'Get it Now', 'pdf-viewer-for-wordpress');?></a>
                        </div>
                  </div>
                  <div class="addon-integration-item">
                       <div class="image-wrap">
                          <img src="<?php echo  $elementor_image; ?>" alt="">
                        </div>
                        <div class="item-content">
                              <h3> <?php _e('Elementor – TNC FlipBook – PDF viewer for WordPress Addon', 'pdf-viewer-for-wordpress');?> </h3>
                              <p> <?php _e('This addon has various elements which will ease the process of showing FlipBooks on your website in different manner. Save your time and work.', 'pdf-viewer-for-wordpress');?></p>
                        </div>
                        <div class="item-btn">
                            <a target="_blank" href="<?php echo esc_url($elementor_url);?>"><?php _e( 'Get it Now', 'pdf-viewer-for-wordpress');?></a>
                        </div>
                  </div>
                  <div class="addon-integration-item">
                       <div class="image-wrap">
                          <img src="<?php echo $display_image; ?>" alt="">
                        </div>
                        <div class="item-content">
                              <h3> <?php _e( 'Display – TNC FlipBook – PDF viewer for WordPress Addon', 'pdf-viewer-for-wordpress');?></h3>
                              <p> <?php _e( 'Bookshelf is the most unique and stylish way of presenting your PDF files. There are also List/Grid view options. You can open FlipBooks as a PopUp or in a new tab', 'pdf-viewer-for-wordpress');?></p>
                        </div>
                        <div class="item-btn">
                                <a target="_blank" href="<?php echo esc_url( $display_url );?>"><?php _e( 'Get it Now', 'pdf-viewer-for-wordpress');?></a>
                         </div>
                  </div>
                  <div class="addon-integration-item">
                       <div class="image-wrap">
                          <img src="<?php echo $wpbakery_image; ?>" alt="">
                        </div>
                        <div class="item-content">
                              <h3><?php _e( 'WPBakery – TNC FlipBook – PDF viewer for WordPress Addon', 'pdf-viewer-for-wordpress');?></h3>
                              <p><?php _e( 'If you are using WPBakery page builder on your website, you can get this addon to embed FlipBooks using WPBakery Page Builder interface.', 'pdf-viewer-for-wordpress');?></p>
                        </div>
                        <div class="item-btn">
                          <a target="_blank" href="<?php echo esc_url($wpbakery_url);?>"><?php _e( 'Get it Now', 'pdf-viewer-for-wordpress');?> </a>
                        </div>
                  </div>
                  <div class="addon-integration-item">
                       <div class="image-wrap">
                          <img src="<?php echo  $navigative_image; ?>" alt="">
                        </div>
                        <div class="item-content">
                              <h3> <?php _e( 'Navigative – TNC FlipBook – PDF viewer for WordPress Addon', 'pdf-viewer-for-wordpress');?></h3>
                              <p> <?php _e( "This addon is useful if you want to have one viewer on a page but open multiple pdf's according to users click. You can have a list of PDF links on the sidebar using a widget.", "pdf-viewer-for-wordpress");?> </p>
                        </div>
                        <div class="item-btn">
                             <a target="_blank" href="<?php  echo esc_url($navigative_url);?>"> <?php _e( 'Get it Now', 'pdf-viewer-for-wordpress');?></a>
                        </div>
                    </div>
                    <div class="addon-integration-item">
                       <div class="image-wrap">
                          <img src="<?php echo $preview_image; ?>" alt="">
                        </div>
                        <div class="item-content">
                              <h3> <?php _e( 'Preview – TNC FlipBook – PDF viewer for WordPress Addon', 'pdf-viewer-for-wordpress');?></h3>
                              <p> <?php _e( "This addon, you can select specific pages of a PDF file and set restrictions for visitors. Restricted visitors will only see a partial view of those selected pages.", "pdf-viewer-for-wordpress");?> </p>
                        </div>
                        <div class="item-btn">
                             <a target="_blank" href="<?php  echo esc_url($preview_url);?>"> <?php _e('Get it Now', 'pdf-viewer-for-wordpress');?></a>
                        </div>
                    </div>
                    <div class="addon-integration-item">
                       <div class="image-wrap">
                          <img src="<?php echo $wpfile_image;?>" alt="">
                        </div>
                        <div class="item-content">
                              <h3><?php _e( 'WP File Access Manager - Easy Way to Restrict WordPress Uploads', 'pdf-viewer-for-wordpress');?></h3>
                              <p><?php _e( 'If you want to restrict access to your media library files by user login/role/woocommerce purchase or paid memberships pro level, this plugin is for you!', 'pdf-viewer-for-wordpress');?></p>
                        </div>
                        <div class="item-btn">
                            <a target="_blank" href="<?php echo esc_url($wpfile_url);?>"> <?php _e( 'Get it Now', 'pdf-viewer-for-wordpress');?> </a>
                         </div>
                    </div>
                  </div>
               </div>
         </div>
     </div>
<?php
}