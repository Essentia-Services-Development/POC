<?php
if(isset($_GET['dismiss-admin-tutorial'])) {
	add_user_meta(get_current_user_id(), 'peepso_vip_hide_admin_tutorial', 1, TRUE);
}

if(isset($_GET['dismiss-admin-tutorial-reset'])) {
	delete_user_meta(get_current_user_id(), 'peepso_vip_hide_admin_tutorial');
}

$assets_dir = PeepSo::get_plugin_dir() .'assets'.DIRECTORY_SEPARATOR;
$assets_url = plugin_dir_url($assets_dir).'assets'.DIRECTORY_SEPARATOR;

$svg_dir = $assets_dir . 'images'.DIRECTORY_SEPARATOR . 'vip' . DIRECTORY_SEPARATOR;
$svg_url = $assets_url . 'images'.DIRECTORY_SEPARATOR . 'vip' . DIRECTORY_SEPARATOR;

$user_svg_dir = PeepSo::get_peepso_dir().'overrides'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'vipicons';
$user_svg_url = PeepSo::get_peepso_uri().'overrides'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'vipicons'.DIRECTORY_SEPARATOR;

add_thickbox();
wp_enqueue_media();
?>

<div id="peepso" class="ps-page--vip-icons wrap">

	<?php if(1 != get_user_meta(get_current_user_id(), 'peepso_vip_hide_admin_tutorial', TRUE)) { ?>
	<div id="ps-vipicons-admin-tutorial">
		<p>
			<h3><?php echo __('Welcome to  PeepSo VIP', 'peepso-core');?></h3>
			<h4><?php echo __('Here\'s what you should know:', 'peepso-core');?></h4>
			<a href="<?php echo $_SERVER['REQUEST_URI'];?>&dismiss-admin-tutorial"><small>(<?php echo __('click here to dismiss permanently', 'peepso-core');?>)</small></a>
		</p>
		<img src="<?php echo $assets_url . 'images' . DIRECTORY_SEPARATOR;?>vip-admin-tutorial.png" />
	</div>
	<?php } ?>

	<?php PeepSoTemplate::exec_template('vip','admin_vipicons_buttons'); ;?>

	<div class="ps-js-vipicons-container ps-postbox--settings__wrapper">
		<?php

		foreach($data as $key => $icon) {
			PeepSoTemplate::exec_template('vip','admin_vipicon', array('icon'=>$icon));
		}

		?>
	</div>
</div>
<?php
// Bundled images
$categories = array(
	'def'		=> __('VIP Icons', 'peepso-core'),
	'custom'	=> __('FTP Uploads', 'peepso-core'),
);

$images = array();

$files = scandir($svg_dir);

foreach($files as $file) {
	if(!strstr($file, '.svg')) { continue;}
	$images[substr($file, 0, strpos($file, '_'))][] = $file;
}

// Custom FTP images
if(@is_dir($user_svg_dir)) {
	$files = @scandir($user_svg_dir);
	if(count($files)) {
		foreach($files as $file) {
			if(!strstr($file, '.svg')) { continue;}
			$images['custom'][] = $file;
		}
	}
}
?>

<div id="ps-vipicons-icon-picker">
	<a href="#" id="ps-vipicons-icon-picker-toggle" onclick="return false;"><i class="ps-icon-cancel"></i> <?php echo __('Cancel','peepso-core');?></a>
	<input type="hidden" id="icon-picker-current" />
	<h3>Media Library</h3>
	<p>
		<a class="btn btn-sm btn-info btn-img-vipicon" href="#"><?php echo __('Add icon', 'peepso-core');?></a>
	</p>
	<small>
		<?php echo __('Want to use SVG files with Media Library? We recommend', 'peepso-core');?>
		<a href="plugin-install.php?tab=plugin-information&amp;plugin=svg-support&amp;TB_iframe=true&amp;width=772&amp;height=315" class="thickbox open-plugin-details-modal" aria-label="More information about SVG Support" data-title="SVG Support">SVG Support by Benbohdi</a>.
	</small>

	<h3>Other Files</h3>
	<?php
foreach($images as $category=>$files) {
	?>
	<h4><?php echo $categories[$category];?></h4>
	<div class="buttons">
	<?php
	$url = $svg_url;

	if('custom' == $category) {
		echo "<small>".__('Use FTP to upload your custom SVG images to', 'peepso-core')." $user_svg_dir</small><br/>";
		$url = $user_svg_url;
	}

	if(!count($files)) {
		echo __('No icons','peepso-core');
		continue;
	}

	foreach($files as $key => $file) {
		$id = $file;
		$display_file = $file;
		if('custom' == $category || 'library' == $category) {
			$id = 'custom.'.$file;
			$display_file = basename($file);
		} else {
			$display_file = str_replace(array("{$category}_",".svg"),'',$display_file);
		}
	?>

	<button data-id="peepsocustom-<?php echo $url.$file;?>" data-url="<?php echo $url.$file;?>" class="ps-vipicons-icon-picker-item">
		<p>
			<img src="<?php echo $url.$file;?>" id=<?php echo $id;?> />
		</p>
		<small><?php echo $display_file;?></small>
	</button>

	<?php
	}
	?>
	</div>
	<div class="clearfix"></div>
	<?php
}


?>
</div>

<style type="text/css">
	#ps-vipicons-admin-tutorial {
		width:100%;
		text-align:center;
		margin-bottom:20px;
	}
	#ps-vipicons-icon-picker-toggle {
		float:right;
		display:block;
		padding:10px;
		fint-size: 12px;
		background: #f0f0f0;
	}
	#ps-vipicons-admin-tutorial {
		width:100%;
		text-align:center;
		margin-bottom:20px;
	}
	.ps-vipicon-notification {
		min-width:300px !important;
	}

	#ps-vipicons-icon-picker {
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

	#ps-vipicons-icon-picker h4 {
		clear:both;
	}

	#ps-vipicons-icon-picker div.buttons {

	}

	#ps-vipicons-icon-picker div.clearfix {
		margin-bottom:20px;
	}

	#ps-vipicons-icon-picker button {
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

	#ps-vipicons-icon-picker button img {
		width: 20px;
	}

	#ps-vipicons-icon-picker small {
		color:#aaaaaa;
	}

	.ps-vipicon-hint {
		color: 		#aaaaaa;
		font-style: italic;
		font-size:	10px;
	}

	.ps-settings__label .warning {
		color: orange;
	}
</style>
