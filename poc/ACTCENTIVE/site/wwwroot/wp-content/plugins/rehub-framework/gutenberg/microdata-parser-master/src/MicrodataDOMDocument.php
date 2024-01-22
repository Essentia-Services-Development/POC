<?php

namespace YusufKandemir\MicrodataParser;

class MicrodataDOMDocument extends \DOMDocument {
	public $xpath;

	/**
	 * Get top-level items of the document
	 *
	 * @see https://www.w3.org/TR/2018/WD-microdata-20180426/#dfn-top-level-microdata-item
	 *
	 */
	public function getItems( $xpath = '' ) {
		if( $xpath )
			return $this->xpath->query( $xpath );
		return $this->xpath->query( '//*[@itemscope and not(@itemprop)]' );
	}

	/**
	 * {@inheritdoc}
	 * Also assigns $xpath with DOMXPath of freshly loaded DOMDocument
	 */
	public function loadHTML( $source, $options = 0 ) {
		$return = parent::loadHTML( $source, $options );

		$this->xpath = new \DOMXPath( $this );

		return $return;
	}

	/**
	 * {@inheritdoc}
	 * Also assigns $xpath with DOMXPath of freshly loaded DOMDocument
	 */
	public function loadHTMLFile( $filename, $options = 0 ) {
		$return = parent::loadHTMLFile( $filename, $options );

		$this->xpath = new \DOMXPath( $this );

		return $return;
	}
	
	/**
	 * {@inheritdoc}
	 * Also assigns $xpath with DOMXPath of freshly loaded DOMDocument
	 */
	public function loadPageHTML( $filename, $options = 0 ) {
		$args = array( 
			'timeout' => 30,
			'httpversion' => '1.0',
			'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36'
		);
			
		$response = wp_safe_remote_get( $filename, $args );
		$body = wp_remote_retrieve_body( $response );
		
		$return = parent::loadHTML( $body, $options );
	
		$this->xpath = new \DOMXPath( $this );

		return $return;
	}
}
