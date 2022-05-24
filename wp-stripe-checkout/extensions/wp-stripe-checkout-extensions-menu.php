<?php

function wp_stripe_checkout_display_extensions_menu()
{
    echo '<div class="wrap">';
    echo '<h2>' .__('WP Stripe Checkout Extensions', 'wp-paypal') . '</h2>';
    
    $extensions_data = array();

    $extension_1 = array(
        'name' => 'Variable Price',
        'thumbnail' => WP_STRIPE_CHECKOUT_URL.'/extensions/images/wp-stripe-checkout-variable-price.png',
        'description' => 'Let buyers set the amount they will pay',
        'page_url' => 'https://noorsplugin.com/how-to-add-a-price-field-to-a-stripe-payment-button/',
    );
    array_push($extensions_data, $extension_1);
    
    $extension_2 = array(
        'name' => 'Variable Quantity',
        'thumbnail' => WP_STRIPE_CHECKOUT_URL.'/extensions/images/wp-stripe-checkout-variable-quantity.png',
        'description' => 'Let buyers set the quantity they will purchase',
        'page_url' => 'https://noorsplugin.com/how-to-add-a-quantity-field-to-a-stripe-payment-button/',
    );
    array_push($extensions_data, $extension_2);
    
    //Display the list
    foreach ($extensions_data as $extension) {
        ?>
        <div class="wp_stripe_checkout_extensions_item_canvas">
        <div class="wp_stripe_checkout_extensions_item_thumb">
            <img src="<?php echo esc_url($extension['thumbnail']);?>" alt="<?php echo esc_attr($extension['name']);?>">
        </div>
        <div class="wp_stripe_checkout_extensions_item_body">
        <div class="wp_stripe_checkout_extensions_item_name">
            <a href="<?php echo esc_url($extension['page_url']);?>" target="_blank"><?php echo esc_html($extension['name']);?></a>
        </div>
        <div class="wp_stripe_checkout_extensions_item_description">
        <?php echo esc_html($extension['description']);?>
        </div>
        <div class="wp_stripe_checkout_extensions_item_details_link">
        <a href="<?php echo esc_url($extension['page_url']);?>" class="wp_stripe_checkout_extensions_view_details" target="_blank">View Details</a>
        </div>    
        </div>
        </div>
        <?php
    } 
    echo '</div>';//end of wrap
}
