<?php

function wp_stripe_checkout_register_order_type() {
    $labels = array(
        'name' => __('Orders', 'wp-stripe-checkout'),
        'singular_name' => __('Order', 'wp-stripe-checkout'),
        'menu_name' => __('Stripe Checkout', 'wp-stripe-checkout'),
        'name_admin_bar' => __('Order', 'wp-stripe-checkout'),
        'add_new' => __('Add New', 'wp-stripe-checkout'),
        'add_new_item' => __('Add New Order', 'wp-stripe-checkout'),
        'new_item' => __('New Order', 'wp-stripe-checkout'),
        'edit_item' => __('Edit Order', 'wp-stripe-checkout'),
        'view_item' => __('View Order', 'wp-stripe-checkout'),
        'all_items' => __('All Orders', 'wp-stripe-checkout'),
        'search_items' => __('Search Orders', 'wp-stripe-checkout'),
        'parent_item_colon' => __('Parent Orders:', 'wp-stripe-checkout'),
        'not_found' => __('No Orders found.', 'wp-stripe-checkout'),
        'not_found_in_trash' => __('No orders found in Trash.', 'wp-stripe-checkout')
    );
    
    $capability = 'manage_options';
    $capabilities = array(
        'edit_post' => $capability,
        'read_post' => $capability,
        'delete_post' => $capability,
        'create_posts' => $capability,
        'edit_posts' => $capability,
        'edit_others_posts' => $capability,
        'publish_posts' => $capability,
        'read_private_posts' => $capability,
        'read' => $capability,
        'delete_posts' => $capability,
        'delete_private_posts' => $capability,
        'delete_published_posts' => $capability,
        'delete_others_posts' => $capability,
        'edit_private_posts' => $capability,
        'edit_published_posts' => $capability
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_nav_menus' => false,
        'show_in_menu' => current_user_can('manage_options') ? true : false,
        'query_var' => false,
        'rewrite' => false,
        'capabilities' => $capabilities,
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('editor')
    );

    register_post_type('wpstripeco_order', $args);
}

function wp_stripe_checkout_order_columns($columns) {
    unset($columns['title']);
    unset($columns['date']);
    $edited_columns = array(
        'title' => __('Order', 'wp-stripe-checkout'),
        'txn_id' => __('Transaction ID', 'wp-stripe-checkout'),
        'name' => __('Name', 'wp-stripe-checkout'),
        'email' => __('Email', 'wp-stripe-checkout'),
        'amount' => __('Total', 'wp-stripe-checkout'),
        'wp_user_id' => __('WP User ID', 'wp-stripe-checkout'),
        'date' => __('Date', 'wp-stripe-checkout')
    );
    return array_merge($columns, $edited_columns);
}

function wp_stripe_checkout_custom_column($column, $post_id) {
    switch ($column) {
        case 'title' :
            echo esc_html($post_id);
            break;
        case 'txn_id' :
            echo esc_html(get_post_meta($post_id, '_txn_id', true));
            break;
        case 'name' :
            echo esc_html(get_post_meta($post_id, '_name', true));
            break;
        case 'email' :
            echo esc_html(get_post_meta($post_id, '_email', true));
            break;
        case 'amount' :
            echo esc_html(get_post_meta($post_id, '_amount', true));
            break;
        case 'wp_user_id' :
            echo esc_html(get_post_meta($post_id, '_wp_user_id', true));
            break;
    }
}

function wpstripeco_order_meta_boxes($post){
    $post_type = 'wpstripeco_order';
    /** Product Data **/
    add_meta_box('wpstripeco_order_data', __('Order Data'),  'wpstripeco_render_order_data_meta_box', $post_type, 'normal', 'high');
}

