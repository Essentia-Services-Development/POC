<?php

namespace ExternalImporter\application\libs\pextractor\client;

defined('\ABSPATH') || exit;

/**
 * XPath class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class XPath {

    protected $xpath;
    protected $dom;

    public function __construct(\DOMDocument $dom = null)
    {
        if ($dom)
            $this->load($dom);
    }

    public function load(\DOMDocument $dom)
    {
        $this->dom = $dom;
        $this->xpath = new \DomXPath($dom);
    }

    public function getDomXPath()
    {
        return $this->xpath;
    }

    public function getDom()
    {
        return $this->dom;
    }

    public function xpathScalar($path, $return_child = false)
    {
        if (is_array($path))
            return $this->xpathScalarMulty($path, $return_child);

        $res = $this->xpath->query($path);
        if ($res && $res->length > 0)
        {
            if ($return_child)
            {
                foreach ($res as $tag)
                {
                    return $this->xpathReturnChild($tag);
                }
            }

            return trim(strip_tags($res->item(0)->nodeValue));
        } else
            return null;
    }

    public function xpathScalarMulty(array $paths, $return_child = false)
    {
        foreach ($paths as $path)
        {
            if ($r = $this->xpathScalar($path, $return_child))
                return $r;
        }
        return $r;
    }

    public function xpathArray($path, $return_child = false)
    {
        if (is_array($path))
            return $this->xpathArrayMulty($path, $return_child);

        $res = $this->xpath->query($path);
        $return = array();
        if ($res && $res->length > 0)
        {
            foreach ($res as $tag)
            {
                if ($return_child)
                    $return[] = $this->xpathReturnChild($tag);
                else
                    $return[] = trim(strip_tags($tag->nodeValue));
            }
        }
        return $return;
    }

    public function xpathArrayMulty(array $paths, $return_child = false)
    {
        foreach ($paths as $path)
        {
            if ($r = $this->xpathArray($path, $return_child))
                return $r;
        }
        return $r;
    }

    protected function xpathReturnChild($tag)
    {
        $innerHTML = '';
        $children = $tag->childNodes;
        foreach ($children as $child)
        {
            $tmp_doc = new \DOMDocument();
            $tmp_doc->appendChild($tmp_doc->importNode($child, true));
            $innerHTML .= $tmp_doc->saveHTML();
        }
        return trim($innerHTML);
    }

}
