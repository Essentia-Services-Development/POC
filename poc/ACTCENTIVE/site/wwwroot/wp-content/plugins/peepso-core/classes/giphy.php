<?php

class PeepSoGiphy
{
	private static $_instance = NULL;

    const POST_META_KEY_GIPHY = 'peepso_giphy';

    private function __construct() {


        /** VERSION LOCKED hooks **/
        if (is_admin()) {
            add_action('admin_init', array(&$this, 'giphy_check'));
        }

        add_action('peepso_init', array(&$this, 'init'));
    }

    /**
     * Retrieve singleton class instance
     * @return PeepSoGiphy instance
     */
    public static function get_instance()
    {
        if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }

    public function init()
    {
        if (is_admin()) {
            add_action('admin_init', array(&$this, 'giphy_check'));
        } else {
            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

            add_filter('peepso_post_types_message', array(&$this, 'post_types_message'));
            add_filter('peepso_post_types', array(&$this, 'post_types'),99);
            add_filter('peepso_postbox_tabs', array(&$this, 'postbox_tabs'));

            // comments addons
            add_filter('peepso_commentsbox_interactions', array(&$this, 'commentsbox_interactions'), 20, 2);
            add_filter('peepso_commentsbox_addons', array(&$this, 'commentsbox_addons'), 10, 2);
            add_filter('peepso_activity_stream_action', array(&$this, 'activity_stream_action'), 10, 2);
            add_action('peepso_activity_post_attachment', array(&$this, 'comments_attach_giphy'), 20, 1);
            add_action('peepso_activity_comment_attachment', array(&$this, 'comments_attach_giphy'), 10);
            add_filter('peepso_activity_allow_empty_comment', array(&$this, 'activity_allow_empty_comment'), 10, 1);
            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
            add_action('peepso_after_add_comment', array(&$this, 'after_add_comment'), 10, 4);
            add_action('peepso_activity_after_save_comment', array(&$this, 'after_save_comment'), 10, 2);
            add_filter('peepso_activity_comment_actions',   array(&$this, 'modify_comments_actions'),100); // priority set to last
            add_filter('peepso_message_input_addons',   array(&$this, 'message_input_addons'), 20, 1);

            // chat integration
            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post'), 20, 2);
        }
    }

    /**
     * Check if Giphy API key has been provided
     * If there is no PeepSo, immediately disable the plugin and display a warning
     * Run license and new version checks against PeepSo.com
     * @return bool
     */
    public function giphy_check()
    {
        $giphy_key = PeepSo::get_option('giphy_api_key', FALSE);
        if (empty($giphy_key) or $giphy_key === FALSE) {
            add_action('admin_notices', array(&$this, 'peepso_giphy_notice'));
            return (FALSE);
        }


        return (TRUE);
    }


    /**
     * Display a message about Giphy API Key not present
     */
    public function peepso_giphy_notice()
    {
        ?>
        <div class="error peepso">
            <strong>
                <?php
                echo __('Please provide GIPHY API Key.', 'peepso-core'),
                    ' <a href="http://api.giphy.com" target="_blank">',
                    __('Get it now!', 'peepso-core'),
                    '</a>';
                ?>
            </strong>
        </div>
        <?php
    }

    /**
     * Enqueue custom scripts and styles
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        wp_register_style('peepso-giphy', PeepSo::get_asset('css/giphy.css'), array('peepso'), PeepSo::PLUGIN_VERSION, 'all');
        wp_enqueue_style('peepso-giphy');

        wp_register_script('peepso-giphy', PeepSo::get_asset('js/giphy.min.js'), array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_localize_script('peepso-giphy', 'peepsogiphydata', array(
            'dialogGiphyTemplate' => PeepSoTemplate::exec_template('giphy', 'dialog-giphy', NULL, TRUE),
            'giphy_api_key' => PeepSo::get_option('giphy_api_key', ''),
            'giphy_rating' => PeepSo::get_option('giphy_rating', ''),
            'giphy_rendition_posts' => PeepSo::get_option('giphy_rendition_posts', ''),
            'giphy_rendition_comments' => PeepSo::get_option('giphy_rendition_comments', ''),
            'giphy_rendition_messages' => PeepSo::get_option('giphy_rendition_messages', ''),
            'giphy_display_limit' => PeepSo::get_option('giphy_display_limit', 25),
        ));

        wp_enqueue_script('peepso-giphy');
    }

    /**
     * FRONTEND
     * ========
     *
     */