function wpstripeco_render_order_data_meta_box($post){
    $post_id = $post->ID;
    //echo '<p>post id: '.$post_id.'</p>';
    $transaction_id = get_post_meta($post_id, '_txn_id', true);
    if(!isset($transaction_id) || empty($transaction_id)){
        $transaction_id = '';
    }
    $product_name = get_post_meta($post_id, '_product_name', true);
    if(!isset($product_name) || empty($product_name)){
        $product_name = '';
    }
    $customer_name = get_post_meta($post_id, '_name', true);
    if(!isset($customer_name) || empty($customer_name)){
        $customer_name = '';
    }
    $customer_email = get_post_meta($post_id, '_email', true);
    if(!isset($customer_email) || empty($customer_email)){
        $customer_email = '';
    }
    $total_amount = get_post_meta($post_id, '_amount', true);
    if(!isset($total_amount) || !is_numeric($total_amount)){
        $total_amount = '';
    }
    $wp_user_id = get_post_meta($post_id, '_wp_user_id', true);
    if(!isset($wp_user_id) || empty($wp_user_id)){
        $wp_user_id = '';
    }
    ?>
    <table>
        <tbody>
            <tr>
                <td valign="top">
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_order_txn_id"><?php _e('Transaction ID', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_order_txn_id" type="text" id="_wpstripeco_order_txn_id" value="<?php echo esc_attr($transaction_id); ?>" class="regular-text"></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_order_product_name"><?php _e('Product Name', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_order_product_name" type="text" id="_wpstripeco_order_product_name" value="<?php echo esc_attr($product_name); ?>" class="regular-text"></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_order_name"><?php _e('Customer Name', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_order_name" type="text" id="_wpstripeco_order_name" value="<?php echo esc_attr($customer_name); ?>" class="regular-text"></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_order_email"><?php _e('Customer Email', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_order_email" type="text" id="_wpstripeco_order_email" value="<?php echo esc_attr($customer_email); ?>" class="regular-text"></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_order_amount"><?php _e('Total Amount', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_order_amount" type="text" id="_wpstripeco_order_amount" value="<?php echo esc_attr($total_amount); ?>" class="regular-text"></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_wp_user_id"><?php _e('WP User ID', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_wp_user_id" type="text" id="_wpstripeco_wp_user_id" value="<?php echo esc_attr($wp_user_id); ?>" class="regular-text"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody> 
    </table>
    <?php
    wp_nonce_field(basename(__FILE__), 'wpstripeco_order_data_meta_box_nonce');
}

function wpstripeco_order_data_meta_box_save($post_id, $post){
    if(!isset($_POST['wpstripeco_order_data_meta_box_nonce']) || !wp_verify_nonce($_POST['wpstripeco_order_data_meta_box_nonce'], basename(__FILE__))){
        return;
    }
    if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit'])){
        return;
    }
    if(isset($post->post_type) && 'revision' == $post->post_type){
        return;
    }
    if(!current_user_can('manage_options')){
        return;
    }
    //update the values
    if(isset($_POST['_wpstripeco_order_txn_id'])){
        $transaction_id = sanitize_text_field($_POST['_wpstripeco_order_txn_id']);
        update_post_meta($post_id, '_txn_id', $transaction_id);
    }
    if(isset($_POST['_wpstripeco_order_product_name'])){
        $product_name = sanitize_text_field($_POST['_wpstripeco_order_product_name']);
        update_post_meta($post_id, '_product_name', $product_name);
    }
    if(isset($_POST['_wpstripeco_order_name'])){
        $customer_name = sanitize_text_field($_POST['_wpstripeco_order_name']);
        update_post_meta($post_id, '_name', $customer_name);
    }
    if(isset($_POST['_wpstripeco_order_email'])){
        $customer_email = sanitize_text_field($_POST['_wpstripeco_order_email']);
        update_post_meta($post_id, '_email', $customer_email);
    }
    if(isset($_POST['_wpstripeco_order_amount']) && is_numeric($_POST['_wpstripeco_order_amount'])){
        $total_amount = sanitize_text_field($_POST['_wpstripeco_order_amount']);
        update_post_meta($post_id, '_amount', $total_amount);
    }
    if(isset($_POST['_wpstripeco_wp_user_id'])){
        $wp_user_id = sanitize_text_field($_POST['_wpstripeco_wp_user_id']);
        update_post_meta($post_id, '_wp_user_id', $wp_user_id);
    }
}

add_action('save_post_wpstripeco_order', 'wpstripeco_order_data_meta_box_save', 10, 2 );
