<?php

namespace ExternalImporter\application\libs\pextractor\parser\advanced;

defined('\ABSPATH') || exit;

use ExternalImporter\application\helpers\FormValidator;
use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\libs\pextractor\parser\parsers\AdvancedParser;

/**
 * AdvancedManager class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AdvancedManager {

    const CUSTOM_PARSER_DIR = 'ei-parsers';

    private static $instance;
    private $parsers;

    private function __construct()
    {
        $this->parsers = $this->getParsers();
    }

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    public static function getCustomParserDirs()
    {
        return array(
            \WP_CONTENT_DIR . '/' . self::CUSTOM_PARSER_DIR,
        );
    }

    public function getParserInstance($uri)
    {
        if (!$parser = $this->getParser($uri))
            return false;

        if (isset($parser['custom']) && $parser['custom'])
            $file = $parser['file'];
        else
            $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'parsers' . DIRECTORY_SEPARATOR . $parser['class'] . '.php';

        if (is_file($file))
            require_once($file);
        else
            throw new \Exception("Parser file '{$file}' not found.");

        $class = "\\ExternalImporter\\application\\libs\\pextractor\\parser\\parsers\\" . $parser['class'];
        if (class_exists($class, false) === false)
            throw new \Exception("Parser class '" . $parser['class'] . "' not found.");

        $instance = new $class($uri);

        if (!($instance instanceof AdvancedParser))
            throw new \Exception("The parser '" . $parser['class'] . "' must inherit from AdvancedParser.");

        return $instance;
    }

    public function getParser($uri)
    {
        $domain = TextHelper::getHostName($uri);
        if (isset($this->parsers[$domain]))
            return $this->parsers[$domain];
        else
            return false;
    }

    public function isParserExists($uri)
    {
        if ($this->getParser($uri))
            return true;
        else
            return false;
    }

    private function getParsers()
    {
        $parsers = require (dirname(__FILE__) . '/config.php');
        return array_merge($parsers, $this->getCustomParsers());
    }

    private function getCustomParsers()
    {
        $parsers = array();
        foreach (self::getCustomParserDirs() as $dir)
        {
            $parsers = array_merge($parsers, $this->scanCustomParsers($dir));
        }
        return $parsers;
    }

    private function scanCustomParsers($path)
    {
        if (!is_dir($path))
            return array();
        $files = glob($path . '/' . '*Advanced.php');

        $parsers = array();
        foreach ($files as $file)
        {
            $data = get_file_data($file, array('uri' => 'URI'));
            if (empty($data['uri']) || !FormValidator::valid_url($data['uri']))
                continue;

            $host = TextHelper::getHostName($data['uri']);
            $data['class'] = ucfirst(strtolower(basename($file, 'Advanced.php'))) . 'Advanced';
            $data['file'] = $file;
            $data['custom'] = true;
            $parsers[$host] = $data;
        }
        return $parsers;
    }

    public function getDomainList($default_only = false, $stable_only = false)
    {
        $list = array();
        foreach ($this->parsers as $domain => $parser)
        {
            if ($default_only && isset($parser['custom']) && $parser['custom'])
                continue;

            if ($stable_only && isset($parser['unstable']) && $parser['unstable'])
                continue;

            $list[] = $domain;
        }

        sort($list);
        return $list;
    }

}
