<?php

class PeepSoGroupPrivacy
{
	public $settings = array();

	const PRIVACY_OPEN 		= 0;
	const PRIVACY_CLOSED 	= 1;
	const PRIVACY_SECRET 	= 2;

	public static function _($privacy = NULL)
	{
		$settings = array(

			self::PRIVACY_OPEN => array(
				'id' 	=> self::PRIVACY_OPEN,
				'icon'	=> 'gcis gci-globe-americas',
                'name'	=> __('Open', 'groupso'),
                'notif'	=> __('open', 'groupso'),
				'desc'	=> __('Non-members can see the group content, but they can\'t post.','groupso'),
			),

            self::PRIVACY_CLOSED => array(
                'id'	=> self::PRIVACY_CLOSED,
                'icon'	=> 'gcis gci-lock',
                'name'	=> __('Private', 'groupso'),
                'notif'	=> __('private', 'groupso'),
                'desc'	=> __('Users need to be invited or request the group membership.', 'groupso') . PHP_EOL . htmlspecialchars(__('Non-members can only see the group page.','groupso')),
            ),
            self::PRIVACY_SECRET=> array(
                'id'	=> self::PRIVACY_SECRET,
                'icon'	=> 'gcis gci-shield-alt',
                'name'	=> __('Secret', 'groupso'),
                'notif' => __('secret', 'groupso'),
                'desc'	=> __('Users need to be invited.','groupso') . PHP_EOL .  __('Non-members can\'t see the group at all.', 'groupso'),
            ),
		);

		// Return a single privacy setting if requested
		if(NULL !== $privacy) {
			return $settings[$privacy];
		}


		// Otherwise return everything
		return apply_filters('peepso_filter_groups_privacy_options', $settings);
	}

    public static function _default() {
        $options = self::_();
        $options = array_reverse($options);
        return array_pop($options);
    }

    /**
     * Displays the privacy options in an unordered list.
     * @param string $callback Javascript callback
     */
    public static function render_dropdown($callback = '')
    {
        ob_start();

        echo '<div class="ps-dropdown__menu ps-js-dropdown-menu">';

        $options = self::_();

        foreach ($options as $key => $option) {
            printf('<a href="#" class="ps-dropdown__group" data-option-value="%d" onclick="%s; return false;">%s</a>',
                $key, $callback, '<div class="ps-dropdown__group-title"><i class="' . $option['icon'] . '"></i><span>' . $option['name'] . '</span></div><div class="ps-dropdown__group-desc">' . nl2br($option['desc']) .'</div>'
            );
        }
        echo '</div>';

        return ob_get_clean();
    }
}
