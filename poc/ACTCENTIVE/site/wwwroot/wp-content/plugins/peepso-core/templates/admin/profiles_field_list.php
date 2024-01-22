<div id="peepso" class="ps-page--extended-profiles wrap">
	<?php PeepSoTemplate::exec_template('admin','profiles_buttons'); ;?>

	<div class="ps-js-fields-container ps-postbox--settings__wrapper">
		<?php

		foreach($data as $key => $field) {
			PeepSoTemplate::exec_template('admin','profiles_field', array('field'=>$field));
		}

		?>
	</div>
</div>
