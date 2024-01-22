<div><?php echo __('Are you sure want to remove a friend?', 'friendso'); ?></div>

<?php

// Additional popup options (optional).
$opts = array(
	'title' => __('Remove Friend', 'friendso'),
	'actions' => array(
		array(
			'label' => __('Cancel', 'friendso'),
			'class' => 'ps-js-cancel'
		),
		array(
			'label' => __('Remove Friend', 'friendso'),
			'class' => 'ps-js-submit',
			'loading' => true,
			'primary' => true
		)
	)
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>
