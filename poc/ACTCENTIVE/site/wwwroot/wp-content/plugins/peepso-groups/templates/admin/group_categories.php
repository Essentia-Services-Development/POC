<?php
// If the details are not open, adjust CSS
$open_pref = get_user_meta(get_current_user_id(), 'peepso_admin_group_category_open_'.$category->get('id'),TRUE);

// Force opening of the newly added field
if(isset($force_open)) {
	$open_pref = 1;
}

// get_user_meta might return an empty string
$open = (strlen($open_pref) && 1 == $open_pref) ? FALSE : 'display:none';

// if not published, dim the container
$postbox_muted 			= (0 == $category->get('published')) ? 'postbox-muted' : FALSE;

// if field is not required, hide postbox-required-mark
$required_mark_hidden 	= 'hidden';

// Title of the category
$title = ($category->get('name')) ? $category->get('name') : __('no name', 'groupso');

?>


<div class="postbox ps-postbox--settings no-padd <?php echo $postbox_muted;?>" data-id="<?php echo $category->get('id');?>">

	<h3 class="hndle ps-postbox__title ui-sortable-handle ps-js-handle">

		<div class="postbox-sorting">
			<span class="fa fa-arrows"></span>
			<span class="fa fa-<?php echo ($open) ? 'expand' : 'compress' ?> ps-js-group-category-toggle"></span>
		</div>

		<div class="ps-postbox__title-label ps-js-group-category-title">
			<span id="group-category-<?php echo $category->get('id');?>-box-title" class="ps-postbox__title-text ps-js-group-category-title-text">
				<?php echo $title; ?>
			</span>

			<span class="postbox-required-mark <?php echo $required_mark_hidden;?>" id="group-category-<?php echo $category->get('id');?>-required-mark"><strong>*</strong></span>

			<span class="fa fa-edit"></span>

			<small>
				<?php #echo $title_after;?>
			</small>
		</div>

		<div class="ps-postbox__title-editor">
			<input type="text" value="<?php echo $category->get('name'); ?>"
				   data-parent-id="<?php echo $category->get('id'); ?>"
				   data-prop-type="prop"
				   data-prop-name="name" <?php echo (1 == get_post_meta($category->get('id'),'default_title',TRUE)) ? 'data-prop-title-is-default="1"':'';?>>

			<button class="button ps-js-btn ps-js-cancel"><?php echo __('Cancel', 'groupso'); ?></button>
			<button class="button button-primary ps-js-btn ps-js-save"><?php echo __('Save', 'groupso'); ?></button>
			<span class="ps-settings__progress ps-js-progress">
				<img src="images/loading.gif" style="display:none">
				<i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
			</span>
		</div>
	</h3>

	<div class="ps-js-group-category" data-id="<?php echo $category->get('id');?>" style="<?php echo $open;?>">
		<div class="ps-settings">
			<div id="group-category<?php echo $category->get('id');?>-tab-1" class="ps-tab__content">
				<?php

				/** ENABLED **/
				$params = array(
					'type'			=> 'checkbox',
					'data'			=> array(
						'data-prop-type' 		=> 'prop',
						'data-prop-name' 		=> 'published',
						'data-disabled-value' 	=> 'private',
						'value'					=> 'publish',
						'admin_value'			=> $category->get('published'),
						'id'					=> 'group-category-' . $category->get('id') .'-published',
					),
					'category'			=> $category,
					'label'			=> __('Published', 'groupso'),
					'label_after'	=> '',
				);

				// add "checked" manually - the value is "published" and by default checkbox looks for "1"
				if(1 == $category->published) {
					$params['data']['checked'] = 'checked';
				}

				PeepSoTemplate::exec_template('admin','group_categories_config_field', $params);

                $params = array(
                    'type' => 'text',
                    'data' => array(
                        'data-prop-type' => 'prop',
                        'data-prop-name' => 'slug',
                        'value' => $category->get('slug'),
                        'admin_value' => $category->get('slug'),
                        'id' => 'field-' . $category->get('id') . '-slug',
                    ),
                    'category' => $category,
                    'label' => __('URL slug', 'peepso-core'),
                    'label_after' =>  '<a href="#" class="ps-js-generate-slug">'.__('Generate','groupso').'</a>',
                );


                PeepSoTemplate::exec_template('admin', 'group_categories_config_field', $params);

                $params = array(
                    'type' => 'textarea',
                    'data' => array(
                        'data-prop-type' => 'prop',
                        'data-prop-name' => 'description',
                        'value' => $category->get('description'),
                        'admin_value' => $category->get('description'),
                        'id' => 'field-' . $category->get('id') . '-description',
                        'cols'                  => '100',
                        'rows'                  => '5',
                    ),
                    'category' => $category,
                    'label' => __('Description', 'peepso-core'),
                    'label_after' =>  '',
                );


                PeepSoTemplate::exec_template('admin', 'group_categories_config_field', $params);
				?>
			</div>
			<div class="ps-settings__action">
				<a data-id="<?php echo $category->get('id'); ?>" href="#" class="ps-js-group-category-delete"><i class="fa fa-trash"></i></a>
			</div>

				<input type="hidden" id="group-category-<?php echo $category->get('id');?>-id" value="<?php echo $category->get('id');?>">
				<input type="hidden" id="group-category-<?php echo $category->get('id');?>-order" value="<?php echo $category->order;?>">
			</div>
		</div>
	</div>
