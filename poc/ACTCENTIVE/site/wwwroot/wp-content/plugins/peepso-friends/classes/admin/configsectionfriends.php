<?php

class PeepSoConfigSectionFriends extends PeepSoConfigSectionAbstract
{
// Builds the groups array
    public function register_config_groups()
    {
        $this->context='left';
        $this->general();

        $this->context='full';
        $this->autofriends();
    }

    private function general(){

        if(PeepSo3_Helper_Addons::license_is_free_bundle(FALSE)) {
            $this->set_field(
                'friends_max_amount_disabled',
                PeepSo3_Helper_Remote_Content::get('free_bundle_disabled_text'),
                'message'
            );
            PeepSoConfigSettings::get_instance()->set_option('friends_max_amount',50);
        }
        $val = ['numeric', 'minval:1'];

        if(PeepSo3_Helper_Addons::license_is_free_bundle( TRUE)) {
            $val[]='readonly';
        }

        $this->args('validation', $val);
        $this->args('descript', __('Too many friendships can result in degraded performance for the user. Limit of friends applies to all users, including the administrators.', 'friendso'));
        $this->set_field(
            'friends_max_amount',
            __('Maximum amount of friends', 'friendso'),
            'text'
        );

        // Build Group
        $this->set_group(
            'general',
            __('Maximum amount of friends', 'friendso')
        );
    }

    private function autofriends()
    {
        if(PeepSo3_Helper_Addons::license_is_free_bundle()) {
            $this->set_field(
                'autofriends_disabled',
                PeepSo3_Helper_Remote_Content::get('free_bundle_disabled_text'),
                'message'
            );
        } else {
            $this->set_field(
                '_searchbox',
                '<input type="search" id="search_user" name="search_user" value="" placeholder="' . __('search', 'friendso') . '" style="width:100%"/><p id="empty-message" style="color:gray;padding-left:15px;padding-right:15px;"></p>',
                'message'
            );

            $this->set_field(
                'autofriends_description_detail1',
                __('Adding a user to the list below will automatically create friendship between that user and any newly registered user. It will not create friendships between this user and already existing ones. <br> To create missing friendship connections, please use the button on the right. Once clicked it\'ll add that user as friends to everyone else in your PeepSo community. <br> Removing user from the list below will <strong>NOT</strong> remove existing friendship connections. ', 'friendso'),
                'message'
            );

            wp_enqueue_style('autocompleteautofriends-css');
            wp_enqueue_script('peepso-window');
            wp_enqueue_script('jquery-ui-autocomplete', array('jquery'));
            wp_enqueue_script('adminuserautofriends-js');

            $peepso_list_table = new PeepSoUserAutoFriendsListTable();
            $peepso_list_table->prepare_items();

            ob_start();
            echo "<div style='margin:0 15px;'>";
            wp_nonce_field('bulk-action', 'autofriends-nonce');
            $peepso_list_table->display();
            echo "</div>";
            $table = ob_get_clean();

            $this->set_field(
                'autofriends_table',
                $table,
                'message'
            );
        }

        // Build Group
        $this->set_group(
            'autofriends',
            __('Auto Friends', 'friendso')
        );
    }
}