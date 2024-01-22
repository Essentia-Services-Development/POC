<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmazoncommxParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
require_once dirname(__FILE__) . '/AmazoncomParser.php';

class AmazoncommxParser extends AmazoncomParser {

    protected $canonical_domain = 'https://www.amazon.com.mx';
    protected $currency = 'MXN';

}
