<?php

function wp_stripe_checkout_display_addons_menu()
{
    echo '<div class="wrap">';
    echo '<h2>' .__('WP Stripe Checkout Add-ons', 'wp-paypal') . '</h2>';
    
    $addons_data = array();

    $addon_1 = array(
        'name' => 'Variable Price',
        'thumbnail' => WP_STRIPE_CHECKOUT_URL.'/addons/images/wp-stripe-checkout-variable-price.png',
        'description' => 'Let buyers set the amount they will pay',
        'page_url' => 'https://noorsplugin.com/how-to-add-a-price-field-to-a-stripe-payment-button/',
    );
    array_push($addons_data, $addon_1);
    
    $addon_2 = array(
        'name' => 'Variable Quantity',
        'thumbnail' => WP_STRIPE_CHECKOUT_URL.'/addons/images/wp-stripe-checkout-variable-quantity.png',
        'description' => 'Let buyers set the quantity they will purchase',
        'page_url' => 'https://noorsplugin.com/how-to-add-a-quantity-field-to-a-stripe-payment-button/',
    );
    array_push($addons_data, $addon_2);
    
    $addon_3 = array(
        'name' => 'Submit Type',
        'thumbnail' => WP_STRIPE_CHECKOUT_URL.'/addons/images/wp-stripe-checkout-submit-type.png',
        'description' => 'Customize the type of the submit button that appears on the Stripe payment page',
        'page_url' => 'https://noorsplugin.com/how-to-customize-the-type-of-the-stripe-payment-page-button/',
    );
    array_push($addons_data, $addon_3);
    
    $addon_4 = array(
        'name' => 'Terms of Service',
        'thumbnail' => WP_STRIPE_CHECKOUT_URL.'/addons/images/wp-stripe-checkout-terms-of-service.png',
        'description' => 'Show a terms of service checkbox on the Stripe payment page',
        'page_url' => 'https://noorsplugin.com/how-to-show-a-terms-of-service-checkbox-on-the-stripe-payment-page/',
    );
    array_push($addons_data, $addon_4);
    
    $addon_5 = array(
        'name' => 'Payment Link Email',
        'thumbnail' => WP_STRIPE_CHECKOUT_URL.'/addons/images/wp-stripe-checkout-payment-link-email.png',
        'description' => 'Collect email addresses for payment link buttons and prefill on the Stripe payment page',
        'page_url' => 'https://noorsplugin.com/how-to-prefill-email-addresses-for-stripe-payment-link-buttons/',
    );
    array_push($addons_data, $addon_5);
    
    //Display the list
    foreach ($addons_data as $addon) {
        ?>
        <div class="wp_stripe_checkout_addons_item_canvas">
        <div class="wp_stripe_checkout_addons_item_thumb">
            <img src="<?php echo esc_url($addon['thumbnail']);?>" alt="<?php echo esc_attr($addon['name']);?>">
        </div>
        <div class="wp_stripe_checkout_addons_item_body">
        <div class="wp_stripe_checkout_addons_item_name">
            <a href="<?php echo esc_url($addon['page_url']);?>" target="_blank"><?php echo esc_html($addon['name']);?></a>
        </div>
        <div class="wp_stripe_checkout_addons_item_description">
        <?php echo esc_html($addon['description']);?>
        </div>
        <div class="wp_stripe_checkout_addons_item_details_link">
        <a href="<?php echo esc_url($addon['page_url']);?>" class="wp_stripe_checkout_addons_view_details" target="_blank">View Details</a>
        </div>    
        </div>
        </div>
        <?php
    } 
    echo '</div>';//end of wrap
}
