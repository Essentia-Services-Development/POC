<?php

class PeepSoInput
{
    // PUBLIC GETTERS
    public function value($name, $default = '', $allowed = FALSE)
    {
        $result = $this->get($name, $this->post($name, $default, TRUE), TRUE);

        /*
         * ENUM like behavior
         * $allowed must be an array (allowed results) or FALSE (no validation required)
         */

        if( FALSE !=$allowed) {

            // all in strtolower just in case
            if (is_array($allowed)) {
                $allowed = array_map('strtolower', $allowed);
            } else {
                $allowed = !empty($allowed) ? strtolower($allowed) : '';
            }

            if(is_array($result)) {
                $check= array_map('strtolower', $result);
            } else {
                $check = !empty($result) ? strtolower($result) : '';
            }


            if (!in_array($check, $allowed) && !array_key_exists($check, $allowed)) {
                return $default;
            }
        }

        return $result;
    }

    public function val($name, $default = '')
    {
        trigger_error("PeepSoInput:val() is deprecated since PeepSo 2.3.0 - use PeepSoInput::value() instead");
        return $this->get($name, $this->post($name, $default, TRUE), TRUE);
    }


    public function int($name, $default = 0)
    {
        return $this->get_int($name, $this->post_int($name, $this->raw_input($name, $default), TRUE), TRUE);
    }


    public function raw($name, $default = 0)
    {
        return $this->get_raw($name, $this->post_raw($name, $default, TRUE), TRUE);
    }

    public function exists($name)
    {
        return ($this->get_exists($name, TRUE) || $this->post_exists($name, TRUE));
    }

    // GET
	private function get($name, $default = '', $private = FALSE)
	{
	    if(!$private) { trigger_error(__CLASS__."::".__METHOD__." is deprecated"); }

		if (isset($_GET[$name])) {
			if (is_array($_GET[$name])) {
				$data = array_map('htmlspecialchars', $_GET[$name]);
				$data = array_map('stripslashes', $data);
				$data = array_map('strip_tags', $data);
				return ($data);
			} else {
				// Use htmlspecialchars to allow input such as "<3" but also sanitizes it in the process.
				return (strip_tags(stripslashes(htmlspecialchars($_GET[$name]))));
			}
		}
		return ($default);
	}

    private function get_int($name, $default = 0, $private = FALSE)
	{
        if(!$private) { trigger_error(__CLASS__."::".__METHOD__." is deprecated"); }

		$get = $this->get($name, $default, $private);

        return PeepSo3_Utilities_String::intval($get);
	}

    private function get_raw($name, $default = '', $private = FALSE)
    {
        if(!$private) { trigger_error(__CLASS__."::".__METHOD__." is deprecated"); }

        if (isset($_GET[$name])) {

        }
        return ($default);
    }

    private function get_exists($name, $private = FALSE)
    {
        if(!$private) { trigger_error(__CLASS__."::".__METHOD__." is deprecated"); }

        if (isset($_GET[$name])) {
            return (TRUE);
        }

        return (FALSE);
    }

	// POST
    private function post($name, $default = '', $private = FALSE)
	{
        if(!$private) { trigger_error(__CLASS__."::".__METHOD__." is deprecated"); }

		if (isset($_POST[$name])) {
			if (is_array($_POST[$name])) {
				$data = array_map('htmlspecialchars', $_POST[$name]);
				$data = array_map('stripslashes', $data);
				$data = array_map('strip_tags', $data);
				return ($data);
			} else {
				// Use htmlspecialchars to allow input such as "<3" but also sanitizes it in the process.
				return (strip_tags(stripslashes(htmlspecialchars($_POST[$name]))));
			}
		}
		return ($default);
	}

    private function post_int($name, $default = 0, $private = FALSE)
	{
        if(!$private) { trigger_error(__CLASS__."::".__METHOD__." is deprecated"); }

		$post = $this->post($name, $default, $private);

		if (is_array($post)) {
			return (array_map('intval', $post));
        }

		return (intval($post));
	}

    private function post_raw($name, $default = '', $private = FALSE)
    {
        if(!$private) { trigger_error(__CLASS__."::".__METHOD__." is deprecated"); }

        if (isset($_POST[$name])) {
            return ($_POST[$name]);
        }

        return ($default);
    }

    private function post_exists($name, $private = FALSE)
    {
        if(!$private) { trigger_error(__CLASS__."::".__METHOD__." is deprecated"); }

        if (isset($_POST[$name]))
            return (TRUE);
        return (FALSE);
    }

    private function raw_input($name, $default = '') {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            parse_str($raw, $output);

            if (isset($output[$name])) {
                return $output[$name];
            }
        }

        return ($default);
    }
}

// EOF