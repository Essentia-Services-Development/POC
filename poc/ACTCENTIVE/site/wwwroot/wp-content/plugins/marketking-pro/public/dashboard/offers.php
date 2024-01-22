<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('B2BKING_DIR') && defined('MARKETKINGPRO_DIR') && intval(get_option('marketking_enable_b2bkingintegration_setting', 1)) === 1){
	if (intval(get_option('b2bking_enable_offers_setting', 1)) === 1){
    if(marketking()->vendor_has_panel('b2bkingoffers')){
  	    ?>
  	    <div class="nk-content marketking_offers_page">
  	        <div class="container-fluid">
  	            <div class="nk-content-inner">
  	                <div class="nk-content-body">
  	                	<div class="nk-block-head nk-block-head-sm">
      	                    <div class="nk-block-between">
      	                        <div class="nk-block-head-content">
      	                            <h3 class="nk-block-title page-title"><?php esc_html_e('Offers','marketking');?></h3>
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
      	                                                <input type="text" class="form-control" id="marketking_offers_search" placeholder="<?php esc_html_e('Search offers...','marketking');?>">
      	                                            </div>
      	                                        </li>
      	                                        <li class="nk-block-tools-opt">
      	                                            <a href="#b2bking_marketking_new_offer_container" rel="modalzz:open" class="btn btn-primary d-none d-md-inline-flex b2bking_marketking_new_offer"><em class="icon ni ni-plus"></em><span><?php esc_html_e('New Offer','marketking');?></span></a>
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
                                    		                <th><?php esc_html_e('Offer Name','marketking'); ?></th>
                                    		                <th><?php esc_html_e('Price','marketking'); ?></th>
                                    		                <th><?php esc_html_e('Actions','marketking'); ?></th>
                                    		            </tr>
                                    		        </thead>
                                    		        <tbody>
                                    		        	<?php
                                    		        	// get all vendor offers
                                    		        	$user_id = marketking()->get_data('user_id');
                                                  $currentuser = new WP_User($user_id);
                                                  
                                    		        	$vendor_offers = get_user_meta($user_id,'b2bking_marketking_vendor_offers_list_ids', true);
                                    		        	if (!empty($vendor_offers)){
                                    		        		$ids_array=explode(',',$vendor_offers);
                                    		        		foreach($ids_array as $offer_id){
                                    		        			if (!empty($offer_id) && $offer_id !== NULL && get_post_type($offer_id) === 'b2bking_offer'){
                                        		        			// title
                                        		        			$title = get_the_title($offer_id);
                                        		        			// price
                                        		        			$offer_details = get_post_meta($offer_id,'b2bking_offer_details',true);
                                        		        			$offer_elements = explode('|',$offer_details);
                                        		        			$currency_symbol = get_woocommerce_currency_symbol();
                                      		        			$price = 0;
                                        		        			foreach ($offer_elements as $element){
                            		        			        		$element_array = explode(';',$element);
                            		        			        		if(isset($element_array[1]) && isset($element_array[2])){
                            		        			        			$price += $element_array[1]*$element_array[2];
                            		        			        		}
                            		        			        	}
                                        		        			?>
                                        		        			<tr>
                                        		        			    <td><?php echo esc_html($title); ?></td>
                                        		        			    <td><?php echo wc_price(esc_html($price)); ?></td>
                                        		        			    <td><a href="#b2bking_marketking_new_offer_container" rel="modalzz:open"><button class="marketking-btn marketking-btn-default b2bking_offer_edit_table btn btn-secondary" type="button" value="<?php echo esc_attr($offer_id);?>"><?php esc_html_e('Edit','marketking');?></button></a>&nbsp;<button class="marketking-btn marketking-btn-default b2bking_offer_delete_table btn btn-secondary" type="button" value="<?php echo esc_attr($offer_id);?>"><?php esc_html_e('Delete','marketking');?></button></td>
                                        		        			</tr>
                                        		        			<?php
                                        		        		}
                                    		        		}
                                    		        	}
                                    		        	?>
                                    		        </tbody>
                                    		        <tfoot>
                                    		            <tr>
                                   		                <th><?php esc_html_e('Offer Name','marketking'); ?></th>
                                   		                <th><?php esc_html_e('Price','marketking'); ?></th>
                                   		                <th><?php esc_html_e('Actions','marketking'); ?></th>
                                   		            </tr>
                                    		        </tfoot>
                                    		    </table>
                                        	</div>
                                      </article>

                                      <div id="b2bking_marketking_new_offer_container" class="modalzz">
                                      	<br>
  			                           	<!-- Show Offer Visibility -->
                              		    <div class="b2bking_group_visibility_container">
                              		    	<div class="b2bking_group_visibility_container_top">
                              		    		<?php esc_html_e( 'Group Visibility', 'marketking' ); ?>
                              		    	</div>
                              		    	<div class="b2bking_group_visibility_container_content">
                              		    		<div class="b2bking_group_visibility_container_content_title">
                              		    			<svg class="b2bking_group_visibility_container_content_title_icon" xmlns="http://www.w3.org/2000/svg" width="31" height="29" fill="none" viewBox="0 0 31 29">
                              						<path fill="#C4C4C4" d="M15.5 6.792V.625H.083v27.75h30.834V6.792H15.5zm-9.25 18.5H3.167v-3.084H6.25v3.084zm0-6.167H3.167v-3.083H6.25v3.083zm0-6.167H3.167V9.875H6.25v3.083zm0-6.166H3.167V3.708H6.25v3.084zm6.167 18.5H9.333v-3.084h3.084v3.084zm0-6.167H9.333v-3.083h3.084v3.083zm0-6.167H9.333V9.875h3.084v3.083zm0-6.166H9.333V3.708h3.084v3.084zm15.416 18.5H15.5v-3.084h3.083v-3.083H15.5v-3.083h3.083v-3.084H15.5V9.875h12.333v15.417zM24.75 12.958h-3.083v3.084h3.083v-3.084zm0 6.167h-3.083v3.083h3.083v-3.083z"></path>
                              						</svg>
                              						<?php esc_html_e( 'Groups who can see this offer', 'marketking' ); ?>
                              		    		</div>
                              	            	<?php
                              		            	$groups = get_posts([
                              		            	  'post_type' => 'b2bking_group',
                              		            	  'post_status' => 'publish',
                              		            	  'numberposts' => -1
                              		            	]);
                              		            	foreach ($groups as $group){
                              		            		?>
                              		            		<div class="b2bking_group_visibility_container_content_checkbox">
                              		            			<div class="b2bking_group_visibility_container_content_checkbox_name">
                              		            				<?php echo esc_html($group->post_title); ?>
                              		            			</div>
                              		            			<input type="hidden" name="b2bking_group_<?php echo esc_attr($group->ID);?>" value="0">
                              		            			<input type="checkbox" value="1" class="b2bking_group_visibility_container_content_checkbox_input" name="b2bking_group_<?php echo esc_attr($group->ID);?>" id="b2bking_group_<?php echo esc_attr($group->ID);?>" value="1" />
                              		            		</div>
                              		            		<?php
                              		            	}
                              		            ?>
                              		    	</div>
                              		    </div>

                              		    <div class="b2bking_group_visibility_container">
                              		    	<div class="b2bking_group_visibility_container_top">
                              		    		<?php esc_html_e( 'User Visibility', 'marketking' ); ?>
                              		    	</div>
                              		    	<div class="b2bking_group_visibility_container_content">
                              		    		<div class="b2bking_group_visibility_container_content_title">
                              						<svg class="b2bking_user_visibility_container_content_title_icon" xmlns="http://www.w3.org/2000/svg" width="31" height="23" fill="none" viewBox="0 0 31 23">
                              						  <path fill="#C4C4C4" d="M9.333 11.58c3.076 0 5.396-2.32 5.396-5.396C14.73 3.11 12.41.79 9.333.79c-3.075 0-5.396 2.32-5.396 5.395 0 3.076 2.32 5.396 5.396 5.396zm1.542 1.462H7.792c-4.25 0-7.709 3.458-7.709 7.708v1.542h18.5V20.75c0-4.25-3.458-7.708-7.708-7.708zm17.412-7.258l-6.63 6.616-1.991-1.992-2.18 2.18 4.171 4.17 8.806-8.791-2.176-2.183z"/>
                              						</svg>
                              						<?php esc_html_e( 'Users who can see this offer (comma-separated).  You can also enter emails for the "Email Offer" functionality.', 'marketking' ); ?>
                              		    		</div>
                              		    		<textarea name="b2bking_category_users_textarea" id="b2bking_category_users_textarea"></textarea>
                              		    	</div>
                              		    </div>

                              		    <!-- Show Custom Offer Text -->
                              		    <div class="b2bking_group_visibility_container">
                              		    	<div class="b2bking_group_visibility_container_top">
                          		    			<?php esc_html_e( 'Offer Custom Text', 'marketking' ); ?>
                          		    		</div>
                      	        		    <div class="b2bking_group_visibility_container_content">
                      	        		    	<div class="b2bking_group_visibility_container_content_title">
                      	        		    		<svg class="b2bking_group_visibility_container_content_title_icon" xmlns="http://www.w3.org/2000/svg" width="39" height="39" fill="none" viewBox="0 0 39 39">
                      	        		    		  <path fill="#C4C4C4" fill-rule="evenodd" d="M29.25 2.438H9.75a4.875 4.875 0 00-4.875 4.874v24.375a4.875 4.875 0 004.875 4.875h19.5a4.875 4.875 0 004.875-4.874V7.313a4.875 4.875 0 00-4.875-4.875zM12.187 9.75a1.219 1.219 0 000 2.438h14.626a1.219 1.219 0 000-2.438H12.188zm-1.218 6.094a1.219 1.219 0 011.219-1.219h14.624a1.219 1.219 0 010 2.438H12.188a1.219 1.219 0 01-1.218-1.22zm1.219 3.656a1.219 1.219 0 000 2.438h14.624a1.219 1.219 0 000-2.438H12.188zm0 4.875a1.219 1.219 0 000 2.438H19.5a1.219 1.219 0 000-2.438h-7.313z" clip-rule="evenodd"/>
                      	        		    		</svg>
                      	        		    		<?php esc_html_e('Additional custom text to display for this offer','marketking');?>
                      	        		    	</div>
                      	        		    	<textarea name="b2bking_offer_customtext" id="b2bking_offer_customtext_textarea"></textarea>
                      	        		    </div>
                      	        		</div>

                      	        		<!-- Show Offer Items -->
                      	        		<textarea id="b2bking_admin_offer_textarea" name="b2bking_admin_offer_textarea"></textarea>
                      	        		<div id="b2bking_offer_number_1" class="b2bking_offer_line_number">
                      	        			<div class="b2bking_offer_input_container">
                      	        				<?php esc_html_e('Item name:','marketking'); ?>
                      	        				<br />
                      	        				<?php
                      	        				if (intval(get_option( 'b2bking_offers_product_selector_setting', 0 )) === 1){
                      	        					?>
                      	        					<select class="b2bking_offer_product_selector b2bking_offer_item_name">
                      	        						<optgroup label="<?php esc_attr_e('Products (individual)', 'marketking'); ?>">
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
                      	        				<?php } else { ?>
                      	        				<input type="text" class="b2bking_offer_text_input b2bking_offer_item_name" placeholder="<?php esc_attr_e('Enter the item name','marketking'); ?>">
                      	        			<?php } ?>
                      	        			</div>
                      	        			<div class="b2bking_offer_input_container">
                      	        				<?php esc_html_e('Item quantity:','marketking'); ?>
                      	        				<br />
                      	        				<input type="number" min="0" class="b2bking_offer_text_input b2bking_offer_item_quantity" placeholder="<?php esc_attr_e('Enter the quantity','marketking'); ?>">
                      	        			</div>
                      	        			<div class="b2bking_offer_input_container">
                      	        				<?php esc_html_e('Unit price:','marketking'); ?>
                      	        				<br />
                      	        				<input type="number" step="0.0001" min="0" class="b2bking_offer_text_input b2bking_offer_item_price" placeholder="<?php esc_attr_e('Enter the unit price','marketking'); ?>"> 
                      	        			</div>
                      	        			<div class="b2bking_offer_input_container">
                      	        				<?php esc_html_e('Item subtotal:','marketking'); ?>
                      	        				<br />
                      	        				<div class="b2bking_item_subtotal"><?php echo get_woocommerce_currency_symbol();?>0</div>
                      	        			</div>
                      	        			<div class="b2bking_offer_input_container">
                      	        				<br />
                      	        				<button type="button" class="button-primary button b2bking_offer_add_item_button btn btn-secondary"><?php esc_html_e('Add item','marketking'); ?></button>
                      	        			</div> 
                      	        			<br /><br />
                      	        		</div>

                      	        		<!-- Show Offer Subtotals-->
                      	        		<br /><hr>
                      	        		<div id="b2bking_offer_total_text">
                      	        			<?php 
                      	        			esc_html_e('Offer Total: ','marketking'); 
                      	        			?>
                      	        			<div id="b2bking_offer_total_text_number">
                      	        				<?php echo get_woocommerce_currency_symbol();?>0
                      	        			</div>
                      	        		</div>
                      	        		<br />
                      	        		<?php esc_html_e('Offer Title:','marketking'); ?>
                      	        		<input type="text" required id="b2bking_new_offer_title" class="b2bking_offer_text_input b2bking_offer_item_name" placeholder="<?php esc_attr_e('Enter the offer title here','marketking'); ?>">
                      	        		<input type="hidden" id="b2bking_new_offer_user_id" value="<?php echo esc_attr($user_id); ?>">
                      	        		<br /><br />
                      	        		<div class="b2bking_marketking_save_new_offer_button_container">
                      	        			<button type="button" value="new" class="marketking-btn marketking-btn-theme b2bking_marketking_save_new_offer btn btn-secondary"><?php esc_html_e('Save Offer','marketking');?></button>
                      	        		</div>
                                      </div>
  					            </div>
  	                        </div><!-- .row -->
  	                    </div><!-- .nk-block -->
  	                </div>
  	            </div>
  	        </div>
  	    </div>
  	    <?php
      }
	}
}