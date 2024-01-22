<?php

class PeepSoGroupCategoryAjax extends PeepSoAjaxCallback
{
	public function create(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'groupso'));
			return;
		}

		$default_category_name = __('New Category', 'groupso');

		$group_cat_data = array(
			'name'			=> $default_category_name,
			'description'	=> '',
			'author_id'		=> get_current_user_id(),
			'published'		=> 'publish',
			'order'			=> $this->_input->int('order', 0),
		);

		// $errors = PeepSoGroupCategory::validate($group_data);

		// if(count($errors)){
		// 	$resp->error($errors);
		// 	return( FALSE );
		// }

		// respect line breaks
		// $description = $this->_input->value('description', '', FALSE); // SQL safe
		// $description = htmlspecialchars($description);
		// $group_data['description'] = trim(PeepSoSecurity::strip_content($description));

		$group_cat = new PeepSoGroupCategory(NULL, $group_cat_data);

		// Prepare HTML output
		ob_start();
		PeepSoTemplate::exec_template('admin','group_categories', array('category'=>$group_cat,'force_open' => 1));
		$html = ob_get_clean();

		// Set response
		$resp->set('id', $group_cat->get('id'));
		$resp->set('html', $html);
		$resp->success(TRUE);
	}

	public function delete(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'groupso'));
			return;
		}

		$category = $this->_input->int('id');
		$group_cat = new PeepSoGroupCategory($category);
		if(FALSE === $group_cat)
		{
			return;
		}

		$group_cat->delete($category);
		$resp->success(TRUE);
	}

	public function set_prop(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'groupso'));
			return;
		}

		$category = $this->_input->int('id');

		$prop = $this->_input->value('prop', '', FALSE); // SQL Safe
		$value = $this->_input->value('value', '', FALSE); // SQL Safe

        if('slug' == $prop) {
            $value = wp_unique_post_slug(sanitize_title_with_dashes($value), $category, 'any', 'peepso-group-cat', 0);
        }


		$group_cat = new PeepSoGroupCategory($category);
		if(FALSE === $group_cat)
		{
			return;
		}

		$data[$prop] = $value;
		$group_cat->update($data);
		$resp->set('value', $value);
		$resp->success(TRUE);
	}

	public function set_meta(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'groupso'));
			return;
		}

		$id = $this->_input->int('id');
		$prop = $this->_input->value('prop', '', FALSE); // SQL Safe

		$value = $this->_input->value('value', '', FALSE); // SQL safe

		if(1 == $this->_input->int('json',0)) {
			$value = htmlspecialchars_decode($value);
			$value = json_decode($value, TRUE);
		}

		$key = $this->_input->value('key', NULL, FALSE); // SQL Safe

		$meta_value = get_post_meta($id, $prop, 1);

		if( NULL !== $key) {
			if(!is_array($meta_value)) {
				$meta_value = array();
			}
			$meta_value[$key] = $value;
		} else {
			$meta_value = $value;
		}

		update_post_meta($id, $prop, $meta_value);
		$resp->success(TRUE);
	}

	public function set_order(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'groupso'));
			return;
		}

		// SQL safe
		if( $categories = json_decode($this->_input->value('group_category', '', FALSE)) ) {
			$i = 1;
			foreach( $categories as $category ) {
				$group_cat = new PeepSoGroupCategory($category);
				if(FALSE !== $group_cat)
				{
					$data['order'] = $i;
					$group_cat->update($data);
					$i++;
				}
			}
		}

		$resp->success(TRUE);
	}

	public function set_admin_box_status(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'groupso'));
			return;
		}

		$id 	= $this->_input->raw('id');
		$status = $this->_input->int('status', 0);

		$id = json_decode($id);

		foreach($id as $field_id) {
			update_user_meta(get_current_user_id(), 'peepso_admin_group_category_open_' . $field_id, $status);
		}

		$resp->success(TRUE);
	}

    /** IMAGES (COVER/AVATAR) **/

    /*
     * POST
     * Called from AjaxHandler when an image crop request is performed
     */
    public function avatar_crop(PeepSoAjaxResponse $resp)
    {
        $category_id = $this->_input->int('u');
        $PeepSoGroupCategory = new PeepSoGroupCategory($category_id);

        if (! (wp_verify_nonce($this->_input->value('_wpnonce', '', FALSE), 'group-category-avatar') && PeepSo::is_admin()) ) {
            $resp->success(FALSE);
            $resp->error(__('Invalid access', 'groupso'));
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
            $src_orig = $PeepSoGroupCategory->get_image_dir() . 'avatar-orig-tmp.jpg';
            $src_full = $PeepSoGroupCategory->get_image_dir() . 'avatar-full-tmp.jpg';
            $src_thumb = $PeepSoGroupCategory->get_image_dir() . 'avatar-tmp.jpg';
        } else {
            $avatar_hash = get_post_meta($PeepSoGroupCategory->id, 'peepso_group_category_avatar_hash', TRUE);
            $avatar_hash = $avatar_hash ? $avatar_hash . '-' : '';

            $src_orig = $PeepSoGroupCategory->get_image_dir() . $avatar_hash  . 'avatar-orig.jpg';
            copy($src_orig, $PeepSoGroupCategory->get_image_dir() . 'avatar-orig-tmp.jpg');

            $src_orig = $PeepSoGroupCategory->get_image_dir() . 'avatar-orig-tmp.jpg';
            $src_full = $PeepSoGroupCategory->get_image_dir() . 'avatar-full-tmp.jpg';
            $src_thumb = $PeepSoGroupCategory->get_image_dir()  . 'avatar-tmp.jpg';
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

        $resp->set('image_url', $PeepSoGroupCategory->get_tmp_avatar(TRUE));
        $resp->success(TRUE);
    }

    /**
     * Avatar rotate.
     * @param PeepSoAjaxResponse $resp
     */
    public function avatar_rotate(PeepSoAjaxResponse $resp)
    {
        $category_id = $this->_input->int('u');
        $PeepSoGroupCategory = new PeepSoGroupCategory($category_id);

        if (! (wp_verify_nonce($this->_input->value('_wpnonce', '', FALSE), 'group-category-avatar') && PeepSo::is_admin()) ) {
            $resp->success(FALSE);
            $resp->error(__('Invalid access', 'groupso'));
            return;
        }

        $direction = $this->_input->value('direction', 'cw', FALSE);
        $tmp = $this->_input->int('tmp');

        if ($tmp) {
            $src_orig = $dest_orig = $PeepSoGroupCategory->get_image_dir() . 'avatar-orig-tmp.jpg';
            $src_full = $dest_full = $PeepSoGroupCategory->get_image_dir() . 'avatar-full-tmp.jpg';
            $src_thumb = $dest_thumb = $PeepSoGroupCategory->get_image_dir() . 'avatar-tmp.jpg';
        } else {
            $avatar_hash = get_post_meta($PeepSoGroupCategory->id, 'peepso_group_category_avatar_hash', TRUE);
            $avatar_hash = $avatar_hash ? $avatar_hash . '-' : '';

            $src_orig = $PeepSoGroupCategory->get_image_dir() . $avatar_hash  . 'avatar-orig.jpg';
            $src_full = $PeepSoGroupCategory->get_image_dir() . $avatar_hash  . 'avatar-full.jpg';
            $src_thumb = $PeepSoGroupCategory->get_image_dir() . $avatar_hash  . 'avatar.jpg';

            $dest_orig = $PeepSoGroupCategory->get_image_dir() . 'avatar-orig-tmp.jpg';
            $dest_full = $PeepSoGroupCategory->get_image_dir() . 'avatar-full-tmp.jpg';
            $dest_thumb = $PeepSoGroupCategory->get_image_dir() . 'avatar-tmp.jpg';
        }

        $angle = 'ccw' === $direction ? 90 : 270;

        foreach (['orig', 'full', 'thumb'] as $type) {
            $si = new PeepSoSimpleImage();
            $si->load(${'src_' . $type});
            $si->rotate($angle);
            $si->save(${'dest_' . $type}, $si->image_type, 100);
        }

        $image_url = $PeepSoGroupCategory->get_tmp_avatar(TRUE);
        $orig_image_url = str_replace('-full', '-orig', $image_url);

        $resp->set('image_url', $image_url);
        $resp->set('orig_image_url', $orig_image_url);
        $resp->success(TRUE);
    }

    /*
     * POST
     * Called from AjaxHandler when an avatar upload request is performed
     */
    public function avatar_upload(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'group-category-avatar')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'groupso'));
        } else {

            $category_id = $this->_input->int('category_id');

            $PeepSoGroupCategory = new PeepSoGroupCategory($category_id);

            // admin only, for now
            if(!PeepSo::is_admin()) {
                $resp->error(__('You do not have enough permissions.', 'groupso'));
                $resp->success(FALSE);
                return;
            } else {

                if (isset($_FILES['filedata'])) {
                    $allowed_mime_types = apply_filters(
                        'peepso_group_avatar_mime_types',
                        array(
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        )
                    );

                    if (empty($_FILES['filedata']['tmp_name'])) {
                        $resp->error(__('The file you uploaded is either missing or too large.', 'groupso'));
                        $resp->success(FALSE);
                        return;
                    }

                    if (!in_array($_FILES['filedata']['type'], $allowed_mime_types)) {
                        $resp->error(__('The file type you uploaded is not allowed.', 'groupso'));
                        $resp->success(FALSE);
                        return;
                    }

                    $PeepSoGroupCategory->move_avatar_file($_FILES['filedata']['tmp_name']);

                    $image_url = $PeepSoGroupCategory->get_tmp_avatar();
                    $full_image_url = $PeepSoGroupCategory->get_tmp_avatar(TRUE);
                    $orig_image_url = str_replace('-full', '-orig', $full_image_url);

                    // check image dimension
                    $si = new PeepSoSimpleImage();
                    $orig_image_path = $PeepSoGroupCategory->get_image_dir() . 'avatar-orig-tmp.jpg';
                    $si->load($orig_image_path);
                    $width = $si->getWidth();
                    $height = $si->getHeight();
                    $avatar_size = PeepSo::get_option('avatar_size','100');

                    if (($width < $avatar_size) || ($height < $avatar_size)) {
                        $resp->success(FALSE);
                        $resp->set('width', $width);
                        $resp->set('height', $height);
                        $resp->error(sprintf(__('Minimum avatar resolution is %d x %d pixels.', 'groupso'), $avatar_size, $avatar_size));
                        return;
                    }

                    $resp->set('image_url', $image_url);
                    $resp->set('orig_image_url', $orig_image_url);
                    $resp->set('orig_image_path', $orig_image_path);
                    $resp->success(TRUE);

                    return;
                } else {
                    $resp->error(__('No file uploaded.', 'groupso'));
                    $resp->success(FALSE);
                    return;
                }
            }
        }
    }

    /*
     * POST
     * Called from AjaxHandler when an avatar upload is finalized
     */
    public function avatar_confirm(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'group-category-avatar')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'groupso'));
        } else {
            $category_id = $this->_input->int('category_id');

            $PeepSoGroupCategory = new PeepSoGroupCategory($category_id);

            // can-manage_content is called on PeepSoGroupUser
            if(PeepSo::is_admin()) {
                $PeepSoGroupCategory->finalize_move_avatar_file();

                $resp->set('image_url', $PeepSoGroupCategory->get_avatar_url());
                $resp->success(TRUE);
            } else {
                $resp->error(__('You do not have enough permissions.', 'groupso'));
                $resp->success(FALSE);
            }
        }
    }

    /*
     * POST
     * Deletes a category's avatar
     */
    public function avatar_delete(PeepSoAjaxResponse $resp)
    {
        $category_id = $this->_input->int('category_id');

        $PeepSoGroupCategory = new PeepSoGroupCategory($category_id);

        // can-manage_content is called on PeepSoGroupUser
        // SQL safe, WP sanitizes it
        if(PeepSo::is_admin() && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'group-category-avatar')) {
            $PeepSoGroupCategory->delete_avatar();
            $resp->success(TRUE);
        } else {
            $resp->success(FALSE);
        }
    }

    /*
    * POST
    * Called from AjaxHandler when a cover photo upload request is performed
    * @param object PeepSoAjaxResponse $resp
    */
    public function cover_upload(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'group-category-cover')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'groupso'));
        } else {
            $category_id = $this->_input->int('category_id');

            $PeepSoGroupCategory = new PeepSoGroupCategory($category_id);

            // can-manage_content is called on PeepSoGroupUser
            if(PeepSo::is_admin()) {

                if (isset($_FILES['filedata'])) {
                    $allowed_mime_types = apply_filters(
                        'peepso_group_cover_mime_types',
                        array(
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        )
                    );

                    if (!in_array($_FILES['filedata']['type'], $allowed_mime_types)) {
                        $resp->error(__('The file type you uploaded is not allowed.', 'groupso'));
                        $resp->success(FALSE);
                        return;
                    }

                    if (empty($_FILES['filedata']['tmp_name'])) {
                        $resp->error(__('The file you uploaded is either missing or too large.', 'groupso'));
                        $resp->success(FALSE);
                        return;
                    }


                    $PeepSoGroupCategory->move_cover_file($_FILES['filedata']['tmp_name']);

                    $resp->set('image_url', $PeepSoGroupCategory->get_cover_url());
                    // $resp->set('html', PeepSoTemplate::exec_template('groups', 'dialog-group-category-cover', array('PeepSoGroupCategory' => $PeepSoGroupCategory), TRUE));
                    $resp->success(TRUE);
                } else {
                    $resp->error(__('No file uploaded.', 'groupso'));
                    $resp->success(FALSE);
                }
            } else {
                $resp->success(FALSE);
                $resp->error(__('You do not have enough permissions.', 'groupso'));
            }
        }
    }

    /*
     * POST
     * Called from AjaxHandler when a cover photo repositoin request is performed
     */
    public function cover_reposition(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (PeepSo::is_admin() && FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'group-category-cover')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'groupso'));
        } else {

            $category_id = $this->_input->int('category_id');

            $x = $this->_input->int('x', 0);
            $y = $this->_input->int('y', 0);

            update_post_meta($category_id, 'peepso_cover_position_x', $x);
            update_post_meta($category_id, 'peepso_cover_position_y', $y);

            $resp->notice(__('Changes saved.', 'groupso'));
            $resp->success(TRUE);
        }
    }

    /**
     * POST
     * Rotates a group's cover photo
     */
    public function cover_rotate(PeepSoAjaxResponse $resp)
    {
        $category_id = $this->_input->int('category_id');
        $category = new PeepSoGroupCategory($category_id);

        if (! (PeepSo::is_admin() && wp_verify_nonce($this->_input->value('_wpnonce', '', FALSE), 'group-category-cover')) ) {
            $resp->success(FALSE);
            return;
        }

        $direction = $this->_input->value('direction', 'cw', FALSE);
        $angle = 'ccw' === $direction ? 90 : 270;

        $old_cover_hash = get_post_meta($category->id, 'peepso_group_category_cover_hash', TRUE);
        if (!$old_cover_hash) {
            $old_cover_hash = '';
        }

        $new_cover_hash = substr(md5(time()), 0, 10);

        // Full-size cover image.
        $filename = $old_cover_hash . '-cover.jpg';
        $filepath = $category->get_image_dir();
        $file = $filepath . $filename;

        if (!file_exists($file)) {
            $resp->success(FALSE);
            return;
        }

        $si = new PeepSoSimpleImage();
        $si->load($file);
        $si->rotate($angle);

        // Save in the new filename to avoid cache.
        $file = str_replace($old_cover_hash . '-cover', $new_cover_hash . '-cover', $file);
        $si->save($file, $si->image_type, 100);

        // Delete old file and use the new one.
        $category->delete_cover_photo($old_cover_hash);
        update_post_meta($category->id, 'group_category_cover_photo', $category->get_image_url() . $new_cover_hash . '-cover.jpg');
        update_post_meta($category->id, 'peepso_group_category_cover_hash', $new_cover_hash);

        $resp->set('image_url', $category->get_cover_url());
        $resp->success(TRUE);
    }

    /**
     * POST
     * Deletes a group's cover photo
     */
    public function cover_delete(PeepSoAjaxResponse $resp)
    {
        $category_id = $this->_input->int('category_id');
        $PeepSoGroupCategory = new PeepSoGroupCategory($category_id);

        // SQL safe, WP sanitizes it
        if (PeepSo::is_admin() && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'group-category-cover')) {
            $resp->success($PeepSoGroupCategory->delete_cover_photo());
        } else {
            $resp->success(FALSE);
        }
    }
}

// EOF
