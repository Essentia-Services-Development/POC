<?php

class PeepSo3_Developer_Tools_Page_PeepSo_Mayfly extends PeepSo3_Developer_Tools_Page
{
	public function __construct()
	{
		$this->title='PeepSo Mayfly';
		$this->description = 'Ephemeral storage solution used by PeepSo as a replacement for WordPress transients';
	}

	public function page()
	{
		$script = trailingslashit( PeepSo3_Developer_Tools::assets_path() ) . 'js/developer_tools_peepso_mayfly.js';
		wp_enqueue_script('peepso-devtools-mayfly', $script);
		wp_localize_script('peepso-devtools-mayfly', 'peepso_devtools_mayfly', array(
			'rest_url' => esc_url_raw( rest_url( '/peepso/v1/mayfly' ) ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
		));

		$this->page_start();
		echo $this->page_data();
		$this->page_end('');
	}

	public function page_data()
	{
		$PeepSoDeveloperTools = PeepSo3_Developer_Tools::get_instance();
		ob_start();
		?>

		<br/>

		<input type="text" id="peepso_mayfly_filter_query" size="50" placeholder="Search name / value" />

		<input type="checkbox" id="peepso_mayfly_filter_autoreload" />
		<label for="peepso_mayfly_filter_autoreload">Automatic refreshing</label><br/><br/>

		Show
		<select id="peepso_mayfly_filter_limit">
			<?php
			$limits = [50,100,250,500,1000];
			foreach($limits as $limit) {
				echo "<option value='$limit'>$limit</option>";
			}
			?>
		</select>

		with status

		<select id="peepso_mayfly_filter_status">
			<option value="all">all</option>
			<option value="expired">expired</option>
			<option value="active">active</option>
		</select>

		entries ordered by

		<select id="peepso_mayfly_filter_orderby">
			<option value="id">creation date (ID)</option>
			<option value="expires">expiry date</option>
			<option value="name">name</option>
			<option value="value">value</option>
		</select>

		<select id="peepso_mayfly_filter_order">
			<option value="desc">&darr;</option>
			<option value="asc">&uarr;</option>
		</select>

		<br/><br/>

		<style>
			.peepso_developer_tools-content {
				width: 100% !important;
			}
			.wp-list-table td {
				vertical-align: middle;
			}
			.wp-list-table th:nth-child(1),
			.wp-list-table td:nth-child(2),
			.wp-list-table td:nth-child(4),
			.wp-list-table td:nth-child(5) {
				width: 1%;
				white-space: nowrap;
				overflow: visible;
			}
			.wp-list-table td:nth-child(2) {
				width: 360px;
			}
			.wp-list-table td:nth-child(2) input {
				width: 100%;
			}
			.wp-list-table td:nth-child(3) div {
				font-family: monospace;
				max-height: 115px;
				overflow: auto;
				overflow-wrap: break-word;
				overflow-wrap: anywhere;
			}
			@media screen and (max-width: 1200px) {
				.wp-list-table td:nth-child(2) {
					width: 200px;
				}
			}
		</style>

		<table class="wp-list-table widefat striped table-view-list is-expanded">
			<thead>
				<tr>
					<th><a href="#" class="peepso_mayfly_sort_column" data-orderby="id">ID</a></th>
					<th class="column-primary"><a href="#" class="peepso_mayfly_sort_column" data-orderby="name">Name</a></th>
					<th><a href="#" class="peepso_mayfly_sort_column" data-orderby="value">Value</a></th>
					<th><a href="#" class="peepso_mayfly_sort_column" data-orderby="id">Created</a></th>
					<th><a href="#" class="peepso_mayfly_sort_column" data-orderby="expires">Expires</a></th>
				</tr>
			</thead>
			<tbody id="peepso_mayfly_results"></tbody>
		</table>

		<?php
		return ob_get_clean();
	}

	public static function peepso_developer_tools_buttons($buttons)
	{
		return array();
	}
}

// EOFpage_phpinfo.php