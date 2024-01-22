<?php

    class PeepSo3_REST_V1_Endpoint_Post extends PeepSo3_REST_V1_Endpoint {

        private $post_id;
        private $post;
        private $user_id;
        private $delta;

        private $table;

        public function __construct() {

            parent::__construct();


            $this->user_id = get_current_user_id();

            // default property map
            $this->state = array(
                'post_date_gmt' => NULL,
                'post_date' => NULL,
            );
        }


        public function read() {
            return array("hello" =>"world");
        }

        public function edit(WP_REST_Request $data) {

            $this->post_id = $this->input->int('post_id', 0);
            $this->post = get_post($this->post_id);

            // Post not found?
            if((!$this->post instanceof WP_POST) || !$this->post->ID) {
                return array('error'=>'post_not_found', 'reason' => 'get_post()','post_id'=>$this->post_id);
            }

            // User has the right to edit?
            if(!PeepSo::check_permissions(intval($this->post->author_id), PeepSo::PERM_POST_EDIT, $this->user_id)) {
                return array('error'=>'insufficient_permissions', 'reason' => 'PeepSo::check_permissions()','post_id'=>$this->post_id,'user_id'=>$this->user_id);
            }

            // Prepare
            $this->delta = [];
            $errors= [];

            $params = $data->get_body_params();

            foreach($this->state as $k=>$v) {

                // if the value was passed
                if(array_key_exists($k, $params)) {

                    $error = FALSE;

                    $new_value = $params[$k];

                    // are we validating it?
                    $validator = "validate_$k";
                    if (method_exists($this, $validator)) {

                        if (!$this->$validator($new_value)) {
                            $errors[] = array(
                                'error' => 'data_invalid',
                                'key' => $k,
                                'value' => $new_value,
                                'method' => get_class($this) . "::" . $validator,
                            );

                            $error = TRUE;
                        }
                    }

                    if (!$error) {

                        $modifier = "modify_$k";

                        if(method_exists($this, $modifier)) {
                            $new_value = $this->$modifier($new_value);
                        }

                        $this->delta[$k] = $new_value;
                    }
                }
            }

            if(count($errors)) {
                return array('error' => 'validation_failed','reason' => $errors);
            }

            if(count($this->delta)) {
                $this->delta['ID'] = $this->post_id;
                if(wp_update_post($this->delta)) {
                    return $this->delta;
                }

                return array('error' => 'update_failed', 'reason' => 'wp_update_post()');
            }
        }

        // VALIDATORS

        private function validate_post_date($v) {
            // not required
            if(!$v) {
                return TRUE;
            }
            $d = DateTime::createFromFormat('Y-m-d H:i:s', $v);
            return $d && $d->format('Y-m-d H:i:s') === $v;
        }


        // MODIFIERS
        private function modify_post_date($v)
        {
            // Apply GMT offset
            $v = strtotime($v);
            $v = $v - 3600 * PeepSoUser::get_gmt_offset($this->user_id);

            // We have to save GMT post_date too
            $this->delta['post_date_gmt'] = gmdate('Y-m-d H:i:s', $v);

            // Return adjusted GMDate
            return date('Y-m-d H:i:s', $v);
        }



        protected function can_create() {
            return FALSE;
        }

        protected function can_read() {
            return TRUE;
        }

        protected function can_edit() {
            // endpoint will handle it, wee nee the post object
            return TRUE;
        }


        protected function can_delete() {
            return FALSE;
        }

    }