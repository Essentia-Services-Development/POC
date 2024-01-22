<?php

class MailsterTags {

	public function __construct() {

		add_action( 'plugins_loaded', array( &$this, 'init' ) );
	}


	public function init() {
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_empty() {
		return (object) array(
			'ID'          => 0,
			'name'        => '',
			'added'       => 0,
			'updated'     => 0,
			'subscribers' => 0,
		);
	}




	/**
	 *
	 *
	 * @param unknown $entry
	 * @param unknown $overwrite      (optional)
	 * @param unknown $subscriber_ids (optional)
	 * @return unknown
	 */
	public function update( $entry, $overwrite = true, $subscriber_ids = null ) {

		global $wpdb;

		$entry = (array) $entry;

		$field_names = array(
			'ID'      => '%d',
			'name'    => '%s',
			'added'   => '%d',
			'updated' => '%d',
		);

		$now = time();

		$data = array();

		$entry = apply_filters( 'mailster_verify_tag', $entry );
		if ( is_wp_error( $entry ) ) {
			return $entry;
		} elseif ( $entry === false ) {
			return new WP_Error( 'not_verified', __( 'Tag failed verification', 'mailster' ) );
		}

		foreach ( $entry as $key => $value ) {
			if ( isset( $field_names[ $key ] ) ) {
				$data[ $key ] = $value;
			}
		}

		if ( isset( $data['name'] ) && empty( $data['name'] ) ) {
			$data['name'] = __( 'undefined', 'mailster' );
		}

		$sql = "INSERT INTO {$wpdb->prefix}mailster_tags (" . implode( ', ', array_keys( $data ) ) . ')';

		$sql .= " VALUES ('" . implode( "', '", array_map( 'esc_sql', array_values( $data ) ) ) . "')";

		if ( $overwrite ) {
			$sql .= " ON DUPLICATE KEY UPDATE updated = $now";
			foreach ( $data as $field => $value ) {
				$sql .= ", $field = values($field)";
			}
		}

		$wpdb->suppress_errors();

		if ( false !== $wpdb->query( $sql ) ) {

			$tag_id = ! empty( $wpdb->insert_id ) ? $wpdb->insert_id : (int) $data['ID'];

			if ( ! empty( $subscriber_ids ) ) {
				$this->assign_subscribers( $tag_id, $subscriber_ids );
			}

			do_action( 'mailster_update_tag', $tag_id );

			return $tag_id;

		} else {

			return new WP_Error( 'tag_exists', $wpdb->last_error );
		}
	}


	/**
	 *
	 *
	 * @param unknown $entry
	 * @param unknown $overwrite      (optional)
	 * @param unknown $subscriber_ids (optional)
	 * @return unknown
	 */
	public function add( $entry, $overwrite = false, $subscriber_ids = null ) {

		$now = time();

		$entry = is_string( $entry ) ? (object) array( 'name' => $entry ) : (object) $entry;

		$entry = (array) $entry;

		$entry = wp_parse_args(
			$entry,
			array(
				'added'   => $now,
				'updated' => $now,
			)
		);

		return $this->update( $entry, $overwrite, $subscriber_ids );
	}


	/**
	 *
	 *
	 * @param unknown $names
	 * @param unknown $create_if_missing   (optional)
	 * @return unknown
	 */
	private function get_ids_by_names( $names, $create_if_missing = false ) {
		if ( ! is_array( $names ) ) {
			$names = array( $names );
		}

		$ids = array();
		foreach ( $names as $name ) {
			if ( $tag_id = $this->get_id_by_name( $name, $create_if_missing ) ) {
				$ids[] = $tag_id;
			}
		}

		return $ids;
	}

	/**
	 *
	 *
	 * @param unknown $names
	 * @param unknown $create_if_missing   (optional)
	 * @return unknown
	 */
	private function get_id_by_name( $name, $create_if_missing = false ) {

		$tag_id = $this->get_by_name( $name, 'ID' );

		if ( ! $tag_id && ! $create_if_missing ) {
			return false;
		}

		if ( ! $tag_id ) {
			$tag_id = $this->add( $name );
			if ( is_wp_error( $tag_id ) ) {
				return false;
			}
			return $tag_id;
		}

		return $tag_id;
	}

	private function get_name_by_id( $id ) {

		$tag = $this->get( $id );

		if ( $tag ) {
			return $tag->name;
		}

		return false;
	}

	/**
	 *
	 *
	 * @param unknown $ids
	 * @param unknown $subscriber_ids
	 * @return unknown
	 */
	public function assign_subscribers( $tags, $subscriber_ids ) {
		if ( ! is_array( $tags ) ) {
			$tags = array( $tags );
		}
		$tags = array_filter( $tags );

		if ( ! is_array( $subscriber_ids ) ) {
			$subscriber_ids = array( (int) $subscriber_ids );
		}
		$subscriber_ids = array_filter( $subscriber_ids );

		$success = true;

		foreach ( $tags as $tag ) {
			foreach ( $subscriber_ids as $subscriber_id ) {
				if ( ! $this->assign_subscriber( $tag, $subscriber_id ) ) {
					$success = false;
				}
			}
		}

		return $success;
	}


	/**
	 *
	 *
	 * @param unknown $tag_id
	 * @param unknown $subscriber_ids
	 * @return unknown
	 */
	public function assign_subscriber( $tag_id, $subscriber_id ) {

		global $wpdb;

		$tag_id = $this->sanitize_tag_id( $tag_id );

		$success = true;

		$args = array(
			'tag_id'        => $tag_id,
			'subscriber_id' => $subscriber_id,
			'added'         => time(),
		);

		$errors                = $wpdb->suppress_errors;
		$wpdb->suppress_errors = true;
		if ( $wpdb->insert( "{$wpdb->prefix}mailster_tags_subscribers", $args ) ) {

			$name = $this->get_name_by_id( $tag_id );

			do_action( 'mailster_tag_added', $tag_id, $subscriber_id, $name );

		} else {
			$success = false;
		}
		$wpdb->suppress_errors = $errors;

		return $success;
	}


	/**
	 *
	 *
	 * @param unknown $tags
	 * @param unknown $subscriber_ids
	 * @return unknown
	 */
	public function unassign_subscribers( $tags, $subscriber_ids ) {

		if ( ! is_array( $tags ) ) {
			$tags = array( $tags );
		}

		if ( ! is_array( $subscriber_ids ) ) {
			$subscriber_ids = array( (int) $subscriber_ids );
		}

		$subscriber_ids = array_filter( $subscriber_ids, 'is_numeric' );

		$success = true;

		foreach ( $tags as $tag ) {
			foreach ( $subscriber_ids as $subscriber_id ) {
				if ( ! $this->unassign_subscriber( $tag, $subscriber_id ) ) {
					$success = false;
				}
			}
		}

		return $success;
	}




	/**
	 *
	 *
	 * @param unknown $tag
	 * @param unknown $subscriber_id
	 * @return unknown
	 */
	public function unassign_subscriber( $tag_id, $subscriber_id ) {

		global $wpdb;

		$tag_id = $this->sanitize_tag_id( $tag_id );

		$success = true;

		$args = array(
			'tag_id'        => $tag_id,
			'subscriber_id' => $subscriber_id,
		);

		$errors                = $wpdb->suppress_errors;
		$wpdb->suppress_errors = true;
		if ( $wpdb->delete( "{$wpdb->prefix}mailster_tags_subscribers", $args ) ) {

			$name = $this->get_name_by_id( $tag_id );

			do_action( 'mailster_tag_removed', $tag_id, $subscriber_id, $name );

		} else {
			$success = false;
		}
		$wpdb->suppress_errors = $errors;

		return $success;
	}

	/**
	 *
	 *
	 * @param unknown $ids
	 * @return unknown
	 */
	public function remove_if_not_asigned( $ids ) {
	}

	/**
	 *
	 *
	 * @param unknown $subscriber_id
	 * @return unknown
	 */
	public function clear( $subscriber_id ) {
		global $wpdb;

		$success = true;

		$args = array(
			'subscriber_id' => $subscriber_id,
		);

		$errors                = $wpdb->suppress_errors;
		$wpdb->suppress_errors = true;
		if ( $wpdb->delete( "{$wpdb->prefix}mailster_tags_subscribers", $args ) ) {

		} else {
			$success = false;
		}
		$wpdb->suppress_errors = $errors;

		return $success;
	}

	/**
	 *
	 *
	 * @param unknown $tag
	 * @return unknown
	 */
	private function sanitize_tag_id( $tag_id ) {

		if ( is_numeric( $tag_id ) ) {
			return (int) $tag_id;
		}

		return $this->get_id_by_name( $tag_id, true );
	}

	/**
	 *
	 *
	 * @param unknown $tag_ids
	 * @param unknown $subscribers (optional)
	 * @return unknown
	 */
	public function remove( $tag_ids, $subscribers = false ) {

		global $wpdb;

		global $wpdb;

		$tag_ids = is_numeric( $tag_ids ) ? array( (int) $tag_ids ) : array_filter( $tag_ids, 'is_numeric' );

		if ( $subscribers ) {

			$subscriber_ids = mailster( 'subscribers' )->query(
				array(
					'return_ids' => true,
					'status'     => false,
					'conditions' => array(
						array(
							array(
								'field'    => '_tags__in',
								'operator' => 'is',
								'value'    => $tag_ids,
							),
						),
					),
				)
			);

		}

		$sql = "DELETE a,b FROM {$wpdb->prefix}mailster_lists AS a LEFT JOIN {$wpdb->prefix}mailster_lists_subscribers b ON a.ID = b.list_id WHERE a.ID IN (" . implode( ', ', $tag_ids ) . ')';

		if ( false !== $wpdb->query( $sql ) ) {

			foreach ( $tag_ids as $tag_id ) {
				// $this->remove_from_forms( $tag_id );
			}

			if ( $subscribers ) {
				mailster( 'subscribers' )->remove( $subscriber_ids );
			}

			return true;
		}

		return false;
	}




	/**
	 *
	 *
	 * @param unknown $id     (optional)
	 * @param unknown $status (optional)
	 * @param unknown $counts (optional)
	 * @return unknown
	 */
	public function get( $id = null, $status = null, $counts = false ) {

		global $wpdb;

		$key = 'tags_' . md5( serialize( $id ) . serialize( $status ) . serialize( $counts ) );

		if ( false === ( $tags = mailster_cache_get( $key ) ) ) {

			if ( is_null( $status ) ) {
				$status = array( 1 );
			} elseif ( $status === false ) {
				$status = array( 0, 1, 2, 3, 4 );
			}
			$statuses = ! is_array( $status ) ? array( $status ) : $status;
			$statuses = array_filter( $statuses, 'is_numeric' );

			$tags = array();

			if ( is_null( $id ) ) {

				if ( $counts ) {
					$sql = "SELECT a.*, COUNT(DISTINCT b.ID) AS subscribers FROM {$wpdb->prefix}mailster_tags AS a LEFT JOIN ( {$wpdb->prefix}mailster_subscribers AS b INNER JOIN {$wpdb->prefix}mailster_tags_subscribers AS ab ON b.ID = ab.subscriber_id AND b.status IN(" . implode( ', ', $statuses ) . ')) ON a.ID = ab.tag_id GROUP BY a.ID ORDER BY name ASC';
				} else {
					$sql = "SELECT a.* FROM {$wpdb->prefix}mailster_tags AS a ORDER BY name ASC";
				}

				$sql = apply_filters( 'mailster_tag_get_sql', $sql, null, $statuses, $counts );

				$tags = $wpdb->get_results( $sql );

			} elseif ( is_numeric( $id ) ) {

				$sql = ( $counts )
					? "SELECT a.*, COUNT(DISTINCT b.ID) AS subscribers FROM {$wpdb->prefix}mailster_tags AS a LEFT JOIN ( {$wpdb->prefix}mailster_subscribers AS b INNER JOIN {$wpdb->prefix}mailster_tags_subscribers AS ab ON b.ID = ab.subscriber_id AND b.status IN(" . implode( ', ', $statuses ) . ')) ON a.ID = ab.tag_id WHERE a.ID = %d GROUP BY a.ID'
					: "SELECT a.* FROM {$wpdb->prefix}mailster_tags AS a WHERE a.ID = %d";

				$sql = apply_filters( 'mailster_tag_get_sql', $sql, $id, $statuses, $counts );

				$tags = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );

			} else {

				$ids = ! is_array( $id ) ? array( $id ) : $id;
				$ids = array_filter( $ids, 'is_numeric' );

				if ( ! empty( $ids ) ) {
					$sql  = ( $counts )
						? "SELECT a.*, COUNT(DISTINCT b.ID) AS subscribers FROM {$wpdb->prefix}mailster_tags AS a LEFT JOIN ( {$wpdb->prefix}mailster_subscribers AS b INNER JOIN {$wpdb->prefix}mailster_tags_subscribers AS ab ON b.ID = ab.subscriber_id AND b.status IN(" . implode( ', ', $statuses ) . ')) ON a.ID = ab.tag_id WHERE a.ID IN(' . implode( ', ', $ids ) . ') GROUP BY a.ID'
						: "SELECT a.* FROM {$wpdb->prefix}mailster_tags AS a WHERE a.ID IN(" . implode( ', ', $ids ) . ')';
					$sql  = apply_filters( 'mailster_tag_get_sql', $sql, $ids, $statuses, $counts );
					$tags = $wpdb->get_results( $sql );
				}
			}

			mailster_cache_add( $key, $tags );

		}

		return $tags;
	}


