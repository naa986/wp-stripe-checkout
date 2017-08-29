<?php
/*
  Plugin Name: WP Stripe Checkout
  Version: 1.0.2
  Plugin URI: https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/
  Author: naa986
  Author URI: https://noorsplugin.com/
  Description: Accept payments with the Stripe payment gateway in WordPress
  Text Domain: wp-stripe-checkout
  Domain Path: /languages
 */

if (!defined('ABSPATH'))
    exit;

class WP_STRIPE_CHECKOUT {
    
    var $plugin_version = '1.0.2';
    var $plugin_url;
    var $plugin_path;
    
    function __construct() {
        define('WP_STRIPE_CHECKOUT_VERSION', $this->plugin_version);
        define('WP_STRIPE_CHECKOUT_SITE_URL', site_url());
        define('WP_STRIPE_CHECKOUT_HOME_URL', home_url());
        define('WP_STRIPE_CHECKOUT_URL', $this->plugin_url());
        define('WP_STRIPE_CHECKOUT_PATH', $this->plugin_path());
        $options = wp_stripe_checkout_get_option();
        if (isset($options['enable_debug']) && $options['enable_debug']=="1") {
            define('WP_STRIPE_CHECKOUT_DEBUG', true);
        } else {
            define('WP_STRIPE_CHECKOUT_DEBUG', false);
        }
        if (isset($options['stripe_testmode']) && $options['stripe_testmode']=="1") {
            define('WP_STRIPE_CHECKOUT_TESTMODE', true);
        } else {
            define('WP_STRIPE_CHECKOUT_TESTMODE', false);
        }
        define('WP_STRIPE_CHECKOUT_DEBUG_LOG_PATH', $this->debug_log_path());
        $this->plugin_includes();
        $this->loader_operations();
    }

    function plugin_includes() {
        include_once('wp-stripe-checkout-order.php');
        include_once('wp-stripe-checkout-process.php');
    }

    function loader_operations() {
        add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
        if (is_admin()) {
            add_filter('plugin_action_links', array($this, 'add_plugin_action_links'), 10, 2);
        }
        add_action('admin_notices', array($this, 'admin_notice'));
        add_action('wp_enqueue_scripts', array($this, 'plugin_scripts'));
        add_action('admin_menu', array($this, 'add_options_menu'));
        add_action('init', array($this, 'plugin_init'));
        add_filter('manage_wpstripeco_order_posts_columns', 'wp_stripe_checkout_order_columns');
        add_action('manage_wpstripeco_order_posts_custom_column', 'wp_stripe_checkout_custom_column', 10, 2);
        add_shortcode('wp_stripe_checkout', 'wp_stripe_checkout_button_handler');
    }

