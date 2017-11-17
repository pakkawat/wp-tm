<?php
// MUST have WordPress.
if ( !defined( 'WPINC' ) ) {
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );
}

function geodir_wpi_allow_pm_scripts() {
    global $pagenow, $post, $current_screen;

    if ( $pagenow == 'post.php' || $pagenow == 'edit.php' ) {
        $post_type = ! empty( $post->post_type ) ? $post->post_type : ( ! empty( $current_screen->post_type ) ? $current_screen->post_type : '' );

        if ( geodir_wpi_is_gd_post_type( $post_type ) ) {
            return true;
        }
    }

    return false;
}

function geodir_wpi_admin_js_localize( $localize ) {
    $localize['hasInvoicing']       = (bool)defined( 'WPINV_VERSION' );
    $localize['hasGD']              = true;
    $localize['hasPM']              = true;
    $localize['emptyInvoice']       = __( 'Add at least one item to save invoice!', 'geodir_payments' );
    $localize['deletePackage']      = __( 'GD package items should be deleted from GD payment manager only, otherwise it will break invoices that created with this package!', 'geodir_payments' );
    $localize['deletePackages']     = __( 'GD package items should be deleted from GD payment manager only', 'geodir_payments' );
    $localize['errDeletePackage']   = __( 'This item is in use! Before delete this package, you need to delete all the invoice(s) using this package.', 'geodir_payments' );
    $localize['ConfirmCreateInvoice']   = __( 'Are you sure you want to create invoice for this listing?', 'geodir_payments' );

    return $localize;
}

function geodir_wpi_notice_edit_package( $message, $item_ID ) {
    if ( get_post_meta( $item_ID, '_wpinv_type', true ) == 'package' && $package_id = get_post_meta( $item_ID, '_wpinv_custom_id', true ) ) {
        return wp_sprintf( __( 'GD price package can be edited from %sGeoDirectory > Price and Payments > Prices%s', 'geodir_payments' ), '<a href="' . admin_url('admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager&gd_pagetype=addeditprice&id=' . $package_id ) . '">', '</a>' );
    }

    return $message;
}

function geodir_wpi_is_gd_post_type( $post_type ) {
    global $gd_is_post_type;

    if ( empty( $post_type ) ) {
        return false;
    }

    if ( strpos( $post_type, 'gd_' ) !== 0 ) {
        return false;
    }

    if ( !empty( $gd_is_post_type ) && !empty( $gd_is_post_type[ $post_type ] ) ) {
        return true;
    }

    $gd_posttypes = geodir_get_posttypes();

    if ( !empty( $gd_posttypes ) && in_array( $post_type, $gd_posttypes ) ) {
        if ( !is_array( $gd_is_post_type ) ) {
            $gd_is_post_type = array();
        }

        $gd_is_post_type[ $post_type ] = true;
        
        return true;
    }

    return false;
}

function geodir_wpi_geodir_integration() {
    if ( !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        // Add  fields for force upgrade
        if ( defined( 'INVOICE_TABLE' ) && !get_option( 'geodir_wpi_gdp_column' ) ) {
            geodir_add_column_if_not_exist( INVOICE_TABLE, 'invoice_id', 'INT( 11 ) NOT NULL DEFAULT 0' );

            update_option( 'geodir_wpi_gdp_column', '1' );
        }

        // Merge price packages
        geodir_wpi_merge_packages_to_items();
    }
}

function geodir_wpi_get_package_type( $item_types ) {
    $item_types['package'] = __( 'Package', 'geodir_payments' );
        
    return $item_types;
}

function geodir_wpi_update_package_item($package_id) {
    return geodir_wpi_merge_package_to_item($package_id, true);
}

function geodir_wpi_merge_packages_to_items( $force = false ) {    
    if ( $merged = get_option( 'geodir_wpi_merge_packages' ) && !$force ) {
        return true;
    }

    $packages = geodir_package_list_info();
    
    foreach ( $packages as $key => $package ) {
        geodir_wpi_merge_package_to_item( $package->pid, $force, $package );
    }

    if ( !$merged ) {
        update_option( 'geodir_wpi_merge_packages', 1 );
    }

    return true;
}

function geodir_wpi_get_package_item( $package_id, $create = false ) {
    $item = wpinv_get_item_by( 'custom_id', $package_id, 'package' );

    if ( !$create ) {
        return $item;
    }

    return geodir_wpi_merge_package_to_item( $package_id, true );
}

function geodir_wpi_merge_package_to_item( $package_id, $force = false, $package = NULL) {
    if ( empty( $package_id ) ) {
        return false;
    }
    
    if ( empty( $package ) ) {
        $package = geodir_get_package_info_by_id( $package_id, '' );
    }

    if ( !( !empty( $package->post_type ) && geodir_wpi_is_gd_post_type( $package->post_type ) ) ) {
        return false;
    }

    $data = array(
        'type'                 => 'package',
        'title'                => $package->title,
        'custom_id'            => $package_id,
        'price'                => wpinv_round_amount( $package->amount ),
        'status'               => $package->status == 1 ? 'publish' : 'pending',
        'custom_name'          => get_post_type_plural_label( $package->post_type ),
        'custom_singular_name' => get_post_type_singular_label( $package->post_type ),
        'vat_rule'             => 'digital',
        'vat_class'            => '_standard',
        'editable'             => false,
        'excerpt'              => $package->title_desc,
    );
    
    if ( !empty( $package->sub_active ) ) {
        $sub_num_trial_days = absint( $package->sub_num_trial_days );
        
        $data['is_recurring']       = 1;
        $data['recurring_period']   = $package->sub_units;
        $data['recurring_interval'] = absint( $package->sub_units_num );
        $data['recurring_limit']    = absint( $package->sub_units_num_times );
        $data['free_trial']         = $sub_num_trial_days > 0 ? 1 : 0;
        $data['trial_period']       = $package->sub_num_trial_units;
        $data['trial_interval']     = $sub_num_trial_days;
    } else {
        $data['is_recurring']       = 0;
        $data['recurring_period']   = '';
        $data['recurring_interval'] = '';
        $data['recurring_limit']    = '';
        $data['free_trial']         = 0;
        $data['trial_period']       = '';
        $data['trial_interval']     = '';
    }

    return wpinv_create_item( $data, false, $force );
}

