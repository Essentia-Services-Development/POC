<?php

/*
 * Modified version of XmlStringStreamer, edited namespaces only 
 * @author keywordrush.com <support@keywordrush.com>
 * @copyright Copyright &copy; 2021 keywordrush.com
 */

/**
 * xml-string-streamer base class
 *
 * @package xml-string-streamer
 * @author  Oskar Thornblad <oskar.thornblad@gmail.com>
 */

namespace ContentEgg\application\vendor\XmlStringStreamer;

use ContentEgg\application\vendor\XmlStringStreamer\ParserInterface;
use ContentEgg\application\vendor\XmlStringStreamer\StreamInterface;
use ContentEgg\application\vendor\XmlStringStreamer\Parser;
use ContentEgg\application\vendor\XmlStringStreamer\Stream;

/**
 * The base class for the xml-string-streamer
 */
class XmlStringStreamer {
	/**
	 * The current parser
	 * @var ParserInterface
	 */
	protected $parser;
	/**
	 * The current stream
	 * @var StreamInterface
	 */
	protected $stream;

	/**
	 * Constructs the XML streamer
	 *
	 * @param ParserInterface $parser A parser with options set
	 * @param StreamInterface $stream A stream for the parser to use
	 */
	public function __construct( ParserInterface $parser, StreamInterface $stream ) {
		$this->parser = $parser;
		$this->stream = $stream;
	}

	/**
	 * Convenience method for creating a StringWalker parser with a File stream
	 *
	 * @param string|resource $file File path or handle
	 * @param array $options Parser configuration
	 *
	 * @return XmlStringStreamer        A streamer ready for use
	 */
	public static function createStringWalkerParser( $file, $options = array() ) {
		$stream = new Stream\File( $file, 16384 );
		$parser = new Parser\StringWalker( $options );

		return new XmlStringStreamer( $parser, $stream );
	}

	/**
	 * Convenience method for creating a UniqueNode parser with a File stream
	 *
	 * @param string|resource $file File path or handle
	 * @param array $options Parser configuration
	 *
	 * @return XmlStringStreamer        A streamer ready for use
	 */
	public static function createUniqueNodeParser( $file, $options = array() ) {
		$stream = new Stream\File( $file, 16384 );
		$parser = new Parser\UniqueNode( $options );

		return new XmlStringStreamer( $parser, $stream );
	}

	/**
	 * Gets the next node from the parser
	 * @return bool|string The xml string or false
	 */
	public function getNode() {
		return $this->parser->getNodeFrom( $this->stream );
	}
}