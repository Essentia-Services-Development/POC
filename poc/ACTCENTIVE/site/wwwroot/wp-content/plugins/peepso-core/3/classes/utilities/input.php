<?php

class PeepSo3_Input
{
    // PUBLIC

    public function value($name, $default = '', $allowed = FALSE)
    {
        $result = $this->get($name, $this->post($name, $default));

        /*
         * ENUM like behavior
         * $allowed must be an array (allowed results) or FALSE (no validation required)
         */

        if( FALSE !=$allowed) {

            // all in strtolower just in case
            if (is_array($allowed)) {
                $allowed = array_map('strtolower', $allowed);
            } else {
                $allowed = [strtolower($allowed)];
            }

            if (is_array($result)) {
                # 6788 if $_REQUEST value is an array

                $check = $result;
                // Only successful if the two arrays are identical
                if($check==$allowed) {
                    return $check;
                }

                return $default;

            } elseif(is_string($result) || is_int($result)) {
                # 6788 double check if $result is an int or string
                $check = !is_null($result) ? strtolower($result) : NULL;
                if (!in_array($check, $allowed) && !array_key_exists($check, $allowed)) {
                    return $default;
                }
            } else {
                # 6788 if somehow $result is not a string, int or array, log an error and return default
                new PeepSoError(__METHOD__.':'.__LINE__.' $result is not a string, int or array '.print_r($result,TRUE));
                return $default;
            }



        }

        return $result;
    }

    public function val($name, $default = '')
    {
        trigger_error("PeepSo3_Input:val() is deprecated since PeepSo 2.8.0 - use PeepSoInput::value() instead");
        return $this->get($name, $this->post($name, $default));
    }

    public function int($name, $default = 0)
    {
        return $this->get_int($name, $this->post_int($name, $this->raw_input($name, $default)));
    }

    public function raw($name, $default = 0)
    {
        return $this->get_raw($name, $this->post_raw($name, $default, TRUE), TRUE);
    }

    public function exists($name)
    {
        return ($this->get_exists($name) || $this->post_exists($name));
    }

    // PRIVATE GET

    private function get($name, $default = '')
    {
        if (isset($_GET[$name])) {
            if (is_array($_GET[$name])) {
                $data = map_deep($_GET[$name], 'htmlspecialchars');
                $data = map_deep($data, 'stripslashes');
                $data = map_deep($data, 'strip_tags');
                return ($data);
            } else {
                // Use htmlspecialchars to allow input such as "<3" but also sanitizes it in the process.
                return (strip_tags(stripslashes(htmlspecialchars($_GET[$name]))));
            }
        }
        return ($default);
    }

    private function get_int($name, $default = 0)
    {
        $get = $this->get($name, $default);

        if (is_array($get)) {
            return(array_map(['PeepSo3_Utilities_String','intval'], $get));
        }

        return PeepSo3_Utilities_String::intval($get);
    }

    private function get_raw($name, $default = '') {

        if (isset($_GET[$name])) {
            return $_GET[$name];
        }

        return ($default);
    }

    private function get_exists($name) {

        if (isset($_GET[$name])) {
            return (TRUE);
        }

        return (FALSE);
    }

    // PRIVATE POST
    private function post($name, $default = '')
    {
        if (isset($_POST[$name])) {
            if (is_array($_POST[$name])) {
                $data = map_deep($_POST[$name], 'htmlspecialchars');
                $data = map_deep($data, 'stripslashes');
                $data = map_deep($data, 'strip_tags');
                return ($data);
            } else {
                // Use htmlspecialchars to allow input such as "<3" but also sanitizes it in the process.
                return (strip_tags(stripslashes(htmlspecialchars($_POST[$name]))));
            }
        }
        return ($default);
    }

    private function post_int($name, $default = 0) {

        $post = $this->post($name, $default);

        if (is_array($post)) {
            return(array_map(['PeepSo3_Utilities_String','intval'], $post));
        }

        return PeepSo3_Utilities_String::intval($post);
    }

    private function post_raw($name, $default = '') {

        if (isset($_POST[$name])) {
            return ($_POST[$name]);
        }

        return ($default);
    }

    private function post_exists($name) {

        if (isset($_POST[$name])) {
            return (TRUE);
        }

        return (FALSE);
    }

    // PRIVATE INPUT
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