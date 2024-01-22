<?php

class PeepSoPrivacy
{
	private static $_instance = NULL;

	private function __construct(){}

	public static function get_instance()
	{
		if (self::$_instance === NULL) {
            self::$_instance = new self();
        }

		return (self::$_instance);
	}

	/**
	 * Return the array of privacy options available.
	 * @return array
	 */
	public function get_access_settings()
	{
		$aAccess = array(
			PeepSo::ACCESS_PUBLIC => array('icon' => 'gcis gci-globe-americas', 'label' => __('Public', 'peepso-core')),
			PeepSo::ACCESS_MEMBERS => array('icon' => 'gcis gci-user-friends', 'label' => __('Site Members', 'peepso-core')),
			PeepSo::ACCESS_PRIVATE => array('icon' => 'gcis gci-lock', 'label' => __('Only Me', 'peepso-core')),
		);

		$aAccess = apply_filters('peepso_privacy_access_levels', $aAccess);

        return $aAccess;
	}

    /**
     * Return the array of privacy options available for user profiles
     * @return array
     */
    public function get_access_settings_profile()
    {
        $all_access = $this->get_access_settings();

        // cherry pick only "public" and "members"
        $access = [];

        if(array_key_exists(PeepSo::ACCESS_PUBLIC, $all_access)) {
            $access[PeepSo::ACCESS_PUBLIC]=$all_access[PeepSo::ACCESS_PUBLIC];
        }

        if(array_key_exists(PeepSo::ACCESS_MEMBERS, $all_access)) {
            $access[PeepSo::ACCESS_MEMBERS]=$all_access[PeepSo::ACCESS_MEMBERS];
        }

        return (apply_filters('peepso_privacy_access_levels_profile', $access));
    }

	/**
	 * Get an access level by associative key.
	 * @param  string $access_level The key of the access level to get. Access level may be one of the following PeepSo::ACCESS_PUBLIC, PeepSo::ACCESS_MEMBERS, PeepSo::ACCESS_PRIVATE
	 * @return array for icon and label
	 */
	public function get_access_setting($access_level)
	{
		$levels = $this->get_access_settings();
        reset($levels);
		return (isset($levels[$access_level]) ? $levels[$access_level] : $levels[key($levels)]);
	}

	/**
	 * Displays the privacy options in an unordered list.
	 * @param string $callback Javascript callback
	 */
	public function render_dropdown($callback = '')
	{
	    ob_start();

		echo '<div class="ps-dropdown__menu ps-js-dropdown-menu">';

		$options = $this->get_access_settings();

		foreach ($options as $key => $option) {
			printf('<a href="#" data-option-value="%d" onclick="return %s"><i class="%s"></i><span>%s</span></a>',
				$key, $callback, $option['icon'], esc_html($option['label'])
			);
		}
		echo '</div>';

		return ob_get_clean();
	}
}
