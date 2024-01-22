<?php
/**
 * Rehub Framework Metabox Functions
 *
 * @package ReHub\Functions
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* load metaboxes */
$page_toptable_metabox  = rf_locate_template('inc/metabox/page_toptable.php'); 
$page_topchart_metabox  = rf_locate_template('inc/metabox/page_topchart.php');
$page_toptable_metabox_obj = new VP_Metabox($page_toptable_metabox);
$page_topchart_metabox_obj = new VP_Metabox($page_topchart_metabox);


//Here we can enable old deprecated Post format
$oldpanel = REHub_Framework::get_option('old_review_meta');
if($oldpanel)
{
	$post_type_metabox  = rf_locate_template('inc/metabox/post_type.php');
	$post_type_metabox_obj = new VP_Metabox($post_type_metabox);
	add_action('save_post', 'rehub_save_post', 13);
	if( !function_exists('rehub_save_post') ) {
		function rehub_save_post( $post_id ){
			global $post;

			$rehub_meta_id = 'rehub_post';

			// check autosave
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;

			// make sure data came from our meta box, verify nonce
			$nonce = isset($_POST[$rehub_meta_id.'_nonce']) ? sanitize_text_field($_POST[$rehub_meta_id.'_nonce']) : NULL ;
			
			if (!wp_verify_nonce($nonce, $rehub_meta_id)) return $post_id;

			// check user permissions
			if ($_POST['post_type'] == 'page')
			{
				if (!current_user_can('edit_page', $post_id)) return $post_id;
			}
			else
			{
				if (!current_user_can('edit_post', $post_id)) return $post_id;
			}

			// authentication passed, process data
			$meta_data = isset( $_POST[$rehub_meta_id] ) ? (array) $_POST[$rehub_meta_id] : NULL ;

			if ( !wp_is_post_revision( $post_id ) ) {
				// if is review post, save data
				if( $meta_data['rehub_framework_post_type'] === 'review' )
				{
					$thecriteria = $meta_data['review_post'][0]['review_post_criteria'];
					array_pop($thecriteria);
					$manual_score = $meta_data['review_post'][0]['review_post_score_manual'];
					$total_scores = rehub_get_overall_score($thecriteria, $manual_score);
					update_post_meta($post_id, 'rehub_review_overall_score', $total_scores); // save total score of review
					$editor_score = rehub_get_overall_score_editor($thecriteria, $manual_score);
					update_post_meta($post_id, 'rehub_review_editor_score', $editor_score); // save editor score of review
			
					$firstcriteria = (!empty($thecriteria[0]['review_post_name'])) ? $thecriteria[0]['review_post_name'] : ''; 
					if($firstcriteria) :
						foreach ($thecriteria as $key=>$criteria) { 
							$key = $key + 1;
							$metakey = '_review_score_criteria_'.$key;
							update_post_meta($post_id, $metakey, $criteria['review_post_score']);
						}
					endif;
				}
			}
		}
	}
}