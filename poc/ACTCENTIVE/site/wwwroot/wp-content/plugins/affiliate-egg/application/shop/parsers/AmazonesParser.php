<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmazonesParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
require_once dirname(__FILE__) . '/AmazoncomParser.php';

class AmazonesParser extends AmazoncomParser {

    protected $canonical_domain = 'https://www.amazon.es';
    protected $currency = 'EUR';

    //protected $user_agent = array('wget');
    protected $user_agent = array('ia_archiver');
    
}
