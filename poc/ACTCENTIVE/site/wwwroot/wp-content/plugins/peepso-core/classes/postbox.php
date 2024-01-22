<?php

class PeepSoPostbox extends PeepSoAjaxCallback
{
	public $template_tags = array(
		'post_interactions',				// output controls for post interactions
		'post',								// AJAX callback method
		'before_postbox',					// called before PostBox HTML is output
		'after_postbox',					// called after PostBox HTML is output
		'postbox_tabs',
	);

	protected function __construct()
	{
		parent::__construct();
		add_filter('peepso_postbox_interactions', array(&$this, 'postbox_type_interaction'), 1, 2);
		add_filter('peepso_postbox_interactions', array(&$this, 'postbox_privacy_interaction'), 2);
        add_filter('peepso_postbox_interactions', array(&$this, 'postbox_pin_interaction'), 14);
        add_filter('peepso_postbox_interactions', array(&$this, 'postbox_schedule_interaction'), 15);
		add_filter('peepso_postbox_interactions', array(&$this, 'postbox_status_interaction'), 90);
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
	}

	/*
	 * Enqueue scripts used by the PostBox
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script('peepso-postbox');
		wp_enqueue_script('peepso-resize');

		$aData = array(
			'postsize_reached' =>
				sprintf(
					__('You can only enter up to %d characters', 'peepso-core'),
					PeepSo::get_option('site_status_limit', 4000)
				)
		);
		wp_localize_script('peepso-postbox', 'peepsopostbox', $aData);
		// Fires when postbox is used.
		do_action('peepso_postbox_enqueue_scripts');
	}


	//// implementation of template tags

	/*
	 * Outputs post interaction UI elements
	 */
	public function post_interactions($params = array())
	{
		$inter = apply_filters('peepso_postbox_interactions', array(), $params);

		if (isset($inter['privacy'])) {

			$privacy = PeepSoPrivacy::get_instance();
			$privacy_settings = apply_filters('peepso_postbox_access_settings', $privacy->get_access_settings());

            // admin defined default privacy
			$user_default_privacy = PeepSo::get_option('activity_privacy_default',PeepSo::ACCESS_PUBLIC);

			// #419 sticky post privacy
			$PeepSoProfile = PeepSoProfile::get_instance();

			$PeepSoUser = $PeepSoProfile->user;
			$postbox_user_id = $PeepSoUser->get_id();

			// only sticky post privacy postbox for current user profile
			if($postbox_user_id == get_current_user_id() || $postbox_user_id == NULL) {
				$user_last_used_privacy = PeepSo::get_last_used_privacy(get_current_user_id());
				if($user_last_used_privacy) {
					$user_default_privacy = $user_last_used_privacy;
				}
			}

			// Emergency fallback to PUBLIC in case the selected privacy level is not found
            if (!isset($privacy_settings[$user_default_privacy])) {
                $user_default_privacy = PeepSo::ACCESS_PUBLIC;
            }

			$inter['privacy']['extra'] = sprintf('<input type="hidden" autocomplete="off" id="postbox_acc" name="postbox_acc" value="%d" />', $user_default_privacy);
			$inter['privacy']['icon'] = (isset($privacy_settings[$user_default_privacy])) ? $privacy_settings[$user_default_privacy]['icon'] : '';
			$inter['privacy']['label'] = (isset($privacy_settings[$user_default_privacy])) ? $privacy_settings[$user_default_privacy]['label'] : '';
            $inter['privacy']['icon_html'] = $inter['privacy']['label'];

			$privacy_option = '<a href="#" class="ps-postbox__privacy-item" id="postbox-acc-%1$d" data-option-value="%1$d" onclick="return false;"><i class="%2$s"></i><span>%3$s</span></a>';

			$inter['privacy']['extra'] .= '<div class="ps-postbox__privacy"><div class="ps-dropdown__menu ps-postbox-privacy ps-privacy-dropdown">';

			foreach ($privacy_settings as $value => $setting) {
                $inter['privacy']['extra'] .= sprintf($privacy_option, $value, $setting['icon'], $setting['label']);
            }

			$inter['privacy']['extra'] .= '</div></div>';
		}

		$fOutput = FALSE;
		foreach ($inter as $key => $data) {
            echo '<div id="', $data['id'], '"';
            if (!empty($data['class']))
                echo ' class="', $data['class'], ' ps-js-interaction-wrapper"';
            if (!empty($data['style']))
                echo ' style="', $data['style'], '"';
            echo '><div class="ps-js-interaction-wrapper ps-js-postbox-toggle">';
            if (!empty($data['click'])) {
                echo '<a';
                if (isset($data['title']))
                    echo ' class="ps-postbox__menu-item-link ps-js-interaction-toggle ps-tooltip ps-tooltip--postbox" data-tooltip="', esc_attr($data['title']), '"';
                else
                    echo ' class="ps-postbox__menu-item-link ps-js-interaction-toggle ps-tooltip ps-tooltip--postbox"';
                echo ' onclick="', esc_js($data['click']), '">', PHP_EOL;
            }

            if (isset($data['icon'])) {
                if (!is_array($data['icon'])) {
                    $data['icon'] = array($data['icon']);
                }
                for ($i = 0; $i < count($data['icon']); $i++) {
                    echo '<i class="ps-icon ', $data['icon'][$i], '"></i>', PHP_EOL;
                }
            }

            if (isset($data['icon_html'])) {
                echo '<span class="ps-postbox__menu-item-label">', $data['icon_html'] , '</span><em class="gcis gci-chevron-down"></em>' , PHP_EOL;

            }

			if (!empty($data['click']))
				echo '</a>', PHP_EOL;
			echo '</div>';

			if (isset($data['extra']))
				echo $data['extra'];

			echo '</div>';

			$fOutput = TRUE;
		}

		if (!$fOutput)
			echo '&nbsp;';
	}

