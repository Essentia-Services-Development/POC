<h2 style="font-weight: bold; font-size: 150%; margin-bottom: 1ch;"><?php echo esc_html(get_option('powerpress_network_title') ) . ' Groups'; ?>
    <button onclick='manageList(-1);directStatus("Create List", "manageForm");' class="cacheButton top-right-corner"><?php echo esc_html(__('CREATE NEW GROUP', 'powerpress-network'));?></button>

</h2>
<h4 style="margin: 0; font-size: 120%;"><?php echo esc_html(__('Manage the lists in your network by controlling which programs are in them.', 'powerpress-network'));?></h4><br>
<table>
    <thead>
    <tr>
        <th style="text-align: left;"><?php echo esc_html(__('Current Groups', 'powerpress-network'));?></th>

        <th style="text-align: left;"><?php echo esc_html(__('Link', 'powerpress-network'));?></th>

        <th style="text-align: left;"><?php echo esc_html(__('Group ID', 'powerpress-network'));?></th>

        <th style="text-align: left;"><?php echo esc_html(__('Manage', 'powerpress-network'));?></th>
    </tr>
    </thead>
    <?php

    $map = get_option ('powerpress_network_map');
	if( empty($props) )
		$props = array(); // Empty array so it will not loop
		
    for ($i = 0; $i < count($props); ++$i) {
        $key = 'l-'.$props[$i]['list_id'];
        if (isset($map[$key])){
            $link = get_permalink($map[$key]);
        } else{
            $link = null;
        }
        $props[$i]['link'] = $link;

        $class = 'odd-row';
        if ($i % 2 == 0) {
            $class = 'even-row';
        }
        ?>
        <tr>
            <td class="<?php echo $class; ?>">
                <span style="font-weight:bold;"><?php echo esc_html($props[$i]['list_title']); ?>            <br>
            </td>
            <td class="<?php echo $class; ?>">
                    <?php
                    if ($props[$i]['link'] == null){
                        ?>
                        <span style="font-weight:bold;">Not Linked</span>
                        <?php
                    } else {
                        ?>
                        <span style="font-weight:bold;"><a style="color: green;" target="_blank" href="<?php echo esc_url($props[$i]['link']);?>"><?php echo esc_html(__('View Page', 'powerpress-network'));?></a></span>
                        <?php
                    }
                    ?>
            </td>
            <td class="<?php echo $class; ?>">
                <span style="font-weight:bold;" class="list-id"><?php echo esc_html($props[$i]['list_id']); ?></span>
            </td>
            <td class="<?php echo $class; ?>"><i onclick="confirmDelete(<?php echo esc_js($props[$i]['list_id']);?>)" class="material-icons" title="Delete List">delete</i></span>
                <span style="font-weight:bold;"><i class="material-icons" title="Edit List" onclick="manageList(<?php echo esc_js($props[$i]['list_id']);?>, '<?php echo esc_js($props[$i]['link'])?>');directStatus('Manage List', 'manageList');">edit</i></span>
            </td>
        </tr>
        <?php
    }
    ?>
</table><br>
<form id="manageForm" action="#/" method="POST" hidden> <!-- Make sure to keep back slash there for WordPress -->
</form>

<form id="manageList" action="#" method="POST" hidden> <!-- Make sure to keep back slash there for WordPress -->
    <input class="requestAction" name="requestAction">
    <input id="listId" name="listId" value="">
    <input id="linkPageList" name="linkPageList" value="">
</form>

<script>
    function manageList(listId, linkPage = false)
    {
        jQuery(function($){ $('#listId').attr('value', listId) });
        jQuery(function($){ $('#linkPageList').attr('value', linkPage) });
    }
    function confirmDelete(listId)
    {
        if (confirm('<?php echo esc_js(__('Are you sure you want to delete this list?', 'powerpress-network'));?>')) { //Confirm the delete network
            jQuery(function($){ $('#manageList .requestAction').attr('value', 'delete') });
            jQuery(function($){ $('#listId').attr('value', listId) });
            directStatus('Select Choice', 'manageList', true, 'groups');
        }
    }
</script>
