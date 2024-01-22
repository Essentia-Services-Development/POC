<?php

namespace ContentEgg\application\modules\Udemy;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataUdemy class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class ExtraDataUdemy extends ExtraData {

	public $is_paid;
	public $avg_rating;
	public $num_reviews;
	public $visible_instructors = array();
	public $num_subscribers;
	public $num_reviews_recent;
	public $favorite_time;
	public $is_wishlisted;
	public $num_lectures;
	public $num_published_lectures;
	public $num_published_quizzes;
	public $num_curriculum_items;
	public $quality_status;
	public $status_label;
	public $image_750x422;
	public $has_certificate;
	public $locale;
	public $created;
	public $instructional_level;
	public $content_info;
	public $published_time;
	public $checkout_url;
	public $prerequisites = array();
	public $objectives = array();
	public $objectives_summary = array();
	public $target_audiences = array();
	public $bestseller_badge_content = array();

}
