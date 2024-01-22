<?php

class PeepSoPostBackgrounds
{

    private static $_instance = null;
    const MODULE_ID = 111;

    public function __construct()
    {
        if (!PeepSo::get_option_new('post_backgrounds_enable')) {
            return;
        }
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // postbox
        add_filter('peepso_post_types', array($this, 'post_types'), 25, 2);
        add_filter('peepso_postbox_tabs', array($this, 'postbox_tabs'), 120);
        add_filter('peepso_postbox_interactions', array($this, 'postbox_interactions'), 110, 2);

        // save additional data
        add_filter('peepso_activity_insert_data', array($this, 'activity_insert_data'));
        add_action('peepso_activity_after_add_post', array($this, 'after_add_post'));
        add_action('peepso_activity_after_save_post', array($this, 'after_add_post'), 10, 1);

        // clean post content from html tags
        add_filter('peepso_activity_post_content', array($this, 'activity_post_content'), 10, 2);

        // attach background to post
        add_action('peepso_activity_post_attachment', array($this, 'attach_background'), 10, 1);
        add_filter('peepso_activity_content', array($this, 'activity_content'), 10, 2);

        // disable repost and edit
        add_filter('peepso_post_filters', array(&$this, 'post_filters'), 10);
    }

    /**
     * Retrieve singleton class instance
     * @return PostBackgrounds instance
     */
    public static function get_instance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }

    /*
     * enqueue scripts for post backgrounds
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('peepso-postbox-backgrounds', PeepSo::get_asset('js/postbox/postbox-backgrounds.js'),
            array('jquery', 'jquery-ui-sortable', 'peepso', 'peepso-postbox'), PeepSo::PLUGIN_VERSION, TRUE);

        add_filter('peepso_data', function($data) {
            $data['backgrounds'] = [
                'post_max_length' => PeepSo::get_option_new('post_backgrounds_max_length'),
                'post_max_linebreaks' => PeepSo::get_option_new('post_backgrounds_max_linebreaks')
            ];

            return $data;
        });
    }

    public function post_types($post_types, $params = array())
    {
        if ((isset($params['is_current_user']) && $params['is_current_user'] === false) || !apply_filters('peepso_permissions_post_backgrounds_create', TRUE)) {
            return ($post_types);
        }

        $post_types['post_backgrounds'] = array(
            'icon' => 'gcis gci-brush',
            'name' => __('Post background', 'peepso-core'),
            'class' => 'ps-postbox__menu-item',
        );

        return ($post_types);
    }

    public function postbox_tabs($tabs)
    {
		$PeepSoPostBackgroundsModel = new PeepSoPostBackgroundsModel(FALSE);

        $tabs['post_backgrounds'] = PeepSoTemplate::exec_template('post-backgrounds', 'postbox', [
            'post_backgrounds' => $PeepSoPostBackgroundsModel->post_backgrounds,
        ], true);

        return $tabs;
    }

    public function postbox_interactions($interactions, $params = array())
    {
        if ((isset($params['is_current_user']) && $params['is_current_user'] === false) && !apply_filters('peepso_permissions_post_backgrounds_create', TRUE)) {
            return ($interactions);
        }


        $interactions['post_backgrounds'] = array(
            'icon' => 'gcis gci-grin-hearts',
            'id' => 'post_backgrounds',
            'class' => 'ps-postbox__menu-item',
            'click' => 'return;',
            'label' => '',
            'title' => __('Post background', 'peepso-core'),
            'style' => 'display:none',
        );

        return ($interactions);
    }
    /**
     * Sets the activity's module ID to the plugin's module ID
     * @param  array $activity
     * @return array
     */
    public function activity_insert_data($activity)
    {
        $input = new PeepSoInput();

        // SQL safe
        $type = $input->value('type', '', false);

        if ($type == 'post_backgrounds') {
            $activity['act_module_id'] = self::MODULE_ID;
        }

        return ($activity);
    }

    /**
     * Adds the postmeta to the post, only called when submitting from the post backgrounds tab
     * @param  int $post_id The post ID
     */
    public function after_add_post($post_id)
    {
        $act = PeepSoActivity::get_instance()->get_activity_data($post_id, self::MODULE_ID);
        if (is_object($act)) {
            $input = new PeepSoInput();
            $background = $input->value('background', null, false); // SQL safe, add_post_meta
            $text_color = $input->value('text_color', '#fff', false); // SQL safe, add_post_meta
            $preset_id = $input->value('preset_id', null, false); // SQL safe, add_post_meta

            add_post_meta($post_id, 'peepso_post_background', json_encode([
                'background' => $background,
                'text_color' => $text_color,
                'preset_id' => $preset_id
            ]));
        }
    }

    /**
     * Attach the background to the post display
     * @param  object $post The post
     */
    public function attach_background($post)
    {
        if ($post->act_module_id != self::MODULE_ID) {
            return;
        }

        $post_meta = json_decode(get_post_meta($post->ID, 'peepso_post_background', true));
        $text_color = $post_meta->text_color;
        $background = $post_meta->background;

        $content = nl2br($post->post_content);

        $data = array(
            'id' => $post->ID,
            'text_color' => $text_color,
            'background' => $background,
            'content' => $content,
        );

        PeepSoTemplate::exec_template('post-backgrounds', 'content', $data);
    }

    public function activity_content($content, $post)
    {
        if ($post->act_module_id != self::MODULE_ID) {
            return $content;
        } else {
            return '';
        }
    }

    /**
     * Disable repost on post backgrounds
     * @param array $actions The default options per post
     * @return  array
     */
    public function post_filters($actions)
    {
        if ($actions['post']->act_module_id == self::MODULE_ID) {
            global $post;
            unset($actions['acts']['repost']);
            //unset($actions['acts']['remove_link_preview']);

            if (isset($actions['acts']['edit'])) {
                $actions['acts']['edit']['click'] = 'PsPostBackground.editPost(' . $post->ID . ', ' . $post->act_id . '); return false';
            }
        }
        return $actions;
    }

    public function activity_post_content($content, $id)
    {
        $act = PeepSoActivity::get_instance()->get_activity_data($id, self::MODULE_ID);

        if (is_object($act)) {
            $content = htmlspecialchars_decode($content);
            $content = strip_tags($content);
        };

        return $content;
    }
}
