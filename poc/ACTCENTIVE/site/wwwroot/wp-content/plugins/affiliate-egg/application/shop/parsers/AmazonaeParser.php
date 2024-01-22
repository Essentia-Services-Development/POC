<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmazonaeParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
require_once dirname(__FILE__) . '/AmazoncomParser.php';

class AmazonaeParser extends AmazoncomParser {

    //protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
    protected $canonical_domain = 'https://www.amazon.ae';
    protected $currency = 'AED';

}
