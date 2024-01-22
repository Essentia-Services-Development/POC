<?php
$availablePages = get_pages();
$new_group = false;
// new group
if (empty($props['list_info'])) {
    $props['programs'] = array();
    $networkInfo['link_page_list'] = '';
    $new_group = true;
}
?>


<h1 style="font-weight: bold; font-size: 170%; margin-bottom: 1ch;"><?php echo esc_html(__('Podcast Network', 'powerpress-network'));?></h1>
<h4 style="margin: 0; font-size: 120%;"><?php echo esc_html(__('Build and manage your podcast network.','powerpress-network'));?></h4><br>

<div class="tabs-container">
<form method="POST" action="#/" id="manageForm"> <!-- Make sure to keep back slash there for WordPress -->

</form>
<!--List settings section-->
<div class="settingBox">
    <form method="POST" action="#/" id="<?php echo $new_group ? 'createForm' : 'editForm'; ?>"> <!-- Make sure to keep back slash there for WordPress -->

        <h2 style="font-weight: bold; font-size: 150%; margin-bottom: 0; margin-top: 1ch;">Edit Group</h2>
        <h4 style="margin-top: 1ch"><?php echo esc_html(__('Group shows together and showcase them on one page.', 'powerpress-network'));?></h4>
        <h4 style="font-weight: bold;margin: 1ch 0;"><?php echo esc_html(__('Group Name', 'powerpress-network'));?></h4>
        <input id="editListTitle" name="<?php echo $new_group ? 'newListTitle' : 'editListTitle'; ?>" type="text" value="<?php echo $new_group ? '' : esc_html($props['list_info']['list_title']); ?>"><br /><br />
        <h4 style="font-weight: bold;margin: 1ch 0;"><?php echo esc_html(__('Group Description', 'powerpress-network'));?></h4>
        <textarea id ="editListDescription" class="description" name="<?php echo $new_group ? 'newListDescription' : 'editListDescription'; ?>" rows ="3" type="text"><?php echo $new_group ? '' : esc_html($props['list_info']['list_description']);?></textarea><br /><br />

        <div id ="programBox" style="display: none">
            <h4 style="font-size: 115%; margin: 1ch 0;"><?php echo __('Add Shows', 'powerpress'); ?></h4>
                <table class="invisible-table">
                    <?php
                    for ($i = 0; $i < count($props['programs']); ++$i) {
                        ?>
                        <tr>
                            <td><input name="program[<?php echo $i; ?>]" class="program" type="checkbox"
                                       value="<?php echo esc_html($props['programs'][$i]['program_id']); ?>"
                                    <?php if ($props['programs'][$i]['checked']) echo ' checked'; ?>
                                onchange="updateListOfShows(this.checked, '<?php echo esc_html($props['programs'][$i]['program_title']); ?>');">
                            </td>
                            <td><?php echo esc_html($props['programs'][$i]['program_title']); ?></td>
                        </tr>

                        <?php
                    }
                    ?>
                </table>
                <a class="ppn-done-link" href="" onclick="tb_remove();return false;"><?php echo esc_html(__('Done', 'powerpress-network'));?></a>
        </div>
        <?php if (!$new_group) { ?>
        <input id="requestAction" name="requestAction" value="save" hidden>
        <?php } ?>
    </form>


<!--List Page Section-->
    <?php if (!$new_group) { ?>
        <h4 style="font-weight: bold;margin: 1ch 0;"><?php echo __('Group Page Link', 'powerpress-network'); ?></h4>
        <?php
        if (!empty($networkInfo['link_page_list'])) {
            ?>
            <input style="width: 100%;" type="text"
                   value="<?php echo $new_group ? '' : esc_html($networkInfo['link_page_list']); ?>" readonly><br><br>
            <a href="#TB_inline?&width=500&height=300&inlineId=selectPageBox" class="thickbox"
               title="Powerpress Network plugin"><?php echo esc_html(__('CHANGE PAGE', 'powerpress-network')); ?></a> &nbsp; &nbsp;
            <a style="color: #D21919;" href="#TB_inline?&width=600&height=200&inlineId=confirmUnlink" class="thickbox"
               title="Powerpress Network plugin"><?php echo esc_html(__('UNLINK PAGE', 'powerpress-network')); ?></a>
            <?php

        } else {
            ?>
            <input style="width: 100%;" type="text" value="(not set)" readonly><br><br>
            <a href="#TB_inline?&width=500&height=300&inlineId=selectPageBox" class="thickbox"
               title="Powerpress Network plugin"><?php echo esc_html(__('SELECT PAGE', 'powerpress-network')); ?></a> &nbsp; &nbsp;
            <a href=""
               onclick="createPage('<?php echo esc_html($networkInfo['list_id']); ?>', 'List', 'createForm', '<?php echo esc_html($props['list_info']['list_title']); ?>'); return false;"><?php echo esc_html(__('CREATE PAGE', 'powerpress-network')); ?></a>
            <form method="POST" id="createForm">
                <input name="target" value="List" hidden>
                <input name="targetId" value="<?php echo esc_html($networkInfo['list_id']) ?>" hidden>
                <input name="redirectUrl" value="true" hidden>
            </form>
            <?php
        }
    }
    ?>
