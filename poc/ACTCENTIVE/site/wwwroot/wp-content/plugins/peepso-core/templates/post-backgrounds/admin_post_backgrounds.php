<?php

$assets_dir = plugin_dir_path(dirname(dirname(__FILE__))) . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
$assets_url = plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/images/';

add_thickbox();
wp_enqueue_media();
?>

<div id="peepso" class="ps-page--postbox-backgrounds wrap ps-page--post-backgrounds">

	<?php PeepSoTemplate::exec_template('post-backgrounds','admin_post_backgrounds_buttons'); ;?>

	<div class="ps-js-post-backgrounds-container ps-postbox--settings__wrapper">
		<?php
        foreach ($data as $key => $post_backgrounds) {
            PeepSoTemplate::exec_template('post-backgrounds', 'admin_post_backgrounds_item', array('post_backgrounds' => $post_backgrounds));
        }
        ?>
	</div>
</div>

<style type="text/css">

	#ps-post-backgrounds-icon-picker-toggle {
		float:right;
		display:block;
		padding:10px;
		fint-size: 12px;
		background: #f0f0f0;
	}
	#ps-post-backgrounds-admin-tutorial {
		width:100%;
		text-align:center;
		margin-bottom:20px;
	}
	.ps-post-backgrounds-notification {
		min-width:300px !important;
	}

	#ps-post-backgrounds-icon-picker {
		display:none;
		min-width:255px;

		position:relative;
		top: -7px;

		margin-left:32px;
		margin-right:20px;

		background: white;
		padding:10px;
		border: solid 1px #CCC;
		z-index: 99999 !important;
	}

	#ps-post-backgrounds-icon-picker h4 {
		clear:both;
	}

	#ps-post-backgrounds-icon-picker div.buttons {

	}

	#ps-post-backgrounds-icon-picker div.clearfix {
		margin-bottom:20px;
	}

	#ps-post-backgrounds-icon-picker button {
		width:50px;
		height:50px;
		display:block;
		float:left;
		padding-top:2px;
		padding-bottom:2px;
		margin:2px;
		border:none;
		background:#f0f0f0;
		overflow:hidden;
		text-align:center;
	}

	#ps-post-backgrounds-icon-picker button img {
		width: 20px;
	}

	#ps-post-backgrounds-icon-picker small {
		color:#aaaaaa;
	}

	.ps-post-backgrounds-hint {
		color: 		#aaaaaa;
		font-style: italic;
		font-size:	10px;
	}

	.ps-settings__label .warning {
		color: orange;
	}
    .ps-js-post-backgrounds-title  img {
        width:32px;
    }
</style>
