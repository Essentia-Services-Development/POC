<?php

class PeepSoMentionsAjax extends PeepSoAjaxCallback
{
    private $types = array(
        'user',
    );

    public function ajax_auth_exceptions()
    {
        return array(
            'get_mention',
        );
    }

    public function get_mention(PeepSoAjaxResponse $resp)
    {
        $user_id = get_current_user_id();

        $type = $this->_input->value('type', 'user', $this->types);
        $id = $this->_input->int('id', 0);
        $name = $this->_input->value('name', NULL, FALSE); // SQL safe, not used


        if(!in_array($type, $this->types)) {
            $resp->error('Unsupported type');
            die();
        }

        // @todo split processing into different object types - in 2.x  we only have users, but 3.x will have Groups etc
        $cache_id = md5($type.$id.$name);
        $cache_key = "ps_mention_{$cache_id}"; // length limit is 45 chars and MD5 is 32

        if($cache = PeepSo3_Mayfly::get($cache_key)) {
            $html = $cache;
        } else {

            // We will assume the user exists and return garbage if he doesn't
            $PeepSoUser = PeepSoUser::get_instance($id);

            // Original mentioned user name
            $display_name = trim(strip_tags($PeepSoUser->get_fullname()));

            // Fallback if multibyte is missing
            $strpos = 'strpos';
            if (function_exists('mb_strpos')) {
                $strpos = 'mb_strpos';
            }

            // If preferred name typed is a part of the original name, we can use it
            if (strlen($name) && is_int($strpos($display_name, $name))) {
                $display_name = $name;
            }

            // Grab before- and after- HTML from the filters (VIP etc)
            ob_start();
            do_action('peepso_action_render_user_name_before', $id);
            $before_fullname = ob_get_clean();

            ob_start();
            do_action('peepso_action_render_user_name_after', $id);
            $after_fullname = ob_get_clean();

            // Put everything together, cringe a little and dream of PeepSo 3.x with full REST + JSON & CSR
            $html = $before_fullname . sprintf('<a class="ps-tag__link ps-csr" href="%s" data-hover-card="%d">%s</a>', $PeepSoUser->get_profileurl(), $id, $display_name) . $after_fullname;

            PeepSo3_Mayfly::set($cache_key, $html, 60);
        }

        $resp->success(TRUE);
        $resp->set('html', $html);
    }
}
