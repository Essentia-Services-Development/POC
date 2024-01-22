<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * PriceministercomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 * 
 */
require_once dirname(__FILE__) . '/RakutencomParser.php';

class PriceministercomParser extends RakutencomParser {

    protected $currency = 'EUR';

}
