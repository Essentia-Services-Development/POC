<?php

namespace YusufKandemir\MicrodataParser;

class XpathParser {
	protected $dom;
	
	/**
	 * MicrodataParser constructor.
	 *
	 * @param MicrodataDOMDocument $dom
	 * @param callable|null $absoluteUriHandler Can be set later with MicrodataParser::setAbsoluteUriHandler()
	 *
	 * @see MicrodataParser::$absoluteUriHandler
	 */
	public function __construct($dom, $absoluteUriHandler = null) {
		$dom->registerNodeClass(\DOMElement::class, MicrodataDOMElement::class);
		$this->dom = $dom;
	}
	
	/**
	 * Extracts and converts microdata to json using \json_encode()
	 *
	 * @param int $options
	 * @param int $depth
	 *
	 * @return false|string
	 * @see \json_encode() to description of parameters and return values
	 *
	 */
	public function toJSON($xpatharray) {
		return json_encode($this->extractData( $xpatharray));
	}
	
	/**
	 * @return \stdClass
	 */
	protected function extractData($DataArray) {
		
		$DataParsed = [];
		
		foreach( $DataArray as $DataKey => $DataPath ){
			$pathes = explode('%DELIMITER%', $DataPath);
			$DataParsed[$DataKey] = $this->xpathScalar( $pathes, $DataKey );
		}

		$result = new \stdClass;
		$result->items = [];
		
		$item = new \stdClass;
		$item->type[] = 'https://schema.org/Product';
		
		$properties = new \stdClass;
		$properties->image[] = $DataParsed['image'];
		$properties->name[] = $DataParsed['name'];
		$properties->description[] = $DataParsed['description'];
		$properties->offers = [];
		
		$offer = new \stdClass;
		$offer->type[] = 'https://schema.org/Offer';
		
		$offer_properties = new \stdClass;
		$offer_properties->price[] = $DataParsed['price'];
		$offer_properties->priceCurrency[] = $DataParsed['priceCurrency'];
		
		$offer->properties = $offer_properties;
		$properties->offers[] = $offer;
		$item->properties = $properties;
		$result->items[] = $item;

		return $result;
	}
	
	/*  */
    public function xpathScalar($path, $DataKey) {
        if (is_array($path))
            return $this->xpathScalarMulty($path, $DataKey);

		$nodes = $this->dom->getItems($path);
	
        if ($nodes && $nodes->length > 0) {
			if($DataKey == 'image'){	
				if($nodes->item(0)->hasAttribute('data-old-hires'))
					return trim( $nodes->item(0)->getAttribute('data-old-hires') );
				return trim( $nodes->item(0)->getAttribute('src') );
			}
			elseif( $DataKey == 'price' && $nodes->item(0)->hasAttribute('data-asin-price') ){
				return $nodes->item(0)->getAttribute( 'data-asin-price' );
			}
			elseif( $DataKey == 'priceCurrency' && $nodes->item(0)->hasAttribute('data-asin-currency-code')){
				return $nodes->item(0)->getAttribute('data-asin-currency-code');
			}
            return trim( strip_tags( $nodes->item(0)->nodeValue ) );
        } else
            return null;
    }
	
	/*  */
    public function xpathScalarMulty(array $paths, $DataKey) {
        foreach ($paths as $path) {
            if ($result = $this->xpathScalar($path, $DataKey))
                return $result;
        }
        return $result;
    }
}