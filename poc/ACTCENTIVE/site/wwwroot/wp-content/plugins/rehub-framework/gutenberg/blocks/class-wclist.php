<?php

namespace Rehub\Gutenberg\Blocks;

defined( 'ABSPATH' ) OR exit;

class WCList extends Basic {
	protected $name = 'woocommerce-list';

	protected $attributes = array(
		'selectedPosts' => array(
			'type'    => 'object',
			'default' => array(),
		),
		'titleTag'        => array(
			'type'    => 'string',
			'default' => 'h3',
		),
	);

	protected function render( $settings = array(), $inner_content = '' ) {
		$selected_posts = $settings['selectedPosts'];
		$title_tag        = $settings['titleTag'];

		if ( empty( $selected_posts ) || count( $selected_posts ) === 0 ) {
			echo '';
			return;
		}

		echo wpsm_toprating_shortcode( array( 'postid' => join( ' ,', $selected_posts ), 'title_tag'=>$title_tag ) );

	}
}