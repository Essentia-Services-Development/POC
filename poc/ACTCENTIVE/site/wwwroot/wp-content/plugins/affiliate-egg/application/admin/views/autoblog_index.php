<?php defined('\ABSPATH') || exit; ?>
<div id="affegg_waiting_products" style="display:none; text-align: center;"> 
    <h2><?php _e('Work of autoblog', 'affegg'); ?></h2> 
    <p>
        <img src="<?php echo Keywordrush\AffiliateEgg\PLUGIN_RES ?>/img/admin/egg_waiting.gif" />
        <br>
        <?php _e('Please, wait for ending of autoblog work.', 'affegg'); ?>

    </p>
</div>
<script type="text/javascript">
    var $j = jQuery.noConflict();
    $j(document).ready(function () {
        $j('.run_avtoblogging').click(function () {
            $j.blockUI({message: $j('#affegg_waiting_products')});
            test();
        });
    });
</script>
<?php
$table->prepare_items();

$message = '';
if ($table->current_action() == 'delete' && !empty($_GET['id']))
{
    if (is_array($_GET['id']))
        $count = count($_GET['id']);
    else
        $count = 1;

    $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Deleted tasks of autoblog:', 'affegg') . ' %d', $count) . '</p></div>';
}
if ($table->current_action() == 'run')
    $message = '<div class="updated below-h2" id="message"><p>' . __('Autoblog finished work', 'affegg') . '</p></div>';
?>
<div class="wrap">

    <h2>
        <?php _e('Auto Bloging', 'affegg'); ?>
        <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=affiliate-egg-autoblog-edit'); ?>"><?php _e('Add Autoblog', 'affegg'); ?></a>
    </h2>
    <?php echo $message; ?>

    <div id="poststuff">    
        <p>
            <?php _e('With autobloging you can adjust automatic creation of posts. For a task of autobloging use the URL of shop catalog with the updated list of goods, for example, the section "new products ".', 'affegg'); ?>
        </p>        
    </div>    

    <form id="eggs-table" method="GET">
        <input type="hidden" name="page" value="<?php echo \esc_attr($_REQUEST['page']); ?>"/>
        <?php $table->display() ?>
    </form>
</div>