<?php defined('\ABSPATH') || exit; ?>
<style>
    table.wp-list-table .column-log_level {
        width: 10ch;
    }
    table.wp-list-table .column-log_time {
        width: 20ch;
    }
    table.wp-list-table mark.error, table.wp-list-table mark.warning, table.wp-list-table mark.info, table.wp-list-table mark.debug, table.wp-list-table mark.pending, table.wp-list-table mark.approved, table.wp-list-table mark.declined {
        font-weight: 700;
        background: transparent none;
        line-height: 1;
    }    
    table.wp-list-table mark.error {
        color: #dc3545;
    }
    table.wp-list-table mark.warning {
        color: #ffc107;
    }
    table.wp-list-table mark.info {
        color: #17a2b8;
    }
    table.wp-list-table mark.debug {
        color: #6c757d ;
    }

</style>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Error log', 'external-importer'); ?>
    </h1>

    <form id="exi-tracker-log-table" method="GET">
        <input type="hidden" name="page" value="<?php echo \esc_attr($_REQUEST['page']); ?>"/>
        <?php $table->views(); ?>
        <?php $table->display(); ?>
    </form>
</div>
