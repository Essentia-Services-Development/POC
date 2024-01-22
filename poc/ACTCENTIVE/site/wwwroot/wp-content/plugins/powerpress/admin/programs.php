<?php

$num_internal = 0;
$num_external = 0;
foreach ($props as $program) {
    if ($program['internal']) {
        $num_internal++;
    } else {
        $num_external++;
    }
}


?>
<h2 style="font-weight: bold; font-size: 150%; margin-bottom: 1ch;"><?php echo esc_html(get_option('powerpress_network_title') ); ?>
    <form method="POST" action="#/" id="clearSiteCache" class="top-right-corner">
        <a href="#TB_inline?&width=600&height=200&inlineId=unlinkNetwork" style="color: #D21919; font-size: 70%; text-decoration: none; margin-right: 1em;" class="thickbox" title="Powerpress Network plugin"><?php echo esc_html(__('UNLINK NETWORK', 'powerpress-network'));?></a>
        <button type="button" class="cacheButton" onclick="refreshAndCallDirectAPI('Select Choice', 'clearSiteCache', 'shows')"><?php echo esc_html(__('CLEAR SITE CACHE', 'powerpress-network'));?></button>
    </form>
</h2>
<h4 style="margin: 0; font-size: 120%;"><?php echo $num_internal; ?> internal | <?php echo $num_external; ?> external</h4><br>

<table>
    <thead>
    <tr>
        <th>Shows</th>
        <th>Link</th>
        <th>Show ID</th>
        <th>Manage</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $option = get_option('powerpress_network_map');
	$list_props = $secondary_props;
	if( empty($props) )
		$props = array(); // Empty array so it will not loop
	
    for ($i = 0; $i < count($props); ++$i){
        $key = 'p-'.$props[$i]['program_id'];
        if (isset($option[$key])){
            $link = get_permalink($option[$key]);
        } else{
            $link = null;
        }
        $props[$i]['link'] = $link;
		if( empty($props[$i]['program_title']) )
			$props[$i]['program_title'] = 'n/a';
		if( empty($props[$i]['program_id']) )
			$props[$i]['program_id'] = 0;

		$class = 'odd-row';
		if ($i % 2 == 0) {
		    $class = 'even-row';
        }
        ?>
        <tr class="<?php echo $class; ?>">
            <td class="<?php echo $class; ?>">
                <span style="font-weight:bold;"><?php echo esc_html($props[$i]['program_title']); ?></span>
            </td>
            <td class="<?php echo $class; ?>">
                <?php
                if ($props[$i]['link'] == null){
                    ?>
                    <span style="font-weight:bold;">Not Linked</span>
                    <?php
                } else {
                    ?>
                <span style="font-weight:bold;"><a style="color: green;" href="<?php echo esc_url($props[$i]['link']);?>"><?php echo esc_html(__('View Page', 'powerpress-network'));?></a></span>
                <?php
                }
                ?>
            </td>
            <td class="<?php echo $class; ?>">
                <span style="font-weight:bold;"><?php echo esc_html($props[$i]['program_id']); ?></span>
            </td>
            <td class="<?php echo $class; ?>">
                <?php if ($props[$i]['link'] != null){ ?>
                    <a onclick="editPageForProgram(<?php echo $props[$i]['program_id']; ?>, '<?php echo $option[$key]; ?>');" href="#TB_inline?&width=500&height=300&inlineId=selectPageBox" class="thickbox" title="Powerpress Network plugin"><i class="material-icons" title="<?php echo esc_html(__('Change Page', 'powerpress-network'));?>">edit</i></a>
                    <a href="#TB_inline?&width=600&height=200&inlineId=confirmUnlink<?php echo $props[$i]['program_id']; ?>" class="thickbox" title="Powerpress Network plugin"><i class="material-icons" title="<?php echo esc_html(__('Unlink Page', 'powerpress-network'));?>">remove_from_queue</i></a>
                <?php } else { ?>
                    <a onclick="editPageForProgram(<?php echo $props[$i]['program_id']; ?>, '');" href="#TB_inline?&width=500&height=300&inlineId=selectPageBox" class="thickbox" title="Powerpress Network plugin"><i class="material-icons" title="<?php echo esc_html(__('Link to Existing Page', 'powerpress-network'));?>">edit</i></a>
                    <a href="" onclick="createPage(<?php echo esc_html($props[$i]['program_id']); ?>, 'Program','createForm', '<?php echo esc_html($props[$i]['program_title']); ?>'); return false;"><i class="material-icons" title="<?php echo esc_html(__('Create New Page', 'powerpress-network'));?>">add_to_queue</i></a>
                <?php } ?>
                <a onclick="jQuery('#add-program-to-group').val(<?php echo $props[$i]['program_id']; ?>);" href="#TB_inline?&width=500&height=200&inlineId=addToGroup" class="thickbox" title="Powerpress Network plugin"><i class="material-icons" title="<?php echo esc_html(__('Add to Group', 'powerpress-network'));?>">list</i></a>
                <a href="#TB_inline?&width=500&height=200&inlineId=confirmRemoval<?php echo $props[$i]['program_id']; ?>" class="thickbox" title="Powerpress Network plugin"><i class="material-icons" title="<?php echo esc_html(__('Delete Program', 'powerpress-network'));?>">delete</i></a>
            </td>
        </tr>
        <?php
    }

    ?>
    </tbody>
