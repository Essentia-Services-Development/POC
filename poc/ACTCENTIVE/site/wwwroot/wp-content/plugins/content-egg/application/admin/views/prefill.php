<?php defined('\ABSPATH') || exit;

use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\admin\GeneralConfig;
?>

<?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    <div class="cegg-maincol">
    <?php endif; ?>
    <div class="wrap">
        <h2>
            <?php esc_html_e('Fill', 'content-egg'); ?>
        </h2>
        <p>
            <?php esc_html_e('This extension will fill module\'s data for all existed posts.', 'content-egg'); ?>
            <?php esc_html_e('All existing data and keywords will not be erased or overwritten.', 'content-egg'); ?>
        </p>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="module_id"><?php esc_html_e('Delay', 'content-egg'); ?></label></th>
                <td>
                    <select id="delay">
                        <option value="1000">1</option>
                        <option value="2000">2</option>
                        <option value="3000">3</option>
                        <option value="4000">4</option>
                        <option value="5000">5</option>
                        <option value="6000">6</option>
                        <option value="7000">7</option>
                        <option value="8000">8</option>
                        <option value="90000">9</option>
                        <option value="10000">10</option>
                        <option value="15000">15</option>
                        <option value="20000">20</option>
                        <option value="30000">30</option>
                        <option value="0">0</option>
                        
                    </select>
                    <p class="description"><?php esc_html_e('Delay in seconds between each post prefill.', 'content-egg'); ?></p>

                </td>
            </tr>            
            <tr>
                <th scope="row"><label for="module_id"><?php esc_html_e('Add data for module', 'content-egg'); ?></label></th>
                <td>
                    <select id="module_id">
                        <?php foreach (ModuleManager::getInstance()->getParserModules(true) as $module): ?>
                            <option value="<?php echo esc_attr($module->getId()); ?>"><?php echo esc_html($module->getName()); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="keyword_source"><?php esc_html_e('Keyword source', 'content-egg'); ?></label></th>
                <td>
                    <select id="keyword_source">
                        <option value="_title"><?php esc_html_e('Post title', 'content-egg'); ?></option>
                        <option value="_density"><?php esc_html_e('Keyword density', 'content-egg'); ?></option>
                        <option value="_tags"><?php esc_html_e('Post tags', 'content-egg'); ?></option>
                        <option value="_custom_field"><?php esc_html_e('Arbitrary custom field', 'content-egg'); ?></option>
                        <?php foreach (ModuleManager::getInstance()->getAffiliateParsers(true) as $module): ?>
                            <option value="_keyword_<?php echo esc_attr($module->getId()); ?>"><?php echo \esc_html($module->getName() . ': ' .  __('autoupdate keyword', 'content-egg')); ?> </option>
                        <?php endforeach; ?>
                        <?php foreach (ModuleManager::getInstance()->getAffiliateParsers(true) as $module): ?>
                            <option value="_ean_<?php echo esc_attr($module->getId()); ?>"><?php echo \esc_html($module->getName() . ': ' .  __('EAN', 'content-egg')); ?> </option>
                        <?php endforeach; ?>
                    </select>
                    <input style="display: none;" id="custom_field" type="text" class="regular-text" placeholder="<?php esc_html_e('Set the name of a custom field', 'content-egg'); ?>">
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="autoupdate"><?php esc_html_e('Autoupdate', 'content-egg'); ?></label></th>
                <td>
                    <label><input id="autoupdate" type="checkbox" value="1"> <?php esc_html_e('Add Keyword for the automatic update', 'content-egg'); ?></label>
                    <p class="description"><?php esc_html_e('Only for those modules, which have autoupdate function.', 'content-egg'); ?></p>
                </td>
            </tr>            

            <tr>
                <th scope="row"><label for="keyword_count"><?php esc_html_e('Number of words', 'content-egg'); ?></label></th>
                <td>
                    <select id="keyword_count">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo esc_attr($i); ?>"<?php if ($i == 5) echo ' selected="selected"'; ?>><?php echo esc_html($i); ?></option>
                        <?php endfor; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Maximum words for one search query.', 'content-egg'); ?></p>
                </td>
            </tr>      

            <tr>
                <th scope="row"><label for="minus_words"><?php esc_html_e('"Minus" words', 'content-egg'); ?></label></th>
                <td>
                    <input id="minus_words" type="text" class="regular-text">
                    <p class="description"><?php esc_html_e('Remove these words from keyword. You can set several minus words/phrases with commas.', 'content-egg'); ?></p>
                </td>
            </tr>       

            <tr>
                <th scope="row"><label for="post_type"><?php esc_html_e('Post type', 'content-egg'); ?></label></th>
                <td>
                    <select id="post_type" multiple="multiple">
                        <?php foreach (GeneralConfig::getInstance()->option('post_types') as $post_type): ?>
                            <option value="<?php echo \esc_attr($post_type); ?>" selected="selected"><?php echo \esc_attr($post_type); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('You can set all supported post types in General settings -> Post Types.', 'content-egg'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="post_status"><?php esc_html_e('Post status', 'content-egg'); ?></label></th>
                <td>
                    <?php
                    $post_statuses = array_merge(get_post_statuses(), array('future' => __('Future')));
                    $selected_post_statuses = array('publish', 'future');
                    ?>
                    <select id="post_status" multiple="multiple" size="5">
                        <?php foreach ($post_statuses as $post_status_value => $post_status_name): ?>
                            <option value="<?php echo \esc_attr($post_status_value); ?>" 
                                    <?php if (in_array($post_status_value, $selected_post_statuses)): ?>selected="selected"<?php endif; ?>>
                                        <?php echo \esc_attr($post_status_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>  

            <tr>
                <th scope="row"><label for="custom_fields"><?php esc_html_e('Add custom fields', 'content-egg'); ?></label></th>
                <td>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <input type="text" name="custom_field_names[]" placeholder="<?php echo esc_attr(sprintf(__('Custom Field %d', 'content-egg'), $i + 1)); ?>" id="custom_fields" class="regular-text" />
                        <input type="text" name="custom_field_values[]" placeholder="<?php echo esc_attr(sprintf(__('Value %d', 'content-egg'), $i + 1)); ?>" class="regular-text" /><br>
                    <?php endfor; ?>
                    <?php $tags = '%KEYWORD%, %RANDOM(10,50)%, %PRODUCT.title%, %PRODUCT.price%, ...'; ?>
                    <p class="description"><?php echo esc_html(sprintf(__('You can use tags: %s.', 'content-egg'), $tags)); ?></p>
                </td>
            </tr>             

        </table>        

        <div id="progressbar" name="progressbar"></div>
        <div><?php esc_html_e('Total posts', 'content-egg'); ?>: <b><span id="post_ids_total"></span></b></div>

        <div>
            <br>
            <button class="button-primary" type="button" id="start_prefill"><?php esc_html_e('Start', 'content-egg'); ?></button>
            <button class="button-primary" type="button" id="start_prefill_begin"><?php esc_html_e('Restart', 'content-egg'); ?></button>
            <button class="button-secondary" type="button" id="stop_prefill" disabled><?php esc_html_e('Stop', 'content-egg'); ?></button>

            <span id="ajaxWaiting__" style="display:none;"><img src="<?php echo esc_url_raw(\ContentEgg\PLUGIN_RES . '/img/ajax-loader.gif'); ?>" /></span>
            <span id="ajaxBusy" style="display:none;"><img src="<?php echo esc_url_raw(\ContentEgg\PLUGIN_RES . '/img/ajax-loader.gif'); ?>" /></span>


        </div>

        <div class="egg-prefill-log" id="logs"></div>



    </div>
    <?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    </div>    
    <?php include('_promo_box.php'); ?>
<?php endif; ?>  