<?php

class myCREDHook extends myCRED_Hook {

    function __construct($hook_prefs, $type = 'mycred_default') {

        parent::__construct(array(
            'id' => 'peepsocreds',
            'defaults' => array(
                'new_peepso-post' => array(
                    'creds' => 1,
                    'log' => '%plural% for new post',
                    'limit' => '0/x'
                ),
                'delete_peepso-post' => array(
                    'creds' => 0,
                    'log' => '%singular% deduction for deleted post'
                ),
                'new_peepso-message' => array(
                    'creds' => 1,
                    'log' => '%plural% for new message',
                    'author' => 0,
                    'limit' => '0/x'
                ),
                'delete_peepso-message' => array(
                    'creds' => 0,
                    'log' => '%singular% deduction for deleted message'
                ),
                'new_peepso-comment' => array(
                    'creds' => 1,
                    'log' => '%plural% for new comment',
                    'author' => 0,
                    'limit' => '0/x'
                ),
                'delete_peepso-comment' => array(
                    'creds' => 0,
                    'log' => '%singular% deduction for deleted comment'
                ),
                'add_peepso_friend' => array(
                    'creds' => 1,
                    'log' => '%plural% for add friend',
                    'limit' => '0/x'
                ),
                'delete_peepso_friend' => array(
                    'creds' => 0,
                    'log' => '%singular% deduction for deleted friend'
                ),
                'like_peepso_content' => array(
                    'creds' => 1,
                    'log' => '%plural% for like content/profile',
                    'limit' => '0/x'
                ),
                'unlike_peepso_content' => array(
                    'creds' => 0,
                    'log' => '%singular% deduction for unlike content/profile'
                ),
                //
                // Photos
                //
                'new_peepso_stream_photo' => array(
                    'creds' => 1,
                    'log' => '%plural% for new stream photo',
                    'limit' => '0/x'
                ),
                'delete_peepso_stream_photo' => array(
                    'creds' => 0,
                    'log' => '%singular% deduction for deleted stream photo'
                ),
                'new_peepso_profile_cover' => array(
                    'creds' => 1,
                    'log' => '%plural% for new profile cover',
                    'limit' => '0/x'
                ),
                'delete_peepso_profile_cover' => array(
                    'creds' => 0,
                    'log' => '%singular% deduction for deleted profile cover'
                ),
                'new_peepso_profile_avatar' => array(
                    'creds' => 1,
                    'log' => '%plural% for new profile avatar',
                    'limit' => '0/x'
                ),
                'delete_peepso_profile_avatar' => array(
                    'creds' => 0,
                    'log' => '%singular% deduction for deleted profile avatar'
                ),
            ),
        ), $hook_prefs, $type);			
    }		


    public function run() {
        // license checking
        if (!class_exists('PeepSoLicense') || !PeepSoLicense::check_license(PeepSoMyCreds::PLUGIN_EDD, PeepSoMyCreds::PLUGIN_SLUG)) {
            return;
        }

        // New Post
        // New Message
        // New Comment
        add_action('transition_post_status', array($this, 'publishing_peepso_content'), 10, 3);

        // Delete Post
        // Delete Message
        // Delete Comment
        add_action('peepso_delete_content', array($this, 'deleting_peepso_content'), 10, 1);

        // Like Post
        // Like Comment
        // Like Profile
        add_action('peepso_action_like_add', array($this, 'like_peepso_content'), 10, 1);

        // Un-Like Post
        // Un-Like Comment
        // Un-Like Profile
        add_action('peepso_action_like_remove', array($this, 'unlike_peepso_content'), 10, 1);

        // Friends add/delete
        add_action('peepsofriends_after_add', array($this, 'peepsofriends_after_add'), 10, 2);
        add_action('peepsofriends_after_delete', array($this, 'peepsofriends_after_delete'), 10, 2);
        add_action('peepso_friends_after_delete', array($this, 'peepsofriends_after_delete'), 10, 2);
    }

    function like_peepso_content($data) {

        $from_id = isset($data->like_user_id) ? $data->like_user_id : '';
        $to_id = isset($data->like_user_id) ? $data->like_user_id : '';

        // Check if params is missing
        if (empty($from_id) || empty($to_id))
            return;

        // Check if user is excluded
        if ($this->core->exclude_user($from_id))
            return;

        // Limit
        if ($this->over_hook_limit('like_peepso_content', 'like_peepso_content', $from_id))
            return;

        // Execute
        $this->core->add_creds(
            'like_peepso_content', $from_id, $this->prefs['like_peepso_content']['creds'], $this->prefs['like_peepso_content']['log'], $to_id, array('ref_type' => 'post'), $this->mycred_type
        );
    }