    function plugins_loaded_handler() {  //Runs when plugins_loaded action gets fired
        load_plugin_textdomain( 'wp-stripe-checkout', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
    }

    function admin_notice() {
        if (WP_STRIPE_CHECKOUT_DEBUG) {  //debug is enabled. Check to make sure log file is writable
            $real_file = WP_STRIPE_CHECKOUT_DEBUG_LOG_PATH;
            if (!is_writeable($real_file)) {
                echo '<div class="updated"><p>' . __('WP Stripe Checkout Debug log file is not writable. Please check to make sure that it has the correct file permission (ideally 644). Otherwise the plugin will not be able to write to the log file. The log file (log.txt) can be found in the root directory of the plugin - ', 'wp-stripe-checkout') . '<code>' . WP_STRIPE_CHECKOUT_URL . '</code></p></div>';
            }
        }
    }

    function plugin_init() {
        //register order type
        wp_stripe_checkout_register_order_type();
        //process order
        wp_stripe_checkout_process_order();
    }

    function plugin_scripts() {
        if (!is_admin()) {
            
        }
    }

    function plugin_url() {
        if ($this->plugin_url)
            return $this->plugin_url;
        return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
    }

    function plugin_path() {
        if ($this->plugin_path)
            return $this->plugin_path;
        return $this->plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
    }

    function debug_log_path() {
        return WP_STRIPE_CHECKOUT_PATH . '/log.txt';
    }

    function add_plugin_action_links($links, $file) {
        if ($file == plugin_basename(dirname(__FILE__) . '/main.php')) {
            $links[] = '<a href="options-general.php?page=wp-stripe-checkout-settings">'.__('Settings', 'wp-stripe-checkout').'</a>';
        }
        return $links;
    }

    function add_options_menu() {
        if (is_admin()) {
            add_submenu_page('edit.php?post_type=wpstripeco_order', __('Settings', 'wp-stripe-checkout'), __('Settings', 'wp-stripe-checkout'), 'manage_options', 'wp-stripe-checkout-settings', array($this, 'options_page'));
            add_submenu_page('edit.php?post_type=wpstripeco_order', __('Debug', 'wp-stripe-checkout'), __('Debug', 'wp-stripe-checkout'), 'manage_options', 'wp-stripe-checkout-debug', array($this, 'debug_page'));
        }
    }

    function options_page() {
        $plugin_tabs = array(
            'wp-stripe-checkout-settings' => __('General', 'wp-stripe-checkout')
        );
        echo '<div class="wrap"><h2>'.__('WP Stripe Checkout', 'wp-stripe-checkout').' v' . WP_STRIPE_CHECKOUT_VERSION . '</h2>';
        $url = 'https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/';
        $link_msg = sprintf( wp_kses( __( 'Please visit the <a target="_blank" href="%s">Stripe Checkout</a> documentation page for setup instructions.', 'wp-stripe-checkout' ), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( $url ) );
        echo '<div class="update-nag">'.$link_msg.'</div>';
        echo '<div id="poststuff"><div id="post-body">';

        if (isset($_GET['page'])) {
            $current = $_GET['page'];
            if (isset($_GET['action'])) {
                $current .= "&action=" . $_GET['action'];
            }
        }
        $content = '';
        $content .= '<h2 class="nav-tab-wrapper">';
        foreach ($plugin_tabs as $location => $tabname) {
            if ($current == $location) {
                $class = ' nav-tab-active';
            } else {
                $class = '';
            }
            $content .= '<a class="nav-tab' . $class . '" href="?post_type=wpstripeco_order&page=' . $location . '">' . $tabname . '</a>';
        }
        $content .= '</h2>';
        echo $content;

        $this->general_settings();

        echo '</div></div>';
        echo '</div>';
    }

    function general_settings() {
        if (isset($_POST['wp_stripe_checkout_update_settings'])) {
            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'wp_stripe_checkout_general_settings')) {
                wp_die('Error! Nonce Security Check Failed! please save the settings again.');
            }
            $stripe_testmode = (isset($_POST["stripe_testmode"]) && $_POST["stripe_testmode"] == '1') ? '1' : '';
            $stripe_test_secret_key = '';
            if(isset($_POST['stripe_test_secret_key']) && !empty($_POST['stripe_test_secret_key'])){
                $stripe_test_secret_key = sanitize_text_field($_POST['stripe_test_secret_key']);
            }
            $stripe_test_publishable_key = '';
            if(isset($_POST['stripe_test_publishable_key']) && !empty($_POST['stripe_test_publishable_key'])){
                $stripe_test_publishable_key = sanitize_text_field($_POST['stripe_test_publishable_key']);
            }
            $stripe_secret_key = '';
            if(isset($_POST['stripe_secret_key']) && !empty($_POST['stripe_secret_key'])){
                $stripe_secret_key = sanitize_text_field($_POST['stripe_secret_key']);
            }
            $stripe_publishable_key = '';
            if(isset($_POST['stripe_publishable_key']) && !empty($_POST['stripe_publishable_key'])){
                $stripe_publishable_key = sanitize_text_field($_POST['stripe_publishable_key']);
            }
            $stripe_currency_code = '';
            if(isset($_POST['stripe_currency_code']) && !empty($_POST['stripe_currency_code'])){
                $stripe_currency_code = sanitize_text_field($_POST['stripe_currency_code']);
            }
            $return_url = '';
            if(isset($_POST['return_url']) && !empty($_POST['return_url'])){
                $return_url = sanitize_text_field($_POST['return_url']);
            }
            $stripe_options = array();
            $stripe_options['stripe_testmode'] = $stripe_testmode;
            $stripe_options['stripe_test_secret_key'] = $stripe_test_secret_key;
            $stripe_options['stripe_test_publishable_key'] = $stripe_test_publishable_key;
            $stripe_options['stripe_secret_key'] = $stripe_secret_key;
            $stripe_options['stripe_publishable_key'] = $stripe_publishable_key;
            $stripe_options['stripe_currency_code'] = $stripe_currency_code;
            $stripe_options['return_url'] = $return_url;
            wp_stripe_checkout_update_option($stripe_options);
            echo '<div id="message" class="updated fade"><p><strong>';
            echo __('Settings Saved', 'wp-stripe-checkout').'!';
            echo '</strong></p></div>';
        }
        
        $stripe_options = wp_stripe_checkout_get_option();
        $api_keys_url = "https://dashboard.stripe.com/account/apikeys";
        $api_keys_link = sprintf(wp_kses(__('You can get it from your <a target="_blank" href="%s">stripe account</a>.', 'wp-stripe-checkout'), array('a' => array('href' => array(), 'target' => array()))), esc_url($api_keys_url));
        
        $currency_check_url = "https://support.stripe.com/questions/which-currencies-does-stripe-support";
        $currency_check_link = sprintf(wp_kses(__('See <a target="_blank" href="%s">which currencies are supported by stripe</a> for details.', 'wp-stripe-checkout'), array('a' => array('href' => array(), 'target' => array()))), esc_url($currency_check_url));
        ?>
        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <?php wp_nonce_field('wp_stripe_checkout_general_settings'); ?>

            <table class="form-table">

                <tbody>

                    <tr valign="top">
                        <th scope="row"><?Php _e('Test Mode', 'wp-stripe-checkout');?></th>
                        <td> <fieldset><legend class="screen-reader-text"><span>Test Mode</span></legend><label for="stripe_testmode">
                                    <input name="stripe_testmode" type="checkbox" id="stripe_testmode" <?php if ($stripe_options['stripe_testmode'] == '1') echo ' checked="checked"'; ?> value="1">
                                    <?Php _e('Check this option if you want to place the Stripe payment gateway in test mode using test API keys.', 'wp-stripe-checkout');?></label>
                            </fieldset></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="stripe_test_secret_key"><?Php _e('Test Secret Key', 'wp-stripe-checkout');?></label></th>
                        <td><input name="stripe_test_secret_key" type="text" id="stripe_test_secret_key" value="<?php echo $stripe_options['stripe_test_secret_key']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('Your Test Secret Key.', 'wp-stripe-checkout').' '.$api_keys_link;?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="stripe_test_publishable_key"><?Php _e('Test Publishable Key', 'wp-stripe-checkout');?></label></th>
                        <td><input name="stripe_test_publishable_key" type="text" id="stripe_test_publishable_key" value="<?php echo $stripe_options['stripe_test_publishable_key']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('Your Test Publishable Key.', 'wp-stripe-checkout').' '.$api_keys_link;?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="stripe_secret_key"><?Php _e('Live Secret Key', 'wp-stripe-checkout');?></label></th>
                        <td><input name="stripe_secret_key" type="text" id="stripe_secret_key" value="<?php echo $stripe_options['stripe_secret_key']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('Your Secret Key.', 'wp-stripe-checkout').' '.$api_keys_link;?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="stripe_publishable_key"><?Php _e('Live Publishable Key', 'wp-stripe-checkout');?></label></th>
                        <td><input name="stripe_publishable_key" type="text" id="stripe_publishable_key" value="<?php echo $stripe_options['stripe_publishable_key']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('Your Live Publishable Key.', 'wp-stripe-checkout').' '.$api_keys_link;?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="stripe_currency_code"><?Php _e('Currency Code', 'wp-stripe-checkout');?></label></th>
                        <td><input name="stripe_currency_code" type="text" id="stripe_currency_code" value="<?php echo $stripe_options['stripe_currency_code']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('The currency of the payment.', 'wp-stripe-checkout').' '.$currency_check_link;?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="return_url"><?Php _e('Return URL', 'wp-stripe-checkout');?></label></th>
                        <td><input name="return_url" type="text" id="return_url" value="<?php echo $stripe_options['return_url']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('The page URL to which the customer will be redirected after a successful payment.', 'wp-stripe-checkout');?></p></td>
                    </tr>

                </tbody>

            </table>

            <p class="submit"><input type="submit" name="wp_stripe_checkout_update_settings" id="wp_stripe_checkout_update_settings" class="button button-primary" value="<?Php _e('Save Changes', 'wp-stripe-checkout');?>"></p></form>

        <?php
    }

