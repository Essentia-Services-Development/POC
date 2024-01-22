<?php defined('\ABSPATH') || exit; ?>
<div id="affegg_waiting_products" style="display:none; text-align: center;"> 
    <h2><?php _e('Product update', 'affegg'); ?></h2> 
    <p>
        <img src="<?php echo Keywordrush\AffiliateEgg\PLUGIN_RES ?>/img/admin/egg_waiting.gif" />
        <br>
        <?php _e('Working... Please wait...', 'affegg'); ?>

    </p>
</div>
<div id="affegg_waiting_catalogs" style="display:none; text-align: center;"> 
    <h2><?php _e('Catalog update', 'affegg'); ?></h2> 
    <p>
        <img src="<?php echo Keywordrush\AffiliateEgg\PLUGIN_RES ?>/img/admin/egg_waiting.gif" />
        <br>
        <?php _e('Working... Please wait...', 'affegg'); ?>
    </p>
</div>
<script type="text/javascript">
    var $j = jQuery.noConflict();
    $j(document).ready(function () {
        $j('.force_update_products').click(function () {
            $j.blockUI({message: $j('#affegg_waiting_products')});
            test();
        });
        $j('.force_update_catalogs').click(function () {
            $j.blockUI({message: $j('#affegg_waiting_catalogs')});
            test();
        });
    });
</script>
<?php
$table->prepare_items();

$message = '';
if ($table->current_action() == 'delete' && !empty($_GET['id']))
    $message = '<div class="updated below-h2" id="message"><p>' . __('Storefronts has been deleted', 'affegg') . '</p></div>';

if (isset($_GET['settings-updated']))
    $message = '<div class="updated below-h2" id="message"><p>' . __('Plugin has been activated', 'affegg') . '</p></div>';

if ($table->current_action() == 'update_products')
    $message = '<div class="updated below-h2" id="message"><p>' . __('Product information was updated', 'affegg') . '</p></div>';

if ($table->current_action() == 'update_catalogs')
    $message = '<div class="updated below-h2" id="message"><p>' . __('Products were updated', 'affegg') . '</p></div>';
?>
<div class="wrap">

    <h2>
        <?php _e('Storefronts', 'affegg'); ?>
        <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=affiliate-egg-edit'); ?>"><?php _e('Add new', 'affegg'); ?></a>
    </h2>
    <?php echo $message; ?>

    <div id="poststuff">    
        <p>
            <?php _e('This is a list of your storefronts.', 'affegg'); ?>
            <?php _e('Сopy shortcode <em>[affegg id=ID]</em> and insert it to post.', 'affegg'); ?>
            <?php _e('Each storefront has unique ID, which must be used in shortcode.', 'affegg'); ?>
        </p>
    </div>    

    <form method="get" action="">
        <?php
        if (isset($_GET['page']))
        {
            echo '<input type="hidden" name="page" value="' . esc_attr($_GET['page']) . '" />' . "\n";
        }
        $table->search_box(__('Search of storefronts', 'affegg'), 'affegg_search');
        ?>
    </form>	

    <form id="eggs-table" method="GET">
        <input type="hidden" name="page" value="<?php echo \esc_attr($_REQUEST['page']); ?>"/>
        <?php $table->display() ?>
    </form>
</div>