<?php

function wp_stripe_checkout_process_order() {
    if (!isset($_POST['wp_stripe_checkout_legacy']) && !isset($_POST['wp_stripe_checkout_legacy'])) {
        return;
    }
    if (!isset($_POST['stripeToken']) && !isset($_POST['stripeTokenType'])) {
        return;
    }
    $nonce = $_REQUEST['_wpnonce'];
    if ( !wp_verify_nonce($nonce, 'wp_stripe_checkout_legacy')){
        $error_msg = __('Error! Nonce Security Check Failed!', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $_POST = stripslashes_deep($_POST);
    $stripeToken = sanitize_text_field($_POST['stripeToken']);
    if (empty($stripeToken)) {
        $error_msg = __('Please make sure your card details have been entered correctly and that your browser supports JavaScript.', 'wp-stripe-checkout');
        $error_msg .= ' ' . __('Please also make sure that you are including jQuery and there are no JavaScript errors on the page.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    if (!isset($_POST['item_name']) || empty($_POST['item_name'])) {
        $error_msg = __('Product name could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $payment_data = array();
    $payment_data['product_name'] = sanitize_text_field($_POST['item_name']);
    /*
    $transient_name = 'wpstripecheckout-amount-' . sanitize_title_with_dashes($payment_data['product_name']);
    $payment_data['price'] = get_transient($transient_name);
    if(!isset($payment_data['price']) || !is_numeric($payment_data['price'])){
        $error_msg = __('Product price could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $transient_name = 'wpstripecheckout-currency-' . sanitize_title_with_dashes($payment_data['product_name']);
    $payment_data['currency_code'] = get_transient($transient_name);
    if(!isset($payment_data['currency_code']) || empty($payment_data['currency_code'])){
        $error_msg = __('Currency could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    */
    if (!isset($_POST['item_price']) || !is_numeric($_POST['item_price'])) {
        $error_msg = __('Product price could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $payment_data['price'] = sanitize_text_field($_POST['item_price']);
    if (!isset($_POST['item_amount']) || !is_numeric($_POST['item_amount'])) {
        $error_msg = __('Product amount could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $payment_data['amount'] = sanitize_text_field($_POST['item_amount']);
    if (!isset($_POST['item_currency']) || empty($_POST['item_currency'])) {
        $error_msg = __('Currency could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $payment_data['currency_code'] = sanitize_text_field($_POST['item_currency']);
    $payment_data['product_description'] = '';
    if(isset($_POST['item_description']) && !empty($_POST['item_description'])){
        $payment_data['product_description'] = sanitize_text_field($_POST['item_description']);
    }
    $success_url = '';
    if (isset($_POST['success_url']) && !empty($_POST['success_url'])) {
        $success_url = esc_url_raw($_POST['success_url']);
    }
    $payment_data['billing_name'] = isset($_POST['stripeBillingName']) && !empty($_POST['stripeBillingName']) ? sanitize_text_field($_POST['stripeBillingName']) : '';
    $customer_description = '';
    $payment_data['billing_first_name'] = '';
    $payment_data['billing_last_name'] = '';
    if(!empty($payment_data['billing_name'])){
        $customer_description = __('Name', 'wp-stripe-checkout').': '.$payment_data['billing_name'];
        $billing_name_parts = explode(" ", $payment_data['billing_name']);
        $payment_data['billing_first_name'] = isset($billing_name_parts[0]) && !empty($billing_name_parts[0]) ? $billing_name_parts[0] : '';
        $payment_data['billing_last_name'] = isset($billing_name_parts[1]) && !empty($billing_name_parts[1]) ? array_pop($billing_name_parts) : '';
    }
    $payment_data['billing_address_line1'] = isset($_POST['stripeBillingAddressLine1']) && !empty($_POST['stripeBillingAddressLine1']) ? sanitize_text_field($_POST['stripeBillingAddressLine1']) : '';
    $payment_data['billing_address_zip'] = isset($_POST['stripeBillingAddressZip']) && !empty($_POST['stripeBillingAddressZip']) ? sanitize_text_field($_POST['stripeBillingAddressZip']) : '';
    $payment_data['billing_address_state'] = isset($_POST['stripeBillingAddressState']) && !empty($_POST['stripeBillingAddressState']) ? sanitize_text_field($_POST['stripeBillingAddressState']) : '';
    $payment_data['billing_address_city'] = isset($_POST['stripeBillingAddressCity']) && !empty($_POST['stripeBillingAddressCity']) ? sanitize_text_field($_POST['stripeBillingAddressCity']) : '';
    $payment_data['billing_address_country'] = isset($_POST['stripeBillingAddressCountry']) && !empty($_POST['stripeBillingAddressCountry']) ? sanitize_text_field($_POST['stripeBillingAddressCountry']) : '';
    $payment_data['shipping_name'] = isset($_POST['stripeShippingName']) && !empty($_POST['stripeShippingName']) ? sanitize_text_field($_POST['stripeShippingName']) : '';
    $payment_data['shipping_first_name'] = '';
    $payment_data['shipping_last_name'] = '';
    if(!empty($payment_data['shipping_name'])){
        $shipping_name_parts = explode(" ", $payment_data['shipping_name']);
        $payment_data['shipping_first_name'] = isset($shipping_name_parts[0]) && !empty($shipping_name_parts[0]) ? $shipping_name_parts[0] : '';
        $payment_data['shipping_last_name'] = isset($shipping_name_parts[1]) && !empty($shipping_name_parts[1]) ? array_pop($shipping_name_parts) : '';
    }
    $payment_data['shipping_address_line1'] = isset($_POST['stripeShippingAddressLine1']) && !empty($_POST['stripeShippingAddressLine1']) ? sanitize_text_field($_POST['stripeShippingAddressLine1']) : '';
    $payment_data['shipping_address_zip'] = isset($_POST['stripeShippingAddressZip']) && !empty($_POST['stripeShippingAddressZip']) ? sanitize_text_field($_POST['stripeShippingAddressZip']) : '';
    $payment_data['shipping_address_state'] = isset($_POST['stripeShippingAddressState']) && !empty($_POST['stripeShippingAddressState']) ? sanitize_text_field($_POST['stripeShippingAddressState']) : '';
    $payment_data['shipping_address_city'] = isset($_POST['stripeShippingAddressCity']) && !empty($_POST['stripeShippingAddressCity']) ? sanitize_text_field($_POST['stripeShippingAddressCity']) : '';
    $payment_data['shipping_address_country'] = isset($_POST['stripeShippingAddressCountry']) && !empty($_POST['stripeShippingAddressCountry']) ? sanitize_text_field($_POST['stripeShippingAddressCountry']) : '';
    wp_stripe_checkout_debug_log("Post Data", true);
    wp_stripe_checkout_debug_log_array($_POST, true);
    // Other charge data
    $post_data['currency'] = strtolower($payment_data['currency_code']);
    $post_data['amount'] = $payment_data['amount']; //$payment_data['price'] * 100;
    $post_data['description'] = $payment_data['product_description'];
    $post_data['capture'] = 'true';
    $payment_data['customer_email'] = '';
    if(isset($_POST['stripeEmail'])) {
        $payment_data['customer_email'] = sanitize_email($_POST['stripeEmail']);
        $post_data['receipt_email'] = $payment_data['customer_email'];
        //create a Stripe customer
        $customer_args = array(
                'email'       => $payment_data['customer_email'],
                'description' => $customer_description,
                'source' => $stripeToken,
        );
        wp_stripe_checkout_debug_log("Creating a Stripe customer", true);
        $response = WP_SC_Stripe_API::request($customer_args, 'customers');
        $post_data['customer'] = $response->id;
    }
    //only specify a source if no customber is created
    if(!isset($post_data['customer'])) {
        $post_data['source'] = $stripeToken;
    }
    $post_data['expand[]'] = 'balance_transaction';

    // Make the request
    wp_stripe_checkout_debug_log("Creating a charge request", true);
    $response = WP_SC_Stripe_API::request($post_data);
    //process data
    $payment_data['txn_id'] = $response->id;
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
    $content .= '<strong>Amount:</strong> '.$payment_data['price'].'<br />';
    $content .= '<strong>Currency:</strong> '.$payment_data['currency_code'].'<br />';
    if(!empty($payment_data['billing_name'])){
        $content .= '<strong>Billing Name:</strong> '.$payment_data['billing_name'].'<br />';
    }
    if(!empty($payment_data['customer_email'])){
        $content .= '<strong>Email:</strong> '.$payment_data['customer_email'].'<br />'; 
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
    wp_stripe_checkout_debug_log("Oder processing completed", true, true);
    do_action('wpstripecheckout_payment_completed', $payment_data);
    $stripe_options = wp_stripe_checkout_get_option();
    if(!empty($success_url)){
        wp_safe_redirect($success_url);
        exit;
    }
    else if(isset($stripe_options['success_url']) && !empty($stripe_options['success_url'])){
        wp_safe_redirect($stripe_options['success_url']);
        exit;
    }
}

function wp_stripe_checkout_process_session_button() {
    if (!isset($_POST['wp_stripe_checkout_session'])) {
        return;
    }
    $nonce = $_REQUEST['_wpnonce'];
    if ( !wp_verify_nonce($nonce, 'wp_stripe_checkout_session_nonce')){
        $error_msg = __('Error! Nonce Security Check Failed!', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $_POST = stripslashes_deep($_POST);
    if (!isset($_POST['client_reference_id']) || empty($_POST['client_reference_id'])) {
        $error_msg = __('Client Reference ID could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $client_reference_id = sanitize_text_field($_POST['client_reference_id']);
    if (!isset($_POST['item_name']) || empty($_POST['item_name'])) {
        $error_msg = __('Item name could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $post_data = array();
    $post_data['item_name'] = sanitize_text_field($_POST['item_name']);
    if(isset($_POST['item_price']) && is_numeric($_POST['item_price']) && $_POST['item_price'] > 0) {
        $post_data['price'] = sanitize_text_field($_POST['item_price']);
    }
    else{
        $error_msg = __('Item price could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $quantity = '1';
    if(isset($_POST['item_quantity']) && is_numeric($_POST['item_quantity']) && $_POST['item_quantity'] > 0) {
        $quantity = sanitize_text_field($_POST['item_quantity']);
    }
    if (!isset($_POST['item_currency']) || empty($_POST['item_currency'])) {
        $error_msg = __('Currency could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $post_data['currency_code'] = sanitize_text_field($_POST['item_currency']);
    $success_url = '';
    if (isset($_POST['success_url']) && !empty($_POST['success_url'])) {
        $success_url = esc_url_raw($_POST['success_url']);
    }
    else{
        $error_msg = __('Success URL could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $cancel_url = '';
    if (isset($_POST['cancel_url']) && !empty($_POST['cancel_url'])) {
        $cancel_url = esc_url_raw($_POST['cancel_url']);
    }
    else{
        $error_msg = __('Cancel URL could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $billing_address = '';
    if(isset($_POST['billing_address']) && !empty($_POST['billing_address'])){
        $billing_address = sanitize_text_field($_POST['billing_address']);
    }
    $phone_number_collection = '';
    if(isset($_POST['phone_number_collection']) && !empty($_POST['phone_number_collection'])){
        $phone_number_collection = sanitize_text_field($_POST['phone_number_collection']);
    }
    $allow_promotion_codes = '';
    if(isset($_POST['allow_promotion_codes']) && !empty($_POST['allow_promotion_codes'])){
        $allow_promotion_codes = sanitize_text_field($_POST['allow_promotion_codes']);
    }
    wp_stripe_checkout_debug_log("Post Data", true);
    wp_stripe_checkout_debug_log_array($_POST, true);
    $session_args = array();
    $line_items = array();
    $price_data = array();
    $price_data['currency'] = strtolower($post_data['currency_code']);
    $price_data['product_data'] = array('name' => $post_data['item_name']);
    $price_data['unit_amount'] = $post_data['price'] * 100;
    $line_items['price_data'] = $price_data;
    $line_items['quantity'] = $quantity;
    $session_args['line_items'] = array($line_items);
    $session_args['mode'] = 'payment';
    $session_args['success_url'] = $success_url;
    $session_args['cancel_url'] = $cancel_url;
    $session_args['client_reference_id'] = $client_reference_id;
    if($billing_address == 'required'){
        $session_args['billing_address_collection'] = 'required';
    }
    if($phone_number_collection == 'true'){
        $session_args['phone_number_collection'] = array('enabled' => 'true');
    }
    if($allow_promotion_codes == 'true'){
        $session_args['allow_promotion_codes'] = 'true';
    }
    wp_stripe_checkout_debug_log("Creating a session", true);
    $response = WP_SC_Stripe_API::request($session_args, 'checkout/sessions');
    $session_url = $response->url;
        
    if(isset($session_url) && !empty($session_url)){
        wp_redirect($session_url);
        exit;
    }
    else{
        $error_msg = __('Session URL could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
}

function wp_stripe_checkout_process_button() {
    if(!isset($_POST['wp_stripe_checkout_button_input'])) {
        return;
    }
    if(!isset($_REQUEST['_wp_stripe_checkout_button_nonce'])) {
        return;
    }
    $nonce = $_REQUEST['_wp_stripe_checkout_button_nonce'];
    if(!wp_verify_nonce($nonce, 'wp_stripe_checkout_button')){
        $error_msg = __('Error! Nonce Security Check Failed!', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $_POST = stripslashes_deep($_POST);
    if (!isset($_POST['wpsc_product_id']) || empty($_POST['wpsc_product_id'])) {
        $error_msg = __('Product ID could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $post_id = sanitize_text_field($_POST['wpsc_product_id']);
    $post = get_post($post_id);
    if (!$post) {
        $error_msg = __('Invalid product ID.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    if('wpstripeco_product' != $post->post_type){
        $error_msg = __('Invalid product type', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $post_data = array();
    $product_name = sanitize_text_field($post->post_title);
    if(!isset($product_name) || empty($product_name)){
        $error_msg = __('Product name could not be found', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $post_data['product_name'] = $product_name;
    $product_price = sanitize_text_field(get_post_meta($post_id, '_wpstripeco_product_price', true));
    $enable_variable_pricing = get_post_meta($post_id, '_wpstripeco_product_enable_variable_pricing', true);
    if(defined('WPSTRIPECO_VARIABLE_PRICE_VERSION') && isset($enable_variable_pricing) && $enable_variable_pricing == '1'){
        if(isset($_POST['wpsc_product_price']) && is_numeric($_POST['wpsc_product_price']) && $_POST['wpsc_product_price'] > 0) {
            $product_price = sanitize_text_field($_POST['wpsc_product_price']);
            $post_data['price'] = $product_price;
        }
        else{
            $error_msg = __('Invalid product price', 'wp-stripe-checkout');
            wp_die($error_msg);
        }
    }
    else{
        if(isset($product_price) && is_numeric($product_price) && $product_price > 0) {
            $post_data['price'] = $product_price;
        }
        else{
            $error_msg = __('Invalid product price', 'wp-stripe-checkout');
            wp_die($error_msg);
        }
    }
    $quantity = '1';
    $enable_variable_quantity = get_post_meta($post_id, '_wpstripeco_product_enable_variable_quantity', true);
    if(defined('WPSTRIPECO_VARIABLE_QUANTITY_VERSION') && isset($enable_variable_quantity) && $enable_variable_quantity == '1'){
        if(isset($_POST['wpsc_product_quantity']) && is_numeric($_POST['wpsc_product_quantity']) && $_POST['wpsc_product_quantity'] > 0) {
            $quantity = sanitize_text_field($_POST['wpsc_product_quantity']);
        }
        else{
            $error_msg = __('Invalid product quantity', 'wp-stripe-checkout');
            wp_die($error_msg);
        }
    }
    $options = wp_stripe_checkout_get_option();
    $currency_code = $options['stripe_currency_code'];
    if(!isset($currency_code) || empty($currency_code)){
        $error_msg = __('Currency could not be found', 'wp-stripe-checkout');
        wp_die($error_msg);
    }

    $post_data['currency_code'] = sanitize_text_field($currency_code);
    $success_url = '';
    if(isset($options['success_url']) && !empty($options['success_url'])){
        $success_url = esc_url_raw($options['success_url']);
    }
    /*
    $product_success_url = esc_url_raw(get_post_meta($post_id, '_wpstripeco_product_success_url', true));
    if(isset($product_success_url) && !empty($product_success_url)){
        $success_url = $product_success_url;
    }
    */
    if(empty($success_url)){
        $error_msg = __('Success URL could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $cancel_url = '';
    if(isset($options['cancel_url']) && !empty($options['cancel_url'])){
        $cancel_url = esc_url_raw($options['cancel_url']);
    }
    /*
    $product_cancel_url = esc_url_raw(get_post_meta($post_id, '_wpstripeco_product_cancel_url', true));
    if(isset($product_cancel_url) && !empty($product_cancel_url)){
        $cancel_url = $product_cancel_url;
    }
    */
    if(empty($cancel_url)){
        $error_msg = __('Cancel URL could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
    $client_reference_id = 'wpscprod-'.$post_id;
    $billing_address_collection = sanitize_text_field(get_post_meta($post_id, '_wpstripeco_product_billing_address_collection', true));
    $phone_number_collection = sanitize_text_field(get_post_meta($post_id, '_wpstripeco_product_phone_number_collection', true));
    $shipping_address_collection = sanitize_text_field(get_post_meta($post_id, '_wpstripeco_product_shipping_address_collection', true));
    $stripe_shipping_rate_id = sanitize_text_field(get_post_meta($post_id, '_wpstripeco_product_stripe_shipping_rate_id', true));
    $allow_promotion_codes = sanitize_text_field(get_post_meta($post_id, '_wpstripeco_product_allow_promotion_codes', true));
    wp_stripe_checkout_debug_log("Post Data", true);
    wp_stripe_checkout_debug_log_array($_POST, true);
    $session_args = array();
    $line_items = array();
    $price_data = array();
    $price_data['currency'] = strtolower($post_data['currency_code']);
    $price_data['product_data'] = array('name' => $post_data['product_name']);
    $price_data['unit_amount'] = $post_data['price'] * 100;
    $line_items['price_data'] = $price_data;
    $line_items['quantity'] = $quantity;
    $session_args['line_items'] = array($line_items);
    $session_args['mode'] = 'payment';
    $session_args['success_url'] = $success_url;
    $session_args['cancel_url'] = $cancel_url;
    $session_args['client_reference_id'] = $client_reference_id;
    if(isset($billing_address_collection) && !empty($billing_address_collection)){
        $session_args['billing_address_collection'] = $billing_address_collection;
    }
    if(isset($phone_number_collection) && $phone_number_collection == '1'){
        $session_args['phone_number_collection'] = array('enabled' => 'true');
    }
    if(isset($shipping_address_collection) && !empty($shipping_address_collection)){
        $allowed_countries = wp_stripe_checkout_get_shipping_countries_array(); 
        $session_args['shipping_address_collection'] = array('allowed_countries' => $allowed_countries);
    }
    if(isset($stripe_shipping_rate_id) && !empty($stripe_shipping_rate_id)) {
        /*
        $shipping_rate_data = array('type' => 'fixed_amount');
        $shipping_rate_data['fixed_amount'] = array('amount' => $product_shipping_amount * 100, 'currency' => $price_data['currency']);
        $shipping_rate_data['display_name'] = 'Next day air';
        */
        $shipping_options = array();
        $shipping_options[] = array('shipping_rate' => $stripe_shipping_rate_id);
        $session_args['shipping_options'] = $shipping_options;
    }
    if(isset($allow_promotion_codes) && $allow_promotion_codes == '1'){
        $session_args['allow_promotion_codes'] = 'true';
    }
    wp_stripe_checkout_debug_log("Creating a session", true);
    $response = WP_SC_Stripe_API::request($session_args, 'checkout/sessions');
    $session_url = $response->url;
        
    if(isset($session_url) && !empty($session_url)){
        wp_redirect($session_url);
        exit;
    }
    else{
        $error_msg = __('Session URL could not be found.', 'wp-stripe-checkout');
        wp_die($error_msg);
    }
}

function wp_stripe_checkout_do_email_tags($payment_data, $content){
    $search = array(
        '{first_name}', 
        '{last_name}', 
        '{full_name}',
        '{txn_id}',
        '{product_name}',
        '{price}',
        '{quantity}',
        '{currency_code}',
        '{customer_email}',
        '{product_id}',
        '{price_id}',
        '{amount_total}',
        '{amount_shipping}',
        '{billing_name}',
        '{billing_address}',
        '{shipping_name}',
        '{shipping_address}'
    );
    $replace = array(
        $payment_data['billing_first_name'], 
        $payment_data['billing_last_name'],
        $payment_data['billing_name'],
        $payment_data['txn_id'],
        $payment_data['product_name'],
        $payment_data['price'],
        $payment_data['quantity'],
        $payment_data['currency_code'],
        $payment_data['customer_email'],
        $payment_data['product_id'],
        $payment_data['price_id'],
        $payment_data['amount_total'],
        $payment_data['amount_shipping'],
        $payment_data['billing_name'],
        $payment_data['billing_address'],
        $payment_data['shipping_name'],
        $payment_data['shipping_address']
    );
    $content = str_replace($search, $replace, $content);
    return $content;
}

function wp_stripe_checkout_set_email_from($from){
    $email_options = wp_stripe_checkout_get_email_option();
    if(isset($email_options['email_from_address']) && !empty($email_options['email_from_address'])){
        $from = $email_options['email_from_address'];
    }
    return $from;
}

function wp_stripe_checkout_set_email_from_name($from_name){
    $email_options = wp_stripe_checkout_get_email_option();
    if(isset($email_options['email_from_name']) && !empty($email_options['email_from_name'])){
        $from_name = $email_options['email_from_name'];
    }
    return $from_name;
}

function wp_stripe_checkout_set_html_email_content_type($content_type){
    $content_type = 'text/html';
    return $content_type;
}