    /**
     * Adds Giphy tab to the available post type options
     * @param  array $post_types
     * @return array
     */
    public function post_types_message($post_types)
    {
        if(PeepSo::get_option_new('giphy_chat_enable')) {
            $post_types['giphy'] = array(
                'icon' => 'gcis gci-giphy',
                'name' => __('GIF', 'peepso-core'),
                'class' => 'ps-postbox__menu-item',
            );
        } else if (isset($post_types['giphy'])) {
            unset($post_types['giphy']);
        }

        return $post_types;
    }

    public function post_types($post_types)
    {
        if(PeepSo::get_option_new('giphy_posts_enable')) {
            $post_types['giphy'] = array(
                'icon' => 'gcis gci-giphy',
                'name' => __('GIF', 'peepso-core'),
                'class' => 'ps-postbox__menu-item',
            );
        }

        return $post_types;
    }

    /**
     * Displays the UI for the Giphy post type
     * @return string The input html
     */
    public function postbox_tabs($tabs)
    {
        $tabs['giphy'] = PeepSoTemplate::exec_template('giphy', 'postbox-giphy', NULL, TRUE);
        return $tabs;
    }


    /**
     * This function inserts the GIPHY UI on the comments box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param int $post_id Post content ID
     */
    public function commentsbox_interactions($interactions, $post_id = FALSE)
    {
        if(PeepSo::get_option_new('giphy_comments_enable')) {
            wp_enqueue_script('peepso-giphy');

            $interactions['stickerpipe'] = array(
                'icon' => 'gcis gci-giphy',
                'class' => 'ps-comments__input-action ps-js-comment-giphy',
                'title' => __('Send gif', 'peepso-core')
            );
        }

        return ($interactions);
    }

    /**
     * This function inserts the photo UI on the comments box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param int $post_id Post content ID
     */
    public function commentsbox_addons($addons, $post_id = FALSE)
    {
        $giphy = array();

        if ($post_id) {
            $giphy['src'] = get_post_meta($post_id, self::POST_META_KEY_GIPHY, true);
        }

        $html = PeepSoTemplate::exec_template('giphy', 'comment-addon', $giphy, TRUE);
        array_push($addons, $html);
        return ($addons);
    }

    /**
     * define the "action text"
     *
     * @param $action
     * @param $post
     * @return string
     */
    public function activity_stream_action($action, $post)
    {
        $giphy = get_post_meta($post->ID, self::POST_META_KEY_GIPHY, true);
        if(!empty($giphy)) {
            $action .= __(' shared a GIF', 'peepso-core');
        }

        return ($action);
    }

    /**
     * Checks if empty comment is allowed
     * @param string $allowed
     * @return boolean always returns TRUE
     */
    public function activity_allow_empty_comment($allowed)
    {
        $input = new PeepSoInput();
        // SQL injection safe - not used in SQL
        $giphy = $input->value('giphy', FALSE, FALSE);
        if(FALSE !== $giphy) {
            $allowed = TRUE;
        }

        return ($allowed);
    }

    public function activity_allow_empty_content($allowed)
    {
        $input = new PeepSoInput();
        // SQL injection safe - not used in SQL
        $giphy = $input->value('giphy', FALSE, FALSE);
        if(FALSE !== $giphy) {
            $allowed = TRUE;
        }

        return ($allowed);
    }


