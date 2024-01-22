<?php

class PeepSoVIP {

    private static $_instance = NULL;

    const VIP_ICON_BEFORE_FULLNAME = 0;
    const VIP_ICON_AFTER_FULLNAME = 1;

    private function __construct() {
        add_action('peepso_init', array(&$this, 'init'));
    }

    /**
     * Retrieve singleton class instance
     * @return PeepSoVIP instance
     */
    public static function get_instance()
    {
        if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }

    public function init()
    {
        if (is_admin()) {
            // add vip to profile
            if(PeepSo::is_admin()) {
                add_action('show_user_profile', array(&$this, 'vip_user_profile_fields'));
                add_action('edit_user_profile', array(&$this, 'vip_user_profile_fields'));
                add_action('personal_options_update', array(&$this, 'save_vip_user_profile_fields'));
                add_action('edit_user_profile_update', array(&$this, 'save_vip_user_profile_fields'));
            }

		    add_action('manage_users_columns', array(&$this, 'filter_user_list_columns'));
		    add_action('manage_users_custom_column', array(&$this, 'filter_custom_user_column'), 10, 3);
        } else {
            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

            if (1 == PeepSo::get_option('vipso_member_search', 0)) {
                add_action('peepso_action_render_member_search_fields', array(&$this, 'action_render_member_search_fields'));
            }
        }

        add_action('peepso_action_render_user_name_before', array(&$this, 'before_display_name'), 10, 2);
        add_action('peepso_action_render_user_name_after', array(&$this, 'after_display_name'), 10, 2);

        // Add VIP icons to UserBar widget
        add_action('peepso_action_userbar_user_name_before', array(&$this, 'before_display_name'), 10, 2);
        add_action('peepso_action_userbar_user_name_before', array(&$this, 'after_display_name'), 10, 2);

        // AJAX endpoint to see user's icons
        add_action('wp_ajax_peepso_vip_user_icons', array(&$this, 'user_icons'));
        add_action('wp_ajax_nopriv_peepso_vip_user_icons', array(&$this, 'user_icons'));

        add_filter('peepso_hovercard', function($data, $user_id) {
            $data['vip'] = $this->get_user_icons($user_id);
            return $data;
        }, 10, 2);

        add_filter('peepso_member_search_args', array(&$this, 'filter_member_search_args'), 10, 2);
        add_filter('peepso_user_search_args', array(&$this, 'filter_user_search_args'), 10, 1);
    }

    public function get_user_icons($user_id) {
        $PeepSoVipIconsModel = new PeepSoVipIconsModel();
        $user_icons = (array) get_the_author_meta('peepso_vip_user_icon', $user_id);
        $user_icons = $this->sort_icons($user_icons);

        $result = array();

        $i = 1;
        foreach ($user_icons as $icon) {
            $vipicon = $PeepSoVipIconsModel->vipicon($icon);
            if (intval($vipicon->published) == 1) {
                $result[$i] = $vipicon;
                $i++;
            }
        }

        return $result;
    }

    public function user_icons() {
        $input = new PeepSoInput();
        $user_id = $input->int('user_id',0);
        $result = $this->get_user_icons($user_id);
        die( json_encode($result) );
    }
    /**
     * Enqueue custom scripts and styles
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('peepso-vip',
            PeepSo::get_asset('js/vip/bundle.min.js'),
            array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);

        add_filter('peepso_data', function($data) {
            $data['vip'] = array(
                'popoverEnable' => PeepSo::get_option('hovercards_enable', 1) == 0,
                'popoverTemplate' => PeepSoTemplate::exec_template('vip', 'popover', NULL, TRUE),
                'hovercardTemplate' => PeepSoTemplate::exec_template('vip', 'hovercard', NULL, TRUE),
            );
            return $data;
        }, 10, 1 );
    }

    /**
     * BACKEND SETTINGS
     * ================
     */

    public function vip_user_profile_fields($user)
    {
        ?>
        <h3><?php echo __('VIP', 'peepso-core');?></h3>
        <table class="form-table">
            <tr class="user-admin-color-wrap">
                <th scope="row"><?php echo __('Icon to display next to name/username in PeepSo', 'peepso-core');?></th>
                <td>
                    <fieldset id="vip-icons" class="scheme-list">
                        <legend class="screen-reader-text"><span><?php echo __('Icon to display', 'peepso-core');?></span></legend>
                        <?php
                        $PeepSoVipIconsModel = new PeepSoVipIconsModel();
                        $selectedIcon = get_the_author_meta( 'peepso_vip_user_icon', $user->ID );
                        $selectedIcon = $this->sort_icons($selectedIcon);
                        if (!is_array($selectedIcon)) {
                            $selectedIcon = [$selectedIcon];
                        }
                        foreach ($PeepSoVipIconsModel->vipicons as $key => $value) {
                            ?>
                            <div class="color-option">
                                <input name="peepso_vip_user_icon[]" id="vip_icon_<?php echo $key;?>" type="checkbox" value="<?php echo $value->post_id;?>" class="tog" <?php echo (in_array($value->post_id, $selectedIcon)) ? 'checked="checked"' : '';?>>
                                <label for="vip_icon_<?php echo $key;?>"><?php echo $value->title;?> <?php  if(!intval($value->published)) { echo "<small>(".__('unpublished', 'peepso-core').")</small>"; }  ?></label>
                                <img src="<?php echo $value->icon_url;?>" style="width: auto; height: 16px;">
                            </div>
                            <?php
                        }
                        ?>
                    </fieldset>
                </td>
            </tr>
        </table>

        <?php
    }

    public function save_vip_user_profile_fields($user_id)
    {
        if ( !current_user_can( 'edit_user', $user_id ) ) {
            return (FALSE);
        }

        update_user_meta( $user_id, 'peepso_vip_user_icon', $_POST['peepso_vip_user_icon'] );
    }