function geodir_wpi_gdp_to_wpi_gateway( $payment_method ) {
    switch( $payment_method ) {
        case 'prebanktransfer':
            $gateway = 'bank_transfer';
        break;
        default:
            $gateway = empty( $payment_method ) ? 'manual' : $payment_method;
        break;
    }
    
    return apply_filters( 'geodir_wpi_gdp_to_wpi_gateway', $gateway, $payment_method );
}

function geodir_wpi_gdp_to_wpi_gateway_title( $payment_method ) {
    $gateway = geodir_wpi_gdp_to_wpi_gateway( $payment_method );
    
    $gateway_title = wpinv_get_gateway_checkout_label( $gateway );
    
    if ( $gateway == $gateway_title ) {
        $gateway_title = geodir_payment_method_title( $gateway );
    }
    
    return apply_filters( 'geodir_wpi_gdp_to_wpi_gateway_title', $gateway_title, $payment_method );
}

function geodir_wpi_print_checkout_errors() {
    global $wpi_session;
    wpinv_print_errors();
}

function geodir_wpi_save_invoice( $invoice_id, $update = false, $pre_status = NULL ) {
    global $wpi_nosave, $wpi_zero_tax, $wpi_gdp_inv_merge;
    
    $invoice_info = geodir_get_invoice( $invoice_id );
    
    $wpi_invoice_id  = !empty( $invoice_info->invoice_id ) ? $invoice_info->invoice_id : 0;
    
    if (!empty($invoice_info)) {
        $wpi_invoice = $wpi_invoice_id > 0 ? wpinv_get_invoice( $wpi_invoice_id ) : NULL;
        
        if ( !empty( $wpi_invoice ) ) { // update invoice
            $save = false;
            if ($invoice_info->coupon_code !== $wpi_invoice->discount_code || (float)$invoice_info->discount < (float)$wpi_invoice->discount || (float)$invoice_info->discount > (float)$wpi_invoice->discount) {
                $save = true;
                $wpi_invoice->set('discount_code', $invoice_info->coupon_code);
                $wpi_invoice->set('discount', $invoice_info->discount);
            }
            
            if ($invoice_info->paymentmethod !== $wpi_invoice->gateway) {
                $save = true;
                $gateway = !empty( $invoice_info->paymentmethod ) ? $invoice_info->paymentmethod : '';
                $gateway = geodir_wpi_gdp_to_wpi_gateway( $gateway );
                $gateway_title = geodir_wpi_gdp_to_wpi_gateway_title( $gateway );
                $wpi_invoice->set('gateway', $gateway );
                $wpi_invoice->set('gateway_title', $gateway_title );
            }
            
            if ( ( $status = geodir_wpi_gdp_to_wpi_status( $invoice_info->status ) ) !== $wpi_invoice->status ) {
                $save = true;
                $wpi_invoice->set( 'status', $status );
            }
            
            if ($save) {
                $wpi_nosave = true;
                $wpi_invoice->recalculate_total();
                $wpi_invoice->save();
            }
            
            return $wpi_invoice;
        } else { // create invoice
            $user_info = get_userdata( $invoice_info->user_id );
            
            if ( !empty( $pre_status ) ) {
                $invoice_info->status = $pre_status;
            }
            $status = geodir_wpi_gdp_to_wpi_status( $invoice_info->status );
            
            $wpi_zero_tax = false;
            
            if ( $wpi_gdp_inv_merge && in_array( $status, array( 'publish', 'wpi-processing', 'wpi-renewal' ) ) ) {
                $wpi_zero_tax = true;
            }
            
            $invoice_data                   = array();
            $invoice_data['invoice_id']     = $wpi_invoice_id;
            $invoice_data['status']         = $status;
            $invoice_data['user_id']        = $invoice_info->user_id;
            $invoice_data['created_via']    = 'API';
            
            if ( !empty( $invoice_info->date ) ) {
                $invoice_data['created_date']   = $invoice_info->date;
            }
            
            $paymentmethod = !empty( $invoice_info->paymentmethod ) ? $invoice_info->paymentmethod : '';
            $paymentmethod = geodir_wpi_gdp_to_wpi_gateway( $paymentmethod );
            $payment_method_title = geodir_wpi_gdp_to_wpi_gateway_title( $paymentmethod );
            
            $invoice_data['payment_details'] = array( 
                'gateway'           => $paymentmethod, 
                'gateway_title'     => $payment_method_title,
                'currency'          => geodir_get_currency_type(),
            );
            
            $user_address = wpinv_get_user_address( $invoice_info->user_id, false );
            
            $invoice_data['user_info'] = array( 
                'user_id'       => $invoice_info->user_id, 
                'first_name'    => $user_address['first_name'],
                'last_name'     => $user_address['last_name'],
                'email'         => $user_address['email'],
                'company'       => $user_address['company'],
                'vat_number'    => $user_address['vat_number'],
                'phone'         => $user_address['phone'],
                'address'       => $user_address['address'],
                'city'          => $user_address['city'],
                'country'       => $user_address['country'],
                'state'         => $user_address['state'],
                'zip'           => $user_address['zip'],
                'discount'      => $invoice_info->coupon_code,
            );
            
            $invoice_data['discount']       = $invoice_info->discount;
            $invoice_data['discount_code']  = $invoice_info->coupon_code;
            
            $post_item = geodir_wpi_get_package_item( $invoice_info->package_id );

            if ( $invoice_info->invoice_type == 'add_franchise' ) {
                $custom_price = $invoice_info->amount;
            } else {
                $custom_price = '';
            }

            if ( !empty( $post_item ) ) {
                $cart_details  = array();
                $cart_details[] = array(
                    'id'            => $post_item->ID,
                    'name'          => $post_item->get_name(),
                    'item_price'    => $post_item->get_price(),
                    'custom_price'  => $custom_price,
                    'discount'      => $invoice_info->discount,
                    'tax'           => 0.00,
                    'meta'          => array( 
                        'post_id'       => $invoice_info->post_id,
                        'invoice_title' => $invoice_info->post_title
                    ),
                );
                
                $invoice_data['cart_details']  = $cart_details;
            }

            $data = array( 'invoice' => $invoice_data );

            $wpinv_api = new WPInv_API();
            $data = $wpinv_api->insert_invoice( $data );
            
            if ( is_wp_error( $data ) ) {
                wpinv_error_log( 'WPInv_Invoice: ' . $data->get_error_message() );
            } else {
                if ( !empty( $data ) ) {
                    update_post_meta( $data->ID, '_wpinv_gdp_id', $invoice_id );
                    
                    $update_data = array();
                    $update_data['tax_amount'] = $data->get_tax();
                    $update_data['paied_amount'] = $data->get_total();
                    $update_data['invoice_id'] = $data->ID;
                    
                    global $wpdb;
                    $wpdb->update( INVOICE_TABLE, $update_data, array( 'id' => $invoice_id ) );
                    
                    return $data;
                } else {
                    if ( $update ) {
                        wpinv_error_log( 'WPInv_Invoice: ' . __( 'Fail to update invoice.', 'geodir_payments' ) );
                    } else {
                        wpinv_error_log( 'WPInv_Invoice: ' . __( 'Fail to create invoice.', 'geodir_payments' ) );
                    }
                }
            }
        }
    }
    
    return false;
}

