<?php

function wp_stripe_checkout_register_product_type() {
    $labels = array(
        'name' => __('Products', 'wp-stripe-checkout'),
        'singular_name' => __('Product', 'wp-stripe-checkout'),
        'menu_name' => __('Stripe Checkout', 'wp-stripe-checkout'),
        'name_admin_bar' => __('Product', 'wp-stripe-checkout'),
        'add_new' => __('Add New', 'wp-stripe-checkout'),
        'add_new_item' => __('Add New Product', 'wp-stripe-checkout'),
        'new_item' => __('New Product', 'wp-stripe-checkout'),
        'edit_item' => __('Edit Product', 'wp-stripe-checkout'),
        'view_item' => __('View Product', 'wp-stripe-checkout'),
        'all_items' => __('All Products', 'wp-stripe-checkout'),
        'search_items' => __('Search Products', 'wp-stripe-checkout'),
        'parent_item_colon' => __('Parent Products:', 'wp-stripe-checkout'),
        'not_found' => __('No Products found.', 'wp-stripe-checkout'),
        'not_found_in_trash' => __('No products found in Trash.', 'wp-stripe-checkout')
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
        'show_in_menu' => current_user_can('manage_options') ? 'edit.php?post_type=wpstripeco_order' : false,
        'query_var' => false,
        'rewrite' => false,
        'capabilities' => $capabilities,
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'thumbnail')
    );

    register_post_type('wpstripeco_product', $args);
}

function wp_stripe_checkout_product_columns($columns) {
    unset($columns['title']);
    unset($columns['date']);
    $edited_columns = array(
        'title' => __('Name', 'wp-stripe-checkout'),
        'id' => __('ID', 'wp-stripe-checkout'),
        'price' => __('Price', 'wp-stripe-checkout'),
        'thumbnail' => __('Thumbnail', 'wp-stripe-checkout'),
        'shortcode' => __('Shortcode', 'wp-stripe-checkout'),
        'date' => __('Date', 'wp-stripe-checkout')
    );
    return array_merge($columns, $edited_columns);
}

function wp_stripe_checkout_product_custom_column($column, $post_id) {
    switch ($column) {
        case 'id' :
            echo $post_id;
            break;
        case 'price' :
            echo get_post_meta($post_id, '_wpstripeco_product_price', true);
            break;
        case 'thumbnail' :
            $thumbnail_url = get_the_post_thumbnail_url($post_id);
            echo ($thumbnail_url) ? '<img width="50" height="50" src='.esc_url($thumbnail_url).'>' : '';
            break;
        case 'shortcode' :
            echo '<code>[wp_stripe_checkout id="'.$post_id.'"]</code>';
            break;
    }
}

function wpstripeco_product_meta_boxes($post){
    $post_type = 'wpstripeco_product';
    /** Product Data **/
    add_meta_box('wpstripeco_product_data', __('Product Data'),  'wpstripeco_render_product_data_meta_box', $post_type, 'normal', 'high');
}

