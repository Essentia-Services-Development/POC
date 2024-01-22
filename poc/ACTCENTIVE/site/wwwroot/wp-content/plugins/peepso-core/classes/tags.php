<?php

class PeepSoTags
{
    private static $_instance = NULL;
    private $user_ids = array();

    const SHORTCODE_TAG = 'peepso_tag';
    const MODULE_ID = 7;

    public $is_enabled = FALSE;

    /**
     * Initialize all variables, filters and actions
     */
    private function __construct()
    {
        $this->is_enabled = PeepSo::get_option('tags_enable', 0) == 1 ? TRUE : FALSE;
        add_action('peepso_init', array(&$this, 'init'));
        add_filter('peepso_remove_shortcodes', array(&$this, 'filter_remove_shortcode'));
    }

    /*
     * retrieve singleton class instance
     * @return instance reference to plugin
     */
    public static function get_instance()
    {
        if (NULL === self::$_instance)
            self::$_instance = new self();
        return (self::$_instance);
    }

    /*
     * Initialize the PeepSoTags plugin
     */
    public function init()
    {
        if (!is_admin()) {
            add_shortcode(self::SHORTCODE_TAG, array(&$this, 'shortcode_tag'));

            if ($this->is_enabled) {
                add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

                add_action('peepso_activity_after_add_post', array(&$this, 'after_save_post'), 10, 2);
                add_action('peepso_after_add_comment', array(&$this, 'after_save_comment'), 10, 4);

                add_filter('peepso_profile_notification_link', array(&$this, 'profile_notification_link'), 10, 2);
                add_filter('peepso_modify_link_item_notification', array(&$this, 'modify_link_item_notification'), 10, 2);
            }
        }

        // used by Profile page UI to configure alerts and notifications setting
        add_filter('peepso_activity_content_before', array(&$this, 'do_tags'));
    }

    /**
     * Registers the needed scripts and styles
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('peepsotags', PeepSo::get_asset('js/usertags.min.js'),
            array('peepso', 'peepso-observer'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_localize_script('peepsotags', 'peepsotagsdata', array(
            'parser' => $this->get_tag_parser(),
            'template' => $this->get_tag_template()
        ));
    }

    /**
     * Returns the regular expression that matches the markup for the @ character.
     * @return string
     */
    public function get_tag_parser()
    {
        $old_tag = '\[peepso_tag id=(\d+)\]([^\]]+)\[\/peepso_tag\]';
        $new_tag = '@peepso_user_(\d+)(?:\(([^\)]+)\))?';
        return apply_filters('peepso_tags_parser', $new_tag);
    }

    /**
     * Returns the template used to render the layout as key/value pairs.
     * @return string
     */
    public function get_tag_template()
    {
        $old_template = '[peepso_tag id=<%= id %>]<%= title %>[/peepso_tag]';
        $new_template = '@peepso_user_<%= id %>(<%= title %>)';
        return apply_filters('peepso_tags_template', $new_template);
    }

    /**
     * Renders the User's display name and profile link
     * @return string
     */
    public function shortcode_tag($atts, $content = '')
    {
        if (!isset($atts['id']) && empty($atts['id'])) { return; }

        $id = $atts['id'];

        $user = PeepSoUser::get_instance($id);
        if (!$content) {
            $content = $user->get_fullname();
        }

        $preferred_name = $content;

        global $post;

        $mention = "@peepso_user_{$atts['id']}($preferred_name)";
        $new_content = $post->post_content;
        $new_content = str_replace('[peepso_tag id='.$atts['id'].']'.$content.'[/peepso_tag]', $mention, $new_content);
        $new_excerpt = $post->post_excerpt;
        $new_excerpt = str_replace('[peepso_tag id='.$atts['id'].']'.$content.'[/peepso_tag]', $mention, $new_excerpt);

        wp_update_post(array('ID'=>$post->ID,'post_content'=>$new_content,'post_excerpt'=>$new_excerpt));

        return $mention;
    }