function geodir_wpi_update_invoice( $invoice_id ) {
    return geodir_wpi_save_invoice( $invoice_id, true );
}

function geodir_wpi_payment_status_changed( $invoice_id, $new_status, $old_status = 'wpi-pending', $subscription = false ) {
    $invoice_info = geodir_get_invoice( $invoice_id );
    if ( empty( $invoice_info ) ) {
        return false;
    }

    $invoice = !empty( $invoice_info->invoice_id ) ? wpinv_get_invoice( $invoice_info->invoice_id ) : NULL;
    if ( !empty( $invoice ) ) {
        $new_status = geodir_wpi_gdp_to_wpi_status($new_status);
        $invoice    = wpinv_update_payment_status( $invoice->ID, $new_status );
    } else {
        $invoice = geodir_wpi_save_invoice( $invoice_id );
    }
    
    return $invoice;
}

function geodir_wpi_transaction_details_note( $invoice_id, $html ) {
    $invoice_info = geodir_get_invoice( $invoice_id );
    if ( empty( $invoice_info ) ) {
        return false;
    }

    $wpi_invoice_id = !empty( $invoice_info->invoice_id ) ? $invoice_info->invoice_id : NULL;
    
    if ( !$wpi_invoice_id ) {
        $invoice = geodir_wpi_save_invoice( $invoice_id, false, $old_status );
        
        if ( !empty( $invoice ) ) {
            $wpi_invoice_id = $invoice->ID;
        }
    }

    $invoice = wpinv_get_invoice( $wpi_invoice_id );
    
    if ( empty( $invoice ) ) {
        return false;
    }
    
    return $invoice->add_note( $html, true );
}

function geodir_wpi_gdp_to_wpi_status( $status ) {
    $inv_status = $status ? $status : 'wpi-pending';
    
    switch ( $status ) {
        case 'pending':
            $inv_status = 'wpi-pending';
        break;
        case 'confirmed':
            $inv_status = 'publish';
        break;
        case 'cancelled':
            $inv_status = 'wpi-cancelled';
        break;
        case 'failed':
            $inv_status = 'wpi-failed';
        break;
        case 'onhold':
            $inv_status = 'wpi-onhold';
        break;
        case 'refunded':
            $inv_status = 'wpi-refunded';
        break;
    }
    return $inv_status;
}

function geodir_wpi_to_gdp_status( $status ) {
    $inv_status = $status ? $status : 'pending';
    
    switch ( $status ) {
        case 'wpi-pending':
            $inv_status = 'pending';
        break;
        case 'publish':
        case 'wpi-processing':
        case 'wpi-renewal':
            $inv_status = 'confirmed';
        break;
        case 'wpi-cancelled':
            $inv_status = 'cancelled';
        break;
        case 'wpi-failed':
            $inv_status = 'failed';
        break;
        case 'wpi-onhold':
            $inv_status = 'onhold';
        break;
        case 'wpi-refunded':
            $inv_status = 'refunded';
        break;
    }
    
    return $inv_status;
}

function geodir_wpi_to_gdp_id( $invoice_id ) {
    global $wpdb;
    
    return $wpdb->get_var( $wpdb->prepare( "SELECT `id` FROM `" . INVOICE_TABLE . "` WHERE `invoice_id` = %d AND `invoice_id` > 0 ORDER BY id DESC LIMIT 1", array( (int)$invoice_id ) ) );
}

function geodir_wpi_gdp_to_wpi_id( $invoice_id ) {
    $invoice = geodir_get_invoice( $invoice_id );    
    return ( empty( $invoice->invoice_id ) ? $invoice->invoice_id : false);
}

function geodir_wpi_to_gdp_recalculate_total( $invoice, $wpi_nosave ) {
    global $wpdb;
    
    if ( !empty( $wpi_nosave ) ) {
        return;
    }
    
    $gdp_invoice_id = geodir_wpi_to_gdp_id( $invoice->ID );
    
    if ( $gdp_invoice_id > 0 ) {
        $update_data = array();
        $update_data['tax_amount']      = $invoice->tax;
        $update_data['paied_amount']    = $invoice->total;
        $update_data['discount']        = $invoice->discount;
        $update_data['coupon_code']     = $invoice->discount_code;
        
        $wpdb->update( INVOICE_TABLE, $update_data, array( 'id' => $gdp_invoice_id ) );
    }
    
    return;
}

function geodir_wpi_gdp_to_wpi_invoice( $invoice_id ) {
    $invoice = geodir_get_invoice( $invoice_id );
    if ( empty( $invoice->invoice_id ) ) {
        return false;
    }
    
    return wpinv_get_invoice( $invoice->invoice_id );
}

function geodir_wpi_payment_set_coupon_code( $status, $invoice_id, $coupon_code ) {
    $invoice = geodir_wpi_gdp_to_wpi_invoice( $invoice_id );
    if ( empty( $invoice ) ) {
        return $status;
    }

    if ( $status === 1 || $status === 0 ) {
        if ( $status === 1 ) {
            $discount = geodir_get_discount_amount( $coupon_code, $invoice->get_subtotal() );
        } else {
            $discount = '';
            $coupon_code = '';
        }
        
        $invoice->set( 'discount', $discount );
        $invoice->set( 'discount_code', $coupon_code );
        $invoice->save();
        $invoice->recalculate_total();
    }
    
    return $status;
}

