<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * HomedepotcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class HomedepotcomAdvanced extends AdvancedParser {

    protected $_total;
    protected $_product = array();

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();
        $httpOptions['user-agent'] = 'ia_archiver';
        return $httpOptions;
    }

    protected function preParseProduct()
    {
        $this->_getProduct();
        return parent::preParseProduct();
    }

    protected function _getProduct()
    {
        $path = parse_url($this->getUrl(), PHP_URL_PATH);

        $parts = explode('/', $path);
        $id = end($parts);

        $request_url = 'https://www.homedepot.com/federation-gateway/graphql?opname=productClientOnlyProduct';
        $response = \wp_remote_post($request_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Api-Cookies' => '{"x-user-id":"9422f6ed-2f50-094d-28ed-5d9c19a77a5e"}',
                'X-current-url' => parse_url($this->getUrl(),  PHP_URL_PATH),
                'x-debug' => 'false',
                'X-Experience-Name' => 'general-merchandise',
                'x-hd-dc' => 'origin',
                'Accept' => '*/*',
            ),
            'body' => '{"operationName":"productClientOnlyProduct","variables":{"skipSpecificationGroup":false,"skipSubscribeAndSave":false,"skipKPF":false,"skipInstallServices":true,"itemId":"' . $id . '","storeId":"910","zipCode":"07094"},"query":"query productClientOnlyProduct($storeId: String, $zipCode: String, $quantity: Int, $itemId: String!, $dataSource: String, $loyaltyMembershipInput: LoyaltyMembershipInput, $skipSpecificationGroup: Boolean = false, $skipSubscribeAndSave: Boolean = false, $skipKPF: Boolean = false, $skipInstallServices: Boolean = true) {\n  product(itemId: $itemId, dataSource: $dataSource, loyaltyMembershipInput: $loyaltyMembershipInput) {\n    fulfillment(storeId: $storeId, zipCode: $zipCode, quantity: $quantity) {\n      backordered\n      fulfillmentOptions {\n        type\n        services {\n          type\n          locations {\n            isAnchor\n            locationId\n            inventory {\n              isOutOfStock\n              quantity\n              isInStock\n              isLimitedQuantity\n              isUnavailable\n              maxAllowedBopisQty\n              minAllowedBopisQty\n              __typename\n            }\n            curbsidePickupFlag\n            isBuyInStoreCheckNearBy\n            distance\n            storeName\n            state\n            type\n            storePhone\n            __typename\n          }\n          hasFreeShipping\n          freeDeliveryThreshold\n          optimalFulfillment\n          deliveryTimeline\n          deliveryDates {\n            startDate\n            endDate\n            __typename\n          }\n          deliveryCharge\n          dynamicEta {\n            hours\n            minutes\n            __typename\n          }\n          totalCharge\n          __typename\n        }\n        fulfillable\n        __typename\n      }\n      backorderedShipDate\n      bossExcludedShipStates\n      excludedShipStates\n      seasonStatusEligible\n      anchorStoreStatus\n      anchorStoreStatusType\n      sthExcludedShipState\n      bossExcludedShipState\n      onlineStoreStatus\n      onlineStoreStatusType\n      inStoreAssemblyEligible\n      __typename\n    }\n    info {\n      dotComColorEligible\n      hidePrice\n      ecoRebate\n      quantityLimit\n      sskMin\n      sskMax\n      unitOfMeasureCoverage\n      wasMaxPriceRange\n      wasMinPriceRange\n      fiscalYear\n      productDepartment\n      classNumber\n      forProfessionalUseOnly\n      globalCustomConfigurator {\n        customButtonText\n        customDescription\n        customExperience\n        customExperienceUrl\n        customTitle\n        __typename\n      }\n      paintBrand\n      movingCalculatorEligible\n      label\n      prop65Warning\n      returnable\n      hasSubscription\n      isBuryProduct\n      isSponsored\n      isGenericProduct\n      isLiveGoodsProduct\n      sponsoredBeacon {\n        onClickBeacon\n        onViewBeacon\n        __typename\n      }\n      sponsoredMetadata {\n        campaignId\n        placementId\n        slotId\n        __typename\n      }\n      productSubType {\n        name\n        link\n        __typename\n      }\n      categoryHierarchy\n      samplesAvailable\n      customerSignal {\n        previouslyPurchased\n        __typename\n      }\n      productDepartmentId\n      augmentedReality\n      swatches {\n        isSelected\n        itemId\n        label\n        swatchImgUrl\n        url\n        value\n        __typename\n      }\n      totalNumberOfOptions\n      recommendationFlags {\n        visualNavigation\n        pipCollections\n        packages\n        ACC\n        __typename\n      }\n      pipCalculator {\n        toggle\n        coverageUnits\n        display\n        publisher\n        __typename\n      }\n      replacementOMSID\n      minimumOrderQuantity\n      projectCalculatorEligible\n      subClassNumber\n      calculatorType\n      protectionPlanSku\n      hasServiceAddOns\n      consultationType\n      __typename\n    }\n    identifiers {\n      skuClassification\n      canonicalUrl\n      brandName\n      itemId\n      modelNumber\n      productLabel\n      storeSkuNumber\n      upcGtin13\n      specialOrderSku\n      toolRentalSkuNumber\n      rentalCategory\n      rentalSubCategory\n      upc\n      productType\n      isSuperSku\n      parentId\n      roomVOEnabled\n      sampleId\n      __typename\n    }\n    itemId\n    dataSources\n    availabilityType {\n      discontinued\n      status\n      type\n      buyable\n      __typename\n    }\n    details {\n      description\n      collection {\n        url\n        collectionId\n        name\n        __typename\n      }\n      highlights\n      descriptiveAttributes {\n        name\n        value\n        bulleted\n        sequence\n        __typename\n      }\n      additionalResources {\n        infoAndGuides {\n          name\n          url\n          __typename\n        }\n        installationAndRentals {\n          contentType\n          name\n          url\n          __typename\n        }\n        diyProjects {\n          contentType\n          name\n          url\n          __typename\n        }\n        __typename\n      }\n      installation {\n        leadGenUrl\n        __typename\n      }\n      __typename\n    }\n    media {\n      images {\n        url\n        type\n        subType\n        sizes\n        __typename\n      }\n      video {\n        shortDescription\n        thumbnail\n        url\n        videoStill\n        link {\n          text\n          url\n          __typename\n        }\n        title\n        type\n        videoId\n        longDescription\n        __typename\n      }\n      threeSixty {\n        id\n        url\n        __typename\n      }\n      augmentedRealityLink {\n        usdz\n        image\n        __typename\n      }\n      richContent {\n        content\n        displayMode\n        richContentSource\n        salsifyRichContent\n        __typename\n      }\n      __typename\n    }\n    pricing(storeId: $storeId) {\n      promotion {\n        dates {\n          end\n          start\n          __typename\n        }\n        type\n        description {\n          shortDesc\n          longDesc\n          __typename\n        }\n        dollarOff\n        percentageOff\n        savingsCenter\n        savingsCenterPromos\n        specialBuySavings\n        specialBuyDollarOff\n        specialBuyPercentageOff\n        experienceTag\n        subExperienceTag\n        itemList\n        reward {\n          tiers {\n            minPurchaseAmount\n            minPurchaseQuantity\n            rewardPercent\n            rewardAmountPerOrder\n            rewardAmountPerItem\n            rewardFixedPrice\n            __typename\n          }\n          __typename\n        }\n        nvalues\n        brandRefinementId\n        __typename\n      }\n      value\n      alternatePriceDisplay\n      alternate {\n        bulk {\n          pricePerUnit\n          thresholdQuantity\n          value\n          __typename\n        }\n        unit {\n          caseUnitOfMeasure\n          unitsOriginalPrice\n          unitsPerCase\n          value\n          __typename\n        }\n        __typename\n      }\n      original\n      mapAboveOriginalPrice\n      message\n      preferredPriceFlag\n      specialBuy\n      unitOfMeasure\n      conditionalPromotions {\n        dates {\n          start\n          end\n          __typename\n        }\n        description {\n          shortDesc\n          longDesc\n          __typename\n        }\n        experienceTag\n        subExperienceTag\n        eligibilityCriteria {\n          itemGroup\n          minPurchaseAmount\n          minPurchaseQuantity\n          relatedSkusCount\n          omsSkus\n          __typename\n        }\n        reward {\n          tiers {\n            minPurchaseAmount\n            minPurchaseQuantity\n            rewardPercent\n            rewardAmountPerOrder\n            rewardAmountPerItem\n            rewardFixedPrice\n            __typename\n          }\n          __typename\n        }\n        nvalues\n        brandRefinementId\n        __typename\n      }\n      __typename\n    }\n    reviews {\n      ratingsReviews {\n        averageRating\n        totalReviews\n        __typename\n      }\n      __typename\n    }\n    seo {\n      seoKeywords\n      seoDescription\n      __typename\n    }\n    specificationGroup @skip(if: $skipSpecificationGroup) {\n      specifications {\n        specName\n        specValue\n        __typename\n      }\n      specTitle\n      __typename\n    }\n    taxonomy {\n      breadCrumbs {\n        label\n        url\n        browseUrl\n        creativeIconUrl\n        deselectUrl\n        dimensionName\n        refinementKey\n        __typename\n      }\n      brandLinkUrl\n      __typename\n    }\n    favoriteDetail {\n      count\n      __typename\n    }\n    sizeAndFitDetail {\n      attributeGroups {\n        attributes {\n          attributeName\n          dimensions\n          __typename\n        }\n        dimensionLabel\n        productType\n        __typename\n      }\n      __typename\n    }\n    subscription @skip(if: $skipSubscribeAndSave) {\n      defaultfrequency\n      discountPercentage\n      subscriptionEnabled\n      __typename\n    }\n    badges(storeId: $storeId) {\n      label\n      name\n      color\n      creativeImageUrl\n      endDate\n      message\n      timerDuration\n      timer {\n        timeBombThreshold\n        daysLeftThreshold\n        dateDisplayThreshold\n        message\n        __typename\n      }\n      __typename\n    }\n    keyProductFeatures @skip(if: $skipKPF) {\n      keyProductFeaturesItems {\n        features {\n          name\n          refinementId\n          refinementUrl\n          value\n          __typename\n        }\n        __typename\n      }\n      __typename\n    }\n    dataSource\n    installServices(storeId: $storeId, zipCode: $zipCode) @skip(if: $skipInstallServices) {\n      scheduleAMeasure\n      gccCarpetDesignAndOrderEligible\n      __typename\n    }\n    projectDetails {\n      projectId\n      __typename\n    }\n    seoDescription\n    __typename\n  }\n}\n"}',
            'method' => 'POST'
        ));
        if (\is_wp_error($response))
            return false;

        if (!$body = \wp_remote_retrieve_body($response))
            return false;

        $result = json_decode($body, true);
        if (!$result || !isset($result['data']['product']))
            return false;

        $this->_product = $result['data']['product'];
        return $this->_product;
    }

    public function parseLinks()
    {
        if ($urls = $this->parseLinksCateg())
            return $urls;

        $path = array(
            ".//div[@class='plp-pod__image']/a/@href",
            ".//a[@class='product-pod--ie-fix']/@href",
        );

        return $this->xpathArray($path);
    }

    protected function parseLinksCateg()
    {
        if (!preg_match('~/N-([0-9a-zA-Z]+)~', $this->getUrl(), $matches))
            return array();

        $pagenum = 0;
        if ($query = parse_url($this->getUrl(), PHP_URL_QUERY))
        {
            parse_str($query, $arr);
            if (isset($arr['Nao']))
                $pagenum = $arr['Nao'];
        }

        $request_url = 'https://www.homedepot.com/federation-gateway/graphql?opname=searchModel';
        $response = \wp_remote_post($request_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Api-Cookies' => '{"x-user-id":"9422f6ed-2f50-094d-28ed-5d9c19a77a5e"}',
                'X-current-url' => parse_url($this->getUrl(),  PHP_URL_PATH),
                'x-debug' => 'false',
                'X-Experience-Name' => 'general-merchandise',
                'x-hd-dc' => 'origin',
                'Accept' => '*/*',
            ),
            'body' => '{"operationName":"searchModel","variables":{"skipInstallServices":false,"skipKPF":false,"skipSpecificationGroup":false,"skipSubscribeAndSave":false,"storefilter":"ALL","channel":"DESKTOP","additionalSearchParams":{"sponsored":true,"mcvisId":"86828292244570686081800766829784248287","deliveryZip":"07094"},"filter":{},"navParam":"' . $matches[1] . '","orderBy":{"field":"TOP_SELLERS","order":"ASC"},"pageSize":24,"startIndex":' . $pagenum . ',"storeId":"910"},"query":"query searchModel($storeId: String, $zipCode: String, $skipInstallServices: Boolean = true, $startIndex: Int, $pageSize: Int, $orderBy: ProductSort, $filter: ProductFilter, $skipKPF: Boolean = false, $skipSpecificationGroup: Boolean = false, $skipSubscribeAndSave: Boolean = false, $keyword: String, $navParam: String, $storefilter: StoreFilter = ALL, $itemIds: [String], $channel: Channel = DESKTOP, $additionalSearchParams: AdditionalParams, $loyaltyMembershipInput: LoyaltyMembershipInput) {\n  searchModel(keyword: $keyword, navParam: $navParam, storefilter: $storefilter, storeId: $storeId, itemIds: $itemIds, channel: $channel, additionalSearchParams: $additionalSearchParams, loyaltyMembershipInput: $loyaltyMembershipInput) {\n    metadata {\n      hasPLPBanner\n      categoryID\n      analytics {\n        semanticTokens\n        dynamicLCA\n        __typename\n      }\n      canonicalUrl\n      searchRedirect\n      clearAllRefinementsURL\n      contentType\n      isStoreDisplay\n      productCount {\n        inStore\n        __typename\n      }\n      stores {\n        storeId\n        storeName\n        address {\n          postalCode\n          __typename\n        }\n        nearByStores {\n          storeId\n          storeName\n          distance\n          address {\n            postalCode\n            __typename\n          }\n          __typename\n        }\n        __typename\n      }\n      __typename\n    }\n    products(startIndex: $startIndex, pageSize: $pageSize, orderBy: $orderBy, filter: $filter) {\n      identifiers {\n        storeSkuNumber\n        canonicalUrl\n        brandName\n        itemId\n        productLabel\n        modelNumber\n        productType\n        parentId\n        isSuperSku\n        __typename\n      }\n      installServices(storeId: $storeId, zipCode: $zipCode) @skip(if: $skipInstallServices) {\n        scheduleAMeasure\n        gccCarpetDesignAndOrderEligible\n        __typename\n      }\n      itemId\n      dataSources\n      media {\n        images {\n          url\n          type\n          subType\n          sizes\n          __typename\n        }\n        __typename\n      }\n      pricing(storeId: $storeId) {\n        value\n        alternatePriceDisplay\n        alternate {\n          bulk {\n            pricePerUnit\n            thresholdQuantity\n            value\n            __typename\n          }\n          unit {\n            caseUnitOfMeasure\n            unitsOriginalPrice\n            unitsPerCase\n            value\n            __typename\n          }\n          __typename\n        }\n        original\n        mapAboveOriginalPrice\n        message\n        preferredPriceFlag\n        promotion {\n          type\n          description {\n            shortDesc\n            longDesc\n            __typename\n          }\n          dollarOff\n          percentageOff\n          savingsCenter\n          savingsCenterPromos\n          specialBuySavings\n          specialBuyDollarOff\n          specialBuyPercentageOff\n          dates {\n            start\n            end\n            __typename\n          }\n          __typename\n        }\n        specialBuy\n        unitOfMeasure\n        __typename\n      }\n      reviews {\n        ratingsReviews {\n          averageRating\n          totalReviews\n          __typename\n        }\n        __typename\n      }\n      availabilityType {\n        discontinued\n        type\n        __typename\n      }\n      badges(storeId: $storeId) {\n        name\n        __typename\n      }\n      details {\n        collection {\n          collectionId\n          name\n          url\n          __typename\n        }\n        highlights\n        __typename\n      }\n      favoriteDetail {\n        count\n        __typename\n      }\n      fulfillment(storeId: $storeId, zipCode: $zipCode) {\n        backordered\n        backorderedShipDate\n        bossExcludedShipStates\n        excludedShipStates\n        seasonStatusEligible\n        fulfillmentOptions {\n          type\n          fulfillable\n          services {\n            type\n            hasFreeShipping\n            freeDeliveryThreshold\n            locations {\n              curbsidePickupFlag\n              isBuyInStoreCheckNearBy\n              distance\n              inventory {\n                isOutOfStock\n                isInStock\n                isLimitedQuantity\n                isUnavailable\n                quantity\n                maxAllowedBopisQty\n                minAllowedBopisQty\n                __typename\n              }\n              isAnchor\n              locationId\n              storeName\n              state\n              type\n              __typename\n            }\n            __typename\n          }\n          __typename\n        }\n        __typename\n      }\n      info {\n        hasSubscription\n        isBuryProduct\n        isSponsored\n        isGenericProduct\n        isLiveGoodsProduct\n        sponsoredBeacon {\n          onClickBeacon\n          onViewBeacon\n          __typename\n        }\n        sponsoredMetadata {\n          campaignId\n          placementId\n          slotId\n          __typename\n        }\n        globalCustomConfigurator {\n          customExperience\n          __typename\n        }\n        returnable\n        hidePrice\n        productSubType {\n          name\n          link\n          __typename\n        }\n        categoryHierarchy\n        samplesAvailable\n        customerSignal {\n          previouslyPurchased\n          __typename\n        }\n        productDepartmentId\n        productDepartment\n        augmentedReality\n        ecoRebate\n        quantityLimit\n        sskMin\n        sskMax\n        unitOfMeasureCoverage\n        wasMaxPriceRange\n        wasMinPriceRange\n        swatches {\n          isSelected\n          itemId\n          label\n          swatchImgUrl\n          url\n          value\n          __typename\n        }\n        totalNumberOfOptions\n        paintBrand\n        dotComColorEligible\n        __typename\n      }\n      keyProductFeatures @skip(if: $skipKPF) {\n        keyProductFeaturesItems {\n          features {\n            name\n            refinementId\n            refinementUrl\n            value\n            __typename\n          }\n          __typename\n        }\n        __typename\n      }\n      specificationGroup @skip(if: $skipSpecificationGroup) {\n        specifications {\n          specName\n          specValue\n          __typename\n        }\n        specTitle\n        __typename\n      }\n      subscription @skip(if: $skipSubscribeAndSave) {\n        defaultfrequency\n        discountPercentage\n        subscriptionEnabled\n        __typename\n      }\n      sizeAndFitDetail {\n        attributeGroups {\n          attributes {\n            attributeName\n            dimensions\n            __typename\n          }\n          dimensionLabel\n          productType\n          __typename\n        }\n        __typename\n      }\n      __typename\n    }\n    id\n    searchReport {\n      totalProducts\n      didYouMean\n      correctedKeyword\n      keyword\n      pageSize\n      searchUrl\n      sortBy\n      sortOrder\n      startIndex\n      __typename\n    }\n    relatedResults {\n      universalSearch {\n        title\n        __typename\n      }\n      relatedServices {\n        label\n        __typename\n      }\n      visualNavs {\n        label\n        imageId\n        webUrl\n        categoryId\n        imageURL\n        __typename\n      }\n      visualNavContainsEvents\n      relatedKeywords {\n        keyword\n        __typename\n      }\n      __typename\n    }\n    taxonomy {\n      brandLinkUrl\n      breadCrumbs {\n        browseUrl\n        creativeIconUrl\n        deselectUrl\n        dimensionId\n        dimensionName\n        label\n        refinementKey\n        url\n        __typename\n      }\n      __typename\n    }\n    templates\n    partialTemplates\n    dimensions {\n      label\n      refinements {\n        refinementKey\n        label\n        recordCount\n        selected\n        imgUrl\n        url\n        nestedRefinements {\n          label\n          url\n          recordCount\n          refinementKey\n          __typename\n        }\n        __typename\n      }\n      collapse\n      dimensionId\n      isVisualNav\n      isVisualDimension\n      isNumericFilter\n      nestedRefinementsLimit\n      visualNavSequence\n      __typename\n    }\n    orangeGraph {\n      universalSearchArray {\n        pods {\n          title\n          description\n          imageUrl\n          link\n          recordType\n          __typename\n        }\n        info {\n          title\n          __typename\n        }\n        __typename\n      }\n      productTypes\n      intents\n      orderNumber\n      __typename\n    }\n    appliedDimensions {\n      label\n      refinements {\n        label\n        refinementKey\n        url\n        __typename\n      }\n      isNumericFilter\n      __typename\n    }\n    __typename\n  }\n}\n"}',
            'method' => 'POST'
        ));

        if (\is_wp_error($response))
            return array();

        if (!$body = \wp_remote_retrieve_body($response))
            return array();

        $result = json_decode($body, true);

        if (!$result || !isset($result['data']['searchModel']['products']))
            return array();

        $urls = array();
        foreach ($result['data']['searchModel']['products'] as $r)
        {
            $urls[] = $r['identifiers']['canonicalUrl'];
        }

        //pagination
        if (isset($result['data']['searchModel']['searchReport']['totalProducts']))
        {
            $this->_total = (int) $result['data']['searchModel']['searchReport']['totalProducts'];
        }

        return $urls;
    }

    public function parsePagination()
    {
        if ($this->_total)
        {
            $urls[] = array();
            for ($i = 1; $i <= ceil($this->_total / 24); $i++)
            {
                $urls[] = \add_query_arg('Nao', $i * 24, $this->getUrl());
            }

            return $urls;
        }

        $path = array(
            ".//ul[@class='hd-pagination__wrapper pagination-margin']/li/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='pricingRegular']//span[@class='pStrikeThru']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseFeatures()
    {
        if (!$this->_product || !isset($this->_product['specificationGroup']))
            return array();

        $features = array();
        foreach ($this->_product['specificationGroup'] as $group)
        {
            foreach ($group['specifications'] as $p)
            {
                $feature = array();
                $feature['name'] = \sanitize_text_field($p['specName']);
                $feature['value'] = \sanitize_text_field($p['specValue']);
                $features[] = $feature;
            }
        }

        $bars = $this->xpathArray(".//div[contains(@class, 'product-info-bar')]//h2");
        foreach ($bars as $bar)
        {
            $parts = explode('#', $bar);
            if (count($parts) == 2)
            {
                $features[] = array(
                    'name' => $parts[0],
                    'value' => $parts[1],
                );
            }
        }

        return $features;
    }

    public function parseReviews()
    {
        if (!preg_match('~\/(\d+)~', $this->getUrl(), $matches))
            return array();

        $url = 'https://www.homedepot.com/ReviewServices/reviews/v1/product/' . urlencode($matches[1]) . '?key=x5w9jA8tWVGcqRhujrHTvjRwQfH8MkFc&startindex=1&pagesize=30&recfirstpage=10&stats=true&sort=photoreview';
        $response = $this->getRemoteJson($url);

        if (!$response || !isset($response['Results']))
            return array();

        $results = array();
        foreach ($response['Results'] as $r)
        {
            $review = array();
            if (!isset($r['ReviewText']))
                continue;

            $review['review'] = $r['ReviewText'];

            if (isset($r['RatingRange']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['RatingRange']);

            if (isset($r['UserNickname']))
                $review['author'] = $r['UserNickname'];

            if (isset($r['LastModeratedTime']))
                $review['date'] = strtotime($r['LastModeratedTime']);

            $results[] = $review;
        }
        return $results;
    }

    public function parseCurrencyCode()
    {
        return 'USD';
    }

    public function parseDescription()
    {

        if (!$this->_product)
            return '';

        $description = '';
        if (isset($this->_product['details']['description']))
        {
            $description .= $this->_product['details']['description'];
        }

        foreach ($this->_product['details']['descriptiveAttributes'] as $h)
        {
            if (!strstr($h['value'], 'href='))
                $highlights[] = $h['value'];
        }

        $description .= '<ul><li>' . join('</li><li>', $highlights) . '</li></ul>';

        return $description;
    }

    public function parseShortDescription()
    {
        if (!$this->_product)
            return '';

        $description = '';
        $highlights = array();
        foreach ($this->_product['details']['highlights'] as $h)
        {
            $highlights[] = $h;
        }

        return '<ul><li>' . join('</li><li>', $highlights) . '</li></ul>';
    }

    public function afterParseFix(Product $product)
    {
        $product->title = trim(str_replace('– The Home Depot', '', $product->title));
        $product->image = str_replace('_100.jpg', '_600.jpg', $product->image);
        foreach ($product->images as $i => $img)
        {
            $product->images[$i] = str_replace('_100.jpg', '_600.jpg', $img);
        }

        if (strstr($product->manufacturer, '}'))
            $product->manufacturer = '';


        return $product;
    }

}