    /**
     * Fires once a post has been saved.
     * @param int $post_id Post ID.
     * @param int $act_id  The activity ID.
     */
    public function after_save_post($post_id, $act_id)
    {
        $post_obj = get_post($post_id);

        // #4219 Don't fire notifications for scheduled posts
        if($post_obj->post_status != 'publish') { return; }

        $match = preg_match_all('/' . $this->get_tag_parser() . '/i', $post_obj->post_content, $matches);

        if ($match) {
            global $post;

            $PeepSoActivity  = PeepSoActivity::get_instance();
            // TODO: not always successful. Should check return value
            $post_act = $PeepSoActivity->get_activity($act_id);

            $post = $post_obj;
            setup_postdata($post);

            $user_author = PeepSoUser::get_instance($post->post_author);

            $data = array('permalink' => $PeepSoActivity->post_link(FALSE));
            $from_fields = $user_author->get_template_fields('from');

            $this->user_ids = $matches[1];

            $notifications = new PeepSoNotifications();


            foreach ($this->user_ids as $user_id) {
                $_notification = PeepSo3_MultiLang__('mentioned you', 'peepso-core', $user_id);
                $user_id = intval($user_id);

                // If self don't send the notification
                if (intval($post->post_author) === $user_id)
                    continue;

                // Check access
                if (!PeepSo::check_permissions($user_id, PeepSo::PERM_POST_VIEW, intval($post->post_author)))
                    continue;

                // check act_owner is current user_id
                if($user_id != $post_act->act_owner_id) {
                    $user_owner = PeepSoUser::get_instance($user_id);
                    $data = array_merge($data, $from_fields, $user_owner->get_template_fields('user'));
                    // TODO: need to use an editable email message, not a constant string
                    // SpyDroid: the constant string is an email subject and not an editable email message, the template for editable email is the 4th parameter 'tagged'

                    $i18n = __('Someone mentioned you in a post', 'peepso-core');
                    $message = 'Someone mentioned you in a post';
                    $args = ['peepso-core'];

                    PeepSoMailQueue::add_notification_new($user_id, $data, $message, $args, 'tagged', 'tag', self::MODULE_ID);

                    $i18n = __('mentioned you in a post', 'peepso-core');
                    $message = 'mentioned you in a post';
                    $args = ['peepso-core'];

                    $notifications->add_notification_new(intval($post->post_author), $user_id, $message, $args, 'tag', self::MODULE_ID, $post_id, $act_id);
                }
                else
                {
                    // if tagged, modify notification
                    add_filter('peepso_notifications_data_before_add', array(&$this, 'modify_message_notification'), 10, 1);
                }
            }
        }
    }

