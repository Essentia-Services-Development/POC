<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmazoncaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
require_once dirname(__FILE__) . '/AmazoncomParser.php';

class AmazoncaParser extends AmazoncomParser {

    protected $canonical_domain = 'https://www.amazon.ca';
    protected $currency = 'CAD';

    //protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
}
