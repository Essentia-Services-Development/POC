<?php

namespace Keywordrush\AffiliateEgg;

/**
 * Shop class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class Shop {

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public $id;
    public $name;
    public $uri;
    public $status;
    public $ico;
    public $img;
    public $description;
    public $deeplink;
    public $search_uri;
    public $cpa = array();
    public $is_deprecated = false;
    public $is_unstable = false;
    public $is_custom = false;
    public $file;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName($custom = true, $deprecated = false, $unstable = false)
    {
        $name = $this->name;
        if ($deprecated && $this->isDeprecated())
            $name .= ' (deprecated)';
        elseif ($custom && $this->isCustom())
            $name .= ' (custom)';
        elseif ($unstable && $this->isUnstable())
            $name .= ' (unstable)';
        return $name;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getHost()
    {
        return preg_replace('/^www\./', '', parse_url($this->uri, PHP_URL_HOST));
    }

    public function isDeprecated()
    {
        if ($this->is_deprecated)
            return true;
        else
            return false;
    }

    public function isUnstable()
    {
        if ($this->is_unstable)
            return true;
        else
            return false;
    }

    public function getStatus()
    {
        if ($this->status !== null)
            return $this->status;
        else
            return self::STATUS_ACTIVE;
    }

    public function isActive()
    {
        if ($this->getStatus() == self::STATUS_ACTIVE)
            return true;
        else
            return false;
    }

    public function isSearchUriExists()
    {
        if ($this->getSearchUri())
            return true;
        else
            return false;
    }

    public function getSearchUri()
    {
        if (empty($this->search_uri))
            return '';
        else
            return $this->search_uri;
    }

    public function isCustom()
    {
        if ($this->is_custom)
            return true;
        else
            return false;
    }

    public function getDefaultCurrency()
    {
        $parser = ParserManager::getInstance()->getParserById($this->id);
        return $parser->getDefaultCurrency();
    }

}
