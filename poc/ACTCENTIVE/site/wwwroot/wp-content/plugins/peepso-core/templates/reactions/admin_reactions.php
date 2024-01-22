<?php

if(isset($_GET['dismiss-admin-tutorial'])) {
	add_user_meta(get_current_user_id(), 'peepso_reactions_hide_admin_tutorial', 1, TRUE);
}

if(isset($_GET['dismiss-admin-tutorial-reset'])) {
	delete_user_meta(get_current_user_id(), 'peepso_reactions_hide_admin_tutorial');
}

$assets_dir = plugin_dir_path(dirname(dirname(__FILE__))).'assets'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
$assets_url = plugin_dir_url(dirname(dirname(__FILE__))).'assets/images/';

$svg_dir = $assets_dir . 'svg'.DIRECTORY_SEPARATOR;
$svg_url = $assets_url . 'svg/';

$user_svg_dir = PeepSo::get_peepso_dir().'overrides'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'reactions';
$user_svg_url = PeepSo::get_peepso_uri().'overrides/images/reactions/';

add_thickbox();
wp_enqueue_media();
?>

<div id="peepso" class="ps-page--reactions wrap">

	<?php if(1 != get_user_meta(get_current_user_id(), 'peepso_reactions_hide_admin_tutorial', TRUE)) { ?>
		<div id="ps-reactions-admin-tutorial">
			<p>
				<h3><?php echo __('Welcome to  PeepSo Reactions&sup2;', 'peepsoreactions');?></h3>
				<h4><?php echo __('Here\'s what you should know:', 'peepsoreactions');?></h4>
				<a href="<?php echo $_SERVER['REQUEST_URI'];?>&dismiss-admin-tutorial"><small>(<?php echo __('click here to dismiss permanently', 'peepso-core');?>)</small></a>
			</p>
			<img src="<?php echo $assets_url;?>reactions/admin-tutorial.png" />
		</div>
	<?php } ?>


	<?php PeepSoTemplate::exec_template('reactions','admin_reactions_buttons'); ;?>

	<div class="ps-js-reactions-container ps-postbox--settings__wrapper">
		<?php

		foreach($data as $key => $reaction) {
			PeepSoTemplate::exec_template('reactions','admin_reaction', array('reaction'=>$reaction));
		}

		?>
	</div>
</div>
<?php
// Bundled images
$categories = array(
	'face'		=> __('Faces', 'peepso-core'),
	'heart'	   	=> __('Hearts', 'peepso-core'),
	'occa' 		=> __('Occasional', 'peepso-core'),
	'like'		=> __('Likes', 'peepso-core'),
	'rest'		=> __('Other', 'peepso-core'),
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

<div id="ps-reactions-icon-picker">
	<a href="#" id="ps-reactions-icon-picker-toggle" onclick="return false;"><i class="ps-icon-cancel"></i> <?php echo __('Cancel','peepso-core');?></a>
	<input type="hidden" id="icon-picker-current" />
	<h3>Media Library</h3>
	<p>
		<a class="btn btn-sm btn-info btn-img-reaction" href="#"><?php echo __('Add icon', 'peepso-core');?></a>
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

	    $file = str_replace('\\', '/', $file);

		$id = $file;
		$display_file = $file;
		if('custom' == $category || 'library' == $category) {
			$id = 'custom.'.$file;
			$display_file = basename($file);
		} else {
			$display_file = str_replace(array("{$category}_",".svg"),'',$display_file);
		}
	?>

	<button data-id="peepsocustom-<?php echo $url.$file;?>" data-url="<?php echo $url.$file;?>" class="ps-reactions-icon-picker-item">
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

	#ps-reactions-icon-picker-toggle {
		float:right;
		display:block;
		padding:10px;
		fint-size: 12px;
		background: #f0f0f0;
	}
	#ps-reactions-admin-tutorial {
		width:100%;
		text-align:center;
		margin-bottom:20px;
	}
	.ps-reaction-notification {
		min-width:300px !important;
	}

	#ps-reactions-icon-picker {
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

	#ps-reactions-icon-picker h4 {
		clear:both;
	}

	#ps-reactions-icon-picker div.buttons {

	}

	#ps-reactions-icon-picker div.clearfix {
		margin-bottom:20px;
	}

	#ps-reactions-icon-picker button {
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

	#ps-reactions-icon-picker button img {
		width: 20px;
	}

	#ps-reactions-icon-picker small {
		color:#aaaaaa;
	}

	.ps-reaction-hint {
		color: 		#aaaaaa;
		font-style: italic;
		font-size:	10px;
	}

	.ps-settings__label .warning {
		color: orange;
	}
</style>
