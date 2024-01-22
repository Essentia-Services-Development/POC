<?php

class PeepSoVideosAWSErrors
{
	const ERROR_KEY = 'peepso_uploadvideos_aws_errors';
	const MAX_ERRORS = 5;

	/**
	 * Stores the error message in the list of AWS error messages
	 * @param string $msg The error message to add to the list
	 */
	public function add_error($msg)
	{
		$error_list = get_option(self::ERROR_KEY, FALSE);

		if (FALSE === $error_list || empty($error_list)) {
			$add = TRUE;
			$error_list = array();
		} else {
			$add = FALSE;
			if (count((array) $error_list) >= self::MAX_ERRORS)
				$error_list = array_slice((array) $error_list, 0 - (self::MAX_ERRORS - 1));
		}

		$time = strval(time());
		$error_list[] = $time . ':' . $msg;

		// persist the error
		if ($add)
			add_option(self::ERROR_KEY, $error_list, FALSE, FALSE);
		else
			update_option(self::ERROR_KEY, $error_list);
	}

	/**
	 * Returns the persisted list of error messages
	 * @returns array An array of the last few error messages
	 */
	public function get_errors()
	{
		$error_list = get_option(self::ERROR_KEY, array());
		return ($error_list);
	}

	public static function clear_errors() {
		delete_option(self::ERROR_KEY);
	}
}

// EOF