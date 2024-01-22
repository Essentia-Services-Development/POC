<?php

namespace ContentEgg\application\libs;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\stopwords\StopWords;

//use ContentEgg\application\libs\stemmer\Stemmer;

/**
 * KeywordDensity class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 */
class KeywordDensity {

	private $stemmer_normalisation = false;
	private $lang = 'en';   //en|ru|fr|de|...
	private $text = '';
	private $words = array();
	private $stemmer = null; // stemmer instance
	private $stop_words = null;
	private $words_rank = null;

	public function __construct( $lang = 'en' ) {
		$this->lang = $lang;
	}

	public function getStemmer() {
		if ( $this->stemmer == null ) {
			require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'stemmer' . DIRECTORY_SEPARATOR . 'Stemmer.php';
			if ( Stemmer::isLangAvailable( $this->lang ) ) {
				$this->stemmer = new Stemmer( $this->lang );
			} else {
				$this->stemmer = false;
			}
		}

		return $this->stemmer;
	}

	public function SetStemmer( $stemmer ) {
		$this->stemmer = $stemmer;
	}

	public function getStopWords() {
		if ( $this->stop_words == null ) {
			require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'stopwords' . DIRECTORY_SEPARATOR . 'StopWords.php';
			if ( StopWords::isLangAvailable( $this->lang ) ) {
				$sw               = new StopWords( $this->lang );
				$this->stop_words = $sw->words( $this->lang );
			} else {
				$this->stop_words = array();
			}
		}

		return $this->stop_words;
	}

	public function setText( $text ) {
		$this->text  = strip_tags( $text );
		$this->words = null;
	}

	public function setStemmerNormalisatiom( $value ) {
		$this->stemmer_normalisation = (bool) $value;
	}

	public function getPopularWords( $max = null ) {
		$this->splitWords();
		$this->words_rank = array_count_values( $this->words );
		arsort( $this->words_rank );
		if ( $max ) {
			return array_keys( array_slice( $this->words_rank, 0, $max ) );
		} else {
			return array_keys( $this->words_rank );
		}
	}

	private function splitWords() {
		$this->words = array();
		preg_match_all( "/\pL+/ui", $this->text, $m );
		foreach ( $m[0] as $word ) {
			$word = $this->prepareWord( $word );
			if ( ! $word ) {
				continue;
			}

			$this->words[] = $word;
		}
	}

	public function prepareWord( $word ) {
		$word = mb_strtolower( $word, 'UTF-8' );

		if ( in_array( $word, $this->getStopWords() ) ) {
			return false;
		}

		if ( ! ctype_digit( $word ) && mb_strlen( $word, 'UTF-8' ) <= 1 ) {
			return false;
		}

		if ( $this->stemmer_normalisation ) {
			$stemmer = $this->getStemmer();
			if ( $stemmer ) {
				$word = $stemmer->stem( $word );
			}
		}

		return $word;
	}

}
