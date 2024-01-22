<?php

class PeepSoFieldCountry extends PeepSoFieldSelectSingle {

    public static $order ='600';
    public static $admin_label='Country';

    private $countries = array();


    public function __construct($post, $user_id)
    {
        $this->field_meta_keys_extra[]='countries_top';
        $this->field_meta_keys_extra[]='countries_exclude';

        $this->field_meta_keys = array_merge($this->field_meta_keys, $this->field_meta_keys_extra);

        parent::__construct($post, $user_id);


        $this->default_desc = __('Select your country.','peepso-core');
    }

    // Utils
    public function get_options()
    {
        $options = array();

        $raw_options = PeepSoCountries::get_countries();

        // keys have to be uppercase
        foreach($raw_options as $k=>$v) {
            $k = strtoupper($k);
            $options[$k] = $v;
        }

        // and the whole thing needs to be alphabetical (by value)
        asort($options, SORT_NATURAL);
        array_multisort(array_map(array('PeepSo3_Utilities_String', 'unaccent'), $options), $options);

        // reorder if some countries need to be on top (defined by admin)
        $options_top = array();
        if(strlen($this->prop('meta','countries_top'))) {
            $countries_top = explode(',',$this->prop('meta','countries_top'));
            if(count($countries_top)) {
                foreach($countries_top as $top) {

                    $top = trim(strtoupper($top));

                    if(array_key_exists($top, $options)) {
                        $options_top[$top] = $options[$top];
                        unset($options[$top]);
                    }
                }
            }
        }

        $options = array_merge($options_top, $options);


        // remove countries hidden by admin
        if(strlen($this->prop('meta','countries_exclude'))) {
            $countries_exclude = explode(',', $this->prop('meta', 'countries_exclude'));
            if(count($countries_exclude)) {
                foreach($countries_exclude as $exclude) {

                    $exclude = trim(strtoupper($exclude));

                    if(array_key_exists($exclude, $options)) {
                        unset($options[$exclude]);
                    }
                }
            }

        }

        return $options;
    }

    function array_natural_sort($string)
    {
        return preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1' . chr(255) . '$2', htmlentities($string, ENT_QUOTES, 'UTF-8'));
    }
}

