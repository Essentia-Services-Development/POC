<?php
namespace Rehub\Gutenberg;

defined('ABSPATH') OR exit;

final class Init {

    public function __construct(){
        add_filter('block_categories_all', array($this,'block_categories_filter'), 10, 2);

        new Assets;
        new Blocks\Box;
        new Blocks\TitleBox;
        new Blocks\Heading;
        new Blocks\PostOfferbox;
        new Blocks\Offerbox;
        new Blocks\ReviewBox;
        new Blocks\ConsPros;
        new Blocks\Accordion;
        new Blocks\PostOfferListing;
        new Blocks\OfferListing;
        new Blocks\WCList;
        new Blocks\VersusTable;
        new Blocks\WCBox;
        new Blocks\Itinerary;
        new Blocks\Slider;
        new Blocks\PrettyList;
        new Blocks\PromoBox;
        new Blocks\ReviewHeading;
        new Blocks\ColorHeading;
        new Blocks\ComparisonTable;
        new Blocks\ComparisonItem;
        new Blocks\Howto;
        new Blocks\OfferListingFull;
        new Blocks\Colortitlebox;
        new Blocks\Countdown;
        new Blocks\Video;
        new Blocks\Toc;
        new Blocks\Metaget;
        new Blocks\Postelement;
        new Blocks\AdvancedListing;
        new Blocks\ContentToggler;
        new Blocks\WCQuery;
        new Blocks\ColoredPostGrid;
        new Blocks\DealGrid;
        new Blocks\DealList;
        new Blocks\SimpleList;
        new Blocks\WCDeal;
        new Blocks\NewsDirectoryList;
        new Blocks\NewsBlock;
        new Blocks\WooFeaturedSection;
        new Blocks\FeaturedSection;
        new Blocks\Scorebox;
        new Blocks\Popupbutton;
        new Blocks\TaxArchive; 
        new Blocks\Searchbox;
        new Blocks\ContentEgg;
        new Blocks\WooCompareBars;
        new Blocks\Wooday;
        new REST;
    }

    public function block_categories_filter($categories, $post){
        array_splice($categories, 3, 0, array(
            array(
                'slug'  => 'helpler-modules',
                'title' => __('Rehub Helper Modules', 'rehub-framework'),
            )
        ));

        return $categories;
    }
}