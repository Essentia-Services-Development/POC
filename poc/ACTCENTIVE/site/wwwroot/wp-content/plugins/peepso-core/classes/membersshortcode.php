<?php

class PeepSoMembersShortcode
{
	public $template_tags = array(
		'show_member'
	);

	public function __construct()
	{
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
	}

	/**
	 * Enqueues the scripts used in this shortcode only.
	 */
	public function enqueue_scripts()
	{

	}

	public static function description() {
	    return __('Displays the list of your community members. You can search and filter through your community members. Filtering is also possible by Custom Profile Fields.','peepso-core');
    }

    public static function post_state() {
        return _x('PeepSo', 'Page listing','peepso-core') . ' - ' . __('Members', 'peepso-core');
    }

	/**
	 * Displays the member search page.
	 */
	public function shortcode_search()
	{
	    PeepSo::reset_query();
		$allow_guest_access = PeepSo::get_option('allow_guest_access_to_members_listing', 0);
		if(get_current_user_id() > 0 || !$allow_guest_access) {
			do_action('peepso_profile_completeness_redirect');
		}

		PeepSo::set_current_shortcode('peepso_members');

		if (FALSE == apply_filters('peepso_access_content', TRUE, 'peepso_members', PeepSo::MODULE_ID)) {
			return PeepSoTemplate::do_404();
		}

		// get gender field
		$PeepSoUser = PeepSoUser::get_instance(0);
		$profile_fields = new PeepSoProfileFields($PeepSoUser);
		$fields = $profile_fields->load_fields();

		ob_start();
		echo PeepSoTemplate::get_before_markup();

		$PeepSoUrlSegments= PeepSoUrlSegments::get_instance();

		$genders = array();

		if(isset($fields['peepso_user_field_gender'])) {
            $genders = $fields['peepso_user_field_gender']->meta->select_options;
        }

		if ('blocked' == $PeepSoUrlSegments->get(1)) {
			wp_enqueue_script('peepso-page-members', PeepSo::get_asset('js/page-blocked.min.js'), array('peepso', 'peepso-page-autoload'), PeepSo::PLUGIN_VERSION, TRUE);
			PeepSoTemplate::exec_template('members', 'members-blocked');
		} else {
			wp_enqueue_script('peepso-page-members', PeepSo::get_asset('js/page-members.min.js'), array('peepso', 'peepso-page-autoload'), PeepSo::PLUGIN_VERSION, TRUE);
			PeepSoTemplate::exec_template('members', 'search', array('allow_guest_access' => $allow_guest_access, 'genders' => $genders));
		}

		echo PeepSoTemplate::get_after_markup();

		return ob_get_clean();
	}
}

// EOF
