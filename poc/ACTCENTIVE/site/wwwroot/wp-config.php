<?php

/** Enable W3 Total Cache */

define('WP_CACHE', true); // Added by W3 Total Cache


/** Enable W3 Total Cache */
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

/** overriding HTTP_HOST header */
if (!empty(getenv('AFD_DOMAIN'))) {
	$_SERVER['HTTP_HOST'] = getenv('AFD_DOMAIN');
}

//Using environment variables for memory limits
$wp_memory_limit = (getenv('WP_MEMORY_LIMIT') && preg_match("/^[0-9]+M$/", getenv('WP_MEMORY_LIMIT'))) ? getenv('WP_MEMORY_LIMIT') : '2048M';
$wp_max_memory_limit = (getenv('WP_MAX_MEMORY_LIMIT') && preg_match("/^[0-9]+M$/", getenv('WP_MAX_MEMORY_LIMIT'))) ? getenv('WP_MAX_MEMORY_LIMIT') : '6144M';
/** General WordPress memory limit for PHP scripts*/
define('WP_MEMORY_LIMIT', $wp_memory_limit );
/** WordPress memory limit for Admin panel scripts */
define('WP_MAX_MEMORY_LIMIT', $wp_max_memory_limit );
//Using environment variables for DB connection information
// ** Database settings - You can get this info from your web host ** //
$connectstr_dbhost = getenv('DATABASE_HOST');
$connectstr_dbname = getenv('DATABASE_NAME');
$connectstr_dbusername = getenv('DATABASE_USERNAME');
$connectstr_dbpassword = getenv('DATABASE_PASSWORD');
/** The name of the database for WordPress */
define('DB_NAME', $connectstr_dbname);
/** MySQL database username */
define('DB_USER', $connectstr_dbusername);
/** MySQL database password */
define('DB_PASSWORD',$connectstr_dbpassword);
/** MySQL hostname */
define('DB_HOST', 'p:' . $connectstr_dbhost . ':3306');
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );
/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', 'utf8mb4_unicode_520_ci' );
/** Enabling support for connecting external MYSQL over SSL*/
$mysql_sslconnect = (getenv('DB_SSL_CONNECTION')) ? getenv('DB_SSL_CONNECTION') : 'true';
if (strtolower($mysql_sslconnect) != 'false' && !is_numeric(strpos($connectstr_dbhost, "127.0.0.1")) && !is_numeric(strpos(strtolower($connectstr_dbhost), "localhost"))) {
	define('MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL);
}
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Up-1p[Hn$==y2*m/S0l)^XM~U3-^i&B4&azSvIlY^$YO{-Gbq{+4X@)<b^GEAL7W' );
define( 'SECURE_AUTH_KEY',  ']MM66I~,>pD|DxZ>x_HrU9e(?DmAEm_mo}bXJSQddT<Fm}4o0u>@nvtS4jO.9<}%' );
define( 'LOGGED_IN_KEY',    'zg#4T5RSd.W0:*^,>ZpJDpxBHm)d`sg`T^&(/|tMvgQErH(9l&Yuy 9LA.<*l5,^' );
define( 'NONCE_KEY',        '=2UWth$jV F};-0&,^<_v{[dY]rpa-YwRw,Tuzp.w~+#N-3RXwOE?JGh_*Kg#m 7' );
define( 'AUTH_SALT',        '~XHdDWJX ]n8*b0K7Um$L)vf}aU<b?@?y?~De7:8R!-o}-uS<9sos=ojW78@cG?}' );
define( 'SECURE_AUTH_SALT', 'iO7ftB}*%Jqs4<FkNZNV8N/:txx6&C`#pLqfaG0l+_`#LWy`<:u<;cjy)XMT)~lZ' );
define( 'LOGGED_IN_SALT',   'G@wf`>FlX#8Wd)<yt_,Cf@p`f$/II(9LoKA1aj~:/G2l1A(dI]e`Q0.kDBw?@KYQ' );
define( 'NONCE_SALT',       'GuP*S3 Y r67(Fy1fE1bp1gIDP+/XiTP>G__OEowGr5t.;6(!I+aO]idi/!,^]_A' );
/**#@-*/
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
/** Azure BLOB CDN Storage */
define('MICROSOFT_AZURE_ACCOUNT_NAME', 'actcentived0d358f049');
define('MICROSOFT_AZURE_ACCOUNT_KEY', '6fYASoORr/yk73+zXQe6AnlxnJe9nC2iWXGlxAIBDpzmbRUMuUaYxFwz5iiX0ETpOIaLgr1GY/c8+AStCIh0qw==');
define('MICROSOFT_AZURE_CONTAINER', 'actcentive-production-media');
define('MICROSOFT_AZURE_CNAME', 'https://cdn.actcentive.com');
define('MICROSOFT_AZURE_USE_FOR_DEFAULT_UPLOAD', true);
define('MICROSOFT_AZURE_CACHE_CONTROL', 600);
/** WP Mail SMTP Configuration */
define('WPMS_ON', true);
define('WPMS_MAIL_FROM', 'admin@actcentive.com');
define('WPMS_MAIL_FROM_NAME', 'Actcentive');
define('WPMS_MAILER', 'smtp'); // Possible values 'smtp', 'mail', or 'sendmail' 
define('WPMS_SET_RETURN_PATH', false); // Sets $phpmailer->Sender if true
define('WPMS_SMTP_HOST', 'smtp.office365.com'); // The SMTP mail host
define('WPMS_SSL', 'tls'); // Possible values '', 'ssl', 'tls' - note TLS is not STARTTLS
define('WPMS_SMTP_PORT', 587); // The SMTP server port number
define('WPMS_SMTP_AUTH', true); // True turns on SMTP authentication, false turns it off
define('WPMS_SMTP_USER', 'admin@actcentive.com'); // SMTP authentication username, only used if WPMS_SMTP_AUTH is true
define('WPMS_SMTP_PASS', '53cUr1tY@@#ACTADM2022'); // SMTP authentication password, only used if WPMS_SMTP_AUTH is true
/* AZURE Function running the CRON JOBS */
define('DISABLE_WP_CRON', true);
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', '/home/LogFiles/debug.log' );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );
/* Force SSL on Login Pages */
define( 'FORCE_SSL_LOGIN', true );
/** Force SSL on Admin dashboard */
define( 'FORCE_SSL_ADMIN', true );
/* WordPress database repair */
define( 'WP_ALLOW_REPAIR', false );
define( 'PMPRO_NETWORK_MAIN_DB_PREFIX', 'wp' );
/* WordPress media bin and deletion period */
define( 'MEDIA_TRASH', true );
define( 'EMPTY_TRASH_DAYS', 7 );
define( 'AUTOSAVE_INTERVAL', 180); // Seconds

define( 'SPRO_CACHE_PMXE_META_KEYS', true );
define( 'WP_ENVIRONMENT_TYPE', 'production' );
define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
$base = '/';
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );
define( 'DOMAIN_CURRENT_SITE', $_SERVER['HTTP_HOST'] );
/* That's all, stop editing! Happy blogging. */
/**https://developer.wordpress.org/reference/functions/is_ssl/ */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
	$_SERVER['HTTPS'] = 'on';
$http_protocol='http://';
if (!preg_match("/^localhost(:[0-9])*/", $_SERVER['HTTP_HOST']) && !preg_match("/^127\.0\.0\.1(:[0-9])*/", $_SERVER['HTTP_HOST'])) {
	$http_protocol='https://';
}
//Relative URLs for swapping across app service deployment slots
define('WP_HOME', $http_protocol . $_SERVER['HTTP_HOST']);
define('WP_SITEURL', $http_protocol . $_SERVER['HTTP_HOST']);
define('WP_CONTENT_URL', '/wp-content');
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';