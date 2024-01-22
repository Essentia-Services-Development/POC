<?php

class PeepSo3_REST_V1_Endpoint_Files extends PeepSo3_REST_V1_Endpoint {

    public function __construct() {

        parent::__construct();

        $this->current_user = get_current_user_id();
        $this->id = $this->input->int('id', 0); // the user id of profile being viewed
        $this->user_id = $this->input->int('user_id', 0); // the user id of profile being viewed
        $this->uid = $this->input->int('uid', 0); // the user id current logged in user
        $this->group_id = $this->input->int('group_id', 0);
        $this->page = $this->input->int('page', 1);
        $this->sort = $this->input->value('sort', 'desc', ['asc','desc']);
        $this->limit = $this->input->int('limit', 1);
        $this->module_id = $this->input->int('module_id', 0);
        $this->context = $this->input->value('context', 'profile');
        $this->files_model = new PeepSoFilesModel();
    }

    public function read() {
        $files_model = new PeepSoFilesModel();

        $offset = ($this->page - 1) * $this->limit;

        if ($this->page < 1) {
            $offset = 0;
        }

        if ($this->group_id) {
            $user_id = $this->uid;
        } else {
            $user_id = $this->user_id;
        }

        $args = [
            'user_id' => $user_id,
            'offset' => $offset,
            'limit' => $this->limit,
            'sort' => $this->sort
        ];

        $message = '';

        // for profile tabs
        if ($this->context == 'profile') {
            if ($this->group_id) {
                $args['group_id'] = $this->group_id;
            } else {
                $args['exclude_group_files'] = TRUE;

            }
        } else if($this->context == 'files_widget') {
            $args['user_id'] = $this->current_user;
            $args['exclude_group_files'] = TRUE;
        } else if($this->context == 'community_files_widget') {
            $args['exclude_group_files'] = TRUE;
            unset($args['user_id']);
        }

        $files = $this->files_model->get_user_files($args);
        $files_for_output = [];

        if (count($files)) {
            $message = 'success';

            foreach ($files as $file) {
                $files_for_output[] = PeepSoFileUploads::prepare_for_display($file);
            }
        } else {
            if ($this->context) {
                if ($this->group_id) {
                    $group = new PeepSoGroup($this->group_id);
                    if ($group) {
                        $message = sprintf(__("Group %s doesn't have any files yet", 'peepsofileuploads'), $group->name);
                    }
                } else {
                    if ($this->user_id == $this->uid) {
                        $message = __("You don't have any files yet", 'peepsofileuploads');
                    } else {
                        $user = PeepSoUser::get_instance($this->user_id);
                        $message = sprintf(__("%s doesn't have any files yet", 'peepsofileuploads'), $user->get_fullname());
                    }
                }
            }
        }

        return [
            'files' => $files_for_output,
            'message' => $message
        ];
    }

    public function delete() {
        if ($this->id) {
            $post = get_post($this->id);
            $post_parent = $post->post_parent;

            wp_delete_post($this->id);

            // check if parent post has another files
            $sql = $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->wpdb->posts} WHERE post_parent = %d", $post_parent);
            $count = $this->wpdb->get_var($sql);

            if (!$count) {
                // if empty, also delete the activity
                $peepso_activity = new PeepSoActivity();

                $activity = $peepso_activity->get_activity_data($post_parent, PeepSoFileUploads::MODULE_ID);
                if (!$activity && class_exists('PeepSoGroupsPlugin')) {
                    $activity = $peepso_activity->get_activity_data($post_parent);
                }

                if ($activity) {
                    add_filter('peepso_check_permissions-post_delete', '__return_true', 99);
                    $peepso_activity->delete_activity($activity->act_id);
                }

                $action = 'delete_activity';
            } else {
                $action = 'delete_file';
            }

            return [
                'success' => 'file_deleted',
                'action' => $action
            ];
        }

        return [
            'error' => 'file_not_deleted'
        ];
    }

    protected function can_read() {
        return TRUE;
    }

    protected function can_delete() {
        return PeepSoFileUploads::can_delete($this->id);
    }

}
