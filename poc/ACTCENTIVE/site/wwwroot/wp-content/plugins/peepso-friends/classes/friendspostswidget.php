<?php

class PeepSoFriendsPostsWidget extends WP_Widget
{
	public $template_tags = array(
		'show_post'
	);

	/**
	 * Register widget with WordPress.
	 */
	public function __construct()
	{
		parent::__construct(
			'peepso_friends_posts_widget', // Base ID
			__('Friends Posts', 'friendso'), // Name
			array('description' => __('This will select up to 5 posts, displaying the most recent post by each of up to five users that are friends.',
				'friendso'),
			) // Args
		);
	}

	public static function get_instance()
	{
		return new PeepSoFriendsPostsWidget();
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance)
	{	
		$activity = PeepSoActivity::get_instance();

		add_filter('peepso_activity_post_clauses', array(&$this, 'filter_post_clauses'), 10, 2);
		add_filter('peepso_user_profile_id', array(&$this, 'force_user_id'), 90);

		$user = get_current_user_id();
		$activity->get_posts(0, NULL, $user, $user);

		remove_filter('peepso_activity_post_clauses', array(&$this, 'filter_post_clauses'), 10);
		remove_filter('peepso_user_profile_id', array(&$this, 'force_user_id'), 90);

		PeepSoTemplate::exec_template('widgets', 'friendsposts');
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance)
	{

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance)
	{
		return array();
	}

	/**
	 * Modify the clauses to filter posts of friends only
	 * @param  array $clauses
	 * @param  int $user_id The owner of the activity stream
	 * @return array
	 */
	public function filter_post_clauses($clauses, $user_id = NULL)
	{
		$clauses['where'] .= ' AND `friends`.`fnd_id` IS NOT NULL ';
		$clauses['limits'] = 'LIMIT 0, 5';
		return $clauses;
	}

	/**
	 * Forces the query to search through all posts
	 */
	public function force_user_id($user_id)
	{
		return 0;
	}

	/*
	 * outputs the contents of a single post
	 */
	public function show_post()
	{
		$activity = PeepSoActivity::get_instance();
		PeepSoTemplate::exec_template('widgets', 'friendsposts.post', $activity->post_data);
	}

} // class Foo_Widget