function geodir_wpi_add_tools() {
    ?>
    <tr>
        <td><?php _e( 'Merge Price Packages', 'geodir_payments' ); ?></td>
        <td><p><?php _e( 'Merge GeoDirectory Payment Manager price packages to the Invoicing items.', 'geodir_payments' ); ?></p></td>
        <td><input type="button" data-tool="merge_packages" class="button-primary wpinv-tool" value="<?php esc_attr_e( 'Run', 'geodir_payments' ); ?>"></td>
    </tr>
    <tr>
        <td><?php _e( 'Merge Invoices', 'geodir_payments' ); ?></td>
        <td><p><?php _e( 'Merge GeoDirectory Payment Manager invoices to the Invoicing.', 'geodir_payments' ); ?></p></td>
        <td><input type="button" data-tool="merge_invoices" class="button-primary wpinv-tool" value="<?php esc_attr_e( 'Run', 'geodir_payments' ); ?>"></td>
    </tr>
	<tr>
        <td><?php _e( 'Fix Taxes for Merged Invoices', 'geodir_payments' ); ?></td>
        <td><p><?php _e( 'Fix taxes for NON-PAID invoices which are merged before, from GeoDirectory Payment Manager invoices to Invoicing. This will recalculate taxes for non-paid merged invoices.', 'geodir_payments' ); ?></p></td>
        <td><input type="button" data-tool="merge_fix_taxes" class="button-primary wpinv-tool" value="<?php esc_attr_e( 'Run', 'geodir_payments' ); ?>"></td>
    </tr>
    <tr>
        <td><?php _e( 'Merge Coupons', 'geodir_payments' ); ?></td>
        <td><p><?php _e( 'Merge GeoDirectory Payment Manager coupons to the Invoicing.', 'geodir_payments' ); ?></p></td>
        <td><input type="button" data-tool="merge_coupons" class="button-primary wpinv-tool" value="<?php esc_attr_e( 'Run', 'geodir_payments' ); ?>"></td>
    </tr>
    <?php
}

function geodir_wpi_tool_merge_packages() {
    $packages = geodir_package_list_info();
    
    $count = 0;
    
    if ( !empty( $packages ) ) {
        $success = true;
        
        foreach ( $packages as $key => $package ) {
            $item = wpinv_get_item_by('custom_id', $package->pid, 'package');
            if ( !empty( $item ) ) {
                continue;
            }
            
            $merged = geodir_wpi_merge_package_to_item( $package->pid, false, $package );
            
            if ( !empty( $merged ) ) {
                wpinv_error_log( 'Package merge S : ' . $package->pid );
                $count++;
            } else {
                wpinv_error_log( 'Package merge F : ' . $package->pid );
            }
        }
        
        if ( $count > 0 ) {
            $message = sprintf( _n( 'Total <b>%d</b> price package is merged successfully.', 'Total <b>%d</b> price packages are merged successfully.', $count, 'geodir_payments' ), $count );
        } else {
            $message = __( 'No price packages merged.', 'geodir_payments' );
        }
    } else {
        $success = false;
        $message = __( 'No price packages found to merge!', 'geodir_payments' );
    }
    
    $response = array();
    $response['success'] = $success;
    $response['data']['message'] = $message;
    wp_send_json( $response );
}

function geodir_wpi_tool_merge_invoices() {
    global $wpdb, $wpi_gdp_inv_merge, $wpi_tax_rates;
    
    $sql = "SELECT `gdi`.`id`, `gdi`.`date`, `gdi`.`date_updated` FROM `" . INVOICE_TABLE . "` AS gdi LEFT JOIN `" . $wpdb->posts . "` AS p ON `p`.`ID` = `gdi`.`invoice_id` AND `p`.`post_type` = 'wpi_invoice' WHERE `p`.`ID` IS NULL ORDER BY `gdi`.`id` ASC";

    $items = $wpdb->get_results( $sql );
    
    $count = 0;
    
    if ( !empty( $items ) ) {
        $success = true;
        $wpi_gdp_inv_merge = true;
        
        foreach ( $items as $item ) {
            $wpi_tax_rates = NULL;
            
            $wpdb->query( "UPDATE `" . INVOICE_TABLE . "` SET `invoice_id` = 0 WHERE id = '" . $item->id . "'" );
            
            $merged = geodir_wpi_save_invoice( $item->id );
            
            if ( !empty( $merged ) && !empty( $merged->ID ) ) {
                $count++;
                
                $post_date = !empty( $item->date ) && $item->date != '0000-00-00 00:00:00' ? $item->date : current_time( 'mysql' );
                $post_date_gmt = get_gmt_from_date( $post_date );
                $post_modified = !empty( $item->date_updated ) && $item->date_updated != '0000-00-00 00:00:00' ? $item->date_updated : $post_date;
                $post_modified_gmt = get_gmt_from_date( $post_modified );
                
                $wpdb->update( $wpdb->posts, array( 'post_date' => $post_date, 'post_date_gmt' => $post_date_gmt, 'post_modified' => $post_modified, 'post_modified_gmt' => $post_modified_gmt ), array( 'ID' => $merged->ID ) );
                
                if ( $merged->is_paid() ) {
                    update_post_meta( $merged->ID, '_wpinv_completed_date', $post_modified );
                }
                
                clean_post_cache( $merged->ID );
                
                wpinv_error_log( 'Invoice merge S : ' . $item->id . ' => ' . $merged->ID );
            } else {
                wpinv_error_log( 'Invoice merge F : ' . $item->id );
            }
        }
        
        $wpi_gdp_inv_merge = false;
        
        if ( $count > 0 ) {
            $message = sprintf( _n( 'Total <b>%d</b> invoice is merged successfully.', 'Total <b>%d</b> invoices are merged successfully.', $count, 'geodir_payments' ), $count );
        } else {
            $message = __( 'No invoices merged.', 'geodir_payments' );
        }
    } else {
        $success = false;
        $message = __( 'No invoices found to merge!', 'geodir_payments' );
    }
    
    $response = array();
    $response['success'] = $success;
    $response['data']['message'] = $message;
    wp_send_json( $response );
}

