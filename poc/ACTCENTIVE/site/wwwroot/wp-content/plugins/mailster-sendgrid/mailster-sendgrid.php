<?php
/*
Plugin Name: Mailster SendGrid Integration
Plugin URI: https://mailster.co/?utm_campaign=wporg&utm_source=Mailster+SendGrid+Integration&utm_medium=plugin
Description: Uses SendGrid to deliver emails for the Mailster Newsletter Plugin for WordPress.
Version: 2.1
Author: EverPress
Author URI: https://mailster.co
Text Domain: mailster-sendgrid
License: GPLv2 or later
*/


define( 'MAILSTER_SENDGRID_VERSION', '2.1' );
define( 'MAILSTER_SENDGRID_REQUIRED_VERSION', '2.2' );
define( 'MAILSTER_SENDGRID_FILE', __FILE__ );

require_once dirname( __FILE__ ) . '/classes/sendgrid.class.php';
new MailsterSendGrid();

