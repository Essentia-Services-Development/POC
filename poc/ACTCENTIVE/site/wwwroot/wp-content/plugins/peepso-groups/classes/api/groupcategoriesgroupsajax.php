<?php

class PeepSoGroupCategoriesGroupsAjax extends PeepSoAjaxCallback
{
	private $_group_id;

	protected function __construct()
	{
		parent::__construct();

		$this->_group_id = $this->_input->int('group_id');

		if(0 == $this->_group_id) {
			return;
		}
	}

    public function ajax_auth_exceptions()
    {
        $list_exception = array();
        $allow_guest_access = PeepSo::get_option('groups_allow_guest_access_to_groups_listing', 0);
        if($allow_guest_access) {
            array_push($list_exception, 'categories_for_group');
        }

        return $list_exception;
    }

	public function init($group_id)
	{
		$this->_group_id = $group_id;
	}

	public function categories_for_group(PeepSoAjaxResponse $resp)
	{
		$categories =  PeepSoGroupCategoriesGroups::get_categories_for_group($this->_group_id);

		if(count($categories)) {

			foreach ($categories as $category) {
			    // SQL safe, parsed
				$categories_response[] = PeepSoGroupAjaxAbstract::format_response($category, PeepSoGroupAjaxAbstract::parse_keys('groupcategory', $this->_input->value('keys', 'id', FALSE)), $this->_group_id);
			}
		}

		$resp->success(1);
		$resp->set('categories', $categories_response);
	}
}
