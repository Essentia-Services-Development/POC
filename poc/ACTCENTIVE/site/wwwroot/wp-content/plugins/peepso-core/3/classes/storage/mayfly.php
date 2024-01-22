<?php

/**
 * This class serves as a replacement for WP transients.
 * The purpose is to be able to store perishable bits of data supposed to expire after a certain period.
 * It's expected to be more dependable, work better with object caching and be easier to debug than transients.
 */
class PeepSo3_Mayfly {

	private static function checkmayfly() {
		return NULL !== get_option('peepso_install_date', NULL);
	}

	/**
	 * Creates a new entry
	 * @param string $name - Duplicates will be overwritten. Characters, underscores, numbers only.
	 * @param string|numeric|object|array $value - Must be a string, numeric, object or array. Will be automatically maybe_(un)serialize()d
	 * @param int $ttl - Time To Live in seconds. Default -1 = infinity (9999-09-09)
	 */
	public static function set(string $name, $value, int $ttl = -1) {
		if (!self::checkmayfly()) return;

		global $wpdb;

		// Validate name
		$name = strtolower($name);
		$sanitized_name = sanitize_key($name);
		if($sanitized_name != $name) {
			trigger_error('mayfly::'.__FUNCTION__ . ': Name must be a combination of letters, underscores and numbers - ' . maybe_serialize($name), E_USER_WARNING);
		}

		// Validate value
		if( !is_string($value) && !is_numeric($value) && !is_object($value)  && !is_array($value) ) {
			trigger_error( 'mayfly::'.__FUNCTION__ .': Value for ' . $name . ' must be numeric, string, array or object - ' . maybe_serialize($value), E_USER_WARNING);
		}

		self::del($name,'set', FALSE); // do not log deletes performed by set()

		$count = 0;
		$value = maybe_serialize($value);

		$insert = $wpdb->insert( $wpdb->prefix.'peepso_mayfly', ['name'=>$name, 'value'=>$value]);
		if($insert) {

			$query = "UPDATE {$wpdb->prefix}peepso_mayfly set `expires` = ";

			if( -1 == $ttl) {
				$query .= " '9999-09-09 09:09:09' ";
			} else {
				$query .= " DATE_ADD(`expires`, INTERVAL $ttl second) ";
			}

			$query .= " WHERE `id`={$wpdb->insert_id}";
			$count = $wpdb->query( $query );
		}

		//new PeepSoError('mayfly::'.__FUNCTION__."() - ". (int) $count . " rows\tTTL:   $ttl\tName:  $name\tValue: $value");
	}

	/**
	 * Returns unserialized original data. NULL will be returned if nothing is found.
	 * @param $name
	 * @return mixed|null
	 */
	public static function get($name) {
		if (!self::checkmayfly()) return;

		global $wpdb;

		$name = strtolower($name);
		$name = sanitize_key($name);
		$query = "SELECT value FROM `{$wpdb->prefix}peepso_mayfly` WHERE `name`='$name' AND `expires` > NOW()";
		$row = $wpdb->get_row($query, ARRAY_A);

		if(is_array($row) && array_key_exists('value',$row)) {
			return maybe_unserialize($row['value']);
		}

		return NULL;
	}

	/**
	 * Delete a specific entry
	 * @param $name
	 */
	public static function del($name, $extra='', $log = TRUE) {
		if (!self::checkmayfly()) return;

		global $wpdb;

		$name = strtolower($name);
		$name = sanitize_key($name);

		$count = $wpdb->delete($wpdb->prefix.'peepso_mayfly', ['name'=>$name]);

		if($log) {
			new PeepSoError( 'mayfly::'.__FUNCTION__ . "($extra) - " . (int) $count . " rows\tName: $name" );
		}
	}

	public static function del_like($name) {
        if (!self::checkmayfly()) return;

        global $wpdb;

        $name = strtolower($name);

        if(strlen($name)) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}peepso_mayfly WHERE `name` LIKE '$name'");
        }
    }

	/**
	 * Purge expired / all entries
	 * @param false $all - if TRUE, all entries will be deleted
	 */
	public static function clr($all=FALSE, $like=FALSE) {
		if (!self::checkmayfly()) return;
		
		global $wpdb;

		$where = " WHERE 1=1 ";

		if($like) {
			$where = "WHERE `name` LIKE ('%$like%')";
		}

		$query     = "DELETE FROM {$wpdb->prefix}peepso_mayfly $where ";

		if(!$all) {
			$query .= " AND `expires` IS NULL OR `expires` <= NOW() ";
		}

		$count = $wpdb->query( $query );

		//new PeepSoError('mayfly::'.__FUNCTION__."($like) - ". (int) $count . ' rows');

		return $count;
	}

	public static function clr_cache() {
		return PeepSo3_Mayfly::clr(TRUE, 'cache');
	}
}

new PeepSo3_Maintenance_Mayfly();
if(PeepSo::get_option_new('resend_activation')) {
	new PeepSo3_Maintenance_Resend_Activation(); // depends on MayFly
}
// EOF