    function debug_page() {
        ?>
        <div class="wrap">
            <h2><?Php _e('WP Stripe Checkout Debug Log', 'wp-stripe-checkout');?></h2>
            <div id="poststuff">
                <div id="post-body">
                    <?php
                    if (isset($_POST['wp_stripe_checkout_update_log_settings'])) {
                        $nonce = $_REQUEST['_wpnonce'];
                        if (!wp_verify_nonce($nonce, 'wp_stripe_checkout_debug_log_settings')) {
                            wp_die('Error! Nonce Security Check Failed! please save the settings again.');
                        }
                        $options = array();
                        $options['enable_debug'] = (isset($_POST["enable_debug"]) && $_POST["enable_debug"] == '1') ? '1' : '';
                        wp_stripe_checkout_update_option($options);
                        echo '<div id="message" class="updated fade"><p>'.__('Settings Saved', 'wp-stripe-checkout').'!</p></div>';
                    }
                    if (isset($_POST['wp_stripe_checkout_reset_log'])) {
                        $nonce = $_REQUEST['_wpnonce'];
                        if (!wp_verify_nonce($nonce, 'wp_stripe_checkout_reset_log_settings')) {
                            wp_die('Error! Nonce Security Check Failed! please save the settings again.');
                        }
                        if (wp_stripe_checkout_reset_log()) {
                            echo '<div id="message" class="updated fade"><p>'.__('Debug log file has been reset', 'wp-stripe-checkout').'!</p></div>';
                        } else {
                            echo '<div id="message" class="error"><p>'.__('Debug log file could not be reset', 'wp-stripe-checkout').'!</p></div>';
                        }
                    }
                    $real_file = WP_STRIPE_CHECKOUT_DEBUG_LOG_PATH;
                    $content = file_get_contents($real_file);
                    $content = esc_textarea($content);
                    $options = wp_stripe_checkout_get_option();
                    ?>
                    <div id="template"><textarea cols="70" rows="25" name="wp_stripe_checkout_log" id="wp_stripe_checkout_log"><?php echo $content; ?></textarea></div>                     
                    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                        <?php wp_nonce_field('wp_stripe_checkout_debug_log_settings'); ?>
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><?Php _e('Enable Debug', 'wp-stripe-checkout');?></th>
                                    <td> <fieldset><legend class="screen-reader-text"><span>Enable Debug</span></legend><label for="enable_debug">
                                                <input name="enable_debug" type="checkbox" id="enable_debug" <?php if ($options['enable_debug'] == '1') echo ' checked="checked"'; ?> value="1">
                                                <?Php _e('Check this option if you want to enable debug', 'wp-stripe-checkout');?></label>
                                        </fieldset></td>
                                </tr>

                            </tbody>

                        </table>
                        <p class="submit"><input type="submit" name="wp_stripe_checkout_update_log_settings" id="wp_stripe_checkout_update_log_settings" class="button button-primary" value="<?Php _e('Save Changes', 'wp-stripe-checkout');?>"></p>
                    </form>
                    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                        <?php wp_nonce_field('wp_stripe_checkout_reset_log_settings'); ?>                            
                        <p class="submit"><input type="submit" name="wp_stripe_checkout_reset_log" id="wp_stripe_checkout_reset_log" class="button" value="<?Php _e('Reset Log', 'wp-stripe-checkout');?>"></p>
                    </form>
                </div>         
            </div>
        </div>
        <?php
    }

}

