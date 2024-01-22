<?php

class PeepSoWidgetAjax extends PeepSoAjaxCallback
{
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        return array(
            'latest_members',
            'online_members',
        );
    }

    public function latest_members(PeepSoAjaxResponse $resp)
    {
        ob_start();
        $empty = TRUE;

        $limit = $this->_input->int('limit');
        $totalmember = $this->_input->int('totalmember', 0);
        $hideempty = $this->_input->int('hideempty', 0);

        $mayfly_suffix = '_' . $limit . '_' . $totalmember;
        if (PeepSo::is_admin()) {
            $mayfly_latest_members = 'peepso_cache_widget_latestmembers_admin' . $mayfly_suffix;
        } else {
            $mayfly_latest_members = 'peepso_cache_widget_latestmembers' . $mayfly_suffix;
        }

        // check cache
        $list_latest_members = PeepSo3_Mayfly::get($mayfly_latest_members);

        if (!is_array($list_latest_members)) {
            $list_latest_members = NULL;
        }

        if (NULL === $list_latest_members) {
            // List of links to be displayed
            $args['orderby'] = 'registered';
            $args['order'] = 'desc';
            // $args['exclude'] = get_current_user_id();
            $args_pagination['offset'] = 0;
            $args_pagination['number'] = $limit;

            // Merge pagination args and run the query to grab paged results
            $args = array_merge($args, $args_pagination);

            $list_latest_members = new PeepSoUserSearch($args, get_current_user_id(), '');
            $list_latest_members = $list_latest_members->results;
            PeepSo3_Mayfly::set($mayfly_latest_members, $list_latest_members, 1 * HOUR_IN_SECONDS);
        }

        $PeepSoMemberSearch = PeepSoMemberSearch::get_instance();

        if (is_array($list_latest_members) && count($list_latest_members)) {
            $empty = FALSE;
            ?>


            <?php foreach ($list_latest_members as $user) { ?>
              <div class="psw-members__item">
                <?php $PeepSoMemberSearch->show_latest_member(PeepSoUser::get_instance($user)); ?>
              </div>
            <?php } ?>

            <?php if ($totalmember == 1) {
                $mayfly_member_count = 'peepso_cache_widget_total_member';
                $total_member_value = PeepSo3_Mayfly::get($mayfly_member_count);

                if ($total_member_value == false) {
                    $user_args = array(
                        'peepso_roles' => array('admin', 'moderator', 'member'),
                    );

                    add_action('pre_user_query', array(PeepSo::get_instance(), 'filter_user_roles'));
                    $user_query = new WP_User_Query($user_args);
                    $user_results = $user_query->get_results();
                    remove_action('pre_user_query', array(PeepSo::get_instance(), 'filter_user_roles'));

                    $total_member_value = count($user_results);
                    PeepSo3_Mayfly::set($mayfly_member_count, $total_member_value, 300);
                }

                echo sprintf("<div class=\"psw-members__count\">" . __('Members count', 'peepso-core') . ": %s</div>", $total_member_value);
            }

        } else { ?>
            <div class="psw-members__info"><?php echo __('No latest members', 'peepso-core'); ?></div>
        <?php }

        $resp->success(TRUE);
        $resp->set('empty', $empty);
        $resp->set('html', str_replace(array("  ",PHP_EOL),'',ob_get_clean()));
    }


    public function online_members(PeepSoAjaxResponse $resp) {
        ob_start();
        $empty = TRUE;

        $limit = $this->_input->int('limit');
        $totalmember = $this->_input->int('totalmember', 0);
        $totalonline = $this->_input->int('totalonline', 0);
        $hideempty = $this->_input->int('hideempty', 0);

        $mayfly_suffix = '_' . $limit . '_' . $totalmember . '_' . $totalonline;
        if (PeepSo::is_admin()) {
            $mayfly_online_members = 'peepso_cache_widget_onlinemembers_admin' . $mayfly_suffix;
        } else {
            $mayfly_online_members = 'peepso_cache_widget_onlinemembers' . $mayfly_suffix;
        }

        // check cache
        // PeepSo3_Mayfly::del($mayfly_online_members);
        $list_online_members = PeepSo3_Mayfly::get($mayfly_online_members);

        if (!is_array($list_online_members)) {
            $list_online_members = NULL;
        }

        if(NULL === $list_online_members) {
            // List of links to be displayed
            $args['orderby']= 'peepso_last_activity';
            $args['order']  = 'desc';
            $args_pagination['offset'] = 0;
            $args_pagination['number'] = $limit;

            // Merge pagination args and run the query to grab paged results
            $args = array_merge($args, $args_pagination);

            add_action('peepso_pre_user_query', array(&$this, 'pre_user_query'), 10, 2);
            $list_online_members = new PeepSoUserSearch($args, get_current_user_id(), '');
            $list_online_members = $list_online_members->results;
            remove_action('peepso_pre_user_query', array(&$this, 'pre_user_query'), 10, 2);

            PeepSo3_Mayfly::set( $mayfly_online_members, $list_online_members, 60 );
        }

        $list = array();
        foreach($list_online_members as $user_id)
        {
            $user = PeepSoUser::get_instance($user_id);
            if(TRUE === $user->is_online())
            {
                $list[] = $user;
            }
        }

        $PeepSoMemberSearch = PeepSoMemberSearch::get_instance();

        if (count($list)) {
            $empty = FALSE;
            ?>

            <?php
            foreach ($list as $user) {
                echo '<div class="psw-members__item">';
                $PeepSoMemberSearch->show_online_member($user);
                echo '</div>';
            }
            ?>

            <?php

            if ($totalonline == 1) {

                $mayfly_online_member_count = 'peepso_cache_online_total';
                $total_online_member_value = PeepSo3_Mayfly::get($mayfly_online_member_count);

                if ($total_online_member_value == false) {
                    global $wpdb;

                    $sql = "SELECT count(`id`) as total FROM `{$wpdb->prefix}peepso_mayfly` WHERE `name` LIKE '%peepso_cache_%_online'";
                    $total_online_member_value = intval($wpdb->get_row($sql)->total);
                    PeepSo3_Mayfly::set($mayfly_online_member_count, $total_online_member_value, 300);
                }

                // to avoid obvious cache differences, default the count to visible items if the limit was not hit
                if($total_online_member_value != count($list) && count($list) < $limit) {
                    $total_online_member_value = count($list);
                }

                echo sprintf("<div class=\"psw-members__count\">" . _n('%d member online','%d members online', $total_online_member_value, 'peepso-core') . "</div>", $total_online_member_value);
            }


            if ($totalmember == 1) {
                $mayfly_member_count = 'peepso_cache_widget_total_member';
                $total_member_value = PeepSo3_Mayfly::get($mayfly_member_count);

                if ($total_member_value == false) {
                    $user_args = array(
                        'peepso_roles' => array('admin', 'moderator', 'member'),
                    );

                    add_action('pre_user_query', array(PeepSo::get_instance(), 'filter_user_roles'));
                    $user_query = new WP_User_Query($user_args);
                    $user_results = $user_query->get_results();
                    remove_action('pre_user_query', array(PeepSo::get_instance(), 'filter_user_roles'));

                    $total_member_value = count($user_results);
                    PeepSo3_Mayfly::set($mayfly_member_count, $total_member_value, 300);
                }

                echo sprintf("<div class=\"psw-members__count\">" . __('%d members total', 'peepso-core') . "</div>", $total_member_value);
            }

        } else { ?>
            <span class='ps-text--muted'><?php echo __('No online members', 'peepso-core');?></span>
        <?php }

        $resp->success(TRUE);
        $resp->set('empty', $empty);
        $resp->set('html', str_replace(array("  ",PHP_EOL),'',ob_get_clean()));
    }

    public function pre_user_query(&$wp_user_query, $user_id)
    {
        global $wpdb;

        // Check config option for Allow users to hide themselves from all user listings
        if (!PeepSo::is_admin()) {
            $wp_user_query->query_from .= ' LEFT JOIN `' . $wpdb->usermeta . '` `psmeta_online`
                    ON (`' . $wpdb->users . '`.`ID` = `psmeta_online`.`user_id` AND `psmeta_online`.`meta_key` = \'peepso_hide_online_status\') ';
            $wp_user_query->query_where .= ' AND (  `psmeta_online`.`meta_value` <> \'1\' OR `psmeta_online`.`user_id` IS NULL )';
        }
    }
}