function geodir_wpi_tool_merge_coupons() {
    global $wpdb;
    
    $sql = "SELECT * FROM `" . COUPON_TABLE . "` WHERE `coupon_code` IS NOT NULL AND `coupon_code` != '' ORDER BY `cid` ASC";
    $items = $wpdb->get_results( $sql );
    $count = 0;
    
    if ( !empty( $items ) ) {
        $success = true;
        
        foreach ( $items as $item ) {
            if ( wpinv_get_discount_by_code( $item->coupon_code ) ) {
                continue;
            }
            
            $args = array(
                'post_type'   => 'wpi_discount',
                'post_title'  => $item->coupon_code,
                'post_status' => !empty( $item->status ) ? 'publish' : 'pending'
            );

            $merged = wp_insert_post( $args );
            
            $item_id = $item->cid;
            
            if ( $merged ) {
                $meta = array(
                    'code'              => $item->coupon_code,
                    'type'              => $item->discount_type != 'per' ? 'flat' : 'percent',
                    'amount'            => (float)$item->discount_amount,
                    'max_uses'          => (int)$item->usage_limit,
                    'uses'              => (int)$item->usage_count,
                );
                wpinv_store_discount( $merged, $meta, get_post( $merged ) );
                
                $count++;
                
                wpinv_error_log( 'Coupon merge S : ' . $item_id . ' => ' . $merged );
            } else {
                wpinv_error_log( 'Coupon merge F : ' . $item_id );
            }
        }
        
        if ( $count > 0 ) {
            $message = sprintf( _n( 'Total <b>%d</b> coupon is merged successfully.', 'Total <b>%d</b> coupons are merged successfully.', $count, 'geodir_payments' ), $count );
        } else {
            $message = __( 'No coupons merged.', 'geodir_payments' );
        }
    } else {
        $success = false;
        $message = __( 'No coupons found to merge!', 'geodir_payments' );
    }
    
    $response = array();
    $response['success'] = $success;
    $response['data']['message'] = $message;
    wp_send_json( $response );
}

function geodir_wpi_gdp_to_wpi_currency( $value, $option = '' ) {
    return wpinv_get_currency();
}

function geodir_wpi_gdp_to_wpi_currency_sign( $value, $option = '' ) {
    return wpinv_currency_symbol();
}

function geodir_wpi_gdp_to_wpi_display_price( $price, $amount, $display = true , $decimal_sep, $thousand_sep ) {
    if ( !$display ) {
        $price = wpinv_round_amount( $amount );
    } else {
        $price = wpinv_price( wpinv_format_amount( $amount ) );
    }
    
    return $price;
}

function geodir_wpi_gdp_to_inv_checkout_redirect( $redirect_url ) {
    $invoice_id         = geodir_payment_cart_id();
    $invoice_info       = geodir_get_invoice( $invoice_id );
    $wpi_invoice        = !empty( $invoice_info->invoice_id ) ? wpinv_get_invoice( $invoice_info->invoice_id ) : NULL;
    
    if ( !( !empty( $wpi_invoice ) && !empty( $wpi_invoice->ID ) ) ) {
        $wpi_invoice_id = geodir_wpi_save_invoice( $invoice_id );
        $wpi_invoice    = wpinv_get_invoice( $wpi_invoice_id );
    }
    
    if ( !empty( $wpi_invoice ) && !empty( $wpi_invoice->ID ) ) {
        
        // Clear cart
        geodir_payment_clear_cart();
    
        $redirect_url = $wpi_invoice->get_checkout_payment_url();
    }
    
    return $redirect_url;
}

function geodir_wpi_gdp_dashboard_invoice_history_link( $dashboard_links ) {    
    if ( get_current_user_id() ) {        
        $dashboard_links .= '<li><i class="fa fa-shopping-cart"></i><a class="gd-invoice-link" href="' . esc_url( wpinv_get_history_page_uri() ) . '">' . __( 'My Invoice History', 'geodir_payments' ) . '</a></li>';
    }

    return $dashboard_links;
}

function geodir_wpi_to_gdp_update_status( $invoice_id, $new_status, $old_status ) {
    global $wpdb;

    $invoice    = wpinv_get_invoice( $invoice_id );
    if ( empty( $invoice ) ) {
        return false;
    }
    
    remove_action( 'geodir_payment_invoice_status_changed', 'geodir_wpi_payment_status_changed', 11, 4 );

    $invoice_id = geodir_wpi_to_gdp_id( $invoice_id );
    $new_status = geodir_wpi_to_gdp_status( $new_status );

    if ( empty( $invoice_id ) && !empty( $invoice->parent_invoice ) && $item = $invoice->get_recurring( true ) ) {
        if ( $item->get_type() == 'package' && $parent_invoice_id = geodir_wpi_to_gdp_id( $invoice->parent_invoice ) ) {
            $geodir_invoice = geodir_get_invoice( $parent_invoice_id );
            if ( !empty( $geodir_invoice ) ) {
                $parent_invoice = wpinv_get_invoice( $invoice->parent_invoice );
                unset( $geodir_invoice->id );
                $data = (array)$geodir_invoice;
                $data['invoice_id'] = $invoice->ID;
                $data['tax_amount'] = $invoice->get_tax();
                $data['paied_amount'] = $invoice->get_total();
                $data['discount'] = $invoice->get_discount();
                $data['coupon_code'] = $invoice->get_discount_code();
                $data['date'] = $invoice->get_invoice_date( false );
                if ( !empty( $parent_invoice ) && $alive_days = wpinv_period_in_days( $parent_invoice->get_subscription_interval(), $parent_invoice->get_subscription_period() ) ) {
                    $data['alive_days'] = $alive_days;
                }
                if ( !empty( $data['alive_days'] ) ) {
                    $data['expire_date'] = date_i18n( 'Y-m-d', strtotime( $data['date'] . "+" . $data['alive_days'] . " days" ) );
                }

                if ( $wpdb->insert( INVOICE_TABLE, $data ) ) {
                    $invoice_id = (int)$wpdb->insert_id;
                    update_post_meta( $invoice->ID, '_wpinv_gdp_id', $invoice_id );
                }
            }
        }
    }
    
    if ( !empty( $invoice_id ) ) {
        geodir_update_invoice_status( $invoice_id, $new_status, $invoice->is_recurring() );
    }
}

function geodir_wpi_gdp_to_wpi_delete_package( $gd_package_id ) {
    $item = wpinv_get_item_by( 'custom_id', $gd_package_id, 'package' );
    
    if ( !empty( $item ) ) {
        wpinv_remove_item( $item, true );
    }
}

