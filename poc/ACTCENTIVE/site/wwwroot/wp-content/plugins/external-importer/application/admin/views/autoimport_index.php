<?php defined('\ABSPATH') || exit; ?>
<div id="ei_waiting_products" style="display:none; text-align: center;"> 
    <h2><?php _e('Working... Please wait...', 'external-importer'); ?></h2> 
    <p>
        <img src="<?php echo \ExternalImporter\PLUGIN_RES; ?>/img/waiting.gif" />
    </p>
</div>
<script type="text/javascript">
    var $j = jQuery.noConflict();
    $j(document).ready(function () {
        $j('.ei-run-autoimport').click(function () {
            $j.blockUI({message: $j('#ei_waiting_products')});
        });
    });
</script>
<?php
if ($table->current_action() == 'delete' && !empty($_GET['id']))
{
    if (is_array($_GET['id']))
        $t = count($_GET['id']);
    else
        $t = 1;

    $message = sprintf(__('Deleted tasks: %d', 'external-importer'), $t);
} elseif ($table->current_action() == 'run')
    $message = __('Done!', 'external-importer');
else
    $message = '';
?>

<div class="wrap">

    <h2>
        <?php _e('Auto import', 'external-importer'); ?>
        <a class="add-new-h2" href="<?php echo \get_admin_url(\get_current_blog_id(), 'admin.php?page=external-importer-autoimport-edit'); ?>"><?php _e('Create', 'external-importer'); ?></a>
    </h2>
    <?php if ($message): ?>
        <?php echo '<div class="updated below-h2" id="message"><p>'; ?>
        <?php echo $message; ?>
        <?php echo '</p></div>'; ?>
    <?php endif; ?>

    <form id="ei-table" method="GET">
        <input type="hidden" name="page" value="<?php echo \esc_attr($_REQUEST['page']); ?>"/>
        <?php $table->display() ?>
    </form>
</div>
