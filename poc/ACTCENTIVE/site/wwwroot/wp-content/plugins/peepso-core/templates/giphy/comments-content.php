<div class="ps-media__attachment ps-media__attachment--giphy cstream-attachment giphy-attachment">
	<div class="ps-media ps-media--giphy ps-js-giphy">
    <?php
    $alt= '';
    $preview = '';
    global $post;
    if($post instanceof WP_Post) {
        $PeepSoUser = PeepSoUser::get_instance($post->post_author);
        $alt = sprintf(__('%s shared a GIF','peepso-core'), $PeepSoUser->get_fullname());
        $preview = __('Shared a GIF','peepso-core');
    }
    ?>
		<img src="<?php echo $giphy;?>" alt="<?php echo $alt;?>" data-preview="<?php echo $preview;?>">
	</div>
</div>
