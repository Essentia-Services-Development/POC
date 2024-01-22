<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * WiggleesParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
require_once dirname(__FILE__) . '/WigglecomParser.php';

class WiggleesParser extends WigglecomParser {

    protected $currency = 'EUR';

}
