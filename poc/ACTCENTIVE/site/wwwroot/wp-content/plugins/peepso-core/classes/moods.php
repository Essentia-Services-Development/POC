<?php

class PeepSoMoods {

	private static $_instance = NULL;
	public $moods = array();

	const META_POST_MOOD = '_peepso_post_mood';

	private $class_prefix = 'ps-emo-';
	public $is_enabled = FALSE;

	/**
	 * Initialize all variables, filters and actions
	 */
	private function __construct()
	{
		$this->is_enabled = PeepSo::get_option('moods_enable', 0) == 1 ? TRUE : FALSE;
		add_action('peepso_init', array(&$this, 'init'));
	}

	/*
	 * Return singleton instance of plugin
	 */

	public static function get_instance()
	{
		if (NULL === self::$_instance)
		{
			self::$_instance = new self();
		}

		return (self::$_instance);
	}

	/*
	 * Initialize the PeepSoMoods plugin
	 */

	public function init()
	{
		if (!is_admin()) {
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
			add_filter('peepso_post_extras', array(&$this, 'filter_post_extras'), 10, 1);

			if ($this->is_enabled) {
				add_action('wp_insert_post', array(&$this, 'save_mood'), 100, 3);
				add_action('peepso_activity_after_save_post', array(&$this, 'save_mood'), 100);
				add_filter('peepso_activity_allow_empty_content', array(&$this, 'filter_activity_allow_empty_content'), 10, 1);
				add_filter('peepso_postbox_interactions', array(&$this, 'filter_postbox_interactions'), 20);
				add_filter('peepso_activity_post_edit', array(&$this, 'filter_post_edit'), 10, 1);
			}
		}

		add_filter('peepso_moods_mood_value', array(&$this, 'filter_mood_value'));

		// initialize moods list
		$this->moods = array(
			1 => __('joyful', 'peepso-core'),
			2 => __('meh', 'peepso-core'),
			3 => __('love', 'peepso-core'),
			4 => __('flattered', 'peepso-core'),
			5 => __('crazy', 'peepso-core'),
			6 => __('cool', 'peepso-core'),
			7 => __('tired', 'peepso-core'),
			8 => __('confused', 'peepso-core'),
			9 => __('speechless', 'peepso-core'),
			10 => __('confident', 'peepso-core'),
			11 => __('relaxed', 'peepso-core'),
			12 => __('strong', 'peepso-core'),
			13 => __('happy', 'peepso-core'),
			14 => __('angry', 'peepso-core'),
			15 => __('scared', 'peepso-core'),
			16 => __('sick', 'peepso-core'),
			17 => __('sad', 'peepso-core'),
			18 => __('blessed', 'peepso-core')
		);
	}

	/**
	 * Load required styles and scripts
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_style('peepso-moods', PeepSo::get_asset('css/moods.css'), array('peepso'), PeepSo::PLUGIN_VERSION, 'all');

		if ($this->is_enabled) {
			wp_enqueue_script('peepso-moods', PeepSo::get_asset('js/moods.min.js'), array('peepso', 'peepso-postbox'), PeepSo::PLUGIN_VERSION, TRUE);
		}
	}

	/**
	 * This function inserts mood selection box on the post box
	 * @param array $out_html is the formated html code that get inserted in the postbox
	 */
	public function filter_postbox_interactions($out_html = array())
	{
		$mood_list = '';
		foreach ($this->moods as $id => $mood)
		{
			$mood_list .= "
				<a class='ps-postbox__moods-item ps-js-mood-item' id='postbox-mood-{$id}' href='#' data-option-value='{$id}' data-option-display-value='{$mood}' onclick='return false;'>
					<i class='ps-emoticon {$this->class_prefix}{$id}'></i><span>" . $mood . "</span>
				</a>";
		}

		$mood_remove = __('Remove Mood', 'peepso-core');
		$mood_ux = '<div style="display:none">
				<input type="hidden" id="postbox-mood-input" name="postbox-mood-input" value="0" />
				<span id="mood-text-string">' . __(' feeling ', 'peepso-core') . '</span>
				</div>';

		$mood_data = array(
			'label' => __('Mood', 'peepso-core'),
			'id' => 'mood-tab',
			'class' => 'ps-postbox__menu-item ps-postbox__menu-item--moods',
			'icon' => 'gcir gci-grin',
			'click' => 'return;',
			'title' => __('Mood', 'peepso-core'),
			'extra' => "<div id='postbox-mood' class='ps-dropdown__menu ps-postbox__moods ps-js-postbox-mood'>
							<div class='ps-postbox__moods-inner'>
								<div class='ps-postbox__moods-list'>
									{$mood_list}
									<button id='postbox-mood-remove' class='ps-btn ps-btn--sm ps-postbox__moods-remove' title='{$mood_remove}'><i class='gcis gci-times-circle'></i> {$mood_remove}</button>
								</div>
							</div>
						</div>{$mood_ux}"
		);

		$out_html['Mood'] = $mood_data;
		return ($out_html);
	}

	/**
	 * This function saves the mood data for the post
	 * @param $post_id is the ID assign to the posted content
	 */
	public function save_mood($post_id, $post = null, $update = false)
	{
		$input = new PeepSoInput();
		$mood = $input->int('mood');

		if (apply_filters('peepso_moods_apply_to_post_types', array(PeepSoActivityStream::CPT_POST)))
		{
			if (empty($mood) && !$post) {
				delete_post_meta($post_id, self::META_POST_MOOD);
			} else if ($mood) {
				update_post_meta($post_id, self::META_POST_MOOD, $mood);
			}
		}
	}

	/**
	 * TODO: docblock
	 */
	public function filter_post_extras( $extras = array() )
	{
		global $post;
		$post_mood_id = get_post_meta($post->ID, self::META_POST_MOOD, TRUE);
		$post_mood = apply_filters('peepso_moods_mood_value', $post_mood_id);

		if (!empty($post_mood))
		{
			ob_start();?>
			<span class="ps-post__mood"><i class="ps-emoticon <?php echo $this->class_prefix . $post_mood_id;?>"></i><span><?php echo __(' feeling ', 'peepso-core') . ucwords($post_mood);?></span></span>
			<?php
			$extras[] = ob_get_clean();
		}

		return $extras;
	}

	/**
	 * Allows empty post content if a mood is set
	 * @param boolean $allowed Current state of the allow posting check
	 * @return boolean Rturns TRUE when mood information is present to indicate that a post with not content and a mood is publishable
	 */
	public function filter_activity_allow_empty_content($allowed)
	{
		$input = new PeepSoInput();
		$mood = $input->int('mood');
		if (!empty($mood))
		{
			$allowed = TRUE;
		}

		return ($allowed);
	}

	public function filter_mood_value($mood)
	{
		if (!$mood)
		{
			return;
		}

		if (array_key_exists($mood, $this->moods))
		{
			return $this->moods[$mood];
		}

		return $mood . "*";
	}

	public function filter_post_edit( $data = array() )
	{
		$input = new PeepSoInput();
		$post_id = $input->int('postid');

		$mood = get_post_meta($post_id, self::META_POST_MOOD, TRUE);
		if (!empty($mood)) {
			$data['mood'] = $mood;
		}

		return $data;
	}

}

// EOF