    /**
     * Fires once a post has been saved.
     * @param int $post_id Post ID.
     * @param int $act_id  The activity ID.
     */
    public function after_save_comment($post_id, $act_id, $did_notify, $did_email)
    {
        $post_obj = get_post($post_id);
        $match = preg_match_all('/' . $this->get_tag_parser() . '/i', $post_obj->post_content, $matches);

        if ($match) {
            global $post;

            $PeepSoActivity = PeepSoActivity::get_instance();
            // TODO: not always successful. Should check return value
            $post_act = $PeepSoActivity->get_activity($act_id);
            $act_comment_object_id = $post_act->act_comment_object_id;
            $act_comment_module_id = $post_act->act_comment_module_id;
            //$comment_object_post = get_post($act_comment_object_id);

            $post = $post_obj;
            setup_postdata($post);

            $not_activity = $PeepSoActivity->get_activity_data($post_id, PeepSoActivity::MODULE_ID);
            $parent_activity = $PeepSoActivity->get_activity_data($not_activity->act_comment_object_id, $not_activity->act_comment_module_id);
            if (is_object($parent_activity)) {
                $not_post = $PeepSoActivity->get_activity_post($not_activity->act_id);
                $parent_post = $PeepSoActivity->get_activity_post($parent_activity->act_id);
                $parent_id = $parent_post->act_external_id;

                // check if parent post is a comment
                if($parent_post->post_type == 'peepso-comment') {
                    $comment_activity = $PeepSoActivity->get_activity_data($not_activity->act_comment_object_id, $not_activity->act_comment_module_id);
                    $post_activity = $PeepSoActivity->get_activity_data($comment_activity->act_comment_object_id, $comment_activity->act_comment_module_id);

                    $parent_post = $PeepSoActivity->get_activity_post($post_activity->act_id);
                    $parent_comment = $PeepSoActivity->get_activity_post($comment_activity->act_id);

                    $parent_link = PeepSo::get_page('activity_status') . $parent_post->post_title . '/?t=' . time() . '#comment.' . $post_activity->act_id . '.' . $parent_comment->ID . '.' . $comment_activity->act_id . '.' . $not_activity->act_external_id;
                } else {
                    $parent_link = PeepSo::get_page('activity_status') .  $parent_post->post_title . '/#comment.' . $parent_activity->act_id . '.' . $not_post->ID . '.' . $not_activity->act_external_id;
                }
            } else {
                $parent_link = $PeepSoActivity->post_link(FALSE);
            }


            $user_author = PeepSoUser::get_instance($post->post_author);
            $data = array('permalink' => $parent_link);
            $from_fields = $user_author->get_template_fields('from');

            $this->user_ids = $matches[1];

            $notifications = new PeepSoNotifications();

            foreach ($this->user_ids as $user_id) {
                $_notification = PeepSo3_MultiLang__('mentioned you', 'peepso-core', $user_id);
                $user_id = intval($user_id);

                // If self don't send the notification
                if (intval($post->post_author) === $user_id)
                    continue;

                // Check access
                if (!PeepSo::check_permissions($user_id, PeepSo::PERM_POST_VIEW, intval($post->post_author)))
                    continue;

                // if parent is owner don't add new notification
                // notification already sent for parent activity owner in peepso-core
                /*if (intval($comment_object_post->post_author) === $user_id)
                    continue;*/

                $users = $PeepSoActivity->get_comment_users($act_comment_object_id, $act_comment_module_id);
                $follower = array();
                while ($users->have_posts()) {

                    $users->next_post();

                    $follower[] = $users->post->post_author;
                }

                // if not following post send tagged notification
                if ((!in_array($user_id, $follower) && ($user_id != $post_act->act_owner_id)) || ($post_act->act_owner_id == $user_id && intval($post_act->act_comment_object_id) > 0)) {
                    $user_owner = PeepSoUser::get_instance($user_id);
                    $data = array_merge($data, $from_fields, $user_owner->get_template_fields('user'));
                    // TODO: need to use an editable email message, not a constant string
                    // SpyDroid: the constant string is an email subject and not an editable email message, the template for editable email is the 4th parameter 'tagged'

                    $i18n = __('Someone mentioned you in a comment', 'peepso-core');
                    $message = 'Someone mentioned you in a comment';
                    $args = ['peepso-core'];

                    if(!in_array($user_id, $did_email)) {
                        PeepSoMailQueue::add_notification_new($user_id, $data, $message, $args, 'tagged_comment', 'tag_comment', self::MODULE_ID);
                    }

                    $i18n = __('mentioned you in a comment', 'peepso-core');
                    $message = 'mentioned you in a comment';
                    $args = ['peepso-core'];

                    if(!in_array($user_id, $did_notify)) {
                        $notifications->add_notification_new(intval($post->post_author), $user_id, $message, $args, 'tag_comment', self::MODULE_ID, $post_id, $act_id);
                    }
                }
                else
                {
                    // if tagged, modify notification
                    add_filter('peepso_notifications_data_before_add', array(&$this, 'modify_message_notification'), 10, 1);
                }
            }
        }
    }

    /**
     * Modify message notification
     * @param array $notification
     * @return array modified $notification
     */
    public function modify_message_notification($notification=array())
    {
        /*array(
                'not_user_id' => $to_user,
                'not_from_user_id' => $from_user,
                'not_module_id' => $module_id,
                'not_external_id' => $external,
                'not_type' => substr($type, 0, 20),
                'not_message' => substr($msg, 0, 200),
                'not_timestamp' => current_time('mysql')
            )*/
        if(count($notification) > 0 && in_array($notification['not_user_id'], $this->user_ids))
        {
            if($notification['not_type'] == 'wall_post') {
                $notification['not_message'] = __('wrote and mentioned you on your wall', 'peepso-core') ;
            } else {
                $notification['not_message'] = __('mentioned you', 'peepso-core') ;
            }
        }

        return $notification;
    }