function geodir_wpi_can_delete_package_item( $return, $post_id ) {
    if ( $return && get_post_meta( $post_id, '_wpinv_type', true ) == 'package' && $package_id = get_post_meta( $post_id, '_wpinv_custom_id', true ) ) {
        $gd_package = geodir_get_package_info_by_id( $package_id, '' );
        
        if ( !empty( $gd_package ) ) {
            $return = false;
        }
    }

    return $return;
}

function geodir_wpi_package_item_classes( $classes, $class, $post_id ) {
    global $typenow;

    if ( $typenow == 'wpi_item' && in_array( 'wpi-type-package', $classes ) ) {
        if ( wpinv_item_in_use( $post_id ) ) {
            $classes[] = 'wpi-inuse-pkg';
        } else if ( !( get_post_meta( $post_id, '_wpinv_type', true ) == 'package' && geodir_get_package_info_by_id( (int)get_post_meta( $post_id, '_wpinv_custom_id', true ), '' ) ) ) {
            $classes[] = 'wpi-delete-pkg';
        }
    }

    return $classes;
}

function geodir_wpi_gdp_package_type_info( $post ) {
    ?><p class="wpi-m0"><?php _e( 'Package: GeoDirectory price packages items.', 'geodir_payments' );?></p><?php
}

function geodir_wpi_gdp_to_gdi_set_zero_tax( $is_taxable, $item_id, $country , $state ) {
    global $wpi_zero_tax;

    if ( $wpi_zero_tax ) {
        $is_taxable = false;
    }

    return $is_taxable;
}

function geodir_wpi_tool_merge_fix_taxes() {
    global $wpdb;

    $sql = "SELECT DISTINCT p.ID FROM `" . $wpdb->posts . "` AS p LEFT JOIN " . $wpdb->postmeta . " AS pm ON pm.post_id = p.ID WHERE p.post_type = 'wpi_item' AND pm.meta_key = '_wpinv_type' AND pm.meta_value = 'package'";
    $items = $wpdb->get_results( $sql );

    if ( !empty( $items ) ) {
        foreach ( $items as $item ) {
            if ( get_post_meta( $item->ID, '_wpinv_vat_class', true ) == '_exempt' ) {
                update_post_meta( $item->ID, '_wpinv_vat_class', '_standard' );
            }
        }
    }
        
    $sql = "SELECT `p`.`ID`, gdi.id AS gdp_id FROM `" . INVOICE_TABLE . "` AS gdi LEFT JOIN `" . $wpdb->posts . "` AS p ON `p`.`ID` = `gdi`.`invoice_id` AND `p`.`post_type` = 'wpi_invoice' WHERE `p`.`ID` IS NOT NULL AND p.post_status NOT IN( 'publish', 'wpi-processing', 'wpi-renewal' ) ORDER BY `gdi`.`id` ASC";
    $items = $wpdb->get_results( $sql );

    if ( !empty( $items ) ) {
        $success = false;
        $message = __( 'Taxes fixed for non-paid merged GD invoices.', 'geodir_payments' );
        
        global $wpi_userID, $wpinv_ip_address_country, $wpi_tax_rates;
        
        foreach ( $items as $item ) {
            $wpi_tax_rates = NULL;               
            $data = wpinv_get_invoice($item->ID);

            if ( empty( $data ) ) {
                continue;
            }
            
            $checkout_session = wpinv_get_checkout_session();
            
            $data_session                   = array();
            $data_session['invoice_id']     = $data->ID;
            $data_session['cart_discounts'] = $data->get_discounts( true );
            
            wpinv_set_checkout_session( $data_session );
            
            $wpi_userID         = (int)$data->get_user_id();
            $_POST['country']   = !empty($data->country) ? $data->country : wpinv_get_default_country();
                
            $data->country      = sanitize_text_field( $_POST['country'] );
            $data->set( 'country', sanitize_text_field( $_POST['country'] ) );
            
            $wpinv_ip_address_country = $data->country;
            
            $data->recalculate_totals(true);
            
            wpinv_set_checkout_session( $checkout_session );
            
            $update_data = array();
            $update_data['tax_amount'] = $data->get_tax();
            $update_data['paied_amount'] = $data->get_total();
            $update_data['invoice_id'] = $data->ID;
            
            $wpdb->update( INVOICE_TABLE, $update_data, array( 'id' => $item->gdp_id ) );
        }
    } else {
        $success = false;
        $message = __( 'No invoices found to fix taxes!', 'geodir_payments' );
    }

    $response = array();
    $response['success'] = $success;
    $response['data']['message'] = $message;
    wp_send_json( $response );
}

function geodir_wpi_to_gdp_handle_subscription_cancel( $invoice_id, $invoice ) {
    if ( !empty( $invoice ) && $invoice->is_recurring() ) {
        if ( $invoice->is_renewal() ) {
            $invoice = $invoice->get_parent_payment();
        }

        // Handle listings on subscription cancelled
        geodir_wpi_to_gdp_subscription_ended( $invoice );
    }
}

function geodir_wpi_to_gdp_subscription_ended( $invoice ) {
    if ( empty( $invoice ) ) {
        return false;
    }
    
    if ( is_int( $invoice ) ) {
        $invoice = new WPInv_Invoice( $invoice );
    }
        
    if ( !empty( $invoice->ID ) && ( $item_ID = $invoice->get_recurring() ) ) {
        if ( $invoice->is_renewal() ) {
            $invoice = $invoice->get_parent_payment();
        }

        $gd_invoice_id = geodir_wpi_to_gdp_id( $invoice->ID );
        $gd_invoice = geodir_get_invoice( $gd_invoice_id );

        if ( !empty( $gd_invoice->post_id ) && get_post_meta( $item_ID, '_wpinv_type', true ) == 'package' && $gd_invoice->package_id = get_post_meta( $item_ID, '_wpinv_custom_id', true ) ) {
            $post_id = $gd_invoice->post_id;

            if ( get_option('geodir_listing_expiry') ) {
                global $gd_force_to_expire, $gd_posts_to_expire;

                $gd_force_to_expire = array( $post_id );
                $gd_posts_to_expire = array( $post_id );

                geodir_expire_check();

                $gd_force_to_expire = NULL;
                $gd_posts_to_expire = NULL;
            } else {
                geodir_set_post_status( $post_id, 'draft' );
            }
        }
    }
}

