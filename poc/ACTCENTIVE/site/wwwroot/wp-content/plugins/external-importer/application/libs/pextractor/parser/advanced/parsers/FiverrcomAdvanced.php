<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * FiverrcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class FiverrcomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        if (preg_match_all('~,"gig_url":"(.+?)"~', $this->html, $matches))
            return $matches[1];
    }

    public function parsePagination()
    {
        if (!preg_match('~"page_size":48,"total":(\d+)~', $this->html, $matches))
            return array();

        $total = ceil($matches[1] / 48);
        if ($total > 100)
            $total = 100;

        $urls = array();
        for ($i = 1; $i <= $total; $i++)
        {
            $urls[] = \add_query_arg('page', $i, $this->getUrl());
        }

        return $urls;
    }

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@class='text-display-3']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        $d = $this->xpathScalar(".//div[@class='description-content']", true);

        $questions = $this->xpathArray(".//div[@class='faq-collapsable is-collapsed']//p[@class='question']");
        $answers = $this->xpathArray(".//div[@class='faq-collapsable is-collapsed']//p[@class='answer']");

        if ($questions && count($questions) == count($answers))
        {
            $d .= '<h2>FAQ</h2>';
            foreach ($questions as $i => $question)
            {
                $d .= '<strong>' . $question . '</strong>';
                $d .= '<p>' . $answers[$i] . '</p>';
            }
        }

        return $d;
    }

    public function parsePrice()
    {
        $paths = array(
            ".//div[@class='package-content']//h3//span[@class='price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//div[@class='thumbs-container']//img/@src");
        foreach ($results as $img)
        {
            $img = str_replace('/image/upload/t_gig_pdf_thumb_ver3,f_jpg/', '/image/upload/t_gig_pdf_gallery_view_ver4,f_jpg/', $img);
            $img = str_replace('/t_thumbnail3_3,q_auto,f_auto/', '/t_main1,q_auto,f_auto,q_auto,f_auto/', $img);
            $img = str_replace('/t_thumbnail3_3/', '/t_main1,q_auto,f_auto/', $img);
            $img = str_replace('/t_delivery_thumb,q_auto,f_auto/', '/t_main1,q_auto,f_auto,q_auto,f_auto/', $img);
            $img = str_replace('/t_gig_pdf_thumb_ver3,f_jpg/', '/t_gig_pdf_gallery_view_ver4,f_jpg/', $img);            
            $img = str_replace(' ', '%20', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//span[@class='user-status']/a",
        );

        return $this->xpathScalar($paths);
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//div[@class='gig-overview']//ul/li/span/a",
        );

        return $this->xpathArray($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//ul[@class='user-stats']//li/text()",
                'value' => ".//ul[@class='user-stats']//li/strong",
            ),
        );
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//ul[@class='review-list']//li/div[@class='review-description']//p[@class='text-body-2']",
                'rating' => ".//ul[@class='review-list']//li/header//span[@class='total-rating-out-five'][1]",
                'author' => ".//ul[@class='review-list']//li/header//h5[1]",
            ),
        );
    }


}