    /**
     * Modify link notification
     * @param array $link
     * @param array $note_data
     * @return string $link
     */
    public function profile_notification_link($link, $note_data) {

        if ('tag' === $note_data['not_type']) {

            // do nothing

        } else if ('tag_comment' === $note_data['not_type']) {

            $activities = PeepSoActivity::get_instance();

            $not_activity = $activities->get_activity_data($note_data['not_external_id'], PeepSoActivity::MODULE_ID);
            $parent_activity = $activities->get_activity_data($not_activity->act_comment_object_id, $not_activity->act_comment_module_id);

            if (is_object($parent_activity)) {

                $not_post = $activities->get_activity_post($not_activity->act_id);
                $parent_post = $activities->get_activity_post($parent_activity->act_id);
                $parent_id = $parent_post->act_external_id;

                // check if parent post is a comment
                if($parent_post->post_type == 'peepso-comment') {
                    $comment_activity = $activities->get_activity_data($not_activity->act_comment_object_id, $not_activity->act_comment_module_id);
                    $post_activity = $activities->get_activity_data($comment_activity->act_comment_object_id, $comment_activity->act_comment_module_id);

                    $parent_comment = $activities->get_activity_post($comment_activity->act_id);
                    $parent_post = $activities->get_activity_post($post_activity->act_id);

                    $link = PeepSo::get_page('activity_status') . $parent_post->post_title . '/?t=' . time() . '#comment.' . $post_activity->act_id . '.' . $parent_comment->ID . '.' . $comment_activity->act_id . '.' . $not_activity->act_external_id;
                } else {
                    $link = PeepSo::get_page('activity_status') . $parent_post->post_title . '/#comment.' . $parent_activity->act_id . '.' . $not_post->ID . '.' . $not_activity->act_id;
                }
            }
        }

        return $link;
    }

    /**
     * Modify link item notification
     * @param array array($print_link, $link)
     * @param array $note_data
     * @return string $link
     */
    public function modify_link_item_notification($link, $note_data) {

        // Print the bits only for legacy pre-translated notifications
        if(!strlen($note_data['not_message_args'])) {
            if ('tag' === $note_data['not_type']) {

                ob_start();
                echo ' ', __('in', 'peepso-core'), ' ', __('a post', 'peepso-core');

                $new_link = ob_get_clean();
            } else if ('tag_comment' === $note_data['not_type']) {
                ob_start();
                // Print the bits only for legacy pre-translated notifications
                if (!strlen($note_data['not_message_args'])) {
                    echo ' ', __('in', 'peepso-core'), ' ', __('a comment', 'peepso-core');
                }
                $new_link = ob_get_clean();
            }
        }

        if (isset($new_link)) {
            return $new_link;
        } else {
            return $link[0];
        }
    }


    /**
     * Add the User Tagged Email to the list of editable emails on the config page
     * @param  array $emails Array of editable emails
     * @return array
     */
    // TODO: move this into a PeepSoTaggingAdmin class
    public function config_email_tags($emails)
    {
        $emails['email_tagged'] = array(
            'title' => __('User Mentioned In Post', 'peepso-core'),
            'description' => __('This will be sent to a user when mentioned in a post.', 'peepso-core')
        );

        $emails['email_tagged_comment'] = array(
            'title' => __('User Tagged In Comment', 'peepso-core'),
            'description' => __('This will be sent to a user when mentioned in a comment.', 'peepso-core')
        );

        return ($emails);
    }


    /**
     * Remove peepso tags shortcode
     * @param string $string to process
     * @return string $string
     */
    public function filter_remove_shortcode($string)
    {
        $string = str_replace('[/peepso_tag]', '', $string);
        $string = preg_replace('/\[peepso_tag(?:.*?)\]/', '', $string);
        return $string;
    }

    /**
     * Do shortcode
     */
    public function do_tags($content)
    {
        if ( has_shortcode( $content, 'peepso_tag' ) ) {
            $content_tmp = $content;

            preg_match_all('/\[peepso_tag(?:.*?)\]/', $content, $matches);

            $offset = 0;
            $closed_tag = '[/peepso_tag]';
            if (isset($matches[0])) {
                foreach ($matches[0] as $match) {
                    $closed_tag_pos = strpos($content, $closed_tag, strpos($content, $match));
                    $name = substr($content, strpos($content, $match) + strlen($match), $closed_tag_pos - (strpos($content, $match) + strlen($match)));

                    $text_to_replace = $match . $name . $closed_tag;
                    $replace_with = do_shortcode($text_to_replace);
                    $content_tmp = str_replace($text_to_replace, $replace_with, $content_tmp);

                    $offset += ($closed_tag_pos + strlen($closed_tag));
                }
            }
            $content = $content_tmp;
        }


        return $content;
    }
}