    /**
     * Displays the embeded media on the comment.
     * - peepso_activity_comment_attachment
     * @param WP_Post The current post object
     */
    public function comments_attach_giphy($stream_comment = NULL)
    {
        $giphy = get_post_meta($stream_comment->ID, self::POST_META_KEY_GIPHY, true);
        if(empty($giphy)) {
            return;
        }

        PeepSoTemplate::exec_template('giphy', 'comments-content', array('stream_comment' => $stream_comment, 'giphy' => $giphy));
    }

    /**
     * This function will save the postmeta for photo comments
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_comment($post_id, $act_id, $did_notify, $did_email)
    {
        $input = new PeepSoInput();

        // SQL injection safe - add_post_meta sanitizes it
        $giphy = $input->value('giphy', FALSE, FALSE);

        // #3048 Re-add url scheme if needed.
        if ( !empty($giphy)) {
            if ( ! preg_match( '/^[a-z]+:\/\//i', $giphy ) ) {
                $giphy = 'https://' . $giphy;
            }
        }

        if(FALSE !== $giphy) {
            add_post_meta($post_id, self::POST_META_KEY_GIPHY, $giphy, TRUE);
        }
    }

    /**
     * This function will save/update the postmeta for photo comments
     * @param object $post The post
     */
    public function after_save_comment($post_id, $activity)
    {
        $input = new PeepSoInput();

        // SQL injection safe - add_post_meta sanitizes it
        $giphy = $input->value('giphy', FALSE, FALSE);

        // delete photo
        if(FALSE === $giphy) {
            delete_post_meta($post_id, self::POST_META_KEY_GIPHY);
            return;
        }

        // #3048 Re-add url scheme if needed.
        if ( !empty($giphy)) {
            if ( ! preg_match( '/^[a-z]+:\/\//i', $giphy ) ) {
                $giphy = 'https://' . $giphy;
            }
        }

        $giphy_meta = get_post_meta($post_id, self::POST_META_KEY_GIPHY, TRUE);

        if(!empty($giphy_meta)) {
            if($giphy_meta === $giphy) {
                return; // same giphy
            }
            // delete previous giphy
            delete_post_meta($post_id, self::POST_META_KEY_GIPHY);
        }

        add_post_meta($post_id, self::POST_META_KEY_GIPHY, $giphy, TRUE);
    }

    /**
     * Change act_id on repost button act_id to follow parent's act_id.
     * @param array $options The default options per post
     * @return  array
     */
    public function modify_comments_actions($options)
    {
        global $post;

        $giphy = get_post_meta($post->ID, self::POST_META_KEY_GIPHY, true);
        $match = preg_match("/\[\[(.*?)\]\]/i", $giphy);
        if(!$match) {
            return ($options);
        }

        unset($options['edit']);

        return ($options);
    }

    /**
     * Add additional GIPHY addon to message input
     * @param array $options The additional addons to be attached to message input
     * @return  array
     */
    public function message_input_addons($addons)
    {
        $addons[] = PeepSoTemplate::exec_template('giphy', 'message-input', NULL, TRUE);
        return ($addons);
    }

    /**
     * This function manipulates giphy upload on chat box
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_post($post_id, $act_id)
    {
        $input = new PeepSoInput();

        // SQL injection safe - add_post_meta sanitizes it
        $giphy = $input->value('giphy', '', FALSE);

        // SQL injection safe - not used in SQL
        if (!empty($giphy) && 'giphy' === $input->value('type', '', FALSE)) {
            // delete photo
            if(FALSE === $giphy) {
                delete_post_meta($post_id, self::POST_META_KEY_GIPHY);
                return;
            }

            $giphy_meta = get_post_meta($post_id, self::POST_META_KEY_GIPHY, TRUE);

            if(!empty($giphy_meta)) {
                if($giphy_meta === $giphy) {
                    return; // same giphy
                }
                // delete previous giphy
                delete_post_meta($post_id, self::POST_META_KEY_GIPHY);
            }

            add_post_meta($post_id, self::POST_META_KEY_GIPHY, $giphy, TRUE);
        }
    }
}

// EOF
