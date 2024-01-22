<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * JumiaciParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
require_once dirname(__FILE__) . '/JumiacomegParser.php';

class JumiaciParser extends JumiacomegParser {

    protected $charset = 'utf-8';
    protected $currency = 'XOF';

}
