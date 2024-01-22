<?php
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');
/*
 * Performs installation process
 * @package PeepSoVideos
 * @author PeepSo
 */
class PeepSoGroupsInstall extends PeepSoInstall
{
	// these items are stored under the mail 'peepso_config' option
	protected $default_config = array(
			'groups_creation_enabled' => 1,
			'groups_creation_enabled_description' => 1,
			'groups_listing_show_group_owner' => 1,
			'groups_listing_show_group_creation_date' => 1,
			'groups_categories_enabled' => 0,
			'groups_categories_multiple_max' => 1,
			'groups_categories_hide_empty' => 0,
            'groups_pin_allow_managers' => 1,
            'groups_members_tab'        => 1,
		);

	/*
	 * called on plugin activation; performs all installation tasks
	 */
	public function plugin_activation( $is_core = FALSE )
	{
		parent::plugin_activation($is_core);
		return (TRUE);
	}

	/*
	 * return default page names information
	 */
	protected function get_page_data()
	{
		// default page names/locations
		$aRet = array(
			'groups' => array(
				'title' => __('Groups', 'msgso'),
				'slug' => 'groups',
				'content' => '[peepso_groups]',
			),
		);

		return ($aRet);
	}

	public static function get_table_data()
	{
		$aRet = array(
		    'group_followers'=>"
		      CREATE TABLE group_followers (
                  gf_id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  gf_group_id bigint(20) UNSIGNED NOT NULL,
                  gf_user_id bigint(20) UNSIGNED  NOT NULL,
                  gf_follow smallint(1) UNSIGNED  NOT NULL DEFAULT 1,
                  gf_notify smallint(1) UNSIGNED  NOT NULL DEFAULT 1,
                  gf_email smallint(1) UNSIGNED  NOT NULL DEFAULT 1,
                  PRIMARY KEY (gf_id),
				  INDEX gf_group (gf_group_id),
				  INDEX gf_user (gf_user_id),
				  INDEX gf_follow (gf_follow),
				  INDEX gf_notify (gf_notify),
				  INDEX gf_email (gf_email)
            ) ENGINE=InnoDB",

			'group_members' => "
				CREATE TABLE group_members (
					gm_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					gm_user_id BIGINT(20) UNSIGNED NOT NULL,
					gm_group_id BIGINT(20) UNSIGNED NOT NULL,
					gm_user_status enum('member_readonly','member','member_moderator','member_manager','member_owner','pending_user','pending_admin','banned','block_invites') NOT NULL,
					gm_joined TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					gm_invited_by_id BIGINT(20) UNSIGNED DEFAULT NULL,
					gm_accepted_by_id BIGINT(20) UNSIGNED DEFAULT NULL,
					PRIMARY KEY (gm_id),
					INDEX gm_user_id (gm_user_id),
					INDEX gm_group_id (gm_group_id),
					INDEX gm_user_status (gm_user_status)
				) ENGINE=InnoDB",

			'group_categories' => "
				CREATE TABLE group_categories (
					gc_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				  	gm_cat_id BIGINT(20) UNSIGNED NOT NULL,
					gm_group_id BIGINT(20) UNSIGNED NOT NULL,
					PRIMARY KEY (gc_id),
					INDEX gc_cat_id (gm_cat_id),
					INDEX gc_group_id (gm_group_id)
				) ENGINE=InnoDB",
		);


		return $aRet;
	}

    public function get_email_contents()
    {
        $emails = array(
            'email_group_new_post' => "Hi {firstname},

{fromfirstname} just wrote a post in {groupname}. You can see it here: {permalink}

Thank you.",

            'email_group_created' => "Hi {firstname},

{fromfirstname} just created a new group: {groupname}


You can see it here: {permalink}

Thank you.",
			'email_user_comment_group_avatar' => "Hi {firstname},

{fromfirstname} had something to say about your group avatar!

You can see the avatar here:

{permalink}

Thank you.",
			'email_user_comment_group_cover' => "Hi {firstname},

{fromfirstname} had something to say about your group cover!

You can see the cover here:

{permalink}

Thank you.",

        );

        return $emails;
    }
}