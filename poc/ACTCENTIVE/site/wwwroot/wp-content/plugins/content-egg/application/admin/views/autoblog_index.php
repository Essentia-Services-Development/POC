<?php defined('\ABSPATH') || exit; ?>
<div id="cegg_waiting_products" style="display:none; text-align: center;"> 
    <h2><?php esc_html_e('Working... Please wait...', 'content-egg'); ?></h2>
    <p>
        <img src="<?php echo esc_url_raw(\ContentEgg\PLUGIN_RES); ?>/img/egg_waiting.gif" />
    </p>
</div>
<script type="text/javascript">
    var $j = jQuery.noConflict();
    $j(document).ready(function () {
        $j('.run_avtoblogging').click(function () {
            $j.blockUI({message: $j('#cegg_waiting_products')});
        });
    });
</script>
<?php
$message = '';
if ($table->current_action() == 'delete' && !empty($_GET['id']))
{
    if (is_array($_GET['id']))
        $count = count($_GET['id']);
    else
        $count = 1;
    $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Deleted tasks for autoblogging: ', 'content-egg') . ' %d', $count) . '</p></div>';
}
if ($table->current_action() == 'run')
    $message = '<div class="updated below-h2" id="message"><p>' . __('Autoblogging finished tasks', 'content-egg') . '</p></div>';
?>

<?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    <div class="cegg-maincol">
    <?php endif; ?>


    <div class="wrap">

        <h2>
            <?php esc_html_e('Autoblogging', 'content-egg'); ?>
            <a class="add-new-h2" href="<?php echo esc_url_raw(get_admin_url(get_current_blog_id(), 'admin.php?page=content-egg-autoblog-edit')); ?>"><?php esc_html_e('Add autoblogging', 'content-egg'); ?></a>
        </h2>
        <?php echo \wp_kses_post($message); ?>

        <div id="poststuff">    
            <p>
            </p>        
        </div>    

        <form id="eggs-table" method="GET">
            <input type="hidden" name="page" value="content-egg-autoblog"/>
            <?php $table->display() ?>
        </form>
    </div>

    <?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    </div>    
    <?php include('_promo_box.php'); ?>
<?php endif; ?>        