<?php

class PeepSoPostBackgroundsModel
{
    public $post_backgrounds;
    public $has_default_title;

    public function __construct($all = true)
    {
        $post_status = (true == $all) ? "any" : "publish";

        $this->post_backgrounds = array();
        $args = array(
            'post_type' => ['peepso_post_bg'],
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'posts_per_page' => -1,
            'post_status' => $post_status,
        );

        $posts = new WP_Query($args);

		if (!count($posts->posts)) {
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../install' . DIRECTORY_SEPARATOR . 'activate.php';
            $install = new PeepSoActivate();
            $install->plugin_activation();
            $posts = new WP_Query($args);
        }

		foreach ($posts->posts as $post) {
			$content = json_decode($post->post_content);

            $post_backgrounds = array(
                'id' => $post->post_parent,
                'post_id' => $post->ID,
                'published' => intval(('publish' == $post->post_status)),
                'title' => $post->post_title,
                'content' => $content,
                'image' => $content->image,
                'image_url' => strpos($content->image, 'https://') !== FALSE || strpos($content->image, 'http://') !== FALSE ?  $content->image : (is_numeric($content->image) ? wp_get_attachment_url($content->image) : plugin_dir_url(dirname(__FILE__)) . 'assets/images/post-backgrounds/' . $content->image),
                'custom' => isset($content->custom) ? 1 : 0,
                'order' => intval($post->menu_order),
                'has_default_title' => false,
            );

            if (1 == $post_backgrounds['custom']) {
                $post_backgrounds['id'] = $post->ID;
            }

            $this->post_backgrounds[$post_backgrounds['id']] = (object) $post_backgrounds;
        }
    }

	public function post_backgrounds($id) {
		if(!isset($this->post_backgrounds[$id])) {
			return clone $this->post_backgrounds[0];
		}

		return clone $this->post_backgrounds[$id];
	}

}
