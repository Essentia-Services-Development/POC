<?php
	while ($PeepSoActivity->next_post()) {
        // display post and any comments
        $PeepSoActivity->post_data['pinned'] = get_post_meta($PeepSoActivity->post_data['ID'],'peepso_pinned', TRUE);
        $PeepSoActivity->post_data['pinned_by'] = get_post_meta($PeepSoActivity->post_data['ID'],'peepso_pinned_by', TRUE);
        $PeepSoActivity->post_data['pinned_date'] = get_post_meta($PeepSoActivity->post_data['ID'],'peepso_pinned_date', TRUE);
        PeepSoTemplate::exec_template('account', 'activity-post', $PeepSoActivity->post_data);

		/*
		 * https://github.com/peepso/peepso/issues/1536
		 * reset wp query to prevent function in another plugin or theme get wrong data
		 */
		wp_reset_query();
    }
?>