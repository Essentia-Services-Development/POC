<?php
	if(count($list_photos) > 0) {
		foreach ($list_photos as $photo) {
			?>
  <div class="block">
    <img src="<?php echo $photo->location; ?>" />
    <div></div>
  </div>
			<?php
		}
	}
?>