    function unlike_peepso_content($data) {

        $from_id = isset($data->like_user_id) ? $data->like_user_id : '';
        $to_id = isset($data->like_user_id) ? $data->like_user_id : '';
        $module_id = isset($data->like_module_id) ? $data->like_module_id : '';

        // Check if params is missing
        if (empty($from_id) || empty($to_id))
            return;

        // Check if user is excluded
        if ($this->core->exclude_user($from_id))
            return;

        if ($this->has_entry('like_peepso_content_' . $module_id, $from_id, $to_id) && !$this->has_entry('like_peepso_content_' . $module_id, $to_id, $from_id)) {

            $tmp = $from_id;
            $from_id = $to_id;
            $to_id = $tmp;
        }

        if ($this->has_entry('unlike_peepso_content_' . $module_id, $to_id, $from_id))
            return;

        // Execute
        $this->core->add_creds(
            'unlike_peepso_content_' . $module_id, $from_id, $this->prefs['unlike_peepso_content']['creds'], $this->prefs['unlike_peepso_content']['log'], $to_id, array('ref_type' => 'post'), $this->mycred_type
        );
    }

    function peepsofriends_after_add($from_id, $to_id) {

        // Check if user is excluded
        if ($this->core->exclude_user($from_id))
            return;

        // Limit
        if ($this->over_hook_limit('add_peepso_friend', 'add_peepso_friend', $from_id))
            return;

        // Make sure this is unique event
        if ($this->has_entry('add_peepso_friend', $to_id, $from_id))
            return;

        // Execute
        $this->core->add_creds(
            'add_peepso_friend', $from_id, $this->prefs['add_peepso_friend']['creds'], $this->prefs['add_peepso_friend']['log'], $to_id, array('ref_type' => 'post'), $this->mycred_type
        );
    }

    function peepsofriends_after_delete($from_id, $to_id) {

        // Check if user is excluded
        if ($this->core->exclude_user($from_id))
            return;

        if ($this->has_entry('add_peepso_friend', $from_id, $to_id) && !$this->has_entry('add_peepso_friend', $to_id, $from_id)) {

            $tmp = $from_id;
            $from_id = $to_id;
            $to_id = $tmp;
        }

        if ($this->has_entry('delete_peepso_friend', $to_id, $from_id))
            return;

        // Execute
        $this->core->add_creds(
            'delete_peepso_friend', $from_id, $this->prefs['delete_peepso_friend']['creds'], $this->prefs['delete_peepso_friend']['log'], $to_id, array('ref_type' => 'post'), $this->mycred_type
        );
    }

    public function publishing_peepso_content($new_status, $old_status, $post) {

        $user_id = $post->post_author;

        // Check for exclusions
        if ($this->core->exclude_user($user_id) === true)
            return;

        $post_id = $post->ID;
        $post_type = $post->post_type;

        $new_post_type = 'new_' . $post_type;

        if (!in_array($post->post_type, array('peepso-post', 'peepso-message', 'peepso-comment')))
            return; // it is not peepso content

        $is_photo = false;

        if ('peepso-post' == $post->post_type && class_exists('PeepSoSharePhotos')) {

            //
            // May be it is photo? // pho_post_id
            //
            global $wpdb;

            // pho_album_name
            $sql = 'SELECT p.`pho_id`, a.pho_system_album 
FROM `' . $wpdb->prefix . 'peepso_photos` p INNER JOIN `' . $wpdb->prefix . 'peepso_photos_album` a ON a.pho_album_id = p.pho_album_id
WHERE p.pho_post_id = ' . (1 * $post_id);

            $photos = $wpdb->get_results($sql);

            if (!empty($photos)) {

                foreach ($photos as $photo) {

                    if (3 == $photo->pho_system_album) {
                        $new_post_type = 'new_peepso_stream_photo';
                        $is_photo = true;
                    } else {
                        if (2 == $photo->pho_system_album) {
                            $new_post_type = 'new_peepso_profile_cover';
                            $is_photo = true;
                        } else {
                            if (1 == $photo->pho_system_album) {
                                $new_post_type = 'new_peepso_profile_avatar';
                                $is_photo = true;
                            } else {
                                // unknow album, do nothing
                            }
                        }
                    }

                    break;
                }
            }
        }

        // Make sure we award points other then zero
        if (!isset($this->prefs[$new_post_type]['creds']))
            return;
        if (empty($this->prefs[$new_post_type]['creds']) || $this->prefs[$new_post_type]['creds'] == 0)
            return;

        // We want to fire when content get published or when it gets privatly published
        $status = apply_filters('mycred_publish_hook_old', array('new', 'auto-draft', 'draft', 'private', 'pending', 'future'));
        $publish_status = apply_filters('mycred_publish_hook_new', array('publish', 'private'));

        if ((
                ($is_photo && 'publish' == $new_status && 'publish' == $old_status) ||
                ($is_photo && 'pending' == $new_status && 'pending' == $old_status) ||
                (in_array($old_status, $status) && in_array($new_status, $publish_status))
            ) && array_key_exists($new_post_type, $this->prefs)) {

            // Prep
            $entry = $this->prefs[$new_post_type]['log'];
            $data = array('ref_type' => 'post');

            // Make sure this is unique
            if ($this->core->has_entry($new_post_type, $post_id, $user_id, $data, $this->mycred_type))
                return;

            // Check limit
            if (!$this->over_hook_limit($new_post_type, $new_post_type, $user_id)) {

                $this->core->add_creds(
                    $new_post_type, $user_id, $this->prefs[$new_post_type]['creds'], $entry, $post_id, $data, $this->mycred_type
                );
            }
        }
    }

