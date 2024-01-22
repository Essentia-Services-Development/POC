<?php defined('\ABSPATH') || exit; ?>
<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>

        <?php if ($item['id']): ?>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php _e('Shortcode', 'affegg'); ?></label>
                </th>
                <td>
                    <input onclick="this.select();" type="text" id="table-information-shortcode" class="table-shortcode" value="[affegg id=<?php echo esc_attr($item['id']) ?>]" readonly="readonly" /></div>
                </td>
            </tr>

        <?php endif; ?>

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="name"><?php _e('Name', 'affegg'); ?></label>
            </th>
            <td>
                <input id="name" name="item[name]" type="text" value="<?php echo esc_attr($item['name']) ?>"
                       size="50" class="code" placeholder="<?php _e('Name of shopfront (not required)', 'affegg'); ?>">
            </td>
        </tr>
        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="urls"><?php _e('Urls', 'affegg'); ?></label>
            </th>
            <td>
                <textarea placeholder="<?php _e('You can set url of single product page or page with list of products. Each url from new line', 'affegg'); ?>" style="overflow: auto; word-wrap: inherit;" rows="12" id="urls" name="item[urls]"><?php echo esc_textarea($item['urls']) ?></textarea>
            </td>
        </tr>
        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="name"><?php _e('Template', 'affegg'); ?></label>
            </th>
            <td>
                <select name="item[template]">
                    <?php foreach ($templates as $tpl_id => $tpl_name): ?>
                        <option value="<?php echo esc_attr($tpl_id); ?>" <?php selected($item['template'], $tpl_id); ?>><?php _e(esc_attr($tpl_name), 'affegg'); ?></option>
                    <?php endforeach; ?>
                </select>                
            </td>
        </tr>

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="name"><?php _e('Limit of products', 'affegg'); ?></label>
            </th>
            <td>

                <input id="limit" name="item[prod_limit]" value="<?php echo esc_attr($item['prod_limit']) ?>"
                       type="number" class="small-text">
            </td>
        </tr>

    </tbody>
</table>