	/**
	 * This function inserts the post type dropdown on the post box, keep it as a filter callback so that
	 * other addons can know what position to place their custom post types.
	 * @param array $interactions is the formated html code that get inserted in the postbox
	 * @param array $params
	 */
	public function postbox_type_interaction($interactions, $params = array())
	{
		$types = apply_filters('peepso_post_types', array(
			'status' => array(
				'icon' => 'gcis gci-pen',
				'name' => __('Text post', 'peepso-core')
			),
		), $params);

		if(2 > count($types)) {
		    return $interactions;
		}

		$html = '<div class="ps-dropdown__menu ps-postbox__types ps-js-postbox-dropdown ps-js-postbox-type">';
		$icons = array();
		foreach ($types as $type => $data) {
			$icons[] = $data['icon'];
			$html .= '<a role="menuitem" class="ps-postbox__type" data-option-value="' . $type . '">';
			$html .= '<i class="ps-icon ' . $data['icon'] . '"></i>';
			$html .= '<span>' . $data['name'] . '</span>';
			$html .= '</a>';
		}
		$html .= '</div>';

		$interactions['type'] = array(
			'id' => 'type-tab',
			'class' => 'ps-postbox__menu-item ps-postbox__menu-item--type',
			'click' => 'return;',
			'title' => __('Post type', 'peepso-core'),
			'icon' => $icons,
			'extra' => $html
		);

		return ($interactions);
	}

	/**
	 * This function inserts the privacy dropdown on the post box, keep it as a filter callback so that
	 * other addons can know what position to place their custom post types.
	 * @param array $interactions is the formated html code that get inserted in the postbox
	 */
	public function postbox_privacy_interaction($interactions)
	{
		$interactions['privacy'] = array(
			'id' => 'privacy-tab',
			'class' => 'ps-postbox__menu-item ps-postbox__menu-item--privacy',
			'click' => 'return;',
			'title' => __('Privacy', 'peepso-core'),
		);

		return ($interactions);
	}

	/**
	 * This function inserts the post scheduler dropdown on the post box.
	 * @param array $interactions is the formated html code that get inserted in the postbox
	 */
    public function postbox_schedule_interaction($interactions)
    {
        if(!PeepSo::can_schedule_posts()) { return $interactions; }

        $interactions['schedule'] = array(
            'icon' => 'gcir gci-clock',
            'icon_html' => '',
            'id' => 'schedule-tab',
            'class' => 'ps-postbox__menu-item ps-postbox__menu-item--schedule',
            'click' => 'return;',
            'title' => __('Schedule', 'peepso-core'),
            'extra' => PeepSoTemplate::exec_template('general', 'postbox-interaction-schedule', null, true),
        );

        return ($interactions);
    }

    public function postbox_pin_interaction($interactions)
    {
		// Do not add the pin button if Pinned Posts feature is disabled.
		if (!PeepSo::get_option_new('pinned_posts_enable')) {
			return $interactions;
		}

        $class = PeepSo::can_pin() ? '' : ' ps-postbox__menu-item--hidden';

        $interactions['pin'] = array(
            'icon' => 'gcis gci-thumbtack',
            'icon_html' => '',
            'id' => 'pin-tab',
            'class' => 'ps-postbox__menu-item ps-postbox__menu-item--pin'.$class,
            'click' => 'return;',
            'title' => __('Pin', 'peepso-core'),
            'extra' => PeepSoTemplate::exec_template('general', 'postbox-interaction-pin', null, true),
        );

        return ($interactions);
    }

