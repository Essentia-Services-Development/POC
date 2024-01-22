<?php

class PeepSoGroupFollowers
{
    const TABLE = 'peepso_group_followers';

    public $followers = array();

    public function __construct($group_id, $load_objects = false, $follow = NULL, $notify = NULL, $email = NULL)
    {
        global $wpdb;
        $group_id = intval($group_id);

        $this->followers = array();

        $where = '';

        if(!is_null($follow) && in_array($follow, array(0,1))) {
            $where.= " AND gf_follow=$follow ";
        }

        if(!is_null($notify) && in_array($notify, array(0,1))) {
            $where.= " AND gf_notify=$notify";
        }

        if(!is_null($email) && in_array($email, array(0,1))) {
            $where.= " AND gf_email=$email";
        }

        $r = $wpdb->get_results("SELECT gf_user_id FROM  ". $wpdb->prefix . PeepSoGroupFollowers::TABLE . " WHERE gf_group_id=$group_id $where");

        if (count($r)) {
            foreach ($r as $gf) {
                if($load_objects ) {
                    $this->followers[$gf->gf_user_id] = new PeepSoGroupFollower($group_id, $gf->gf_user_id);
                } else {
                    $this->followers[$gf->gf_user_id] = $gf->gf_user_id;
                }
            }
        }
    }


    public function get_followers() {
        return $this->followers;
    }


    public static function rebuild($limit = 10)
    {
        global $wpdb;
        $r = $wpdb->get_results("SELECT `gm_group_id` as g, `gm_user_id` as u FROM " . $wpdb->prefix . PeepSoGroupUsers::TABLE . " gm 
            LEFT JOIN " . $wpdb->prefix . PeepSoGroupFollowers::TABLE . " ON gf_group_id=gm.gm_group_id AND gf_user_id=gm.gm_user_id
            WHERE `gm_user_status` LIKE 'member%' AND gf_user_id IS NULL LIMIT 0,$limit");
        $i =0;
        if (count($r)) {
            foreach ($r as $gm) {
                new PeepSoGroupFollower($gm->g, $gm->u);
                $i ++;
            }
        }

        return $i;
    }
}