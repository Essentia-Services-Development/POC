<table id="pmpro_levels_table" class="pmpro_checkout">
<thead>
  <tr>
	<th><?php echo __('Level', 'pmpro');?></th>
	<th><?php echo __('Price', 'pmpro');?></th>	
	<th>&nbsp;</th>
  </tr>
</thead>
<tbody>
	<?php	
	$count = 0;
	foreach($pmpro_levels as $level)
	{
		$current_level = false;
	?>
	<tr class="<?php if($count++ % 2 == 0) { ?>odd<?php } ?><?php if($current_level == $level) { ?> active<?php } ?>">
		<td><?php echo $current_level ? "<strong>{$level->name}</strong>" : $level->name?></td>
		<td>
			<?php 
				if(pmpro_isLevelFree($level))
					$cost_text = "<strong>" . __("Free", "pmpro") . "</strong>";
				else
					$cost_text = pmpro_getLevelCost($level, true, true); 
				$expiration_text = pmpro_getLevelExpiration($level);
				if(!empty($cost_text) && !empty($expiration_text))
					echo $cost_text . "<br />" . $expiration_text;
				elseif(!empty($cost_text))
					echo $cost_text;
				elseif(!empty($expiration_text))
					echo $expiration_text;
			?>
		</td>
		<td>
			<a class="pmpro_btn pmpro_btn-select" href="<?php echo PeepSo::get_page('register') . '?pmp_checkout/'.$user_id . '/' . $level->id; ?>"><?php echo __('Select', 'pmpro');?></a>
		</td>
	</tr>
	<?php
	}
	?>
</tbody>
</table>