$GLOBALS['wp_stripe_checkout'] = new WP_STRIPE_CHECKOUT();

function wp_stripe_checkout_button_handler($atts) {
    
    if(!isset($atts['item_name']) || empty($atts['item_name'])){
        return __('item_name cannot be left empty', 'wp-stripe-checkout');
    }
    if(!isset($atts['amount']) || !is_numeric($atts['amount'])){
        return __('You need to provide a valid price amount for your item', 'wp-stripe-checkout');
    }
    $description = '';
    if(isset($atts['description']) && !empty($atts['description'])){
        $description = $atts['description'];
    }
    $options = wp_stripe_checkout_get_option();
    $key = $options['stripe_publishable_key'];
    if(WP_STRIPE_CHECKOUT_TESTMODE){
        $key = $options['stripe_test_publishable_key'];
    }
    $atts['key'] = $key;
    //$atts['image'] = "https://stripe.com/img/documentation/checkout/marketplace.png";
    $currency = $options['stripe_currency_code'];
    if(!isset($atts['currency']) || empty($atts['currency'])){
        $atts['currency'] = $currency;
    }
    $transient_name = 'wpstripecheckout-amount-' . sanitize_title_with_dashes($atts['item_name']);
    set_transient( $transient_name, $atts['amount'], 4 * 3600 );
    $transient_name = 'wpstripecheckout-currency-' . sanitize_title_with_dashes($atts['item_name']);
    set_transient( $transient_name, $atts['currency'], 4 * 3600 );
    $atts['amount'] = $atts['amount'] * 100;
    //unset item_name because Stripe doesn't recognize it
    $item_name = $atts['item_name'];
    unset($atts['item_name']);
    //
    $button_code = '<form action="" method="POST">';
    $button_code .= '<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"';
    foreach ($atts as $key => $value) {
        $button_code .= 'data-' . $key . '="' . $value . '"';
    }
    $button_code .= '></script>';
    $button_code .= wp_nonce_field('wp_stripe_checkout', '_wpnonce', true, false);
    $button_code .= '<input type="hidden" value="'.$item_name.'" name="item_name" />';
    $button_code .= '<input type="hidden" value="'.$atts['amount'].'" name="item_amount" />';
    $button_code .= '<input type="hidden" value="'.$atts['currency'].'" name="item_currency" />';
    $button_code .= '<input type="hidden" value="'.$description.'" name="item_description" />';
    $button_code .= '<input type="hidden" value="1" name="wp_stripe_checkout" />';
    $button_code .= '</form>';
    return $button_code;
}

