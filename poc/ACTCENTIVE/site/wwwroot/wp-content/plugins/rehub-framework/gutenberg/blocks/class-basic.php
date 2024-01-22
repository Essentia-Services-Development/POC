<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') or exit;

use Rehub\Gutenberg\Blocks\Basic\Inline_Attributes_Trait;
use WP_REST_Request;
use WP_REST_Server;

abstract class Basic
{
	use Inline_Attributes_Trait;

	protected static $index = 0;

	protected $render_index = 1;
	protected $name = 'basic';

	protected $is_rest = false;

	protected $attributes = array();

	public function __construct()
	{
		add_action('init', array($this, 'init_handler'));
		add_filter('rehub/gutenberg/default_attributes', array($this, 'get_default_attributes'));

		$this->ajaxActions();

		$this->construct();
	}

	protected function construct()
	{
	}
	private function __clone()
	{
	}
	public function __wakeup()
	{
	}

	protected function ajaxActions()
	{
		add_action('wp_ajax_rehub_render_preview', array($this, 'render_preview'));

		add_action('wp_ajax_get_taxonomies_list', array($this, 'get_taxonomies_list'));
		add_action('wp_ajax_get_taxonomy_terms', array($this, 'get_taxonomy_terms'));
		add_action('wp_ajax_get_taxonomy_terms_search', array($this, 'get_taxonomy_terms_search'));
		add_action('wp_ajax_get_products_title_list', array($this, 'get_products_title_list'));
		add_action('wp_ajax_get_post_type_el', array($this, 'get_post_type_el'));
	}

	public function rest_handler(WP_REST_Request $Request)
	{
		$data = array(
			'rendered' => $this->render_block($Request->get_params(), ''),
		);

		return rest_ensure_response($data);
	}

	public function init_handler()
	{
		register_block_type('rehub/' . $this->name, array(
			'attributes'      => $this->attributes,
			'render_callback' => array($this, 'render_block'),
		));
	}

	public function restHandler(WP_REST_Request $Request)
	{
		$data = array(
			'rendered' => $this->render_block($Request->get_params(), ''),
		);

		return rest_ensure_response($data);
	}

	public function render_preview()
	{
		$settings = $_POST['settings'];
		$type = $_POST['type'];
		$this->normalize_arrays($settings);

		if (!empty($settings['listargs'])) {
			if (!empty($settings['listargs']['section'])) {
				foreach ($settings['listargs']['section'] as $index => $section) {
					if (!empty($section['imageMapper'])) {
						$imagearray = array();
						foreach ($section['imageMapper'] as $image) {
							$imageindex = $image['image']['id'];
							$valueindex = $image['value'];
							$imagearray[$imageindex] = $valueindex;
						}
						$settings['listargs']['section'][$index]['imageMapper'] = $imagearray;
					}
				}
			}
			$settings['listargs'] = json_encode($settings['listargs']);
		}

		if (!empty($settings['attrpanel'])) {
			$settings['attrelpanel'] = rawurlencode(json_encode($settings['attrpanel']));
		}

		if (!empty($settings['filterpanel'])) {
			$settings['filterpanel'] = $this->filter_values($settings['filterpanel']);
			$settings['filterpanel'] = rawurlencode(json_encode($settings['filterpanel']));
		}

		$preview = '';

		switch ($type) {
			case 'wc-query':
				$preview = wpsm_woogrid_shortcode($settings);
				break;
			case 'advanced-listing':
				$preview = wpsm_list_constructor($settings);
				break;
			case 'wc-deal-list':
				if ($settings['designtype'] == 'row' || $settings['designtype'] == 'compact') {
					$preview = wpsm_woorows_shortcode($settings);
				} else {
					$preview = wpsm_woolist_shortcode($settings);
				}
				break;
			case 'deal-coupon-list':
				$preview = wpsm_offer_list_loop_shortcode($settings);
				break;
			case 'simple-list':
				$preview = recent_posts_function($settings);
				break;
			case 'deal-coupon-grid':
				$preview = wpsm_compactgrid_loop_shortcode($settings);
				break;
			case 'news-directory-list':
				$preview = wpsm_small_thumb_loop_shortcode($settings);
				break;
			case 'colored-post-grid':
				$preview = wpsm_colorgrid_shortcode($settings);
				break;
			case 'news-block':
				$preview = wpsm_news_with_thumbs_mod_shortcode($settings);
				break;
			case 'wc-featured-section':
				$preview = wpsm_woofeatured_function($settings);
				break;
			case 'featured-section':
				$preview = wpsm_featured_function($settings);
				break;
			case 'tax-archive':
				if (!empty($settings['child_of'])) {
					$settings['child_of'] = get_term_by('slug', $settings['child_of']['id'], $settings['taxonomy'])->term_id;
				}

				if (!empty($settings['include'])) {
					$this->normalize_terms($settings);
				}
				$preview = wpsm_tax_archive_shortcode($settings);
				break;
			case 'searchbox':
				$preview = wpsm_searchbox_function($settings);
				break;
		}

		wp_send_json_success($preview);
	}

