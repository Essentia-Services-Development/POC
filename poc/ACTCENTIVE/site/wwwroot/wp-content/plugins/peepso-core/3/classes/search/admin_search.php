<?php

class PeepSo3_Search_Admin
{
    private static $instance;

    public static function get_instance()
    {
        return isset(self::$instance) ? self::$instance : self::$instance = new self;
    }

    private function __construct()
    {
        if (is_admin()) {

            add_filter('peepso_admin_config_tabs', 			function($tabs){
                $tabs['search'] = array(
                    'label' => __('Search', 'groupso'),
                    'icon' => 'https://cdn.peepso.com/icons/configsections/search.svg',
                    'tab' => 'search',
                    'description' => 'Search',
                    'function' => 'PeepSo3_Search_Config',
                    'cat' => 'foundation-advanced',
                );

                return $tabs;
            });

        }
    }
}

class PeepSo3_Search_Config extends PeepSoConfigSectionAbstract {

    public function register_config_groups() {
        $this->context='left';
        $this->general();

        $this->context='right';
        $this->sections();
    }

    private function general() {

        $this->args('validation',['numeric']);
        $this->set_field(
            'peepso_search_limit_items_per_section',
            __('Items per section','peepso-core'),
            'text'
        );

        $this->args('validation',['numeric']);
        $this->set_field(
            'peepso_search_limit_length_title',
            __('Title length','peepso-core'),
            'text'
        );

        $this->args('validation',['numeric']);
        $this->set_field(
            'peepso_search_limit_length_text',
            __('Text length','peepso-core'),
            'text'
        );

        $this->set_field(
            'peepso_search_show_images',
            __('Show images'),
            'yesno_switch'
        );


        $this->set_field(
            'peepso_search_show_empty_sections',
            __('Show empty sections'),
            'yesno_switch'
        );



        $this->set_group(
            'peepso_search_general',
            __('General', 'peepso-core')
        );
    }

    private function sections() {

        $sections = apply_filters('peepso_search_sections', []);

        if(count($sections)) {
            $options=[];

            // To give admin some breathing room when ordering, round up to the next 5
            // 7 -> 20, 13 -> 30, etc
            $limit = ceil(1+count($sections)/10)*10;
            for($i=1;$i<=$limit;$i++) {
                $options[$i]=$i;
            }

            foreach($sections as $section) {

                if(!$section instanceof PeepSo3_Search_Adapter) { continue; }

                $this->set_field(
                    'peepso_search_section_separator_'.$section->section,
                    $section->title,
                    'separator'
                );

                $this->set_field(
                    'peepso_search_section_enable_'.$section->section,
                    __('Enabled', 'peepso-core'),
                    'yesno_switch'
                );


                $this->args('options',$options);
                $this->args('default', 10);
                $this->set_field(
                    'peepso_search_section_order_'.$section->section,
                    __('Order', 'peepso-core'),
                    'select'
                );
            }
        }


        $this->set_group(
            'peepso_search_sections',
            __('Sections', 'peepso-core')
        );
    }
}

if(PeepSo::is_dev_mode('new_search')) {
    PeepSo3_Search_Admin::get_instance();
}