<?PHP
/**
 * Adds a box to the main column on the Post add/edit screens.
 */

$gecko_settings = GeckoConfigSettings::get_instance();

function gecko_add_page_meta_box() {
  add_meta_box(
    'gc-page-options', 'Gecko Page Options', 'gecko_page_meta_box_callback', 'page', 'side', 'high'
  );

  add_meta_box(
    'gc-page-options', 'Gecko Post Options', 'gecko_page_meta_box_callback', 'post', 'side', 'high'
  );

  add_meta_box(
    'gc-page-options', 'Gecko Product Options', 'gecko_page_meta_box_callback', 'product', 'side', 'high'
  );

  add_meta_box(
    'gc-page-options', 'Gecko Product Options', 'gecko_page_meta_box_callback', 'download', 'side', 'high'
  );

  add_meta_box(
    'gc-page-options', 'Gecko Product Options', 'gecko_page_meta_box_callback', 'sfwd-courses', 'side', 'high'
  );

  add_meta_box(
    'gc-page-options', 'Gecko Product Options', 'gecko_page_meta_box_callback', 'advert', 'side', 'high'
  );
}
if($gecko_settings->get_option( 'opt_limit_page_options', 0 ) == 1) {
  if(current_user_can('administrator')) {
    add_action( 'add_meta_boxes', 'gecko_add_page_meta_box' );
  }
} else {
  add_action( 'add_meta_boxes', 'gecko_add_page_meta_box' );
}


/**
 * Prints the box content.
 *
 * @param WP_Post $post The object for the current post/page.
 */
