<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXo5VDdKVWhJY3FhbDFCWUZ2QnZjdFEwR0I0SXhQMkJjdEhSS0ZVWlRKcFhQVHBBbm5rUDRLbVE1SDEzdUxGSDdPZnhiUnNzZDQ4aGNKUVE0WEtVMkhwQmVwN3M4VHhZL0RYQmFxa3hxcnZmb0licTN5dGJubFcydXl2d1pSZTJnPQ==*/

class PeepSoPhotos extends PeepSoAjaxCallback
{
	private static $_peepsophotos = NULL;

	public $template_tags = array(
		'get_next_photo',
		'show_photo',
		'show_photo_comments',
		'photo_album_extra_fields',
	);

	/**
	 * Initialize all variables, filters and actions
	 */
	protected function __construct()
	{
		parent::__construct();
		self::$_peepsophotos = PeepSoSharePhotos::get_instance();
	}

	/**
	 * Iterates throught the $_photos ArrayObject and returns the current photo
	 * @param int $photo_id
	 * @return PeepSoUser
	 */
	public function get_next_photo()
	{
		return (self::$_peepsophotos->get_next_photo());
	}

	/**
	 * Shows a single photo.
	 * @param WP_Post $photo A WP_Post object with a post type of peepso-photo.
	 */
	public function show_photo($photo)
	{
		return (self::$_peepsophotos->show_photo($photo));
	}

	/**
	 * Shows a single photo comments.
	 * @param WP_Post $photo A WP_Post object with a post type of peepso-photo.
	 */
	public function show_photo_comments($photo)
	{
		return (self::$_peepsophotos->show_photo_comments($photo));
	}

	//// implementation of template tags

	/*
	 * Outputs create album extra fields elements
	 */
	public function photo_album_extra_fields($params = array())
	{
		$extra = apply_filters('peepso_photo_album_extra_fields', array(), $params);

		foreach ($extra as $key => $data) {

			$isfull = '';
			if(isset($data['isfull']) && $data['isfull'] === TRUE) {
				$isfull = ' ps-form__row--full';
			}

			echo '<div class="ps-form__row ' . $isfull . '">';
			echo '<label class="ps-form__label">' . $data['label'] . '</label>';
			echo '<div class="ps-form__field">';
			if(isset($data['field'])) {
				echo $data['field'];
			}
			echo '</div>';
			echo '</div>';

			if (isset($data['extra'])) {
				echo $data['extra'];
			}
		}
	}

	/*
	 * Outputs detail album extra fields elements
	 */
	public function photo_album_show_extra_fields($post_id = '', $can_edit = false)
	{
		$extra = '';
		$extra = apply_filters('peepso_photo_album_show_extra_fields', $extra, $post_id, $can_edit);

		if(!empty($extra)) {
			echo $extra;
		}
	}
}

// EOF
