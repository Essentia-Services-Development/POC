<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmazoncomtrParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
require_once dirname(__FILE__) . '/AmazoncomParser.php';

class AmazoncomtrParser extends AmazoncomParser {

    protected $canonical_domain = 'https://www.amazon.com.tr';
    protected $currency = 'TRY';

}
