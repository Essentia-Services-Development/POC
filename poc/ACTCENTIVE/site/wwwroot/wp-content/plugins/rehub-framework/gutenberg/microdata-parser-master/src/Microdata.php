<?php

namespace YusufKandemir\MicrodataParser;

abstract class Microdata {
	/**
	 * Creates a MicrodataParser from HTML string
	 *
	 * @param string $html HTML string to be parsed
	 * @param string $documentURI DocumentURI to be used in absolutizing URIs
	 *
	 * @return MicrodataParser
	 */
	public static function fromHTML( $html, $documentURI = '' ) {
		$dom = new MicrodataDOMDocument;
		$dom->loadHTML( $html, LIBXML_NOERROR );
		$dom->documentURI = $documentURI;

		return new MicrodataParser( $dom );
	}

	/**
	 * Creates a MicrodataParser from a HTML file
	 *
	 * @param string $filename Path to the file to be parsed
	 * @param string $documentURI DocumentURI to be used in absolutizing URIs
	 *
	 * @return MicrodataParser
	 */
	public static function fromHTMLFile( $filename, $documentURI = '' ) {
		$dom = new MicrodataDOMDocument;
		$dom->loadHTMLFile( $filename, LIBXML_NOERROR );
		$dom->documentURI = $documentURI;

		return new MicrodataParser( $dom );
	}

	/**
	 * Creates a MicrodataParser from a DOMDocument instance.
	 * If you have MicrodataDOMDocument then instantiate MicrodataParser class directly to avoid conversion.
	 *
	 * @param \DOMDocument $domDocument DOMDocument to be parsed.
	 * Needs to have documentURI property to be used in absolutizing URIs if wanted.
	 *
	 * @return MicrodataParser
	 */
	public static function fromDOMDocument( $domDocument ) {
		$dom = new MicrodataDOMDocument;
		$importedNode = $dom->importNode( $domDocument->documentElement, true );
		$dom->appendChild( $importedNode );

		return new MicrodataParser( $dom );
	}
	
	/**
	 * Creates a MicrodataParser from a custom HTML file
	 *
	 * @param string $filename Path to the file to be parsed
	 * @param string $documentURI DocumentURI to be used in absolutizing URIs
	 *
	 * @return MicrodataParser
	 */
	public static function fromXpathFile( $filename, $documentURI = '' ) {
		
		$dom = new MicrodataDOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadPageHTML( $filename, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING );
		$dom->documentURI = $documentURI;

		return new XpathParser( $dom );
	}
}
