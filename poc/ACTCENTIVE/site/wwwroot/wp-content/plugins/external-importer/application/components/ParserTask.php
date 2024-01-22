<?php

namespace ExternalImporter\application\components;

defined('\ABSPATH') || exit;

/**
 * ParserTask class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ParserTask {

    const STEP_PARSE_LISING = 0;
    const STEP_PARSE_PRODUCTS = 1;
    const LINK_STATUS_NEW = 0;
    const LINK_STATUS_PROCESSED_SUCCESS = 1;
    const LINK_STATUS_PROCESSED_ERROR = -1;

    private $init_data = null;
    private $step = 0;
    private $consecutive_errors = 0;
    private $links = array(); // product links
    private $pagination = array(); // pagination links
    private $links_pointer = -1;
    private $pagination_pointer = -1;

    public function __construct($init_data)
    {
        $this->init_data = $init_data;
    }

    public function incrementConsecutiveErrors()
    {
        $this->consecutive_errors++;
    }

    public function getConsecutiveErrors()
    {
        return $this->consecutive_errors;
    }

    public function resetConsecutiveErrors()
    {
        $this->consecutive_errors = 0;
    }

    public function setStepParseListing()
    {
        $this->setStep(self::STEP_PARSE_LISING);
    }

    public function setStepParseProducts()
    {
        $this->setStep(self::STEP_PARSE_PRODUCTS);
    }

    public function setStep($step)
    {
        if (!in_array($step, array(self::STEP_PARSE_LISING, self::STEP_PARSE_PRODUCTS)))
            throw new \Exception('Step does not exist');
        $this->step = $step;
    }

    public function getStep()
    {
        return $this->step;
    }

    public function getNextLinkUri()
    {
        if (!$this->isNextLinkExists())
            return false;

        $this->links_pointer++;
        return $this->links[$this->links_pointer]['uri'];
    }

    public function isNextLinkExists()
    {
        if (isset($this->links[$this->links_pointer + 1]))
            return true;
        else
            return false;
    }

    public function getNextPaginationUri()
    {
        if (!$this->isNextPaginationExists())
            return false;

        $this->pagination_pointer++;
        return $this->pagination[$this->pagination_pointer]['uri'];
    }

    public function isNextPaginationExists()
    {
        if (isset($this->pagination[$this->pagination_pointer + 1]))
            return true;
        else
            return false;
    }

    public function getPaginationPointer()
    {
        return $this->pagination_pointer;
    }

    public function addLinks($links)
    {
        if (!is_array($links))
            $links = array($links);

        foreach ($links as $link)
        {
            if (self::isDublicateLink($link, $this->links))
                continue;
            $new['uri'] = $link;
            $new['status'] = self::LINK_STATUS_NEW;
            $this->links[] = $new;
        }
    }

    public function addPagination($links)
    {
        if (!is_array($links))
            $links = array($links);

        foreach ($links as $link)
        {
            if (self::isDublicateLink($link, $this->pagination))
                continue;
            $new['uri'] = $link;
            $new['status'] = self::LINK_STATUS_NEW;
            $this->pagination[] = $new;
        }
        if ($this->pagination_pointer >= 0)
            $current_page_url = $this->pagination[$this->pagination_pointer]['uri'];
        else
            $current_page_url = '';

        usort($this->pagination, function($a, $b) {
            
            if (!strstr($a['uri'], '?') && !strstr($b['uri'], '?'))
                return (strlen($a['uri']) > strlen($b['uri'])) ? 1 : -1;
            
            if (abs(strlen($a['uri']) - strlen($b['uri']) > 3))                  
                return (strlen($a['uri']) > strlen($b['uri'])) ? 1 : -1;            
            
            return strnatcasecmp($a['uri'], $b['uri']);
        });


        // set pagination pointer after sorting
        if ($current_page_url)
        {
            foreach ($this->pagination as $pointer => $pagination)
            {
                if ($pagination['uri'] == $current_page_url)
                {
                    $this->pagination_pointer = $pointer;
                    break;
                }
            }
        }
    }

    public function setLinkStatusSuccess()
    {
        $this->links[$this->links_pointer]['status'] = self::LINK_STATUS_PROCESSED_SUCCESS;
        $this->resetConsecutiveErrors();
    }

    public function setLinkStatusError()
    {
        $this->links[$this->links_pointer]['status'] = self::LINK_STATUS_PROCESSED_ERROR;
        $this->incrementConsecutiveErrors();
    }

    public function setPaginationStatusSuccess()
    {
        $this->pagination[$this->pagination_pointer]['status'] = self::LINK_STATUS_PROCESSED_SUCCESS;
        $this->resetConsecutiveErrors();
    }

    public function setPaginationStatusError()
    {
        $this->pagination[$this->pagination_pointer]['status'] = self::LINK_STATUS_PROCESSED_ERROR;
        $this->incrementConsecutiveErrors();
    }

    public function getPaginationCount()
    {
        return count($this->pagination);
    }

    public function getLinksCount()
    {
        return count($this->links);
    }

    public function setNextStep()
    {
        if ($this->isNextLinkExists())
        {
            $this->setStepParseProducts();
            return self::STEP_PARSE_PRODUCTS;
        } elseif ($this->isNextPaginationExists())
        {
            $this->setStepParseListing();
            return self::STEP_PARSE_LISING;
        } else
            return false;
    }

    public function getStat()
    {
        $success = $errors = $new = 0;
        foreach ($this->links as $link)
        {
            if ($link['status'] == self::LINK_STATUS_PROCESSED_SUCCESS)
                $success++;
            elseif ($link['status'] == self::LINK_STATUS_PROCESSED_ERROR)
                $errors++;
            elseif ($link['status'] == self::LINK_STATUS_NEW)
                $new++;
        }
        return array($new, $success, $errors);
    }

    public function getProductSuccessCount()
    {
        $count = 0;
        foreach ($this->links as $link)
        {
            if ($link['status'] == self::LINK_STATUS_PROCESSED_SUCCESS)
                $count++;
            if ($link['status'] == self::LINK_STATUS_NEW)
                break;
        }
        return $count;
    }

    public function isLimitProductReached()
    {
        if (!isset($this->init_data['max_count']))
            return false;
        if ($this->getProductSuccessCount() >= $this->init_data['max_count'])
            return true;
        else
            return false;
    }

    public static function isDublicateLink($url, array $links)
    {
        foreach ($links as $link)
        {
            if ($link['uri'] == $url)
                return true;
        }

        return false;
    }

    public function getPaginationParsedCount()
    {
        $parsed = 0;
        foreach ($this->pagination as $pointer => $pagination)
        {
            if ($pagination['status'] != self::LINK_STATUS_NEW)
                $parsed++;
        }
        return $parsed;
    }

}