	protected function filter_values($haystack)
	{
		foreach ($haystack as $key => $value) {
			if (is_array($value)) {
				$haystack[$key] = $this->filter_values($haystack[$key]);
			}

			if (empty($haystack[$key])) {
				unset($haystack[$key]);
			}
		}
		return $haystack;
	}

	protected function normalize_arrays(&$settings, $fields = ['cat', 'tag', 'ids', 'field', 'cat_exclude', 'tag_exclude', 'postid', 'tax_slug_exclude', 'tax_slug', 'user_id'])
	{
		foreach ($fields as $field) {

			if (!isset($settings[$field]) || !is_array($settings[$field]) || empty($settings[$field])) {
				$settings[$field] = null;
				continue;
			}
			$ids = '';
			$last = count($settings[$field]);
			foreach ($settings[$field] as $item) {
				$ids .= $item['id'];
				if (0 !== --$last) {
					$ids .= ',';
				}
			}
			$settings[$field] = $ids;
		}
		if (isset($settings['select_type']) && $settings['select_type'] == 'manual') {
			$settings['data_source'] = 'ids';
		}
	}

	public function normalize_terms(&$settings)
	{
		$terms = [];
		foreach ((array) $settings['include'] as $include) {
			$terms[] = get_term_by('slug', $include['id'], $settings['taxonomy'])->term_id;
		}
		return $settings['include'] = implode(',', $terms);
	}

	protected function render($settings, $inner_content)
	{
		return '';
	}

	public function render_block($settings, $inner_content)
	{
		$settings = array_merge(
			$this->array_column_ext($this->attributes, 'default', -1),
			is_array($settings) ? $settings : array()
		);
		ob_start();
		$content = $this->render($settings, $inner_content);

		return !empty($content) ? $content : ob_get_clean();
	}

	protected function array_column_ext($array, $columnkey, $indexkey = null)
	{
		$result = array();
		foreach ($array as $subarray => $value) {
			if (array_key_exists($columnkey, $value)) {
				$val = $array[$subarray][$columnkey];
			} else if ($columnkey === null) {
				$val = $value;
			} else {
				continue;
			}

			if ($indexkey === null) {
				$result[] = $val;
			} else if ($indexkey == -1 || array_key_exists($indexkey, $value)) {
				$result[($indexkey == -1) ? $subarray : $array[$subarray][$indexkey]] = $val;
			}
		}
		return $result;
	}

	public function get_default_attributes($attributes)
	{
		$attributes[$this->name] = $this->attributes;

		return $attributes;
	}

	public function get_taxonomies_list()
	{
		$isWoo = (!empty($_POST['isWoo'])) ? $_POST['isWoo'] : '';
		$exclude_list = array_flip([
			'nav_menu', 'link_category', 'post_format',
			'elementor_library_type', 'elementor_library_category', 'action-group'
		]);
		if ($isWoo) {
			$exclude_list = array_flip([
				'category', 'post_tag', 'nav_menu', 'link_category', 'post_format',
				'elementor_library_type', 'elementor_library_category', 'action-group'
			]);
		}

		$response_data = [
			'results' => []
		];
		$args = [];
		foreach (get_taxonomies($args, 'objects') as $taxonomy => $object) {
			if (isset($exclude_list[$taxonomy])) {
				continue;
			}

			$taxonomy = esc_html($taxonomy);
			$response_data['results'][] = [
				'value'    => $taxonomy,
				'label'  => esc_html($object->label),
			];
		}
		wp_send_json_success($response_data);
	}

