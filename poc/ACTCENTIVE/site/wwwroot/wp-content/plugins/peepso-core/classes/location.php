<?php

class PeepSoLocation
{
    private static $_instance = null;

    const SHORTCODE_TAG = 'peepso_geo';

    public $is_enabled = FALSE;
    public $user_search_field_id = 0;

    /**
     * Initialize all variables, filters and actions
     */
    private function __construct()
    {
		$this->is_enabled = TRUE;

        if(1 != PeepSo::get_option('location_enable', 0)) {
            $this->is_enabled = FALSE;
        } else {
            if (PeepSo::get_option_new('location_user_search_enable')) {
                $field_id = PeepSo::get_option_new('location_user_search_field');
                if ($field_id != 0) {
                    $this->user_search_field_id = $field_id;
                }
            }
        }

        if(!strlen(PeepSo::get_option('location_gmap_api_key',''))) {
            if($this->is_enabled) {
                add_action('admin_notices', function() {
                    if(isset($_REQUEST['page']) && isset($_REQUEST['tab'])) {
                        if('peepso_config' == $_REQUEST['page'] && 'location' == $_REQUEST['tab']) {
                            return;
                        }
                    }
                    ?>
                    <div class="error peepso">
                        <strong>
                            <?php echo __('A Google maps API key is required for the Location suggestions to work properly','peepso-core');?>.
                            <?php echo __('You can configure it','peepso-core');?>
                            <a href="admin.php?page=peepso_config&tab=location#field_location_gmap_api_key"><?php echo __('here', 'peepso-core');?></a>.
                        </strong>
                    </div>
                <?php });
            }

            $this->is_enabled = FALSE;
        }

        add_action('peepso_init', array(&$this, 'init'));
        add_filter('peepso_admin_profile_field_types', array(&$this, 'admin_profile_field_types'));

    }

