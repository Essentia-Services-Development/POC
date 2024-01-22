<?php

class PeepSoProfile extends PeepSoAjaxCallback
{
    private $user_id = NULL;
    public $user = NULL;
    private $acting_user_id = NULL;

    private $notifications = NULL;
    private $num_notifications = 0;
    private $note_data = array();
    private $message = NULL;

    private $blocked = NULL;
    private $num_blocked = 0;
    private $block_idx = 0;
    private $block_data = array();
    private $_url_segments = null;

    public $note_idx;

    protected function __construct()
    {
        parent::__construct();
        $this->init();

        add_filter('peepso_postbox_access_settings', array(&$this, 'filter_postbox_access_settings'), 10, 1);
        $this->_url_segments = PeepSoUrlSegments::get_instance();
    }

    /**
     * Set which user is to be handled and return the user object
     * @param int $user_id The ID of the user - if "0" it will look for user_id in request or cascade to current user
     * @return PeepSoUser|null
     */
    public function init($user_id = 0)
    {
        // Only fire if PeepSoUser is empty OR the user_id is being overriden OR self::user_id is 0
        if(!$this->user instanceof PeepSoUser || 0!=$user_id || 0==$this->user_id) {
            if (0 == $user_id) {
                $PeepSoInput = new PeepSoInput();
                $user_id = $PeepSoInput->int('user_id', 0);
            }

            if (0 == $user_id) {
                $user_id = get_current_user_id();
            }

            $this->user_id = $user_id;

            $this->user = PeepSoUser::get_instance($this->user_id);

            $this->acting_user_id = get_current_user_id();
        }

        return $this->user;
    }

    /**
     * Check if editing self or being an admin
     * @return bool
     */
    public function can_edit()
    {
        if (get_current_user_id() == $this->user_id || PeepSo::is_admin()) {
            return (TRUE);
        }
        return (FALSE);
    }

    /**
     * Check if editing self or being an admin
     * @return bool
     */
    public function can_delete()
    {
        if (get_current_user_id() == $this->user_id || PeepSo::is_admin()) {
            if(PeepSo::get_option('site_registration_allowdelete', 0)) {
                return (TRUE);
            }
            return (FALSE);
        }
        return (FALSE);
    }

    /**
     * Checks to see whether the current viewed profile is the current user's own profile.
     * @return boolean
     */
    public function is_current_user()
    {
        return ($this->user_id == get_current_user_id());
    }

    /**
     * Called after rendering the profile edit page.
     */
    public function after_edit_form()
    {
        do_action('peepso_profile_after_edit_form');
    }

    /*********** FORM VALIDATION ****************/

    /**
     * Used in conjunction with form validation
     * @param string $value The value of Change Password field
     * @return boolean Either to generate an error message if FALSE otherwise not
     */
    public function check_password_change($value)
    {
        $verify_password = $this->_input->value('verify_password', '', FALSE); // SQL Safe
        if (($value || $verify_password) && $value !== $verify_password)
            return (FALSE);
        return (TRUE);
    }

    /**
     * Used in conjunction with form validation
     * @param string $value The value of User Name field
     * @return boolean Either to generate an error message if FALSE otherwise not
     * @deprecated since 1.8.4
     */
    public function check_username_change($value)
    {
        if ($value !== $this->user->get_username()) {
            $check_existing_username = get_user_by('login', $value);
            if (FALSE === $check_existing_username)
                return (TRUE);
            return (FALSE);
        }
        return (TRUE);
    }

    /**
     * Used in conjunction with form validation
     * @param string $value The value of Email field
     * @return boolean Either to generate an error message if FALSE otherwise not
     */
    public function check_email_change($value)
    {
        $user = get_user_by('email', $value);

        if (is_object($user) && $user->ID != get_current_user_id()) {
            return (FALSE);
        }

        return (TRUE);
    }

    /**
     * Set validation for change_password field
     * @param boolean $valid Whether or not the form passed the initial validation
     * @param object $form Instance of PeepSoForm
     * @return boolean
     */
    public function change_password_validate_after($valid, PeepSoForm $form)
    {
        if (!apply_filters('peepso_user_can_change_password', TRUE)) {
            $field['valid'] = TRUE;
            return $valid;
        }
        $field = &$form->fields['change_password'];

        $change_password = $this->_input->raw('change_password', ''); // Accept Raw, since password can be special char
        $verify_password = $this->_input->raw('verify_password', ''); // Accept Raw, since password can be special char

        $user = get_user_by('id', get_current_user_id());
        $check = wp_check_password($verify_password, $user->data->user_pass, $user->ID);

        if (!$check) {
            $field['valid'] = FALSE;
            $field['error_messages'][] = __('Please enter current password in <b>Current Password</b> field.', 'peepso-core');
            return FALSE;
        }

        if ($valid && $change_password) {
            if (strlen($change_password) >= intval(PeepSo::get_option('minimum_password_length', 10))) {
                $valid = TRUE;
            } else {
                $valid = FALSE;
                $field['error_messages'][] = sprintf(__('The password should be at least %d characters.', 'peepso-core'), PeepSo::get_option('minimum_password_length', 10));
            }
        }

        $field['valid'] = $valid;

        return $valid;
    }



    /**************** AJAX - BLOCKED USERS ****************/

    public function block_delete(PeepSoAjaxResponse $resp)
    {
        $this->init();

        if($this->can_edit()) {
            $block_ids = explode(',', $this->_input->value('delete', array(), FALSE)); // SQL Safe
            $aIds = array();

            foreach ($block_ids as $id) {
                $id = intval($id);
                if (!in_array($id, $aIds))
                    $aIds[] = $id;
            }

            if (0 != count($aIds)) {
                $blk = new PeepSoBlockUsers();
                $blk->delete_by_id($aIds);
            }

            $resp->success(TRUE);
            return;
        }

        $resp->success(FALSE);
        return;
    }

    /**************** AJAX - AVATARS ****************/

    /**
     * Avatar change #1 - upload
     * @param PeepSoAjaxResponse $resp
     */
    public function upload_avatar(PeepSoAjaxResponse $resp)
    {
        $this->init();

        // SQL safe, WP sanitizes it
        if($this->can_edit() && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'profile-avatar')) {

            $shortcode = PeepSoProfileShortcode::get_instance();
            $shortcode->set_page('profile');
            $shortcode->init();

            if ($shortcode->has_error()) {
                $resp->error($shortcode->get_error_message());
                return;
            }

            $image_url = $this->user->get_tmp_avatar();
            $full_image_url = $this->user->get_tmp_avatar(TRUE);
            $orig_image_url = str_replace('-full', '-orig', $full_image_url);

            // check image dimensions
            $si = new PeepSoSimpleImage();
            $orig_image_path = $this->user->get_image_dir() . 'avatar-orig-tmp.jpg';
            $si->load($orig_image_path);
            $width = $si->getWidth();
            $height = $si->getHeight();
            $avatar_size = PeepSo::get_option('avatar_size','250');

            if (($width < $avatar_size) || ($height < $avatar_size)) {
                $resp->set('width', $width);
                $resp->set('height', $height);
                $resp->error(sprintf(__('Minimum avatar resolution is %d x %d pixels.', 'peepso-core'), $avatar_size, $avatar_size));
                $resp->success(FALSE);
                return;
            }

            $resp->set('image_url', $image_url);
            $resp->set('orig_image_url', $orig_image_url);
            $resp->set('orig_image_path', $orig_image_path);
            $resp->set('html', PeepSoTemplate::exec_template('profile', 'dialog-profile-avatar', NULL, TRUE));
            $resp->success(TRUE);
            return;
        }

