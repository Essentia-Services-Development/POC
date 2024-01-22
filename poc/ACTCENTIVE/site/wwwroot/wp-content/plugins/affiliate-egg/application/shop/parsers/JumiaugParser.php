<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * JumiaugParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
require_once dirname(__FILE__) . '/JumiacomegParser.php';

class JumiaugParser extends JumiacomegParser {

    protected $charset = 'utf-8';
    protected $currency = 'UGX';

}
