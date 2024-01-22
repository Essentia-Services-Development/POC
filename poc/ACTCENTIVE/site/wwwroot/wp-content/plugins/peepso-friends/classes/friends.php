<?php

class PeepSoFriends
{
	private static $_instance = NULL;

	/**
	 * Class constructor
	 */
	private function __construct()
	{
	}

	/**
	 * Retrieve singleton class instance
	 * @return PeepSoMessage instance
	 */
	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}

	public $template_tags = array(
		'has_friends',
		'get_next_friend',
		'get_num_friends',
		'app_box',
	);

	/**
	 * Return TRUE/FALSE if the user has friends
	 * @param  int  $user_id The user id to check
	 * @return boolean
	 */
	public function has_friends($user_id = NULL)
	{
		return ($this->get_num_friends() > 0);
	}

	/**
	 * Iterates through the $_friends ArrayObject and returns the current friend in the loop as an
	 * instance of PeepSoUser.
	 * @param  int $user_id The user ID to get friends of.
	 * @return PeepSoUser A PeepSoUser instance of the current friend in the loop.
	 */
	public function get_next_friend($user_id = NULL)
	{
        $model = PeepSoFriendsModel::get_instance();
		if (is_null($model->_friends))
            $model->get_friends($user_id);

		if ($model->get_iterator()->valid()) {
			$friend = PeepSoUser::get_instance($model->get_iterator()->current());
			$model->get_iterator()->next();
			return ($friend);
		}

		return (FALSE);
	}

	/**
	 * Echoes the number of friends a user has.
	 * @param  int $user_id The user ID to search friends of.
	 */
	public function get_num_friends($user_id = NULL, $reset = FALSE)
	{
        $model = PeepSoFriendsModel::get_instance();
	    $user_id = $user_id ? $user_id : get_current_user_id();
	    $count = 0;

	    if($user_id) {
            $key = 'friends_count_' . $user_id;

            // MayFly cache?
            if(!$reset) {
                $cache_count = PeepSo3_Mayfly_Int::get($key);

                if (NULL !== $cache_count) {
                    return $cache_count;
                }
            }

            // Refresh count
            $count = $model->get_num_friends($user_id, TRUE);
            PeepSo3_Mayfly_Int::set($key, $count, PeepSoFriendsCache::CACHE_TIME);
        }

        return $count;
	}

	/**
	 * Template tag callback - used to render the Friends App Widget on templates.	 
	 */
	public function app_box()
	{
		$widget = new PeepSoFriendsAppWidget();
		$widget->widget();
	}	
}

// EOF