<?php

class WC_Shipping_MarketKing extends WC_Shipping_Method {

	/**
	 * Constructor. The instance ID is passed to this.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                    = 'marketking_shipping';
		$this->instance_id           = absint( $instance_id );
		$this->method_title          = esc_html__( 'Vendor Shipping (MarketKing)', 'marketking' ) ;
		$this->method_description    = esc_html__( 'Allows vendors to choose and configure their own shipping methods.', 'marketking' );
		$this->supports              = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal'
		);
		$this->instance_form_fields = array(
			'enabled' => array(
				'title' 		=> esc_html__( 'Enable/Disable', 'marketking' ),
				'type' 			=> 'checkbox',
				'label' 		=> esc_html__( 'Enable this shipping method', 'marketking' ),
				'default' 		=> 'yes',
			),
			'title' => array(
				'title' 		=> esc_html__( 'Method Title', 'marketking' ),
				'type' 			=> 'text',
				'description' 	=> esc_html__( 'This controls the title which the user sees during checkout.', 'marketking' ),
				'default'		=> esc_html__( 'Vendor Shipping', 'marketking' ),
				'desc_tip'		=> true
			)
		);
		$this->enabled              = $this->get_option( 'enabled' );
		$this->title                = $this->get_option( 'title' );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	function init() {
	    $this->instance_form_fields = array(
	        'title' => array(
	            'title'         => esc_html__( 'Method title', 'marketking' ),
	            'type'          => 'text',
	            'description'   => esc_html__( 'This controls the title which the user sees during checkout.', 'marketking' ),
	            'default'       => esc_html__( 'Vendor Shipping', 'marketking' ),
	            'desc_tip'      => true,
	        ),
	        'tax_status' => array(
	            'title'         => esc_html__( 'Tax status', 'marketking' ),
	            'type'          => 'select',
	            'class'         => 'wc-enhanced-select',
	            'default'       => 'taxable',
	            'options'       => array(
	                'taxable'   => esc_html__( 'Taxable', 'marketking' ),
	                'none'      => _x( 'None', 'Tax status', 'marketking' ),
	            ),
	        )
	    );

	    $this->title      = $this->get_option( 'title' );
	    $this->tax_status = $this->get_option( 'tax_status' );
	}

	function calculate_shipping( $package = array() ) {

	    $rates = array();

	    $vendor_id = $package['vendor_id'];

	    $shipping_methods = marketking()->get_vendor_shipping_methods($vendor_id);

	    if ( empty( $shipping_methods ) ) {
	        return;
	    }

	    foreach ( $shipping_methods as $key => $method ) {

	    	// 1. Check that method is enabled
	    	if (intval($method['enabled']) !== 1){
	    		continue;
	    	}

	    	// 2. Check that zone matches
	    	$shipping_zone = WC_Shipping_Zones::get_zone_matching_package( $package );
	    	$zone_id = $shipping_zone->get_id();

	    	if (intval($method['zoneid']) !== intval($zone_id)){
	    		continue;
	    	}

	    	// Tax

	    	// overwrite defaults with settings if they exist
	    	$settings = get_option('woocommerce_'.$method['value'].'_'.$method['instanceid'].'_settings');

	    	if (empty($settings)){
	    		$settings = array();
	    		$settings['tax_status'] = 'none';
	    		$settings['title'] = '';
	    	}

	    	if (!empty($settings)){
	    	    $method['name'] = $settings['title'];
	    	}

	    	$tax_rate = '';
	    	if (isset($settings['tax_status'])){
	    		if ($settings['tax_status'] === 'none'){
	    			$tax_rate = false;
	    		}
	    	}
	        $has_costs     = false;


	        if ( $method['value'] == 'flat_rate' ) {
	            $setting_cost = isset( $settings['cost'] ) ? stripslashes_deep( $settings['cost'] ) : '';

	            if ( '' !== $setting_cost ) {
	                $has_costs = true;
	                $cost = $this->evaluate_cost( $setting_cost, array(
	                    'qty'  => $this->get_package_item_qty( $package ),
	                    'cost' => $package['contents_cost'],
	                ) );
	            }

	            // Add shipping class costs.
	            $shipping_classes = WC()->shipping->get_shipping_classes();

	            if ( ! empty( $shipping_classes ) ) {
	                $found_shipping_classes = $this->find_shipping_classes( $package );
	                $highest_class_cost     = 0;
	                $calculation_type = ! empty( $settings['calculation_type'] ) ? $settings['calculation_type'] : 'class';
	                foreach ( $found_shipping_classes as $shipping_class => $products ) {
	                    // Also handles BW compatibility when slugs were used instead of ids
	                    $shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
	                    $class_cost_string   = $shipping_class_term && $shipping_class_term->term_id
	                                            ? ( ! empty( $settings['class_cost_' . $shipping_class_term->term_id ] ) ? stripslashes_deep( $settings['class_cost_' . $shipping_class_term->term_id] ) : '' )
	                                            : ( ! empty( $settings['no_class_cost'] ) ? $settings['no_class_cost'] : '' );

	                    if ( '' === $class_cost_string ) {
	                        continue;
	                    }

	                    $has_costs  = true;

	                    $class_cost = $this->evaluate_cost( $class_cost_string, array(
	                        'qty'  => array_sum( wp_list_pluck( $products, 'quantity' ) ),
	                        'cost' => array_sum( wp_list_pluck( $products, 'line_total' ) ),
	                    ) );

	                    if ( 'class' === $calculation_type ) {
	                        $cost += $class_cost;
	                    } else {
	                        $highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
	                    }
	                }

	                if ( 'order' === $calculation_type && $highest_class_cost ) {
	                    $cost += $highest_class_cost;
	                }
	            }

	        } elseif ( 'free_shipping' == $method['value'] ) {
	            $is_available = $this->free_shipping_is_available( $package, $method );

	            if ( $is_available ) {
	                $cost = '0';
	                $has_costs = true;
	            }
	        } else {
	            if ( ! empty( $settings['cost'] ) ) {
	                $has_costs = true;
	                $cost = $settings['cost'];
	            } else {
	                $has_costs = true;
	                $cost = '0';
	            }
	        }


	        if ( ! $has_costs ) {
	            continue;
	        }

	        $rates[] = array(
	            'id'          => $method['instanceid'],
	            'label'       => $method['name'],
	            'cost'        => $cost,
	            'description' => ! empty( $settings['description'] ) ? $settings['description'] : '',
	            'taxes'       => $tax_rate,
	            'default'     => 'off',
	            'value'		  => $method['value']
	        );
	    }

	    $rates = apply_filters('marketking_vendor_package_rates', $rates);

	    // send shipping rates to WooCommerce
	    if( is_array( $rates ) && count( $rates ) > 0 ) {

	        // cycle through rates to send and alter post-add settings
	        foreach( $rates as $key => $rate ) {

	        	// USPS PLUGIN SHIPPING INTEGRATION 
	        	if ( $rate['value'] == 'usps' ) {

	        		// save usps found rates to option, so we can retrieve them and add them
	        		add_filter('woocommerce_shipping_usps_found_rates', function($found_rates, $raw_found_rates, $offer_rates){
	        			update_option('marketking_last_usps_found_rates', $found_rates);
	        			return $found_rates;
	        		}, 10, 3);

		        	// get rate details, for more complex methods other than flat rate, local shipping, etc.
		        	$vendor_shipping_methods = get_user_meta($vendor_id,'marketking_vendor_shipping_methods', true);
		        	if (empty($vendor_shipping_methods)){
		        	    $vendor_shipping_methods = array();
		        	}
		        	foreach ($vendor_shipping_methods as $index => $method){
		        		if (intval($method['instanceid']) === intval($rate['id'])){
		        			// get details here
		        			$shipping_class_names = WC()->shipping->get_shipping_method_class_names();
		        			if (isset($shipping_class_names['usps'])){
			        			if (class_exists($shipping_class_names['usps'])){
				        			$method_instance = new $shipping_class_names['usps'](intval($rate['id']));
				        			$method_instance->calculate_shipping($package);
				        		}
				        	}

		        		}
		        	}

		        	$foundrates = get_option('marketking_last_usps_found_rates');
		        	if (!empty($foundrates)){
			        	foreach ($foundrates as $rate){
			        		$this->add_rate($rate);
			        	}
			        }

		        	// go to next rate
		        	continue;
		        }

            	// UPS PLUGIN SHIPPING INTEGRATION 
            	if ( $rate['value'] == 'ups' ) {

            		// save UPS found rates to option, so we can retrieve them and add them
            		add_filter('woocommerce_shipping_ups_rate', function($rate, $currency, $shipment, $apiclient){
            			$upsrates = get_option('marketking_last_ups_found_rates', array());
            			$upsrates[] = $rate;
            			update_option('marketking_last_ups_found_rates', $upsrates);
            			return $rate;
            		}, 10, 4);

    	        	// get rate details, for more complex methods other than flat rate, local shipping, etc.
    	        	$vendor_shipping_methods = get_user_meta($vendor_id,'marketking_vendor_shipping_methods', true);
    	        	if (empty($vendor_shipping_methods)){
    	        	    $vendor_shipping_methods = array();
    	        	}
    	        	foreach ($vendor_shipping_methods as $index => $method){
    	        		if (intval($method['instanceid']) === intval($rate['id'])){
    	        			// get details here
    	        			$shipping_class_names = WC()->shipping->get_shipping_method_class_names();
    	        			if (isset($shipping_class_names['ups'])){
	    	        			if (class_exists($shipping_class_names['ups'])){
	    		        			$method_instance = new $shipping_class_names['ups'](intval($rate['id']));
	    		        			$method_instance->calculate_shipping($package);
	    		        			$offer_rates = $method_instance->get_option( 'offer_rates', 'all' );
	    		        		}
	    		        	}

    	        		}
    	        	}

    	        	$upsrates = get_option('marketking_last_ups_found_rates', array());
    	        	if (!empty($upsrates)){
    	        		if ( 'all' == $offer_rates ) {
    	        			foreach ( $upsrates as $rate ) {
    	        				$this->add_rate( $rate );
    	        			}
    	        		} else {
    	        			$cheapest_rate = '';
    	        			foreach ( $upsrates as $rate ) {
    	        				if ( ! $cheapest_rate || $cheapest_rate['cost'] > $rate['cost'] ) {
    	        					$cheapest_rate = $rate;
    	        				}
    	        			}
    	        			$this->add_rate( $cheapest_rate );
    	        		}
	    	        	// set rates to empty 
	    	        	update_option('marketking_last_ups_found_rates', array());
	    	        }

    	        	// go to next rate
    	        	continue;
    	        }



	            $this->add_rate( array(
	                'id'        => $rate['id'],
	                'label'     => apply_filters( 'marketking_vendor_shipping_rate_label', $rate['label'], $rate ),
	                'cost'      => $rate['cost'],
	                'meta_data' => array( 'description' => $rate['description'] ),
	                'package'   => $package,
	                'taxes'     => $rate['taxes'],
	            ));

	            if( $rate['default'] == 'on' ) {
	                $this->default = $rate['id'];
	            }
	        }
	    }
	}

	public function get_package_item_qty( $package ) {
	    $total_quantity = 0;
	    foreach ( $package['contents'] as $item_id => $values ) {
	        if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
	            $total_quantity += $values['quantity'];
	        }
	    }
	    return $total_quantity;
	}

	public function find_shipping_classes( $package ) {
	    $found_shipping_classes = array();

	    foreach ( $package['contents'] as $item_id => $values ) {
	        if ( $values['data']->needs_shipping() ) {
	            $found_class = $values['data']->get_shipping_class();

	            if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
	                $found_shipping_classes[ $found_class ] = array();
	            }

	            $found_shipping_classes[ $found_class ][ $item_id ] = $values;
	        }
	    }

	    return $found_shipping_classes;
	}

	public function fee( $atts ) {
	    $atts = shortcode_atts( array(
	        'percent' => '',
	        'min_fee' => '',
	        'max_fee' => '',
	    ), $atts, 'fee' );

	    $calculated_fee = 0;

	    if ( $atts['percent'] ) {
	        $calculated_fee = $this->fee_cost * ( floatval( $atts['percent'] ) / 100 );
	    }

	    if ( $atts['min_fee'] && $calculated_fee < $atts['min_fee'] ) {
	        $calculated_fee = $atts['min_fee'];
	    }

	    if ( $atts['max_fee'] && $calculated_fee > $atts['max_fee'] ) {
	        $calculated_fee = $atts['max_fee'];
	    }

	    return $calculated_fee;
	}


	public function evaluate_cost( $sum, $args = array() ) {
	    include_once( WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php' );

	    // Allow 3rd parties to process shipping cost arguments
	    $args           = apply_filters( 'woocommerce_evaluate_shipping_cost_args', $args, $sum, $this );
	    $locale         = localeconv();
	    $decimals       = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' );
	    $this->fee_cost = $args['cost'];

	    // Expand shortcodes
	    add_shortcode( 'fee', array( $this, 'fee' ) );

	    $sum = do_shortcode( str_replace(
	        array(
	            '[qty]',
	            '[cost]',
	        ),
	        array(
	            $args['qty'],
	            $args['cost'],
	        ),
	        $sum
	    ) );

	    remove_shortcode( 'fee', array( $this, 'fee' ) );

	    // Remove whitespace from string
	    $sum = preg_replace( '/\s+/', '', $sum );

	    // Remove locale from string
	    $sum = str_replace( $decimals, '.', $sum );

	    // Trim invalid start/end characters
	    $sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

	    // Do the math
	    return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
	}

	public function free_shipping_is_available( $package, $method ) {

		$settings = get_option('woocommerce_'.$method['value'].'_'.$method['instanceid'].'_settings');

	    $has_met_min_amount = false;
	    $min_amount = ! empty( $settings['min_amount'] ) ? $settings['min_amount'] : 0;

	    $line_subtotal      = wp_list_pluck( $package['contents'], 'line_subtotal', null );
	    $line_total         = wp_list_pluck( $package['contents'], 'line_total', null );
	    $discount_total     = array_sum( $line_subtotal ) - array_sum( $line_total );
	    $line_subtotal_tax  = wp_list_pluck( $package['contents'], 'line_subtotal_tax', null );
	    $line_total_tax     = wp_list_pluck( $package['contents'], 'line_tax', null );
	    $discount_tax_total = array_sum( $line_subtotal_tax ) - array_sum( $line_total_tax );

	    $total = array_sum( $line_subtotal ) + array_sum( $line_subtotal_tax );

	    if ( WC()->cart->display_prices_including_tax() ) {
	        $total = round( $total - ( $discount_total + $discount_tax_total ), wc_get_price_decimals() );
	    } else {
	        $total = round( $total - $discount_total, wc_get_price_decimals() );
	    }

	    if ( $total >= $min_amount ) {
	        $has_met_min_amount = true;
	    }

	    // if is set to N/A (no min amount)
	    if (empty($settings['requires'])){
	    	$has_met_min_amount = true;
	    }

	    return apply_filters( 'marketking_free_shipping_available', $has_met_min_amount, $package, $method );
	}
}