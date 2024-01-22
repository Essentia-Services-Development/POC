<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmazoncoukParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
require_once dirname(__FILE__) . '/AmazoncomParser.php';

class AmazoncoukParser extends AmazoncomParser {

    protected $canonical_domain = 'https://www.amazon.co.uk';
    //protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
    protected $user_agent = array('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:74.0) Gecko/20100101 Firefox/74.0');
    protected $currency = 'GBP';

}
