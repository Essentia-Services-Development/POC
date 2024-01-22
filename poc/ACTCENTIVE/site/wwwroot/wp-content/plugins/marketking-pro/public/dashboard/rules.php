<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('B2BKING_DIR') && defined('MARKETKINGPRO_DIR') && intval(get_option('marketking_enable_b2bkingintegration_setting', 1)) === 1){
	if (intval(get_option('b2bking_show_dynamic_rules_vendors_setting_marketking', 1)) === 1){
		if(marketking()->vendor_has_panel('b2bkingrules')){
			$user_id = marketking()->get_data('user_id');
			$currentuser = new WP_User($user_id);
			
		    ?>
		    <style type="text/css">
		    	.b2bking_otherrules_area {
		    	    padding: 20px;
		    	    font-size: 14px;
		    	    background: #fff;
		    	    box-shadow: 0 1px 6px rgb(0 0 0 / 17%);
		    	    border-radius: 5px;
		    	}
		    </style>

		    <div class="nk-content marketking_rules_page">
		        <div class="container-fluid">
		            <div class="nk-content-inner">
		                <div class="nk-content-body">
		                	<div class="nk-block-head nk-block-head-sm">
	    	                    <div class="nk-block-between">
	    	                        <div class="nk-block-head-content">
	    	                            <h3 class="nk-block-title page-title"><?php esc_html_e('Rules','marketking');?></h3>
	    	                        </div><!-- .nk-block-head-content -->
	    	                        <div class="nk-block-head-content">
	    	                            <div class="toggle-wrap nk-block-tools-toggle">
	    	                                <a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
	    	                                <div class="toggle-expand-content" data-content="pageMenu">
	    	                                    <ul class="nk-block-tools g-3">
	    	                                        <li>
	    	                                            <div class="form-control-wrap">
	    	                                                <div class="form-icon form-icon-right">
	    	                                                    <em class="icon ni ni-search"></em>
	    	                                                </div>
	    	                                                <input type="text" class="form-control" id="marketking_offers_search" placeholder="<?php esc_html_e('Search rules...','marketking');?>">
	    	                                            </div>
	    	                                        </li>
	    	                                        <li class="nk-block-tools-opt">
	    	                                            <a href="#b2bking_marketking_new_rule_container" rel="modalzz:open" class="btn btn-primary d-none d-md-inline-flex b2bking_marketking_new_rule"><em class="icon ni ni-plus"></em><span><?php esc_html_e('New Rule','marketking');?></span></a>
	    	                                        </li>
	    	                                    </ul>
	    	                                </div>
	    	                            </div>
	    	                        </div><!-- .nk-block-head-content -->
	    	                    </div><!-- .nk-block-between -->
	    	                </div>

		                    <div class="nk-block">
		                        <div class="row g-gs">
		                        	<div class="col-xxl-12">
			                            <article class="messaging-content-area">
						                  	<div id="b2bkingmarketking_dashboard_offers_table_container">
						                  		<table id="b2bkingmarketking_dashboard_offers_table">
						                  		        <thead>
						                  		            <tr>
						                  		                <th><?php esc_html_e('Rule Name','marketking'); ?></th>
						                  		                <th><?php esc_html_e('Rule Type','marketking'); ?></th>
						                  		                <th><?php esc_html_e('Actions','marketking'); ?></th>
						                  		            </tr>
						                  		        </thead>
						                  		        <tbody>
						                  		        	<?php
						                  		        	// get all vendor rules
						                  		        	$vendor_rules = get_user_meta($user_id,'b2bking_marketking_vendor_rules_list_ids', true);
						                  		        	if (!empty($vendor_rules)){
						                  		        		$ids_array=explode(',',$vendor_rules);
						                  		        		foreach($ids_array as $rule_id){
						                  		        			if (!empty($rule_id) && $rule_id !== NULL){
							                  		        			// title
							                  		        			$title = get_the_title($rule_id);
							                  		        			$rule_name = get_post_meta($rule_id, 'b2bking_rule_what', true);
							                		        			switch ( $rule_name ){
							                		        				case 'discount_amount':
							                		        				$rule_name = esc_html__('Discount Amount','marketking');
							                		        				break;

							                		        				case 'discount_percentage':
							                		        				$rule_name = esc_html__('Discount Percentage','marketking');
							                		        				break;

							                		        				case 'fixed_price':
							                		        				$rule_name = esc_html__('Fixed Price','marketking');
							                		        				break;

							                		        				case 'hidden_price':
							                		        				$rule_name = esc_html__('Hidden Price','marketking');
							                		        				break;

							                		        				case 'free_shipping':
							                		        				$rule_name = esc_html__('Free Shipping','marketking');
							                		        				break;

							                		        				case 'minimum_order':
							                		        				$rule_name = esc_html__('Minimum Order','marketking');
							                		        				break;

							                		        				case 'maximum_order':
							                		        				$rule_name = esc_html__('Maximum Order','marketking');
							                		        				break;

							                		        				case 'required_multiple':
							                		        				$rule_name = esc_html__('Required Multiple','marketking');
							                		        				break;

							                		        				case 'tax_exemption_user':
							                		        				$what = esc_html__('Tax Exemption','marketking');
							                		        				break;

							                		        				case 'tax_exemption':
							                		        				$rule_name = esc_html__('Zero Tax Product','marketking');
							                		        				break;

							                		        				case 'add_tax_percentage':
							                		        				$rule_name = esc_html__('Add Tax / Fee (Percentage)','marketking');
							                		        				break;

							                		        				case 'add_tax_amount':
							                		        				$rule_name = esc_html__('Add Tax / Fee (Amount)','marketking');
							                		        				break;

							                		        				case 'replace_prices_quote':
							                		        				$rule_name = esc_html__('Replace Cart with Quote System','marketking');
							                		        				break;

							                		        				case 'set_currency_symbol':
							                		        				$rule_name = esc_html__('Set Currency Symbol','marketking');
							                		        				break;

							                		        				case 'payment_method_minimum_order':
							                		        				$rule_name = esc_html__('Payment Method Minimum Order','marketking');
							                		        				break;
							                		        			}

							                  		        			?>
							                  		        			<tr>
							                  		        			    <td><?php echo esc_html($title); ?></td>
							                  		        			    <td><?php echo esc_html($rule_name); ?></td>
							                  		        			    <td><a href="#b2bking_marketking_new_rule_container" rel="modalzz:open"><button class="btn btn-secondary marketking-btn marketking-btn-default b2bking_rule_edit_table" type="button" value="<?php echo esc_attr($rule_id);?>"><?php esc_html_e('Edit','marketking');?></button></a>&nbsp;<button class="btn btn-secondary marketking-btn marketking-btn-default b2bking_rule_delete_table" type="button" value="<?php echo esc_attr($rule_id);?>"><?php esc_html_e('Delete','marketking');?></button></td>
							                  		        			</tr>
							                  		        			<?php
							                  		        		}
						                  		        		}
						                  		        	}
						                  		        	?>
						                  		        </tbody>
						                  		        <tfoot>
						                  		            <tr>
							             		                <th><?php esc_html_e('Rule Name','marketking'); ?></th>
							             		                <th><?php esc_html_e('Rule Type','marketking'); ?></th>
							             		                <th><?php esc_html_e('Actions','marketking'); ?></th>
							             		            </tr>
						                  		        </tfoot>
						                  		    </table>
						                  		</div>
						                </article>

						                <div id="b2bking_marketking_new_rule_container" class="modalzz">
						                	<br>
						                	<div class="b2bking_dynamic_rule_metabox_content_container">
						                		<div class="b2bking_rule_select_container">
						                			<div class="b2bking_rule_label"><?php esc_html_e('Rule type:','marketking'); ?></div>
						                			<select id="b2bking_rule_select_what" name="b2bking_rule_select_what">
						                				<optgroup label="<?php esc_attr_e('Basic Rules', 'marketking'); ?>"> 
						                					<option value="discount_amount"><?php esc_html_e('Discount Amount','marketking'); ?></option>
						                					<option value="discount_percentage"><?php esc_html_e('Discount Percentage','marketking'); ?></option>
						                					<option value="fixed_price"><?php esc_html_e('Fixed Price','marketking'); ?></option>
						                					<option value="hidden_price"><?php esc_html_e('Hidden Price','marketking'); ?></option>
						                					<option value="minimum_order"><?php esc_html_e('Minimum Order','marketking'); ?></option>
						                					<option value="maximum_order"><?php esc_html_e('Maximum Order','marketking'); ?></option>
						                					<option value="required_multiple"><?php esc_html_e('Required Multiple','marketking'); ?></option>
						                				</optgroup>
						                			</select>
						                		</div>
							                	<!-- content section -->
							                	<div class="b2bking_rule_select_container" id="b2bking_container_applies">
							                		<div class="b2bking_rule_label"><?php esc_html_e('Applies to:','marketking'); ?></div>
							                		
							                		<select id="b2bking_rule_select_applies" name="b2bking_rule_select_applies">
							                			<optgroup label="<?php esc_attr_e('Cart', 'marketking'); ?>" id="b2bking_cart_total_optgroup" >
							                					<option value="multiple_options"><?php esc_html_e('Select multiple products','marketking'); ?></option>
							                			</optgroup>
							                			<optgroup label="<?php esc_attr_e('Products (individual)', 'marketking'); ?>">
							                				<?php
							                				// Get all products
							                				$products = get_posts(array( 
							                					'post_type' => 'product',
							                					'post_status'=>'publish',
							                					'author'=> $user_id, 
							                					'numberposts' => -1,
							                					'fields' => 'ids',
							                				));

							                				foreach ($products as $product){
							               						$productobj = wc_get_product($product);
							                					echo '<option value="product_'.esc_attr($product).'">'.esc_html($productobj->get_name()).'</option>';
							                				}
							                				?>
							                			</optgroup>
							                			<optgroup label="<?php esc_attr_e('Products (Individual Variations)', 'marketking'); ?>">
							                				<?php
							                				// Get all products
							                				$products = get_posts(array( 
							                					'post_type' => 'product_variation',
							                					'post_status'=>'publish',
							                					'author'=> $user_id, 
							                					'numberposts' => -1,
							                					'fields' => 'ids',
							                				));

							                				foreach ($products as $product){
							                					$productobj = wc_get_product($product);
							                					echo '<option value="product_'.esc_attr($product).'">'.esc_html($productobj->get_name()).'</option>';
							                				}
							                				?>
							                			</optgroup>
							                		</select>
							                	</div>


							                	
							                	<div class="b2bking_rule_select_container">
							                		<div class="b2bking_rule_label"><?php esc_html_e('For who:','marketking'); ?></div>
							                		<select id="b2bking_rule_select_who" name="b2bking_rule_select_who">
							                			<optgroup label="<?php esc_attr_e('Everyone', 'marketking'); ?>">
							                				<option value="all_registered"><?php esc_html_e('All registered users','marketking'); ?></option>
							                				<option value="everyone_registered_b2b"><?php esc_html_e('All registered B2B users','marketking'); ?></option>
							                				<option value="everyone_registered_b2c"><?php esc_html_e('All registered B2C users','marketking'); ?></option>
							                				<option value="user_0"><?php esc_html_e('All guest users (logged out)','marketking'); ?></option>
							                				<option value="multiple_options"><?php esc_html_e('Select multiple options','marketking'); ?></option>
							                			</optgroup>
							                			<optgroup label="<?php esc_attr_e('B2B Groups', 'marketking'); ?>">
							                				<?php
							                				// Get all groups
							                				$groups = get_posts( array( 'post_type' => 'b2bking_group','post_status'=>'publish','numberposts' => -1) );
							                				foreach ($groups as $group){
							                					echo '<option value="group_'.esc_attr($group->ID).'">'.esc_html($group->post_title).'</option>';
							                				}
							                				?>
							                			</optgroup>
							                		</select>
							                	</div>
							                	<div id="b2bking_container_quantity_value" class="b2bking_rule_select_container">
							                		<div class="b2bking_rule_label"><?php esc_html_e('Quantity/Value:','marketking'); ?></div>
							                		<select id="b2bking_rule_select_quantity_value" name="b2bking_rule_select_quantity_value">
							                			<option value="quantity"><?php esc_html_e('Quantity','marketking'); ?></option>
							                			<option value="value"><?php esc_html_e('Value','marketking'); ?></option>
							                		</select>
							                	</div>
							                	<div id="b2bking_container_howmuch" class="b2bking_rule_select_container">
							                		<div class="b2bking_rule_label"><?php esc_html_e('How much:','marketking'); ?></div>
							                		<input type="number" min="0.001" step="0.00001" name="b2bking_rule_select_howmuch" id="b2bking_rule_select_howmuch" value="">
							                	</div>

							                	<br /><br />
							                	<?php
							                	if (intval(get_option( 'b2bking_replace_product_selector_setting', 0 )) === 1){
							                		?>
							                	<div id="b2bking_rule_select_applies_replaced_container" >
							                		<div class="b2bking_rule_label"><?php esc_html_e('Product or Variation ID(s) (comma-separated):','marketking'); ?></div>
							                		<?php
							                		/*
							                		$replaced_content = get_post_meta($post->ID,'b2bking_rule_applies_multiple_options', true);
							                		$replaced_content_array = explode(',', $replaced_content);
							                		$replaced_content_string = '';
							                		foreach ($replaced_content_array as $element){
							                			$replaced_content_string.= substr($element, 8).',';
							                		}
							                		// remove last comma
							                		$replaced_content_string = substr($replaced_content_string, 0, -1);
							                		*/
							                		$replaced_content_string = '';
							                		?>
							                		<input type="text" id="b2bking_rule_select_applies_replaced" name="b2bking_rule_select_applies_replaced" value="<?php echo esc_attr($replaced_content_string);?>">
							                	</div>
							                		<?php
							                	}
							                	?>

							                	<div id="b2bking_select_multiple_product_categories_selector" >
							                		<div class="b2bking_select_multiple_products_categories_title">
							                			<?php esc_html_e('Select multiple products','marketking'); ?>
							                		</div>
							                		<select id="b2bking_select_multiple_product_categories_selector_select" class="b2bking_select_multiple_product_categories_selector_select" name="b2bking_select_multiple_product_categories_selector_select[]" multiple>
							                	        <optgroup id="b2bking_products_optgroup" label="<?php esc_attr_e('Products (individual)', 'marketking'); ?>">
							                	        	<?php
							                	        	// Get all products
							                	        	$products = get_posts( array(
							                	        		'post_type' => 'product',
							                	        		'post_status'=>'publish',
							                	        		'author'=> $user_id, 
							                	        		'numberposts' => -1,
							                	        		'fields' => 'ids',
							                	        	));

							                	        	foreach ($products as $product){
							                	        		$productobj = wc_get_product($product);
							                	        		echo '<option value="product_'.esc_attr($product).'">'.esc_html($productobj->get_name()).'</option>';
							                	        	}
							                	        	?>
							                	        </optgroup>
							                	        <optgroup label="<?php esc_attr_e('Products (individual variations)', 'marketking'); ?>">
							                	        	<?php
							                	        	// Get all products
							                	        	$products = get_posts( array( 
							                	        		'post_type' => 'product_variation',
							                	        		'post_status'=>'publish',
							                	        		'author'=> $user_id, 
							                	        		'numberposts' => -1,
							                	        		'fields' => 'ids'
							                	        	));

							                	        	foreach ($products as $product){
							                					$productobj = wc_get_product($product);
							                	        		echo '<option value="product_'.esc_attr($product).'">'.esc_html($productobj->get_name()).'</option>';
							                	        		}
							                	        	?>
							                	        </optgroup>
							                		</select>
							                		<br /><button id="b2bking_select_all" type="button" class="btn btn-secondary"><?php esc_html_e('Select all', 'marketking'); ?></button>&nbsp;&nbsp;<button id="b2bking_unselect_all" class="btn btn-secondary" type="button"><?php esc_html_e('Unselect all', 'marketking'); ?></button>
							                	</div>

							                	<div id="b2bking_select_multiple_users_selector" >
							                		<div class="b2bking_select_multiple_products_categories_title">
							                			<?php esc_html_e('Select multiple options','marketking'); ?>
							                		</div>
							                		<select id="b2bking_select_multiple_users_selector_select" class="b2bking_select_multiple_product_categories_selector_select" name="b2bking_select_multiple_users_selector_select[]" multiple>
							                			<optgroup label="<?php esc_attr_e('Everyone', 'marketking'); ?>">
							                				<option value="all_registered"><?php esc_html_e('All registered users','marketking'); ?></option>
							                				<option value="everyone_registered_b2b"><?php esc_html_e('All registered B2B users','marketking'); ?></option>
							                				<option value="everyone_registered_b2c"><?php esc_html_e('All registered B2C users','marketking'); ?></option>
							                				<option value="user_0"><?php esc_html_e('All guest users (logged out)','marketking'); ?></option>

							                			</optgroup>
							                			<optgroup label="<?php esc_attr_e('B2B Groups', 'marketking'); ?>">
							                				<?php
							                				// Get all groups
							                				$groups = get_posts( array( 'post_type' => 'b2bking_group','post_status'=>'publish','numberposts' => -1) );
							                				foreach ($groups as $group){
							                					echo '<option value="group_'.esc_attr($group->ID).'">'.esc_html($group->post_title).'</option>';
							                				}
							                				?>
							                			</optgroup>
							                		</select>
							                	</div>

							                	<div class="b2bking_rule_label_discount"><?php esc_html_e('Additional options:','marketking'); ?></div>
							                	<div class="b2bking_dynamic_rule_discount_show_everywhere_checkbox_container">
							                		<div class="b2bking_dynamic_rule_discount_show_everywhere_checkbox_name">
							                			<?php esc_html_e('Show discount everywhere, as "Sale Price". Incompatible with "Value" conditions.','marketking'); ?>
							                		</div>
							                		<input type="checkbox" value="1" id="b2bking_dynamic_rule_discount_show_everywhere_checkbox_input" name="b2bking_dynamic_rule_discount_show_everywhere_checkbox_input">
							                	</div>
							                	<!-- Information panel -->
							                	<div class="b2bking_discount_options_information_box">
							                		<svg class="b2bking_group_payment_shipping_information_box_icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 36 36">
							                		  <path fill="#358BBB" d="M18 0C8.06 0 0 8.06 0 18s8.06 18 18 18 18-8.06 18-18S27.94 0 18 0zm0 28.446a1.607 1.607 0 110-3.213 1.607 1.607 0 010 3.213zm2.527-8.819a1.941 1.941 0 00-1.241 1.8v.912a.322.322 0 01-.322.322h-1.928a.322.322 0 01-.322-.322v-.864c0-.928.27-1.844.8-2.607a4.49 4.49 0 012.093-1.643c1.366-.527 2.25-1.672 2.25-2.921 0-1.772-1.732-3.215-3.857-3.215s-3.857 1.443-3.857 3.215v.305a.322.322 0 01-.322.321h-1.928a.322.322 0 01-.322-.321v-.305c0-1.58.691-3.054 1.945-4.15C14.721 9.095 16.312 8.517 18 8.517c1.688 0 3.279.582 4.484 1.635 1.253 1.097 1.945 2.572 1.945 4.15 0 2.323-1.531 4.412-3.902 5.324z"/>
							                		</svg>
							                		<?php 
							                			esc_html_e('Checking this box will show discounts everywhere. Not checking it will show discounts in cart subtotal. ','marketking');
							                		?>
							                	</div>
							                	<br />
							                	<div class="b2bking_rule_select_container" id="b2bking_rule_select_conditions_container">
							                		<div class="b2bking_rule_label"><?php esc_html_e('Conditions: (optional)','marketking'); ?></div>
							                		<input type="text" name="b2bking_rule_select_conditions" id="b2bking_rule_select_conditions">
							                		<div id="b2bking_condition_number_1" class="b2bking_rule_condition_container">
							                			<select class="b2bking_dynamic_rule_condition_name b2bking_condition_identifier_1">
							                				<option value="product_quantity"><?php esc_html_e('Product Quantity','marketking'); ?></option>
							                				<option value="product_value"><?php esc_html_e('Product Value','marketking'); ?></option>
							                			</select>
							                			<select class="b2bking_dynamic_rule_condition_operator b2bking_condition_identifier_1">
							                				<option value="greater"><?php esc_html_e('greater (>)','marketking'); ?></option>
							                				<option value="equal"><?php esc_html_e('equal (=)','marketking'); ?></option>
							                				<option value="smaller"><?php esc_html_e('smaller (<)','marketking'); ?></option>
							                			</select>
							                			<input type="number" step="0.00001" class="b2bking_dynamic_rule_condition_number b2bking_condition_identifier_1" placeholder="<?php esc_attr_e('Enter the quantity/value','marketking');?>">
							                			<button type="button" class="b2bking_dynamic_rule_condition_add_button b2bking_condition_identifier_1"><?php esc_html_e('Add Condition', 'marketking'); ?></button>
							                		</div>
							                	</div>
							                	<div class="b2bking_rule_conditions_information_box">
							                		<svg class="b2bking_group_payment_shipping_information_box_icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 36 36">
							                		  <path fill="#358BBB" d="M18 0C8.06 0 0 8.06 0 18s8.06 18 18 18 18-8.06 18-18S27.94 0 18 0zm0 28.446a1.607 1.607 0 110-3.213 1.607 1.607 0 010 3.213zm2.527-8.819a1.941 1.941 0 00-1.241 1.8v.912a.322.322 0 01-.322.322h-1.928a.322.322 0 01-.322-.322v-.864c0-.928.27-1.844.8-2.607a4.49 4.49 0 012.093-1.643c1.366-.527 2.25-1.672 2.25-2.921 0-1.772-1.732-3.215-3.857-3.215s-3.857 1.443-3.857 3.215v.305a.322.322 0 01-.322.321h-1.928a.322.322 0 01-.322-.321v-.305c0-1.58.691-3.054 1.945-4.15C14.721 9.095 16.312 8.517 18 8.517c1.688 0 3.279.582 4.484 1.635 1.253 1.097 1.945 2.572 1.945 4.15 0 2.323-1.531 4.412-3.902 5.324z"/>
							                		</svg>
							                		<span id="b2bking_rule_conditions_information_box_text">
							                		<?php 
							                			esc_html_e('Conditions must apply cumulatively.','marketking');
							                		?>
							                		</span>
							                	</div>
							                </div>
							                <br />
							                <?php esc_html_e('Rule Title:','marketking'); ?>
							                <input type="text" required id="b2bking_new_rule_title" class="b2bking_offer_text_input b2bking_offer_item_name" placeholder="<?php esc_attr_e('Enter the rule title here','marketking'); ?>">
							                <input type="hidden" id="b2bking_new_rule_user_id" value="<?php echo esc_attr($user_id); ?>"><br /><br />
							                <div class="b2bking_marketking_save_new_rule_button_container">
							                	<button type="button" value="new" class="btn btn-secondary marketking-btn marketking-btn-theme b2bking_marketking_save_new_rule"><?php esc_html_e('Save Rule','marketking');?></button>
							                </div>
						                </div>
						            </div>
		                        </div><!-- .row -->
		                    </div><!-- .nk-block -->
		                    <br><br>
    	                	<div class="nk-block-head nk-block-head-sm">
        	                    <div class="nk-block-between">
        	                        <div class="nk-block-head-content">
        	                            <h3 class="nk-block-title page-title"><?php esc_html_e('Other Rules','marketking');?></h3>
        	                        </div><!-- .nk-block-head-content -->
        	                       
        	                    </div><!-- .nk-block-between -->
        	                </div>
                            <div class="nk-block">
                                <div class="row g-gs">
                                	<div class="col-xxl-12">
        	                            <div class="b2bking_otherrules_area">
        	                            	<?php
        	                            	$marketking_minordervalb2b = get_user_meta($user_id, 'marketking_minordervalb2b', true);
        	                            	$marketking_minorderqtyb2b = get_user_meta($user_id, 'marketking_minorderqtyb2b', true);
        	                            	$marketking_maxordervalb2b = get_user_meta($user_id, 'marketking_maxordervalb2b', true);
        	                            	$marketking_maxorderqtyb2b = get_user_meta($user_id, 'marketking_maxorderqtyb2b', true);

        	                            	$marketking_minordervalb2c = get_user_meta($user_id, 'marketking_minordervalb2c', true);
        	                            	$marketking_minorderqtyb2c = get_user_meta($user_id, 'marketking_minorderqtyb2c', true);
        	                            	$marketking_maxordervalb2c = get_user_meta($user_id, 'marketking_maxordervalb2c', true);
        	                            	$marketking_maxorderqtyb2c = get_user_meta($user_id, 'marketking_maxorderqtyb2c', true);

        	                            	?>
        	                            	<h6><?php esc_html_e('B2B Customers','marketking');?> </h6><br>
        	                            	<div class="form-group">
	                                            <label class="form-label" for="minordervalb2b"><?php esc_html_e('Minimum Order Value','marketking');?></label>
	                                            <div class="form-control-wrap">
	                                                <div class="form-icon form-icon-left">
	                                                    <em class="icon ni ni-edit"></em>
	                                                </div>
	                                                <input type="text" class="form-control" id="minordervalb2b" placeholder="<?php esc_attr_e('Enter a minimum order value (E.g. $100) applicable to B2B customers.','marketking');?>" value="<?php echo esc_attr($marketking_minordervalb2b);?>">
	                                            </div>
	                                        </div>
        	                            	
        	                            	<div class="form-group">
	                                            <label class="form-label" for="maxordervalb2b"><?php esc_html_e('Maximum Order Value','marketking');?></label>
	                                            <div class="form-control-wrap">
	                                                <div class="form-icon form-icon-left">
	                                                    <em class="icon ni ni-edit"></em>
	                                                </div>
	                                                <input type="text" class="form-control" id="maxordervalb2b" placeholder="<?php esc_attr_e('Enter a maximum order value (E.g. $100) applicable to B2B customers.','marketking');?>" value="<?php echo esc_attr($marketking_maxordervalb2b);?>">
	                                            </div>
	                                        </div>

        	                            	<div class="form-group">
	                                            <label class="form-label" for="minorderqtyb2b"><?php esc_html_e('Minimum Order Quantity','marketking');?></label>
	                                            <div class="form-control-wrap">
	                                                <div class="form-icon form-icon-left">
	                                                    <em class="icon ni ni-edit"></em>
	                                                </div>
	                                                <input type="text" class="form-control" id="minorderqtyb2b" placeholder="<?php esc_attr_e('Enter a minimum order quantity (E.g. 20 pieces) applicable to B2B customers.','marketking');?>" value="<?php echo esc_attr($marketking_minorderqtyb2b);?>">
	                                            </div>
	                                        </div>
        	                            	<div class="form-group">
	                                            <label class="form-label" for="minorderqtyb2b"><?php esc_html_e('Maximum Order Quantity','marketking');?></label>
	                                            <div class="form-control-wrap">
	                                                <div class="form-icon form-icon-left">
	                                                    <em class="icon ni ni-edit"></em>
	                                                </div>
	                                                <input type="text" class="form-control" id="maxorderqtyb2b" placeholder="<?php esc_attr_e('Enter a maximum order quantity (E.g. 20 pieces) applicable to B2B customers.','marketking');?>" value="<?php echo esc_attr($marketking_maxorderqtyb2b);?>">
	                                            </div>
	                                        </div>

	                                        <?php
	                                        if (get_option( 'b2bking_plugin_status_setting', 'b2b' ) === 'hybrid'){
	                                        	?>
	                                        	<br>
	                                        	<h6><?php esc_html_e('Retail Customers','marketking');?> </h6><br>

	        	                            	<div class="form-group">
		                                            <label class="form-label" for="minordervalb2c"><?php esc_html_e('Minimum Order Value','marketking');?></label>
		                                            <div class="form-control-wrap">
		                                                <div class="form-icon form-icon-left">
		                                                    <em class="icon ni ni-edit"></em>
		                                                </div>
		                                                <input type="text" class="form-control" id="minordervalb2c" placeholder="<?php esc_attr_e('Enter a minimum order value (E.g. $100) applicable to retail customers.','marketking');?>" value="<?php echo esc_attr($marketking_minordervalb2c);?>">
		                                            </div>
		                                        </div>
	        	                            	<div class="form-group">
		                                            <label class="form-label" for="maxordervalb2c"><?php esc_html_e('Maximum Order Value','marketking');?></label>
		                                            <div class="form-control-wrap">
		                                                <div class="form-icon form-icon-left">
		                                                    <em class="icon ni ni-edit"></em>
		                                                </div>
		                                                <input type="text" class="form-control" id="maxordervalb2c" placeholder="<?php esc_attr_e('Enter a maximum order value (E.g. $100) applicable to retail customers.','marketking');?>" value="<?php echo esc_attr($marketking_maxordervalb2c);?>">
		                                            </div>
		                                        </div>
            	                            	<div class="form-group">
    	                                            <label class="form-label" for="minorderqtyb2c"><?php esc_html_e('Minimum Order Quantity','marketking');?></label>
    	                                            <div class="form-control-wrap">
    	                                                <div class="form-icon form-icon-left">
    	                                                    <em class="icon ni ni-edit"></em>
    	                                                </div>
    	                                                <input type="text" class="form-control" id="minorderqtyb2c" placeholder="<?php esc_attr_e('Enter a minimum order quantity (E.g. 20 pieces) applicable to retail customers.','marketking');?>" value="<?php echo esc_attr($marketking_minorderqtyb2c);?>">
    	                                            </div>
    	                                        </div>
            	                            	<div class="form-group">
    	                                            <label class="form-label" for="maxorderqtyb2c"><?php esc_html_e('Maximum Order Quantity','marketking');?></label>
    	                                            <div class="form-control-wrap">
    	                                                <div class="form-icon form-icon-left">
    	                                                    <em class="icon ni ni-edit"></em>
    	                                                </div>
    	                                                <input type="text" class="form-control" id="maxorderqtyb2c" placeholder="<?php esc_attr_e('Enter a maximum order quantity (E.g. 20 pieces) applicable to retail customers.','marketking');?>" value="<?php echo esc_attr($marketking_maxorderqtyb2c);?>">
    	                                            </div>
    	                                        </div>
	                                        	<?php
	                                        }
        	                            	?>

        	                            	<button class="btn btn-primary" type="submit" id="marketking_save_otherrules_settings" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('Save Settings','marketking');?></button>

        	                            </div>
    	                            </div>
    	                        </div>
    	                    </div>
		                </div>
		            </div>
		        </div>
		    </div>
		    <?php
		}
	}
}