function geodir_wpi_cart_line_item_summary( $summary, $cart_item, $wpi_item, $invoice ) {
    if ( !empty( $wpi_item ) && !empty( $cart_item['meta']['post_id'] ) && $wpi_item->get_type() == 'package' ) {
        $post_link = !empty( $cart_item['meta']['invoice_title'] ) ? $cart_item['meta']['invoice_title'] : get_the_title( $cart_item['meta']['post_id'] );
        $summary = wp_sprintf( __( '%s: %s', 'geodir_payments' ), $wpi_item->get_custom_singular_name(), $post_link );
        $summary = '<small class="meta">' . wpautop( wp_kses_post( $summary ) ) . '</small>';
    }
    
    return $summary;
}

function geodir_wpi_email_line_item_summary( $summary, $cart_item, $wpi_item, $invoice ) {
    if ( !empty( $wpi_item ) && !empty( $cart_item['meta']['post_id'] ) && $wpi_item->get_type() == 'package' ) {
        $post_link = '<a href="' . get_permalink( $cart_item['meta']['post_id'] ) .'" target="_blank">' . ( !empty($cart_item['meta']['invoice_title'] ) ? $cart_item['meta']['invoice_title'] : get_the_title( $cart_item['meta']['post_id']) ) . '</a>';
        $summary = wp_sprintf( __( '%s: %s', 'geodir_payments' ), $wpi_item->get_custom_singular_name(), $post_link );
    }

    return $summary;
}

function geodir_wpi_admin_line_item_summary( $summary, $cart_item, $wpi_item, $invoice ) {
    if ( !empty( $wpi_item ) && !empty( $cart_item['meta']['post_id'] ) && $wpi_item->get_type() == 'package' ) {
        $post_link = '<a href="' . get_edit_post_link( $cart_item['meta']['post_id'] ) .'" target="_blank">' . (!empty($cart_item['meta']['invoice_title']) ? $cart_item['meta']['invoice_title'] : get_the_title( $cart_item['meta']['post_id']) ) . '</a>';
        $summary = wp_sprintf( __( '%s: %s', 'geodir_payments' ), $wpi_item->get_custom_singular_name(), $post_link );
    }

    return $summary;
}

function geodir_wpi_print_line_item_summary( $summary, $cart_item, $wpi_item, $invoice ) {
    if ( !empty( $wpi_item ) && !empty( $cart_item['meta']['post_id'] ) && $wpi_item->get_type() == 'package' ) {
        $title = !empty( $cart_item['meta']['invoice_title'] ) ? $cart_item['meta']['invoice_title'] : get_the_title( $cart_item['meta']['post_id'] );
        $summary = wp_sprintf( __( '%s: %s', 'geodir_payments' ), $wpi_item->get_custom_singular_name(), $title );
    }
    
    return $summary;
}

function wpinv_wpi_prices_price_note( $item ) {
    if ( !empty( $item ) && $item->get_type() == 'package' ) { ?>
        <span class="description"><?php _e( 'GD package item price can be edited only from GD payment manager.', 'geodir_payments' ); ?></span>
    <?php }
}

function geodir_wpi_skip_save_package_price( $return, $field, $post_id ) {
    if ( !empty( $post_id ) && $field == '_wpinv_price' && get_post_meta( $post_id, '_wpinv_type', true ) == 'package' ) {
        $return = false;
    }

    return $return;
}

function geodir_wpi_remove_package_for_quick_add_item( $item_types, $post = array() ) {
    if ( isset( $item_types['package'] ) ) {
        unset( $item_types['package'] );
    }
        
    return $item_types;
}

function geodir_wpi_item_dropdown_hide_packages( $item_args, $args, $defaults ) {
    if ( !empty( $args['name'] ) && $args['name'] == 'wpinv_invoice_item' ) {
        $item_args['meta_query'] = array(
            array(
                'key'       => '_wpinv_type',
                'compare'   => '!=',
                'value'     => 'package'
            ),
        );
    }

    return $item_args;
}

function geodir_wpi_google_map_places_params( $extra ) {
    if ( wpinv_is_checkout() && strpos( $extra, 'libraries=places' ) === false ) {
        $extra .= "&amp;libraries=places";
    }

    return $extra;
}

function geodir_wpi_set_google_map_api_key( $value, $key, $default ) {
    if ( empty( $value ) && $api_key = get_option( 'geodir_google_api_key' ) ){
        $value = $api_key;
        wpinv_update_option( 'address_autofill_api', $api_key );
    }

    return $value;
}

/**
 * Register meta box to create invoice for listing.
 *
 * @since 2.0.32
 *
 * @param string  $post_type Post type.
 * @param WP_Post $post      Post object.
 */
function geodir_wpi_register_meta_box_create_invoice( $post_type, $post ) {
    if ( geodir_wpi_is_gd_post_type( $post_type ) ) {
        $add_meta_box = apply_filters( 'geodir_wpi_add_meta_box_create_invoice', current_user_can( 'manage_options' ), $post_type, $post );

        if ( $add_meta_box && geodir_wpi_allow_invoice_for_listing( $post->ID ) ) {
            add_meta_box( 'gd-wpi-create-invoice', __( 'Invoice for Listing' ), 'geodir_wpi_display_meta_box_create_invoice', $post_type, 'side', 'high' );
        }
    }
}

/**
 * 
 *
 * @since 2.0.32
 *
 * @param  WP_Post $post      Post object.
 * @return bool
 */
function geodir_wpi_allow_invoice_for_listing( $post_ID ) {
    $return = in_array( get_post_status( $post_ID ), array( 'draft' ) ) ? true : false;

    if ( $return ) {
        $gd_post = geodir_get_post_info( $post_ID );

        if ( empty( $gd_post->package_id ) ) {
            $return = false;
        }

        // WPML
        if ( $return && geodir_is_wpml() ) {
            if ( geodir_wpml_is_post_type_translated( $gd_post->post_type ) && get_post_meta( $post_ID, '_icl_lang_duplicate_of', true ) ) {
                $return = false; // Do not allow to create invoice for duplicated listing.
            }
        }

        // Franchise
        if ( $return && function_exists( 'geodir_franchise_enabled' ) && geodir_franchise_enabled( $gd_post->post_type ) ) {
            $parent_ID = geodir_franchise_main_franchise_id( $post_ID );

            if ( $parent_ID && $parent_ID != $post_ID ) {
                $return = false; // Do not allow to create invoice for franchise listings.
            }
        }
    }

    return apply_filters( 'geodir_wpi_allow_invoice_for_listing', $return, $post_ID );
}

