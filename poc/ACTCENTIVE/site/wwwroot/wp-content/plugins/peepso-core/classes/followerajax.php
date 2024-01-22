<?php

class PeepSoFollowerAjax extends PeepSoAjaxCallback
{
	private static $_peepsolocation = NULL;

	protected function __construct()
	{
		parent::__construct();
	}

	public function set_follow_status(PeepSoAjaxResponse $resp) {
        $passive_user_id = $this->_input->int('user_id');
        $follow = $this->_input->int('follow');

        $PeepSoUserFollower = new PeepSoUserFollower($passive_user_id, get_current_user_id(), TRUE);
        $PeepSoUserFollower->set('follow', $follow);

		$actions = PeepSoMemberSearch::get_actions($passive_user_id);

        $resp->success(TRUE);
        $resp->set('actions', $actions);
    }

    public function get_user_followers(PeepSoAjaxResponse $resp) {
        $user_id = $this->_input->int('user_id');
        $page = $this->_input->int('page', 1);

        // default limit is 1 (NewScroll)
        $limit = $this->_input->int('limit', 1);

        $offset = ($page - 1) * $limit;

        if ($page < 1) {
            $page = 1;
            $offset = 0;
        }

        $users =  PeepSoUserFollower::get_followers([
            'offset' => $offset,
            'limit' => $limit,
            'user_id' => $user_id
        ]);

        ob_start();

        if (count($users)) {
            foreach ($users as $user) {
                echo '<div class="ps-member ps-js-member" data-user-id="' . $user->uf_active_user_id . '">';
                echo '<div class="ps-member__inner">';
                PeepSoTemplate::exec_template('members', 'member-item', array(
                    'hide_member_buttons_extra' => TRUE,
                    'member' => PeepSoUser::get_instance($user->uf_active_user_id)
                ));

                echo '</div></div>';
            }

            $resp->success(1);
            $resp->set('found_followers', count($users));
            $resp->set('followers', ob_get_clean());
        } else {
        	$message =  (get_current_user_id() == $user_id) ? __('You have no followers yet', 'peepso-core') : sprintf(__('%s has no followers yet', 'peepso-core'), PeepSoUser::get_instance($user_id)->get_firstname());
            $resp->error(PeepSoTemplate::exec_template('profile','no-results-ajax', array('message' => $message), TRUE));
		}
    }

    
    public function get_user_following(PeepSoAjaxResponse $resp) {
        $user_id = $this->_input->int('user_id');
        $page = $this->_input->int('page', 1);

        // default limit is 1 (NewScroll)
        $limit = $this->_input->int('limit', 1);

        $offset = ($page - 1) * $limit;

        if ($page < 1) {
            $page = 1;
            $offset = 0;
        }

        $users =  PeepSoUserFollower::get_following([
            'offset' => $offset,
            'limit' => $limit,
            'user_id' => $user_id
        ]);

        ob_start();

        if (count($users)) {
            foreach ($users as $user) {
                echo '<div class="ps-member ps-js-member" data-user-id="' . $user->uf_passive_user_id . '">';
                echo '<div class="ps-member__inner">';
                PeepSoTemplate::exec_template('members', 'member-item', array(
                    'hide_member_buttons_extra' => TRUE,
                    'member' => PeepSoUser::get_instance($user->uf_passive_user_id)
                ));

                echo '</div></div>';
            }

            $resp->success(1);
            $resp->set('found_following', count($users));
            $resp->set('following', ob_get_clean());
        } else {
        	$message =  (get_current_user_id() == $user_id) ? __('You have not followed anybody yet', 'peepso-core') : sprintf(__('%s has not followed anybody yet', 'peepso-core'), PeepSoUser::get_instance($user_id)->get_firstname());
            $resp->error(PeepSoTemplate::exec_template('profile','no-results-ajax', array('message' => $message), TRUE));
		}
    }
}

// EOF