</table><br>

<div id="addToGroup" style="display: none">
    <form method="POST" id="addForm">
    <input id="add-program-to-group" name="program" type="hidden" />
        <input id="group-for-program-add" name="list_id" type="hidden" />
        <input id="requestAction" name="requestAction" value="add" hidden>
        <table class="invisible-table">
        <?php foreach ($list_props as $list) { ?>
            <tr>
                <td><a href="" class="ppn-done-link" style="float: left;" onclick="jQuery('#group-for-program-add').val(<?php echo $list['list_id']; ?>);directStatus('Select Choice', 'addForm', true, 'shows');return false;"><?php echo $list['list_title']; ?></a></td>
            </tr>
        <?php } ?>
        </table>
    </form>
</div>
<div class="selectPageBox" id="selectPageBox" style="display: none">
    <h2 class="thickboxTitle"><?php echo esc_html(__('List Page Box', 'powerpress-network'));?></h2>
    <form method="POST" id="changeForm">
        <select class="dropdownChoice" id="page-select-ppn" name="pageID">
            <?php
            $availablePages = get_pages();
            for ($j = 0; $j < count($availablePages); ++$j) {
                ?>
                <option name="<?php echo esc_html($availablePages[$j]->post_title); ?>" value="<?php echo esc_html($availablePages[$j]->ID); ?>"><?php echo esc_html($availablePages[$j]->post_title); ?></option>
                <?php
            }
            ?>
        </select>
        <br>
        <p style="color: black; font-weight: bold"><?php echo esc_html(__('Remember to put this short code on your new page', 'powerpress-network'));?></p>
        <input readonly id="ppn-program-shortcode" value=''>
        <input name="target" value="Program" hidden>
        <input id="select-page-target-id" name="targetId" value="" hidden>
        <input name="redirectUrl" value="false" hidden>
    </form>

    <button type="submit" class="ppn-back-button" onclick="directStatus('Select Choice', 'changeForm', true, 'shows')"><?php echo esc_html(__('Save', 'powerpress-network'));?></button>
    <p class="ppn-back-button" onclick="tb_remove()"><?php echo esc_html(__('Cancel', 'powerpress-network'));?></p>
</div>
<form method="POST" id="createForm" hidden></form>
<?php


for ($i = 0; $i < count($props); ++$i){ ?>



<form method="POST" id="createForm<?php echo $props[$i]['program_id']; ?>">
    <input name="target" value="Program" hidden>
    <input name="targetId" value="<?php echo esc_html($props[$i]['program_id']); ?>" hidden>
    <input name="redirectUrl" value="false" hidden>
</form>

<?php if ($props[$i]['link'] != null){ ?>
<div class="confirmUnlink" id="confirmUnlink<?php echo $props[$i]['program_id']; ?>" style="display: none">
    <h2 class="thickboxTitle"><?php echo esc_html(__('Confirm Unlink', 'powerpress-network'));?></h2>
    <p style="color: black; font-weight: bold"><?php echo esc_html(__('Are you sure you want to unlink the current page off the program ' . esc_html($props[$i]['program_title']) . '?', 'powerpress-network'));?></p><br>
    <button type="submit" class="warningButton" onclick="confirmUnlink('createForm<?php echo $props[$i]['program_id']; ?>');directStatus('Select Choice', 'createForm<?php echo $props[$i]['program_id']; ?>', false, 'shows')"><?php echo esc_html(__('Unlink page', 'powerpress-network'));?></button>
    <p class="ppn-back-button" onclick="tb_remove();"><?php echo esc_html(__('Cancel', 'powerpress-network'));?></p>
</div>
<?php } ?>

<form method="POST" id="removeForm<?php echo $props[$i]['program_id']; ?>" action="">
    <input name="target" value="program" hidden>
    <input name="requestAction" class="requestAction" value="" hidden>
    <input name="targetId" class="removeProgram" id="removeProgram"
        <?php
        echo 'value = ' . esc_html($props[$i]['program_id']);
        ?>
           hidden>
    <input name="redirectUrl" value="false" hidden>
</form>
<div class="confirmRemoval" id="confirmRemoval<?php echo $props[$i]['program_id']; ?>" style="display: none;">
    <h2 class="thickboxTitle"><?php echo esc_html(__('Confirm removal of program from your Network', 'powerpress-network')); ?></h2>
    <p><?php echo esc_html(__('Are you sure you want to remove this program off of your network?')); ?></p>
    <button type="submit" class="warningButton" onclick="confirmRemovalOfProgram(<?php echo esc_html($props[$i]['program_id']); ?>);"><?php echo esc_html(__('Remove program', 'powerpress-network')); ?>
</div>
<?php
}

?>

<form id="manageProgram" action="#/" method="POST" hidden> <!-- Make sure to keep back slash there for WordPress -->
    <input id="programId" name="programId" value="">
    <input id="linkPageProgram" name="linkPageProgram" value="">
    <input name="previousStatus" value="List Programs">
</form>
