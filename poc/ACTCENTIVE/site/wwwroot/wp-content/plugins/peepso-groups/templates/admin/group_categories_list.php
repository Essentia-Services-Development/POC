<div id="peepso" class="ps-page--group-categories wrap">
	<?php PeepSoTemplate::exec_template('admin','group_categories_button'); ;?>

	<div class="ps-js-group-categories-container ps-postbox--settings__wrapper">
		<?php

		foreach($data as $key => $category) {
			PeepSoTemplate::exec_template('admin','group_categories', array('category'=>$category));
		}

		?>
	</div>
</div>
