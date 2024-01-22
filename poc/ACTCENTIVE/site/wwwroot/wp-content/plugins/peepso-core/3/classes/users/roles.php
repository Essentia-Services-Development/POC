<?php

class PeepSo3_Roles {

    public static function is_admin() {

        static $is_admin = NULL;

        if (NULL !== $is_admin) {
            return $is_admin;
        }

        // WP admins
        if (current_user_can('manage_options')) {
            return $is_admin = TRUE;
        }


        if (!get_current_user_id()) {
            return $is_admin = FALSE;
        }

        // TODO: use current_user_can() when we create capabilities


        // check the PeepSo user role
//        $role = self::_get_role();
//        if ('admin' === $role)
//            return ($is_admin = TRUE);

//		if (current_user_can('peepso_admin'))
//			return ($is_admin = TRUE);

        return $is_admin = FALSE;
    }

    public static function is_member() {

        static $is_member = FALSE;

        if (NULL !== $is_member) {
            return $is_member;
        }

        if (!get_current_user_id()) {
            return $is_member = FALSE;
        }

        // TODO: ability to have logged-in user to NOT be members of the community?

        return $is_member = TRUE;
    }
}