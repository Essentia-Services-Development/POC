<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MailsterImportWordPress extends MailsterImport {

	protected $type = 'WordPress';
	protected $name = 'WordPress';

	function init() {}


	public function get_import_part( &$import_data ) {

		$limit  = $import_data['performance'] ? 100 : 500;
		$offset = $import_data['page'] - 1;

		$result = $this->query(
			array(
				'roles'       => isset( $import_data['extra']['roles'] ) ? (array) $import_data['extra']['roles'] : array(),
				'no_role'     => isset( $import_data['extra']['no_role'] ),
				'meta_values' => isset( $import_data['extra']['meta_values'] ) ? (array) $import_data['extra']['meta_values'] : array(),
				'offset'      => $offset,
				'limit'       => $limit,
				'add_id'      => true,
			)
		);

		return $result['data'];
	}

	public function get_import_data() {

		parse_str( $_POST['data'], $data );

		$meta_values = isset( $data['meta_values'] ) ? (array) $data['meta_values'] : array();

		$args = array(
			'roles'       => isset( $data['roles'] ) ? (array) $data['roles'] : array(),
			'no_role'     => isset( $data['no_role'] ),
			'meta_values' => $meta_values,
			'offset'      => isset( $_POST['offset'] ) ? (int) $_POST['offset'] : 0,
			'limit'       => 10,
			'count_total' => true,
		);

		$result = $this->query( $args );

		$last_sample = null;

		if ( $result['total'] ) {
			$args['offset']      = $result['total'] - 1;
			$args['limit']       = 1;
			$args['count_total'] = false;

			$last_result = $this->query( $args );
			$last_sample = $last_result['data'][0];
		}

		$header = array_merge(
			array(
				'email'           => mailster_text( 'email' ),
				'firstname'       => mailster_text( 'firstname' ),
				'lastname'        => mailster_text( 'lastname' ),
				esc_html__( 'Login', 'mailster' ),
				esc_html__( 'Nickname', 'mailster' ),
				esc_html__( 'User URL', 'mailster' ),
				esc_html__( 'Display Name', 'mailster' ),
				'_confirm_signup' => esc_html__( 'Registered', 'mailster' ),
				esc_html__( 'Roles', 'mailster' ),
			),
			$meta_values
		);

		return array(
			'total'       => $result['total'],
			'header'      => $header,
			'sample'      => $result['data'],
			'sample_last' => $last_sample,
			'extra'       => $data,
			'defaults'    => array(
				'existing' => 'merge',
			),
		);
	}


	public function import_options( $data = null ) {
		include MAILSTER_DIR . '/views/manage/method-wordpress.php';
	}

	private function query( $args ) {

		$args     = wp_parse_args(
			$args,
			array(
				'roles'       => array(),
				'no_role'     => false,
				'meta_values' => array(),
				'offset'      => 0,
				'limit'       => 5,
				'count_total' => false,
				'add_id'      => false,
			)
		);
		$total    = null;
		$user_ids = array();

		$query_args = array(
			'fields'      => 'ID',
			'number'      => $args['limit'],
			'offset'      => $args['offset'] * $args['limit'],
			'orderby'     => 'ID',
			'count_total' => $args['count_total'],
		);

		// handle no role as you cannot do this with WP_User_Query
		$query_args['meta_query'] = array( 'relation' => 'OR' );
		if ( $args['no_role'] ) {
			$query_args['meta_query'][] = array(
				'key'     => 'wp_capabilities',
				'value'   => 'a:0:{}',
				'compare' => 'LIKE',
			);
		}

		$query_args['role__in'] = (array) $args['roles'];

		$user_query = new WP_User_Query( $query_args );
		$user_ids   = $user_query->get_results();
		if ( $args['count_total'] ) {
			$total = $user_query->get_total();
		}

		$data = array();

		$wp_roles = wp_list_pluck( wp_roles()->roles, 'name' );

		foreach ( $user_ids as $i => $user_id ) {
			$user = get_user_by( 'ID', $user_id );
			if ( ! $user ) {
				--$total;
				continue;
			}
			if ( $i >= $args['limit'] ) {
				break;
			}

			$roles = array_intersect_key( $wp_roles, array_flip( $user->roles ) );
			$entry = array(
				$user->user_email,
				get_user_meta( $user->ID, 'first_name', true ),
				get_user_meta( $user->ID, 'last_name', true ),
				$user->user_login,
				$user->user_nicename,
				$user->user_url,
				$user->user_name,
				$user->user_registered,
				implode( ', ', $roles ),
			);
			foreach ( $args['meta_values'] as $meta_value ) {
				$entry[] = get_user_meta( $user_id, $meta_value, true );
			}
			if ( $args['add_id'] ) {
				$entry[] = $user->ID;
			}

			$data[] = $entry;
		}

		return array(
			'total' => $total,
			'data'  => $data,
		);
	}

	public function filter( $insert, $data, $import_data ) {

		$insert['wp_id']   = end( $data );
		$insert['referer'] = 'wpuser';

		return $insert;
	}
}