	/**
	 *
	 *
	 * @param unknown $name
	 * @param unknown $field  (optional)
	 * @param unknown $status (optional)
	 * @return unknown
	 */
	public function get_by_name( $name, $field = null, $status = 1 ) {

		global $wpdb;

		$key = 'tags_n_' . md5( serialize( $name ) . serialize( $field ) . serialize( $status ) );

		if ( false === ( $result = mailster_cache_get( $key ) ) ) {

			if ( ! is_null( $field ) && $field != 'subscribers' ) {

				$result = $wpdb->get_var( $wpdb->prepare( 'SELECT ' . esc_sql( $field ) . " FROM {$wpdb->prefix}mailster_tags WHERE name = %s LIMIT 1", $name ) );

			} else {

				$stati = ! is_array( $status ) ? array( $status ) : $status;

				$stati = array_filter( $stati, 'is_numeric' );

				$result = $wpdb->get_row( $wpdb->prepare( "SELECT a.*, COUNT(DISTINCT ab.subscriber_id) as subscribers FROM {$wpdb->prefix}mailster_tags as a LEFT JOIN ({$wpdb->prefix}mailster_subscribers as b INNER JOIN {$wpdb->prefix}mailster_tags_subscribers AS ab ON b.ID = ab.subscriber_id) ON a.ID = ab.tag_id WHERE b.status IN(" . implode( ', ', $stati ) . ') AND a.name = %s GROUP BY a.ID', $name ) );

				if ( is_null( $field ) ) {
					$result = $result;
				} elseif ( isset( $result->{$field} ) ) {
					$result = $result->{$field};
				} else {
					$result = false;
				}
			}

			mailster_cache_add( $key, $result );

		}

		return $result;
	}


