<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AliexpressruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
require_once dirname(__FILE__) . '/AliexpresscomParser.php';

class AliexpressruParser extends AliexpresscomParser {

    protected $canonical_domain = 'https://aliexpress.ru';
    protected $currency = 'USD';

}