        $resp->success(FALSE);
        return;
    }

    /**
     * Avatar change #2 (optional) - crop
     * @param PeepSoAjaxResponse $resp
     */
    public function crop(PeepSoAjaxResponse $resp)
    {
        $this->init();

        if (! ($this->can_edit() && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'profile-avatar')) ) {
            $resp->success(FALSE);
            return;
        }

        $x = $this->_input->int('x');
        $y = $this->_input->int('y');
        $x2 = $this->_input->int('x2');
        $y2 = $this->_input->int('y2');
        $width = $this->_input->int('width');
        $height = $this->_input->int('height');
        $tmp = $this->_input->int('tmp');

        if ($tmp) {
            $src_orig = $this->user->get_image_dir() . 'avatar-orig-tmp.jpg';
            $src_full = $this->user->get_image_dir() . 'avatar-full-tmp.jpg';
            $src_thumb = $this->user->get_image_dir() . 'avatar-tmp.jpg';
        } else {
            $avatar_hash = get_user_meta($this->user->get_id(), 'peepso_avatar_hash', TRUE);
            $avatar_hash = $avatar_hash ? $avatar_hash . '-' : '';

            $src_orig = $this->user->get_image_dir() . $avatar_hash  . 'avatar-orig.jpg';
            copy($src_orig, $this->user->get_image_dir() . 'avatar-orig-tmp.jpg');

            $src_orig = $this->user->get_image_dir() . 'avatar-orig-tmp.jpg';
            $src_full = $this->user->get_image_dir() . 'avatar-full-tmp.jpg';
            $src_thumb = $this->user->get_image_dir()  . 'avatar-tmp.jpg';
        }

        $si = new PeepSoSimpleImage();
        $si->load($src_orig);

        // Resize image as edited on the screen, we do this because getting x and y coordinates
        // are unreliable when we are cropping from the edit avatar page; the dimensions on the edit
        // avatar page is not the same as the original image dimensions.
        if (isset($width) && isset($height) && $width > 0 && $height > 0) {
            $si->resize($width, $height);
        }

        // Create full-size avatar.
        $new_image = imagecreatetruecolor(PeepSo::get_option('avatar_size', 250), PeepSo::get_option('avatar_size', 250));
        imagecopyresampled($new_image, $si->image,
            0, 0, $x, $y,
            PeepSo::get_option('avatar_size', 250), PeepSo::get_option('avatar_size', 250), $x2 - $x, $y2 - $y);
        imagejpeg($new_image, $src_full, 100);

        // Create thumbnail-size avatar.
        $new_image = imagecreatetruecolor(PeepSoUser::THUMB_WIDTH, PeepSoUser::THUMB_WIDTH);
        imagecopyresampled($new_image, $si->image, // Resize from cropeed image "$si"
            0, 0, $x, $y,
            PeepSoUser::THUMB_WIDTH, PeepSoUser::THUMB_WIDTH, $x2 - $x, $y2 - $y);
        imagejpeg($new_image, $src_thumb, 75);

        $resp->set('image_url', $this->user->get_tmp_avatar());
        $resp->success(TRUE);
    }

    /**
     * Avatar rotate.
     * @param PeepSoAjaxResponse $resp
     */
    public function rotate(PeepSoAjaxResponse $resp)
    {
        $this->init();

        if (! ($this->can_edit() && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'profile-avatar')) ) {
            $resp->success(FALSE);
            return;
        }

        $direction = $this->_input->value('direction', 'cw', FALSE);
        $tmp = $this->_input->int('tmp');

        if ($tmp) {
            $src_orig = $dest_orig = $this->user->get_image_dir() . 'avatar-orig-tmp.jpg';
            $src_full = $dest_full = $this->user->get_image_dir() . 'avatar-full-tmp.jpg';
            $src_thumb = $dest_thumb = $this->user->get_image_dir() . 'avatar-tmp.jpg';
        } else {
            $avatar_hash = get_user_meta($this->user->get_id(), 'peepso_avatar_hash', TRUE);
            $avatar_hash = $avatar_hash ? $avatar_hash . '-' : '';

            $src_orig = $this->user->get_image_dir() . $avatar_hash  . 'avatar-orig.jpg';
            $src_full = $this->user->get_image_dir() . $avatar_hash  . 'avatar-full.jpg';
            $src_thumb = $this->user->get_image_dir() . $avatar_hash  . 'avatar.jpg';

            $dest_orig = $this->user->get_image_dir() . 'avatar-orig-tmp.jpg';
            $dest_full = $this->user->get_image_dir() . 'avatar-full-tmp.jpg';
            $dest_thumb = $this->user->get_image_dir() . 'avatar-tmp.jpg';
        }

        $angle = 'ccw' === $direction ? 90 : 270;

        foreach (['orig', 'full', 'thumb'] as $type) {
            $si = new PeepSoSimpleImage();
            $si->load(${'src_' . $type});
            $si->rotate($angle);
            $si->save(${'dest_' . $type}, $si->image_type, 100);
        }

        $image_url = $this->user->get_tmp_avatar();
        $orig_image_url = str_replace('-full', '-orig', $image_url);

        $resp->set('image_url', $image_url);
        $resp->set('orig_image_url', $orig_image_url);
        $resp->success(TRUE);
    }

    /**
     * Avatar change #3 - finalize
     * @param PeepSoAjaxResponse $resp
     */
    public function confirm_avatar(PeepSoAjaxResponse $resp)
    {
        $this->init();

        // SQL safe, WP sanitizes it
        if($this->can_edit() && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'profile-avatar')) {

            delete_user_meta($this->_input->int('user_id'), 'peepso_use_gravatar');

            if ($this->_input->value('use_gravatar', 0, FALSE) == 1 && PeepSo::get_option('avatars_gravatar_enable') == 1)
            {
                add_user_meta($this->_input->int('user_id'), 'peepso_use_gravatar', 1);
            }

            $this->user->finalize_move_avatar_file();

            $resp->set('image_url', $this->user->get_avatar());
            $resp->success(TRUE);
            return;
        }

        $resp->success(FALSE);
        return;
    }

    /**
     * Set user account to use Gravatar instead of uploaded file
     * @param PeepSoAjaxResponse $resp
     */
    public function use_gravatar(PeepSoAjaxResponse $resp)
    {
        $this->init();

        if($this->can_edit()) {
            $file = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->user->get_email()))) . '?s=160&r=' . strtolower(get_option('avatar_rating'));

            $resp->set('image_url', $file);
            $resp->set('html', PeepSoTemplate::exec_template('profile', 'dialog-profile-avatar', NULL, TRUE));

            $resp->success(TRUE);
            return;
        }

        $resp->success(FALSE);
        return;
    }

    /**
     * Avatar delete
     * @param PeepSoAjaxResponse $resp
     */
    public function remove_avatar(PeepSoAjaxResponse $resp)
    {
        $this->init($this->_input->int('user_id'));

        // SQL safe, WP sanitizes it
        if($this->can_edit() && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'profile-avatar')) {
            $this->user->delete_avatar();
            $resp->set('image_url', $this->user->get_avatar());
            $resp->success(TRUE);
        } else {
            $resp->success(FALSE);
        }
    }

    /**************** AJAX - COVER ****************/

    /**
     * Cover change #1 - upload
     * @param PeepSoAjaxResponse $resp
     */
    public function upload_cover(PeepSoAjaxResponse $resp)
    {
        $this->init();

        // SQL safe, WP sanitizes it
        if ($this->can_edit() && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'profile-cover')) {
            $shortcode = PeepSoProfileShortcode::get_instance();
            $shortcode->set_page('profile');
            $shortcode->init();

            if ($shortcode->has_error()) {
                $resp->error($shortcode->get_error_message());
                $resp->success(FALSE);
                return;
            }

            $resp->set('image_url', $this->user->get_cover());
            $resp->success(TRUE);
            return;
        }

        $resp->success(FALSE);
        return;
    }

    /**
     * Cover change #2 - reposition
     * @param PeepSoAjaxResponse $resp
     */
    public function reposition_cover(PeepSoAjaxResponse $resp)
    {
        $this->init();

        // SQL safe, WP sanitizes it
        if ($this->can_edit() && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'profile-cover')) {
            $x = $this->_input->int('x', 0);
            $y = $this->_input->int('y', 0);

            update_user_meta($this->user_id, 'peepso_cover_position_x', $x);
            update_user_meta($this->user_id, 'peepso_cover_position_y', $y);

            $resp->success(TRUE);
            return;
        }

        $resp->success(FALSE);
        return;
    }

    /**
     * Cover - rotate
     * @param PeepSoAjaxResponse $resp
     */
    public function rotate_cover(PeepSoAjaxResponse $resp)
    {
        $this->init();

        if (! ($this->can_edit() && wp_verify_nonce($this->_input->value('_wpnonce', '', FALSE), 'profile-cover')) ) {
            $resp->success(FALSE);
            return;
        }

        $direction = $this->_input->value('direction', 'cw', FALSE);
        $angle = 'ccw' === $direction ? 90 : 270;

        $old_cover_hash = get_user_meta($this->user->get_id(), 'peepso_cover_hash', TRUE);
        if (!$old_cover_hash) {
            $old_cover_hash = '';
        }

        $new_cover_hash = substr(md5(time()), 0, 10);

        // Full-size cover image.
        $filename = $old_cover_hash . '-cover.jpg';
        $filepath = $this->user->get_image_dir();

        $files = [ $filepath . $filename ];
        $other_sizes = apply_filters('peepso_filter_cover_sizes_to_delete', array(750));
		foreach ($other_sizes as $size) {
            array_push($files, $filepath . str_replace('-cover.jpg', '-cover-' . $size . '.jpg', $filename));
        }

        foreach ($files as $file) {
            if (file_exists($file)) {
                $si = new PeepSoSimpleImage();
                $si->load($file);
                $si->rotate($angle);

                // Save in the new filename to avoid cache.
                $file = str_replace($old_cover_hash . '-cover', $new_cover_hash . '-cover', $file);
                $si->save($file, $si->image_type, 100);
            }
        }

        // Delete old file and use the new one.
        $this->user->delete_cover_photo($old_cover_hash);
        update_user_meta($this->user->get_id(), 'peepso_cover_hash', $new_cover_hash);

        $resp->set('image_url', $this->user->get_cover());
        $resp->success(TRUE);
    }

    /**
     * Cover - delete
     * @param PeepSoAjaxResponse $resp
     */
    public function remove_cover_photo(PeepSoAjaxResponse $resp)
    {
        $this->init();

        // SQL safe, WP sanitizes it
        if ($this->can_edit() && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'profile-cover')) {
            $resp->success($this->user->delete_cover_photo());
            return;
        }

        $resp->success(FALSE);
        return;
    }

    /**************** AJAX - NOTIFICATIONS ****************/

    /*
     * Performs delete operation on notification messages
     * @param PeepSoAjaxResponse $resp The AJAX response object
     */
    public function notification_delete(PeepSoAjaxResponse $resp)
    {
        // SQL Safe
        if ('' === ($delete_values = $this->_input->value('delete', '', FALSE))) {
            $resp->success(FALSE);
            $resp->error(__('Please select at least one notification to delete.', 'peepso-core'));
        } else {
            $note_ids = explode(',', $delete_values);
            $aIds = array();

            foreach ($note_ids as $id) {
                $id = intval($id);
                if (!in_array($id, $aIds))
                    $aIds[] = $id;
            }

            if (0 !== count($aIds)) {
                $note = new PeepSoNotifications();
                $note->delete_by_id($aIds);
            }

            $resp->success(1);
        }
    }

    /**************** AJAX - misc****************/

    /**
     * Performs delete operation on the current user's profile information
     * @param PeepSoAjaxResponse $resp The response object
     */
    public function delete_profile(PeepSoAjaxResponse $resp)
    {
        $this->init();
        $pass = $this->_input->raw('password');
        $user = $this->user->get_user();
        if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID ) ) {
            if($this->can_edit()) {
                require_once(ABSPATH.'wp-admin/includes/user.php');
                wp_delete_user($this->user_id);
                wp_logout();

                $resp->set('url', PeepSo::get_page('logout_redirect'));
                $resp->set('messages', __('Your account has been completely removed from our system. Please bear in mind, it might take a while to completely delete all your content.', 'peepso-core'));
                $resp->success(TRUE);
            } else {
                $resp->error(__('You don\'t have permissions to do this.', 'peepso-core'));
                $resp->success(FALSE);
            }
        } else {
            $resp->error(__('Invalid password.', 'peepso-core'));
            $resp->success(FALSE);
        }
    }

    /**
     * Performs request account data operation on the current user's profile information
     * @param PeepSoAjaxResponse $resp The response object
     */
    public function request_account_data(PeepSoAjaxResponse $resp)
    {
        $this->init();
        $pass = $this->_input->raw('password');
        $user = $this->user->get_user();
        if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID ) ) {
            if($this->can_edit()) {

                PeepSoGdpr::add($user->ID);

                $resp->set('url', $this->user->get_profileurl() . 'about/account/');
                $resp->set('messages', __('Your request has been recorded by our system.', 'peepso-core'));
                $resp->success(TRUE);
            } else {
                $resp->error(__('You don\'t have permissions to do this.', 'peepso-core'));
                $resp->success(FALSE);
            }
        } else {
            $resp->error(__('Invalid password.', 'peepso-core'));
            $resp->success(FALSE);
        }
    }

    /**
     * Performs download account data operation on the current user's profile information
     * @param PeepSoAjaxResponse $resp The response object
     */
    public function download_account_data(PeepSoAjaxResponse $resp)
    {
        $this->init();
        $pass = $this->_input->raw('password');
        $user = $this->user->get_user();
        if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID ) ) {
            if($this->can_edit()) {

                $request_exists = PeepSoGdpr::request_exists($user->ID);
                if (count($request_exists) > 0 && (isset($request_exists[0]) && $request_exists[0]->request_status == PeepSoGdpr::STATUS_SUCCESS)) {
                    $url = $request_exists[0]->request_file_url;

                    $resp->set('url', $url);
                    $resp->set('messages', __('Your download is starting.', 'peepso-core'));
                    $resp->success(TRUE);
                } else {
                    $resp->error(__('Account data not found.', 'peepso-core'));
                    $resp->success(FALSE);
                }
            } else {
                $resp->error(__('You don\'t have permissions to do this.', 'peepso-core'));
                $resp->success(FALSE);
            }
        } else {
            $resp->error(__('Invalid password.', 'peepso-core'));
            $resp->success(FALSE);
        }
    }

    /**
     * Performs delete account data archive operation on the current user's profile information
     * @param PeepSoAjaxResponse $resp The response object
     */
    public function delete_account_data_archive(PeepSoAjaxResponse $resp)
    {
        $this->init();
        $pass = $this->_input->raw('password');
        $user = $this->user->get_user();
        if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID ) ) {
            if($this->can_edit()) {

                PeepSoGdpr::delete_request($user->ID);

                $resp->set('url', $this->user->get_profileurl() . 'about/account/');
                $resp->set('messages', __('Your archive has been deleted.', 'peepso-core'));
                $resp->success(TRUE);
            } else {
                $resp->error(__('You don\'t have permissions to do this.', 'peepso-core'));
                $resp->success(FALSE);
            }
        } else {
            $resp->error(__('Invalid password.', 'peepso-core'));
            $resp->success(FALSE);
        }
    }

    /**
     * Like/unlike action
     * @param PeepSoAjaxResponse $resp
     * @return bool
     */
    public function like(PeepSoAjaxResponse $resp)
    {
        $this->init();

        if(PeepSo::check_permissions($this->user_id, PeepSo::PERM_PROFILE_LIKE, $this->acting_user_id)) {

            $PeepSoLike = PeepSoLike::get_instance();

            if (FALSE === $PeepSoLike->user_liked($this->user_id, PeepSo::MODULE_ID, $this->acting_user_id)) {

                $PeepSoLike->add_like($this->user_id, PeepSo::MODULE_ID, $this->acting_user_id);

                $PeepSoUser = PeepSoUser::get_instance($this->acting_user_id);
                $data = array(
                    'permalink' => PeepSo::get_page('profile', FALSE) . '?notifications',
                );
                $data = array_merge($data, $PeepSoUser->get_template_fields('from'), $this->user->get_template_fields('user'));

                $i18n = __('Someone liked your profile', 'peepso-core');
                $message = 'Someone liked your profile';
                $args = ['peepso-core'];

                PeepSoMailQueue::add_notification_new($this->user_id, $data, $message, $args, 'like_profile', 'profile_like', PeepSo::MODULE_ID);

                $PeepSoNotifications = new PeepSoNotifications();


                $i18n = __('liked your profile', 'peepso-core');
                $message = 'liked your profile';
                $args = ['peepso-core'];

                $PeepSoNotifications->add_notification_new($this->acting_user_id, $this->user_id, $message, $args, 'profile_like', PeepSo::MODULE_ID);

            } else {
                $PeepSoLike->remove_like($this->user_id, PeepSo::MODULE_ID, get_current_user_id());
            }

            $resp->success(TRUE);
            $resp->set('like_count', $PeepSoLike->get_like_count($this->user_id, PeepSo::MODULE_ID));

            ob_start();
            $this->interactions();
            $resp->set('html', ob_get_clean());
            return;
        }

        $resp->success(FALSE);
        return;
    }

    /**
     * Report a profile
     * @param PeepSoAjaxResponse $resp
     */
    public function report(PeepSoAjaxResponse $resp)
    {
        $this->init();
        $reason = $this->_input->value('reason', '', FALSE); // SQL Safe
        $reason_desc = $this->_input->value('reason_desc', '', FALSE); // SQL Safe

        if (PeepSo::check_permissions($this->user_id , PeepSo::PERM_REPORT, $this->acting_user_id)) {
            if (!empty($reason_desc)) {
                $reason = $reason . ' - ' . $reason_desc;
            }
            $rep = new PeepSoReport();
            $rep->add_report($this->user_id, $this->acting_user_id, PeepSo::MODULE_ID, $reason);

            $resp->success(TRUE);
            $resp->notice(__('This profile has been reported', 'peepso-core'));
            return;
        }

        $resp->success(FALSE);
        return;
    }


    /**************** UTILITIES - NOTIFICATIONS ****************/
    /*
   * Determine if user has any pending notifications
   */
    public function has_notifications()
    {
        return (0 !== $this->num_notifications());
    }

    /*
     * Return number of pending notifications
     * @return int Number of pending notifications
     */
    public function num_notifications()
    {
        if (0 === $this->num_notifications) {
            $note = new PeepSoNotifications();
            $this->num_notifications = $note->get_count_for_user(get_current_user_id());
        }
        return ($this->num_notifications);
    }

    /*
     * Checks for any remaining notifications and sets up current notification data
     * for showing with 'show_notification' template tag.
     * @return Boolean TRUE if more notifications; otherwise FALSE
     */
    public function next_notification($limit = 40, $offset = 0, $unread_only =0 )
    {
        if (NULL === $this->notifications) {
            $note = new PeepSoNotifications();
            $this->notifications = $note->get_by_user(get_current_user_id(), $limit, $offset, $unread_only);
            $this->note_idx = 0;
        }

        if (0 !== count($this->notifications)) {
            if ($this->note_idx >= count($this->notifications)) {
                return (FALSE);											// ran out; exit loop
            } else {
                $this->note_data = get_object_vars($this->notifications[$this->note_idx]);
                ++$this->note_idx;
                return (TRUE);
            }
        } else {
            return (FALSE);
        }
    }

    /*
     * Outputs notification content based on template
     */
    public function show_notification()
    {
        PeepSoTemplate::exec_template('profile', 'notification', $this->note_data);
    }

    /*
     * Display notifications age in human readable form
     */
    public function notification_age($override = FALSE)
    {
        $data = $this->note_data;

        if($override) {
            $data = $override;
            $current_user_id = intval($data['not_user_id']);
        }

        $post_date = mysql2date('U', $data['not_timestamp'], FALSE);
        $curr_date = date('U', current_time('timestamp', 0));

        echo '<span title="', esc_attr($data['not_timestamp'], ' ', $data['not_timestamp']), '">';
        echo PeepSoTemplate::time_elapsed($post_date, $curr_date), '</span>';
    }

    /*
     * Displays the notification record's ID value
     */
    public function notification_id($echo = TRUE)
    {
        $id = $this->note_data['not_id'];

        if ( !$echo ) {
            return $id;
        }

        echo $id;
    }

    /*
     * Displays the notification record's "from" user id
     */
    public function notification_user()
    {
        return ($this->note_data['not_from_user_id']);
    }

    /**
     * Get read status from notification
     */
    public function notification_readstatus()
    {
        if ( intval($this->note_data['not_read']) === 1 ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*
     * Displays the link for the notification's content
     */
    public function notification_link($echo = 1, $override = FALSE)
    {
        $data = $this->note_data;
        $current_user_id = get_current_user_id();

        if($override) {
            $data = $override;
            $current_user_id = intval($data['not_user_id']);
        }

        /*if (0 === intval($data['not_external_id']))
            return;*/

        $link = PeepSo::get_page('activity_status') . $data['post_title'] . '/';
        $link = apply_filters('peepso_profile_notification_link', $link, $data);

        // checking if the like was not actually made on a comment
        // @todo this might be a bit MySQL expensive
        $is_a_comment = 0;
        if ('user_comment' === $data['not_type']) {
            $is_a_comment = 1;
        }

        if ('like_post' == $data['not_type']) {
            global $wpdb;
            $sql = 'SELECT COUNT(id) as `is_comment_like` FROM `' . $wpdb->prefix . 'posts` WHERE `post_type`=\'peepso-comment\' AND ID=' . $data['not_external_id'];
            $res = $wpdb->get_row($sql);

            $is_a_comment = $res->is_comment_like;
        }

        $print_link = '';
        $activity_type = array(
            'type' => 'post',
            'text' => __('post', 'peepso-core')
        );

        if ('stream_reply_comment' === $data['not_type']) {

            $activities = PeepSoActivity::get_instance();

            $not_activity = $activities->get_activity_data($data['not_external_id'], $data['not_module_id']);
            $comment_activity = $activities->get_activity_data($not_activity->act_comment_object_id, $not_activity->act_comment_module_id);
            $post_activity = $activities->get_activity_data($comment_activity->act_comment_object_id, $comment_activity->act_comment_module_id);


            if (is_object($comment_activity) && is_object($post_activity)) {
                $parent_comment = $activities->get_activity_post($comment_activity->act_id);
                $parent_post = $activities->get_activity_post($post_activity->act_id);
                $parent_id = $parent_comment->act_external_id;

                $post_link = PeepSo::get_page('activity_status') . $parent_post->post_title . '/';
                $comment_link = $post_link . '?t=' . time() . '#comment.' . $post_activity->act_id . '.' . $parent_comment->ID . '.' . $comment_activity->act_id . '.' . $not_activity->act_external_id;

                if( 0 === intval($echo) ) {
                    return apply_filters('peepso_notification_link', $comment_link, $data);
                }

                ob_start();

                // Print the bits only for legacy pre-translated notifications
                if(!strlen($data['not_message_args'])) {
                    echo ' ';
                    $post_content = __('a comment', 'peepso-core');

                    if (intval($parent_comment->post_author) === $current_user_id) {
                        $post_content = ($data['not_message'] != __('replied to', 'peepso-core')) ? __('on ', 'peepso-core') : '';
                        $post_content .= __('your comment', 'peepso-core');
                    }

                    echo $post_content;
                }

                $print_link = ob_get_clean();
            }

        } else if ('profile_like' === $data['not_type']) {

            $author = PeepSoUser::get_instance($data['not_from_user_id']);

            $link = $author->get_profileurl();

            if( 0 === intval($echo) ) {
                return apply_filters('peepso_notification_link', $link, $data);
            }

        } else if (1 == $is_a_comment) {

            $activities = PeepSoActivity::get_instance();

            $not_activity = $activities->get_activity_data($data['not_external_id'], $data['not_module_id']);

            $parent_activity = $activities->get_activity_data($not_activity->act_comment_object_id, $not_activity->act_comment_module_id);
            if (is_object($parent_activity)) {

                $not_post = $activities->get_activity_post($not_activity->act_id);
                $parent_post = $activities->get_activity_post($parent_activity->act_id);
                $parent_id = $parent_post->act_external_id;

                // modify the type of post (eg. post, photo, video, avatar, cover);
                // $activity_type = apply_filters('peepso_notifications_activity_type', $activity_type, $parent_id, NULL);

                // check if parent post is a comment
                if($parent_post->post_type == 'peepso-comment') {
                    $comment_activity = $activities->get_activity_data($not_activity->act_comment_object_id, $not_activity->act_comment_module_id);
                    $post_activity = $activities->get_activity_data($comment_activity->act_comment_object_id, $comment_activity->act_comment_module_id);

                    $parent_post = $activities->get_activity_post($post_activity->act_id);
                    $parent_comment = $activities->get_activity_post($comment_activity->act_id);

                    $parent_link = PeepSo::get_page('activity_status') . $parent_post->post_title . '/?t=' . time() . '#comment.' . $post_activity->act_id . '.' . $parent_comment->ID . '.' . $comment_activity->act_id . '.' . $not_activity->act_external_id;
                } else {
                    $parent_link = PeepSo::get_page('activity_status') .  $parent_post->post_title . '/#comment.' . $parent_activity->act_id . '.' . $not_post->ID . '.' . $not_activity->act_external_id;
                }

                if( 0 === intval($echo) ) {
                    return apply_filters('peepso_notification_link', $parent_link, $data);
                }

                ob_start();

                // Print the bits only for legacy pre-translated notifications
                if(!strlen($data['not_message_args'])) {
                    if (!in_array($data['not_type'], array('like_post', 'like_comment')) && $activity_type['type'] == 'post') {
                        echo ' ' . __('on', 'peepso-core') . ' ';

                        if (intval($parent_post->post_author) === $current_user_id) {
                            echo sprintf(__('your post', 'peepso-core'), $activity_type['text']);
                        } else {
                            $post_content = sprintf(__('a %s', 'peepso-core'), $activity_type['text']);
                            echo __('a post you follow', 'peepso-core');
                        }
                    }
                }

                $print_link = ob_get_clean();
            }
        } else {

            if( 0 === intval($echo) ) {
                return apply_filters('peepso_notification_link', $link, $data);
            }

            if ('share' === $data['not_type']) {

                $activities = PeepSoActivity::get_instance();
                $repost = $activities->get_activity_data($data['not_external_id'], $data['not_module_id']);
                $orig_post = $activities->get_activity_post($repost->act_repost_id);

                // modify the type of post (eg. post, photo, video, avatar, cover);
                //$activity_type = apply_filters('peepso_notifications_activity_type', $activity_type, $orig_post->ID, NULL);

                ob_start();
                // Print the bits only for legacy pre-translated notifications
                if(!strlen($data['not_message_args'])) {
                    echo ' ', sprintf(__('your %s', 'peepso-core'), $activity_type['text']);
                }
                $print_link = ob_get_clean();
            }
        }

        $print_link = apply_filters('peepso_modify_link_item_notification', array($print_link, $link), $data);

        if(is_array($print_link)) {
            echo $print_link[0];
        } else {
            echo $print_link;
        }
    }


    /*
     * Displays the notification message
     */
    public function notification_message()
    {
        $message =  PeepSoNotifications::parse($this->note_data);
        echo $message;
    }

    public function notification_human_friendly($override = FALSE) {

        $data = $this->note_data;

        if($override) {
            $data = $override;
        }

        $icon = '';

        if(!PeepSo::get_option('notification_previews',1)) {
            return;
        }

        $preview = get_post_meta($data['not_external_id'],'peepso_human_friendly', TRUE);

        if(!strlen($preview) && class_exists('PeepSoGroupsPlugin') && $data['not_module_id']==PeepSoGroupsPlugin::MODULE_ID) {
            $PeepSoGroup = new PeepSoGroup($data['not_external_id']);
            $preview = $PeepSoGroup->name;

            $icon= '<i class="gcis gci-users"></i>';
        }

        if(!strlen($preview) && class_exists('PeepSo_WPEM_Plugin') && $data['not_module_id']==PeepSo_WPEM_Plugin::MODULE_ID) {
            $event = get_post($data['not_external_id']);
            $preview = $event->post_title;

            $icon= '<i class="gcis gci-calendar"></i>';
        }

//        if(!strlen($preview) && strlen($post_title=$data['post_title'])) {
//            $preview = $post_title;
//        }

        if(!is_array($preview) && strlen($preview)) {
            ?>
            <div class="ps-notification__desc-quote">
                <span><?php echo $icon;?><?php echo trim(truncateHtml($preview, PeepSo::get_option('notification_preview_length',50), PeepSo::get_option('notification_preview_ellipsis','...'), false, FALSE)); ?></span>
            </div>
            <?php
        }
    }

    /*
     * Displays the notification record's timestamp value
     */
    public function notification_timestamp()
    {
        echo $this->note_data['not_timestamp'];
    }

    /*
     * Displays the notification record's type
     */
    public function notification_type()
    {
        echo $this->note_data['not_type'];
    }


    /**************** UTILITIES - ACTIONS, INTERACTIONS & MENUS ****************/

    /**
     * Render profile segment menu
     * @param $args
     * @return string
     */
    public function profile_navigation($args)
    {
        $links = array('_user_id'=>$this->user_id);
        $links = apply_filters('peepso_navigation_profile', $links);

        $args['links'] = $links;
        return PeepSoTemplate::exec_template('profile','profile-menu', $args);
    }

    public function profile_actions()
    {
        $act = array();

        if (is_user_logged_in()) {
            if ($this->user_id == get_current_user_id() && $this->_url_segments->_shortcode == "peepso_profile" && $this->_url_segments->get(2) == null) {
                $act['update_info'] = array(
                    'label' => __('Update Info', 'peepso-core'),
                    'class' => 'ps-focus__cover-action',
                    'title' => __('Redirect to about page', 'peepso-core'),
                    'icon'	=> 'gcis gci-user-edit',
                    'click' => 'window.location="'.$this->user->get_profileurl().'about"; return false;',
                );
            }
            $act = apply_filters('peepso_profile_actions', $act, $this->user_id);
        }

        foreach ($act as $name => $data) {

            echo '<a href="#" ';
            if (isset($data['class']))
                echo ' class="', esc_attr($data['class']), '" ';
            if (isset($data['title']))
                echo ' title="', esc_attr($data['title']), '" aria-label="', esc_attr($data['title']), '" ';
            if (isset($data['click']))
                echo ' onclick="', esc_js($data['click']), '" ';

            if (isset($data['extra']))
                echo $data['extra'];
            echo '>';
            if (isset($data['icon']))
                echo '<i class="' . $data['icon'] . '"></i> ';
            if (isset($data['label']))
                echo'<span>' . $data['label'] . '</span>';

            echo '<img class="ps-loading" src="', PeepSo::get_asset('images/ajax-loader.gif'), '" style="display: none"></a>', PHP_EOL;
        }

        if (is_user_logged_in()) {
            PeepSoMemberSearch::member_options($this->user_id, TRUE);
        }
    }

    public function profile_actions_extra()
    {
        $act = array();

        if (is_user_logged_in()) {
            $act = apply_filters('peepso_profile_actions_extra', $act, $this->user_id);
        }

        foreach ($act as $name => $data) {

            echo '<a href="#" ';
            if (isset($data['class']))
                echo ' class="', esc_attr($data['class']), '" ';
            if (isset($data['title']))
                echo ' title="', esc_attr($data['title']), '" aria-label="', esc_attr($data['title']), '" ';
            if (isset($data['click']))
                echo ' onclick="', esc_js($data['click']), '" ';

            if (isset($data['extra']))
                echo $data['extra'];
            echo '>';
            if (isset($data['icon']))
                echo '<i class="' . $data['icon'] . '"></i> ';
            if (isset($data['label']))
                echo'<span>' . $data['label'] . '</span>';

            echo '<img class="ps-loading" src="', PeepSo::get_asset('images/ajax-loader.gif'), '" style="display: none"></a>', PHP_EOL;
        }
    }

    /*
     * Output a series of <li> with links for profile interactions
     */
    public function interactions($return = FALSE, $all = FALSE)
    {
        $aAct = [];

        $decimals = apply_filters('peepso_filter_short_profile_count_decimals',1);
        $threshold = apply_filters('peepso_filter_short_profile_count_threshold',1000);

        // Only show user registered if the date is within the last 10 years
        $user_registered = get_userdata($this->user_id)->user_registered;
        $limit = 25;//years
        $limit = $limit * 365 * 24  * 3600;
        if(time()-strtotime($user_registered) <= $limit) {
            $aAct['member_since'] = array(
                'label' => sprintf(__('Member since %s', 'peepso-core'), date_i18n(get_option('date_format'), strtotime($user_registered))),
                'title' => __('Member since', 'peepso-core'),
                'icon' => 'gcis gci-user-plus',
                'class' => 'ps-focus__detail',
                'count' => FALSE,
                'order' => 101,
                'all_values' => 1,
                'is_details' => TRUE,
            );
        }

        $last_online = $this->user->get_last_online();
        if (DateTime::createFromFormat('Y-m-d H:i:s', $last_online) !== false) {

            $aAct['last_online'] = array(
                'label' => sprintf(_x('%s', 'Last seen label in profile details','peepso-core'), date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($last_online)) ),
                'title' => __('Last seen online', 'peepso-core'),
                'icon' => 'gcis gci-clock',
                'class' => 'ps-focus__detail',
                'count' => FALSE,
                'order' => 301,
                'all_values' => 1,
                'is_details' => TRUE,
            );
        }

        if(class_exists('PeepSoFriends')) {
            $PeepSoFriends = PeepSoFriends::get_instance();
            $count = $PeepSoFriends->get_num_friends($this->user_id);

            // Debug
            if(isset($_GET['profile_counts_debug'])) { $count = rand(1,11111); }

            $aAct['friends'] = array(
                'label' => _n('Friend','Friends', $count, 'peepso-core'),
                'title' => __('Friends', 'peepso-core'),
                'icon' => 'gcis gci-user-friends',
                'class' => 'ps-focus__detail',
                'count' => ' ' . PeepSo3_Utilities_String::shorten_big_number($count,$decimals,$threshold), // PeepSoViewLog::get_views($this->user_id, PeepSo::MODULE_ID),
                'order' => 501,
                'all_values' => 1,
                'is_details' => TRUE,
                'href' => PeepSoUser::get_instance($this->user_id)->get_profileurl() . 'friends',
            );

        }

        $count = PeepSoUserFollower::count_followers($this->user_id, true);

        // Debug
        if(isset($_GET['profile_counts_debug'])) { $count = rand(1,11111); }

        $aAct['followers'] = array(
            'label' => _n('Follower','Followers', $count, 'peepso-core'),
            'title' => __('Followers', 'peepso-core'),
            'icon' => 'gcis gci-user-friends',
            'class' => 'ps-focus__detail',
            'count' => ' ' . PeepSo3_Utilities_String::shorten_big_number($count,$decimals,$threshold),
            'order' => 502,
            'all_values' => 1,
            'is_details' => TRUE,
            'href' => PeepSoUser::get_instance($this->user_id)->get_profileurl() . 'followers',
        );

        $count = PeepSoUserFollower::count_following($this->user_id, true);

        // Debug
        if(isset($_GET['profile_counts_debug'])) { $count = rand(1,11111); }

        $aAct['following'] = array(
            'label' => __('Following', 'peepso-core'),
            'title' => __('Following', 'peepso-core'),
            'icon' => 'gcis gci-user-friends',
            'class' => 'ps-focus__detail',
            'count' => PeepSo3_Utilities_String::shorten_big_number($count,$decimals,$threshold),
            'order' => 503,
            'all_values' => 1,
            'is_details' => TRUE,
            'href' => PeepSoUser::get_instance($this->user_id)->get_profileurl() . 'followers/following',
        );

        $count = $this->init()->get_view_count();

        // Debug
        if(isset($_GET['profile_counts_debug'])) { $count = rand(1,11111); }

        $aAct['view_count'] = array(
            'label' => __('Profile views', 'peepso-core'),
            'title' => __('Profile views', 'peepso-core'),
            'icon' => 'gcis gci-eye',
            'class' => 'ps-focus__detail',
            'count' => ' ' . PeepSo3_Utilities_String::shorten_big_number($count,$decimals,$threshold), // PeepSoViewLog::get_views($this->user_id, PeepSo::MODULE_ID),
            'order' => 901,
            'all_values' => 1,
            'is_details' => TRUE,
        );

        $aAct['_user_id'] = $this->init()->get_id();

        $aAct = apply_filters('peepso_user_activities_links_before', $aAct);

        if(!$all) {
            foreach ($aAct as $section => $config) {
                if (isset($config['is_details']) && $config['is_details']) {
                    if (!PeepSo::check_permission_profile_details($this->user_id, $section)) {
                        unset($aAct[$section]);
                    }
                }
            }

            if(isset($aAct['last_online']) && $this->user->get_hide_online_status()) {
                unset($aAct['last_online']);
            }
        }

        // @todo privacy
        if (PeepSo::get_option('profile_sharing', TRUE)) {
            $aAct['share'] = array(
                'label' => __('Share', 'peepso-core'),
                'title' => __('Share...', 'peepso-core'),
                'click' => 'share.share_url("' . $this->user->get_profileurl() . '"); return false;',
                'icon' => 'gcis gci-share-alt',
                'class' => 'ps-focus__shared', // @todo add dedicated class
                'order' => 10002,
            );
        }


        if (is_user_logged_in()) {
            // Check whether the profile like button should be visible.
            $is_like_enabled = PeepSo::get_option('site_likes_profile', TRUE);
            if ($is_like_enabled) {
                $is_owner = $this->user->get_id() == get_current_user_id();
                $is_likable = $this->user->is_profile_likable();
                $is_like_enabled = $is_owner || $is_likable;
            }

            if ($is_like_enabled) {
                $peepso_like = PeepSoLike::get_instance();
                $likes = $peepso_like->get_like_count($this->user_id, PeepSo::MODULE_ID);

                if (!$is_likable) {
                    $like_icon = 'gcir gci-thumbs-up';
                    $like_label = __('Like', 'peepso-core');
                    $like_title = '';
                    $like_liked = FALSE;
                } else if (FALSE === $peepso_like->user_liked($this->user_id, PeepSo::MODULE_ID, get_current_user_id())) {
                    $like_icon = 'gcir gci-thumbs-up';
                    $like_label = __('Like', 'peepso-core');
                    $like_title = __('Like this Profile', 'peepso-core');
                    $like_liked = FALSE;
                } else {
                    $like_icon = 'gcis gci-thumbs-up';
                    $like_label = __('Like', 'peepso-core');
                    $like_title = __('Unlike this Profile', 'peepso-core');
                    $like_liked = TRUE;
                }

                $aAct['like'] = array(
                    'label' => $like_label,
                    'title' => $like_title,
                    'click' => $is_likable ? 'profile.new_like();' : '',
                    'icon' => $like_icon,
                    'count' => (! empty($likes) ? $likes : 0),
                    'class' => $like_liked ? 'ps-focus__like ps-focus__like--liked' : 'ps-focus__like',
                    'order' => 10001
                );
            }
        }

        unset($aAct['_user_id']);

        $sort_col = array();

        foreach ($aAct as $item) {
            $sort_col[] = (isset($item['order']) ? $item['order'] : 50);
        }

        array_multisort($sort_col, SORT_ASC, $aAct);

        $old = $aAct;

        $aAct['_user_id'] = $this->user_id;

        $aAct = apply_filters('peepso_user_activities_links_after', $aAct);

        unset($aAct['_user_id']);

        // Resort if the final filter changed something
        if($old != $aAct) {
            $sort_col = [];
            foreach ($aAct as $item) {
                $sort_col[] = (isset($item['order']) ? $item['order'] : 50);
            }

            array_multisort($sort_col, SORT_ASC, $aAct);
        }

        if($return) {
            return $aAct;
        }

        $previous_line = 0;
        $previous_icon = '';
        foreach ($aAct as $section => $config) {

            $has_onclick = FALSE;
            $has_href = FALSE;
            $has_linebreak = FALSE;

            // If the first digir of "order" is different, do a linebreak
            $line = substr($config['order'],0,1);
            if($line != $previous_line) {
                if($previous_line > 0) {
                    $has_linebreak = TRUE;
                }
                $previous_line = $line;
            }

            $click = $title = $class = $icon = $label = '';

            if($has_onclick = (isset($config['click']) && !empty($config['click']))) {
                $click = esc_js(trim($config['click'], ';'));
            } elseif($has_href = (isset($config['href']) && !empty($config['href']))) {
                $href = $config['href'];
            }

            $title = (isset($config['title']) ? ' title="' . esc_attr($config['title']) . '" ' : '');
            $class = (isset($config['class']) ? ' class="' . esc_attr($config['class']) . '" ' : '');
            $icon = esc_attr($config['icon']);
            $label = $config['label'];

            if ($has_onclick || $has_href) {
                echo "<a ";
                if($has_onclick) { echo "href=\"#\" onclick=\"$click; return false;\" ";}
                if($has_href)    { echo "href=\"$href\" "; }

                echo " $title $class >" . PHP_EOL;
            } else {
                echo "<span $title $class >" . PHP_EOL;
            }

            if($icon!=$previous_icon || $has_linebreak) {
                echo "<i class=\"$icon\"></i>";
                $previous_icon = $icon;
            }

            if (isset($config['count'])) {

                $count = $config['count'];

                // if the key "all_values" is not present, values below 1 will not render
                if( $count<1 && (!array_key_exists('all_values', $config) || FALSE === $config['all_values'])) {
                    $count = '';
                }

                echo "<span id=\"$section-count\"><strong>$count</strong>$label</span>";
            } else {
                echo "<span>$label</span>";
            }

            echo ( ($has_onclick || $has_href) ? '</a>' : '</span>'), PHP_EOL;
        }
    }

    /**************** UTILITIES - ALERTS ****************/

    /**
     * Defines all alerts
     * @return array $alerts List of all alerts
     */
    public function get_alerts_definition($all = FALSE)
    {
        static $alerts = NULL;
        if (NULL !== $alerts)
            return ($alerts);

        $activity_items = array(
            array(
                'label' => __('Someone commented on a post', 'peepso-core'),
                'descript' => __('Applies to all posts you follow', 'peepso-core'),
                'setting' => 'user_comment',
                'loading' => TRUE,
            ),
            array(
                'label' => __('Someone reacted to a post', 'peepso-core'),
                'descript' => __('Applies to all posts you follow', 'peepso-core'),
                'setting' => 'like_post',
                'loading' => TRUE,
            ),
            array(
                'label' => __('Someone reacted to my comment', 'peepso-core'),
                'setting' => 'like_comment',
                'loading' => TRUE,
            ),
            array(
                'label' => __('Someone replied to my comment', 'peepso-core'),
                'setting' => 'stream_reply_comment',
                'loading' => TRUE,
            ),
            // TODO: need to add settings for each type of alert/email being created
            // Art: I don't think we need this? 2 checkboxes are created for each setting
            // TODO: check calls to PeepSoNotifications::add_notification() and PeepSoMailQueue::add_messsage()- we need a config setting for each of those
            // Art: I'm not quite understand, for each setting we have 2 distinct names, for instance 'stream_reply_comment' creates 2 settings named 'stream_reply_comment_notification' and 'stream_reply_comment_email' and they are controlled or managed by 2 checkboxes for each setting, hence the 'stream_reply_comment' is just a prefix for the 2 notifications
        );

        if ($all || PeepSo::get_option('site_repost_enable', TRUE)) {
            array_push($activity_items, array(
                'label' => __('Someone shared my post', 'peepso-core'),
                'setting' => 'share',
                'loading' => TRUE,
            ));
        }

        $profile_items = array();

        if ($all || PeepSo::get_option('site_likes_profile', TRUE)) {

            array_push($profile_items, array(
                'label' => __('Someone liked my profile', 'peepso-core'),
                'setting' => 'profile_like',
                'loading' => TRUE,
            ));
        }

        array_push($profile_items, array(
            'label' => __('Someone wrote a post on my profile', 'peepso-core'),
            'setting' => 'wall_post',
            'loading' => TRUE,
        ));

        $alerts = array(
            'activity' => array(
                'title' => __('Posts and comments', 'peepso-core'),
                'items' => $activity_items,
            ),
            'profile' => array(
                'title' => __('Profile', 'peepso-core'),
                'items' => $profile_items,
            ),

            // NOTE: when adding new items here, also add settings to /install/activate.php site_alerts_ sections
        );

        $alerts['tags'] = array(
            'title' => __('Mentions', 'peepso-core'),
            'items' => array(
                array(
                    'label' => __('Someone mentioned me in a post', 'peepso-core'),
                    'setting' => 'tag',
                    'loading' => TRUE,
                ),
                array(
                    'label' => __('Someone mentioned me in a comment', 'peepso-core'),
                    'setting' => 'tag_comment',
                    'loading' => TRUE,
                )
            ),
        );


        $alerts = apply_filters('peepso_profile_alerts', $alerts);

        return ($alerts);
    }

    /**
     * Get available or configurable alerts
     * @return array List of alerts where user can override
     */
    public function get_available_alerts()
    {
        $alerts = array();
        $alerts_definition = $this->get_alerts_definition();

        foreach ($alerts_definition as $key => $value) {
            if (isset($value['items'])) {
                $alerts[$key] = $value;
            }
        }

        return ($alerts);
    }

    /**
     * Get alerts form fields definitions
     * @return array $fields
     */
    public function get_alerts_form_fields()
    {
        $alerts = $this->get_available_alerts();

        $fields = array();
        if (!empty($alerts)) {
            // append group alerts to field
            $fields['group_alerts'] = array(
                'label' => '',
                'descript' => '',
                'value' => 1,
                'fields' => array(),
                'type' => 'title',
                'section' => 1,
            );

            $fields['form_header'] = array(
                'label' => '',
                'field_wrapper_class' => 'ps-profile__notification-header ps-js-preferences-header',
                'fields' => array(
                    array(
                        'label'     => 'onsite',
                        'type'      => 'label',
                        'html'      => '<i class="ps-tip ps-tip--inline gcis gci-bell" aria-label="'.__('On-Site', 'peepso-core').'"></i>',
                    ),
                    array(
                        'label'     => 'email',
                        'type'      => 'label',
                        'html'      => '<i class="ps-tip ps-tip--inline gcis gci-envelope" aria-label="'.__('Email', 'peepso-core').'"></i>',
                    ),
                ),
                'type' => 'custom',
            );

            $counter = 0;

            // generate form fields
            foreach ($alerts as $key => $value) {

                // generate section
                $fields[$key] = array(
                    'label' => '',
                    'descript' => "{$value['title']}",
                    'value' => 1,
                    'fields' => array(),
                    'type' => 'custom',
                    'section' => 1,
                    'field_wrapper_class' => 'ps-profile__notification-legend',
                );


                // title
                if (!isset($value['items']) || empty($value['items']))
                    continue;

                $peepso_notifications = get_user_meta(get_current_user_id(), 'peepso_notifications');
                $notifications = ($peepso_notifications) ? $peepso_notifications[0] : array();
                if (count($value['items']) <= 1)
                    $fields[$key]['fields'] = array();

                // generate items
                foreach ($value['items'] as $item) {
                    $name_email = "{$item['setting']}_email";
                    $name_notification = "{$item['setting']}_notification";

                    $d ='';
                    if(array_key_exists('descript',$item)) {
                        $d = "<small>{$item['descript']}</small>";
                    }

                    $setting_fields = array(
                        array(
                            'label' => 'onsite',
                            'name' => $name_notification,
                            'type' => 'checkbox',
                            'group_key' => "__{$key}_notification",
                            'value' => apply_filters('peepso_get_notification_value', !in_array($name_notification, $notifications) ? 1 : 0, $name_notification),
                        ),
                        array(
                            'label' => 'email',
                            'name' => $name_email,
                            'type' => 'checkbox',
                            'group_key' => "__{$key}_email",
                            'value' => apply_filters('peepso_get_notification_value', !in_array($name_email, $notifications) ? 1 : 0, $name_email),
                        ),
                    );

                    $setting_fields = apply_filters('peepso_profile_alert_setting_fields', $setting_fields,$item['setting'], $key, $notifications);

                    $fields[$item['setting']] = array(

                        'label' => '',
                        'descript' => ''.$item['label'].$d,
                        'value' => 1,
                        'fields' => $setting_fields,
                        'type' => 'custom',
                        'loading' => (isset($item['loading']) && $item['loading'] ? 1 : 0),
                    );
                }
            }
        }
        $fields = apply_filters('peepso_profile_alerts_form_fields', $fields);
        return ($fields);
    }

    /**************** UTILITIES - BLOCKED USERS ****************/

    public function num_blocked()
    {
        if (0 === $this->num_blocked) {
            $blk = new PeepSoBlockUsers();
            $this->num_blocked = $blk->get_count_for_user(get_current_user_id());
        }

        return ($this->num_blocked);
    }

    public function block_user()
    {
        return ($this->block_data['blk_blocked_id']);
    }

    public function block_username()
    {
        $PeepSoUser = PeepSoUser::get_instance($this->block_data['blk_blocked_id']);
        echo $PeepSoUser->get_fullname();
    }

    /**************** UTILITIES - EDIT ACCOUNT ****************/

    /**
     * Render the form
     */
    public function edit_form()
    {
        if($this->can_edit()) {

            $fields = array(
                'verify_password' => array(
                    'label' => __('Current Password', 'peepso-core'),
                    'descript' => __('Enter your current password to change your account information', 'peepso-core'),
                    'class' => 'ps-input--sm ps-js-password-preview',
                    'type' => 'password',
                    #'row_wrapper_class' => 'ps-form__row--half',
                ),
                'user_nicename_readonly' => array(
                    'section' => __('Your Account', 'peepso-core'),
                    'label' => __('User Name', 'peepso-core'),
                    'descript' => __('If you change your username, you will be signed out', 'peepso-core'),
                    'value' => $this->user->get_username(),
                    'type' => 'hidden',
                    'html' => '<div class="ps-input ps-input--sm ps-input--disabled">'. $this->user->get_username() . '</div>',
                    'row_wrapper_class' => 'ps-form__row--user',
                ),
                'user_nicename' => array(
                    'section' => __('Your Account', 'peepso-core'),
                    'label' => __('User Name', 'peepso-core'),
                    #'descript' => __('Enter your user name', 'peepso-core'),
                    'value' => $this->user->get_username(),
                    'required' => 1,
                    'type' => 'text',
                    'class' => 'ps-input--sm',
                    'extra'=>'readonly',
                    'validation' => array(
                        'username',
                        'required',
	                    'minlen:' . apply_filters('peepso_filter_username_len_min',PeepSoUser::USERNAME_MINLEN),
	                    'maxlen:' . apply_filters('peepso_filter_username_len_max',PeepSoUser::USERNAME_MAXLEN),
                        'custom'
                    ),
                    'validation_options' => [
                        [
                            'error_message' => __('That username is already in use by someone else.', 'peepso-core'),
                            'function' => array($this, 'check_username_change')
                        ],
                    ],
                ),
                'user_email_readonly' => array(
                    'section' => __('Your Account', 'peepso-core'),
                    'label' => __('Email', 'peepso-core'),
                    'value' => $this->user->get_email(),
                    'type' => 'hidden',
                    'html' => $this->user->get_email(),
                ),
                'account' => array(
                    'value' => 1,
                    'type' => 'hidden',
                ),
                'user_email' => array(
                    'section' => __('Your Account', 'peepso-core'),
                    'label' => __('Email', 'peepso-core'),
                    #'descript' => __('Enter your email address', 'peepso-core'),
                    'value' => $this->user->get_email(),
                    'required' => 1,
                    'type' => 'text',
                    'class' => 'ps-input--sm',
                    'extra'=>'readonly',
                    'validation' => array(
                        'email',
                        'required',
                        'maxlen:' . PeepSoUser::EMAIL_MAXLEN,
                        'custom'
                    ),
                    'validation_options' => [
                        [
                            'error_message' => __('This email is already in use by someone else.', 'peepso-core'),
                            'function' => array($this, 'check_email_change'),
                        ],
                    ],
                ),
                'change_password' => array(
                    'label' => __('Change Password', 'peepso-core'),
                    'descript' => __('If you change your password, you will be signed out', 'peepso-core'),
                    'class' => 'ps-input--sm ps-js-password-preview',
                    'type' => 'password',
                    'validation' => array('password'),
                    'extra'=>'readonly',
                    #'row_wrapper_class' => 'ps-form__row--half',
                    /*'validation_options' => array(
                        'error_message' => __('Passwords mismatched.', 'peepso-core'),
                        'function' => array($this, 'check_password_change'),
                    ),*/
                ),
                'task' => array(
                    'type' => 'hidden',
                    'value' => 'profile_edit_save',
                ),
                '-form-id' => array(
                    'type' => 'hidden',
                    'value' => wp_create_nonce('profile-edit-form'),
                ),
                'authkey' => array(
                    'type' => 'hidden',
                    'value' => '',
                ),
            );

            if (!apply_filters('peepso_user_can_change_password', TRUE)) {
                unset($fields['verify_password']);
                $fields = apply_filters('peepso_edit_form_fields', $fields);

                unset($fields['change_password']);
                unset($fields['user_email']['extra']);

                if (intval(PeepSo::get_option('system_allow_username_changes', 0))) {
                    unset($fields['user_nicename']['extra']);
                }
            }

            // enable username change
            if (0 === intval(PeepSo::get_option('system_allow_username_changes', 0))) {
                $fields['user_nicename']['type'] = 'hidden';
                $fields['user_nicename_readonly']['type'] = 'html';
            }

            $fields['submit'] = array(
                'label' => __('Save', 'peepso-core'),
                'class' => 'ps-btn--sm ps-btn--action',
                'click' => 'submitbutton(\'frmSaveProfile\'); return false;',
                'type' => 'submit',
                'row_wrapper_class' => 'ps-form__row--submit',
            );

            $form = array(
                'container' => array(
                    'element' => 'div',
                    'class' => 'ps-form__grid',
                ),
                'fieldcontainer' => array(
                    'element' => 'div',
                    'class' => 'ps-form__row',
                ),
                'form' => array(
                    'name' => 'profile-edit',
                    'action' => $this->user->get_profileurl(). 'about/account/',
                    'method' => 'POST',
                    'class' => 'community-form-validate',
                    'extra' => 'autocomplete="off"',
                ),
                'fields' => $fields,
            );

            $peepso_form = PeepSoForm::get_instance();
            $peepso_form->render(apply_filters('peepso_profile_edit_form', $form));
        }
    }




    /**
     * Read or write message to be displayed after form is saved
     * @param bool $set
     * @return bool|null
     */
    public function edit_form_message($set = FALSE)
    {
        if(FALSE != $set) {
            $this->message = $set;
        }

        if (!is_null($this->message)) {
            return $this->message;
        }

        return FALSE;
    }

    /**************** UTILITIES - DELETE ACCOUNT ****************/

    /**
     * Render the form
     */
    public function delete_form()
    {
        if($this->can_delete()) {

            $fields = array(
                'profile_username' => array(
                    'section' => __('Profile Deletion', 'peepso-core'),
                    'label' => __('User Name', 'peepso-core'),
                    #'descript' => __('Enter your user name', 'peepso-core'),
                    'value' => $this->user->get_username(),
                    'type' => 'hidden',
                    'html' => $this->user->get_username(),
                ),
                'delete_account' => array(
                    'value' => 1,
                    'type' => 'hidden',
                ),
                'profile_user_id' => array(
                    'type' => 'hidden',
                    'value' => $this->user_id,
                ),
                'task' => array(
                    'type' => 'hidden',
                    'value' => 'profile_delete',
                ),
                '-form-id' => array(
                    'type' => 'hidden',
                    'value' => wp_create_nonce('profile-delete-form'),
                ),
                'authkey' => array(
                    'type' => 'hidden',
                    'value' => '',
                ),
            );

            $fields['submit'] = array(
                'label' => __('Delete', 'peepso-core'),
                'class' => 'ps-btn--sm ps-btn--abort ps-js-profile-delete',
                'type' => 'submit',
            );

            $form = array(
                'container' => array(
                    'element' => 'div',
                    'class' => 'ps-form__container',
                ),
                'fieldcontainer' => array(
                    'element' => 'div',
                    'class' => 'ps-form__row',
                ),
                'form' => array(
                    'name' => 'profile-delete',
                    'action' => $this->user->get_profileurl(). 'about/account/',
                    'method' => 'POST',
                    'class' => 'community-form-validate',
                    'extra' => 'autocomplete="off"',
                ),
                'fields' => $fields,
            );

            $peepso_form = PeepSoForm::get_instance();
            $peepso_form->render($form);
        }
    }

    /**************** UTILITIES - DELETE ACCOUNT ****************/

    /**
     * Render the form
     */
    public function request_data_form()
    {

        $can_edit = FALSE;
        if(get_current_user_id()) {
            $can_edit = TRUE;
        }

        $request_exists = PeepSoGdpr::request_exists(get_current_user_id());
        $content = '';

        if($can_edit) {

            $fields = array(
                'profile_username' => array(
                    'section' => __('Request your data.', 'peepso-core'),
                    'label' => __('User Name', 'peepso-core'),
                    #'descript' => __('Enter your user name', 'peepso-core'),
                    'value' => $this->user->get_username(),
                    'type' => 'hidden',
                    'html' => $this->user->get_username(),
                ),
                'request_account_data' => array(
                    'value' => 1,
                    'type' => 'hidden',
                ),
                'profile_user_id' => array(
                    'type' => 'hidden',
                    'value' => $this->user_id,
                ),
                'task' => array(
                    'type' => 'hidden',
                    'value' => 'request_account_data',
                ),
                '-form-id' => array(
                    'type' => 'hidden',
                    'value' => wp_create_nonce('request-account-data-form'),
                ),
                'authkey' => array(
                    'type' => 'hidden',
                    'value' => '',
                ),
            );

            $fields['submit'] = array(
                'label' => __('Export my Community data', 'peepso-core'),
                'class' => 'ps-btn--sm ps-btn--action ps-js-export-data-request',
                'type' => 'submit',
            );

            $form = array(
                'container' => array(
                    'element' => 'div',
                    'class' => 'ps-form__container',
                ),
                'fieldcontainer' => array(
                    'element' => 'div',
                    'class' => 'ps-form__row',
                ),
                'form' => array(
                    'name' => 'profile-request-account-data',
                    'action' => $this->user->get_profileurl(). 'about/account/',
                    'method' => 'POST',
                    'class' => 'community-form-validate',
                    'extra' => 'autocomplete="off"',
                ),
                'fields' => $fields,
            );


            $content .= '<p>' . __('You can download a complete copy of all the data you have shared in this Community. This includes posts, messages, photos, videos, comments, etc.  The data will be compiled automatically and delivered to you in a machine-readable JSON format. Please bear in mind that depending on the amount of data that needs to be compiled, preparing your download might take a while.', 'peepso-core') .'</p>';


            if (count($request_exists) > 0 && (isset($request_exists[0]) && $request_exists[0]->request_status != PeepSoGdpr::STATUS_SUCCESS)) {
                $content .= '<blockquote>' . __('Your export is being prepared. We\'ll email you when it\'s ready.', 'peepso-core') .'</blockquote>';
            } else {
                ob_start();
                $peepso_form = PeepSoForm::get_instance();
                $peepso_form->render($form);

                $content .= ob_get_clean();
            }


            //$content .= '<p>' . __('You can access your data by visiting your Activity Log or by downloading your information, or by simply logging into your account.', 'peepso-core') . '</p>';
        }

        if (count($request_exists) > 0 && (isset($request_exists[0]) && ($request_exists[0]->request_status == PeepSoGdpr::STATUS_SUCCESS))) {

            $gdpr = new PeepSoGdpr;
            $array_status = $gdpr::$array_status;

            $fields = array(
                'profile_username' => array(
                    'section' => __('Download your data.', 'peepso-core'),
                    'label' => __('User Name', 'peepso-core'),
                    #'descript' => __('Enter your user name', 'peepso-core'),
                    'value' => $this->user->get_username(),
                    'type' => 'hidden',
                    'html' => $this->user->get_username(),
                ),
                'download_account_data' => array(
                    'value' => 1,
                    'type' => 'hidden',
                ),
                'download_user_id' => array(
                    'type' => 'hidden',
                    'value' => $this->user_id,
                ),
                'task' => array(
                    'type' => 'hidden',
                    'value' => 'download_account_data',
                ),
                '-form-id' => array(
                    'type' => 'hidden',
                    'value' => wp_create_nonce('download-account-data-form'),
                ),
                'authkey' => array(
                    'type' => 'hidden',
                    'value' => '',
                ),
            );

            $fields['submit'] = array(
                'label' => __('Download Archive', 'peepso-core'),
                'class' => 'ps-btn--sm ps-btn--action ps-js-export-data-download',
                'type' => 'submit',
            );

            $fields['delete'] = array(
                'label' => __('Delete Archive', 'peepso-core'),
                'class' => 'ps-btn--sm ps-js-export-data-delete',
                'type' => 'submit',
            );

            $download_form = array(
                'container' => array(
                    'element' => 'div',
                    'class' => 'ps-form__container',
                ),
                'fieldcontainer' => array(
                    'element' => 'div',
                    'class' => 'ps-form__row',
                ),
                'form' => array(
                    'name' => 'download-request-account-data',
                    'action' => $this->user->get_profileurl(). 'about/account/',
                    'method' => 'POST',
                    'class' => 'community-form-validate',
                    'extra' => 'autocomplete="off"',
                ),
                'fields' => $fields,
            );

            $content = '';
            $content .= '<p>' . __('This is a copy of personal information you\'ve shared on this site. To protect your info, we\'ll ask you to re-enter your password to confirm that this is your account.', 'peepso-core') .  '</p>';
            $content .= '<p>' . __('Please note that your archive will be deleted after one week.', 'peepso-core')  . '</p>';

            ob_start();
            $peepso_form = PeepSoForm::get_instance();
            $peepso_form->render($download_form);

            $content .= ob_get_clean();

            $content .= '<p>' . __('Caution: Protect your archive', 'peepso-core') . '</p>';
            $content .= '<p>' . __('Your data archive includes sensitive info like your private activity, photos and profile information. Please keep this in mind before storing or sending your archive.', 'peepso-core')  . '</p>';
        }

        echo $content;
    }

    /*************** UTILITIES - POSTBOX ****************/

    /**
     * Remove "only me" privace when posting to a different user
     * @param  array $acc The access settings from the apply_filters call.
     * @return array The modified access settings.
     */
    public function filter_postbox_access_settings($acc)
    {
        if (is_int($this->user_id) && $this->user_id !== intval(get_current_user_id())) {
            unset($acc[PeepSo::ACCESS_PRIVATE]);
        }

        return ($acc);
    }

    /**************** UTILITIES - PREFERENCES ****************/

    public function num_preferences_fields()
    {
        return (count($this->get_available_preferences()));
    }

    public function get_preferences_definition($override = FALSE)
    {
        static $pref = NULL;
        if (NULL !== $pref)
            return ($pref);

        if(FALSE == $override) {
            $offset_range = array(-12, -11.5, -11, -10.5, -10, -9.5, -9, -8.5, -8, -7.5, -7, -6.5, -6, -5.5, -5, -4.5, -4, -3.5, -3, -2.5, -2, -1.5, -1, -0.5,
                0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 7.5, 8, 8.5, 8.75, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.75, 13, 13.75, 14);

            $options_gmt = array();

            foreach ($offset_range as $offset) {


                $offset_label = (string)$offset;

                if (0 <= $offset) {
                    $offset_label = '+' . $offset_label;
                }

                $offset_label = 'UTC' . str_replace(array('.25', '.5', '.75'), array(':15', ':30', ':45'), $offset_label);


                $options_gmt[(string)$offset] = $offset_label;
            }

            $group_profile_fields = array();

            if (1 === intval(PeepSo::get_option('system_override_name', 0))) {

                $options = apply_filters('peepso_filter_display_name_styles', []);

                foreach($options as $style => $description) {
                    $description = $description . ': '. $this->user->get_fullname(FALSE, $style);
                    $options[$style] = $description;
                }

                $group_profile_fields['peepso_profile_display_name_as'] = array(
                    'label' => __('Display my name as', 'peepso-core'),
                    'type' => 'select',
                    'descript' => __('Settings based on real name will display your username if you don\'t provide your real name', 'peepso-core'),
                    'validation' => array(/*'required'*/),
                    'options' => $options,
                    'value' => $this->user->get_display_name_as(),
                    'loading' => TRUE,
                );
            }
            if(PeepSo::get_option('site_likes_profile')) {
                $likable = get_user_meta($this->user_id, 'peepso_is_profile_likable', TRUE);
                $likable = (('' !== $likable) ? $likable : TRUE);

                $group_profile_fields['peepso_is_profile_likable'] = array(
                    // 'label' => __('Profile Likes', 'peepso-core'),
                    'label-desc' => __('Allow others to "like" my profile', 'peepso-core'),
                    'value' => $likable,
                    'type' => 'yesno_switch',
                    'loading' => TRUE,
                );
            }

            $field = PeepSoField::get_field_by_id('birthdate');

            if(is_object($field) && $field->prop('published') && !stristr($field->prop('meta','method'), 'relative')) {
                $group_profile_fields['peepso_hide_birthday_year'] = array(
                    'label-desc' => __('Hide my birthday year', 'peepso-core'),
                    'value' => $this->user->get_hide_birthday_year(),
                    'type' => 'yesno_switch',
                    'validation' => array(/*'required'*/),
                    'loading' => TRUE,
                );
            }


            $group_profile_fields['usr_profile_acc'] = array(
                'label' => __('Who can see my profile', 'peepso-core'),
                'descript' => __('Using any other setting than "public" might limit the visibility and reach of your posts.','peepso-core'),
                'value' => $this->user->get_profile_accessibility(),
                'type' => 'access-profile',
                'validation' => array(/*'required'*/),
                'loading' => TRUE,
            );





            $group_profile_fields['peepso_profile_post_acc'] = array(
                'label' => __('Who can post on my profile', 'peepso-core'),
                'value' => $this->user->get_profile_post_accessibility(),
                'type' => 'access-profile-post',
                'validation' => array(/*'required'*/),
                'loading' => TRUE,
            );

            // Allow users to hide themselves from all user listings
            if (1 === intval(PeepSo::get_option('allow_hide_user_from_user_listing', 0)))
                $group_profile_fields['peepso_is_hide_profile_from_user_listing'] = array(
                    // 'label' => __('Profile Likes', 'peepso-core'),
                    'label-desc' => __('Hide my profile from all user listings', 'peepso-core'),
                    'value' => $this->user->is_hide_profile_from_user_listing(),
                    'type' => 'yesno_switch',
                    'loading' => TRUE,
                );

            $group_other_fields = array();
            $group_other_fields['peepso_hide_online_status'] = array(
                //'label' => __('Don\'t show my online status', 'peepso-core'),
                'label-desc' => __('Don\'t show my online status', 'peepso-core'),
                'value' => $this->user->get_hide_online_status(),
                'type' => 'yesno_switch',
                'validation' => array(/*'required'*/),
                'loading' => TRUE,
            );

            $options_gmt = apply_filters('peepso_options_gmt', $options_gmt);

            $group_other_fields['peepso_gmt_offset'] = array(
                'label' => __('My timezone', 'peepso-core'),
                'descript' => __('Display all activity date and time in your own timezone', 'peepso-core'),
                'value' => PeepSoUser::get_gmt_offset($this->user->get_id()),
                'type' => 'select',
                'options' => $options_gmt,
                'validation' => array(/*'required'*/),
                'loading' => TRUE,
            );

            if (0 == PeepSo::get_option('site_profile_posts_override', 1)) {
                unset($group_profile_fields['peepso_profile_post_acc']);
            }

            $pref = array(
                'group_profile' => array(
                    'title' => __('Profile', 'peepso-core'),
                    'items' => $group_profile_fields,
                ),
                'group_other' => array(
                    'title' => __('Other', 'peepso-core'),
                    'items' => $group_other_fields,
                ),
            );
            $pref_plugins = apply_filters('peepso_profile_preferences', $pref);
            if (is_array($pref_plugins))
                $pref = array_merge($pref, $pref_plugins);
        } else {
            $pref = apply_filters('peepso_profile_preferences_'.$override, $pref);
        }

        return ($pref);
    }

    public function get_available_notifications($override = FALSE)
    {
        $pref_definition = array();
        if(FALSE == $override) {
            $pref_plugins = apply_filters('peepso_profile_notifications', $pref_definition);
            if (is_array($pref_plugins))
                $pref_definition = array_merge($pref_definition, $pref_plugins);
        } else {
            $pref_definition = apply_filters('peepso_profile_notifications_'.$override, $pref);
        }

        $prefs = array();
        if(is_array($pref_definition) && count((array) $pref_definition) > 0) {
            foreach ($pref_definition as $key => $value) {
                if (!isset($value['items']))
                    continue;
                $items = array();
                foreach ($value['items'] as $key_fields => $value_fields) {
                    $field_name = $key_fields;
                    $items[$key_fields] = $value_fields;
                }
                if ($items) {
                    $value['items'] = $items;
                    $prefs[$key] = $value;
                }
            }
        }
        return ($prefs);
    }

    public function get_available_preferences($override = FALSE)
    {
        $prefs = array();
        $pref_definition = $this->get_preferences_definition($override);
        if(is_array($pref_definition) && count((array) $pref_definition) > 0) {
            foreach ($pref_definition as $key => $value) {
                if (!isset($value['items']))
                    continue;
                $items = array();
                foreach ($value['items'] as $key_fields => $value_fields) {
                    $field_name = $key_fields;
                    $items[$key_fields] = $value_fields;
                }
                if ($items) {
                    $value['items'] = $items;
                    $prefs[$key] = $value;
                }
            }
        }
        return ($prefs);
    }

    public function get_notification_form_fields($override = FALSE)
    {
        $prefs = $this->get_available_notifications($override);

        $fields = array();
        if (!empty($prefs)) {

            $counter = 0;
            // generate form fields
            foreach ($prefs as $key => $value) {
                // generate section
                $fields[$key] = array(
                    'label' => "{$value['title']}",
                    'descript' => '',
                    'value' => 1,
                    'fields' => array(),
                    'type' => 'title',
                    'section' => 1,
                );

                // title
                if (!isset($value['items']) || empty($value['items']))
                    continue;

                if (count($value['items']) <= 1)
                    $fields[$key]['fields'] = array();

                // generate items
                foreach ($value['items'] as $key_item=> $value_item) {
                    $name_pref = $key_item;
                    $fields[$key_item] = $value_item;
                }
            }
        }
        $fields = apply_filters('peepso_profile_notifications_form_fields', $fields);
        return ($fields);
    }

    public function get_preferences_form_fields($override = FALSE)
    {
        $prefs = $this->get_available_preferences($override);

        $fields = array();
        if (!empty($prefs)) {

            $counter = 0;
            // generate form fields
            foreach ($prefs as $key => $value) {
                // generate section
                $fields[$key] = array(
                    'label' => "{$value['title']}",
                    'descript' => '',
                    'value' => 1,
                    'fields' => array(),
                    'type' => 'title',
                    'section' => 1,
                );

                // title
                if (!isset($value['items']) || empty($value['items']))
                    continue;

                if (count($value['items']) <= 1)
                    $fields[$key]['fields'] = array();

                // generate items
                foreach ($value['items'] as $key_item=> $value_item) {
                    $name_pref = $key_item;
                    $fields[$key_item] = $value_item;
                }
            }
        }
        $fields = apply_filters('peepso_profile_preferences_form_fields', $fields);
        return ($fields);
    }

    public function preferences_form_fields($preferences = TRUE, $notifications= FALSE)
    {
        $form = array();

        if($preferences) {
            $override = FALSE;
            if(is_string($preferences)) {
                $override = $preferences;
            }

            $fields = $this->get_preferences_form_fields($override);

            $form = array(
                'container' => array(
                    'element' => 'div',
                    'class' => 'ps-profile__preferences',
                ),
                'fieldcontainer' => array(
                    'element' => 'div',
                    'class' => 'ps-profile__preference ps-js-profile-preferences-option',
                ),
                'fields' => $fields,
            );
        }

        if($notifications) {
            $fields = $this->get_alerts_form_fields();

            $form = array(
                'container' => array(
                    'element' => 'div',
                    'class' => 'ps-profile__notifications-list',
                ),
                'fieldcontainer' => array(
                    'element' => 'div',
                    'class' => 'ps-profile__notifications-list-item ps-js-notification-option',
                ),
                'fields' => $fields,
            );
        }

        //remove_filter('peepso_render_form_field', array(&$this, 'render_custom_form_field'), 10, 2);
        add_filter('peepso_render_form_field', array(&$this, 'render_preferences_form_field'), 10, 2);

        $peepso_form = PeepSoForm::get_instance();
        $peepso_form->render($form);
    }

    public function notifications_form_fields()
    {
        $fields = $this->get_notification_form_fields();
        $form = array(
            'container' => array(
                'element' => 'div',
                'class' => 'ps-profile__notifications',
            ),
            'fieldcontainer' => array(
                'element' => 'div',
                'class' => 'ps-profile__notifications-item ps-js-notification-option',
            ),
            'fields' => $fields,
        );

        //remove_filter('peepso_render_form_field', array(&$this, 'render_custom_form_field'), 10, 2);
        add_filter('peepso_render_form_field', array(&$this, 'render_preferences_form_field'), 10, 2);

        $peepso_form = PeepSoForm::get_instance();
        $peepso_form->render($form);
    }

    public function render_preferences_form_field($field, $name)
    {
        $peepso_form = PeepSoForm::get_instance();

        $custom_field = '<div class="ps-profile__notification ps-preferences__notification ' . $field['class'] . '">';
        if (isset($field['descript']) && !empty($field['descript'])) {
            $custom_field .= '<label id="' . $name . '" class="ps-profile__notification-label">' . $field['descript'];
            if (isset($field['loading']) && $field['loading']) {
                $custom_field .= ' <span class="ps-form__check ps-js-loading">' .
                    '<img src="' . PeepSo::get_asset('images/ajax-loader.gif') . '" />' .
                    '<i class="gcis gci-check"></i></span>';
            }
            $custom_field .= '</label>';
        }

        $custom_field .= '<div class="ps-profile__notification-checkbox ps-preferences__checkbox">';
        foreach ($field['fields'] as $value) {
            $custom_field .= '<span data-type="' . esc_attr($value['label']) . '">';
            if ('checkbox' === $value['type']) {
                if (isset($field['section']))
                    $custom_field .= '
						<div class="ps-checkbox">
							<input type="checkbox" aria-labelledby="' . $name . '" class="ps-checkbox__input" id="' . esc_attr($value['name']) . '" onclick="ps_alerts.toggle(\'' . esc_attr($value['name']) . '\', this.checked)" >
							<label class="ps-checkbox__label" for="' . esc_attr($value['name']) . '"></label>
						</div>';
                else {
                    $checked = (1 === $value['value'])? 'checked="checked"' : '';
                    $custom_field .= '
						<div class="ps-checkbox">
							<input type="checkbox" aria-labelledby="' . $name . ' ' . esc_attr($value['label']) . '" id="' . esc_attr($value['name']) . '" name="' . esc_attr($value['name']) . '" value="1" ' . $checked . ' class="ps-checkbox__input ' . esc_attr($value['group_key']) . '" />
							<label class="ps-checkbox__label" for="' . esc_attr($value['name']) . '"></label>
						</div>';
                }
            } else if ('label' === $value['type']) {
                $custom_field .= '<div class="ps-profile__notification-title">' . $value['html'] . '</div>';
            }
            $custom_field .= '</span>';
        }
        $custom_field .= '</div>';	// .ps-profile__notification-checkbox
        $custom_field .= '</div>';	// .ps-profile__notification
        return ($custom_field);
    }


}

// EOF
