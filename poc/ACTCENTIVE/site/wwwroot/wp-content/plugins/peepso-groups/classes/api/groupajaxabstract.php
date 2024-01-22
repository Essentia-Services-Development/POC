<?php

class PeepSoGroupAjaxAbstract extends PeepSoAjaxCallback
{
	protected $_group_id;		// ID of the group
	protected $_user_id;		// ID of the current user
	protected $_model;			// Model instance applicable to the given endpoint

	protected function __construct()
	{
		parent::__construct();

		$this->_user_id = get_current_user_id();
		if($this->_request_method == 'post') {
			$this->_group_id = $this->_input->int('group_id');
		} else {
			$this->_group_id = $this->_input->int('group_id');
		}
	}

	/** GLOBAL PEEPSOGROUP(*)AJAX UTILITIES **/
	public static function parse_keys($default, $keys)
	{
		$raw_keys = explode(',', $keys);
		$keys = array();

		foreach($raw_keys as $key) {
			if(strstr($key, '.')) {
				$key = explode('.', $key);

				$class = $key[0];
				$method = $key[1];
			} else {
				$class = $default;
				$method = $key;
			}

			$keys[] = array('class' =>$class,'method'=>$method);
		}

		return $keys;
	}

	public static function format_response( $class, $keys, $group_id )
	{
		#var_dump($keys);die();
		$resp = array();
		foreach($keys as $key) {

			$class_key  = $key['class'];
			$class_name = "peepso$class_key";
			$method_key = $key['method'];

			// if the passed class instance is what we want data from
			if($class instanceof  $class_name) {
				$resp[$method_key] = $class->get($method_key);

				// Add markdown tag in the group description.
				if ('description' === $method_key && PeepSo::get_option_new('md_groups_about', 0)) {
					$resp[$method_key] = PeepSo::do_parsedown($resp[$method_key]);
				}
			} else {
				// PeepSoGroup
				if('group' == $class_key) {
					if(!isset($peepsogroup)) {
						$peepsogroup = new PeepSoGroup($group_id);
					}

					$tmp_class = $peepsogroup;
				}

                // PeepSoGroupFollower
                if('groupfollowerajax' == $class_key) {
                    if(!isset($PeepSoGroupFollowerAjax)) {
                        $PeepSoGroupFollowerAjax = PeepSoGroupFollowerAjax::get_instance();
                        $PeepSoGroupFollowerAjax->init($group_id);
                    }

                    $tmp_class = $PeepSoGroupFollowerAjax;
                }

				// PeepSoGroupUser
				if('groupuserajax' == $class_key) {
					if(!isset($PeepSoGroupUserAjax)) {
						$PeepSoGroupUserAjax = PeepSoGroupUserAjax::get_instance();
						$PeepSoGroupUserAjax->init($group_id);
					}

					$tmp_class = $PeepSoGroupUserAjax;
				}

				// PeepSoGroupUsers
				if('groupusersajax' == $class_key) {
					if(!isset($PeepSoGroupUsersAjax)) {
						$PeepSoGroupUsersAjax = PeepSoGroupUsersAjax::get_instance();
						$PeepSoGroupUsersAjax->init($group_id);
					}

					$tmp_class = $PeepSoGroupUsersAjax;
				}

				// PeepSoGroupCategoriesGroups
				if('groupcategoriesgroupsajax' == $class_key) {
					if(!isset($PeepSoGroupCategoriesGroupsAjax)) {
						$PeepSoGroupCategoriesGroupsAjax = PeepSoGroupCategoriesGroupsAjax::get_instance();
						$PeepSoGroupCategoriesGroupsAjax->init($group_id);
					}

					$tmp_class = $PeepSoGroupCategoriesGroupsAjax;
				}

				if(strstr($method_key, '(')) {
					$method = explode('(', $method_key);
					$method_key = $method[0];

					$from = array('|',')');
					$to = array(',','');
					$keys = str_replace($from, $to, $method[1]);

					$resp[$class_key][$method_key] = $tmp_class->$method_key($keys);
				} else {
					$resp[$class_key][$method_key] = $tmp_class->$method_key();
				}
			}
		}

		return $resp;
	}
}
