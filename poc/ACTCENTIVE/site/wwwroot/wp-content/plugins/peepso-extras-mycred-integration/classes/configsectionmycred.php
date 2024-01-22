<?php

class PeepSoConfigSectionMyCred extends PeepSoConfigSectionAbstract {

// Builds the groups array
	public function register_config_groups() {
		$this->context = 'left';
		$this->_group_general();
	}

	private function _group_general() {
		# Enable myCRED
		if (class_exists('myCRED_Core')) {
			$this->args('default', 0);
			$this->args('descript', __("When enabled it will show user's points history in their profile.", 'peepsocreds'));
			$this->set_field(
					'mycred_point_history_enabled', __('Show myCRED Points History Page', 'peepsocreds'), 'yesno_switch'
			);
		} else {

			$mycred = ' <a href="plugin-install.php?tab=plugin-information&plugin=mycred&TB_iframe=true&width=750&height=500" class="thickbox">myCRED</a> ';


			# Enable myCRED Description
			$this->set_field(
					'mycred_disabled_description', sprintf(__("Requires $mycred to be installed and properly configured. This is a third party plugin, so use at your own risk.<br/><br/><br/>"
									. "$mycred not found! Please install the plugin to see the configuration setting", 'peepsocreds'), $mycred), 'message'
			);
		}

		// Build Group
		$this->set_group(
				'general', __('General', 'peepsocreds')
		);
	}

}