</div>
<div id="selectPageBox" style="display: none">
    <form method="POST" id="pageForm">
        <p style="color: black; font-weight: bold"><?php echo esc_html(__('Select an existing page to link to current program', 'powerpress-network'));?></p>
        <br>
        <select class="dropdownChoice" name="pageID">
            <?php
            for ($i = 0; $i < count($availablePages); ++$i) {
                ?>
                <option
                        value="<?php echo esc_html($availablePages[$i]->ID); ?>"><?php echo esc_html($availablePages[$i]->post_title); ?></option>
                <?php
            }
            ?>

        </select>
        <br>
        <p style="color: black; font-weight: bold"><?php echo esc_html(__('Remember to put this short code on your new page', 'powerpress-network'));?></p>
        <br>
        <input readonly value='<?php echo esc_html($props['list_info']['shortcode']);?>'>
        <input name="target" value="List" hidden>
        <input name="targetId" value="<?php echo esc_html($networkInfo['list_id']); ?>" hidden>
        <input name="redirectUrl" value="false" hidden>
    </form>
    <button type="submit" class="ppn-back-button" onclick="directStatus('Manage List', 'pageForm', true)"><?php echo esc_html(__('Save', 'powerpress-network'));?></button>
    <p class="ppn-back-button" onclick="tb_remove();"><?php echo esc_html(__('Cancel', 'powerpress-network'));?></p>
</div>

<div class="confirmUnlink" id="confirmUnlink" style="display: none">
    <h2 class="thickboxTitle"><?php echo esc_html(__('Confirm Unlink', 'powerpress-network'));?></h2>
    <form method="POST" id="unlinkForm">
        <input name="target" value="List" hidden>
        <input name="targetId" value="<?php echo esc_html($props['list_info']['list_id']); ?>" hidden>
        <input name="redirectUrl" value="false" hidden>
    </form>

    <p style="color: black; font-weight: bold"><?php echo esc_html(__('Are you sure you want to unlink the current page off the program?', 'powerpress-network'));?></p><br>
    <button type="submit" class="warningButton" onclick="confirmUnlink('unlinkForm');directStatus('Manage List', 'unlinkForm', 'groups')"><?php echo esc_html(__('Unlink page', 'powerpress-network'));?></button>
    <p class="ppn-back-button" onclick="tb_remove();"><?php echo esc_html(__('Cancel', 'powerpress-network'));?></p>
</div>
<!--Program Section-->
    <div class="settingBox" style="float: right;">
        <div class="top-right-corner right-margin-responsive">
        <a href="<?php echo admin_url("admin.php?page=". urlencode(powerpress_admin_get_page()) ."&status=Select+Choice&tab=groups"); ?>">
            <?php echo esc_html(__('BACK', 'powerpress-network'));?></a>
        <button type="submit" class="cacheButton" style="font-size: 90%; margin-left: 2em;" onclick="directStatus('Manage List', '<?php echo $new_group ? 'createForm' : 'editForm'; ?>', true)"><?php echo esc_html(__('SAVE GROUP', 'powerpress-network'));?></button>
        </div>
<?php if (!$new_group) { ?>
<div class="settingBox" style="margin-top: 1em;">
    <h4 style="font-weight: bold; margin-bottom: 0; margin-top: 1ch;"><?php echo esc_html(__('Shows in Group', 'powerpress-network'));?></h4>
    <ul id="shows-in-group" style="font-weight: bold; margin-bottom: 2em;">
    <?php
    $option = get_option('powerpress_network_map');
    for ($i = 0; $i < count($props['programs']); ++$i) {
        $key = 'p-'.$props['programs'][$i]['program_id'];
        if (isset($option[$key])){
            $link = get_permalink($option[$key]);
        } else{
            $link = null;
        }
        $props['programs'][$i]['link'] = $link;
        if ($props['programs'][$i]['checked'] == true) {
            ?>
            <li><span><?php echo esc_html($props['programs'][$i]['program_title']);?></span></li>
            <?php
        }
    }
    ?>
    </ul>
    <a href="#TB_inline?&width=500&height=300&inlineId=programBox&modal=true" class="thickbox" title="Powerpress Network plugin">ADD SHOWS</a>

    <form id="specificProgramForm" method ="POST" hidden>
        <input id="programId" name="programId" value="">
        <input id="linkPageProgram" name ="linkPageProgram" value="">
        <input name="previousStatus" value = "Manage List">
    </form>
</div>


<?php } ?>
    </div>

    <div class="clear"></div>
<!--End of all section-->
<form method="POST" action="#/" id="manageForm"> <!-- Make sure to keep back slash there for WordPress -->
</form>
</div>