    /**
     * Retrieve singleton class instance
     * @return instance reference to plugin
     */
    public static function get_instance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return (self::$_instance);
    }

    /*
     * Callback for 'peepso_init' action; initialize the PeepSoLocation plugin
     */
    public function init()
    {
        if (is_admin()) {
            if (empty(PeepSo::get_option('location_gmap_api_key')) && !isset($_REQUEST['location_gmap_api_key']) && $this->is_enabled) {
                add_action('admin_notices', array(&$this, 'api_key_missing_notice'));
            }
        } else {
            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

            if ($this->is_enabled) {
                // PeepSo postbox
                add_filter('peepso_postbox_interactions', array(&$this, 'postbox_interactions'), 30, 1);
                add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);

                // Attach post extras
                add_action('wp_insert_post', array(&$this, 'insert_post'), 30, 3);
                add_action('peepso_activity_after_save_post', array(&$this, 'insert_post'), 30);

                // Clean up all legacy information from old posts
                add_filter('peepso_activity_content', array(&$this, 'filter_remove_legacy'), 20, 1);
                add_filter('peepso_remove_shortcodes', array(&$this, 'filter_remove_legacy'), 30, 1);

                // create album extra fields
                add_filter('peepso_photo_album_extra_fields', array(&$this, 'photo_album_extra_fields'), 10, 1);
                add_filter('peepso_photo_album_show_extra_fields', array(&$this, 'photo_album_show_extra_fields'), 10, 3);
                add_filter('peepso_photo_album_update_location', array(&$this, 'photo_album_update_location'), 10);

                add_filter('peepso_activity_post_edit', array(&$this, 'filter_post_edit'), 10, 1);
            }

            // Print post extras
            add_filter('peepso_post_extras', array(&$this, 'filter_post_extras'), 20, 1);
        }
    }

    # # # # # # # # # # User Front End # # # # # # # # # #

    /**
     * POSTBOX - add the Location button
     * @param  array $interactions An array of interactions available.
     * @return array $interactions
     */
    public function postbox_interactions($interactions = array())
    {
        wp_enqueue_script('peepsolocation-js');
        wp_enqueue_style('locso');

        $interactions['location'] = array(
            'label' => __('Location', 'peepso-core'),
            'id' => 'location-tab',
            'class' => 'ps-postbox__menu-item ps-postbox__menu-item--location',
            'icon' => 'gcis gci-map-marker-alt',
            'click' => 'return;',
            'title' => __('Location', 'peepso-core'),
            'extra' => PeepSoTemplate::exec_template('location', 'interaction', null, true),
        );

        return ($interactions);
    }

    /**
     * EP add field types
     * @param array $fieldtypes An array of field types
     * @return array modified $fieldtypes
     */
    public function admin_profile_field_types($fieldtypes)
    {
        $fieldtypes[] = 'location';

        return $fieldtypes;
    }

    /**
     * PHOTO ALBUM - add the Location field
     * @param  array $fields An array of interactions available.
     * @return array $fields
     */
    public function photo_album_extra_fields($fields = array())
    {
        wp_enqueue_script('peepsolocation-js');
        wp_enqueue_style('locso');

        $fields['location'] = array(
            'label' => __('Location', 'peepso-core'),
            'field' => '<input type="text" name="album_location" class="ps-input ps-input--sm ps-js-location" value="" />',
            'isfull' => true,
            'extra' => PeepSoTemplate::exec_template('location', 'photo_album_extra_fields', null, true),
        );

        return ($fields);
    }

    /**
     * PHOTO ALBUM - display the Location field
     * @param  array $fields An array of interactions available.
     * @return array $fields
     */
    public function photo_album_show_extra_fields($extras, $post_id, $can_edit)
    {
        $loc = get_post_meta($post_id, 'peepso_location', true);

        if ($can_edit || $loc) {
            $data = array(
                'post_id' => $post_id,
                'can_edit' => $can_edit,
                'loc' => $loc,
            );
            $extras = PeepSoTemplate::exec_template('location', 'photo_album_show_extra_fields', $data, true);
        }

        return $extras;
    }

    /**
     * PHOTO ALBUM - update metadata
     * @param  int $post_id The post ID to add the metadata in.
     * @param  object $post The WP_Post object.
     */
    public function photo_album_update_location($save = array())
    {
        $input = new PeepSoInput();

        $owner = $input->int('user_id');
        $post_id = $input->int('post_id');
        $location = $input->value('location', null,  false); // SQL Safe

        // SQL safe, WP sanitizes it
        if (false === wp_verify_nonce($input->value('_wpnonce','',FALSE), 'set-album-location')) {
            $save['success'] = false;
            $save['error'] = __('Request could not be verified.', 'peepso-core');
        } else {
            $the_post = get_post($post_id);
            $can_edit = PeepSo::check_permissions(intval($the_post->post_author), PeepSo::PERM_POST_EDIT, get_current_user_id());
            if ($can_edit) {

                if (false === is_null($location)) {
                    update_post_meta($post_id, 'peepso_location', $location);

                    $save['success'] = true;
                    $save['msg'] = __('Photo album location saved.', 'peepso-core');
                } else {
                    $save['success'] = false;
                    $save['msg'] = __('Missing field location.', 'peepso-core');
                }
            } else {
                $save['success'] = false;
                $save['msg'] = __('You are not authorized to change this album location.', 'peepso-core');
            }
        }

        return $save;
    }

    /**
     * POSTBOX - set a flag allowing the post content to be empty
     * @param string $allowed
     * @return boolean
     */
    public function activity_allow_empty_content($allowed)
    {
        $input = new PeepSoInput();
        $location = $input->value('location', null, FALSE); // SQL Safe
        if (!empty($location)) {
            $allowed = true;
        }
        return ($allowed);
    }

    /**
     * POST CREATION - build metadata
     * @param  int $post_id The post ID to add the metadata in.
     * @param  object $post The WP_Post object.
     */
    public function insert_post($post_id, $post = null, $update = false)
    {
        $input = new PeepSoInput();
        $location = $input->value('location', null, FALSE); // SQL Safe

        if (empty($location) && !$post) {
            delete_post_meta($post_id, 'peepso_location');
        } else if ($location) {
            update_post_meta($post_id, 'peepso_location', $location);
        }
    }

    /**
     * POST RENDERING - add location information to post extras array
     * @return array
     */
    public function filter_post_extras($extras = array())
    {
        global $post;
        $loc = get_post_meta($post->ID, 'peepso_location', true);

        if ($loc) {
            $lat = $loc['latitude'];
            $lng = $loc['longitude'];
            $name = $loc['name'];
            $name_esc = esc_attr($name);
            ob_start();

            ?>
      			<span class="ps-post__location">
              <a href="#" title="<?php echo $name_esc; ?>"
                 data-preview="<?php echo sprintf(__('Location:  %s','peepso-core'),$name);?>"
                  onclick="pslocation.show_map(<?php echo "$lat, $lng, '$name_esc'"; ?>); return false;">
                  <i class="gcis gci-map-marker-alt"></i><?php echo $name; ?>
              </a>
      			</span>
			<?php

			$extras[] = ob_get_clean();
        }

        return $extras;
    }

    /**
     * POST RENDERING - clean old location information and shortcodes
     * @return string
     */
    public function filter_remove_legacy($content)
    {
        // Clean up old info attached to the post
        $regex = '/(<span>&mdash;)[\s\S]+(<\/span>)/';
        $content = preg_replace($regex, '', $content);

        // Since 1.6.1 we don't use shortcodes
        $content = preg_replace('/\[peepso_geo(?:.*?)\][\s\S]*\[\/peepso_geo]/', '', $content);

        $content = trim($content);

        return $content;
    }

    /**
     * Enqueue the assets
     */
    public function enqueue_scripts()
    {
        global $wp_query;

        add_filter('peepso_data', function( $data ) {
            $location = array(
                'api_key' => PeepSo::get_option('location_gmap_api_key'),
                'template_selector' => PeepSoTemplate::exec_template('location', 'selector', array(), true),
                'template_postbox' => PeepSoTemplate::exec_template('location', 'postbox', array(), true),
            );
            $data['location'] = $location;
            return $data;
        }, 10, 1 );

        wp_enqueue_script('peepsolocation-js', PeepSo::get_asset('js/location.min.js'),
            array('peepso', 'jquery-ui-position', 'peepso-lightbox'), PeepSo::PLUGIN_VERSION, true);
    }

    # # # # # # # # # # Utilities: Activation, Licensing, PeepSo detection and compatibility  # # # # # # # # # #

	public function api_key_missing_notice()
    {?>
		<div class="error">
			<strong>
                <?php echo __('PeepSo Location requires a Google Maps API key.', 'peepso-core'); ?>
                <a href="admin.php?page=peepso_config&tab=location"><?php echo __('Click here to configure it', 'peepso-core'); ?></a>.


			</strong>
		</div>
		<?php
    }

    public function filter_post_edit($data = array())
    {
        $input = new PeepSoInput();
        $post_id = $input->int('postid');

        $location = get_post_meta($post_id, 'peepso_location', true);
        if (!empty($location)) {
            $data['location'] = $location;
        }

        return $data;
    }

    public function can_search_users() {
        $field_id = $this->user_search_field_id;
        $field = PeepSoField::get_field_by_id($field_id);
        if($field instanceof PeepSoFieldLocation) {
            return TRUE;
        }

        return FALSE;
    }
}

// EOF
