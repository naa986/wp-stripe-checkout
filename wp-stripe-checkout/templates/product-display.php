<?php
function wp_stripe_checkout_button_get_display_template1($button_code, $atts) {
    if(!isset($atts['template']) || $atts['template'] != '1'){
        return $button_code;
    }
    $post = get_post($atts['id']);
    if(!$post){
        return __('Invalid product ID', 'wp-stripe-checkout');
    }
    $options = wp_stripe_checkout_get_option();
    $thumbnail_url = get_the_post_thumbnail_url($atts['id']);
    $product_image = ($thumbnail_url) ? '<img class="attachment-post-thumbnail size-post-thumbnail wp-post-image" src='.esc_url($thumbnail_url).'>' : '';
    $product_name = get_the_title($atts['id']);
    $product_price = get_post_meta($atts['id'], '_wpstripeco_product_price', true);
    $enable_variable_pricing = get_post_meta($atts['id'], '_wpstripeco_product_enable_variable_pricing', true);
    if(defined('WPSTRIPECO_VARIABLE_PRICE_VERSION') && isset($enable_variable_pricing) && $enable_variable_pricing == '1'){
        $product_price = '';
    }
    $template_code = '';
    $template_code .= '<table class="wpsc_template1_table">';
    $template_code .= '<tr class="wpsc_template1_tr">';
    //$template_code .= '<div class="wpsc_template1">';
    if(isset($product_image) && !empty($product_image)){
        $template_code .= '<td class="wpsc_template1_product_thumbnail_td">';
        $template_code .= '<div class="wpsc_template1_product_thumbnail">'.$product_image.'</div>';
        $template_code .= '</td>';
    }
    $template_code .= '<td class="wpsc_template1_product_summary_td">';
    $template_code .= '<div class="wpsc_template1_product_summary">';
    $template_code .= '<div class="wpsc_template1_product_name">'.esc_html($post->post_title).'</div>';
    if(isset($product_price) && !empty($product_price)){
        $template_code .= '<div class="wpsc_template1_product_price">'.$product_price.' '.$options['stripe_currency_code'].'</div>';
    }
    $product_description = wpautop(do_shortcode($post->post_content));//apply_filters('the_content', $post->post_content);
    $template_code .= '<div class="wpsc_template1_product_description">'.$product_description.'</div>';
    $template_code .= '<div class="wpsc_template1_product_button">'.$button_code.'</div>';
    $template_code .= '</div>';
    //$template_code .= '<div style="clear:both;"></div>';
    $template_code .= '</td>';
    $template_code .= '</tr>';
    $template_code .= '</table>';
    //$template_code .= '</div';
    $button_code = $template_code;
    return $button_code;
}
