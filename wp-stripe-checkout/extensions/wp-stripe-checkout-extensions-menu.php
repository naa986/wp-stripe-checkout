<?php

function wp_stripe_checkout_display_extensions_menu()
{
    echo '<div class="wrap">';
    echo '<h2>' .__('WP Stripe Checkout Extensions', 'wp-paypal') . '</h2>';
    echo '<link type="text/css" rel="stylesheet" href="'.WP_STRIPE_CHECKOUT_URL.'/extensions/wp-stripe-checkout-extensions-menu.css" />' . "\n";
    
    $extensions_data = array();

    $extension_1 = array(
        'name' => 'Variable Price',
        'thumbnail' => WP_STRIPE_CHECKOUT_URL.'/extensions/images/wp-stripe-checkout-variable-price.png',
        'description' => 'Let buyers set the amount they will pay',
        'page_url' => 'https://noorsplugin.com/how-to-add-a-price-field-to-a-stripe-payment-button/',
    );
    array_push($extensions_data, $extension_1);
    
    //Display the list
    $output = '';
    foreach ($extensions_data as $extension) {
        $output .= '<div class="wp_stripe_checkout_extensions_item_canvas">';

        $output .= '<div class="wp_stripe_checkout_extensions_item_thumb">';
        $img_src = $extension['thumbnail'];
        $output .= '<img src="' . $img_src . '" alt="' . $extension['name'] . '">';
        $output .= '</div>'; //end thumbnail

        $output .='<div class="wp_stripe_checkout_extensions_item_body">';
        $output .='<div class="wp_stripe_checkout_extensions_item_name">';
        $output .= '<a href="' . $extension['page_url'] . '" target="_blank">' . $extension['name'] . '</a>';
        $output .='</div>'; //end name

        $output .='<div class="wp_stripe_checkout_extensions_item_description">';
        $output .= $extension['description'];
        $output .='</div>'; //end description

        $output .='<div class="wp_stripe_checkout_extensions_item_details_link">';
        $output .='<a href="'.$extension['page_url'].'" class="wp_stripe_checkout_extensions_view_details" target="_blank">View Details</a>';
        $output .='</div>'; //end detils link      
        $output .='</div>'; //end body

        $output .= '</div>'; //end canvas
    }
    echo $output;
    
    echo '</div>';//end of wrap
}
