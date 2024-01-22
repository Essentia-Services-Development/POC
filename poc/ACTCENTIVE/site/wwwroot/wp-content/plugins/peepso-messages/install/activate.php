<?php
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');
/*
 * Performs installation process
 * @package PeepSoMessages
 * @author PeepSo
 */
class PeepSoMessagesInstall extends PeepSoInstall
{
    const DBVERSION_OPTION_NAME = 'peepso_messages_database_version';
    const DBVERSION = '3';

    protected $default_config = array(
        'messages_limit' => 4000,
        'messages_archive_days' => 60,
        'messages_auto_refresh' => 3,
        'messages_get_chats_longpoll' => FALSE,
        'messages_friends_only' => 1,
    );

    /*
     * called on plugin activation; performs all installation tasks
     */
    public function plugin_activation( $is_core = FALSE )
    {
        parent::plugin_activation($is_core);
        return (TRUE);
    }

    public static function get_table_data()
    {
        // we just need to create records that relate back to the wp_posts.ID column

        $aRet = array(
            'message_participants' => "
				CREATE TABLE message_participants (
					mpart_key int(11) NOT NULL AUTO_INCREMENT,
					mpart_msg_id BIGINT(20) UNSIGNED NOT NULL,
					mpart_user_id BIGINT(20) UNSIGNED NOT NULL,
  					mpart_last_activity datetime NULL DEFAULT NULL,
  					mpart_is_group tinyint(4) NOT NULL DEFAULT '0',
  					mpart_chat_state tinyint(4) NOT NULL DEFAULT '0',
  					mpart_chat_order int(11) NOT NULL DEFAULT '0',
  					mpart_chat_disabled tinyint(4) NOT NULL DEFAULT '0',
  					mpart_muted tinyint(4) NOT NULL DEFAULT '0',
  					PRIMARY KEY (mpart_key),
					INDEX mpart_msg_id (mpart_msg_id),
					INDEX mpart_user (mpart_user_id)
				) ENGINE=InnoDB",
            'message_recipients' => "
				CREATE TABLE message_recipients (
				    mrec_msg_id BIGINT(20) UNSIGNED NOT NULL,
					mrec_parent_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
					mrec_user_id BIGINT(20) UNSIGNED NOT NULL,
					mrec_viewed TINYINT(1) UNSIGNED DEFAULT 0,
					mrec_deleted TINYINT(1) UNSIGNED DEFAULT 0,
					INDEX mrec_msg_id (mrec_msg_id),
					INDEX mrec_user (mrec_user_id)
				) ENGINE=InnoDB",
        );

        return $aRet;
    }

    protected function migrate_database_tables()
    {
        $current = intval(get_option(self::DBVERSION_OPTION_NAME, -1));
        if (-1 === $current) {
            $current = 0;
            add_option(self::DBVERSION_OPTION_NAME, $current, NULL, 'no');
        }

        global $wpdb;
        $wpdb->query('START TRANSACTION');	// start the transaction

        $rollback = FALSE;

        switch ($current)
        {
            case 0:
                $sql = "ALTER TABLE {$wpdb->prefix}peepso_message_participants
				 ADD mpart_read_notif TINYINT(4) NOT NULL DEFAULT '0'";
                $wpdb->query($sql);
            // fall through to next migration, if it exists
            case 3:
                #5685
                $wpdb->suppress_errors();
                $sql = "ALTER TABLE {$wpdb->prefix}peepso_message_recipients DROP PRIMARY KEY";
                $wpdb->query($sql);
                $wpdb->suppress_errors(FALSE);
        }

        // finalize the transaction
        if ($rollback)
            $wpdb->query('ROLLBACK');
        else
            $wpdb->query('COMMIT');				// commit the database changes

        // set the dbversion in the option so we don't keep migrating
        update_option(self::DBVERSION_OPTION_NAME, self::DBVERSION);
    }

    public function get_email_contents()
    {
        $emails = array(

            'email_new_message' => "Hello {userfirstname},

{fromfirstname} sent you a message!

You can view the message here:
{permalink}

Thank you.",



        );

        return $emails;
    }

    /*
     * return default page names information
     */
    protected function get_page_data()
    {
        // default page names/locations
        $aRet = array(
            'messages' => array(
                'title' => __('Messages', 'msgso'),
                'slug' => 'messages',
                'content' => '[peepso_messages]'//'[' . PeepSoMessagesShortcode::SHORTCODE_FRIENDS . ']',
            ),
        );

        return ($aRet);
    }
}
