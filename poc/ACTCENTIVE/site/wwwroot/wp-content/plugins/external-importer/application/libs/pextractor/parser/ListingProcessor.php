<?php

namespace ExternalImporter\application\libs\pextractor\parser;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Listing;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * ListingProcessor class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class ListingProcessor {

    public static function prepare(Listing $listing, $url)
    {
        if (!$listing->links)
            $listing->links = array();
        if (!$listing->pagination)
            $listing->pagination = array();
        $listing->links = self::prepareLinks($listing->links, $url);
        $listing->pagination = self::prepareLinks($listing->pagination, $url);

        natsort($listing->pagination);
        return $listing;
    }

    public static function prepareLinks(array $links, $url)
    {
        $links = array_unique($links);
        $links = array_filter($links);
        $links = array_values($links);
        $links = ExtractorHelper::resolveUrls($links, $url);
        $links = array_diff($links, array($url));
        $links = ExtractorHelper::filterForeignDomains($links, $url);
        return $links;
    }

    public static function mergeListings(Listing $listing1, Listing $listing2)
    {
        foreach ($listing2->links as $i => $link)
        {
            if (!in_array($link, $listing1->links))
                $listing1->links[] = $link;
        }

        foreach ($listing2->pagination as $i => $link)
        {
            if (!in_array($link, $listing1->pagination))
                $listing1->pagination[] = $link;
        }

        return $listing1;
    }

}
