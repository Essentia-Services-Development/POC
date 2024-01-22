<?php defined('\ABSPATH') || exit; ?>
<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="name"><?php _e('Name', 'external-importer'); ?></label>
            </th>
            <td>
                <input id="name" name="item[name]" type="text" value="<?php echo \esc_attr($item['name']) ?>"
                       size="50" class="code" placeholder="<?php _e('Name (optional)', 'external-importer'); ?>">
            </td>
        </tr>

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="status"><?php _e('Status', 'external-importer'); ?></label>
            </th>
            <td>
                <select id="status" name="item[status]">
                    <option value="1"<?php if ($item['status']) echo ' selected="selected"'; ?>><?php _e('Enabled', 'external-importer'); ?></option>
                    <option value="0"<?php if (!$item['status']) echo ' selected="selected"'; ?>><?php _e('Disabled', 'external-importer'); ?></option>
                </select>
            </td>
        </tr>        
        <tr class=
            "form-field">
            <th valign="top" scope="row">
                <label for="listing_url"><?php _e('Listing URL', 'external-importer'); ?> <span style="color:red;">*</span></label>
            </th>
            <td>
                <input<?php if ($item['id']) echo ' readonly'; ?> id="listing_url" name="item[listing_url]" type="text" value="<?php echo \esc_attr($item['listing_url']) ?>"
                                                                  class="code" placeholder="<?php _e('Listing URL', 'external-importer'); ?> <?php _e('(required)', 'external-importer'); ?>">
            </td>
        </tr>

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="recurrency"><?php _e('Recurrency', 'external-importer'); ?></label>
            </th>
            <td>
                <select id="recurrency" name="item[recurrency]">
                    <option value="3600"<?php if ($item['recurrency'] == 3600) echo ' selected="selected"'; ?>><?php _e('Every hour', 'external-importer'); ?></option>
                    <option value="21600"<?php if ($item['recurrency'] == 21600) echo ' selected="selected"'; ?>><?php echo sprintf(__('Every %d hours', 'external-importer'), 6); ?></option>
                    <option value="43200"<?php if ($item['recurrency'] == 43200) echo ' selected="selected"'; ?>><?php echo sprintf(__('Every %d hours', 'external-importer'), 12); ?></option>
                    <option value="86400"<?php if ($item['recurrency'] == 86400) echo ' selected="selected"'; ?>><?php _e('Once a day', 'external-importer'); ?></option>
                    <option value="259200"<?php if ($item['recurrency'] == 259200) echo ' selected="selected"'; ?>><?php echo sprintf(__('Every %d days', 'external-importer'), 3); ?></option>
                    <option value="604800"<?php if ($item['recurrency'] == 604800) echo ' selected="selected"'; ?>><?php echo sprintf(__('Every 7 days', 'external-importer'), 7); ?></option>
                    <option value="1296000"<?php if ($item['recurrency'] == 1296000) echo ' selected="selected"'; ?>><?php echo sprintf(__('Every %d days', 'external-importer'), 15); ?></option>
                </select>  
                <p class="description"><?php _e('How often to check the URL for new products.', 'external-importer'); ?></p>                
            </td>
        </tr> 


        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="process_products"><?php _e('Process products', 'external-importer'); ?></label>
            </th>
            <td>
                <input id="process_products" name="item[process_products]" value="<?php echo \esc_attr($item['process_products']) ?>"
                       type="number" class="small-text">
                <p class="description"><?php _e('How many products to process at a time.', 'external-importer'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="category"><?php _e('Default category', 'external-importer'); ?></label>
            </th>
            <td>
                <select id="recurrency" name="item[extra][category]">

                    <?php
                    foreach (ExternalImporter\application\helpers\WooHelper::getCategoryList() as $c_id => $categ)
                    {
                        echo '<option value="' . \esc_attr($c_id) . '"';
                        if ($item['extra']['category'] == $c_id)
                            echo ' selected="selected"';
                        echo '>' . \esc_html($categ) . '</option>';
                    }
                    ?>
                </select>  


            </td>
        </tr>


    </tbody>
</table>
