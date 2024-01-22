<?php

namespace ExternalImporter\application\libs\pextractor\parser;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\XPath;

/**
 * Dom class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
interface ParserInterface {

    public function __construct($url);

    public function parseListing(XPath $xpath, $html);

    public function parseProduct(XPath $xpath, $html);
}
