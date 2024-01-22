<?php
$tab = 'shows';
if (!empty($_GET['tab'])) {
    $tab = $_GET['tab'];
}
?>
<h1 style="font-weight: bold; font-size: 170%; margin-bottom: 1ch;"><?php echo esc_html(__('Podcast Network', 'powerpress-network'));?></h1>
<h4 style="margin: 0; font-size: 120%;"><?php echo esc_html(__('Build and manage your podcast network.','powerpress-network'));?></h4><br>

<div class="tabs-container">
    <div class="tab">
        <button class="<?php echo $tab == 'shows' ? 'tabActive' : 'tabInactive' ?>" id="programsTab"    onclick="showPPNTab('programs')">Shows</button>
        <button class="<?php echo $tab == 'groups' ? 'tabActive' : 'tabInactive' ?>" id="groupsTab"  onclick="showPPNTab('groups')">Groups</button>
        <button class="<?php echo $tab == 'requests' ? 'tabActive' : 'tabInactive' ?>" id="requestsTab"  onclick="showPPNTab('requests')">Requests</button>
    </div>

    <div class="tabContent" style="<?php echo $tab == 'shows' ? 'display:block' : 'display:none' ?>" id="programs">
        <?php echo $shows_html; //include(dirname(__FILE__) . '/programs.php'); ?>
    </div>
    <div class="tabContent" style="<?php echo $tab == 'groups' ? 'display:block' : 'display:none' ?>" id="groups">
        <?php echo $groups_html; //include(dirname(__FILE__) . '/lists.php'); ?>
    </div>
    <div class="tabContent" style="<?php echo $tab == 'requests' ? 'display:block' : 'display:none' ?>" id="requests">
        <?php echo $requests_html; //include(dirname(__FILE__) . '/applications.php'); ?>
    </div>
</div>
<div class="unlinkNetwork" id="unlinkNetwork" style="display: none;">
    <form method='POST' id="choiceForm" action="<?php echo admin_url("admin.php?page=". urlencode(powerpress_admin_get_page()) .""); ?>"> <!-- Make sure to keep back slash there for WordPress -->
		<input type="hidden" name="ppn-action" value="unset-network-id" />
        <h2 class="thickboxTitle"><?php echo esc_html(__('Are you sure you want to unlink ', 'powerpress-network')) . esc_html(get_option('powerpress_network_title') ) . '?'; ?></h2>
        <button class="warningButton" type="submit"><?php echo esc_html(__('Unlink Network', 'powerpress-network'));?></button>
    </form>
</div>
