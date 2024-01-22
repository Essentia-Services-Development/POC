<?php

class PeepSoExportDataTemplate
{
	// default tokens
	private $aTokens = array(
		'data_title' => '',
		'data_name' => '',
		'data_sidebar' => '',
		'data_contents' => '',
	);


	/*
	 * Constructor
	 */
	public function __construct()
	{
		//
	}


	/*
	 * Sets a token's value
	 * @param string $name The name of the token
	 * @param string $value The value to be used for this token
	 */
	public function set_token($name, $value)
	{
		$this->aTokens[$name] = $value;
	}

	/**
	 * Sets a token's value based on a given $data
	 * @param array $data An array of tokens
	 */
	public function set_tokens($data)
	{
		foreach ($data as $name => $value) {
			$this->aTokens[$name] = $value;
		}
	}

	/**
	 * Searches through a string and replaces the tokens with corresponding values
	 * @param  string $content The string to replace the contents of
	 * @return string The string with the tokens replaced
	 */
	public function replace_content_tokens($content)
	{
		// look for any other tokens and replace their values
		foreach ($this->aTokens as $token => $value) {
			$replace_token = '{' . $token . '}';
			$content = str_ireplace($replace_token, $value, $content);
		}

		return ($content);
	}
}

// EOF
