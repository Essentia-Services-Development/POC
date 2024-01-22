<?php

/*
 * Modified version of XmlStringStreamer, edited namespaces only 
 * @author keywordrush.com <support@keywordrush.com>
 * @copyright Copyright &copy; 2021 keywordrush.com
 */

/**
 * xml-string-streamer Stream interface
 *
 * @package xml-string-streamer
 * @author  Oskar Thornblad <oskar.thornblad@gmail.com>
 */

namespace ContentEgg\application\vendor\XmlStringStreamer;

use Exception;

/**
 * Interface describing a stream provider
 */
interface StreamInterface {
	/**
	 * Gets the next chunk form the stream if one is available
	 * @return bool|string The next chunk if available, or false if not available
	 */
	public function getChunk();

	/**
	 * Is the stream seekable?
	 * @return bool
	 */
	public function isSeekable();

	/**
	 * Rewind the stream
	 * @return void
	 * @throws Exception if the stream isn't seekable
	 */
	public function rewind();
}