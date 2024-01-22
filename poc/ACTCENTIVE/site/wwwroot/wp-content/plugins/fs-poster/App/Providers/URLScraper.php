<?php

namespace FSPoster\App\Providers;

use DOMNode;
use Exception;
use DOMDocument;
use FSP_GuzzleHttp\Client;

class URLScraper
{
	private $url;
	private $title           = '';
	private $description     = '';
	private $image           = '';
	private $html            = NULL;
	private $priority        = 'og';
	private $domSearchResult = [
		'ogTitle'            => '',
		'ogDescription'      => '',
		'ogImage'            => '',
		'twitterTitle'       => '',
		'twitterDescription' => '',
		'twitterImage'       => ''
	];

	private function __construct ( $url )
	{
		$this->url = $url;
	}

	/**
	 * @param $url
	 * @param $priority ("og", "twitter")
	 *
	 * @return array
	 */
	public static function scrape ( $url, $priority = 'og' )
	{
		$scraper = new self( $url );
		$scraper->getHTML();
		$scraper->setPriority( $priority );

		if ( empty( $scraper->html ) )
		{
			return $scraper->getScrapeData();
		}

		$scraper->doDomSearch();
		$scraper->doRegexSearch();

		return $scraper->getScrapeData();
	}

	private function getScrapeData ()
	{
		if ( ! empty( $this->image ) && strpos( $this->image, 'http' ) !== 0 )
		{
			$url         = explode( '//', $this->url );
			$website     = explode( '/', $url[ 1 ] );
			$this->image = $url[ 0 ] . '//' . $website[ 0 ] . '/' . trim( $this->image, '/' );
		}

		return [
			'title'       => $this->title,
			'description' => $this->description,
			'image'       => $this->image
		];
	}

	private function getHTML ()
	{
		$c = new Client();
		try
		{
			$this->html = $c->get( $this->url, [
				'allow_redirects' => TRUE,
				'verify'          => FALSE,
				'headers'         => [
					'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36'
				]
			] )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			$this->html = '';
		}
	}

	private function doDomSearch ()
	{
		if ( ! class_exists( DOMDocument::class ) || ! class_exists( DOMNode::class ) )
		{
			return;
		}

		$doc = new DOMDocument();
		@$doc->loadHTML( "<meta http-equiv='Content-Type' content='charset=utf-8' />" . $this->html );
		$nl = $doc->getElementsByTagName( 'meta' );

		$properties = [ 'title', 'description', 'image' ];

		for ( $i = 0; $i < $nl->length; $i++ )
		{
			foreach ( $properties as $property )
			{
				if ( empty( $this->domSearchResult[ 'og' . ucfirst( $property ) ] ) && $nl[ $i ]->hasAttribute( 'property' ) && $nl[ $i ]->getAttribute( 'property' ) === ( 'og:' . $property ) )
				{
					$this->domSearchResult[ 'og' . ucfirst( $property ) ] = $nl[ $i ]->hasAttribute( 'content' ) ? $nl[ $i ]->getAttribute( 'content' ) : '';
				}

				if ( empty( $this->domSearchResult[ 'twitter' . ucfirst( $property ) ] ) && $nl[ $i ]->hasAttribute( 'name' ) && $nl[ $i ]->getAttribute( 'name' ) === ( 'twitter:' . $property ) )
				{
					$this->domSearchResult[ 'twitter' . ucfirst( $property ) ] = $nl[ $i ]->hasAttribute( 'content' ) ? $nl[ $i ]->getAttribute( 'content' ) : '';
				}
			}
		}

		$this->doAction( 'dom' );

	}

	private function doRegexSearch ()
	{
		$this->doAction( 'regex' );
	}

	/**
	 * @param $provider = 'regex' | 'dom'
	 *
	 * @return void
	 */
	private function doAction ( $provider )
	{
		$types  = $this->priority === 'twitter' ? [ 'twitter', 'og' ] : [ 'og', 'twitter' ];
		$fields = [ 'title', 'description', 'image' ];

		foreach ( $types as $type )
		{
			$attr = $type === 'og' ? 'property' : 'name';
			foreach ( $fields as $field )
			{
				if ( empty( $this->$field ) )
				{
					if ( $provider == 'dom' )
					{
						$this->$field = $this->domSearchResult[ $type . ucfirst( $field ) ];
					}
					else if ( $provider == 'regex' )
					{
						preg_match( '/<meta ' . $attr . '=\"' . $type . ':' . $field . '\" content=\"(.+?)\"/', $this->html, $match );

						if ( ! empty( $match[ 1 ] ) )
						{
							$this->$field = $match[ 1 ];
						}
					}
				}
			}
		}
	}

	private function setPriority ( $priority )
	{
		$this->priority = $priority;
	}
}