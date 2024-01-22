<?php

class PeepSo3_REST_V1_Endpoint_Mayfly extends PeepSo3_REST_V1_Endpoint {

	protected $query;
	protected $sql;
	protected $orderby;
	protected $order;
	protected $status;
	protected $limit;

	protected $results = array();

	public function __construct() {

		parent::__construct();

		$this->query = sanitize_key($this->input->value('query', '', FALSE)); // SQL Safe
		$this->orderby = $this->input->value('orderby','id', ['id','name','value','expires']);
		$this->order = $this->input->value('order','desc', ['asc','desc']);
		$this->limit = $this->input->int('limit','50');
		$this->status = $this->input->value('status', 'all', ['all','expired','active']);

		$PeepSo3_Input = new PeepSo3_Input();

		$this->results['meta'] = array(
			'timestamp' => date('Y-m-d H:i:s'),
			'config' => [
				'limit' => $this->limit,
				'orderby' => $this->orderby,
				'order' => $this->order,
				'status' => $this->status,
				'query' => $this->query,

			],
		);
	}

	public function read($data) {
		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}peepso_mayfly WHERE 1=1 ";

		// Filter: search
		if(strlen($this->query)) {
			$sql .= " AND (`name` LIKE '%{$this->query}%' OR `value` LIKE '%{$this->query}%') ";
		}

		// Filter: expiry
		if($this->status == 'expired') {
			$sql .= "AND `expires` <= NOW() ";
		}elseif($this->status == 'active') {
			$sql .= "AND `expires` > NOW() ";
		}

		// Count all results
		$sql_count_total = str_ireplace("SELECT *", "SELECT COUNT(id) as total", $sql);
		$all_results = $wpdb->get_var($sql_count_total);

		// Order & Limit
		$sql .= " ORDER BY {$this->orderby} {$this->order} LIMIT {$this->limit} ";

		$this->results['meta']['sql']['query'] = $sql;

		$this->results['results'] = $wpdb->get_results($sql);

		$this->results['meta']['sql']['count_result'] = count($this->results['results']);
		$this->results['meta']['sql']['count_total'] = (int) $all_results;


		return $this->results;
	}

	protected function can_read() {
		return (bool) PeepSo::is_admin();
	}

	protected function can_create() {
		return (bool) PeepSo::is_admin();
	}

	protected function can_delete() {
		return (bool) PeepSo::is_admin();
	}

	protected function can_edit() {
		return (bool) PeepSo::is_admin();
	}

}