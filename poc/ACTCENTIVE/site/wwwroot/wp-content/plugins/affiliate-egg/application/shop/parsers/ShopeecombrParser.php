<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ShopeecombrParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
require_once dirname(__FILE__) . '/ShopeevnParser.php';

class ShopeecombrParser extends ShopeevnParser {

    protected $canonical_domain = 'https://shopee.com.br';
    protected $currency = 'BRL';

}