    public function deleting_peepso_content($post_id) {
        
        $post = get_post($post_id);

        if (!$post || !is_object($post))
            return;

        $user_id = $post->post_author;

        // Check for exclusions
        if ($this->core->exclude_user($user_id) === true)
            return;

        $post_type = $post->post_type;

        $delete_post_type = 'delete_' . $post_type;

        if (!in_array($post->post_type, array('peepso-post', 'peepso-message', 'peepso-comment')))
            return; // it is not peepso content

        $is_photo = false;

        if ('peepso-post' == $post->post_type) {

            //
            // May be it is photo? // pho_post_id
            //
            global $wpdb;

            // pho_album_name
            $sql = 'SELECT p.`pho_id`, a.pho_system_album 
FROM `' . $wpdb->prefix . 'peepso_photos` p INNER JOIN `' . $wpdb->prefix . 'peepso_photos_album` a ON a.pho_album_id = p.pho_album_id
WHERE p.pho_post_id = ' . (1 * $post_id);

            $photos = $wpdb->get_results($sql);
            
            if (!empty($photos)) {

                foreach ($photos as $photo) {

                    if (3 == $photo->pho_system_album) {
                        $delete_post_type = 'delete_peepso_stream_photo';
                        $is_photo = true;
                    } else {
                        if (2 == $photo->pho_system_album) {
                            $delete_post_type = 'delete_peepso_profile_cover';
                            $is_photo = true;
                        } else {
                            if (1 == $photo->pho_system_album) {
                                $delete_post_type = 'delete_peepso_profile_avatar';
                                $is_photo = true;
                            } else {
                                // unknow album, do nothing
                            }
                        }
                    }

                    break;
                }
            }
        }

        if (array_key_exists($delete_post_type, $this->prefs)) {

            // Prep
            $entry = $this->prefs[$delete_post_type]['log'];
            $data = array('ref_type' => 'post');

            // Make sure this is unique
            if ($this->core->has_entry($delete_post_type, $post_id, $user_id, $data, $this->mycred_type))
                return;

            $this->core->add_creds(
                $delete_post_type, $user_id, $this->prefs[$delete_post_type]['creds'], $entry, $post_id, $data, $this->mycred_type
            );
        }
    }