	public function get_taxonomy_terms()
	{
		$response_data = [
			'results' => []
		];

		if (empty($_POST['taxonomy'])) {
			wp_send_json_success($response_data);
		}

		$taxonomy = sanitize_text_field($_POST['taxonomy']);
		$selected = isset($_POST['selected']) ? $_POST['selected'] : '';
		$terms = get_terms([
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'number' => 15,
			'exclude' => $selected
		]);

		foreach ($terms as $term) {
			$response_data['results'][] = [
				'id'    	=> $term->slug,
				'label'  	=> esc_html($term->name),
				'value' 	=> $term->term_id
			];
		}

		wp_send_json_success($response_data);
	}

	public function get_taxonomy_terms_search()
	{
		global $wpdb;
		$taxonomy = isset($_POST['taxonomy']) ? $_POST['taxonomy'] : '';
		$query = [
			"select" => "SELECT SQL_CALC_FOUND_ROWS a.term_id AS id, b.name as name, b.slug AS slug
                        FROM {$wpdb->term_taxonomy} AS a
                        INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id",
			"where"  => "WHERE a.taxonomy = '{$taxonomy}'",
			"like"   => "AND (b.slug LIKE '%s' OR b.name LIKE '%s' )",
			"offset" => "LIMIT %d, %d"
		];

		$search_term = '%' . $wpdb->esc_like($_POST['search']) . '%';
		$offset = 0;
		$search_limit = 100;

		$final_query = $wpdb->prepare(implode(' ', $query), $search_term, $search_term, $offset, $search_limit);
		// Return saved values

		$results = $wpdb->get_results($final_query);

		$total_results = $wpdb->get_row("SELECT FOUND_ROWS() as total_rows;");
		$response_data = [
			'results'       => [],
		];

		if ($results) {
			foreach ($results as $result) {
				$response_data['results'][] = [
					'id'    	=> esc_html($result->slug),
					'label'  	=> esc_html($result->name),
					'value' 	=> (int)$result->id
				];
			}
		}

		wp_send_json_success($response_data);
	}

	public function get_products_title_list()
	{
		global $wpdb;

		$query = [
			"select" => "SELECT SQL_CALC_FOUND_ROWS ID, post_title FROM {$wpdb->posts}",
			"where"  => "WHERE post_type IN ('post', 'product', 'blog', 'page')",
			"like"   => "AND post_title NOT LIKE %s",
			"offset" => "LIMIT %d, %d"
		];

		$search_term = '';
		if (!empty($_POST['search'])) {
			$search_term = $wpdb->esc_like($_POST['search']) . '%';
			$query['like'] = 'AND post_title LIKE %s';
		}

		$offset = 0;
		$search_limit = 100;
		if (isset($_POST['page']) && intval($_POST['page']) && $_POST['page'] > 1) {
			$offset = $search_limit * absint($_POST['page']);
		}

		$final_query = $wpdb->prepare(implode(' ', $query), $search_term, $offset, $search_limit);
		// Return saved values

		if (!empty($_POST['saved']) && is_array($_POST['saved'])) {
			$saved_ids = $_POST['saved'];
			$placeholders = array_fill(0, count($saved_ids), '%d');
			$format = implode(', ', $placeholders);

			$new_query = [
				"select" => $query['select'],
				"where"  => $query['where'],
				"id"     => "AND ID IN( $format )",
				"order"  => "ORDER BY field(ID, " . implode(",", $saved_ids) . ")"
			];

			$final_query = $wpdb->prepare(implode(" ", $new_query), $saved_ids);
		}

		$results = $wpdb->get_results($final_query);
		$total_results = $wpdb->get_row("SELECT FOUND_ROWS() as total_rows;");
		$response_data = [
			'results'       => [],
			'total_count'   => $total_results->total_rows
		];

		if ($results) {
			foreach ($results as $result) {
				$response_data['results'][] = [
					'value'    => $result->ID,
					'id'    => $result->ID,
					'label'  => esc_html($result->post_title)
				];
			}
		}

		wp_send_json_success($response_data);
	}

	public function get_post_type_el()
	{
		$post_types = get_post_types(array('public' => true));
		$post_types_list = array();
		foreach ($post_types as $post_type) {
			if ($post_type !== 'revision' && $post_type !== 'nav_menu_item' && $post_type !== 'attachment') {
				$post_types_list[] = array(
					'label' => $post_type,
					'value' => $post_type
				);
			}
		}
		wp_send_json_success($post_types_list);
	}
}