function wpstripeco_render_product_data_meta_box($post){
    $post_id = $post->ID;
    //echo '<p>post id: '.$post_id.'</p>';
    $product_price = get_post_meta($post_id, '_wpstripeco_product_price', true);
    if(!isset($product_price) || !is_numeric($product_price) || $product_price < 0.1){
        $product_price = '';
    }
    $button_text = get_post_meta($post_id, '_wpstripeco_product_button_text', true);
    if(!isset($button_text) || empty($button_text)){
        $button_text = '';
    }
    $button_image = get_post_meta($post_id, '_wpstripeco_product_button_image', true);
    if(!isset($button_image) || empty($button_image)){
        $button_image = '';
    }
    /*
    $success_url = get_post_meta($post_id, '_wpstripeco_product_success_url', true);
    if(!isset($success_url) || empty($success_url)){
        $success_url = '';
    }
    $cancel_url = get_post_meta($post_id, '_wpstripeco_product_cancel_url', true);
    if(!isset($cancel_url) || empty($cancel_url)){
        $cancel_url = '';
    }
    */
    $billing_address_collection = get_post_meta($post_id, '_wpstripeco_product_billing_address_collection', true);
    if(!isset($billing_address_collection) || empty($billing_address_collection)){
        $billing_address_collection = '';
    }
    $phone_number_collection = get_post_meta($post_id, '_wpstripeco_product_phone_number_collection', true);
    if(!isset($phone_number_collection) || empty($phone_number_collection)){
        $phone_number_collection = '';
    }
    $shipping_address_collection = get_post_meta($post_id, '_wpstripeco_product_shipping_address_collection', true);
    if(!isset($shipping_address_collection) || empty($shipping_address_collection)){
        $shipping_address_collection = '';
    }
    $stripe_shipping_rate_id = get_post_meta($post_id, '_wpstripeco_product_stripe_shipping_rate_id', true);
    if(!isset($stripe_shipping_rate_id) || empty($stripe_shipping_rate_id)){
        $stripe_shipping_rate_id = '';
    }
    $allow_promotion_codes = get_post_meta($post_id, '_wpstripeco_product_allow_promotion_codes', true);
    if(!isset($allow_promotion_codes) || empty($allow_promotion_codes)){
        $allow_promotion_codes = '';
    }
    /*
    $output = '<label for="_wpstripeco_product_price">'.__('Price', 'wp-stripe-checkout').'</label>';
    $output .= '<input type="text" name="_wpstripeco_product_price" id="_wpstripeco_product_price" value="'.esc_attr($product_price).'" class="regular-text">';
    $output .= '<p class="description">'.__('Enter a value greater than 0 e.g. 2.99', 'wp-stripe-checkout').'</p>';
    echo $output;
    */
    ?>
    <table>
        <tbody>
            <tr>
                <td valign="top">
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_product_price"><?php _e('Price', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_product_price" type="text" id="_wpstripeco_product_price" value="<?php echo esc_attr($product_price); ?>" class="regular-text">
                                    <p class="description"><?php printf(__('Enter a numeric value greater than %s. e.g. %s.', 'wp-stripe-checkout'), '0', '2.99');?></p></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_product_button_text"><?php _e('Button Text', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_product_button_text" type="text" id="_wpstripeco_product_button_text" value="<?php echo esc_attr($button_text); ?>" class="regular-text">
                                    <p class="description"><?php _e('Enter a text for the button. If the field is not empty the plugin will use a plain button for this product. The default text is "Buy Now".', 'wp-stripe-checkout');?></p></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_product_button_image"><?php _e('Button Image', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_product_button_image" type="text" id="_wpstripeco_product_button_image" value="<?php echo esc_url($button_image); ?>" class="large-text" placeholder="https://example.com/wp-content/uploads/button.png">
                                    <p class="description"><?php _e('Enter a URL to an image that will act as the button. If the field is not empty the plugin will use the specified button image for this product.', 'wp-stripe-checkout');?></p></td>
                            </tr>
                            <!--
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_product_success_url"><?php _e('Success URL', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_product_success_url" type="text" id="_wpstripeco_product_success_url" value="<?php echo esc_url($success_url); ?>" class="large-text" placeholder="https://example.com/success/">
                                    <p class="description"><?php _e('The URL to which the customer will be redirected after a successful payment.', 'wp-stripe-checkout');?></p></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_product_cancel_url"><?php _e('Cancel URL', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_product_cancel_url" type="text" id="_wpstripeco_product_cancel_url" value="<?php echo esc_url($cancel_url); ?>" class="large-text" placeholder="https://example.com/cancel/">
                                    <p class="description"><?php _e('The URL to which the customer will be directed if they decide to cancel payment and return to your website.', 'wp-stripe-checkout');?></p></td>
                            </tr>
                            -->
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_product_billing_address_collection"><?php _e('Billing Address Collection', 'wp-stripe-checkout');?></label></th>
                                <td>
                                <select name="_wpstripeco_product_billing_address_collection" id="_wpstripeco_product_billing_address_collection">
                                    <option <?php echo ($billing_address_collection === 'auto')?'selected="selected"':'';?> value="auto"><?php _e('Auto', 'wp-stripe-checkout')?></option>
                                    <option <?php echo ($billing_address_collection === 'required')?'selected="selected"':'';?> value="required"><?php _e('Required', 'wp-stripe-checkout')?></option>
                                </select>
                                <p class="description"><?php _e("Specify how Checkout should collect the customer's billing address.", 'wp-stripe-checkout')?></p>    
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e('Phone Number Collection', 'wp-stripe-checkout');?></th>
                                <td> 
                                    <fieldset>
                                        <legend class="screen-reader-text"><span>Phone Number Collection</span></legend>
                                        <label for="_wpstripeco_product_phone_number_collection">
                                            <input name="_wpstripeco_product_phone_number_collection" type="checkbox" id="_wpstripeco_product_phone_number_collection" <?php if ($phone_number_collection == '1') echo ' checked="checked"'; ?> value="1">
                                            <?php _e('Enable phone number collection at checkout', 'wp-stripe-checkout');?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e('Shipping Address Collection', 'wp-stripe-checkout');?></th>
                                <td> 
                                    <fieldset>
                                        <legend class="screen-reader-text"><span>Shipping Address Collection</span></legend>
                                        <label for="_wpstripeco_product_shipping_address_collection">
                                            <input name="_wpstripeco_product_shipping_address_collection" type="checkbox" id="_wpstripeco_product_shipping_address_collection" <?php if ($shipping_address_collection == '1') echo ' checked="checked"'; ?> value="1">
                                            <?php _e('Enable shipping address collection at checkout', 'wp-stripe-checkout');?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="_wpstripeco_product_stripe_shipping_rate_id"><?php _e('Stripe Shipping Rate ID', 'wp-stripe-checkout');?></label></th>
                                <td><input name="_wpstripeco_product_stripe_shipping_rate_id" type="text" id="_wpstripeco_product_stripe_shipping_rate_id" value="<?php echo esc_attr($stripe_shipping_rate_id); ?>" class="regular-text">
                                    <p class="description"><?php printf(__('The ID of the shipping rate configured in your Stripe account. e.g. %s.', 'wp-stripe-checkout'), 'shr_1MuqbvCW3vOdLdEXy3Bh7Lts');?></p></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e('Allow Promotion Codes', 'wp-stripe-checkout');?></th>
                                <td> 
                                    <fieldset>
                                        <legend class="screen-reader-text"><span>Allow Promotion Codes</span></legend>
                                        <label for="_wpstripeco_product_allow_promotion_codes">
                                            <input name="_wpstripeco_product_allow_promotion_codes" type="checkbox" id="_wpstripeco_product_allow_promotion_codes" <?php if ($allow_promotion_codes == '1') echo ' checked="checked"'; ?> value="1">
                                            <?php _e('Allow user redeemable promotion codes at checkout', 'wp-stripe-checkout');?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody> 
    </table>
    <?php
    wp_nonce_field(basename(__FILE__), 'wpstripeco_product_data_meta_box_nonce');
}

