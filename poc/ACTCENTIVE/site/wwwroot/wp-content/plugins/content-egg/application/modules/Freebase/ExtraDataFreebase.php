<?php

namespace ContentEgg\application\modules\Freebase;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataFreebase class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataFreebase extends ExtraData {

	public $article;
	public $officialWebsite = array();
	public $topicEquivalentWebpage = array();
	public $topicalWebpage = array();
	public $quotations = array();
	public $notableFor = array();
	public $notableTypes = array();
	public $awardNominations = array();
	public $artistTrack = array();
	public $dateOfBirth;
	public $freebaseId;

}