function wp_stripe_checkout_get_option(){
    $options = get_option('wp_stripe_checkout_options');
    if(!is_array($options)){
        $options = wp_stripe_checkout_get_empty_options_array();
    }
    return $options;
}

function wp_stripe_checkout_update_option($new_options){
    $empty_options = wp_stripe_checkout_get_empty_options_array();
    $options = wp_stripe_checkout_get_option();
    if(is_array($options)){
        $current_options = array_merge($empty_options, $options);
        $updated_options = array_merge($current_options, $new_options);
        update_option('wp_stripe_checkout_options', $updated_options);
    }
    else{
        $updated_options = array_merge($empty_options, $new_options);
        update_option('wp_stripe_checkout_options', $updated_options);
    }
}

function wp_stripe_checkout_get_empty_options_array(){
    $options = array();
    $options['stripe_testmode'] = '';
    $options['stripe_test_secret_key'] = '';
    $options['stripe_test_publishable_key'] = '';
    $options['stripe_secret_key'] = '';
    $options['stripe_publishable_key'] = '';
    $options['stripe_currency_code'] = '';
    $options['return_url'] = '';
    $options['enable_debug'] = '';
    return $options;
}

function wp_stripe_checkout_debug_log($msg, $success, $end = false) {
    if (!WP_STRIPE_CHECKOUT_DEBUG) {
        return;
    }
    $date_time = date('F j, Y g:i a');//the_date('F j, Y g:i a', '', '', FALSE);
    $text = '[' . $date_time . '] - ' . (($success) ? 'SUCCESS :' : 'FAILURE :') . $msg . "\n";
    if ($end) {
        $text .= "\n------------------------------------------------------------------\n\n";
    }
    // Write to log.txt file
    $fp = fopen(WP_STRIPE_CHECKOUT_DEBUG_LOG_PATH, 'a');
    fwrite($fp, $text);
    fclose($fp);  // close file
}

function wp_stripe_checkout_debug_log_array($array_msg, $success, $end = false) {
    if (!WP_STRIPE_CHECKOUT_DEBUG) {
        return;
    }
    $date_time = date('F j, Y g:i a');//the_date('F j, Y g:i a', '', '', FALSE);
    $text = '[' . $date_time . '] - ' . (($success) ? 'SUCCESS :' : 'FAILURE :') . "\n";
    ob_start();
    print_r($array_msg);
    $var = ob_get_contents();
    ob_end_clean();
    $text .= $var;
    if ($end) {
        $text .= "\n------------------------------------------------------------------\n\n";
    }
    // Write to log.txt file
    $fp = fopen(WP_STRIPE_CHECKOUT_DEBUG_LOG_PATH, 'a');
    fwrite($fp, $text);
    fclose($fp);  // close filee
}

function wp_stripe_checkout_reset_log() {
    $log_reset = true;
    $date_time = date('F j, Y g:i a');//the_date('F j, Y g:i a', '', '', FALSE);
    $text = '[' . $date_time . '] - SUCCESS : Log reset';
    $text .= "\n------------------------------------------------------------------\n\n";
    $fp = fopen(WP_STRIPE_CHECKOUT_DEBUG_LOG_PATH, 'w');
    if ($fp != FALSE) {
        @fwrite($fp, $text);
        @fclose($fp);
    } else {
        $log_reset = false;
    }
    return $log_reset;
}
