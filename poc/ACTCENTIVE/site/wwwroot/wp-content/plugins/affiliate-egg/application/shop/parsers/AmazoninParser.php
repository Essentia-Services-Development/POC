<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmazoninParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
require_once dirname(__FILE__) . '/AmazoncomParser.php';

class AmazoninParser extends AmazoncomParser
{

    protected $canonical_domain = 'https://www.amazon.in';
    //protected $user_agent = array('wget');
    protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');

    protected $currency = 'INR';
}