    /**
     * vip core
     *
     */

    public function more_icons($amount, $last_icon, $user_id) {

        if(!PeepSo::get_option('vipso_display_more_icons_count', 0)){
            return;
        }

        if($amount == 1) {
            echo $last_icon;
        }

        if($amount>1) {
            ?>
            <div class="ps-vip__counter ps-js-vip-badge" data-id="<?php echo $user_id ?>">
                +<?php echo $amount; ?></div>
            <?php
        }
    }

    public function before_display_name($user_id)
    {
        $icons = get_the_author_meta( 'peepso_vip_user_icon', $user_id ) ;
        $icons = $this->sort_icons($icons);
        $display = PeepSo::get_option('vipso_where_to_display', 1);
        $limit = PeepSo::get_option('vipso_display_how_many', 10);
        if( $display == self::VIP_ICON_BEFORE_FULLNAME && is_array($icons) && count($icons) > 0 && $limit>0) {
            $PeepSoVipIconsModel = new PeepSoVipIconsModel();

            $i=0;
            $class = '';
            $more = 0;
            $last_icon = '';

            foreach ($icons as $icon) {

                $vipicon = $PeepSoVipIconsModel->vipicon($icon);
                if(intval($vipicon->published) == 1) {

                    if($i>=$limit) {
                        $class = 'ps-vip__icon--hidden ps-js-vip-badge-hidden';
                        $more++;
                    }

                    echo '<img src="' . $vipicon->icon_url . '" alt="'.$vipicon->title.'"  title="'.$vipicon->title
                        .'" class="ps-vip__icon ps-js-vip-badge '.$class.'" data-id="'.$user_id.'"> ';

                    $last_icon = '<img src="' . $vipicon->icon_url . '" alt="'.$vipicon->title.'"  title="'.$vipicon->title
                        .'" class="ps-vip__icon ps-js-vip-badge" data-id="'.$user_id.'"> ';

                    $i++;
                }
            }

            echo $this->more_icons($more, $last_icon, $user_id);
        }

    }

    public function after_display_name($user_id)
    {
        $icons = get_the_author_meta( 'peepso_vip_user_icon', $user_id );
        $icons = $this->sort_icons($icons);
        $display = PeepSo::get_option('vipso_where_to_display', 1);
        $limit = PeepSo::get_option('vipso_display_how_many', 10);
        if( $display == self::VIP_ICON_AFTER_FULLNAME && is_array($icons) && count($icons) > 0) {
            $PeepSoVipIconsModel = new PeepSoVipIconsModel();

            $i=0;
            $class = '';
            $more = 0;
            $last_icon = '';

            foreach ($icons as $icon) {

                $vipicon = $PeepSoVipIconsModel->vipicon($icon);
                if(intval($vipicon->published) == 1) {

                    if($i>=$limit) {
                        $class = 'ps-vip__icon--hidden ps-js-vip-badge-hidden';
                        $more++;
                    }

                    echo ' <img src="' . $vipicon->icon_url . '" alt="'.$vipicon->title.'" title="'.$vipicon->title
                        .'" class="ps-vip__icon ps-js-vip-badge '.$class.'" data-id="'.$user_id.'">';

                    $last_icon = '<img src="' . $vipicon->icon_url . '" alt="'.$vipicon->title.'"  title="'.$vipicon->title
                        .'" class="ps-vip__icon ps-js-vip-badge" data-id="'.$user_id.'"> ';

                    $i++;
                }
            }

            echo $this->more_icons($more, $last_icon, $user_id);
        }
    }

    public function filter_user_list_columns($columns)
	{
        $columns['peepso_vip'] = __('PeepSo VIP icons', 'peepso-core');
		return $columns;
	}

    public function filter_custom_user_column($value, $column, $id)
	{
        $PeepSoVipIconsModel = new PeepSoVipIconsModel();

		switch ($column)
		{
            case 'peepso_vip':
                $icons = get_the_author_meta('peepso_vip_user_icon', $id);
                $icons = $this->sort_icons($icons);
                if (is_array($icons) && count($icons) > 0) {
                    foreach ($icons as $icon) {
                        $vipicon = $PeepSoVipIconsModel->vipicon($icon);
                        if (intval($vipicon->published) == 1) {
                            $value .= ' <img width="16px" src="' . $vipicon->icon_url . '" alt="' . $vipicon->title . '" title="' .
                                $vipicon->title . '" data-id="' . $id . '">';
                        }
                    }
                }
                break;
		}
		return $value;
	}

    public function sort_icons($icons) {
        $icons = (!is_array($icons) && !empty($icons)) ? [$icons] : $icons;
        if (empty($icons)) {
            return [];
        }
        $PeepSoVipIconsModel = new PeepSoVipIconsModel();
        $keys = array_keys($PeepSoVipIconsModel->vipicons);
        return array_intersect($keys, $icons);

    }

    public function action_render_member_search_fields() {
        PeepSoTemplate::exec_template('vip', 'search_field', NULL);
    }

    public function filter_member_search_args($peepso_args, $input) {
        $vip_icon = $input->value('vip_icon', '', FALSE);
        if ($vip_icon) {
            $peepso_args['vip_icon'] = $vip_icon;
        }

        return $peepso_args;
    }

    public function filter_user_search_args($args) {
        if (isset($args['_peepso_args']['vip_icon'])) {
            $vip_icon = $args['_peepso_args']['vip_icon'];
            unset($args['_peepso_args']['vip_icon']);

            $args['meta_key'] = 'peepso_vip_user_icon';
            $args['meta_value'] = '"' . $vip_icon . '"';
            $args['meta_compare'] = 'LIKE';
        }

        return $args;
    }
}
