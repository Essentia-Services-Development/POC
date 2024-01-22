<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Mailster_Logs_Table extends WP_List_Table {

	public $total_items;
	public $total_pages;
	public $per_page;

	private $page;
	private $paged;
	private $search;
	private $orderby;
	private $order;

	public function __construct() {

		parent::__construct(
			array(
				'singular' => esc_html__( 'Log', 'mailster' ), // singular name of the listed records
				'plural'   => esc_html__( 'Logs', 'mailster' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);

		add_filter( 'manage_newsletter_page_mailster_logs_columns', array( &$this, 'get_columns' ) );

		$this->paged   = isset( $_GET['paged'] ) ? (int) $_GET['paged'] - 1 : null;
		$this->search  = isset( $_GET['s'] ) ? $_GET['s'] : null;
		$this->orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'id';
		$this->order   = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_views() {

		return array();
	}

	public function no_items() {

		esc_html_e( 'No logs found', 'mailster' );
	}


	/**
	 *
	 *
	 * @param unknown $text
	 * @param unknown $input_id
	 */
	public function search_box( $text, $input_id ) {

		?>

	<form id="searchform" action method="get">


		<?php if ( $this->paged ) : ?>
			<input type="hidden" name="paged" value="<?php echo esc_attr( $this->paged ); ?>">
		<?php endif; ?>

			<input type="hidden" name="post_type" value="newsletter">
			<input type="hidden" name="page" value="mailster_logs">

		<p class="search-box">
			<label class="screen-reader-text" for="sa-search-input"><?php echo esc_html( $text ); ?></label>
			<input type="search" id="<?php echo $input_id; ?>" name="s" value="<?php echo esc_attr( $this->search ); ?>">
			<input type="submit" name="" id="search-submit" class="button" value="<?php echo esc_attr( $text ); ?>">
		</p>

	</form>

		<?php
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_columns() {
		return mailster( 'logs' )->get_columns();
	}


	/**
	 *
	 *
	 * @param unknown $item
	 * @param unknown $column_name
	 * @return unknown
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'timestamp':
				$timeoffset = mailster( 'helper' )->gmt_offset( true );
				return '<strong><code>' . date_i18n( 'Y-m-d H:i:s', $item->{'timestamp'} + $timeoffset ) . '</code></strong>';

			case 'receivers':
				$addresses = maybe_unserialize( $item->{'receivers'} );
				return implode( ', ', $addresses );

			case 'campaign':
				return '<a href="' . admin_url( 'post.php?post=' . $item->{'campaign_id'} . '&action=edit' ) . '"><strong>' . esc_html( get_the_title( $item->{'campaign_id'} ) ) . '</strong></a>';

			case 'subject':
				return '<a href="' . add_query_arg( array( 'ID' => $item->ID ) ) . '" title="' . esc_attr( $item->{'subject'} ) . '"><strong>' . esc_html( $item->{'subject'} ) . '</strong></a>';

			default:
		}
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_sortable_columns() {
		return array();
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_bulk_actions() {
		return array();
	}



	/**
	 *
	 *
	 * @param unknown $which (optional)
	 */
	public function extra_tablenav( $which = '' ) {}


	/**
	 *
	 *
	 * @param unknown $item
	 * @return unknown
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="logs[]" value="%s" class="log_cb" />',
			$item->ID
		);
	}


	/**
	 *
	 *
	 * @param unknown $current_mode
	 * @return unknown
	 */
	public function view_switcher( $current_mode ) {
		return '';
	}


	/**
	 *
	 *
	 * @param unknown $domain  (optional)
	 * @param unknown $post_id (optional)
	 */
	public function prepare_items( $domain = null, $post_id = null ) {

		global $wpdb;
		$screen        = get_current_screen();
		$columns       = $this->get_columns();
		$hidden        = get_hidden_columns( $screen );
		$sortable      = $this->get_sortable_columns();
		$custom_fields = mailster()->get_custom_fields();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		// How many to display per page?
		if ( ! ( $this->per_page = (int) get_user_option( 'mailster_logs_per_page' ) ) ) {
			$this->per_page = 200;
		}

		$offset = $this->paged * $this->per_page;

		$orderby = $this->orderby;
		$order   = $this->order;

		$max_entries = mailster_option( 'logging-max' );
		$max_days    = mailster_option( 'logging-max' );

		$sql = "SELECT SQL_CALC_FOUND_ROWS ID, `receivers`, `subscriber_id`, `campaign_id`, `timestamp`, `subject` FROM {$wpdb->prefix}mailster_logs WHERE 1";

		if ( $this->search ) {
			$sql .= $wpdb->prepare( " AND CONCAT(`receivers`, ' ', `raw`) LIKE '%%%s%%'", $this->search );
		}

		$sql .= ' ORDER BY timestamp DESC, ID DESC LIMIT %d, %d';

		$items = $wpdb->get_results( $wpdb->prepare( $sql, $offset, $this->per_page ) );

		$this->items       = $items;
		$this->total_items = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$this->total_pages = ceil( $this->total_items / $this->per_page );

		$this->set_pagination_args(
			array(
				'total_items' => $this->total_items,
				'total_pages' => $this->total_pages,
				'per_page'    => $this->per_page,
			)
		);
	}
}