/**
 * 
 *
 * @since 2.0.32
 *
 * @param  WP_Post $post      Post object.
 * @return bool
 */
function geodir_wpi_display_meta_box_create_invoice( $post ) {
    $package_id = geodir_get_post_meta( $post->ID, 'package_id', true );
    $package_info = geodir_get_package_info( $package_id );

    if ( empty( $package_info ) ) {
        return;
    }
    $package_title = strip_tags( __( stripslashes_deep( $package_info->title_desc ), 'geodirectory' ) );
    ?>
    <p><?php echo wp_sprintf( __( 'Create a new invoice for this listing with a package <b>%s</b>. Invoice will be created with Pending Payment status.', 'geodir_payments' ), $package_title ); ?></p>
    <div id="gd-btn-action">
        <span class="spinner"></span>
        <input id="gd_wpi_create" data-id="<?php echo $post->ID; ?>" class="button button-primary button-large" value="<?php _e( 'Create Invoice for this Listing', 'geodir_payments' ); ?>" type="button">
    </div>
    <?php
}

/**
 * 
 *
 * @since 2.0.32
 *
 */
function geodir_wpi_create_invoice() {
    global $wpdb;
    
    check_ajax_referer( 'wpinv-nonce', '_nonce' );

    if ( ! current_user_can( 'manage_options' ) || empty( $_POST['_id'] ) ) {
        die( -1 );
    }

    $post_id = absint( $_POST['_id'] );

    $json            = array();
    $json['success'] = false;
    
    if ( $post_id > 0 ) {
        $json['success'] = false;
        if ( geodir_wpi_allow_invoice_for_listing( $post_id ) ) {
            $gd_post = geodir_get_post_info( $post_id );
            $package_info = $gd_post->package_id ? geodir_get_package_info( $gd_post->package_id ) : NULL;
            
            if ( !empty( $package_info->pid ) ) {
                $package_id = $package_info->pid;
                
                $item = wpinv_get_item_by( 'custom_id', $package_id, 'package' );

                if ( !empty( $item->ID ) ) {
                    $invoice_title = wp_sprintf(  __( 'Add Listing: %s', 'geodir_payments' ), get_the_title( $post_id ) );

                    $data = array(
                        'status'        => 'wpi-pending',
                        'user_id'       => $gd_post->post_author,
                        'cart_details'  => array(
                            array(
                                'id'    => $item->ID,
                                'meta'  => array( 
                                    'post_id'       => $post_id,
                                    'invoice_title' => $invoice_title
                                ),
                            ),
                        )
                    );

                    $invoice = wpinv_insert_invoice( $data, true );

                    if ( !is_wp_error( $invoice ) && !empty( $invoice->ID ) ) {
                        $amount = $invoice->get_total();
                        
                        $save_data = array();
                        $save_data['invoice_id'] = $invoice->ID;
                        $save_data['type'] = $amount > 0 ? 'paid' : 'free';
                        $save_data['post_id'] = $post_id;
                        $save_data['post_title'] = $invoice_title;
                        $save_data['post_action'] = 'add';
                        $save_data['invoice_type'] = 'add_listing';
                        $save_data['invoice_callback'] = 'add_listing';
                        $save_data['invoice_data'] = maybe_serialize( array() );
                        $save_data['package_id'] = $package_info->pid;
                        $save_data['package_title'] = __( stripslashes_deep( $package_info->title ), 'geodirectory' );
                        $save_data['amount'] = $package_info->amount;
                        $save_data['alive_days'] = geodir_payment_package_alive_days( $package_id );
                        if ( !empty( $save_data['alive_days'] ) ) {
                            $save_data['expire_date'] = date_i18n( 'Y-m-d', strtotime( date_i18n( 'Y-m-d' ) . "+" . $save_data['alive_days'] . " days" ) );
                        }
                        $save_data['user_id'] = $gd_post->post_author;
                        $save_data['coupon_code'] = $invoice->get_discount_code();
                        $save_data['discount'] = $invoice->get_discount();
                        $save_data['tax_amount'] = $invoice->get_tax();
                        $save_data['paied_amount'] = $amount;
                        $save_data['status'] = 'pending';
                        $save_data['subscription'] = !empty( $package_info->sub_active ) ? 1 : 0;
                        $save_data['is_current'] = 1;
                        $save_data['date'] = date_i18n( 'Y-m-d H:i:s' );

                        if ( $wpdb->insert( INVOICE_TABLE, $save_data ) ) {
                            $invoice_id = (int)$wpdb->insert_id;
                            update_post_meta( $invoice->ID, '_wpinv_gdp_id', $invoice_id );
                        }
                
                        $json['success'] = true;
                        $json['link']    = '<a target="_blank" href="' . get_edit_post_link( $invoice->ID ) . '">' . wp_sprintf( __( 'View Invoice #%s' , 'geodir_payments' ), $invoice->get_number() ) . '</a>';
                    } else {
                        if ( is_wp_error( $invoice ) ) {
                            $json['msg'] = wp_sprintf( __( 'Fail to create invoice. No invoicing item found with the package selected.' , 'geodir_payments' ), implode( ', ', $invoice->get_error_messages() ) );
                        } else {
                            $json['msg'] = __( 'Fail to create invoice. Please refresh page and try again.' , 'geodir_payments' );
                        }
                    }
                } else {
                    $json['msg'] = __( 'Fail to create invoice. No invoicing item found with the package selected.' , 'geodir_payments' );
                }
            } else {
                $json['msg'] = __( 'Fail to create invoice. No package assigned to this listing.' , 'geodir_payments' );
            }
        } else {
            $json['msg'] = __( 'This listing is not allowed to create invoice.' , 'geodir_payments' );
        }
    }

    wp_send_json( $json );
}

function geodir_wpi_check_redirects() {
    if ( geodir_payment_is_page( 'checkout' ) ) {
        wp_redirect( wpinv_get_checkout_uri() );
        wpinv_die();
    } else if ( geodir_payment_is_page( 'invoices' ) ) {
        wp_redirect( wpinv_get_history_page_uri() );
        wpinv_die();
    }
}