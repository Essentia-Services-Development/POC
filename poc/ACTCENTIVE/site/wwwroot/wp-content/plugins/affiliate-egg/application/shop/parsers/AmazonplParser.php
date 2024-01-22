<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmazonplParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
require_once dirname(__FILE__) . '/AmazoncomParser.php';

class AmazonplParser extends AmazoncomParser {

    protected $canonical_domain = 'https://www.amazon.pl';
    protected $currency = 'PLN';

}
