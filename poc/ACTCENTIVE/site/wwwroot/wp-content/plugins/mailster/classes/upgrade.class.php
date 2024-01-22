<?php

class MailsterUpgrade {

	private $performance = 1;
	private $starttime;
	private $stop_process = false;


	public function __construct() {

		add_action( 'admin_init', array( &$this, 'init' ) );
		add_action( 'wp_ajax_mailster_batch_update', array( &$this, 'run_update' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'mailster_background_update', array( &$this, 'background_update' ) );

		register_activation_hook( 'myMail/myMail.php', array( &$this, 'maybe_deactivate_mymail' ) );
	}

	public function __call( $method, $args ) {

		if ( method_exists( $this, 'do_' . $method ) ) {
			ob_start();
			$return = call_user_func_array( array( &$this, 'do_' . $method ), $args );
			$output = ob_get_contents();
			ob_end_clean();
			if ( ! empty( $output ) ) {
				error_log( $output );
			}
			return $return;
		}
	}

	public function init() {

		global $pagenow;

		$old_version   = get_option( 'mailster_version' );
		$version_match = $old_version == MAILSTER_VERSION;

		if ( ! $version_match ) {

			if ( ! $old_version ) {
				$old_version = get_option( 'mymail_version' );
			}

			if ( version_compare( $old_version, MAILSTER_VERSION, '<' ) ) {
				include MAILSTER_DIR . 'includes/updates.php';
			}

			update_option( 'mailster_version', MAILSTER_VERSION );
		}

		if ( mailster_option( 'db_update_required' ) ) {

			$current_url = home_url( $_SERVER['REQUEST_URI'] );

			$db_version = $this->get_db_version();

			$redirectto  = add_query_arg( 'redirect_to', $current_url, admin_url( 'admin.php?page=mailster_update' ) );
			$update_msg  = '<h2>' . esc_html__( 'An additional update is required for Mailster!', 'mailster' ) . '</h2>';
			$update_msg .= '<p>' . esc_html__( 'To continue using Mailster we need some update on the database structure. Depending on the size of your database this can take a couple of minutes.', 'mailster' ) . '</p>';
			$update_msg .= '<p>' . esc_html__( 'Please continue by clicking the button.', 'mailster' ) . '</p>';
			$update_msg .= '<p><a class="button button-primary" href="' . $redirectto . '" target="_top">' . esc_html__( 'Progress Update now', 'mailster' ) . '</a></p>';

			if ( 'update.php' == $pagenow ) {

				if ( isset( $_GET['success'] )
					&& isset( $_GET['action'] ) && 'activate-plugin' == $_GET['action']
					&& isset( $_GET['plugin'] ) && MAILSTER_SLUG == $_GET['plugin'] ) {

					echo $update_msg;

				}
			} elseif ( isset( $_GET['page'] ) && $_GET['page'] == 'mailster_update' ) {

				if ( $timestamp = wp_next_scheduled( 'mailster_background_update' ) ) {
					wp_clear_scheduled_hook( 'mailster_background_update' );
				}
			} elseif ( ! mailster_option( 'db_update_background' ) ) {
				if ( ! is_network_admin() && isset( $_GET['post_type'] ) && $_GET['post_type'] = 'newsletter' ) {
					mailster_redirect( $redirectto );
					exit;
				} elseif ( ! is_network_admin() && isset( $_GET['page'] ) && 0 === strpos( $_GET['page'], 'mailster_' ) ) {
					mailster_redirect( $redirectto );
					exit;
				} else {
					mailster_remove_notice( 'no_homepage' );
					mailster_notice( $update_msg, 'error', true, 'db_update_required' );
				}
			} else {
				$update_msg  = '<h2>' . esc_html__( 'Mailster database update in progress', 'mailster' ) . '</h2>';
				$update_msg .= '<p>' . esc_html__( 'Mailster is updating the database in the background. The database update process may take a little while, so please be patient.', 'mailster' ) . '</p>';
				$update_msg .= '<p><a class="button" href="' . $redirectto . '" target="_top">' . esc_html__( 'View progress →', 'mailster' ) . '</a></p>';
				mailster_notice( $update_msg, 'info', false, 'background_update' );

				if ( ! wp_next_scheduled( 'mailster_background_update' ) ) {
					wp_schedule_single_event( time() + 10, 'mailster_background_update' );
				}
			}
		} elseif ( ! $version_match ) {

			// update db structure
			if ( MAILSTER_DBVERSION != get_option( 'mailster_dbversion' ) ) {
				mailster()->dbstructure();
			}

			update_option( 'mailster_dbversion', MAILSTER_DBVERSION );

		} elseif ( mailster_option( 'setup' ) ) {

			if ( ! is_network_admin() &&
				( ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'mailster_' ) !== false ) && 'mailster_setup' != $_GET['page'] ) ) {
				mailster_redirect( 'admin.php?page=mailster_setup', 302 );
				exit;
			}
		} elseif ( mailster_option( 'welcome' ) ) {

			if ( ! is_network_admin() &&
				( ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'mailster_' ) !== false ) && 'mailster_welcome' != $_GET['page'] ) ) {
				mailster_redirect( 'admin.php?page=mailster_welcome', 302 );
				exit;
			}
		}
	}


	public function background_update() {

		$actions = $this->get_actions();

		if ( empty( $actions ) ) {
			return;
		}

		foreach ( $actions as $method => $name ) {
			$r = $this->{$method}();
			if ( $r === false ) {
				return;
			}
		}

		$update_msg  = '<h2>' . esc_html__( 'Update finished.', 'mailster' ) . '</h2>';
		$update_msg .= '<p>' . esc_html__( 'Mailster database update complete. Thank you for updating to the latest version!', 'mailster' ) . '</p>';
		mailster_notice( $update_msg, 'info', 20, 'background_update' );
	}


	public function maybe_deactivate_mymail() {

		add_action( 'update_option_active_plugins', array( &$this, 'deactivate_mymail' ) );
	}


	public function deactivate_mymail( $info = true ) {
		if ( is_plugin_active( 'myMail/myMail.php' ) ) {
			if ( $info ) {
				mailster_notice( 'MyMail is now Mailster - Plugin deactivated', 'error', true );
			}
			deactivate_plugins( 'myMail/myMail.php' );
		}
	}


	public function run_update() {

		// cron look
		set_transient( 'mailster_cron_lock', microtime( true ), 360 );

		global $mailster_batch_update_output;

		$this->starttime = microtime( true );

		$id                = $_POST['id'];
		$this->performance = isset( $_POST['performance'] ) ? (int) $_POST['performance'] : $this->performance;

		$actions = $this->get_actions();

		if ( method_exists( $this, 'do_' . $id ) ) {
			ob_start();
			$return[ $id ] = $this->{'do_' . $id}();
			$output        = ob_get_contents();
			ob_end_clean();
			if ( ! empty( $output ) ) {
				$return['output']  = '' . "\n";
				$return['output'] .= str_repeat( '―', 80 ) . "\n";
				$return['output'] .= "\"{$actions[$id]}\" (" . number_format( microtime( true ) - $this->starttime, 2 ) . ' sec. - ' . size_format( memory_get_peak_usage( true ), 2 ) . " usage)\n";
				$return['output'] .= str_repeat( '·', 80 ) . "\n";
				$return['output'] .= trim( strip_tags( $output ) ) . "\n\n";
				// $return['output']  .= str_repeat('―', 80)."\n";

			}
			if ( $this->stop_process ) {
				wp_send_json_error( $return );
			}
		}

		wp_send_json_success( $return );
	}


	/**
	 *
	 *
	 * @param unknown $args
	 */
	public function admin_menu( $args ) {

		$page = add_submenu_page( true, 'Mailster Update', 'Mailster Update', 'manage_options', 'mailster_update', array( &$this, 'page' ) );
		add_action( 'load-' . $page, array( &$this, 'scripts_styles' ) );
	}

	private function get_actions() {

		$db_version = $this->get_db_version();

		$actions = array();

		// pre - Mailster time
		if ( get_option( 'mymail' ) || isset( $_GET['mymail'] ) ) {

			$actions = wp_parse_args(
				array(
					'pre_mailster_updateslug'      => 'Update Plugin Slug',
					'pre_mailster_backuptables'    => 'Backup old Tables',
					'pre_mailster_form_prepare'    => 'Checking Forms',
					'pre_mailster_copytables'      => 'Copy Database Tables',
					'pre_mailster_options'         => 'Copy Options',
					'pre_mailster_updatedpostmeta' => 'Update Post Meta',
					'pre_mailster_movefiles'       => 'Moving Files and Folders',
					'pre_mailster_removeoldtables' => 'Remove old Tables',
					'pre_mailster_removemymail'    => 'Remove old Options',
					'pre_mailster_legacy'          => 'Prepare Legacy mode',
				),
				$actions
			);

			$db_version = get_option( 'mymail_dbversion', 0 );

		} elseif ( ! get_option( 'mailster' ) ) {

				$actions = wp_parse_args(
					array(
						'maybe_install' => 'Installing Mailster',
					),
					$actions
				);

		} else {
			$actions = wp_parse_args(
				array(
					'db_structure' => 'Checking DB structure',
				),
				$actions
			);
		}

		if ( isset( $_GET['hard'] ) ) {
			$db_version = 0;
			$actions    = wp_parse_args( $actions, array( 'remove_db_structure' => 'Removing DB structure' ) );
		}
		if ( isset( $_GET['redo'] ) ) {
			$db_version = 0;
		}

		if ( $db_version < 20140924 ) {
			$actions = wp_parse_args(
				array(
					'update_lists'           => 'updating Lists',
					'update_forms'           => 'updating Forms',
					'update_campaign'        => 'updating Campaigns',
					'update_subscriber'      => 'updating Subscriber',
					'update_list_subscriber' => 'update Lists <=> Subscribers',
					'update_actions'         => 'updating Actions',
					'update_pending'         => 'updating Pending Subscribers',
					'update_autoresponder'   => 'updating Autoresponder',
					'update_settings'        => 'updating Settings',
				),
				$actions
			);
		}

		if ( $db_version < 20150924 ) {
			$actions = wp_parse_args(
				array(
					'update_forms' => 'updating Forms',
				),
				$actions
			);
		}

		if ( $db_version < 20151218 ) {
			$actions = wp_parse_args(
				array(
					'update_db_structure' => 'Changes in DB structure',
				),
				$actions
			);
		}

		if ( $db_version < 20160105 ) {
			$actions = wp_parse_args(
				array(
					'remove_old_data' => 'Removing MyMail 1.x data',
				),
				$actions
			);
		}

		if ( $db_version < 20170201 ) {
			$actions = wp_parse_args( array(), $actions );
		}

		if ( $db_version < 20210901 ) {
			unset( $actions['db_structure'] );
			$actions = wp_parse_args(
				array(
					'legacy_cleanup'                 => 'Legacy Table cleanup',
					'create_primary_keys'            => 'Create primary keys',
					'db_structure'                   => 'Checking DB structure',
					'update_action_table_sent'       => 'Update Action Table - Sent',
					'update_action_table_opens'      => 'Update Action Table - Opens',
					'update_action_table_clicks'     => 'Update Action Table - Clicks',
					'update_action_table_unsubs'     => 'Update Action Table - Unsubscribes',
					'update_action_table_unsubs_msg' => 'Update Unsubscribes Messages',
					'update_action_table_bounces'    => 'Update Action Table - Bounces',
					'update_action_table_bounce_msg' => 'Update Bounce Messages',
					'update_action_table_errors'     => 'Update Action Table - Errors',
					'update_action_table_errors_msg' => 'Update Errors Messages',
					'maybe_fix_indexes'              => 'Fix indexes',
				),
				$actions
			);
		}

		if ( $db_version < 20220727 ) {
			$actions = wp_parse_args(
				array(
					'maybe_fix_indexes' => 'Fix indexes',
					'db_structure'      => 'Checking DB structure',
				),
				$actions
			);
		}

		$actions = wp_parse_args(
			array(
				'db_check' => 'Database integrity',
				'cleanup'  => 'Cleanup',
			),
			$actions
		);

		return array_unique( $actions );
	}

	private function get_db_version() {
		$db_version = get_option( 'mailster_dbversion', MAILSTER_DBVERSION );
		// overwrite if set
		if ( isset( $_GET['dbversion'] ) ) {
			$db_version = (int) $_GET['dbversion'];
			update_option( 'mailster_dbversion', $db_version );
		}
		return $db_version;
	}

	public function scripts_styles() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		do_action( 'mailster_admin_header' );

		wp_enqueue_style( 'mailster-update-style', MAILSTER_URI . 'assets/css/upgrade-style' . $suffix . '.css', array(), MAILSTER_VERSION );
		wp_enqueue_script( 'mailster-update-script', MAILSTER_URI . 'assets/js/upgrade-script' . $suffix . '.js', array( 'mailster-script' ), MAILSTER_VERSION, true );

		$autostart = true;

		$db_version = $this->get_db_version();
		if ( $db_version < 20210131 ) {
			$autostart = false;
		}
		$actions = $this->get_actions();

		wp_localize_script( 'mailster-update-script', 'mailster_updates', $actions );
		wp_localize_script(
			'mailster-update-script',
			'mailster_updates_options',
			array(
				'autostart' => $autostart,
			)
		);
		$performance = isset( $_GET['performance'] ) ? max( 1, (int) $_GET['performance'] ) : 1;
		wp_localize_script( 'mailster-update-script', 'mailster_updates_performance', array( $performance ) );

		remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );
	}

	public function page() {

		global $wpdb;

		?>
	<div class="wrap">
		<h1>Mailster Batch Update</h1>
		<?php wp_nonce_field( 'mailster_nonce', 'mailster_nonce', false ); ?>

		<h3>Some additional updates are required! Please keep this browser tab open until all updates are finished!</h3>
		<p>Your campaigns will continue once the update is finished.</p>
		<hr>
		<div id="mailster-update-info" style="display: none;">
			<div class="notice-error error inline"><p>Make sure to create a backup before run the Mailster Batch Update. If you experience any issues upgrading please reach out to us via our member area <a href="<?php echo mailster_url( 'https://mailster.co/go/register' ); ?>" class="external">here</a>.<br>
			<strong>Important: No data can get lost thanks to our smart upgrade process.</strong></p></div>
			<p>Built: <?php echo date_i18n( 'Y-m-d H:i:s', MAILSTER_BUILT ); ?></p>
			<?php if ( $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mailster_subscribers" ) ) : ?>
			<p>Subscribers Table: <?php echo number_format( $count ); ?> entries</p>
			<?php endif; ?>
			<?php if ( $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mailster_subscriber_meta" ) ) : ?>
			<p>Subscriber Meta Table: <?php echo number_format( $count ); ?> entries</p>
			<?php endif; ?>
			<p>
				<a class="button button-primary button-hero" id="mailster-start-upgrade">Ok, I've got a backup. Start the Update Process</a>
			</p>
		</div>
		<div id="mailster-update-process" style="display:none;">
		<p>If you encounter any problem please get in touch with us by open up a ticket:</p>
		<p><a class="button button-primary" href="<?php echo mailster_url( 'https://mailster.co/support/' ); ?>" target="_blank">Get Support</a></p>

			<div class="alignleft" style="width:54%">

				<div id="output"></div>
				<div id="error-list"></div>
				<form id="mailster-post-upgrade" action="" method="get" style="display: none;">
				<input type="hidden" name="post_type" value="newsletter">
				<input type="hidden" name="page" value="mailster_update">
					<input type="submit" class="hidden button button-small" name="redo" value="redo update" onclick="return confirm('Do you really like to redo the update?');">
				</form>
			</div>

			<div class="alignright" style="width:45%">
				<textarea id="textoutput" class="widefat" rows="30"></textarea>
			</div>

		</div>

	</div>
		<?php
	}


	private function do_remove_db_structure() {

		global $wpdb;

		$tables = mailster()->get_tables();

		foreach ( $tables as $table ) {
			$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %s', "{$wpdb->prefix}mailster_$table" ) );
		}

		return true;
	}


	private function do_remove_old_data() {

		global $wpdb;

		if ( $count = $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'mailster-campaign' LIMIT 1000" ) ) {
			echo 'old Campaign Data removed.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'mailster-campaigns' LIMIT 1000" ) ) {
			echo 'old Campaign related User Data removed.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'mailster-userdata' LIMIT 10000" ) ) {
			echo 'old User Data removed.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'mailster-data' LIMIT 1000" ) ) {
			echo 'old User Data removed.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE m FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->postmeta} AS m ON p.ID = m.post_id WHERE p.post_type = 'subscriber' AND m.post_id" ) ) {
			echo 'old User related data removed.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE a,b,c FROM {$wpdb->term_taxonomy} AS a LEFT JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id JOIN {$wpdb->term_taxonomy} AS c ON c.term_taxonomy_id = a.term_taxonomy_id WHERE a.taxonomy = 'newsletter_lists'" ) ) {
			echo 'old Lists removed.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'subscriber' LIMIT 10000" ) ) {
			echo $count . ' old User removed.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'mailster_confirms'" ) ) {
			echo $count . ' old Pending User removed.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'mailster_autoresponders'" ) ) {
			echo $count . ' old Autoresponder Data.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'mailster_subscribers_count'" ) ) {
			echo $count . ' old Cache.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'mailster_bulk_%'" ) ) {
			echo $count . ' old import data.' . "\n";
			return false;
		}
		if ( $count = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name IN ('mailster_countries', 'mailster_cities')" ) ) {
			echo $count . ' old data.' . "\n";
			return false;
		}

		return true;
	}


	private function do_pre_mailster_updateslug() {

		$this->deactivate_mymail( false );

		$active_plugins = get_option( 'active_plugins', array() );

		$slug = 'mailster/mailster.php';

		if ( in_array( $slug, $active_plugins ) ) {
			return true;
		}

		$wp_filesystem = mailster_require_filesystem();

		$old_location = MAILSTER_DIR . '/myMail.php';
		$new_location = MAILSTER_DIR . '/mailster.php';

		if ( ! $wp_filesystem->move( $old_location, $new_location, true ) ) {
			rename( $old_location, $new_location );
		}

		$old_location = MAILSTER_DIR;
		$new_location = dirname( MAILSTER_DIR ) . '/mailster';

		if ( ! $wp_filesystem->move( $old_location, $new_location, true ) ) {
			rename( $old_location, $new_location );
		}

		deactivate_plugins( array( MAILSTER_SLUG ), false, true );
		activate_plugin( $slug, '', false, true );

		$active_plugins = get_option( 'active_plugins', array() );

		if ( in_array( $slug, $active_plugins ) ) {
			return true;
		}

		return false;
	}


	private function do_pre_mailster_form_prepare() {

		global $wpdb;

		if ( $formstructure = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mymail_forms WHERE ID = 0" ) ) {
			$wpdb->query( "DELETE FROM {$wpdb->prefix}mymail_forms WHERE ID = 0" );
			update_option( '_mailster_formstructure', $formstructure );
		}

		return true;
	}


	private function do_pre_mailster_options() {

		global $wpdb;

		echo 'Converting Options.' . "\n";

		$options = $wpdb->get_results( "SELECT option_name, option_value, autoload FROM {$wpdb->options} WHERE option_name LIKE '%mymail%'" );

		foreach ( $options as $option ) {
			$option->option_name = str_replace( 'mymail', 'mailster', $option->option_name );
			$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->options} (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s ) ON DUPLICATE KEY UPDATE option_value = values(option_value)", $option->option_name, $option->option_value, $option->autoload ) );
		}

		$tables = mailster()->get_tables( true );

		set_transient( '_mailster_mymail', true, MONTH_IN_SECONDS );
		update_option( 'mailster', time() );
		update_option( 'mailster_setup', time() );
		update_option( 'mailster_templates', '' );
		$wpdb->query( "UPDATE {$wpdb->options} SET autoload = 'no' WHERE option_name IN ('mailster_templates', 'mailster_cron_lasthit')" );

		mailster_update_option( 'webversion_bar', true );

		if ( wp_next_scheduled( 'mymail_cron_worker' ) ) {
			wp_clear_scheduled_hook( 'mymail_cron_worker' );
		}
		if ( wp_next_scheduled( 'mymail_cron' ) ) {
			wp_clear_scheduled_hook( 'mymail_cron' );
		}

		$post_notice = '';

		if ( mailster_option( 'cron_service' ) == 'cron' ) {
			$post_notice .= '<p><strong>The URL to the cron has changed!</strong></p><a class="button button-primary" href="edit.php?post_type=newsletter&page=mailster_settings#cron">Get the new URL</a>';
		}
		if ( defined( 'MYMAIL_MU_CRON' ) && MYMAIL_MU_CRON ) {
			$post_notice .= '<p><strong>The MyMail - MU Cron in the mu folder is no longer in use!</strong></p><a class="button button-primary" href="plugins.php?plugin_status=mustuse">You can remove it!</a>';
		}

		if ( ! empty( $post_notice ) ) {
			mailster_notice( $post_notice, 'error', false, 'update_post_notice' );
		}

		usleep( 1000 );
		return true;
	}


	private function do_pre_mailster_backuptables() {

		global $wpdb;

		$tables = mailster()->get_tables();

		foreach ( $tables as $table ) {

			if ( ! $this->table_exists( "{$wpdb->prefix}mymail_bak_{$table}" ) ) {

				if ( $count = $wpdb->query( "CREATE TABLE {$wpdb->prefix}mymail_bak_{$table} LIKE {$wpdb->prefix}mymail_{$table}" ) ) {
					echo 'Backup table ' . $table . '.' . "\n";
					if ( $count = $wpdb->query( "INSERT {$wpdb->prefix}mymail_bak_{$table} SELECT * FROM {$wpdb->prefix}mymail_{$table}" ) ) {
						echo 'Backup data ' . $table . '.' . "\n";
					}
					return false;
				}
			}
		}

		usleep( 1000 );
		return true;
	}


	private function do_pre_mailster_copytables() {

		global $wpdb;

		$wpdb->suppress_errors();

		$tables = mailster()->get_tables();

		foreach ( $tables as $table ) {

			if ( $this->table_exists( "{$wpdb->prefix}mymail_{$table}" ) ) {

				if ( ! $this->table_exists( "{$wpdb->prefix}mailster_{$table}" ) ) {
					if ( $count = $wpdb->query( "CREATE TABLE {$wpdb->prefix}mailster_{$table} LIKE {$wpdb->prefix}mymail_{$table}" ) ) {
						echo 'Copy table structure ' . $table . '.' . "\n";
						return false;
					}
				}
				if ( $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}mailster_{$table}" ) ) {
					echo 'Clean ' . $table . '.' . "\n";
				}
				if ( $wpdb->query( "INSERT {$wpdb->prefix}mailster_{$table} SELECT * FROM {$wpdb->prefix}mymail_{$table}" ) ) {
					echo 'Copy data ' . $table . '.' . "\n";
				}
			}
		}

		usleep( 1000 );
		return true;
	}

	private function do_pre_mailster_updatedpostmeta() {

		global $wpdb;

		if ( $formstructure = get_option( '_mailster_formstructure' ) ) {
			unset( $formstructure->ID );
			$wpdb->insert( "{$wpdb->prefix}mailster_forms", (array) $formstructure );
			delete_option( '_mailster_formstructure' );
			$form_id = $wpdb->insert_id;

			if ( is_numeric( $form_id ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mailster_form_fields SET form_id = %d WHERE form_id = 0", $form_id ) );
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mailster_forms_lists SET form_id = %d WHERE form_id = 0", $form_id ) );
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET `post_content` = replace(post_content, %s, %s)", '[newsletter_signup_form id=0]', '[newsletter_signup_form id=' . $form_id . ']' ) );

				$old_profile_form = mailster_option( 'profile_form' );
				if ( 0 == $old_profile_form ) {
					mailster_update_option( 'profile_form', $form_id );
				}
			}
		}

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET `meta_key` = replace(meta_key, %s, %s)", 'mymail', 'mailster' ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET `post_content` = replace(post_content, %s, %s)", 'mymail_image_placeholder', 'mailster_image_placeholder' ) );

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mailster_forms SET `style` = replace(style, %s, %s)", 'mymail', 'mailster' ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mailster_forms SET `custom_style` = replace(custom_style, %s, %s)", 'mymail', 'mailster' ) );

		$autoresponder_data = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = '_mailster_autoresponder' AND meta_value LIKE '%mymail%'" );

		foreach ( $autoresponder_data as $data ) {

			$meta_value = maybe_unserialize( $data->meta_value );

			if ( isset( $meta_value['action'] ) ) {
				$meta_value['action'] = str_replace( 'mymail', 'mailster', $meta_value['action'] );
			}
			update_post_meta( $data->post_id, $data->meta_key, $meta_value );
		}

		return true;
	}


	private function do_pre_mailster_movefiles() {

		global $wpdb;

		$wp_filesystem = mailster_require_filesystem();

		$new_location = MAILSTER_UPLOAD_DIR;
		$old_location = dirname( MAILSTER_UPLOAD_DIR ) . '/myMail';

		if ( is_dir( $new_location ) ) {
			if ( ! $wp_filesystem->move( $new_location, $new_location . '_bak', true ) ) {
				rename( $new_location, $new_location . '_bak' );
			}
		}

		if ( is_dir( $old_location ) && ! is_dir( $new_location ) ) {

			if ( ! $wp_filesystem->move( $old_location, $new_location, true ) ) {
				rename( $old_location, $new_location );
			}
		}

		$new_location_url = preg_replace( '/https?:/', '', MAILSTER_UPLOAD_URI );
		$old_location_url = preg_replace( '/https?:/', '', dirname( MAILSTER_UPLOAD_URI ) . '/myMail' );

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET `post_content` = replace(post_content, %s, %s)", $old_location_url, $new_location_url ) );

		$new_location = trailingslashit( MAILSTER_UPLOAD_DIR . '/backgrounds' );
		$old_location = trailingslashit( MAILSTER_DIR . 'assets/img/bg' );

		$new_location_url = preg_replace( '/https?:/', '', MAILSTER_UPLOAD_URI . '/backgrounds/' );
		$old_location_url = preg_replace( '/https?:/', '', dirname( MAILSTER_URI ) . '/myMail/assets/img/bg/' );

		if ( ! is_dir( $new_location ) ) {
			wp_mkdir_p( $new_location );
		}

		$to_copy = list_files( $old_location, 1 );
		foreach ( $to_copy as $file ) {
			if ( ! $wp_filesystem->copy( $file, $new_location . basename( $file ), false ) ) {
				copy( $file, $new_location . basename( $file ) );
			}
		}

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET `post_content` = replace(post_content, %s, %s)", $old_location_url, $new_location_url ) );

		return true;
	}


	private function do_pre_mailster_removeoldtables() {

		global $wpdb;

		$tables = mailster()->get_tables();

		foreach ( $tables as $table ) {

			if ( $this->table_exists( "{$wpdb->prefix}mymail_{$table}" ) ) {

				if ( $count = $wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %s', "{$wpdb->prefix}mymail_{$table}" ) ) ) {
					echo 'old ' . $table . ' table removed.' . "\n";
					return false;
				}
			}
		}

		usleep( 1000 );
		return true;
	}


	private function do_pre_mailster_removebackup() {

		global $wpdb;

		$tables = mailster()->get_tables();

		foreach ( $tables as $table ) {

			if ( $this->table_exists( "{$wpdb->prefix}mymail_bak_{$table}" ) ) {

				if ( $count = $wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %s', "{$wpdb->prefix}mymail_bak_{$table}" ) ) ) {
					echo 'Backup table ' . $table . ' removed.' . "\n";
					return false;
				}
			}
		}

		usleep( 1000 );
		return true;
	}


	private function do_pre_mailster_removemymail() {

		global $wpdb;

		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE '_transient_mymail_%'" );
		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE '_transient_timeout_mymail_%'" );
		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE '_transient__mymail_%'" );
		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE '_transient_timeout__mymail_%'" );
		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE '_transient_timeout__mymail_%'" );
		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE 'mymail_%'" );
		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` = 'mymail'" );

		usleep( 1000 );
		return true;
	}


	private function do_pre_mailster_legacy() {

		$wp_filesystem = mailster_require_filesystem();

		$this->deactivate_mymail( false );

		if ( ! is_dir( WP_PLUGIN_DIR . '/myMail' ) ) {
			wp_mkdir_p( WP_PLUGIN_DIR . '/myMail' );
		}

		$from = MAILSTER_DIR . '/form.php';
		$to   = WP_PLUGIN_DIR . '/myMail/form.php';

		if ( ! $wp_filesystem->copy( $from, $to, true ) ) {
			copy( $from, $to );
		}

		$from = MAILSTER_DIR . '/cron.php';
		$to   = WP_PLUGIN_DIR . '/myMail/cron.php';

		if ( ! $wp_filesystem->copy( $from, $to, true ) ) {
			copy( $from, $to );
		}

		$content = "<?php\n/*\nPlugin Name: MyMail Legacy Code Helper\nDescription: Helper for legacy external forms and cron of Mailster (former MyMail). You can delete this 'plugin' if you have no external forms or subscriber buttons or you have update them already to the new version.\n */\ndie('There\'s no need to activate this plugin! If you experience any issues upgrading please reach out to us via our member area <a href=\"" . mailster_url( 'https://mailster.co/go/register' ) . "\" target=\"_blank\">here</a>.');\n";

		if ( ! $wp_filesystem->put_contents( WP_PLUGIN_DIR . '/myMail/deprecated.php', $content, FS_CHMOD_FILE ) ) {
			mailster( 'helper' )->file_put_contents( WP_PLUGIN_DIR . '/myMail/deprecated.php', $content );
		}

		if ( file_exists( WP_PLUGIN_DIR . '/myMail/myMail.php' ) ) {
			if ( ! $wp_filesystem->delete( WP_PLUGIN_DIR . '/myMail/MyMail.php' ) ) {
				unlink( WP_PLUGIN_DIR . '/myMail/MyMail.php' );
			}
		}

		usleep( 1000 );
		return true;
	}


	private function do_pre_mailster_checkhooks() {

		global $wp_filter;
		$hooks = array_values( preg_grep( '/^mymail/', array_keys( $wp_filter ) ) );

		if ( ! empty( $hooks ) ) {
			$msg = '<p>Following deprecated MyMail hooks were found and should get replaced:</p><ul>';
			foreach ( $hooks as $hook ) {
				echo 'Hook ' . $hook . ' found.' . "\n";
				$msg .= '<li><code>' . $hook . '</code> => <code>' . str_replace( 'mymail', 'mailster', $hook ) . '</code></li>';
			}
			$msg .= '</ul>';

			mailster_notice( $msg, 'error', false, 'old_hooks' );

		}

		usleep( 1000 );
		return true;
	}




	private function do_maybe_install() {
		mailster()->install();
		return true;
	}

	private function do_db_structure() {

		mailster()->dbstructure( true, true, true, true );
		return true;
	}


	private function do_db_check() {

		global $wpdb;

		ob_start();

		mailster()->dbstructure( true, true, true, false );

		$output = ob_get_contents();

		ob_end_clean();

		if ( false === mailster( 'subscribers' )->wp_id() ) {
			$status = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $wpdb->users ) );
			if ( isset( $status->Collation ) ) {
				$tables = mailster()->get_tables( true );

				foreach ( $tables as $table ) {
					$sql = $wpdb->prepare( 'ALTER TABLE %s CONVERT TO CHARACTER SET utf8mb4 COLLATE %s', $table, $status->Collation );
					if ( false !== $wpdb->query( $sql ) ) {
						echo "'$table' converted to {$status->Collation}.\n";
					}
				}
			}
		}

		if ( ! $output ) {
			echo 'No DB structure problem found.' . "\n";
		}

		if ( function_exists( 'maybe_convert_table_to_utf8mb4' ) ) {
			$tables = mailster()->get_tables( true );

			foreach ( $tables as $table ) {
				maybe_convert_table_to_utf8mb4( $table );
			}
		}

		return true;
	}


	private function do_legacy_cleanup() {

		global $wpdb;

		if ( $this->table_exists( "{$wpdb->prefix}mailster_actions" ) ) {
			if ( $count = $wpdb->query( "DELETE a FROM {$wpdb->prefix}mailster_actions AS a WHERE campaign_id IS NULL" ) ) {
				echo 'Removed ' . number_format( $count ) . " actions where's no campaign\n";
				return false;

			}
			if ( $campaing_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'newsletter'" ) ) {
				if ( $count = $wpdb->query( "DELETE a FROM {$wpdb->prefix}mailster_actions AS a WHERE campaign_id NOT IN (" . implode( ',', $campaing_ids ) . ')' ) ) {
					echo 'Removed ' . number_format( $count ) . " actions where's no campaign\n";
					return false;

				}
			}
		}

		if ( $count = $wpdb->query( "DELETE a FROM {$wpdb->prefix}mailster_subscriber_meta AS a WHERE a.meta_value = '' OR a.subscriber_id = 0" ) ) {
			echo 'Removed ' . number_format( $count ) . " rows of unassigned subscriber meta\n";
			return false;
		}

		if ( $count = $wpdb->query( "DELETE a FROM {$wpdb->prefix}mailster_actions AS a WHERE campaign_id IS NULL AND subscriber_id IS NULL " ) ) {
			echo 'Removed ' . number_format( $count ) . " actions where's no campaign or subscriber\n";
			return false;
		}

		return true;
	}


	public function create_primary_keys( $tables = null ) {

		$return = '';

		ob_start();
		while ( ! $this->do_create_primary_keys( $tables ) ) {
		}
		$return .= ob_get_contents();
		ob_end_clean();

		return $return;
	}


	private function do_create_primary_keys( $tables = null ) {

		global $wpdb;

		if ( is_null( $tables ) ) {
			$tables = mailster()->get_tables();
		}
		$tables = (array) $tables;

		foreach ( $tables as $table ) {
			$tablename = $wpdb->prefix . 'mailster_' . $table;
			if ( 'lists_subscribers' == $table ) {
				continue;
			}
			if ( 'tags_subscribers' == $table ) {
				continue;
			}
			if ( 'forms_lists' == $table ) {
				continue;
			}
			if ( 'forms_tags' == $table ) {
				continue;
			}
			if ( ! $this->table_exists( $tablename ) ) {
				continue;
			}
			if ( $wpdb->get_var( "SHOW INDEXES FROM {$tablename} WHERE Key_name = 'PRIMARY'" ) ) {
				continue;
			}

			if ( ! $this->create_primary_key( $tablename ) ) {
				return false;
			}

			usleep( 1000 );

			if ( ! $this->column_exists( 'ID', $tablename ) ) {
				echo 'Not able to create primary Key for  "' . $tablename . '".' . "\n";
			} else {
				echo 'Primary Key for "' . $tablename . '" created.' . "\n";
			}
			return false;
		}

		return true;
	}


	private function create_primary_key( $table ) {

		global $wpdb;

		if ( $wpdb->get_var( "SHOW INDEXES FROM {$table} WHERE Key_name = 'PRIMARY'" ) ) {
			return true;
		}

		if ( ! ( $method = get_transient( 'mailster_create_primary_key_method_' . $table ) ) ) {
			$method = 1;
			set_transient( 'mailster_create_primary_key_method_' . $table, $method, HOUR_IN_SECONDS );
		}

		switch ( $method ) {
			case 1:
				if ( ! $this->column_exists( 'ID', $table ) ) {
					$wpdb->query( "ALTER TABLE {$table} ADD `ID` bigint(20) unsigned NOT NULL FIRST" );
					if ( $wpdb->last_error ) {
						echo $wpdb->last_error . "\n";
						set_transient( 'mailster_create_primary_key_method_' . $table, 2, HOUR_IN_SECONDS );
						return false;
					}
				}
				$wpdb->query( 'SET @a = 0;' );
				$wpdb->query( "UPDATE {$table} SET ID = @a:=@a+1;" );
				$wpdb->query( "ALTER TABLE {$table} MODIFY COLUMN `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY" );
				break;

			case 2:
				if ( ! $this->column_exists( 'ID', $table ) ) {
					$wpdb->query( "ALTER TABLE {$table} ADD `ID` bigint(20) unsigned NOT NULL FIRST" );
					if ( $wpdb->last_error ) {
						echo $wpdb->last_error . "\n";
						set_transient( 'mailster_create_primary_key_method_' . $table, 3, MINUTE_IN_SECONDS );
						return false;
					}
				}
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE ID = 0" );

				if ( $count ) {

					$limit = max( 1000, min( 500000, round( $count / 3 ) ) );

					$wpdb->query( "SELECT @a := max(ID) FROM {$table}" );
					$wpdb->query( "UPDATE {$table} SET ID = @a:=@a+1 WHERE ID = 0 LIMIT {$limit};" );

					return false;
				}

				$wpdb->query( "ALTER TABLE {$table} MODIFY COLUMN `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY" );
				break;

			case 3:
				$temp_table = $table . '_temp';

				echo '# Not able to create primary keys.' . "\n";
				echo "# Please use this SQL statement to do it manually via phpMyAdmin and come back here once it's finished." . "\n";
				echo '# Contact support if you still have issue: https://mailster.co/support.' . "\n\n";
				echo "CREATE TABLE {$temp_table} LIKE {$table};" . "\n";
				echo "ALTER TABLE {$temp_table} ADD `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;" . "\n";
				echo "INSERT INTO {$temp_table} SELECT NULL, a.* FROM {$table} AS a;" . "\n";
				echo "RENAME TABLE {$table} TO {$table}_old, {$temp_table} TO {$table};" . "\n";

				// echo "DROP TABLE {$table}_old;" . "\n";

				$this->please_die();
				return false;
		}

		return $wpdb->get_var( "SHOW INDEXES FROM {$table} WHERE Key_name = 'PRIMARY'" );
	}


	private function do_maybe_fix_indexes() {

		global $wpdb;

		$tables = mailster()->get_table_structure();

		foreach ( $tables as $table ) {
			if ( preg_match_all( '/UNIQUE KEY `(\w+)` \(([a-z_ ,`]+)\)/', $table, $unique_keys, PREG_SET_ORDER ) ) {
				$table_name = preg_replace( '/(.*?)CREATE TABLE (' . preg_quote( $wpdb->prefix . 'mailster_' ) . '[a-z_]+)(.*)/s', '$2', $table );
				foreach ( $unique_keys as $unique_key ) {
					$index     = $unique_key[1];
					$fields    = array_map( 'trim', explode( ',', str_replace( '`', '', $unique_key[2] ) ) );
					$rows      = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX IN `{$table_name}` WHERE Key_name = %s", $index ) );
					$col_names = wp_list_pluck( $rows, 'Column_name' );
					$diff      = array_diff( $fields, $col_names );
					if ( ! empty( $diff ) ) {
						echo 'Remove index for "' . $table_name . '".' . "\n";
						$wpdb->query( "ALTER TABLE `{$table_name}` DROP INDEX {$index}" );
					}
				}
			}
		}

		return true;
	}


	private function do_update_action_table_sent() {
		return $this->update_action_table( 'sent' );
	}
	private function do_update_action_table_opens() {
		return $this->update_action_table( 'opens' );
	}
	private function do_update_action_table_clicks() {
		return $this->update_action_table( 'clicks', array( 'link_id' ) );
	}
	private function do_update_action_table_unsubs() {
		return $this->update_action_table( 'unsubs' );
	}
	private function do_update_action_table_bounces() {
		return $this->update_action_table( 'bounces' );
	}
	private function do_update_action_table_errors() {
		return $this->update_action_table( 'errors' );
	}

	private function update_action_table( $table, $fields = array() ) {

		global $wpdb;

		$types = array(
			'sent'    => 1,
			'opens'   => 2,
			'clicks'  => 3,
			'unsubs'  => 4,
			'bounces' => array( 5, 6 ),
			'errors'  => 7,
		);

		if ( ! isset( $types[ $table ] ) ) {
			return true;
		}

		$type = implode( ', ', (array) $types[ $table ] );

		$fields        = array_merge( array( 'subscriber_id', 'campaign_id', 'timestamp', 'count' ), $fields );
		$fields_string = implode( ', ', $fields );
		$legacy_fields = implode( ', ', $fields );
		$select_string = implode( ', ', $fields );

		if ( 'bounces' == $table ) {
			$fields_string .= ', hard';
			$select_string .= ', IF(a.type = 5, 0, 1)';
		}

		if ( ! $limit = get_transient( 'mailster_update_action_table_' . $table ) ) {
			$limit = 100;
		}

		if ( ! ( $method = get_transient( 'mailster_update_action_table_method_' . $table ) ) ) {
			$method = 1;
			set_transient( 'mailster_update_action_table_method_' . $table, $method, HOUR_IN_SECONDS );
		}

		if ( ! ( $start_id = get_transient( 'mailster_update_action_table_start_id_' . $method . $table ) ) ) {
			$start_id = 0;
		}
		if ( ! ( $total = get_transient( 'mailster_update_action_table_total_' . $table ) ) ) {
			$total = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}mailster_actions` AS a WHERE a.type IN($type)" );
			$moved = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}mailster_action_$table`" );
			if ( $moved >= $total ) {
				echo 'Table "' . $table . '" finished.' . "\n";
				return true;
			}
			set_transient( 'mailster_update_action_table_total_' . $table, $total, HOUR_IN_SECONDS );
		}
		if ( ! ( $total_actions = get_transient( 'mailster_update_action_table_total' ) ) ) {
			$total_actions = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}mailster_actions`" );
			set_transient( 'mailster_update_action_table_total', $total_actions, HOUR_IN_SECONDS );
		}

		$count = 0;

		if ( $total ) {

			switch ( $method ) {

				// method #1 faster with less entries
				case 1:
					$legacy_select = 'NULL, ' . str_replace( ', count', ", '0', count", $select_string );
					if ( 'unsubs' == $table || 'bounces' == $table || 'errors' == $table ) {
						$legacy_select .= ", ''";
					}

					$sql   = "INSERT IGNORE INTO `{$wpdb->prefix}mailster_action_{$table}` SELECT {$legacy_select} FROM `{$wpdb->prefix}mailster_actions` AS a WHERE a.type IN ({$type});";
					$count = $wpdb->query( $sql );

					break;

				// method #2 faster with less entries
				case 2:
					if ( ! $this->column_exists( 'ID', "{$wpdb->prefix}mailster_actions" ) ) {
						$this->create_primary_key( "{$wpdb->prefix}mailster_actions" );
						return false;
					}

					$compare = '';
					foreach ( $fields as $field ) {
						$compare .= ' AND a.' . $field . ' <=> b.' . $field;
					}

					$sql = "SELECT a.ID FROM `{$wpdb->prefix}mailster_actions` AS a LEFT JOIN `{$wpdb->prefix}mailster_action_$table` AS b ON 1 {$compare} WHERE b.ID IS NULL AND a.type IN ($type) AND a.ID > %d ORDER BY a.ID ASC LIMIT 1";

					// get first missing primary key
					if ( $key = $wpdb->get_var( $wpdb->prepare( $sql, $start_id ) ) ) {

						$sql = "INSERT IGNORE INTO `{$wpdb->prefix}mailster_action_$table` ($fields_string) SELECT $select_string FROM `{$wpdb->prefix}mailster_actions` AS a WHERE a.ID >= %d AND a.type IN ($type) ORDER BY a.ID ASC LIMIT %d;";

						$sql = $wpdb->prepare( $sql, $key, $limit );

						$count = $wpdb->query( $sql );

						set_transient( 'mailster_update_action_table_start_id_' . $method . $table, $key );

					}
					break;

				// method #3 more reliable with more entries
				case 3:
					if ( ! $this->column_exists( 'ID', "{$wpdb->prefix}mailster_actions" ) ) {
						$this->create_primary_key( "{$wpdb->prefix}mailster_actions" );
						return false;
					}

					// get old data
					$old_data = $wpdb->get_results( $wpdb->prepare( "SELECT ID, type, $legacy_fields FROM `{$wpdb->prefix}mailster_actions` AS a WHERE a.ID > %d AND a.type IN ($type) ORDER BY a.ID ASC LIMIT %d", $start_id, $limit ), ARRAY_A );

					$insert_data = array();

					// insert old data and remember last ID for the next start ID
					foreach ( $old_data as $data ) {
						$start_id = $data['ID'];
						unset( $data['ID'] );
						if ( $data['type'] == 5 ) {
							$data['hard'] = 0;
						} elseif ( $data['type'] == 6 ) {
							$data['hard'] = 1;
						}
						unset( $data['type'] );

						$string = "('" . implode( "', '", array_values( $data ) ) . "')";
						$string = str_replace( "''", 'NULL', $string );

						$insert_data[] = $string;

					}

					$chunks = array_chunk( $insert_data, 5000 );

					foreach ( $chunks as $insert ) {
						$sql = "INSERT IGNORE INTO `{$wpdb->prefix}mailster_action_$table` ($fields_string) VALUES";

						$sql .= ' ' . implode( ',', $insert );

						if ( false !== ( $c = $wpdb->query( $sql ) ) ) {
							$count += $c;
						}
					}

					set_transient( 'mailster_update_action_table_start_id_' . $method . $table, $start_id );

					break;

				// method #4 like #3 with timestamp (no primary key)
				case 4:
					// get old data
					$old_data = $wpdb->get_results( $wpdb->prepare( "SELECT type, $legacy_fields FROM `{$wpdb->prefix}mailster_actions` AS a WHERE a.timestamp >= %d AND a.type IN ($type) ORDER BY a.timestamp ASC LIMIT %d", $start_id, $limit ), ARRAY_A );

					$insert_data = array();

					foreach ( $old_data as $data ) {
						$start_id = $data['timestamp'];
						if ( $data['type'] == 5 ) {
							$data['hard'] = 0;
						} elseif ( $data['type'] == 6 ) {
							$data['hard'] = 1;
						}
						unset( $data['type'] );

						$string = "('" . implode( "', '", array_values( $data ) ) . "')";
						$string = str_replace( "''", 'NULL', $string );

						$insert_data[] = $string;

					}

					$chunks = array_chunk( $insert_data, 5000 );

					foreach ( $chunks as $insert ) {
						$sql = "INSERT IGNORE INTO `{$wpdb->prefix}mailster_action_$table` ($fields_string) VALUES";

						$sql .= ' ' . implode( ',', $insert );

						if ( false !== ( $c = $wpdb->query( $sql ) ) ) {
							$count += $c;
						}
					}

					set_transient( 'mailster_update_action_table_start_id_' . $method . $table, $start_id );

					break;

				// method #5  backup for tables with more entries
				case 5:
					if ( ! $this->column_exists( 'exported', "{$wpdb->prefix}mailster_actions" ) ) {
						$wpdb->query( "ALTER TABLE {$wpdb->prefix}mailster_actions ADD `exported` bigint(20) unsigned NULL FIRST" );
						return false;
					}

					$old_data = $wpdb->get_results( $wpdb->prepare( "SELECT $select_string FROM `{$wpdb->prefix}mailster_actions` AS a WHERE a.exported IS NULL AND a.type IN ($type) ORDER by a.timestamp ASC LIMIT %d", $limit ), ARRAY_A );

					foreach ( $old_data as $data ) {

						$sql = "INSERT IGNORE INTO `{$wpdb->prefix}mailster_action_$table` ($fields_string) VALUES ('" . implode( "', '", array_values( $data ) ) . "')";

						$update_sql = $wpdb->prepare( "UPDATE `{$wpdb->prefix}mailster_actions` SET exported = %d WHERE type IN ($type)", time() );
						foreach ( $data as $key => $value ) {
							$update_sql .= " AND $key = '$value'";
						}
						if ( $wpdb->query( $sql ) && $wpdb->query( $update_sql ) ) {
							++$count;
						}
					}
					break;

				default:
					echo 'Method invalid.' . "\n";
					usleep( 5000 );

					return false;
					break;
			}

			$moved = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}mailster_action_$table`" );
			$moved = min( $moved, $total );

			$p = min( 1, $moved / $total );

			echo number_format( $count ) . ' moved.' . "\n";
			echo number_format( $moved ) . ' of ' . number_format( $total ) . ' (' . number_format( $p * 100, 2 ) . '%) in total from table ' . $table . ".\n";
			if ( $moved < $total ) {
				// get the limit from the 10th of the total within a range
				$limit = max( 1000, min( 50000, round( $total / 10 ) ) );
				set_transient( 'mailster_update_action_table_' . $table, $limit );
				return false;
			}

			delete_transient( 'mailster_update_action_table_start_id_' . $method . $table );
			delete_transient( 'mailster_update_action_table_method_' . $table );
			delete_transient( 'mailster_update_action_table_' . $table );

		}
		echo 'Table "' . $table . '" finished.' . "\n";
		usleep( 200 );

		return true;
	}

	private function do_update_action_table_unsubs_msg() {
		return $this->do_update_action_table_msg( 'unsubscribe' );
	}

	private function do_update_action_table_bounce_msg() {
		return $this->do_update_action_table_msg( 'bounce' );
	}

	private function do_update_action_table_errors_msg() {
		return $this->do_update_action_table_msg( 'error' );
	}

	private function do_update_action_table_msg( $type ) {
		global $wpdb;

		$types = array(
			'error'       => 'errors',
			'bounce'      => 'bounces',
			'unsubscribe' => 'unsubs',
		);

		if ( ! isset( $types[ $type ] ) ) {
			return true;
		}

		$table = $types[ $type ];

		$sql = "SELECT * FROM `{$wpdb->prefix}mailster_subscriber_meta` AS a LEFT JOIN `{$wpdb->prefix}mailster_action_$table` AS b ON a.subscriber_id <=> b.subscriber_id AND a.campaign_id <=> b.campaign_id WHERE a.meta_key = %s AND b.timestamp IS NOT NULL AND a.meta_value != b.text LIMIT 1000";

		$result = $wpdb->get_results( $wpdb->prepare( $sql, $type ) );

		$count = count( $result );

		if ( ! $count ) {
			echo 'Moving ' . $type . ' messages finished.' . "\n";
			$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}mailster_subscriber_meta` WHERE meta_key = %s", $type ) );
			return true;
		}

		foreach ( $result as $entry ) {
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}mailster_action_$table` SET text = %s WHERE subscriber_id = %d AND campaign_id = %d AND timestamp = %d", $entry->meta_value, $entry->subscriber_id, $entry->campaign_id, $entry->timestamp ) );
		}

		echo number_format( $count ) . " messages moved to table $table.\n";
		usleep( 1000 );
		return false;
	}


	private function do_delete_legacy_action_table() {
		global $wpdb;

		if ( $this->table_exists( "{$wpdb->prefix}mailster_actions" ) ) {

			// check for data younger than one year
			$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailster_actions WHERE timestamp > %d", time() - YEAR_IN_SECONDS );

			// no data => delete
			if ( ! $wpdb->get_var( $sql ) ) {
				if ( $count = $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_actions" ) ) {
					echo "removed legacy action table\n";
					return false;
				}
			}
		}

		return true;
	}


	private function do_update_db_structure() {

		global $wpdb;

		$wpdb->query( "ALTER TABLE {$wpdb->prefix}mailster_queue CHANGE subscriber_id  subscriber_id BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE campaign_id campaign_id BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE requeued requeued TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE added added INT( 11 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE timestamp timestamp INT( 11 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE sent sent INT( 11 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE priority priority TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE count count TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE error error TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE ignore_status ignore_status TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE options options VARCHAR( 191 ) NOT NULL DEFAULT ''" );

		$wpdb->query( "ALTER TABLE {$wpdb->prefix}mailster_actions CHANGE subscriber_id  subscriber_id BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE campaign_id campaign_id BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE timestamp timestamp INT( 11 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE count count TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE type type TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0', CHANGE link_id link_id BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT '0'" );

		return true;
	}


	private function do_update_lists() {

		global $wpdb;

		$now = time();

		$limit = ceil( 25 * $this->performance );

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->terms} AS a LEFT JOIN {$wpdb->term_taxonomy} as b ON b.term_id = a.term_id LEFT JOIN {$wpdb->prefix}mailster_lists AS c ON c.ID = a.term_id WHERE b.taxonomy = 'newsletter_lists' AND c.ID IS NULL" );

		echo $count . ' lists left.' . "\n";

		$sql = "SELECT a.term_id AS ID, a.name, a.slug, b.description FROM {$wpdb->terms} AS a LEFT JOIN {$wpdb->term_taxonomy} as b ON b.term_id = a.term_id LEFT JOIN {$wpdb->prefix}mailster_lists AS c ON c.ID = a.term_id WHERE b.taxonomy = 'newsletter_lists' AND c.ID IS NULL LIMIT $limit";

		$lists = $wpdb->get_results( $sql );
		if ( ! count( $lists ) ) {
			return true;
		}

		foreach ( $lists as $list ) {
			$sql = "INSERT INTO {$wpdb->prefix}mailster_lists (ID, parent_id, name, slug, description, added, updated) VALUES (%d, '0', %s, %s, %s, %d, %d)";

			if ( false !== $wpdb->query( $wpdb->prepare( $sql, $list->ID, $list->name, $list->slug, $list->description, $now, $now ) ) ) {
				echo 'added list ' . $list->name . "\n";
			}
		}

		return false;
	}


	private function do_update_forms() {

		global $wpdb;

		$now = time();

		$forms = mailster_option( 'forms' );

		if ( empty( $forms ) ) {
			return true;
		}

		$ids = wp_list_pluck( $forms, 'id' );

		$form_css = mailster_option( 'form_css' );

		foreach ( $forms as $id => $form ) {

			if ( $wpdb->query( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mailster_forms WHERE ID = %d", $id ) ) ) {
				continue;
			}

			$sql = "INSERT INTO {$wpdb->prefix}mailster_forms
                (ID, name, submit, asterisk, userschoice, precheck, dropdown, prefill, inline, addlists, style, custom_style, doubleoptin, subject, headline, link, content, resend, resend_count, resend_time, template, vcard, vcard_content, confirmredirect, redirect, added, updated) VALUES
                (%d, %s, %s, %d, %d, %d, %d, %d, %d, %d, %s, %s, %d, %s, %s, %s, %s, %d, %d, %d, %s, %d, %s, %s, %s, %d, %d)

                ON DUPLICATE KEY UPDATE updated=%d";

			$sql = $wpdb->prepare( $sql, $id, $form['name'], $form['submitbutton'], isset( $form['asterisk'] ), isset( $form['userschoice'] ), isset( $form['precheck'] ), isset( $form['dropdown'] ), isset( $form['prefill'] ), isset( $form['inline'] ), isset( $form['addlists'] ), '', str_replace( '.mailster-form ', '.mailster-form-' . $id . ' ', $form_css ), isset( $form['double_opt_in'] ), $form['text']['subscription_subject'], $form['text']['subscription_headline'], $form['text']['subscription_link'], $form['text']['subscription_text'], isset( $form['subscription_resend'] ), $form['subscription_resend_count'], $form['subscription_resend_time'], $form['template'], isset( $form['vcard'] ), $form['vcard_content'], $form['confirmredirect'], $form['redirect'], $now, $now, $now );

			if ( $wpdb->query( $sql ) ) {
				if ( $wpdb->insert_id != $id ) {
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mailster_forms SET `ID` = %d WHERE {$wpdb->prefix}mailster_forms.ID = %d;", $id, $wpdb->insert_id ) );
				}

				foreach ( $form['order'] as $position => $field_id ) {

					$sql = "INSERT INTO {$wpdb->prefix}mailster_form_fields (form_id, field_id, name, required, position) VALUES (%d, %s, %s, %d, %d)";
					$wpdb->query( $wpdb->prepare( $sql, $id, $field_id, $form['labels'][ $field_id ], in_array( $field_id, $form['required'] ), $position ) );
				}

				echo 'updated form ' . $form['name'] . " \n";
				if ( mailster( 'forms' )->assign_lists( $id, $form['lists'], false ) ) {
					echo 'assigned lists to form ' . $form['name'] . " \n";
				}
			}
		}

		$wpdb->query( $wpdb->prepare( "ALTER TABLE {$wpdb->prefix}mailster_forms AUTO_INCREMENT = %d", count( $forms ) ) );

		$wpdb->query( "UPDATE {$wpdb->posts} SET `post_content` = replace(post_content, '[newsletter_signup_form]', '[newsletter_signup_form id=0]')" );

		return true;
	}



	private function do_update_campaign() {

		global $wpdb;

		$limit = ceil( 25 * $this->performance );

		$timeoffset = mailster( 'helper' )->gmt_offset( true );

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} AS m LEFT JOIN {$wpdb->posts} AS p ON p.ID = m.post_id LEFT JOIN {$wpdb->postmeta} AS c ON p.ID = c.post_id LEFT JOIN {$wpdb->postmeta} AS b ON b.post_id = p.ID AND b.meta_key = '_mailster_timestamp' WHERE m.meta_key = 'mailster-data' AND c.meta_key = 'mailster-campaign' AND p.post_type = 'newsletter' AND b.meta_key IS NULL" );

		echo $count . ' campaigns left.' . "\n";

		$sql = "SELECT p.ID, p.post_title, p.post_status, m.meta_value as meta, c.meta_value AS campaign FROM {$wpdb->postmeta} AS m LEFT JOIN {$wpdb->posts} AS p ON p.ID = m.post_id LEFT JOIN {$wpdb->postmeta} AS c ON p.ID = c.post_id LEFT JOIN {$wpdb->postmeta} AS b ON b.post_id = p.ID AND b.meta_key = '_mailster_timestamp' WHERE m.meta_key = 'mailster-data' AND c.meta_key = 'mailster-campaign' AND p.post_type = 'newsletter' AND b.meta_key IS NULL LIMIT $limit";

		$campaigns = $wpdb->get_results( $sql );

		// no campaigns left => update ok
		if ( ! count( $campaigns ) ) {
			return true;
		}

		foreach ( $campaigns as $data ) {

			$meta = mailster( 'helper' )->unserialize( $data->meta );

			$campaign = wp_parse_args(
				array(
					'original_campaign' => '',
					'finished'          => '',
					'timestamp'         => '',
					'totalerrors'       => '',
				),
				mailster( 'helper' )->unserialize( $data->campaign )
			);

			$lists = $wpdb->get_results( $wpdb->prepare( "SELECT b.* FROM {$wpdb->term_relationships} AS a LEFT JOIN {$wpdb->term_taxonomy} AS b ON b.term_taxonomy_id = a.term_taxonomy_id WHERE object_id = %d", $data->ID ) );

			$listids = wp_list_pluck( $lists, 'term_id' );

			if ( $data->post_status == 'autoresponder' ) {
				$autoresponder = $meta['autoresponder'];
				$active        = isset( $meta['active_autoresponder'] ) && $meta['active_autoresponder'];
				$timestamp     = isset( $autoresponder['timestamp'] ) ? $autoresponder['timestamp'] : strtotime( $autoresponder['date'] . ' ' . $autoresponder['time'] );

			} else {
				$autoresponder = '';
				$active        = isset( $meta['active'] ) && $meta['active'] && ! $campaign['finished'];
				$timestamp     = isset( $meta['timestamp'] ) ? $meta['timestamp'] : time();
			}

			$timestamp = $timestamp - $timeoffset;

			if ( $data->post_status == 'finished' ) {
				$campaign['finished'] = $campaign['finished'] ? $campaign['finished'] - $timeoffset : $timestamp;
			}

			$values = array(
				'parent_id'     => $campaign['original_campaign'],
				'timestamp'     => $timestamp,
				'finished'      => $campaign['finished'],
				'active'        => $active, // all campaigns inactive
				'from_name'     => $meta['from_name'],
				'from_email'    => $meta['from'],
				'reply_to'      => $meta['reply_to'],
				'subject'       => $meta['subject'],
				'preheader'     => $meta['preheader'],
				'template'      => $meta['template'],
				'file'          => $meta['file'],
				'lists'         => array_unique( $listids ),
				'ignore_lists'  => 0,
				'autoresponder' => $autoresponder,
				'head'          => trim( $meta['head'] ),
				'background'    => $meta['background'],
				'colors'        => $meta['newsletter_color'],
				'track_opens'   => mailster_option( 'trackcountries' ),
				'track_clicks'  => mailster_option( 'trackcountries' ),
			);

			if ( $data->post_status == 'active' ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status = 'queued' WHERE ID = %d AND post_type = 'newsletter'", $data->ID ) );
			}

			mailster( 'campaigns' )->update_meta( $data->ID, $values );

			echo 'updated campaign ' . $data->post_title . "\n";

		}

		return false;
	}


	private function do_update_subscriber() {

		global $wpdb;

		$timeoffset = mailster( 'helper' )->gmt_offset( true );

		$limit = ceil( 500 * $this->performance );

		$now = time();

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->prefix}mailster_subscribers AS s ON s.ID = p.ID LEFT JOIN {$wpdb->prefix}mailster_subscribers AS s2 ON s2.email = p.post_title LEFT JOIN {$wpdb->postmeta} AS c ON p.ID = c.post_id AND c.meta_key = 'mailster-campaigns' LEFT JOIN {$wpdb->postmeta} AS u ON p.ID = u.post_id AND u.meta_key = 'mailster-userdata' WHERE p.post_type = 'subscriber' AND post_status IN ('subscribed', 'unsubscribed', 'hardbounced', 'error') AND s.ID IS NULL AND (s2.email != p.post_title OR s2.email IS NULL)" );

		echo $count . ' subscribers left' . "\n\n";

		$sql = "SELECT p.ID, p.post_title AS email, p.post_status AS status, p.post_name AS hash, c.meta_value as campaign, u.meta_value as userdata FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->prefix}mailster_subscribers AS s ON s.ID = p.ID LEFT JOIN {$wpdb->prefix}mailster_subscribers AS s2 ON s2.email = p.post_title LEFT JOIN {$wpdb->postmeta} AS c ON p.ID = c.post_id AND c.meta_key = 'mailster-campaigns' LEFT JOIN {$wpdb->postmeta} AS u ON p.ID = u.post_id AND u.meta_key = 'mailster-userdata' WHERE p.post_type = 'subscriber' AND post_status IN ('subscribed', 'unsubscribed', 'hardbounced', 'error') AND s.ID IS NULL AND (s2.email != p.post_title OR s2.email IS NULL) GROUP BY p.ID ORDER BY p.post_title ASC LIMIT $limit";

		$users = $wpdb->get_results( $sql );

		$count = count( $users );

		// no users left => update ok
		if ( ! $count ) {
			return true;
		}

		foreach ( $users as $data ) {
			$userdata = mailster( 'helper' )->unserialize( $data->userdata );

			$meta = array(
				'confirmtime' => 0,
				'signuptime'  => 0,
				'signupip'    => '',
				'confirmip'   => '',
			);

			if ( is_array( $userdata ) && isset( $userdata['_meta'] ) ) {
				$meta = wp_parse_args( $userdata['_meta'], $meta );
				unset( $userdata['_meta'] );
			}

			$status = mailster( 'subscribers' )->get_status_by_name( $data->status );

			$values = array(
				'ID'         => $data->ID,
				'email'      => addcslashes( $data->email, "'" ),
				'hash'       => $data->hash,
				'status'     => $status,
				'added'      => isset( $meta['imported'] ) ? $meta['imported'] : ( isset( $meta['confirmtime'] ) ? $meta['confirmtime'] : $now ),
				'updated'    => $now,
				'signup'     => $meta['signuptime'],
				'confirm'    => $meta['confirmtime'],
				'ip_signup'  => $meta['signupip'],
				'ip_confirm' => $meta['confirmip'],
			);

			$campaign_data = mailster( 'helper' )->unserialize( $data->campaign );

			$sql = "INSERT INTO {$wpdb->prefix}mailster_subscribers (" . implode( ',', array_keys( $values ) ) . ") VALUES ('" . implode( "','", array_values( $values ) ) . "') ON DUPLICATE KEY UPDATE updated = values(updated);";

			if ( false !== $wpdb->query( $sql ) ) {

				echo 'added ' . $data->email . "\n";
				$this->update_customfields( $data->ID );
				echo "\n";

			}
		}

		// not finished yet (but successfull)
		return false;
	}


	private function do_update_list_subscriber() {

		global $wpdb;

		$limit = ceil( 500 * $this->performance );

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_relationships} AS a LEFT JOIN {$wpdb->term_taxonomy} AS b ON a.term_taxonomy_id = b.term_taxonomy_id LEFT JOIN {$wpdb->prefix}mailster_lists_subscribers AS c ON c.subscriber_id = a.object_id AND c.list_id = b.term_id WHERE b.taxonomy = 'newsletter_lists' AND c.subscriber_id IS NULL" );

		echo $count . ' list - subscriber connections left' . "\n\n";

		$sql = "SELECT a.object_id AS subscriber_id, b.term_id AS list_id FROM {$wpdb->term_relationships} AS a LEFT JOIN {$wpdb->term_taxonomy} AS b ON a.term_taxonomy_id = b.term_taxonomy_id LEFT JOIN {$wpdb->prefix}mailster_lists_subscribers AS c ON c.subscriber_id = a.object_id AND c.list_id = b.term_id WHERE b.taxonomy = 'newsletter_lists' AND c.subscriber_id IS NULL LIMIT $limit";

		$connections = $wpdb->get_results( $sql );
		if ( ! count( $connections ) ) {
			return true;
		}

		$inserts = array();

		$now = time();

		$sql = "INSERT INTO {$wpdb->prefix}mailster_lists_subscribers (list_id, subscriber_id, added) VALUES";

		foreach ( $connections as $connection ) {
			$inserts[] = $wpdb->prepare( '(%d, %d, %d)', $connection->list_id, $connection->subscriber_id, $now );
		}

		if ( empty( $inserts ) ) {
			return true;
		}

		$sql .= implode( ',', $inserts );

		$wpdb->query( $sql );

		return false;
	}


	/**
	 *
	 *
	 * @param unknown $id
	 */
	private function update_customfields( $id ) {
		global $wpdb;

		$timeoffset = mailster( 'helper' )->gmt_offset( true );

		$now = time();

		$id = (int) $id;

		$sql = "SELECT a.meta_value AS meta FROM {$wpdb->postmeta} AS a LEFT JOIN {$wpdb->prefix}mailster_subscriber_fields AS b ON b.subscriber_id = a.post_id WHERE a.meta_key = 'mailster-userdata' AND b.subscriber_id IS NULL AND a.post_id = %d LIMIT 1";

		if ( $usermeta = $wpdb->get_var( $wpdb->prepare( $sql, $id ) ) ) {

			$userdata = mailster( 'helper' )->unserialize( $usermeta );
			if ( ! is_array( $userdata ) ) {
				'ERROR: Corrupt data: "' . $userdata . '"';
				return;
			}

			$meta = array();
			if ( isset( $userdata['_meta'] ) ) {
				$meta = $userdata['_meta'];
				unset( $userdata['_meta'] );
			}

			foreach ( $userdata as $field => $value ) {
				if ( $value == '' ) {
					continue;
				}

				$sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}mailster_subscriber_fields (subscriber_id, meta_key, meta_value) VALUES (%d, %s, %s) ON DUPLICATE KEY UPDATE subscriber_id = values(subscriber_id)", $id, trim( $field ), trim( $value ) );

				if ( false !== $wpdb->query( $sql ) ) {
					echo "added field '$field' => '$value' \n";
				}
			}

			foreach ( $meta as $field => $value ) {
				if ( $value == '' || ! in_array( $field, array( 'ip', 'lang' ) ) ) {
					continue;
				}

				$sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}mailster_subscriber_meta (subscriber_id, meta_key, meta_value) VALUES (%d, %s, %s) ON DUPLICATE KEY UPDATE subscriber_id = values(subscriber_id)", $id, trim( $field ), trim( $value ) );

				if ( false !== $wpdb->query( $sql ) ) {
					echo "added meta field '$field' => '$value' \n";
				}
			}
		}
	}


	private function do_update_customfields() {

		global $wpdb;

		$timeoffset = mailster( 'helper' )->gmt_offset( true );

		$limit = ceil( 2500 * $this->performance );

		$now = time();

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} AS a LEFT JOIN {$wpdb->prefix}mailster_subscriber_fields AS b ON b.subscriber_id = a.post_id WHERE a.meta_key = 'mailster-userdata' AND b.subscriber_id IS NULL" );

		echo $count . ' customfields left' . "\n\n";

		$sql = "SELECT a.post_id AS ID, a.meta_value AS meta FROM {$wpdb->postmeta} AS a LEFT JOIN {$wpdb->prefix}mailster_subscriber_fields AS b ON b.subscriber_id = a.post_id WHERE a.meta_key = 'mailster-userdata' AND b.subscriber_id IS NULL LIMIT $limit";

		$usermeta = $wpdb->get_results( $sql );

		// no usermeta left => update ok
		if ( ! count( $usermeta ) ) {
			return true;
		}

		foreach ( $usermeta as $data ) {
			$userdata = mailster( 'helper' )->unserialize( $data->meta );
			$meta     = array();
			if ( isset( $userdata['_meta'] ) ) {
				$meta = $userdata['_meta'];
				unset( $userdata['_meta'] );
			}

			if ( empty( $userdata ) ) {
				$sql = "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'mailster-userdata'";
				$wpdb->query( $wpdb->prepare( $sql, $data->ID ) );
			}

			foreach ( $userdata as $field => $value ) {
				if ( $value == '' ) {
					continue;
				}

				$sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}mailster_subscriber_fields (subscriber_id, meta_key, meta_value) VALUES (%d, %s, %s)", $data->ID, trim( $field ), trim( $value ) );

				$wpdb->query( $sql );

			}
			foreach ( $meta as $field => $value ) {
				if ( $value == '' || ! in_array( $field, array( 'ip', 'lang' ) ) ) {
					continue;
				}

				$sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}mailster_subscriber_meta (subscriber_id, meta_key, meta_value) VALUES (%d, %s, %s) ON DUPLICATE KEY UPDATE subscriber_id = values(subscriber_id)", $data->ID, trim( $field ), trim( $value ) );

				$wpdb->query( $sql );

			}
			echo 'added fields for ' . $data->ID . "\n";

		}

		// not finished yet (but successful)
		return false;
	}


	private function do_update_actions() {

		global $wpdb;

		$timeoffset = mailster( 'helper' )->gmt_offset( true );

		$limit = ceil( 500 * $this->performance );

		$offset = get_transient( 'mailster_do_update_actions' );

		if ( ! $offset ) {
			$offset = 0;
		}

		$now = time();

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} AS a LEFT JOIN {$wpdb->prefix}mailster_actions AS b ON a.post_id = b.subscriber_id AND a.meta_key = 'mailster-campaigns' WHERE b.subscriber_id IS NULL AND a.meta_key = 'mailster-campaigns' AND a.meta_value != 'a:0:{}' ORDER BY a.post_id ASC" );

		echo $count . ' actions left' . "\n\n";

		$sql = "SELECT a.post_id AS ID, a.meta_value AS meta FROM {$wpdb->postmeta} AS a LEFT JOIN {$wpdb->prefix}mailster_actions AS b ON a.post_id = b.subscriber_id AND a.meta_key = 'mailster-campaigns' WHERE b.subscriber_id IS NULL AND a.meta_key = 'mailster-campaigns' AND a.meta_value != 'a:0:{}' GROUP BY a.post_id ORDER BY a.post_id ASC LIMIT $limit";

		$campaignmeta = $wpdb->get_results( $sql );

		// nothing left
		if ( ! count( $campaignmeta ) ) {
			delete_transient( 'mailster_do_update_actions' );
			return true;
		}

		$bounce_attempts = mailster_option( 'bounce_attempts' );

		$old_unsubscribelink = add_query_arg( array( 'unsubscribe' => '' ), get_permalink( mailster_option( 'homepage' ) ) );
		$new_unsubscribelink = mailster()->get_unsubscribe_link();

		foreach ( $campaignmeta as $data ) {

			$userdata = mailster( 'helper' )->unserialize( $data->meta );

			foreach ( $userdata as $campaign_id => $infos ) {

				$default = array(
					'subscriber_id' => $data->ID,
					'campaign_id'   => $campaign_id,
					'count'         => 1,
				);
				foreach ( $infos as $info_key => $info_value ) {

					echo 'added action ' . $info_key . ' => ' . $info_value . "\n";
					switch ( $info_key ) {
						case 'sent':
							if ( gettype( $info_value ) == 'boolean' && ! $info_value ) {
								$info_value = $now;
							}

							if ( $info_value ) {
								$values = wp_parse_args(
									array(
										'timestamp' => $info_value,
										'type'      => 1,
									),
									$default
								);

								$wpdb->query( "INSERT INTO {$wpdb->prefix}mailster_actions (" . implode( ',', array_keys( $values ) ) . ") VALUES ('" . implode( "','", array_values( $values ) ) . "') ON DUPLICATE KEY UPDATE timestamp = values(timestamp)" );
							} else {

								$values = wp_parse_args(
									array(
										'timestamp' => $now,
										'sent'      => $info_value,
										'priority'  => 10,
									),
									$default
								);

								$wpdb->query( "INSERT INTO {$wpdb->prefix}mailster_queue (" . implode( ',', array_keys( $values ) ) . ") VALUES ('" . implode( "','", array_values( $values ) ) . "') ON DUPLICATE KEY UPDATE timestamp = values(timestamp)" );
							}

							break;
						case 'open':
							$values = wp_parse_args(
								array(
									'timestamp' => $info_value,
									'type'      => 2,
								),
								$default
							);

								$wpdb->query( "INSERT INTO {$wpdb->prefix}mailster_actions (" . implode( ',', array_keys( $values ) ) . ") VALUES ('" . implode( "','", array_values( $values ) ) . "') ON DUPLICATE KEY UPDATE timestamp = values(timestamp)" );
							break;

						case 'clicks':
							foreach ( $info_value as $link => $count ) {

								// new unsubscribe links
								if ( $link == $old_unsubscribelink ) {
									$link = $new_unsubscribelink;
								}

								$values = wp_parse_args(
									array(
										'timestamp' => $infos['firstclick'],
										'type'      => 3,
										'link_id'   => mailster( 'actions' )->get_link_id( $link, 0 ),
										'count'     => $count,
									),
									$default
								);

								$wpdb->query( "INSERT INTO {$wpdb->prefix}mailster_actions (" . implode( ',', array_keys( $values ) ) . ") VALUES ('" . implode( "','", array_values( $values ) ) . "') ON DUPLICATE KEY UPDATE timestamp = values(timestamp)" );

							}
							break;

						case 'unsubscribe':
							$values = wp_parse_args(
								array(
									'timestamp' => $info_value,
									'type'      => 4,
								),
								$default
							);

								$wpdb->query( "INSERT INTO {$wpdb->prefix}mailster_actions (" . implode( ',', array_keys( $values ) ) . ") VALUES ('" . implode( "','", array_values( $values ) ) . "') ON DUPLICATE KEY UPDATE timestamp = values(timestamp)" );

							break;

						case 'bounces':
							$values = wp_parse_args(
								array(
									'timestamp' => $now,
									'type'      => $info_value >= $bounce_attempts ? 6 : 5,
									'count'     => $info_value >= $bounce_attempts ? $bounce_attempts : 1,
								),
								$default
							);

								$wpdb->query( "INSERT INTO {$wpdb->prefix}mailster_actions (" . implode( ',', array_keys( $values ) ) . ") VALUES ('" . implode( "','", array_values( $values ) ) . "') ON DUPLICATE KEY UPDATE timestamp = values(timestamp)" );

							break;

					}
				}
			}
		}

		set_transient( 'mailster_do_update_actions', $offset + $limit );

		// not finished yet (but successful)
		return false;

		return new WP_Error( 'update_error', 'An error occured during batch update' );
	}


	private function do_update_pending() {

		global $wpdb;

		$timeoffset = mailster( 'helper' )->gmt_offset( true );

		$now = time();

		$limit = ceil( 25 * $this->performance );

		$pending = get_option( 'mailster_confirms', array() );

		$i = 0;

		foreach ( $pending as $hash => $user ) {

			$userdata = $user['userdata'];
			$meta     = array();
			if ( isset( $userdata['_meta'] ) ) {
				$meta = $userdata['_meta'];
				unset( $userdata['_meta'] );
			}

			$values = array(
				'email'     => $userdata['email'],
				'hash'      => $hash,
				'status'    => 0,
				'added'     => $user['timestamp'],
				'updated'   => $now,
				'signup'    => $user['timestamp'],
				'ip_signup' => $meta['signupip'],
			);

			$sql = "INSERT INTO {$wpdb->prefix}mailster_subscribers (" . implode( ',', array_keys( $values ) ) . ") VALUES ('" . implode( "','", array_values( $values ) ) . "')";

			if ( false !== $wpdb->query( $sql ) ) {

				$subscriber_id = $wpdb->insert_id;

				unset( $userdata['email'] );

				foreach ( $userdata as $field => $value ) {
					if ( $value == '' ) {
						continue;
					}

					$sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}mailster_subscriber_fields (subscriber_id, meta_key, meta_value) VALUES (%d, %s, %s) ON DUPLICATE KEY UPDATE subscriber_id = values(subscriber_id)", $subscriber_id, trim( $field ), trim( $value ) );

					if ( false !== $wpdb->query( $sql ) ) {
						echo "added field '$field' => '$value' \n";
					}
				}

				foreach ( $meta as $field => $value ) {
					if ( $value == '' || ! in_array( $field, array( 'ip', 'lang' ) ) ) {
						continue;
					}

					$sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}mailster_subscriber_meta (subscriber_id, meta_key, meta_value) VALUES (%d, %s, %s) ON DUPLICATE KEY UPDATE subscriber_id = values(subscriber_id)", $subscriber_id, trim( $field ), trim( $value ) );

					if ( false !== $wpdb->query( $sql ) ) {
						echo "added meta field '$field' => '$value' \n";
					}
				}

				echo 'added pending user ' . $values['email'] . "\n";

			}
		}

		return true;
	}


	private function do_update_autoresponder() {

		global $wpdb;

		$timeoffset = mailster( 'helper' )->gmt_offset( true );

		$now = time();

		$limit = ceil( 25 * $this->performance );

		$cron = get_option( 'cron', array() );

		foreach ( $cron as $timestamp => $jobs ) {
			if ( ! is_array( $jobs ) ) {
				continue;
			}

			foreach ( $jobs as $id => $data ) {
				if ( $id != 'mailster_autoresponder' ) {
					continue;
				}

				foreach ( $data as $crondata ) {
					$args = $crondata['args'];

					$values = array(
						'subscriber_id' => $args['args'][0],
						'campaign_id'   => $args['campaign_id'],
						'added'         => $now,
						'timestamp'     => $timestamp,
						'sent'          => 0,
						'priority'      => 15,
						'count'         => $args['try'],
						'ignore_status' => $args['action'] == 'mailster_subscriber_unsubscribed',
					);

					$wpdb->query( "INSERT INTO {$wpdb->prefix}mailster_queue (" . implode( ',', array_keys( $values ) ) . ") VALUES ('" . implode( "','", array_values( $values ) ) . "')" );

				}
			}
		}

		return true;
	}


	private function do_update_settings() {

		global $wpdb;

		$forms = mailster_option( 'forms' );

		if ( empty( $forms ) ) {
			return true;
		}

		foreach ( $forms as $id => $form ) {

			// Stop if all list items are numbers (Mailster 2 already)
			if ( ! isset( $form['lists'] ) || ! is_array( $form['lists'] ) ) {
				continue;
			}

			if ( count( array_filter( $form['lists'], 'is_numeric' ) ) == count( $form['lists'] ) ) {
				continue;
			}

			$sql = "SELECT a.ID FROM {$wpdb->prefix}mailster_lists AS a WHERE a.slug IN ('" . implode( "','", $form['lists'] ) . "')";

			$lists = $wpdb->get_col( $sql );

			$forms[ $id ]['lists'] = $lists;

			echo 'updated form ' . $form['name'] . "\n";

		}

		mailster_update_option( 'forms', $forms );

		$texts = mailster_option( 'text' );

		$texts['profile_update'] = ! empty( $texts['profile_update'] ) ? $texts['profile_update'] : esc_html__( 'Profile Updated!', 'mailster' );
		$texts['profilebutton']  = ! empty( $texts['profilebutton'] ) ? $texts['profilebutton'] : esc_html__( 'Update Profile', 'mailster' );
		$texts['forward']        = ! empty( $texts['forward'] ) ? $texts['forward'] : esc_html__( 'forward to a friend', 'mailster' );
		$texts['profile']        = ! empty( $texts['profile'] ) ? $texts['profile'] : esc_html__( 'update profile', 'mailster' );

		echo "updated texts\n";

		mailster_update_option( 'text', $texts );

		return true;
	}


	private function do_cleanup() {

		global $wpdb;

		if ( $count = $wpdb->query( "DELETE a FROM {$wpdb->postmeta} AS a LEFT JOIN {$wpdb->posts} AS p ON p.ID = a.post_id WHERE p.ID IS NULL AND a.meta_key LIKE '_mailster_%'" ) ) {
			echo 'Removed ' . number_format( $count ) . " rows of meta where's no campaign\n";
			return false;
		}

		if ( $count = $wpdb->query( "DELETE a FROM {$wpdb->prefix}mailster_subscriber_meta AS a WHERE a.meta_value = '' OR a.subscriber_id = 0" ) ) {
			echo 'Removed ' . number_format( $count ) . " rows of unassigned subscriber meta\n";
			return false;
		}

		$tables_with_subscriber_ids = array( 'action_sent', 'action_opens', 'action_clicks', 'action_unsubs', 'action_bounces', 'action_errors', 'subscriber_meta', 'subscriber_fields', 'tags_subscribers', 'subscriber_meta', 'lists_subscribers', 'queue' );

		foreach ( $tables_with_subscriber_ids as $table ) {
			if ( $count = $wpdb->query( "DELETE a FROM {$wpdb->prefix}mailster_{$table} AS a LEFT JOIN {$wpdb->prefix}mailster_subscribers AS b ON b.ID = a.subscriber_id WHERE b.ID IS NULL AND a.subscriber_id IS NOT NULL" ) ) {
				echo 'Removed ' . number_format( $count ) . " rows of table $table where's no subscriber\n";
				return false;
			}
		}

		if ( $count = mailster( 'subscribers' )->wp_id() ) {
			echo 'Assign ' . number_format( $count ) . " WP users\n";
			return false;
		}

		if ( $this->table_exists( "{$wpdb->prefix}mailster_temp_import" ) ) {
			if ( $count = $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_temp_import" ) ) {
				echo "Removed temporary import table\n";
				return false;
			}
		}

		if ( $wpdb->query( "DELETE a FROM {$wpdb->options} AS a WHERE a.option_name LIKE 'mailster_bulk_import%'" ) ) {
			echo "Removed temporary import data\n";
			return false;
		}

		$action_tables = array( 'sent', 'opens', 'clicks', 'unsubs', 'bounces', 'errors' );

		foreach ( $action_tables as $table ) {

			if ( $count = $wpdb->query( "DELETE a FROM {$wpdb->prefix}mailster_action_{$table} AS a WHERE a.subscriber_id IS NULL AND a.campaign_id IS NULL" ) ) {
				echo 'Removed ' . number_format( $count ) . " actions where's no campaign and no subscriber in $table table.\n";
				return false;
			}
			if ( $count = $wpdb->query( "DELETE a FROM {$wpdb->prefix}mailster_action_{$table} AS a JOIN (SELECT b.campaign_id, b.subscriber_id FROM {$wpdb->prefix}mailster_action_{$table} AS b LEFT JOIN {$wpdb->posts} AS p ON p.ID = b.campaign_id WHERE p.ID IS NULL ORDER BY b.campaign_id LIMIT 1000) AS ab ON (a.campaign_id = ab.campaign_id AND a.subscriber_id = ab.subscriber_id)" ) ) {
				echo 'Removed ' . number_format( $count ) . " actions where's no campaign in $table table.\n";
				return false;
			}
		}

		$wpdb->query( "UPDATE {$wpdb->prefix}mailster_subscribers SET ip_signup = '' WHERE ip_signup = 0" );
		$wpdb->query( "UPDATE {$wpdb->prefix}mailster_subscribers SET ip_confirm = '' WHERE ip_confirm = 0" );

		$this->do_delete_legacy_action_table();

		delete_transient( 'mailster_cron_lock' );

		update_option( 'mailster_dbversion', MAILSTER_DBVERSION );
		mailster_update_option( 'db_update_required', false );
		mailster_update_option( 'db_update_background', false );
		mailster_remove_notice( 'db_update_required' );
		mailster_remove_notice( 'background_update' );

		delete_option( 'updatecenter_plugins' );
		do_action( 'updatecenterplugin_check' );

		return true;
	}


	/**
	 *
	 *
	 * @param unknown $table
	 * @return unknown
	 */
	private function table_exists( $table ) {

		global $wpdb;
		return $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	}

	/**
	 *
	 *
	 * @param unknown $column
	 * @param unknown $table
	 * @return unknown
	 */
	private function column_exists( $column, $table ) {

		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "SHOW COLUMNS FROM {$table} LIKE %s", $column ) );
	}


	/**
	 *
	 *
	 * @param unknown $content (optional)
	 */
	private function output( $content = '' ) {

		global $mailster_batch_update_output;

		$mailster_batch_update_output[] = $content;
	}

	private function please_die() {

		$this->stop_process = true;
	}
}
