<?php defined('\ABSPATH') || exit; ?>
<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="name"><?php _e('Name', 'affegg'); ?></label>
            </th>
            <td>
                <input id="name" name="item[name]" type="text" value="<?php echo esc_attr($item['name']) ?>"
                       size="50" class="code" placeholder="<?php _e('Name', 'affegg'); ?>">
            </td>
        </tr>
        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="url"><?php _e('Url of catalog', 'affegg'); ?></label>
            </th>
            <td>
                <input id="url" name="item[url]" type="text" value="<?php echo esc_url($item['url']) ?>"
                       size="50" class="code" placeholder="<?php _e('Url of catalog', 'affegg'); ?>"<?php if ($item['id']) echo ' readonly="readonly"'; ?>>
                <p class="description"><?php _e('Set catalog url of shop', 'affegg'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="check_frequency"><?php _e('Frequency', 'affegg'); ?></label>
            </th>
            <td>
                <select name="item[check_frequency]">
                    <option value="43200"<?php if ($item['check_frequency'] == 43200) echo ' selected="selected"'; ?>><?php _e('2 times in day', 'affegg'); ?></option>                    
                    <option value="86400"<?php if ($item['check_frequency'] == 86400) echo ' selected="selected"'; ?>><?php _e('Once a day', 'affegg'); ?></option>
                    <option value="259200"<?php if ($item['check_frequency'] == 259200) echo ' selected="selected"'; ?>><?php _e('Each three days', 'affegg'); ?></option>
                    <option value="604800"<?php if ($item['check_frequency'] == 604800) echo ' selected="selected"'; ?>><?php _e('Once a week', 'affegg'); ?></option>
                </select>  
                <p class="description"><?php _e('Checking of catalog for new products', 'affegg'); ?></p>                
            </td>
        </tr> 

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="items_per_check"><?php _e('Handle products', 'affegg'); ?></label>
            </th>
            <td>
                <input id="items_per_check" name="item[items_per_check]" value="<?php echo esc_attr($item['items_per_check']) ?>"
                       type="number" class="small-text">
                <p class="description"><?php _e('Only first N products will be handled.', 'affegg'); ?></p>                
            </td>
        </tr>       

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="items_per_post"><?php _e('Products in one post', 'affegg'); ?></label>
            </th>
            <td>

                <input id="items_per_post" name="item[items_per_post]" value="<?php echo esc_attr($item['items_per_post']) ?>"
                       type="number" class="small-text">
                <p class="description"><?php _e('How many products do we need to merge in one post.', 'affegg'); ?></p>                

            </td>
        </tr>          

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="post_status"><?php _e('Post status', 'affegg'); ?></label>
            </th>
            <td>
                <select id="post_status" name="item[post_status]">
                    <option value="1"<?php if ($item['post_status'] == 1) echo ' selected="selected"'; ?>><?php _e('Published', 'affegg'); ?></option>                    
                    <option value="0"<?php if ($item['post_status'] == 0) echo ' selected="selected"'; ?>><?php _e('Draft', 'affegg'); ?></option>
                </select>                
            </td>
        </tr>         

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="user_id"><?php _e('User', 'affegg'); ?></label>
            </th>
            <td>
                <?php
                wp_dropdown_users(array('name' => 'item[user_id]',
                    'who' => 'authors', 'id' => 'user_id', 'selected' => $item['user_id']));
                ?>
                <p class="description"><?php _e('Name of this user will be used for post publishing', 'affegg'); ?></p>                
            </td>
        </tr> 

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="category"><?php _e('Category', 'affegg'); ?></label>
            </th>
            <td>
                <?php
                wp_dropdown_categories(array('name' => 'item[category]',
                    'id' => 'category', 'selected' => $item['category'], 'hide_empty' => false));
                ?>
                <p class="description"><?php _e('Post category', 'affegg'); ?></p>                
            </td>
        </tr>      

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="title_tpl"><?php _e('Template for title', 'affegg'); ?></label>
            </th>
            <td>

                <input id="items_per_post" name="item[title_tpl]" value="<?php echo esc_attr($item['title_tpl']) ?>"
                       type="text" class="regular-text ltr">
                <p class="description">
                    <?php _e('Template for post title', 'affegg'); ?>
                    <?php _e('Use tags:', 'affegg'); ?> %PRODUCT.TITLE%, %PRODUCT.PRICE%, %PRODUCT.OLD_PRICE%, %PRODUCT.CURRENCY%, %PRODUCT.MANUFACTURER%.<br>
                    <?php _e('You can use "formulas" with a list of synonyms, of which one will be selected as random option, for example, {Discount|Sale|Cheap}.', 'affegg'); ?>
                </p>                

            </td>
        </tr>          

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="template"><?php _e('Template for shopfronts', 'affegg'); ?></label>
            </th>
            <td>
                <select id="template" name="item[template]">
                    <?php foreach ($templates as $tpl_id => $tpl_name): ?>
                        <option value="<?php echo esc_attr($tpl_id); ?>" <?php selected($item['template'], $tpl_id); ?>><?php _e(esc_attr($tpl_name), 'affegg'); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <?php /*
          <tr class="form-field">
          <th valign="top" scope="row">
          <label for="post_status"><?php _e('Проверка дубликатов', 'affegg');?></label>
          </th>
          <td>
          <select id="post_status" name="item[duplicate_type]">
          <option value="1"<?php if ($item['duplicate_type'] == 1) echo ' selected="selected"'; ?>><?php _e('Определяется парсером', 'affegg');?></option>
          <option value="0"<?php if ($item['duplicate_type'] == 0) echo ' selected="selected"'; ?>><?php _e('По URL', 'affegg');?></option>
          </select>
          <p class="description"><?php _e('Некоторые каталоги могут изменить URL на одни и те же товары со временем. Попробуйте выбрать "Определяется парсером" для таких каталогов.', 'affegg');?></p>
          </td>
          </tr>
         * 
         */ ?>      

        <tr class="form-field">
            <th valign="top" scope="row">
                <label for="status"><?php _e('Task status', 'affegg'); ?></label>
            </th>
            <td>
                <select id="status" name="item[status]">
                    <option value="1"<?php if ($item['status']) echo ' selected="selected"'; ?>><?php _e('Works', 'affegg'); ?></option>                    
                    <option value="0"<?php if (!$item['status']) echo ' selected="selected"'; ?>><?php _e('Stoped', 'affegg'); ?></option>
                </select>
                <p class="description"><?php _e('Autobloging can be stoped.', 'affegg'); ?></p>                                
            </td>
        </tr>
    </tbody>
</table>
