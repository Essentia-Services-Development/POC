<?PHP
/**
 * Adds a box to the main column on the Post add/edit screens.
 */
function gecko_add_landing_meta_box() {
  $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

  if ( 'page-tpl-landing.php' !== $page_template ) return;

  add_meta_box(
    'gc-landing-options', 'Gecko Landing Options', 'gecko_landing_meta_box_callback', 'page', 'normal', 'high'
  );
}
add_action( 'add_meta_boxes', 'gecko_add_landing_meta_box');

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function gecko_landing_meta_box_callback( $post ) {

  // Add an nonce field so we can check for it later.
  wp_nonce_field( 'gecko_landing_options', 'gecko_landing_options_nonce' );

  ?>
  <div class="gc-page__settings">
    <div class="gc-page__settings-section">
      <h3><?php esc_html_e( 'Redirect Button', 'peepso-theme-gecko' ); ?></h3>

      <div class="gc-page__options">
        <div class="gc-page__option gc-page__option--text">
          <label class="gc-page__option-label" for="gecko-page-full-width">
            <?php esc_html_e( 'Button url', 'peepso-theme-gecko' ); ?>
          </label>
          <input class="gc-input gc-input--text" type="text" name="gecko-landing-btn-url" value="<?php echo get_post_meta($post->ID, "gecko-landing-btn-url", true); ?>" />
        </div>

        <div class="gc-page__option gc-page__option--text">
          <label class="gc-page__option-label" for="gecko-page-full-width">
            <?php esc_html_e( 'Button label', 'peepso-theme-gecko' ); ?>
          </label>
          <input class="gc-input gc-input--text" type="text" name="gecko-landing-btn-label" value="<?php echo get_post_meta($post->ID, "gecko-landing-btn-label", true); ?>" />
        </div>
      </div>
    </div>
  </div>
  <?php

}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function gecko_save_landing_meta_box_data( $post_id ) {

  /*
   * We need to verify this came from our screen and with proper authorization,
   * because the save_post action can be triggered at other times.
   */

  // Check if our nonce is set.
  if ( !isset( $_POST['gecko_landing_options_nonce'] ) ) {
    return;
  }

  // Verify that the nonce is valid.
  if ( !wp_verify_nonce( $_POST['gecko_landing_options_nonce'], 'gecko_landing_options' ) ) {
    return;
  }

  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  // Check the user's permissions.
  if ( !current_user_can( 'edit_post', $post_id ) ) {
    return;
  }

  $landing_btn_url = "";
  $landing_btn_label = "";

  if( isset( $_POST["gecko-landing-btn-url"] ) ) { $landing_btn_url = $_POST["gecko-landing-btn-url"]; }   
  update_post_meta( $post_id, "gecko-landing-btn-url", $landing_btn_url );

  if( isset( $_POST["gecko-landing-btn-label"] ) ) { $landing_btn_label = $_POST["gecko-landing-btn-label"]; }   
  update_post_meta( $post_id, "gecko-landing-btn-label", $landing_btn_label );
}
add_action( 'save_post', 'gecko_save_landing_meta_box_data' );

/**
 * Add heading above default editor
 * 
 */
add_action( 'edit_form_after_title', 'titlebeforeeditorlanding' );
function titlebeforeeditorlanding() {
    $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
    if ( 'page-tpl-landing.php' !== $page_template ) return;

    echo '<h3>All the content will be displayed in the left column. To replace default login form in the right column add your widgets to the Landing widget position.</h3>';
}
