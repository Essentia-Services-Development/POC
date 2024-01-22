<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ShopeecothParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
require_once dirname(__FILE__) . '/ShopeevnParser.php';

class ShopeecothParser extends ShopeevnParser {

    protected $canonical_domain = 'https://shopee.co.th';
    protected $currency = 'THB';

}