function gecko_page_meta_box_callback( $post ) {

  // Add an nonce field so we can check for it later.
  wp_nonce_field( 'gecko_page_options', 'gecko_page_options_nonce' );

  /*
   * Use get_post_meta() to retrieve an existing value
   * from the database and use the value for the form.
   */
  $value = get_post_meta( $post->ID );
  $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

  ?>
  <style>
    .gc-alert {
      margin-top: 10px;
      padding: 5px 10px;
      font-size: 100%;
      color: #c67c00;
      background-color: #ffecb3;
      border-radius: 6px;
    }
    .gc-page__new {
      position: absolute;
      top: 3px;
      right: 3px;
      display: block;
      padding: 3px 6px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
      color: #fff;
      background-color: #00c853;
      border-radius: 4px;
    }

    .gc-page__settings {}

    .gc-page__settings-section {
      margin-bottom: 10px;
      margin-left: -12px;
      margin-right: -12px;
      padding-bottom: 12px;
      border-bottom: 2px solid #eee;
    }

    .gc-page__settings-section:last-of-type {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }

    .gc-page__settings-section > h3 {
      margin-top: 0;
      margin-bottom: 10px;
      padding-left: 12px;
      padding-right: 12px;
      font-size: 14px;
    }

    .gc-page__options {}

    .gc-page__options input[type="checkbox"] {
      transform: translateY(2px);
    }

    .gc-page__option {
      position: relative;
      margin-bottom: 5px;
      padding-left: 12px;
      padding-right: 12px;
      padding-bottom: 12px;
      border-bottom: 1px solid #eee;
    }

    .gc-page__option:last-of-type {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }

    .gc-page__option--new {
      padding-right: 40px;
    }

    .gc-page__option-label {
      line-height: 1;
    }

    .gc-page__option-label + .gc-select {
      margin-top: 10px;
    }

    .gc-page__option--text .gc-page__option-label {
      dibsplay: block;
    }

    .gc-input--text,
    .gc-select {
      width: 100%;
    }

    .gc-page__option-desc {
      margin-bottom: 0;
      padding-top: 10px;
      font-size: 12px;
      color: #888;
    }

    .post-type-post .gc-page__option--page-bg {
      display: none;
    }
  </style>
  <div class="gc-page__settings">
    <?php if ( 'page-tpl-profile.php' == $page_template ) : ?>
      <div class="gc-page__settings-section">
        <h3>Profile</h3>

        <div class="gc-page__options">
          <div class="gc-page__option">
            <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-profile-centered-focus" id="gecko-profile-centered-focus" value="1" <?php if ( isset ( $value['gecko-profile-centered-focus'] ) ) checked( $value['gecko-profile-centered-focus'][0], '1' ); ?> />
            <label class="gc-page__option-label" for="gecko-profile-centered-focus">Center profile avatar & menu</label>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="gc-page__settings-section">
      <h3>Layout</h3>

      <div class="gc-page__options">
        <?php if (get_post_type( get_the_ID() ) == 'post') : ?>
        <div class="gc-page__option">
          <label class="gc-page__option-label" for="gecko-post-update-date">Post update date</label>
          <select class="gc-select" name="gecko-post-update-date" id="gecko-post-update-date">
            <option value="0">Default</option>
            <option value="1" <?php if ( isset ( $value['gecko-post-update-date'] ) ) selected( $value['gecko-post-update-date'][0], '1' ); ?>>Show</option>
            <option value="2" <?php if ( isset ( $value['gecko-post-update-date'] ) ) selected( $value['gecko-post-update-date'][0], '2' ); ?>>Hide</option>
          </select>
        </div>
        <?php endif; ?>

        <?php
        // Check Blog page ID
        $check_page_id = get_the_ID();
        $check_blog_id = get_option('page_for_posts');
        ?>

        <?php if ($check_page_id != $check_blog_id) : ?>
          <div class="gc-page__option">
            <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-hide-title" id="gecko-page-hide-title" value="1" <?php if ( isset ( $value['gecko-page-hide-title'] ) ) checked( $value['gecko-page-hide-title'][0], '1' ); ?> />
            <label class="gc-page__option-label" for="gecko-page-hide-title">Hide page title</label>
          </div>
        <?php endif; ?>

        <?php if ($check_page_id != $check_blog_id) : ?>
          <?php if (get_post_type( get_the_ID() ) == 'page') : ?>
          <div class="gc-page__option">
            <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-hide-featuredimage" id="gecko-page-hide-featuredimage" value="1" <?php if ( isset ( $value['gecko-page-hide-featuredimage'] ) ) checked( $value['gecko-page-hide-featuredimage'][0], '1' ); ?> />
            <label class="gc-page__option-label" for="gecko-page-hide-featuredimage">Hide Featured image</label>
          </div>
          <?php endif; ?>
        <?php endif; ?>

        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-hide-header" id="gecko-page-hide-header" value="1" <?php if ( isset ( $value['gecko-page-hide-header'] ) ) checked( $value['gecko-page-hide-header'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-hide-header">Hide Header</label>
        </div>

        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-hide-footer" id="gecko-page-hide-footer" value="1" <?php if ( isset ( $value['gecko-page-hide-footer'] ) ) checked( $value['gecko-page-hide-footer'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-hide-footer">Hide Footer</label>
        </div>

        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-hide-header-menu" id="gecko-page-hide-header-menu" value="1" <?php if ( isset ( $value['gecko-page-hide-header-menu'] ) ) checked( $value['gecko-page-hide-header-menu'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-hide-header-menu">Hide Header menu</label>
          <?php if(is_Gecko_MegaMenu()) : ?>
            <div class="gc-alert">
              <?php _e('MegaMenu is activated. This setting is overridden by MegaMenu plugin.', 'peepso-theme-gecko'); ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="gc-page__option gc-page__option--page-bg gc-page__option--new">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-enable-box-mode" id="gecko-page-enable-box-mode" value="1" <?php if ( isset ( $value['gecko-page-enable-box-mode'] ) ) checked( $value['gecko-page-enable-box-mode'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-enable-box-mode">Display background under content</label>
          <p class="gc-page__option-desc">
            <?php esc_html_e( 'Display content inside box with background and shadow around (just like on single post view).', 'peepso-theme-gecko' ); ?>
          </p>
        </div>

        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-full-width" id="gecko-page-full-width" value="1" <?php if ( isset ( $value['gecko-page-full-width'] ) ) checked( $value['gecko-page-full-width'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-full-width">Full width layout</label>
        </div>

        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-full-width-header" id="gecko-page-full-width-header" value="1" <?php if ( isset ( $value['gecko-page-full-width-header'] ) ) checked( $value['gecko-page-full-width-header'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-full-width-header">Full width header</label>
          <?php if(is_Gecko_MegaMenu()) : ?>
            <div class="gc-alert">
              <?php _e('MegaMenu is activated. This setting is overridden by MegaMenu plugin.', 'peepso-theme-gecko'); ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-transparent-header" id="gecko-page-transparent-header" value="1" <?php if ( isset ( $value['gecko-page-transparent-header'] ) ) checked( $value['gecko-page-transparent-header'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-transparent-header">Header Blend mode</label>
          <p class="gc-page__option-desc">
            <?php esc_html_e( 'Makes header transparent (removes top padding on <body> with Builder friendly option enabled) to help header blend with page background. It will still use solid background color on scroll.', 'peepso-theme-gecko' ); ?>
          </p>
          <?php if(is_Gecko_MegaMenu()) : ?>
            <div class="gc-alert">
              <?php _e('MegaMenu is activated. This setting is overridden by MegaMenu plugin.', 'peepso-theme-gecko'); ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-builder-friendly" id="gecko-page-builder-friendly" value="1" <?php if ( isset ( $value['gecko-page-builder-friendly'] ) ) checked( $value['gecko-page-builder-friendly'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-builder-friendly">Builder friendly template</label>
          <p class="gc-page__option-desc">
            <?php esc_html_e( 'Makes page full-width without paddings around so you can use whole available space in page builder of your choice.', 'peepso-theme-gecko' ); ?>
          </p>
        </div>
      </div>
    </div>

    <div class="gc-page__settings-section">
      <h3>Sidebars</h3>

      <div class="gc-page__options">
        <div class="gc-page__option">
          <select class="gc-select" name="gecko-page-sidebars" id="gecko-page-sidebars">
            <option value="">Default</option>
            <option value="both" <?php if ( isset ( $value['gecko-page-sidebars'] ) ) selected( $value['gecko-page-sidebars'][0], 'both' ); ?>>Hide both</option>
            <option value="left" <?php if ( isset ( $value['gecko-page-sidebars'] ) ) selected( $value['gecko-page-sidebars'][0], 'left' ); ?>>Hide left</option>
            <option value="right" <?php if ( isset ( $value['gecko-page-sidebars'] ) ) selected( $value['gecko-page-sidebars'][0], 'right' ); ?>>Hide right</option>
          </select>
        </div>

        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-left-sidebar-mobile" id="gecko-page-left-sidebar-mobile" value="1" <?php if ( isset ( $value['gecko-page-left-sidebar-mobile'] ) ) checked( $value['gecko-page-left-sidebar-mobile'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-left-sidebar-mobile">Hide left sidebar on Mobile</label>
        </div>

        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-right-sidebar-mobile" id="gecko-page-right-sidebar-mobile" value="1" <?php if ( isset ( $value['gecko-page-right-sidebar-mobile'] ) ) checked( $value['gecko-page-right-sidebar-mobile'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-right-sidebar-mobile">Hide right sidebar on Mobile</label>
        </div>

        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-sidebars-mobile" id="gecko-page-sidebars-mobile" value="1" <?php if ( isset ( $value['gecko-page-sidebars-mobile'] ) ) checked( $value['gecko-page-sidebars-mobile'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-sidebars-mobile">Hide both sidebars on Mobile view</label>
        </div>
      </div>
    </div>

    <div class="gc-page__settings-section">
      <h3>Other</h3>

      <div class="gc-page__options">
        <div class="gc-page__option">
          <input class="gc-input gc-input--checkbox" type="checkbox" name="gecko-page-footer-mobile" id="gecko-page-footer-mobile" value="1" <?php if ( isset ( $value['gecko-page-footer-mobile'] ) ) checked( $value['gecko-page-footer-mobile'][0], '1' ); ?> />
          <label class="gc-page__option-label" for="gecko-page-footer-mobile">Hide footer widgets on mobile</label>
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
function gecko_save_page_meta_box_data( $post_id ) {

  /*
   * We need to verify this came from our screen and with proper authorization,
   * because the save_post action can be triggered at other times.
   */

  // Check if our nonce is set.
  if ( !isset( $_POST['gecko_page_options_nonce'] ) ) {
    return;
  }

  // Verify that the nonce is valid.
  if ( !wp_verify_nonce( $_POST['gecko_page_options_nonce'], 'gecko_page_options' ) ) {
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

  $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

  //save values
  if ( 'page-tpl-profile.php' == $page_template ) {
    if( isset( $_POST[ 'gecko-profile-centered-focus' ] ) ) {
      update_post_meta( $post_id, 'gecko-profile-centered-focus', '1' );
    } else {
      update_post_meta( $post_id, 'gecko-profile-centered-focus', '0' );
    }
  }

  if( isset( $_POST[ 'gecko-page-hide-title' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-hide-title', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-hide-title', '0' );
  }

  if( isset( $_POST[ 'gecko-page-hide-header' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-hide-header', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-hide-header', '0' );
  }

  if( isset( $_POST[ 'gecko-page-hide-featuredimage' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-hide-featuredimage', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-hide-featuredimage', '0' );
  }

  if( isset( $_POST[ 'gecko-page-hide-footer' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-hide-footer', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-hide-footer', '0' );
  }

  if( isset( $_POST[ 'gecko-page-hide-header-menu' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-hide-header-menu', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-hide-header-menu', '0' );
  }

  if( isset( $_POST[ 'gecko-page-enable-box-mode' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-enable-box-mode', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-enable-box-mode', '0' );
  }

  if( isset( $_POST[ 'gecko-page-full-width' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-full-width', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-full-width', '0' );
  }

  if( isset( $_POST[ 'gecko-page-full-width-header' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-full-width-header', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-full-width-header', '0' );
  }

  if( isset( $_POST[ 'gecko-page-transparent-header' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-transparent-header', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-transparent-header', '0' );
  }

  if( isset( $_POST[ 'gecko-page-builder-friendly' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-builder-friendly', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-builder-friendly', '0' );
  }

  $hide_sidebars_value = ( isset( $_POST['gecko-page-sidebars'] ) ? sanitize_html_class( $_POST['gecko-page-sidebars'] ) : '' );
  update_post_meta( $post_id, 'gecko-page-sidebars', $hide_sidebars_value );

  if (get_post_type( get_the_ID() ) == 'post') {
    $hide_update_date_value = ( isset( $_POST['gecko-post-update-date'] ) ? sanitize_html_class( $_POST['gecko-post-update-date'] ) : '' );
    update_post_meta( $post_id, 'gecko-post-update-date', $hide_update_date_value );
  }

  if( isset( $_POST[ 'gecko-page-left-sidebar-mobile' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-left-sidebar-mobile', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-left-sidebar-mobile', '0' );
  }

  if( isset( $_POST[ 'gecko-page-right-sidebar-mobile' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-right-sidebar-mobile', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-right-sidebar-mobile', '0' );
  }

  if( isset( $_POST[ 'gecko-page-sidebars-mobile' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-sidebars-mobile', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-sidebars-mobile', '0' );
  }

  if( isset( $_POST[ 'gecko-page-footer-mobile' ] ) ) {
    update_post_meta( $post_id, 'gecko-page-footer-mobile', '1' );
  } else {
    update_post_meta( $post_id, 'gecko-page-footer-mobile', '0' );
  }
}
add_action( 'save_post', 'gecko_save_page_meta_box_data' );
