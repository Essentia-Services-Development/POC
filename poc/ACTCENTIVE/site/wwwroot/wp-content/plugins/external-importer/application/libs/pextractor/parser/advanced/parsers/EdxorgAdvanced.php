<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * EdxorgAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class EdxorgAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[@class='discovery-card-inner-wrapper']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseTitle()
    {
        $paths = array(
            ".//div[@class='program']//div[@class='title']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        $description = '';

        if ($pieces = $this->xpathArray(".//div[contains(@class, 'program-body')]//ul//li"))
            $description .= '<h3>What you will learn</h3><ul><li>' . join('</li><li>', $pieces) . '</li></ul>';

        if ($d = $this->xpathScalar(".//div[contains(@class, 'program-body')]//div[@class='overview-info']", true))
            $description .= '<h3>Program Overview</h3>' . $d;

        $titles = $this->xpathArray(".//div[contains(@class, 'program-body')]//ol[@class='pathway']//div[contains(@class, 'collapsible-title')]");
        $bodies = $this->xpathArray(".//div[contains(@class, 'program-body')]//ol[@class='pathway']//div[contains(@class, 'collapsible-body')]//*[contains(@class, '-3')]", true);
        if ($titles && count($titles) == count($bodies))
        {
            $description .= '<h3>Courses in this program</h3>';
            foreach ($titles as $i => $title)
            {
                $description .= '<h4>' . $title . '</h4>';
                
                if ($i == count($title) - 1)
                    $description .= '<ul>' . $bodies[$i] . '</ul>';
                else
                    $description .= '<p>' . $bodies[$i] . '</p>';
            }
        }

        if ($description)
            return $description;
        
        if ($d = $this->xpathScalar(".//div[@class='course-description']", true))
            $description .= '<h3>About this course</h3>' . $d;
        
        if ($pieces = $this->xpathArray(".//div[@class='course-description d-flex flex-column']//ul//li"))
            $description .= '<h3>What you will learn</h3><ul><li>' . join('</li><li>', $pieces) . '</li></ul>';
        
        return $description;
    }

    public function parsePrice()
    {
        $paths = array(
            ".//div[@class='details']//div[@class='main d-flex flex-wrap']/text()",
            ".//div[@class='details']//div[@class='main d-flex flex-wrap']/span/text()",
            ".//div[@class='program-price d-flex flex-wrap']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='details']//div[@class='font-weight-normal']//s",
        );

        return $this->xpathScalar($paths);

    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//div[@class='partner']//img/@alt",
        );

        return $this->xpathScalar($paths);
    }


    public function parseCategoryPath()
    {
        $paths = array(
            ".//ol[@class='breadcrumb-list list-inline']//li[@class='breadcrumb-item']/a",
        );

        if ($categs = $this->xpathArray($paths))
        {
            array_shift($categs);
            return $categs;
        }
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//ul[@class='list-group list-group-flush w-100']//div[@class='col d-flex']//span",
                'value' => ".//ul[@class='list-group list-group-flush w-100']//*[@class='col']",
            ),
        );
    }


}