	/**
	 *
	 *
	 * @param unknown $subscriber_id
	 * @param unknown $ids_only (optional)
	 * @return unknown
	 */
	public function get_by_subscriber( $subscriber_id, $ids_only = false ) {

		global $wpdb;

		$sql = "SELECT b.* from `{$wpdb->prefix}mailster_tags_subscribers` AS a LEFT JOIN `{$wpdb->prefix}mailster_tags` AS b ON b.ID = a.tag_id WHERE a.`subscriber_id` = %d";

		$result = $wpdb->get_results( $wpdb->prepare( $sql, $subscriber_id ) );

		return $ids_only ? wp_list_pluck( $result, 'ID' ) : $result;
	}


	/**
	 *
	 *
	 * @param unknown $tags    (optional)
	 * @param unknown $statuses (optional)
	 * @return unknown
	 */
	public function count( $tags = null, $statuses = null ) {

		global $wpdb;

		if ( $tags && ! is_array( $tags ) ) {
			$tags = array( $tags );
		}

		if ( ! is_null( $statuses ) && ! is_array( $statuses ) ) {
			$statuses = array( $statuses );
		}

		if ( is_array( $tags ) ) {
			$tags = array_filter( $tags, 'is_numeric' );
		}

		if ( is_array( $statuses ) ) {
			$statuses = array_filter( $statuses, 'is_numeric' );
		}

		$sql = "SELECT COUNT(DISTINCT a.ID) FROM {$wpdb->prefix}mailster_subscribers AS a LEFT JOIN ({$wpdb->prefix}mailster_tags AS b INNER JOIN {$wpdb->prefix}mailster_tags_subscribers AS ab ON b.ID = ab.tag_id) ON a.ID = ab.subscriber_id WHERE 1=1";

		$sql .= ( is_array( $tags ) )
			? ' AND b.ID IN (' . implode( ',', $tags ) . ')'
			: ( $tags === false ? ' AND b.ID IS NULL' : '' );

		if ( is_array( $statuses ) ) {
			$sql .= ' AND a.status IN (' . implode( ',', $statuses ) . ')';
		}

		$result = $wpdb->get_var( $sql );

		return $result ? (int) $result : 0;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_tag_count() {

		global $wpdb;

		$sql = "SELECT COUNT( * ) AS count FROM {$wpdb->prefix}mailster_tags";

		return $wpdb->get_var( $sql );
	}



	/**
	 *
	 *
	 * @param unknown $tag_id  (optional)
	 * @param unknown $statuses (optional)
	 * @return unknown
	 */
	public function get_member_count( $tag_id = null, $statuses = null ) {

		global $wpdb;

		$statuses = ! is_null( $statuses ) && ! is_array( $statuses ) ? array( $statuses ) : $statuses;
		$key      = is_array( $statuses ) ? 'tag_counts_' . implode( '|', $statuses ) : 'tag_counts';

		if ( false === ( $tag_counts = mailster_cache_get( $key ) ) ) {

			$sql = "SELECT a.ID, COUNT(DISTINCT ab.subscriber_id) AS count FROM {$wpdb->prefix}mailster_tags AS a LEFT JOIN ({$wpdb->prefix}mailster_subscribers AS b INNER JOIN {$wpdb->prefix}mailster_tags_subscribers AS ab ON b.ID = ab.subscriber_id) ON a.ID = ab.tag_id";

			if ( is_array( $statuses ) ) {
				$sql .= ' AND b.status IN (' . implode( ',', array_filter( $statuses, 'is_numeric' ) ) . ')';
			}

			$sql .= ' GROUP BY a.ID';

			$result = $wpdb->get_results( $sql );

			$tag_counts = array();

			foreach ( $result as $tag ) {
				if ( ! isset( $tag_counts[ $tag->ID ] ) ) {
					$tag_counts[ $tag->ID ] = 0;
				}

				$tag_counts[ $tag->ID ] += (int) $tag->count;
			}

			mailster_cache_add( $key, $tag_counts );

		}

		if ( is_null( $tag_id ) ) {
			return $tag_counts;
		}

		return isset( $tag_counts[ $tag_id ] ) && isset( $tag_counts[ $tag_id ] ) ? (int) $tag_counts[ $tag_id ] : 0;
	}




	/**
	 *
	 *
	 * @param unknown $id         (optional)
	 * @param unknown $status     (optional)
	 * @param unknown $name       (optional)
	 * @param unknown $show_count (optional)
	 * @param unknown $checked    (optional)
	 */
	public function return_it( $id = null, $status = null, $name = 'mailster_tags', $show_count = true, $checked = array(), $type = 'checkbox' ) {

		$html = '';
		if ( $tags = $this->get( $id, $status, (bool) $show_count ) ) {

			if ( ! is_array( $checked ) ) {
				$checked = array( $checked );
			}

			if ( $type == 'checkbox' ) {
				$html .= '<ul>';
				foreach ( $tags as $tag ) {
					$html .= '<li><label title="' . ( $tag->description ? $tag->description : $tag->name ) . '">' . '<input type="checkbox" value="' . $tag->ID . '" name="' . $name . '[]" ' . checked( in_array( $tag->ID, $checked ), true, false ) . ' class="tag' . '"> ' . $tag->name . '' . ( $show_count ? ' <span class="count">(' . number_format_i18n( $tag->subscribers ) . ( is_string( $show_count ) ? ' ' . $show_count : '' ) . ')</span>' : '' ) . '</label></li>';
				}
				$html .= '</ul>';
			} else {
				$html .= '<select class="widefat" multiple name="' . $name . '">';
				foreach ( $tags as $tag ) {
					$html .= '<option value="' . $tag->ID . '" ' . selected( in_array( $tag->ID, $checked ), true, false ) . '>' . $tag->name . '' . ( $show_count ? ' (' . number_format_i18n( $tag->subscribers ) . ( is_string( $show_count ) ? ' ' . $show_count : '' ) . ')' : '' ) . '</option>';
				}
				$html .= '</select>';
			}
		} elseif ( is_admin() ) {
				$html .= '<ul><li>' . __( 'No Tags found!', 'mailster' ) . '</li></ul>';
		}

		return $html;
	}


	/**
	 *
	 *
	 * @param unknown $id         (optional)
	 * @param unknown $status     (optional)
	 * @param unknown $name       (optional)
	 * @param unknown $show_count (optional)
	 * @param unknown $checked    (optional)
	 */
	public function print_it( $id = null, $status = null, $name = 'mailster_lists', $show_count = true, $checked = array(), $type = 'checkbox' ) {

		echo $this->return_it( $id, $status, $name, $show_count, $checked, $type );
	}



	/**
	 *
	 *
	 * @param unknown $new
	 */
	public function on_activate( $new ) {
	}
}