function wpstripeco_product_data_meta_box_save($post_id, $post){
    if(!isset($_POST['wpstripeco_product_data_meta_box_nonce']) || !wp_verify_nonce($_POST['wpstripeco_product_data_meta_box_nonce'], basename(__FILE__))){
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
    if(isset($_POST['_wpstripeco_product_price']) && is_numeric($_POST['_wpstripeco_product_price']) && $_POST['_wpstripeco_product_price'] > 0){
        $product_price = sanitize_text_field($_POST['_wpstripeco_product_price']);
        update_post_meta($post_id, '_wpstripeco_product_price', $product_price);
    }
    if(isset($_POST['_wpstripeco_product_button_text'])){
        $button_text = sanitize_text_field($_POST['_wpstripeco_product_button_text']);
        update_post_meta($post_id, '_wpstripeco_product_button_text', $button_text);
    }
    if(isset($_POST['_wpstripeco_product_button_image'])){
        $button_image = esc_url_raw($_POST['_wpstripeco_product_button_image']);
        update_post_meta($post_id, '_wpstripeco_product_button_image', $button_image);
    }
    /*
    if(isset($_POST['_wpstripeco_product_success_url']) && filter_var($_POST['_wpstripeco_product_success_url'], FILTER_VALIDATE_URL)){
        $success_url = esc_url_raw($_POST['_wpstripeco_product_success_url']);
        update_post_meta($post_id, '_wpstripeco_product_success_url', $success_url);
    }
    if(isset($_POST['_wpstripeco_product_cancel_url']) && filter_var($_POST['_wpstripeco_product_cancel_url'], FILTER_VALIDATE_URL)){
        $cancel_url = esc_url_raw($_POST['_wpstripeco_product_cancel_url']);
        update_post_meta($post_id, '_wpstripeco_product_cancel_url', $cancel_url);
    }
    */
    if(isset($_POST['_wpstripeco_product_billing_address_collection']) && !empty($_POST['_wpstripeco_product_billing_address_collection'])){
        $billing_address_collection = sanitize_text_field($_POST['_wpstripeco_product_billing_address_collection']);
        update_post_meta($post_id, '_wpstripeco_product_billing_address_collection', $billing_address_collection);
    }
    $phone_number_collection = (isset($_POST['_wpstripeco_product_phone_number_collection']) && $_POST['_wpstripeco_product_phone_number_collection'] == '1') ? '1' : '';
    update_post_meta($post_id, '_wpstripeco_product_phone_number_collection', $phone_number_collection);
    $shipping_address_collection = (isset($_POST['_wpstripeco_product_shipping_address_collection']) && $_POST['_wpstripeco_product_shipping_address_collection'] == '1') ? '1' : '';
    update_post_meta($post_id, '_wpstripeco_product_shipping_address_collection', $shipping_address_collection);
    if(isset($_POST['_wpstripeco_product_stripe_shipping_rate_id']) && !empty($_POST['_wpstripeco_product_stripe_shipping_rate_id'])){
        $stripe_shipping_rate_id = sanitize_text_field($_POST['_wpstripeco_product_stripe_shipping_rate_id']);
        update_post_meta($post_id, '_wpstripeco_product_stripe_shipping_rate_id', $stripe_shipping_rate_id);
    }
    $allow_promotion_codes = (isset($_POST['_wpstripeco_product_allow_promotion_codes']) && $_POST['_wpstripeco_product_allow_promotion_codes'] == '1') ? '1' : '';
    update_post_meta($post_id, '_wpstripeco_product_allow_promotion_codes', $allow_promotion_codes);
}

add_action('save_post_wpstripeco_product', 'wpstripeco_product_data_meta_box_save', 10, 2 );