    public function preferences() {

        $prefs = $this->prefs;

        if (!isset($prefs['new_peepso-post']['limit']))
            $prefs['new_peepso-post']['limit'] = '0/x';

        if (!isset($prefs['new_peepso-message']['limit']))
            $prefs['new_peepso-message']['limit'] = '0/x';

        if (!isset($prefs['new_peepso-comment']['limit']))
            $prefs['new_peepso-comment']['limit'] = '0/x';
        ?>
        <!-- Creds for New PeepSo Post -->
        <label for="<?php echo $this->field_id(array('new_peepso-post', 'creds')); ?>"
                class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for New PeepSo Post', 'mycred')); ?></label>
        <ol>
            <li>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso-post', 'creds')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso-post', 'creds')); ?>"
                                        value="<?php echo $this->core->number($prefs['new_peepso-post']['creds']); ?>" size="8" /></div>
            </li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso-post', 'limit')); ?>"><?php echo __('Limit', 'mycred'); ?></label>
                <?php echo $this->hook_limit_setting($this->field_name(array('new_peepso-post', 'limit')), $this->field_id(array('new_peepso-post', 'limit')), $prefs['new_peepso-post']['limit']);
                ?>
            </li>
            <li class="empty">&nbsp;</li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso-post', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso-post', 'log')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso-post', 'log')); ?>" value="<?php echo esc_attr($prefs['new_peepso-post']['log']); ?>"
                                        class="long" /></div>
                <span class="description"><?php echo $this->available_template_tags(array('general', 'post')); ?></span>
            </li>
        </ol>
        <?php if (1) { ?>
            <!-- Creds for Deleting PeepSo Post -->
            <label for="<?php echo $this->field_id(array('delete_peepso-post', 'creds')); ?>"
                    class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for PeepSo Post Deletion', 'mycred')); ?></label>
            <ol>
                <li>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso-post', 'creds')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso-post', 'creds')); ?>"
                                            value="<?php echo $this->core->number($prefs['delete_peepso-post']['creds']); ?>" size="8" /></div>
                </li>
                <li class="empty">&nbsp;</li>
                <li>
                    <label for="<?php echo $this->field_id(array('delete_peepso-post', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso-post', 'log')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso-post', 'log')); ?>" value="<?php echo esc_attr($prefs['delete_peepso-post']['log']); ?>"
                                            class="long" /></div>
                    <span class="description"><?php echo $this->available_template_tags(array('general')); ?></span>
                </li>
            </ol>
        <?php } ?>
        <!-- Creds for New PeepSo Message -->
        <label for="<?php echo $this->field_id(array('new_peepso-message', 'creds')); ?>"
                class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for New PeepSo Message', 'mycred')); ?></label>
        <ol>
            <li>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso-message', 'creds')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso-message', 'creds')); ?>"
                                        value="<?php echo $this->core->number($prefs['new_peepso-message']['creds']); ?>" size="8" /></div>
            </li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso-message', 'limit')); ?>"><?php echo __('Limit', 'mycred'); ?></label>
                <?php echo $this->hook_limit_setting($this->field_name(array('new_peepso-message', 'limit')), $this->field_id(array('new_peepso-message', 'limit')), $prefs['new_peepso-message']['limit']);
                ?>
            </li>
            <li class="empty">&nbsp;</li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso-message', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso-message', 'log')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso-message', 'log')); ?>" value="<?php echo esc_attr($prefs['new_peepso-message']['log']); ?>"
                                        class="long" /></div>
                <span class="description"><?php echo $this->available_template_tags(array('general', 'post')); ?></span>
            </li>
            <?php if (0) { ?>
                <li class="empty">&nbsp;</li>
                <li>
                    <input type="checkbox" name="<?php echo $this->field_name(array('new_peepso-message' => 'author')); ?>"
                            id="<?php echo $this->field_id(array('new_peepso-message' => 'author')); ?>" <?php checked($prefs['new_peepso-message']['author'], 1); ?> value="1" />
                    <label for="<?php echo $this->field_id(array('new_peepso-message' => 'author')); ?>"><?php echo $this->core->template_tags_general(__('PeepSo Message authors can receive %_plural% for creating new message.', 'mycred')); ?></label>
                </li>
            <?php } ?>
        </ol>
        <?php if (1) { ?>
            <!-- Creds for Deleting PeepSo Message -->
            <label for="<?php echo $this->field_id(array('delete_peepso-message', 'creds')); ?>" class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for PeepSo Message Deletion', 'mycred')); ?></label>
            <ol>
                <li>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso-message', 'creds')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso-message', 'creds')); ?>"
                                            value="<?php echo $this->core->number($prefs['delete_peepso-message']['creds']); ?>" size="8" /></div>
                </li>
                <li class="empty">&nbsp;</li>
                <li>
                    <label for="<?php echo $this->field_id(array('delete_peepso-message', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso-message', 'log')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso-message', 'log')); ?>"
                                            value="<?php echo esc_attr($prefs['delete_peepso-message']['log']); ?>" class="long" /></div>
                    <span class="description"><?php echo $this->available_template_tags(array('general')); ?></span>
                </li>
            </ol>
        <?php } ?>
        <!-- Creds for New PeepSo Comment -->
        <label for="<?php echo $this->field_id(array('new_peepso-comment', 'creds')); ?>" class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for New PeepSo Comment', 'mycred')); ?></label>
        <ol>
            <li>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso-comment', 'creds')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso-comment', 'creds')); ?>"
                                        value="<?php echo $this->core->number($prefs['new_peepso-comment']['creds']); ?>" size="8" /></div>
            </li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso-comment', 'limit')); ?>"><?php echo __('Limit', 'mycred'); ?></label>
                <?php echo $this->hook_limit_setting($this->field_name(array('new_peepso-comment', 'limit')), $this->field_id(array('new_peepso-comment', 'limit')), $prefs['new_peepso-comment']['limit']);
                ?>
            </li>
            <li class="empty">&nbsp;</li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso-comment', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso-comment', 'log')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso-comment', 'log')); ?>"
                                        value="<?php echo esc_attr($prefs['new_peepso-comment']['log']); ?>" class="long" /></div>
                <span class="description"><?php echo $this->available_template_tags(array('general', 'post')); ?></span>
            </li>
            <?php if (0) { ?>
                <li class="empty">&nbsp;</li>
                <li>
                    <input type="checkbox" name="<?php echo $this->field_name(array('new_peepso-comment' => 'author')); ?>"
                            id="<?php echo $this->field_id(array('new_peepso-comment' => 'author')); ?>" <?php checked($prefs['new_peepso-comment']['author'], 1); ?> value="1" />
                    <label for="<?php echo $this->field_id(array('new_peepso-comment' => 'author')); ?>"><?php echo $this->core->template_tags_general(__('PeepSo Message authors can receive %_plural% for replying to their own Message', 'mycred')); ?></label>
                </li>
            <?php } ?>
        </ol>
        <?php if (1) { ?>
            <!-- Creds for Deleting Comment -->
            <label for="<?php echo $this->field_id(array('delete_peepso-comment', 'creds')); ?>" class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for Comment Deletion', 'mycred')); ?></label>
            <ol>
                <li>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso-comment', 'creds')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso-comment', 'creds')); ?>"
                                            value="<?php echo $this->core->number($prefs['delete_peepso-comment']['creds']); ?>" size="8" /></div>
                </li>
                <li class="empty">&nbsp;</li>
                <li>
                    <label for="<?php echo $this->field_id(array('delete_peepso-comment', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso-comment', 'log')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso-comment', 'log')); ?>"
                                            value="<?php echo esc_attr($prefs['delete_peepso-comment']['log']); ?>" class="long" /></div>
                    <span class="description"><?php echo $this->available_template_tags(array('general')); ?></span>
                </li>
            </ol>
        <?php } ?>


        <!-- Creds for Like PeepSo Content -->
        <label for="<?php echo $this->field_id(array('like_peepso_content', 'creds')); ?>"
                class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for Like PeepSo Content / Profile', 'mycred')); ?></label>
        <ol>
            <li>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('like_peepso_content', 'creds')); ?>"
                                        id="<?php echo $this->field_id(array('like_peepso_content', 'creds')); ?>"
                                        value="<?php echo $this->core->number($prefs['like_peepso_content']['creds']); ?>" size="8" /></div>
            </li>
            <li>
                <label for="<?php echo $this->field_id(array('like_peepso_content', 'limit')); ?>"><?php echo __('Limit', 'mycred'); ?></label>
                <?php echo $this->hook_limit_setting($this->field_name(array('like_peepso_content', 'limit')), $this->field_id(array('like_peepso_content', 'limit')), $prefs['like_peepso_content']['limit']);
                ?>
            </li>
            <li class="empty">&nbsp;</li>
            <li>
                <label for="<?php echo $this->field_id(array('like_peepso_content', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('like_peepso_content', 'log')); ?>"
                                        id="<?php echo $this->field_id(array('like_peepso_content', 'log')); ?>" value="<?php echo esc_attr($prefs['like_peepso_content']['log']); ?>"
                                        class="long" /></div>
                <span class="description"><?php echo $this->available_template_tags(array('general', 'post')); ?></span>
            </li>
        </ol>
        <?php if (1) { ?>
            <!-- Creds for Un-Like PeepSo Content -->
            <label for="<?php echo $this->field_id(array('unlike_peepso_content', 'creds')); ?>"
                    class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for Un-Like PeepSo Content / Profile', 'mycred')); ?></label>
            <ol>
                <li>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('unlike_peepso_content', 'creds')); ?>"
                                            id="<?php echo $this->field_id(array('unlike_peepso_content', 'creds')); ?>"
                                            value="<?php echo $this->core->number($prefs['unlike_peepso_content']['creds']); ?>" size="8" /></div>
                </li>
                <li class="empty">&nbsp;</li>
                <li>
                    <label for="<?php echo $this->field_id(array('unlike_peepso_content', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('unlike_peepso_content', 'log')); ?>"
                                            id="<?php echo $this->field_id(array('unlike_peepso_content', 'log')); ?>" value="<?php echo esc_attr($prefs['unlike_peepso_content']['log']); ?>"
                                            class="long" /></div>
                    <span class="description"><?php echo $this->available_template_tags(array('general')); ?></span>
                </li>
            </ol>
        <?php } ?>


        <!-- Creds for Add New PeepSo Freind -->
        <label for="<?php echo $this->field_id(array('add_peepso_friend', 'creds')); ?>"
                class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for Add New PeepSo Friend', 'mycred')); ?></label>
        <ol>
            <li>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('add_peepso_friend', 'creds')); ?>"
                                        id="<?php echo $this->field_id(array('add_peepso_friend', 'creds')); ?>"
                                        value="<?php echo $this->core->number($prefs['add_peepso_friend']['creds']); ?>" size="8" /></div>
            </li>
            <li>
                <label for="<?php echo $this->field_id(array('add_peepso_friend', 'limit')); ?>"><?php echo __('Limit', 'mycred'); ?></label>
                <?php echo $this->hook_limit_setting($this->field_name(array('add_peepso_friend', 'limit')), $this->field_id(array('add_peepso_friend', 'limit')), $prefs['add_peepso_friend']['limit']);
                ?>
            </li>
            <li class="empty">&nbsp;</li>
            <li>
                <label for="<?php echo $this->field_id(array('add_peepso_friend', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('add_peepso_friend', 'log')); ?>"
                                        id="<?php echo $this->field_id(array('add_peepso_friend', 'log')); ?>" value="<?php echo esc_attr($prefs['add_peepso_friend']['log']); ?>"
                                        class="long" /></div>
                <span class="description"><?php echo $this->available_template_tags(array('general', 'post')); ?></span>
            </li>
        </ol>
        <?php if (1) { ?>
            <!-- Creds for Deleting PeepSo Firend -->
            <label for="<?php echo $this->field_id(array('delete_peepso_friend', 'creds')); ?>"
                    class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for PeepSo Friend Deletion', 'mycred')); ?></label>
            <ol>
                <li>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso_friend', 'creds')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso_friend', 'creds')); ?>"
                                            value="<?php echo $this->core->number($prefs['delete_peepso_friend']['creds']); ?>" size="8" /></div>
                </li>
                <li class="empty">&nbsp;</li>
                <li>
                    <label for="<?php echo $this->field_id(array('delete_peepso_friend', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso_friend', 'log')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso_friend', 'log')); ?>" value="<?php echo esc_attr($prefs['delete_peepso_friend']['log']); ?>"
                                            class="long" /></div>
                    <span class="description"><?php echo $this->available_template_tags(array('general')); ?></span>
                </li>
            </ol>
        <?php } ?>

        <!-- Creds for Add New PeepSo Stream Photo -->
        <label for="<?php echo $this->field_id(array('new_peepso_stream_photo', 'creds')); ?>"
                class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for Add New PeepSo Stream Photo', 'mycred')); ?></label>
        <ol>
            <li>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso_stream_photo', 'creds')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso_stream_photo', 'creds')); ?>"
                                        value="<?php echo $this->core->number($prefs['new_peepso_stream_photo']['creds']); ?>" size="8" /></div>
            </li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso_stream_photo', 'limit')); ?>"><?php echo __('Limit', 'mycred'); ?></label>
                <?php echo $this->hook_limit_setting($this->field_name(array('new_peepso_stream_photo', 'limit')), $this->field_id(array('new_peepso_stream_photo', 'limit')), $prefs['new_peepso_stream_photo']['limit']);
                ?>
            </li>
            <li class="empty">&nbsp;</li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso_stream_photo', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso_stream_photo', 'log')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso_stream_photo', 'log')); ?>" value="<?php echo esc_attr($prefs['new_peepso_stream_photo']['log']); ?>"
                                        class="long" /></div>
                <span class="description"><?php echo $this->available_template_tags(array('general', 'post')); ?></span>
            </li>
        </ol>
        <?php if (1) { ?>
            <!-- Creds for Deleting PeepSo Stream Photo -->
            <label for="<?php echo $this->field_id(array('delete_peepso_stream_photo', 'creds')); ?>"
                    class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for deleting PeepSo Stream Photo', 'mycred')); ?></label>
            <ol>
                <li>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso_stream_photo', 'creds')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso_stream_photo', 'creds')); ?>"
                                            value="<?php echo $this->core->number($prefs['delete_peepso_stream_photo']['creds']); ?>" size="8" /></div>
                </li>
                <li class="empty">&nbsp;</li>
                <li>
                    <label for="<?php echo $this->field_id(array('delete_peepso_stream_photo', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso_stream_photo', 'log')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso_stream_photo', 'log')); ?>" value="<?php echo esc_attr($prefs['delete_peepso_stream_photo']['log']); ?>"
                                            class="long" /></div>
                    <span class="description"><?php echo $this->available_template_tags(array('general')); ?></span>
                </li>
            </ol>
        <?php } ?>

        <!-- Creds for Add New PeepSo Profile Cover -->
        <label for="<?php echo $this->field_id(array('new_peepso_profile_cover', 'creds')); ?>"
                class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for Add New PeepSo Profile Cover', 'mycred')); ?></label>
        <ol>
            <li>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso_profile_cover', 'creds')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso_profile_cover', 'creds')); ?>"
                                        value="<?php echo $this->core->number($prefs['new_peepso_profile_cover']['creds']); ?>" size="8" /></div>
            </li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso_profile_cover', 'limit')); ?>"><?php echo __('Limit', 'mycred'); ?></label>
                <?php echo $this->hook_limit_setting($this->field_name(array('new_peepso_profile_cover', 'limit')), $this->field_id(array('new_peepso_profile_cover', 'limit')), $prefs['new_peepso_profile_cover']['limit']);
                ?>
            </li>
            <li class="empty">&nbsp;</li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso_profile_cover', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso_profile_cover', 'log')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso_profile_cover', 'log')); ?>" value="<?php echo esc_attr($prefs['new_peepso_profile_cover']['log']); ?>"
                                        class="long" /></div>
                <span class="description"><?php echo $this->available_template_tags(array('general', 'post')); ?></span>
            </li>
        </ol>
        <?php if (1) { ?>
            <!-- Creds for Deleting PeepSo Profile Cover -->
            <label for="<?php echo $this->field_id(array('delete_peepso_profile_cover', 'creds')); ?>"
                    class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for deleting PeepSo Profile Cover', 'mycred')); ?></label>
            <ol>
                <li>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso_profile_cover', 'creds')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso_profile_cover', 'creds')); ?>"
                                            value="<?php echo $this->core->number($prefs['delete_peepso_profile_cover']['creds']); ?>" size="8" /></div>
                </li>
                <li class="empty">&nbsp;</li>
                <li>
                    <label for="<?php echo $this->field_id(array('delete_peepso_profile_cover', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso_profile_cover', 'log')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso_profile_cover', 'log')); ?>" value="<?php echo esc_attr($prefs['delete_peepso_profile_cover']['log']); ?>"
                                            class="long" /></div>
                    <span class="description"><?php echo $this->available_template_tags(array('general')); ?></span>
                </li>
            </ol>
        <?php } ?>

        <!-- Creds for Add New PeepSo Profile Avatar -->
        <label for="<?php echo $this->field_id(array('new_peepso_profile_avatar', 'creds')); ?>"
                class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for Add New PeepSo Profile Avatar', 'mycred')); ?></label>
        <ol>
            <li>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso_profile_avatar', 'creds')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso_profile_avatar', 'creds')); ?>"
                                        value="<?php echo $this->core->number($prefs['new_peepso_profile_avatar']['creds']); ?>" size="8" /></div>
            </li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso_profile_avatar', 'limit')); ?>"><?php echo __('Limit', 'mycred'); ?></label>
                <?php echo $this->hook_limit_setting($this->field_name(array('new_peepso_profile_avatar', 'limit')), $this->field_id(array('new_peepso_profile_avatar', 'limit')), $prefs['new_peepso_profile_avatar']['limit']);
                ?>
            </li>
            <li class="empty">&nbsp;</li>
            <li>
                <label for="<?php echo $this->field_id(array('new_peepso_profile_avatar', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                <div class="h2"><input type="text" name="<?php echo $this->field_name(array('new_peepso_profile_avatar', 'log')); ?>"
                                        id="<?php echo $this->field_id(array('new_peepso_profile_avatar', 'log')); ?>" value="<?php echo esc_attr($prefs['new_peepso_profile_avatar']['log']); ?>"
                                        class="long" /></div>
                <span class="description"><?php echo $this->available_template_tags(array('general', 'post')); ?></span>
            </li>
        </ol>
        <?php if (1) { ?>
            <!-- Creds for Deleting PeepSo Profile Avatat -->
            <label for="<?php echo $this->field_id(array('delete_peepso_profile_avatar', 'creds')); ?>"
                    class="subheader"><?php echo $this->core->template_tags_general(__('%plural% for deleting PeepSo Profile Avatar', 'mycred')); ?></label>
            <ol>
                <li>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso_profile_avatar', 'creds')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso_profile_avatar', 'creds')); ?>"
                                            value="<?php echo $this->core->number($prefs['delete_peepso_profile_avatar']['creds']); ?>" size="8" /></div>
                </li>
                <li class="empty">&nbsp;</li>
                <li>
                    <label for="<?php echo $this->field_id(array('delete_peepso_profile_avatar', 'log')); ?>"><?php echo __('Log template', 'mycred'); ?></label>
                    <div class="h2"><input type="text" name="<?php echo $this->field_name(array('delete_peepso_profile_avatar', 'log')); ?>"
                                            id="<?php echo $this->field_id(array('delete_peepso_profile_avatar', 'log')); ?>" value="<?php echo esc_attr($prefs['delete_peepso_profile_avatar']['log']); ?>"
                                            class="long" /></div>
                    <span class="description"><?php echo $this->available_template_tags(array('general')); ?></span>
                </li>
            </ol>
        <?php } ?>

        <?php
    }

    function sanitise_preferences($data) {

        //print '<pre>';
        //print_r($data);
        //print '</pre>';
        //exit;

        if (isset($data['new_peepso-post']['limit']) && isset($data['new_peepso-post']['limit_by'])) {
            $limit = sanitize_text_field($data['new_peepso-post']['limit']);
            if ($limit == '')
                $limit = 0;
            $data['new_peepso-post']['limit'] = $limit . '/' . $data['new_peepso-post']['limit_by'];
            unset($data['new_peepso-post']['limit_by']);
        }

        if (isset($data['new_peepso-message']['limit']) && isset($data['new_peepso-message']['limit_by'])) {
            $limit = sanitize_text_field($data['new_peepso-message']['limit']);
            if ($limit == '')
                $limit = 0;
            $data['new_peepso-message']['limit'] = $limit . '/' . $data['new_peepso-message']['limit_by'];
            unset($data['new_peepso-message']['limit_by']);
        }

        if (isset($data['new_peepso-comment']['limit']) && isset($data['new_peepso-comment']['limit_by'])) {
            $limit = sanitize_text_field($data['new_peepso-comment']['limit']);
            if ($limit == '')
                $limit = 0;
            $data['new_peepso-comment']['limit'] = $limit . '/' . $data['new_peepso-comment']['limit_by'];
            unset($data['new_peepso-comment']['limit_by']);
        }

        if (isset($data['add_peepso_friend']['limit']) && isset($data['add_peepso_friend']['limit_by'])) {
            $limit = sanitize_text_field($data['add_peepso_friend']['limit']);
            if ($limit == '')
                $limit = 0;
            $data['add_peepso_friend']['limit'] = $limit . '/' . $data['add_peepso_friend']['limit_by'];
            unset($data['add_peepso_friend']['limit_by']);
        }

        if (isset($data['like_peepso_content']['limit']) && isset($data['like_peepso_content']['limit_by'])) {
            $limit = sanitize_text_field($data['like_peepso_content']['limit']);
            if ($limit == '')
                $limit = 0;
            $data['like_peepso_content']['limit'] = $limit . '/' . $data['like_peepso_content']['limit_by'];
            unset($data['like_peepso_content']['limit_by']);
        }

        //
        // Photos
        //
        if (isset($data['new_peepso_stream_photo']['limit']) && isset($data['new_peepso_stream_photo']['limit_by'])) {
            $limit = sanitize_text_field($data['new_peepso_stream_photo']['limit']);
            if ($limit == '')
                $limit = 0;
            $data['new_peepso_stream_photo']['limit'] = $limit . '/' . $data['new_peepso_stream_photo']['limit_by'];
            unset($data['new_peepso_stream_photo']['limit_by']);
        }

        if (isset($data['new_peepso_profile_cover']['limit']) && isset($data['new_peepso_profile_cover']['limit_by'])) {
            $limit = sanitize_text_field($data['new_peepso_profile_cover']['limit']);
            if ($limit == '')
                $limit = 0;
            $data['new_peepso_profile_cover']['limit'] = $limit . '/' . $data['new_peepso_profile_cover']['limit_by'];
            unset($data['new_peepso_profile_cover']['limit_by']);
        }
        if (isset($data['new_peepso_profile_avatar']['limit']) && isset($data['new_peepso_profile_avatar']['limit_by'])) {
            $limit = sanitize_text_field($data['new_peepso_profile_avatar']['limit']);
            if ($limit == '')
                $limit = 0;
            $data['new_peepso_profile_avatar']['limit'] = $limit . '/' . $data['new_peepso_profile_avatar']['limit_by'];
            unset($data['new_peepso_profile_avatar']['limit_by']);
        }

        return $data;
    }

}