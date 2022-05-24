<?php

function wp_stripe_checkout_process_webhook(){
    if(!isset($_REQUEST['wp_stripe_co_webhook']))
    {
        return;
    }
    //status_header(200);
    http_response_code(200);
    // retrieve the request's body and parse it as JSON
    $body = @file_get_contents('php://input');
    // grab the event information
    $event_json = json_decode($body);

    $allowed_events = array("checkout.session.completed"); //add event types that we want to handle
    if (!in_array($event_json->type, $allowed_events))   
    {
        return;
    }
    wp_stripe_checkout_debug_log("Received event notification from Stripe. Event type: ".$event_json->type, true);
    wp_stripe_checkout_debug_log_array($event_json, true);
    $client_reference_id = sanitize_text_field($event_json->data->object->client_reference_id);
    if(!isset($client_reference_id) || empty($client_reference_id)){
        wp_stripe_checkout_debug_log("Client Reference ID could not be found. This notification cannot be processed.", false);
        return;
    }
    if(strpos($client_reference_id, 'wpsc') === false){
        wp_stripe_checkout_debug_log("This payment was not initiated by the Stripe checkout plugin.", false);
        return;
    }
    if(strpos($client_reference_id, 'wpscprod') !== false){
        wp_stripe_checkout_process_wpsc_product_webhook($event_json);
        return;
    }
    $payment_data = array();
    $checkout_session_id = sanitize_text_field($event_json->data->object->id);
    if(!isset($checkout_session_id) || empty($checkout_session_id)){
        wp_stripe_checkout_debug_log("Checkout Session ID could not be found. This notification cannot be processed.", false);
        return;
    }
    $checkout_session = WP_SC_Stripe_API::retrieve('checkout/sessions/'.$checkout_session_id.'/line_items');
    $product_name = sanitize_text_field($checkout_session->data[0]->description);
    $payment_data['product_name'] = isset($product_name) && !empty($product_name) ? $product_name : '';
    $temp_product_price = sanitize_text_field($checkout_session->data[0]->price->unit_amount);
    $product_price = $temp_product_price/100;
    $payment_data['price'] = number_format($product_price, 2, '.', '');
    $product_quantity = sanitize_text_field($checkout_session->data[0]->quantity);
    $payment_data['quantity'] = $product_quantity;
    $stripe_price_id = sanitize_text_field($checkout_session->data[0]->price->id);
    $payment_data['price_id'] = isset($stripe_price_id) && !empty($stripe_price_id) ? $stripe_price_id : '';
    $stripe_product_id = sanitize_text_field($checkout_session->data[0]->price->product);
    $payment_data['product_id'] = isset($stripe_product_id) && !empty($stripe_product_id) ? $stripe_product_id : '';
    $currency = sanitize_text_field($event_json->data->object->currency);
    $payment_data['currency_code'] = strtoupper($currency);
    $temp_amount_total = sanitize_text_field($event_json->data->object->amount_total);
    $amount_total = $temp_amount_total/100;
    $payment_data['amount_total'] = number_format($amount_total, 2, '.', '');
    $subscription_id = sanitize_text_field($event_json->data->object->subscription);
    if(isset($subscription_id) && !empty($subscription_id)){
        $payment_data['txn_id'] = $subscription_id;
        wp_stripe_checkout_debug_log("This notification is for a subscription payment.", true);
        $payment_data['stripe_customer_id'] = sanitize_text_field($event_json->data->object->customer);
        if(!isset($payment_data['stripe_customer_id']) || empty($payment_data['stripe_customer_id'])){
            wp_stripe_checkout_debug_log("Customer ID could not be found. This notification cannot be processed.", false);
            return;
        }
        $customers = WP_SC_Stripe_API::retrieve('customers/'.$payment_data['stripe_customer_id']);
        $payment_data['customer_email'] = sanitize_email($customers->email);
        if(!isset($payment_data['customer_email']) || empty($payment_data['customer_email'])){
            wp_stripe_checkout_debug_log("Customer email could not be found. This notification cannot be processed.", false);
            return;
        }
        $subscriptions = WP_SC_Stripe_API::retrieve('subscriptions/'.$subscription_id);
        $product_id = sanitize_text_field($subscriptions->plan->product);
        if(!isset($product_id) || empty($product_id)){
            wp_stripe_checkout_debug_log("Product ID could not be found. This notification cannot be processed.", false);
            return;
        }
        $products = WP_SC_Stripe_API::retrieve('products/'.$product_id);
        $payment_data['product_name'] = sanitize_text_field($products->name);       
        $payment_method_id = sanitize_text_field($subscriptions->default_payment_method);
        if(!isset($payment_method_id) || empty($payment_method_id)){
            wp_stripe_checkout_debug_log("Payment method could not be found. This notification cannot be processed.", false);
            return;
        }
        $payment_methods = WP_SC_Stripe_API::retrieve('payment_methods/'.$payment_method_id);               
        $billing_name = $payment_methods->billing_details->name;
        $payment_data['billing_name'] = isset($billing_name) && !empty($billing_name) ? sanitize_text_field($billing_name) : '';
        $payment_data['billing_first_name'] = '';
        $payment_data['billing_last_name'] = '';
        if(!empty($payment_data['billing_name'])){
            $billing_name_parts = explode(" ", $payment_data['billing_name']);
            $payment_data['billing_first_name'] = isset($billing_name_parts[0]) && !empty($billing_name_parts[0]) ? $billing_name_parts[0] : '';
            $payment_data['billing_last_name'] = isset($billing_name_parts[1]) && !empty($billing_name_parts[1]) ? array_pop($billing_name_parts) : '';
        }
        $address_line1 = $payment_methods->billing_details->address->line1;
        $payment_data['billing_address_line1'] = isset($address_line1) && !empty($address_line1) ? sanitize_text_field($address_line1) : '';
        $address_zip = $payment_methods->billing_details->address->postal_code;
        $payment_data['billing_address_zip'] = isset($address_zip) && !empty($address_zip) ? sanitize_text_field($address_zip) : '';
        $address_state = $payment_methods->billing_details->address->state;
        $payment_data['billing_address_state'] = isset($address_state) && !empty($address_state) ? sanitize_text_field($address_state) : '';
        $address_city = $payment_methods->billing_details->address->city;
        $payment_data['billing_address_city'] = isset($address_city) && !empty($address_city) ? sanitize_text_field($address_city) : '';
        $address_country = $payment_methods->billing_details->address->country;
        $payment_data['billing_address_country'] = isset($address_country) && !empty($address_country) ? sanitize_text_field($address_country) : '';
    }
    else{
        $payment_intent_id = $event_json->data->object->payment_intent;
        if(!isset($payment_intent_id) || empty($payment_intent_id)){
            wp_stripe_checkout_debug_log("Payment Intent ID could not be found. This notification cannot be processed.", false);
            return;
        }

        $payment_intent = WP_SC_Stripe_API::retrieve('payment_intents/'.$payment_intent_id);
        if(empty($payment_data['product_name'])){
            $payment_data['product_name'] = sanitize_text_field($payment_intent->charges->data[0]->description);
        }
        $amount = sanitize_text_field($payment_intent->charges->data[0]->amount);
        $payment_data['price'] = $amount/100;
        $currency = sanitize_text_field($payment_intent->charges->data[0]->currency);
        $payment_data['currency_code'] = strtoupper($currency);

        $billing_name = $payment_intent->charges->data[0]->billing_details->name;
        $payment_data['billing_name'] = isset($billing_name) && !empty($billing_name) ? sanitize_text_field($billing_name) : '';
        $payment_data['billing_first_name'] = '';
        $payment_data['billing_last_name'] = '';
        if(!empty($payment_data['billing_name'])){
            $billing_name_parts = explode(" ", $payment_data['billing_name']);
            $payment_data['billing_first_name'] = isset($billing_name_parts[0]) && !empty($billing_name_parts[0]) ? $billing_name_parts[0] : '';
            $payment_data['billing_last_name'] = isset($billing_name_parts[1]) && !empty($billing_name_parts[1]) ? array_pop($billing_name_parts) : '';
        }
        $address_line1 = $payment_intent->charges->data[0]->billing_details->address->line1;
        $payment_data['billing_address_line1'] = isset($address_line1) && !empty($address_line1) ? sanitize_text_field($address_line1) : '';
        $address_zip = $payment_intent->charges->data[0]->billing_details->address->postal_code;
        $payment_data['billing_address_zip'] = isset($address_zip) && !empty($address_zip) ? sanitize_text_field($address_zip) : '';
        $address_state = $payment_intent->charges->data[0]->billing_details->address->state;
        $payment_data['billing_address_state'] = isset($address_state) && !empty($address_state) ? sanitize_text_field($address_state) : '';
        $address_city = $payment_intent->charges->data[0]->billing_details->address->city;
        $payment_data['billing_address_city'] = isset($address_city) && !empty($address_city) ? sanitize_text_field($address_city) : '';
        $address_country = $payment_intent->charges->data[0]->billing_details->address->country;
        $payment_data['billing_address_country'] = isset($address_country) && !empty($address_country) ? sanitize_text_field($address_country) : '';
        $customer_email = $payment_intent->charges->data[0]->billing_details->email;
        $payment_data['customer_email'] = sanitize_email($customer_email);
        $payment_data['stripe_customer_id'] = sanitize_text_field($event_json->data->object->customer);
        //process data
        $txn_id = sanitize_text_field($payment_intent->charges->data[0]->id);
        if(!isset($txn_id) || empty($txn_id)){
            $txn_id = $payment_intent_id;
        }
        $payment_data['txn_id'] = $txn_id;
    }
    //process shipping address
    if(isset($event_json->data->object->shipping)){
        $shipping_name = $event_json->data->object->shipping->name;
        $payment_data['shipping_name'] = isset($shipping_name) && !empty($shipping_name) ? sanitize_text_field($shipping_name) : '';
        $payment_data['shipping_first_name'] = '';
        $payment_data['shipping_last_name'] = '';
        if(!empty($payment_data['shipping_name'])){
            $shipping_name_parts = explode(" ", $payment_data['shipping_name']);
            $payment_data['shipping_first_name'] = isset($shipping_name_parts[0]) && !empty($shipping_name_parts[0]) ? $shipping_name_parts[0] : '';
            $payment_data['shipping_last_name'] = isset($shipping_name_parts[1]) && !empty($shipping_name_parts[1]) ? array_pop($shipping_name_parts) : '';
        }
        $shipping_address_line1 = $event_json->data->object->shipping->address->line1;
        $payment_data['shipping_address_line1'] = isset($shipping_address_line1) && !empty($shipping_address_line1) ? sanitize_text_field($shipping_address_line1) : '';
        $shipping_address_zip = $event_json->data->object->shipping->address->postal_code;
        $payment_data['shipping_address_zip'] = isset($shipping_address_zip) && !empty($shipping_address_zip) ? sanitize_text_field($shipping_address_zip) : '';
        $shipping_address_state = $event_json->data->object->shipping->address->state;
        $payment_data['shipping_address_state'] = isset($shipping_address_state) && !empty($shipping_address_state) ? sanitize_text_field($shipping_address_state) : '';
        $shipping_address_city = $event_json->data->object->shipping->address->city;
        $payment_data['shipping_address_city'] = isset($shipping_address_city) && !empty($shipping_address_city) ? sanitize_text_field($shipping_address_city) : '';
        $shipping_address_country = $event_json->data->object->shipping->address->country;
        $payment_data['shipping_address_country'] = isset($shipping_address_country) && !empty($shipping_address_country) ? sanitize_text_field($shipping_address_country) : '';
    }
    $amount_shipping = sanitize_text_field($event_json->data->object->total_details->amount_shipping);
    if(isset($amount_shipping) && is_numeric($amount_shipping)){
        $temp_shipping = $amount_shipping/100;
        $payment_data['amount_shipping'] = number_format($temp_shipping, 2, '.', '');
    }
    $args = array(
        'post_type' => 'wpstripeco_order',
        'meta_query' => array(
            array(
                'key' => '_txn_id',
                'value' => $payment_data['txn_id'],
                'compare' => '=',
            ),
        ),
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {  //a record already exists
        wp_stripe_checkout_debug_log("An order with this transaction ID already exists. This payment will not be processed.", false);
        return;
    }
    $content = '';
    $content .= '<strong>Transaction ID:</strong> '.$payment_data['txn_id'].'<br />';
    $content .= '<strong>Product name:</strong> '.$payment_data['product_name'].'<br />';
    if(!empty($payment_data['product_id'])){
        $content .= '<strong>Product ID:</strong> '.$payment_data['product_id'].'<br />'; 
    }
    if(!empty($payment_data['price_id'])){
        $content .= '<strong>Price ID:</strong> '.$payment_data['price_id'].'<br />'; 
    }
    $content .= '<strong>Amount:</strong> '.$payment_data['price'].'<br />';
    $content .= '<strong>Currency:</strong> '.$payment_data['currency_code'].'<br />';
    if(!empty($payment_data['billing_name'])){
        $content .= '<strong>Billing Name:</strong> '.$payment_data['billing_name'].'<br />';
    }
    if(!empty($payment_data['customer_email'])){
        $content .= '<strong>Email:</strong> '.$payment_data['customer_email'].'<br />'; 
    }
    if(!empty($payment_data['stripe_customer_id'])){
        $content .= '<strong>Stripe Customer ID:</strong> '.$payment_data['stripe_customer_id'].'<br />'; 
    }
    if(!empty($payment_data['billing_address_line1'])){
        $content .= '<strong>Billing Address:</strong> '.$payment_data['billing_address_line1'];
        if(!empty($payment_data['billing_address_city'])){
            $content .= ', '.$payment_data['billing_address_city'];
        }
        if(!empty($payment_data['billing_address_state'])){
            $content .= ', '.$payment_data['billing_address_state'];
        }
        if(!empty($payment_data['billing_address_zip'])){
            $content .= ', '.$payment_data['billing_address_zip'];
        }
        if(!empty($payment_data['billing_address_country'])){
            $content .= ', '.$payment_data['billing_address_country'];
        }
        $content .= '<br />';
    }
    if(!empty($payment_data['shipping_name'])){
        $content .= '<strong>Shipping Name:</strong> '.$payment_data['shipping_name'].'<br />';
    }
    if(!empty($payment_data['shipping_address_line1'])){
        $content .= '<strong>Shipping Address:</strong> '.$payment_data['shipping_address_line1'];
        if(!empty($payment_data['shipping_address_city'])){
            $content .= ', '.$payment_data['shipping_address_city'];
        }
        if(!empty($payment_data['shipping_address_state'])){
            $content .= ', '.$payment_data['shipping_address_state'];
        }
        if(!empty($payment_data['shipping_address_zip'])){
            $content .= ', '.$payment_data['shipping_address_zip'];
        }
        if(!empty($payment_data['shipping_address_country'])){
            $content .= ', '.$payment_data['shipping_address_country'];
        }
        $content .= '<br />';
    }
    $payment_data['order_id'] = '';
    $wp_stripe_checkout_order = array(
        'post_title' => 'order',
        'post_type' => 'wpstripeco_order',
        'post_content' => '',
        'post_status' => 'publish',
    );
    wp_stripe_checkout_debug_log("Updating order information", true);
    $post_id = wp_insert_post($wp_stripe_checkout_order);  //insert a new order
    $post_updated = false;
    if ($post_id > 0) {
        $post_content = $content;
        $updated_post = array(
            'ID' => $post_id,
            'post_title' => $post_id,
            'post_type' => 'wpstripeco_order',
            'post_content' => $post_content
        );
        $updated_post_id = wp_update_post($updated_post);  //update the order
        if ($updated_post_id > 0) {  //successfully updated
            $post_updated = true;
        }
    }
    //save order information
    if ($post_updated) {
        $payment_data['order_id'] = $post_id;
        update_post_meta($post_id, '_txn_id', $payment_data['txn_id']);
        update_post_meta($post_id, '_name', $payment_data['billing_name']);
        update_post_meta($post_id, '_amount', $payment_data['price']);
        update_post_meta($post_id, '_email', $payment_data['customer_email']);
        wp_stripe_checkout_debug_log("Order information updated", true);
        $email_options = wp_stripe_checkout_get_email_option();
        add_filter('wp_mail_from', 'wp_stripe_checkout_set_email_from');
        add_filter('wp_mail_from_name', 'wp_stripe_checkout_set_email_from_name');
        if(isset($email_options['purchase_email_enabled']) && !empty($email_options['purchase_email_enabled']) && !empty($payment_data['customer_email'])){
            $subject = $email_options['purchase_email_subject'];
            $type = $email_options['purchase_email_type'];
            $body = $email_options['purchase_email_body'];
            $body = wp_stripe_checkout_do_email_tags($payment_data, $body);
            if($type == "html"){
                add_filter('wp_mail_content_type', 'wp_stripe_checkout_set_html_email_content_type');
                $body = apply_filters('wp_stripe_checkout_email_body_wpautop', true) ? wpautop($body) : $body;
            }
            wp_stripe_checkout_debug_log("Sending a purchase receipt email to ".$payment_data['customer_email'], true);
            $mail_sent = wp_mail($payment_data['customer_email'], $subject, $body);
            if($type == "html"){
                remove_filter('wp_mail_content_type', 'wp_stripe_checkout_set_html_email_content_type');
            }
            if($mail_sent == true){
                wp_stripe_checkout_debug_log("Email was sent successfully by WordPress", true);
            }
            else{
                wp_stripe_checkout_debug_log("Email could not be sent by WordPress", false);
            }
        }
        if(isset($email_options['sale_notification_email_enabled']) && !empty($email_options['sale_notification_email_enabled']) && !empty($email_options['sale_notification_email_recipient'])){
            $subject = $email_options['sale_notification_email_subject'];
            $type = $email_options['sale_notification_email_type'];
            $body = $email_options['sale_notification_email_body'];
            $body = wp_stripe_checkout_do_email_tags($payment_data, $body);
            if($type == "html"){
                add_filter('wp_mail_content_type', 'wp_stripe_checkout_set_html_email_content_type');
                $body = apply_filters('wp_stripe_checkout_email_body_wpautop', true) ? wpautop($body) : $body;
            }
            wp_stripe_checkout_debug_log("Sending a sale notification email to ".$email_options['sale_notification_email_recipient'], true);
            $mail_sent = wp_mail($email_options['sale_notification_email_recipient'], $subject, $body);
            if($type == "html"){
                remove_filter('wp_mail_content_type', 'wp_stripe_checkout_set_html_email_content_type');
            }
            if($mail_sent == true){
                wp_stripe_checkout_debug_log("Email was sent successfully by WordPress", true);
            }
            else{
                wp_stripe_checkout_debug_log("Email could not be sent by WordPress", false);
            }
        }
        remove_filter('wp_mail_from', 'wp_stripe_checkout_set_email_from');
        remove_filter('wp_mail_from_name', 'wp_stripe_checkout_set_email_from_name');      
        do_action('wpstripecheckout_order_processed', $post_id);
    } else {
        wp_stripe_checkout_debug_log("Order information could not be updated", false);
        return;
    }
    wp_stripe_checkout_debug_log("Order processing completed", true, true);
    do_action('wpstripecheckout_payment_completed', $payment_data);
}

function wp_stripe_checkout_process_wpsc_product_webhook($event_json){
    wp_stripe_checkout_debug_log("wpsc product webhook handler.", true);
    $payment_data = array();
    $checkout_session_id = sanitize_text_field($event_json->data->object->id);
    if(!isset($checkout_session_id) || empty($checkout_session_id)){
        wp_stripe_checkout_debug_log("Checkout Session ID could not be found. This notification cannot be processed.", false);
        return;
    }
    $client_reference_id = sanitize_text_field($event_json->data->object->client_reference_id);
    $reference_id_array = explode('-', $client_reference_id);
    if(!isset($reference_id_array[1]) || empty($reference_id_array[1])){
        wp_stripe_checkout_debug_log("Product ID could not be found. This notification cannot be processed.", false);
        return;
    }
    $post_id = $reference_id_array[1];
    $post = get_post($post_id);
    if (!$post) {
        wp_stripe_checkout_debug_log("Invalid product ID. This notification cannot be processed.", false);
        return;
    }
    if('wpstripeco_product' != $post->post_type){
        wp_stripe_checkout_debug_log("Invalid product type. This notification cannot be processed.", false);
        return;
    }
    $options = wp_stripe_checkout_get_option();
    $checkout_session = WP_SC_Stripe_API::retrieve('checkout/sessions/'.$checkout_session_id.'/line_items');
    $product_name = sanitize_text_field($post->post_title); //sanitize_text_field($checkout_session->data[0]->description);
    $temp_product_price = sanitize_text_field($checkout_session->data[0]->price->unit_amount); //sanitize_text_field(get_post_meta($post_id, '_wpstripeco_product_price', true));
    $product_price = $temp_product_price/100;
    $product_quantity = sanitize_text_field($checkout_session->data[0]->quantity);
    $currency_code = sanitize_text_field($options['stripe_currency_code']);
    $payment_data['product_name'] = isset($product_name) && !empty($product_name) ? $product_name : '';
    $payment_data['price'] = number_format($product_price, 2, '.', '');
    $payment_data['quantity'] = $product_quantity;
    $payment_data['product_id'] = $post_id;
    $payment_data['currency_code'] = $currency_code;
    $temp_amount_total = sanitize_text_field($event_json->data->object->amount_total);
    $amount_total = $temp_amount_total/100;
    $payment_data['amount_total'] = number_format($amount_total, 2, '.', '');
    $subscription_id = sanitize_text_field($event_json->data->object->subscription);
    if(isset($subscription_id) && !empty($subscription_id)){
        $payment_data['txn_id'] = $subscription_id;
        wp_stripe_checkout_debug_log("This notification is for a subscription payment.", true);
        $payment_data['stripe_customer_id'] = sanitize_text_field($event_json->data->object->customer);
        if(!isset($payment_data['stripe_customer_id']) || empty($payment_data['stripe_customer_id'])){
            wp_stripe_checkout_debug_log("Customer ID could not be found. This notification cannot be processed.", false);
            return;
        }
        $customers = WP_SC_Stripe_API::retrieve('customers/'.$payment_data['stripe_customer_id']);
        $payment_data['customer_email'] = sanitize_email($customers->email);
        if(!isset($payment_data['customer_email']) || empty($payment_data['customer_email'])){
            wp_stripe_checkout_debug_log("Customer email could not be found. This notification cannot be processed.", false);
            return;
        }
        $subscriptions = WP_SC_Stripe_API::retrieve('subscriptions/'.$subscription_id);
        $stripe_product_id = sanitize_text_field($subscriptions->plan->product);
        if(!isset($stripe_product_id) || empty($stripe_product_id)){
            wp_stripe_checkout_debug_log("Stripe Product ID could not be found. This notification cannot be processed.", false);
            return;
        }
        $products = WP_SC_Stripe_API::retrieve('products/'.$stripe_product_id);
        //$payment_data['product_name'] = sanitize_text_field($products->name);
        $amount_total = sanitize_text_field($event_json->data->object->amount_total);
        $amount = $amount_total/100;
        $payment_data['amount_total'] = $amount;
        /*
        if($amount != $product_price){
            wp_stripe_checkout_debug_log("Stripe Product ID could not be found. This notification cannot be processed.", false);
            return;
        }*/
        //$payment_data['price'] = $amount;
        //$currency = sanitize_text_field($event_json->data->object->currency);
        //$payment_data['currency_code'] = strtoupper($currency);
        
        $payment_method_id = sanitize_text_field($subscriptions->default_payment_method);
        if(!isset($payment_method_id) || empty($payment_method_id)){
            wp_stripe_checkout_debug_log("Payment method could not be found. This notification cannot be processed.", false);
            return;
        }
        $payment_methods = WP_SC_Stripe_API::retrieve('payment_methods/'.$payment_method_id);               
        $billing_name = $payment_methods->billing_details->name;
        $payment_data['billing_name'] = isset($billing_name) && !empty($billing_name) ? sanitize_text_field($billing_name) : '';
        $payment_data['billing_first_name'] = '';
        $payment_data['billing_last_name'] = '';
        if(!empty($payment_data['billing_name'])){
            $billing_name_parts = explode(" ", $payment_data['billing_name']);
            $payment_data['billing_first_name'] = isset($billing_name_parts[0]) && !empty($billing_name_parts[0]) ? $billing_name_parts[0] : '';
            $payment_data['billing_last_name'] = isset($billing_name_parts[1]) && !empty($billing_name_parts[1]) ? array_pop($billing_name_parts) : '';
        }
        $address_line1 = $payment_methods->billing_details->address->line1;
        $payment_data['billing_address_line1'] = isset($address_line1) && !empty($address_line1) ? sanitize_text_field($address_line1) : '';
        $address_zip = $payment_methods->billing_details->address->postal_code;
        $payment_data['billing_address_zip'] = isset($address_zip) && !empty($address_zip) ? sanitize_text_field($address_zip) : '';
        $address_state = $payment_methods->billing_details->address->state;
        $payment_data['billing_address_state'] = isset($address_state) && !empty($address_state) ? sanitize_text_field($address_state) : '';
        $address_city = $payment_methods->billing_details->address->city;
        $payment_data['billing_address_city'] = isset($address_city) && !empty($address_city) ? sanitize_text_field($address_city) : '';
        $address_country = $payment_methods->billing_details->address->country;
        $payment_data['billing_address_country'] = isset($address_country) && !empty($address_country) ? sanitize_text_field($address_country) : '';
    }
    else{
        $payment_intent_id = $event_json->data->object->payment_intent;
        if(!isset($payment_intent_id) || empty($payment_intent_id)){
            wp_stripe_checkout_debug_log("Payment Intent ID could not be found. This notification cannot be processed.", false);
            return;
        }

        $payment_intent = WP_SC_Stripe_API::retrieve('payment_intents/'.$payment_intent_id);
        /*
        if(empty($payment_data['product_name'])){
            $payment_data['product_name'] = sanitize_text_field($payment_intent->charges->data[0]->description);
        }
        $amount = sanitize_text_field($payment_intent->charges->data[0]->amount);
        $payment_data['price'] = $amount/100;
        $currency = sanitize_text_field($payment_intent->charges->data[0]->currency);
        $payment_data['currency_code'] = strtoupper($currency);
        */
        $billing_name = $payment_intent->charges->data[0]->billing_details->name;
        $payment_data['billing_name'] = isset($billing_name) && !empty($billing_name) ? sanitize_text_field($billing_name) : '';
        $payment_data['billing_first_name'] = '';
        $payment_data['billing_last_name'] = '';
        if(!empty($payment_data['billing_name'])){
            $billing_name_parts = explode(" ", $payment_data['billing_name']);
            $payment_data['billing_first_name'] = isset($billing_name_parts[0]) && !empty($billing_name_parts[0]) ? $billing_name_parts[0] : '';
            $payment_data['billing_last_name'] = isset($billing_name_parts[1]) && !empty($billing_name_parts[1]) ? array_pop($billing_name_parts) : '';
        }
        $address_line1 = $payment_intent->charges->data[0]->billing_details->address->line1;
        $payment_data['billing_address_line1'] = isset($address_line1) && !empty($address_line1) ? sanitize_text_field($address_line1) : '';
        $address_zip = $payment_intent->charges->data[0]->billing_details->address->postal_code;
        $payment_data['billing_address_zip'] = isset($address_zip) && !empty($address_zip) ? sanitize_text_field($address_zip) : '';
        $address_state = $payment_intent->charges->data[0]->billing_details->address->state;
        $payment_data['billing_address_state'] = isset($address_state) && !empty($address_state) ? sanitize_text_field($address_state) : '';
        $address_city = $payment_intent->charges->data[0]->billing_details->address->city;
        $payment_data['billing_address_city'] = isset($address_city) && !empty($address_city) ? sanitize_text_field($address_city) : '';
        $address_country = $payment_intent->charges->data[0]->billing_details->address->country;
        $payment_data['billing_address_country'] = isset($address_country) && !empty($address_country) ? sanitize_text_field($address_country) : '';
        $customer_email = $payment_intent->charges->data[0]->billing_details->email;
        $payment_data['customer_email'] = sanitize_email($customer_email);
        $payment_data['stripe_customer_id'] = sanitize_text_field($event_json->data->object->customer);
        //process data
        $txn_id = sanitize_text_field($payment_intent->charges->data[0]->id);
        if(!isset($txn_id) || empty($txn_id)){
            $txn_id = $payment_intent_id;
        }
        $payment_data['txn_id'] = $txn_id;
    }
    //process shipping address
    if(isset($event_json->data->object->shipping)){
        $shipping_name = $event_json->data->object->shipping->name;
        $payment_data['shipping_name'] = isset($shipping_name) && !empty($shipping_name) ? sanitize_text_field($shipping_name) : '';
        $payment_data['shipping_first_name'] = '';
        $payment_data['shipping_last_name'] = '';
        if(!empty($payment_data['shipping_name'])){
            $shipping_name_parts = explode(" ", $payment_data['shipping_name']);
            $payment_data['shipping_first_name'] = isset($shipping_name_parts[0]) && !empty($shipping_name_parts[0]) ? $shipping_name_parts[0] : '';
            $payment_data['shipping_last_name'] = isset($shipping_name_parts[1]) && !empty($shipping_name_parts[1]) ? array_pop($shipping_name_parts) : '';
        }
        $shipping_address_line1 = $event_json->data->object->shipping->address->line1;
        $payment_data['shipping_address_line1'] = isset($shipping_address_line1) && !empty($shipping_address_line1) ? sanitize_text_field($shipping_address_line1) : '';
        $shipping_address_zip = $event_json->data->object->shipping->address->postal_code;
        $payment_data['shipping_address_zip'] = isset($shipping_address_zip) && !empty($shipping_address_zip) ? sanitize_text_field($shipping_address_zip) : '';
        $shipping_address_state = $event_json->data->object->shipping->address->state;
        $payment_data['shipping_address_state'] = isset($shipping_address_state) && !empty($shipping_address_state) ? sanitize_text_field($shipping_address_state) : '';
        $shipping_address_city = $event_json->data->object->shipping->address->city;
        $payment_data['shipping_address_city'] = isset($shipping_address_city) && !empty($shipping_address_city) ? sanitize_text_field($shipping_address_city) : '';
        $shipping_address_country = $event_json->data->object->shipping->address->country;
        $payment_data['shipping_address_country'] = isset($shipping_address_country) && !empty($shipping_address_country) ? sanitize_text_field($shipping_address_country) : '';
    }
    $amount_shipping = sanitize_text_field($event_json->data->object->total_details->amount_shipping);
    if(isset($amount_shipping) && is_numeric($amount_shipping)){
        $temp_shipping = $amount_shipping/100;
        $payment_data['amount_shipping'] = number_format($temp_shipping, 2, '.', '');
    }
    $args = array(
        'post_type' => 'wpstripeco_order',
        'meta_query' => array(
            array(
                'key' => '_txn_id',
                'value' => $payment_data['txn_id'],
                'compare' => '=',
            ),
        ),
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {  //a record already exists
        wp_stripe_checkout_debug_log("An order with this transaction ID already exists. This payment will not be processed.", false);
        return;
    }
    $content = '';
    $content .= '<strong>Transaction ID:</strong> '.$payment_data['txn_id'].'<br />';
    $content .= '<strong>Product name:</strong> '.$payment_data['product_name'].'<br />';
    if(!empty($payment_data['product_id'])){
        $content .= '<strong>Product ID:</strong> '.$payment_data['product_id'].'<br />'; 
    }
    if(isset($payment_data['price']) && is_numeric($payment_data['price'])){
        $content .= '<strong>Price:</strong> '.$payment_data['price'].'<br />'; 
    }
    if(isset($payment_data['quantity']) && is_numeric($payment_data['quantity'])){
        $content .= '<strong>Quantity:</strong> '.$payment_data['quantity'].'<br />'; 
    }
    if(isset($payment_data['amount_shipping']) && is_numeric($payment_data['amount_shipping'])){
        $content .= '<strong>Shipping:</strong> '.$payment_data['amount_shipping'].'<br />';
    }
    $content .= '<strong>Total:</strong> '.$payment_data['amount_total'].'<br />';
    $content .= '<strong>Currency:</strong> '.$payment_data['currency_code'].'<br />';
    if(!empty($payment_data['billing_name'])){
        $content .= '<strong>Billing Name:</strong> '.$payment_data['billing_name'].'<br />';
    }
    if(!empty($payment_data['customer_email'])){
        $content .= '<strong>Email:</strong> '.$payment_data['customer_email'].'<br />'; 
    }
    if(!empty($payment_data['stripe_customer_id'])){
        $content .= '<strong>Stripe Customer ID:</strong> '.$payment_data['stripe_customer_id'].'<br />'; 
    }
    if(!empty($payment_data['billing_address_line1'])){
        $content .= '<strong>Billing Address:</strong> '.$payment_data['billing_address_line1'];
        if(!empty($payment_data['billing_address_city'])){
            $content .= ', '.$payment_data['billing_address_city'];
        }
        if(!empty($payment_data['billing_address_state'])){
            $content .= ', '.$payment_data['billing_address_state'];
        }
        if(!empty($payment_data['billing_address_zip'])){
            $content .= ', '.$payment_data['billing_address_zip'];
        }
        if(!empty($payment_data['billing_address_country'])){
            $content .= ', '.$payment_data['billing_address_country'];
        }
        $content .= '<br />';
    }
    if(!empty($payment_data['shipping_name'])){
        $content .= '<strong>Shipping Name:</strong> '.$payment_data['shipping_name'].'<br />';
    }
    if(!empty($payment_data['shipping_address_line1'])){
        $content .= '<strong>Shipping Address:</strong> '.$payment_data['shipping_address_line1'];
        if(!empty($payment_data['shipping_address_city'])){
            $content .= ', '.$payment_data['shipping_address_city'];
        }
        if(!empty($payment_data['shipping_address_state'])){
            $content .= ', '.$payment_data['shipping_address_state'];
        }
        if(!empty($payment_data['shipping_address_zip'])){
            $content .= ', '.$payment_data['shipping_address_zip'];
        }
        if(!empty($payment_data['shipping_address_country'])){
            $content .= ', '.$payment_data['shipping_address_country'];
        }
        $content .= '<br />';
    }
    $payment_data['order_id'] = '';
    $wp_stripe_checkout_order = array(
        'post_title' => 'order',
        'post_type' => 'wpstripeco_order',
        'post_content' => '',
        'post_status' => 'publish',
    );
    wp_stripe_checkout_debug_log("Updating order information", true);
    $post_id = wp_insert_post($wp_stripe_checkout_order);  //insert a new order
    $post_updated = false;
    if ($post_id > 0) {
        $post_content = $content;
        $updated_post = array(
            'ID' => $post_id,
            'post_title' => $post_id,
            'post_type' => 'wpstripeco_order',
            'post_content' => $post_content
        );
        $updated_post_id = wp_update_post($updated_post);  //update the order
        if ($updated_post_id > 0) {  //successfully updated
            $post_updated = true;
        }
    }
    //save order information
    if ($post_updated) {
        $payment_data['order_id'] = $post_id;
        update_post_meta($post_id, '_txn_id', $payment_data['txn_id']);
        update_post_meta($post_id, '_name', $payment_data['billing_name']);
        update_post_meta($post_id, '_amount', $payment_data['amount_total']);
        update_post_meta($post_id, '_email', $payment_data['customer_email']);
        wp_stripe_checkout_debug_log("Order information updated", true);
        $email_options = wp_stripe_checkout_get_email_option();
        add_filter('wp_mail_from', 'wp_stripe_checkout_set_email_from');
        add_filter('wp_mail_from_name', 'wp_stripe_checkout_set_email_from_name');
        if(isset($email_options['purchase_email_enabled']) && !empty($email_options['purchase_email_enabled']) && !empty($payment_data['customer_email'])){
            $subject = $email_options['purchase_email_subject'];
            $type = $email_options['purchase_email_type'];
            $body = $email_options['purchase_email_body'];
            $body = wp_stripe_checkout_do_email_tags($payment_data, $body);
            if($type == "html"){
                add_filter('wp_mail_content_type', 'wp_stripe_checkout_set_html_email_content_type');
                $body = apply_filters('wp_stripe_checkout_email_body_wpautop', true) ? wpautop($body) : $body;
            }
            wp_stripe_checkout_debug_log("Sending a purchase receipt email to ".$payment_data['customer_email'], true);
            $mail_sent = wp_mail($payment_data['customer_email'], $subject, $body);
            if($type == "html"){
                remove_filter('wp_mail_content_type', 'wp_stripe_checkout_set_html_email_content_type');
            }
            if($mail_sent == true){
                wp_stripe_checkout_debug_log("Email was sent successfully by WordPress", true);
            }
            else{
                wp_stripe_checkout_debug_log("Email could not be sent by WordPress", false);
            }
        }
        if(isset($email_options['sale_notification_email_enabled']) && !empty($email_options['sale_notification_email_enabled']) && !empty($email_options['sale_notification_email_recipient'])){
            $subject = $email_options['sale_notification_email_subject'];
            $type = $email_options['sale_notification_email_type'];
            $body = $email_options['sale_notification_email_body'];
            $body = wp_stripe_checkout_do_email_tags($payment_data, $body);
            if($type == "html"){
                add_filter('wp_mail_content_type', 'wp_stripe_checkout_set_html_email_content_type');
                $body = apply_filters('wp_stripe_checkout_email_body_wpautop', true) ? wpautop($body) : $body;
            }
            wp_stripe_checkout_debug_log("Sending a sale notification email to ".$email_options['sale_notification_email_recipient'], true);
            $mail_sent = wp_mail($email_options['sale_notification_email_recipient'], $subject, $body);
            if($type == "html"){
                remove_filter('wp_mail_content_type', 'wp_stripe_checkout_set_html_email_content_type');
            }
            if($mail_sent == true){
                wp_stripe_checkout_debug_log("Email was sent successfully by WordPress", true);
            }
            else{
                wp_stripe_checkout_debug_log("Email could not be sent by WordPress", false);
            }
        }
        remove_filter('wp_mail_from', 'wp_stripe_checkout_set_email_from');
        remove_filter('wp_mail_from_name', 'wp_stripe_checkout_set_email_from_name');      
        do_action('wpstripecheckout_order_processed', $post_id);
    } else {
        wp_stripe_checkout_debug_log("Order information could not be updated", false);
        return;
    }
    wp_stripe_checkout_debug_log("Order processing completed", true, true);
    do_action('wpstripecheckout_payment_completed', $payment_data);
}