	/**
	* This function inserts the status post type on the post box, keep it as a filter callback so that
	* other addons can know what position to place their custom post types.
	* @param array $interactions is the formated html code that get inserted in the postbox
	*/
	public function postbox_status_interaction($interactions)
	{
		$interactions['status'] = array(
			'icon' => 'gcis gci-pen',
			'id' => 'status-post',
			'class' => 'ps-postbox__menu-item',
			'click' => 'return;',
			'label' => '',
			'title' => __('Post a Status', 'peepso-core'),
			'style' => 'display:none'
		);

		return ($interactions);
	}

	/*
	 * Triggers action/hook points for add-ons to output content before the postbox
	 */
	public function before_postbox()
	{
		do_action('peepso_postbox_before');
	}


	/*
	 * Triggers action/hook points for add-ons to output content after the postbox
	 */
	public function after_postbox()
	{
		do_action('peepso_postbox_after');
	}

	/**
	 * Display available post box tabs
	 */
	public function postbox_tabs()
	{
		$tabs = apply_filters('peepso_postbox_tabs', array());

		foreach ($tabs as $id => $html) {
			echo '<div class="ps-postbox__view" data-tab-id="', $id, '">';
			echo $html;
			echo '</div>';
		}
	}

	/**
	 * Performs a post operation, adding to a user's wall
	 * @param  PeepSoAjaxResponse $resp Instance of PeepSoAjaxResponse
	 */
	public function post(PeepSoAjaxResponse $resp)
	{
		$input = new PeepSoInput();
		$content = $input->raw('content');
		$user_id = $input->int('id');
		$owner_id = $input->int('uid');
		$access = $input->int('acc');
        $repost = $input->int('repost', NULL);


        // SQL injection safe, it is escaped in add_post
        $future = $input->value('future', NULL, FALSE);
        $pin = $input->value('pin', NULL, FALSE);


		if (0 === $owner_id) {
            $owner_id = $user_id;
        }

		$type = $input->value('type', '', FALSE); // SQL Safe
		$PeepSoUser = PeepSoUser::get_instance($owner_id);
		if (PeepSoUser::is_accessible_static($PeepSoUser->get_profile_post_accessibility(), $owner_id)
			&& PeepSo::check_permissions($owner_id, PeepSo::PERM_POST, $user_id)) {
			$args = array(
				'content' => $content,
				'user_id' => $user_id,
				'target_user_id' => $owner_id,
				'type' => $type,
				'written' => 0,
			);

			$act = PeepSoActivity::get_instance();
			$extra = array(
                'module_id' => $input->int('module_id', PeepSoActivity::MODULE_ID),
                'show_preview' => $input->int('show_preview', 1),
            );

			if ($access) {
                $extra['act_access'] = $access;
            }

			if (!is_null($repost)) {
                $extra['repost'] = $repost;
            }

            if($future) {
                $extra['future'] = $future;
            }

            if($pin) {
                $extra['pin'] = $pin;
            }

			$res = $act->add_post($owner_id, $user_id, $content, $extra);

			if (FALSE !== $res) {
				$args['written'] = 1;
				$args['post_id'] = $res;
			}


			if (isset($args['written']) && 1 == $args['written']) {

				$resp->success(TRUE);

				$wpq = $act->get_post(intval($args['post_id']), $owner_id, $user_id);

				if ($act->has_posts()) { // ($wpq->have_posts()) {
                    $act->next_post();

                    // check if post status is pending
                    $is_pending = isset($act->post_data) && $act->post_data['post_status'] === 'pending';

                    if (!$is_pending) {
                        ob_start();
                        $act->show_post();
                        $post_data = ob_get_clean();

                        $resp->set('post_id', $args['post_id']);
                        $resp->set('html', $post_data);
                    }

                    if (NULL !== $repost)
                        $resp->notice(__('This post was successfully shared.', 'peepso-core'));
                    else
                        $resp->notice(__('Post added.', 'peepso-core'));
                    }

			} else {
				$resp->success(FALSE);
				$resp->error(__('Error in writing Activity Stream post', 'peepso-core'));
			}
		} else {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions or invalid User Id / Owner Id.', 'peepso-core'));
		}
	}
}

// EOF
