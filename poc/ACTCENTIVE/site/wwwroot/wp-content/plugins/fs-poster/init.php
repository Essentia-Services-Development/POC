<?php
/*
 * Plugin Name: FS Poster
 * Description: FS Poster gives you a great opportunity to auto-publish WordPress posts on Facebook, Instagram, Twitter, Linkedin, Pinterest, Google Business Profile, Telegram, Reddit, Tumblr, VK, OK.ru, Telegram, Medium, Blogger, Plurk and WordPress based sites automatically.
 * Version: 6.5.3
 * Author: FS-Code
 * Author URI: https://www.fs-code.com
 * License: Commercial
 * Text Domain: fs-poster
 */

namespace FSPoster;

use FSPoster\App\Providers\Bootstrap;

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

$networks = [
	'facebook',
	'instagram',
    'threads',
	'twitter',
	'planly',
	'linkedin',
	'pinterest',
	'telegram',
	'reddit',
	'youtube_community',
	'google_b',
	'tumblr',
	'vk',
	'ok',
	'medium',
	'wordpress',
	'webhook',
	'blogger',
	'plurk',
	'xing',
	'discord',
	'mastodon',
];

foreach ( $networks as $network ) {
	require_once __DIR__ . '/App/SocialNetworks/' . $network . '/init.php';
}

new Bootstrap();
