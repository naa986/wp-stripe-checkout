<?php
/*
  Plugin Name: WP Stripe Checkout
  Version: 1.2.2.23
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
    
    var $plugin_version = '1.2.2.23';
    var $db_version = '1.0.10';
    var $plugin_url;
    var $plugin_path;
    
    function __construct() {
        define('WP_STRIPE_CHECKOUT_VERSION', $this->plugin_version);
        define('WP_STRIPE_CHECKOUT_DB_VERSION', $this->db_version);
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
        include_once('wp-stripe-checkout-product.php');
        include_once('wp-stripe-checkout-process.php');
        include_once('wp-stripe-checkout-process-webhook.php');
        include_once('templates/product-display.php');
        include_once('class-wp-sc-stripe-api.php');
        if(is_admin()){
            include_once('addons/wp-stripe-checkout-addons-menu.php');
        }
    }

    function loader_operations() {
        register_activation_hook( __FILE__, array($this, 'activate_handler') );
        add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
        if (is_admin()) {
            add_filter('plugin_action_links', array($this, 'add_plugin_action_links'), 10, 2);
        }
        add_action('admin_notices', array($this, 'admin_notice'));
        //add_action('wp_head', array($this, 'wp_head'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'plugin_scripts'));
        add_action('admin_menu', array($this, 'add_options_menu'));
        add_action('init', array($this, 'plugin_init'));
        add_filter('manage_wpstripeco_order_posts_columns', 'wp_stripe_checkout_order_columns');
        add_action('manage_wpstripeco_order_posts_custom_column', 'wp_stripe_checkout_custom_column', 10, 2);
        add_filter('manage_wpstripeco_product_posts_columns', 'wp_stripe_checkout_product_columns');
        add_action('manage_wpstripeco_product_posts_custom_column', 'wp_stripe_checkout_product_custom_column', 10, 2);
        add_action('add_meta_boxes_wpstripeco_product', 'wpstripeco_product_meta_boxes');
        add_shortcode('wp_stripe_checkout', 'wp_stripe_checkout_button_handler');
        add_shortcode('wp_stripe_checkout_v3', 'wp_stripe_checkout_v3_button_handler');
        add_shortcode('wp_stripe_checkout_session', 'wp_stripe_checkout_session_button_handler');
    }

    function plugins_loaded_handler() {  //Runs when plugins_loaded action gets fired
        load_plugin_textdomain( 'wp-stripe-checkout', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
        $this->check_upgrade();
    }
    
    function activate_handler() {
        add_option('wp_stripe_checkout_verify_front_end_nonces', '1');
        wp_stripe_checkout_set_default_email_options();
        add_option('wp_stripe_checkout_db_version', $this->db_version);
    }

    function check_upgrade() {
        if (is_admin()) {
            $db_version = get_option('wp_stripe_checkout_db_version');
            if (!isset($db_version) || $db_version != $this->db_version) {
                $options = wp_stripe_checkout_get_option();
                if(isset($options['return_url']) && !empty($options['return_url'])){
                    $options['success_url'] = $options['return_url'];
                    wp_stripe_checkout_update_option($options);
                }
                add_option('wp_stripe_checkout_verify_front_end_nonces', '1');
                wp_stripe_checkout_set_default_email_options();
                update_option('wp_stripe_checkout_db_version', $this->db_version);
            }
        }
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
        //register product type
        wp_stripe_checkout_register_product_type();
        //process order
        wp_stripe_checkout_process_order();
        //process session button
        wp_stripe_checkout_process_session_button();
        //process button
        wp_stripe_checkout_process_button();
        //process webhook
        wp_stripe_checkout_process_webhook();
    }
    
    function enqueue_admin_scripts($hook) {
        if('wpstripeco_order_page_wp-stripe-checkout-addons' != $hook) {
            return;
        }
        wp_register_style('wp-stripe-checkout-addon-menu', WP_STRIPE_CHECKOUT_URL.'/addons/wp-stripe-checkout-addons-menu.css');
        wp_enqueue_style('wp-stripe-checkout-addon-menu');
    }

    function plugin_scripts() {
        if (!is_admin()) {
            global $post;
            if(!is_a($post, 'WP_Post')){
                return;
            }
            $is_css_required = false;
            $is_js_required = false;
            if(has_shortcode($post->post_content, 'wp_stripe_checkout')){
                $is_css_required = true;
                $is_js_required = true;
            }
            if(has_shortcode($post->post_content, 'wp_stripe_checkout_v3')){
                $is_js_required = true;
            }
            if($is_css_required){
                wp_register_style('wp-stripe-checkout', WP_STRIPE_CHECKOUT_URL.'/css/style.css');
                wp_enqueue_style('wp-stripe-checkout');
            }
            if($is_js_required){
                wp_register_script('wp-stripe-checkout', 'https://js.stripe.com/v3', array(), null);
                wp_enqueue_script('wp-stripe-checkout');
            }
        }
    }
    
    function wp_head(){

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
            $links[] = '<a href="edit.php?post_type=wpstripeco_order&page=wp-stripe-checkout-settings">'.__('Settings', 'wp-stripe-checkout').'</a>';
        }
        return $links;
    }

    function add_options_menu() {
        if (is_admin()) {
            add_submenu_page('edit.php?post_type=wpstripeco_order', __('Settings', 'wp-stripe-checkout'), __('Settings', 'wp-stripe-checkout'), 'manage_options', 'wp-stripe-checkout-settings', array($this, 'options_page'));
            add_submenu_page('edit.php?post_type=wpstripeco_order', __('Debug', 'wp-stripe-checkout'), __('Debug', 'wp-stripe-checkout'), 'manage_options', 'wp-stripe-checkout-debug', array($this, 'debug_page'));
            add_submenu_page('edit.php?post_type=wpstripeco_order', __('Add-ons', 'wp-stripe-checkout'), __('Add-ons', 'wp-stripe-checkout'), 'manage_options', 'wp-stripe-checkout-addons', 'wp_stripe_checkout_display_addons_menu');
            global $submenu;
            unset($submenu['edit.php?post_type=wpstripeco_order'][10]);
        }
    }

    function options_page() {
        $plugin_tabs = array(
            'wp-stripe-checkout-settings' => __('General', 'wp-stripe-checkout'),
            'wp-stripe-checkout-settings&tab=emails' => __('Emails', 'wp-stripe-checkout')
        );
        echo '<div class="wrap"><h2>'.__('WP Stripe Checkout', 'wp-stripe-checkout').' v' . WP_STRIPE_CHECKOUT_VERSION . '</h2>';
        $url = 'https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/';
        $link_msg = sprintf(__( 'Please visit the <a target="_blank" href="%s">Stripe Checkout</a> documentation page for setup instructions.', 'wp-stripe-checkout' ), esc_url($url));
        $allowed_html_tags = array(
            'a' => array(
                'href' => array(),
                'target' => array()
            )
        );
        echo '<div class="update-nag">'.wp_kses($link_msg, $allowed_html_tags).'</div>';
        $current = '';
        $tab = '';
        if (isset($_GET['page'])) {
            $current = sanitize_text_field($_GET['page']);
            if (isset($_GET['tab'])) {
                $tab = sanitize_text_field($_GET['tab']);
                $current .= "&tab=" . $tab;
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
        $allowed_html_tags = array(
            'a' => array(
                'href' => array(),
                'class' => array()
            ),
            'h2' => array(
                'href' => array(),
                'class' => array()
            )
        );
        echo wp_kses($content, $allowed_html_tags);
        
        if(!empty($tab))
        { 
            switch($tab)
            {
               case 'emails':
                   $this->email_settings();
                   break;
            }
        }
        else
        {
            $this->general_settings();
        }

        echo '</div>';
    }

    function general_settings() {
        if (isset($_POST['wp_stripe_checkout_update_settings'])) {
            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'wp_stripe_checkout_general_settings')) {
                wp_die(__('Error! Nonce Security Check Failed! please save the general settings again.', 'wp-stripe-checkout'));
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
            $success_url = '';
            if(isset($_POST['success_url']) && !empty($_POST['success_url'])){
                $success_url = esc_url_raw($_POST['success_url']);
            }
            $cancel_url = '';
            if(isset($_POST['cancel_url']) && !empty($_POST['cancel_url'])){
                $cancel_url = esc_url_raw($_POST['cancel_url']);
            }
            $verify_front_end_nonces = (isset($_POST['verify_front_end_nonces']) && $_POST['verify_front_end_nonces'] == '1') ? '1' : '';
            update_option('wp_stripe_checkout_verify_front_end_nonces', $verify_front_end_nonces);
            $stripe_options = array();
            $stripe_options['stripe_testmode'] = $stripe_testmode;
            $stripe_options['stripe_test_secret_key'] = $stripe_test_secret_key;
            $stripe_options['stripe_test_publishable_key'] = $stripe_test_publishable_key;
            $stripe_options['stripe_secret_key'] = $stripe_secret_key;
            $stripe_options['stripe_publishable_key'] = $stripe_publishable_key;
            $stripe_options['stripe_currency_code'] = $stripe_currency_code;
            $stripe_options['success_url'] = $success_url;
            $stripe_options['cancel_url'] = $cancel_url;
            wp_stripe_checkout_update_option($stripe_options);
            echo '<div id="message" class="updated fade"><p><strong>';
            echo __('Settings Saved', 'wp-stripe-checkout').'!';
            echo '</strong></p></div>';
        }
        
        $stripe_options = wp_stripe_checkout_get_option();
        $verify_front_end_nonces = get_option('wp_stripe_checkout_verify_front_end_nonces');
        if(!isset($verify_front_end_nonces) || empty($verify_front_end_nonces)){
            $verify_front_end_nonces = '';
        }
        $api_keys_url = "https://dashboard.stripe.com/account/apikeys";
        $api_keys_link = sprintf(__('You can get it from your <a target="_blank" href="%s">stripe account</a>.', 'wp-stripe-checkout'), esc_url($api_keys_url));
        
        $currency_check_url = "https://support.stripe.com/questions/which-currencies-does-stripe-support";
        $currency_check_link = sprintf(__('See <a target="_blank" href="%s">which currencies are supported by stripe</a> for details.', 'wp-stripe-checkout'), esc_url($currency_check_url));
        
        $webhook_doc_url = "https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/";
        $webhook_doc_url = sprintf(__('Learn how to configure it <a target="_blank" href="%s">here</a>.', 'wp-stripe-checkout'), esc_url($webhook_doc_url));
        
        $allowed_html_tags = array(
            'a' => array(
                'href' => array(),
                'target' => array()
            )
        );
        ?>
        <table class="wpsc-general-settings-table">
            <tbody>
                <tr>
                    <td valign="top">
                        <form method="post" action="">
                            <?php wp_nonce_field('wp_stripe_checkout_general_settings'); ?>

                            <table class="form-table">

                                <tbody>

                                    <tr valign="top">
                                        <th scope="row"><?php _e('Test Mode', 'wp-stripe-checkout');?></th>
                                        <td> <fieldset><legend class="screen-reader-text"><span>Test Mode</span></legend><label for="stripe_testmode">
                                                    <input name="stripe_testmode" type="checkbox" id="stripe_testmode" <?php if ($stripe_options['stripe_testmode'] == '1') echo ' checked="checked"'; ?> value="1">
                                                    <?php _e('Check this option if you want to place the Stripe payment gateway in test mode using test API keys.', 'wp-stripe-checkout');?></label>
                                            </fieldset></td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="stripe_test_secret_key"><?php _e('Test Secret Key', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="stripe_test_secret_key" type="text" id="stripe_test_secret_key" value="<?php echo esc_attr($stripe_options['stripe_test_secret_key']); ?>" class="regular-text">
                                            <p class="description"><?php echo __('Your Test Secret Key.', 'wp-stripe-checkout').' '.wp_kses($api_keys_link, $allowed_html_tags);?></p></td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="stripe_test_publishable_key"><?php _e('Test Publishable Key', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="stripe_test_publishable_key" type="text" id="stripe_test_publishable_key" value="<?php echo esc_attr($stripe_options['stripe_test_publishable_key']); ?>" class="regular-text">
                                            <p class="description"><?php echo __('Your Test Publishable Key.', 'wp-stripe-checkout').' '.wp_kses($api_keys_link, $allowed_html_tags);?></p></td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="stripe_secret_key"><?php _e('Live Secret Key', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="stripe_secret_key" type="text" id="stripe_secret_key" value="<?php echo esc_attr($stripe_options['stripe_secret_key']); ?>" class="regular-text">
                                            <p class="description"><?php echo __('Your Secret Key.', 'wp-stripe-checkout').' '.wp_kses($api_keys_link, $allowed_html_tags);?></p></td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="stripe_publishable_key"><?php _e('Live Publishable Key', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="stripe_publishable_key" type="text" id="stripe_publishable_key" value="<?php echo esc_attr($stripe_options['stripe_publishable_key']); ?>" class="regular-text">
                                            <p class="description"><?php echo __('Your Live Publishable Key.', 'wp-stripe-checkout').' '.wp_kses($api_keys_link, $allowed_html_tags);?></p></td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="stripe_currency_code"><?php _e('Currency Code', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="stripe_currency_code" type="text" id="stripe_currency_code" value="<?php echo esc_attr($stripe_options['stripe_currency_code']); ?>" class="regular-text">
                                            <p class="description"><?php echo __('The currency of the payment.', 'wp-stripe-checkout').' '.wp_kses($currency_check_link, $allowed_html_tags);?></p></td>
                                    </tr>
                                    
                                    <tr valign="top">
                                        <th scope="row"><label for="success_url"><?php _e('Success URL', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="success_url" type="text" id="success_url" value="<?php echo esc_url($stripe_options['success_url']); ?>" class="regular-text">
                                            <p class="description"><?php echo __('The page URL to which the customer will be redirected after a successful payment.', 'wp-stripe-checkout');?></p></td>
                                    </tr>
                                    
                                    <tr valign="top">
                                        <th scope="row"><label for="cancel_url"><?php _e('Cancel URL', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="cancel_url" type="text" id="cancel_url" value="<?php echo esc_url($stripe_options['cancel_url']); ?>" class="regular-text">
                                            <p class="description"><?php echo __('The page URL to which the customer will be redirected if they decide to cancel payment and return to your website.', 'wp-stripe-checkout');?></p></td>
                                    </tr>
                                    
                                    <tr valign="top">
                                        <th scope="row"><label><?php _e('Stripe Webhook URL', 'wp-stripe-checkout');?></label></th>
                                        <td><code><?php echo esc_url(home_url('/')."?wp_stripe_co_webhook=1"); ?></code>
                                            <p class="description"><?php echo __('The URL of your site where Stripe will send notification of an event.', 'wp-stripe-checkout').' '.wp_kses($webhook_doc_url, $allowed_html_tags);?></p></td>
                                    </tr>
                                    
                                    <tr valign="top">
                                        <th scope="row"><?php _e('Verify Front-end Nonces', 'wp-stripe-checkout');?></th>
                                        <td> <fieldset><legend class="screen-reader-text"><span>Verify Front-end Nonces</span></legend><label for="verify_front_end_nonces">
                                                    <input name="verify_front_end_nonces" type="checkbox" id="verify_front_end_nonces" <?php if ($verify_front_end_nonces == '1') echo ' checked="checked"'; ?> value="1">
                                                    <?php _e("Check this option if you want to verify nonces on the front end. Nonces are WordPress's security tokens that can help protect buttons from certain types of misuse.", 'wp-stripe-checkout');?></label>
                                            </fieldset></td>
                                    </tr>

                                </tbody>

                            </table>

                            <p class="submit"><input type="submit" name="wp_stripe_checkout_update_settings" id="wp_stripe_checkout_update_settings" class="button button-primary" value="<?php _e('Save Changes', 'wp-stripe-checkout');?>"></p></form>
                    </td>
                    <td valign="top" style="width: 300px">
                        <div style="background: #ffc; border: 1px solid #333; margin: 2px; padding: 3px 15px">
                        <h3><?php _e('Need Help?', 'wp-stripe-checkout')?></h3>
                        <ol>
                        <li><?php printf(__('Use the <a href="%s">Debug</a> menu for diagnostics.', 'wp-stripe-checkout'), 'edit.php?post_type=wpstripeco_order&page=wp-stripe-checkout-debug');?></li>
                        <li><?php printf(__('Check out the <a target="_blank" href="%s">support forum</a> and <a target="_blank" href="%s">FAQ</a>.', 'wp-stripe-checkout'), 'https://wordpress.org/support/plugin/wp-stripe-checkout', 'https://wordpress.org/plugins/wp-stripe-checkout/#faq');?></li>
                        <li><?php printf(__('Visit the <a target="_blank" href="%s">plugin homepage</a>.', 'wp-stripe-checkout'), 'https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/');?></li>
                        </ol>
                        <h3><?php _e('Rate This Plugin', 'wp-stripe-checkout')?></h3>
                        <p><?php printf(__('Please <a target="_blank" href="%s">rate us</a> and give feedback.', 'wp-stripe-checkout'), 'https://wordpress.org/support/plugin/wp-stripe-checkout/reviews?rate=5#new-post');?></p>
                        </div>
                    </td>
                </tr>
            </tbody> 
        </table>
        <?php
    }
    
    function email_settings() {
        if (isset($_POST['wp_stripe_checkout_update_email_settings'])) {
            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'wp_stripe_checkout_email_settings')) {
                wp_die(__('Error! Nonce Security Check Failed! please save the email settings again.', 'wp-stripe-checkout'));
            }
            $_POST = stripslashes_deep($_POST);
            $email_from_name = '';
            if(isset($_POST['email_from_name']) && !empty($_POST['email_from_name'])){
                $email_from_name = sanitize_text_field($_POST['email_from_name']);
            }
            $email_from_address= '';
            if(isset($_POST['email_from_address']) && !empty($_POST['email_from_address'])){
                $email_from_address = sanitize_email($_POST['email_from_address']);
            }
            $purchase_email_enabled = (isset($_POST["purchase_email_enabled"]) && $_POST["purchase_email_enabled"] == '1') ? '1' : '';
            $purchase_email_subject = '';
            if(isset($_POST['purchase_email_subject']) && !empty($_POST['purchase_email_subject'])){
                $purchase_email_subject = sanitize_text_field($_POST['purchase_email_subject']);
            }
            $purchase_email_type = '';
            if(isset($_POST['purchase_email_type']) && !empty($_POST['purchase_email_type'])){
                $purchase_email_type = sanitize_text_field($_POST['purchase_email_type']);
            }
            $purchase_email_body = '';
            if(isset($_POST['purchase_email_body']) && !empty($_POST['purchase_email_body'])){
                $purchase_email_body = wp_kses_post($_POST['purchase_email_body']);
            }
            $sale_notification_email_enabled = (isset($_POST["sale_notification_email_enabled"]) && $_POST["sale_notification_email_enabled"] == '1') ? '1' : '';
            $sale_notification_email_recipient = '';
            if(isset($_POST['sale_notification_email_recipient']) && !empty($_POST['sale_notification_email_recipient'])){
                $sale_notification_email_recipient = sanitize_email($_POST['sale_notification_email_recipient']);
            }
            $sale_notification_email_subject = '';
            if(isset($_POST['sale_notification_email_subject']) && !empty($_POST['sale_notification_email_subject'])){
                $sale_notification_email_subject = sanitize_text_field($_POST['sale_notification_email_subject']);
            }
            $sale_notification_email_type = '';
            if(isset($_POST['sale_notification_email_type']) && !empty($_POST['sale_notification_email_type'])){
                $sale_notification_email_type = sanitize_text_field($_POST['sale_notification_email_type']);
            }
            $sale_notification_email_body = '';
            if(isset($_POST['sale_notification_email_body']) && !empty($_POST['sale_notification_email_body'])){
                $sale_notification_email_body = wp_kses_post($_POST['sale_notification_email_body']);
            }
            $stripe_options = array();
            $stripe_options['email_from_name'] = $email_from_name;
            $stripe_options['email_from_address'] = $email_from_address;
            $stripe_options['purchase_email_enabled'] = $purchase_email_enabled;
            $stripe_options['purchase_email_subject'] = $purchase_email_subject;
            $stripe_options['purchase_email_type'] = $purchase_email_type;
            $stripe_options['purchase_email_body'] = $purchase_email_body;
            $stripe_options['sale_notification_email_enabled'] = $sale_notification_email_enabled;
            $stripe_options['sale_notification_email_recipient'] = $sale_notification_email_recipient;
            $stripe_options['sale_notification_email_subject'] = $sale_notification_email_subject;
            $stripe_options['sale_notification_email_type'] = $sale_notification_email_type;
            $stripe_options['sale_notification_email_body'] = $sale_notification_email_body;
            wp_stripe_checkout_update_email_option($stripe_options);
            echo '<div id="message" class="updated fade"><p><strong>';
            echo __('Settings Saved', 'wp-stripe-checkout').'!';
            echo '</strong></p></div>';
        }
        
        $stripe_options = wp_stripe_checkout_get_email_option();
        $email_tags_url = "https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/";
        $email_tags_link = sprintf(__('You can find the full list of available email tags <a target="_blank" href="%s">here</a>.', 'wp-stripe-checkout'), esc_url($email_tags_url));
        $allowed_html_tags = array(
            'a' => array(
                'href' => array(),
                'target' => array()
            )
        );
        ?>
        <table class="wpsc-email-settings-table">
            <tbody>
                <tr>
                    <td valign="top">
                        <form method="post" action="">
                            <?php wp_nonce_field('wp_stripe_checkout_email_settings'); ?>

                            <h2><?php _e('Email Sender Options', 'wp-stripe-checkout');?></h2>
                            <table class="form-table">
                                <tbody>                   
                                    <tr valign="top">
                                        <th scope="row"><label for="email_from_name"><?php _e('From Name', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="email_from_name" type="text" id="email_from_name" value="<?php echo esc_attr($stripe_options['email_from_name']); ?>" class="regular-text">
                                            <p class="description"><?php _e('The sender name that appears in outgoing emails. Leave empty to use the default.', 'wp-stripe-checkout');?></p></td>
                                    </tr>                
                                    <tr valign="top">
                                        <th scope="row"><label for="email_from_address"><?php _e('From Email Address', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="email_from_address" type="text" id="email_from_address" value="<?php echo esc_attr($stripe_options['email_from_address']); ?>" class="regular-text">
                                            <p class="description"><?php _e('The sender email that appears in outgoing emails. Leave empty to use the default.', 'wp-stripe-checkout');?></p></td>
                                    </tr>
                                </tbody>
                            </table>
                            <h2><?php _e('Purchase Receipt Email', 'wp-stripe-checkout');?></h2>
                            <p><?php _e('A purchase receipt email is sent to the customer after completion of a successful purchase', 'wp-stripe-checkout');?></p>
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><?php _e('Enable/Disable', 'wp-stripe-checkout');?></th>
                                        <td> <fieldset><legend class="screen-reader-text"><span>Enable/Disable</span></legend><label for="purchase_email_enabled">
                                                    <input name="purchase_email_enabled" type="checkbox" id="purchase_email_enabled" <?php if ($stripe_options['purchase_email_enabled'] == '1') echo ' checked="checked"'; ?> value="1">
                                                    <?php _e('Enable this email notification', 'wp-stripe-checkout');?></label>
                                            </fieldset></td>
                                    </tr>                   
                                    <tr valign="top">
                                        <th scope="row"><label for="purchase_email_subject"><?php _e('Subject', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="purchase_email_subject" type="text" id="purchase_email_subject" value="<?php echo esc_attr($stripe_options['purchase_email_subject']); ?>" class="regular-text">
                                            <p class="description"><?php _e('The subject line for the purchase receipt email.', 'wp-stripe-checkout');?></p></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="purchase_email_type"><?php _e('Email Type', 'wp-stripe-checkout');?></label></th>
                                        <td>
                                        <select name="purchase_email_type" id="purchase_email_type">
                                            <option <?php echo ($stripe_options['purchase_email_type'] === 'plain')?'selected="selected"':'';?> value="plain"><?php _e('Plain Text', 'wp-stripe-checkout')?></option>
                                            <option <?php echo ($stripe_options['purchase_email_type'] === 'html')?'selected="selected"':'';?> value="html"><?php _e('HTML', 'wp-stripe-checkout')?></option>
                                        </select>
                                        <p class="description"><?php _e('The content type of the purchase receipt email.', 'wp-stripe-checkout')?></p>
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="purchase_email_body"><?php _e('Email Body', 'wp-stripe-checkout');?></label></th>
                                        <td><?php wp_editor($stripe_options['purchase_email_body'], 'purchase_email_body', array('textarea_name' => 'purchase_email_body'));?>
                                            <p class="description"><?php echo __('The main content of the purchase receipt email.', 'wp-stripe-checkout').' '.wp_kses($email_tags_link, $allowed_html_tags);?></p></td>
                                    </tr>
                                </tbody>
                            </table>
                            <h2><?php _e('Sale Notification Email', 'wp-stripe-checkout');?></h2>
                            <p><?php _e('A sale notification email is sent to the chosen recipient after completion of a successful purchase', 'wp-stripe-checkout');?></p>
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><?php _e('Enable/Disable', 'wp-stripe-checkout');?></th>
                                        <td> <fieldset><legend class="screen-reader-text"><span>Enable/Disable</span></legend><label for="sale_notification_email_enabled">
                                                    <input name="sale_notification_email_enabled" type="checkbox" id="sale_notification_email_enabled" <?php if ($stripe_options['sale_notification_email_enabled'] == '1') echo ' checked="checked"'; ?> value="1">
                                                    <?php _e('Enable this email notification', 'wp-stripe-checkout');?></label>
                                            </fieldset></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="sale_notification_email_recipient"><?php _e('Recipient', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="sale_notification_email_recipient" type="text" id="sale_notification_email_recipient" value="<?php echo esc_attr($stripe_options['sale_notification_email_recipient']); ?>" class="regular-text">
                                            <p class="description"><?php _e('The email address that should receive a notification anytime a sale is made.', 'wp-stripe-checkout');?></p></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="sale_notification_email_subject"><?php _e('Subject', 'wp-stripe-checkout');?></label></th>
                                        <td><input name="sale_notification_email_subject" type="text" id="sale_notification_email_subject" value="<?php echo esc_attr($stripe_options['sale_notification_email_subject']); ?>" class="regular-text">
                                            <p class="description"><?php _e('The subject line for the sale notification email.', 'wp-stripe-checkout');?></p></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="sale_notification_email_type"><?php _e('Email Type', 'wp-stripe-checkout');?></label></th>
                                        <td>
                                        <select name="sale_notification_email_type" id="sale_notification_email_type">
                                            <option <?php echo ($stripe_options['sale_notification_email_type'] === 'plain')?'selected="selected"':'';?> value="plain"><?php _e('Plain Text', 'wp-stripe-checkout')?></option>
                                            <option <?php echo ($stripe_options['sale_notification_email_type'] === 'html')?'selected="selected"':'';?> value="html"><?php _e('HTML', 'wp-stripe-checkout')?></option>
                                        </select>
                                        <p class="description"><?php _e('The content type of the sale notification email.', 'wp-stripe-checkout')?></p>
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="sale_notification_email_body"><?php _e('Email Body', 'wp-stripe-checkout');?></label></th>
                                        <td><?php wp_editor($stripe_options['sale_notification_email_body'], 'sale_notification_email_body', array('textarea_name' => 'sale_notification_email_body'));?>
                                            <p class="description"><?php echo __('The main content of the sale notification email.', 'wp-stripe-checkout').' '.wp_kses($email_tags_link, $allowed_html_tags);?></p></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <p class="submit"><input type="submit" name="wp_stripe_checkout_update_email_settings" id="wp_stripe_checkout_update_email_settings" class="button button-primary" value="<?php _e('Save Changes', 'wp-stripe-checkout');?>"></p></form>
                    </td>
                    <td valign="top" style="width: 300px">
                        <div style="background: #ffc; border: 1px solid #333; margin: 2px; padding: 3px 15px">
                        <h3><?php _e('Need Help?', 'wp-stripe-checkout')?></h3>
                        <ol>
                        <li><?php printf(__('Use the <a href="%s">Debug</a> menu for diagnostics.', 'wp-stripe-checkout'), 'edit.php?post_type=wpstripeco_order&page=wp-stripe-checkout-debug');?></li>
                        <li><?php printf(__('Check out the <a target="_blank" href="%s">support forum</a> and <a target="_blank" href="%s">FAQ</a>.', 'wp-stripe-checkout'), 'https://wordpress.org/support/plugin/wp-stripe-checkout', 'https://wordpress.org/plugins/wp-stripe-checkout/#faq');?></li>
                        <li><?php printf(__('Visit the <a target="_blank" href="%s">plugin homepage</a>.', 'wp-stripe-checkout'), 'https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/');?></li>
                        </ol>
                        <h3><?php _e('Rate This Plugin', 'wp-stripe-checkout')?></h3>
                        <p><?php printf(__('Please <a target="_blank" href="%s">rate us</a> and give feedback.', 'wp-stripe-checkout'), 'https://wordpress.org/support/plugin/wp-stripe-checkout/reviews?rate=5#new-post');?></p>
                        </div>
                    </td>
                </tr>
            </tbody> 
        </table>
        <?php
    }

    function debug_page() {
        ?>
        <div class="wrap">
            <h2><?php _e('WP Stripe Checkout Debug Log', 'wp-stripe-checkout');?></h2>
            <div id="poststuff">
                <div id="post-body">
                    <?php
                    if (isset($_POST['wp_stripe_checkout_update_log_settings'])) {
                        $nonce = $_REQUEST['_wpnonce'];
                        if (!wp_verify_nonce($nonce, 'wp_stripe_checkout_debug_log_settings')) {
                            wp_die(__('Error! Nonce Security Check Failed! please save the debug settings again.', 'wp-stripe-checkout'));
                        }
                        $options = array();
                        $options['enable_debug'] = (isset($_POST["enable_debug"]) && $_POST["enable_debug"] == '1') ? '1' : '';
                        wp_stripe_checkout_update_option($options);
                        echo '<div id="message" class="updated fade"><p>'.__('Settings Saved', 'wp-stripe-checkout').'!</p></div>';
                    }
                    if (isset($_POST['wp_stripe_checkout_reset_log'])) {
                        $nonce = $_REQUEST['_wpnonce'];
                        if (!wp_verify_nonce($nonce, 'wp_stripe_checkout_reset_log_settings')) {
                            wp_die(__('Error! Nonce Security Check Failed! please reset the debug log file again.', 'wp-stripe-checkout'));
                        }
                        if (wp_stripe_checkout_reset_log()) {
                            echo '<div id="message" class="updated fade"><p>'.__('Debug log file has been reset', 'wp-stripe-checkout').'!</p></div>';
                        } else {
                            echo '<div id="message" class="error"><p>'.__('Debug log file could not be reset', 'wp-stripe-checkout').'!</p></div>';
                        }
                    }
                    $real_file = WP_STRIPE_CHECKOUT_DEBUG_LOG_PATH;
                    $content = file_get_contents($real_file);
                    $options = wp_stripe_checkout_get_option();
                    ?>
                    <div id="template"><textarea cols="70" rows="25" name="wp_stripe_checkout_log" id="wp_stripe_checkout_log"><?php echo esc_textarea($content); ?></textarea></div>                     
                    <form method="post" action="">
                        <?php wp_nonce_field('wp_stripe_checkout_debug_log_settings'); ?>
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><?php _e('Enable Debug', 'wp-stripe-checkout');?></th>
                                    <td> <fieldset><legend class="screen-reader-text"><span>Enable Debug</span></legend><label for="enable_debug">
                                                <input name="enable_debug" type="checkbox" id="enable_debug" <?php if ($options['enable_debug'] == '1') echo ' checked="checked"'; ?> value="1">
                                                <?php _e('Check this option if you want to enable debug', 'wp-stripe-checkout');?></label>
                                        </fieldset></td>
                                </tr>

                            </tbody>

                        </table>
                        <p class="submit"><input type="submit" name="wp_stripe_checkout_update_log_settings" id="wp_stripe_checkout_update_log_settings" class="button button-primary" value="<?php _e('Save Changes', 'wp-stripe-checkout');?>"></p>
                    </form>
                    <form method="post" action="">
                        <?php wp_nonce_field('wp_stripe_checkout_reset_log_settings'); ?>                            
                        <p class="submit"><input type="submit" name="wp_stripe_checkout_reset_log" id="wp_stripe_checkout_reset_log" class="button" value="<?php _e('Reset Log', 'wp-stripe-checkout');?>"></p>
                    </form>
                </div>         
            </div>
        </div>
        <?php
    }

}

$GLOBALS['wp_stripe_checkout'] = new WP_STRIPE_CHECKOUT();

function wp_stripe_checkout_button_handler($atts) {
    $atts = array_map('sanitize_text_field', $atts);
    if(!isset($atts['id']) || !is_numeric($atts['id'])){
        return wp_stripe_checkout_legacy_checkout_button_handler($atts);
    }
    $post = get_post($atts['id']);
    if(!$post){
        return __('Invalid product ID', 'wp-stripe-checkout');
    }
    if('wpstripeco_product' != $post->post_type){
        return __('Invalid product type', 'wp-stripe-checkout');
    }
    $options = wp_stripe_checkout_get_option();
    $success_url = $options['success_url'];
    if(!isset($success_url) || empty($success_url)){
        return __('You need to provide a success URL page in the settings', 'wp-stripe-checkout');
    }
    $cancel_url = $options['cancel_url'];
    if(!isset($cancel_url) || empty($cancel_url)){
        return __('You need to provide a cancel URL page in the settings', 'wp-stripe-checkout');
    }
    $button_code = '<form action="" method="post">';
    $button_code .= wp_nonce_field('wp_stripe_checkout_button', '_wp_stripe_checkout_button_nonce', true, false);
    $button_code .= '<input type="hidden" name="wpsc_product_id" value="'.esc_attr($atts['id']).'" />';
    $price_input_code = '';
    $price_input_code = apply_filters('wp_stripe_checkout_button_price', $price_input_code, $button_code, $atts);
    if(!empty($price_input_code)){
        $button_code .= $price_input_code;
    }
    $quantity_input_code = '';
    $quantity_input_code = apply_filters('wp_stripe_checkout_button_quantity', $quantity_input_code, $button_code, $atts);
    if(!empty($quantity_input_code)){
        $button_code .= $quantity_input_code;
    }
    $button_code .= '<input type="hidden" name="wp_stripe_checkout_button_input" value="1" />';
    $button_image = get_post_meta($atts['id'], '_wpstripeco_product_button_image', true);
    if(!isset($button_image) || empty($button_image)){
        $button_text = get_post_meta($atts['id'], '_wpstripeco_product_button_text', true);
        if(!isset($button_text) || empty($button_text)){
            $button_text = 'Buy Now';
        }
        $button_code .= '<input type="submit" value="'.esc_attr($button_text).'" />';
    } 
    else{
        $button_code .= '<input type="image" src="'.esc_url($button_image).'" alt="Submit" />';    
    }
    $button_code .= '</form>';
    //template
    if(isset($atts['template']) && $atts['template'] == '1'){
        $button_code = wp_stripe_checkout_button_get_display_template1($button_code, $atts);
    }
    return $button_code;
}

function wp_stripe_checkout_legacy_checkout_button_handler($atts) {
    $atts = array_map('sanitize_text_field', $atts);
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
    $success_url = '';
    if(isset($atts['success_url'])){ 
        if(!empty($atts['success_url'])){
            $success_url = $atts['success_url'];
        }
        unset($atts['success_url']);
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
    //prefill the email if the user is logged in
    if(is_user_logged_in()){
        $current_user = wp_get_current_user();
        $atts['email'] = $current_user->user_email;
    }
    /*
    $transient_name = 'wpstripecheckout-amount-' . sanitize_title_with_dashes($atts['item_name']);
    set_transient( $transient_name, $atts['amount'], 4 * 3600 );
    $transient_name = 'wpstripecheckout-currency-' . sanitize_title_with_dashes($atts['item_name']);
    set_transient( $transient_name, $atts['currency'], 4 * 3600 );
    */
    $price = $atts['amount']; //actual item price
    $atts['amount'] = $atts['amount'] * 100;  //the price supported by Stripe
    //unset item_name because Stripe doesn't recognize it
    $item_name = $atts['item_name'];
    unset($atts['item_name']);
    //
    $button_code = '<form action="" method="POST">';
    $button_code .= '<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"';
    foreach ($atts as $key => $value) {
        $button_code .= 'data-'.$key.'="'.esc_js($value).'"';
    }
    $button_code .= '></script>';
    $button_code .= wp_nonce_field('wp_stripe_checkout_legacy', '_wpnonce', true, false);
    $button_code .= '<input type="hidden" value="'.esc_attr($item_name).'" name="item_name" />';
    $button_code .= '<input type="hidden" value="'.esc_attr($price).'" name="item_price" />';
    $button_code .= '<input type="hidden" value="'.esc_attr($atts['amount']).'" name="item_amount" />';
    $button_code .= '<input type="hidden" value="'.esc_attr($atts['currency']).'" name="item_currency" />';
    $button_code .= '<input type="hidden" value="'.esc_attr($description).'" name="item_description" />';
    if(!empty($success_url)){
        $button_code .= '<input type="hidden" value="'.esc_url($success_url).'" name="success_url" />';
    }
    $button_code .= '<input type="hidden" value="1" name="wp_stripe_checkout_legacy" />';
    $button_code .= '</form>';
    return $button_code;
}

function wp_stripe_checkout_v3_button_handler($atts) {
    $atts = array_map('sanitize_text_field', $atts);
    $button_text = 'Buy Now';
    if(isset($atts['button_text']) && !empty($atts['button_text'])){
        $button_text = $atts['button_text'];
    }
    $identifier = '';
    if(!isset($atts['price']) || empty($atts['price'])){  //new API
        //check for existing items that may still use sku
        if(!isset($atts['sku']) || empty($atts['sku'])){
            return __('You need to provide a price ID or sku in the shortcode', 'wp-stripe-checkout');
        }
        else{
            $identifier = $atts['sku'];
        }
    }
    else{
        $identifier = $atts['price'];
    }
    $data_arr = array();
    $line_items_arr = array();
    $line_items_arr['price'] = $identifier;
    $options = wp_stripe_checkout_get_option();
    $success_url = $options['success_url'];
    if(isset($atts['success_url']) && !empty($atts['success_url'])){
        $success_url = $atts['success_url'];
    }
    if(!isset($success_url) || empty($success_url)){
        return __('You need to provide a success URL page in the settings', 'wp-stripe-checkout');
    }
    $data_arr['successUrl'] = $success_url;
    $cancel_url = $options['cancel_url'];
    if(isset($atts['cancel_url']) && !empty($atts['cancel_url'])){
        $cancel_url = $atts['cancel_url'];
    }
    if(!isset($cancel_url) || empty($cancel_url)){
        return __('You need to provide a cancel URL page in the settings', 'wp-stripe-checkout');
    }
    $data_arr['cancelUrl'] = $cancel_url;
    $key = $options['stripe_publishable_key'];
    if(WP_STRIPE_CHECKOUT_TESTMODE){
        $key = $options['stripe_test_publishable_key'];
    }
    if(!isset($key) || empty($key)){
        return __('You need to provide your publishable key in the settings', 'wp-stripe-checkout');
    }
    //mode
    $mode = 'payment';
    if(isset($atts['mode']) && 'subscription' == $atts['mode']){
        $mode = 'subscription';
    }
    $data_arr['mode'] = $mode;
    //billingAddressCollection
    $billingAddressCollection = '';
    if(isset($atts['billing_address']) && !empty($atts['billing_address'])){
        //$billingAddressCollection = "billingAddressCollection: '".$atts['billing_address']."',";
        $data_arr['billingAddressCollection'] = $atts['billing_address'];
    }
    //shippingAddressCollection
    $shippingAddressCollection = '';
    if(isset($atts['shipping_address']) && !empty($atts['shipping_address'])){
        $allowed_countries = wp_stripe_checkout_get_shipping_countries_array();       
        if(isset($atts['shipping_countries']) && !empty($atts['shipping_countries'])){
            $allowed_countries_str = $atts['shipping_countries'];
            $allowed_countries_str = str_replace("'", '', $allowed_countries_str);  //backwards compatibility
            $allowed_countries = array_map('trim', explode(',', $allowed_countries_str));
        }
        //$shippingAddressCollection = "shippingAddressCollection: {allowedCountries: [".$allowed_countries."]},";
        $data_arr['shippingAddressCollection'] = array('allowedCountries' => $allowed_countries);
    }
    //locale
    $locale = '';
    if(isset($atts['locale']) && !empty($atts['locale'])){
        $data_arr['locale'] = $atts['locale'];
    }
    //button class
    $class = '';
    if(isset($atts['class']) && !empty($atts['class'])){
        $class = " ".$atts['class'];
    }
    //submit type
    $submit_type = '';
    if(isset($atts['submit_type']) && !empty($atts['submit_type'])){
        $submit_type = apply_filters('wp_stripe_checkout_v3_submit_type', $submit_type, $atts);
        if(!empty($submit_type)){
            $data_arr['submitType'] = $submit_type;
        }
    }
    $id = uniqid();
    $client_reference_id = 'wpsc'.$id;
    $data_arr['clientReferenceId'] = $client_reference_id;
    $qty_input_class_id = 'wpsc'.$id.'_qty_input';
    $atts['qty_input_class_id'] = $qty_input_class_id;
    $line_items_arr['quantity'] = ' Number(btnqty_'.$id.'.value) ';
    $data_arr['lineItems'] = array($line_items_arr);
    $data_arr_json = json_encode($data_arr);
    $data_arr_json = str_replace('" ', '', $data_arr_json);
    $data_arr_json = str_replace(' "', '', $data_arr_json);
    $button_code = '<div class="wpsc-v3-button-container">';
    $quantity_input_code = '';
    $quantity_input_code = apply_filters('wp_stripe_checkout_v3_quantity', $quantity_input_code, $button_code, $atts);
    if(!empty($quantity_input_code)){
        $button_code .= $quantity_input_code;
    }
    else{
        $button_code .= '<input class="wpstripeco_variable_quantity_input '.$qty_input_class_id.'" type="hidden" name="item_quantity" value="1" required>';
    }
    $button = '<button id="wpsc'.$id.'" class="wpsc-v3-button'.$class.'">'.$button_text.'</button>';
    if(isset($atts['button_image']) && !empty($atts['button_image'])){
        $button = '<a href="#" onclick="event.preventDefault();" id="wpsc'.$id.'" class="wpsc-v3-button'.$class.'"><img src="'.$atts['button_image'].'"></a>';
    }
    $button_code .= $button;
    $button_code .= '</div>';
    $button_code .= <<<EOT
    <div id="error-wpsc$id"></div>
    <script>
    (function() {
        var stripe_$id = Stripe('$key');
        var checkoutButton_$id = document.querySelector('#wpsc$id');
        var btnqty_$id = document.querySelector('.{$qty_input_class_id}');
        var data_arr_$id = $data_arr_json;
        btnqty_$id.addEventListener('change', function () {
            data_arr_$id = $data_arr_json;
        });
        //console.log(data_arr_$id);
        checkoutButton_$id.addEventListener('click', function () {
            stripe_$id.redirectToCheckout(data_arr_$id)
            .then(function (result) {
                if (result.error) {
                  var displayError = document.getElementById('error-wpsc$id');
                  displayError.textContent = result.error.message;
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                var displayError = document.getElementById('error-wpsc$id');
                displayError.textContent = error;
            });
        });
    })();
    </script>        
EOT;
    return $button_code;
}

function wp_stripe_checkout_session_button_handler($atts) {
    $atts = array_map('sanitize_text_field', $atts);
    if(!isset($atts['name']) || empty($atts['name'])){
        return __('You need to provide a name for your item', 'wp-stripe-checkout');
    }
    $item_name = $atts['name'];
    $button_text = 'Buy Now';
    if(isset($atts['button_text']) && !empty($atts['button_text'])){
        $button_text = $atts['button_text'];
    }  
    $options = wp_stripe_checkout_get_option();
    $currency = $options['stripe_currency_code'];
    if(isset($atts['currency']) && !empty($atts['currency'])){
        $currency = $atts['currency'];
    }
    $success_url = $options['success_url'];
    if(isset($atts['success_url']) && !empty($atts['success_url'])){
        $success_url = $atts['success_url'];
    }
    //check to make sure that the success_url is set
    if(!isset($success_url) || empty($success_url)){
        return __('You need to provide a return URL page in the settings', 'wp-stripe-checkout');
    }
    $cancel_url = home_url();
    if(isset($atts['cancel_url']) && !empty($atts['cancel_url'])){
        $cancel_url = $atts['cancel_url'];
    }
    $billing_address = '';
    if(isset($atts['billing_address']) && !empty($atts['billing_address'])){
        $billing_address = sanitize_text_field($atts['billing_address']);
    }
    $phone_number_collection = '';
    if(isset($atts['phone_number_collection']) && !empty($atts['phone_number_collection'])){
        $phone_number_collection = sanitize_text_field($atts['phone_number_collection']);
    }
    $allow_promotion_codes = '';
    if(isset($atts['allow_promotion_codes']) && !empty($atts['allow_promotion_codes'])){
        $allow_promotion_codes = sanitize_text_field($atts['allow_promotion_codes']);
    }
    $submit_type = '';
    if(isset($atts['submit_type']) && !empty($atts['submit_type'])){
        $submit_type = sanitize_text_field($atts['submit_type']);
    }
    $terms_of_service = '';
    if(isset($atts['terms_of_service']) && !empty($atts['terms_of_service'])){
        $terms_of_service = sanitize_text_field($atts['terms_of_service']);
    }
    $key = $options['stripe_publishable_key'];
    if(WP_STRIPE_CHECKOUT_TESTMODE){
        $key = $options['stripe_test_publishable_key'];
    }
    if(!isset($key) || empty($key)){
        return __('You need to provide your publishable key in the settings', 'wp-stripe-checkout');
    }
    //button class
    $class = 'wpsc-session';
    if(isset($atts['class']) && !empty($atts['class'])){
        $class = $class." ".$atts['class'];
    }
    $id = uniqid();
    $client_reference_id = 'wpsc'.$id;
    $button_code = '<form class="'.esc_attr($class).'" action="" method="post">';
    $button_code .= wp_nonce_field('wp_stripe_checkout_session_nonce', '_wpnonce', true, false);
    $button_code .= '<input type="hidden" name="client_reference_id" value="'.esc_attr($client_reference_id).'" />';
    $button_code .= '<input type="hidden" name="item_name" value="'.esc_attr($item_name).'" />';
    $price_input_code = '';
    $price_input_code = apply_filters('wp_stripe_checkout_session_price', $price_input_code, $button_code, $atts);
    if(!empty($price_input_code)){
        $button_code .= $price_input_code;
    }
    else{
        if(isset($atts['price']) && is_numeric($atts['price']) && $atts['price'] > 0) {
            $button_code .= '<input type="hidden" name="item_price" value="'.esc_attr($atts['price']).'" />';
        }
        else{
            return __('You need to provide a valid price for your item', 'wp-stripe-checkout');
        }
    }
    $quantity_input_code = '';
    $quantity_input_code = apply_filters('wp_stripe_checkout_session_quantity', $quantity_input_code, $button_code, $atts);
    if(!empty($quantity_input_code)){
        $button_code .= $quantity_input_code;
    }
    $button_code .= '<input type="hidden" name="item_currency" value="'.esc_attr($currency).'" />';
    if(!empty($success_url)){
        $button_code .= '<input type="hidden" name="success_url" value="'.esc_url($success_url).'" />';
    }
    if(!empty($cancel_url)){
        $button_code .= '<input type="hidden" name="cancel_url" value="'.esc_url($cancel_url).'" />';
    }
    if(!empty($billing_address)){
        $button_code .= '<input type="hidden" name="billing_address" value="'.esc_attr($billing_address).'" />';
    }
    if(!empty($phone_number_collection)){
        $button_code .= '<input type="hidden" name="phone_number_collection" value="'.esc_attr($phone_number_collection).'" />';
    }
    if(!empty($allow_promotion_codes)){
        $button_code .= '<input type="hidden" name="allow_promotion_codes" value="'.esc_attr($allow_promotion_codes).'" />';
    }
    if(!empty($submit_type)){
        $button_code .= '<input type="hidden" name="submit_type" value="'.esc_attr($submit_type).'" />';
    }
    if(!empty($terms_of_service)){
        $button_code .= '<input type="hidden" name="terms_of_service" value="'.esc_attr($terms_of_service).'" />';
    }
    $button_code .= '<input type="hidden" name="wp_stripe_checkout_session" value="1" />';
    if(isset($atts['button_image']) && !empty($atts['button_image'])){
        $button_code .= '<input type="image" src="'.esc_url($atts['button_image']).'" alt="Submit" />';    
    }
    else{
        $button_code .= '<input type="submit" value="'.esc_attr($button_text).'" />';
    }
    $button_code .= '</form>';
    return $button_code;
}

function wp_stripe_checkout_get_shipping_countries_string(){
    $shipping_countries = "'AC', 'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AR', 'AT', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BR', 'BS', 'BT', 'BV', 'BW', 'BY', 'BZ', 'CA', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CR', 'CV', 'CW', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MK', 'ML', 'MM', 'MN', 'MO', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PM', 'PN', 'PR', 'PS', 'PT', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SB', 'SC', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SX', 'SZ', 'TA', 'TC', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VN', 'VU', 'WF', 'WS', 'XK', 'YE', 'YT', 'ZA', 'ZM', 'ZW', 'ZZ'";
    return $shipping_countries;
}

function wp_stripe_checkout_get_shipping_countries_array(){
    $shipping_countries = array('AC', 'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AR', 'AT', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BR', 'BS', 'BT', 'BV', 'BW', 'BY', 'BZ', 'CA', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CR', 'CV', 'CW', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MK', 'ML', 'MM', 'MN', 'MO', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PM', 'PN', 'PR', 'PS', 'PT', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SB', 'SC', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SX', 'SZ', 'TA', 'TC', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VN', 'VU', 'WF', 'WS', 'XK', 'YE', 'YT', 'ZA', 'ZM', 'ZW', 'ZZ');
    return $shipping_countries;
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
    $options['success_url'] = '';
    $options['cancel_url'] = '';
    $options['enable_debug'] = '';
    return $options;
}

function wp_stripe_checkout_get_email_option(){
    $options = get_option('wp_stripe_checkout_email_options');
    if(!is_array($options)){
        $options = wp_stripe_checkout_get_empty_email_options_array();
    }
    return $options;
}

function wp_stripe_checkout_update_email_option($new_options){
    $empty_options = wp_stripe_checkout_get_empty_email_options_array();
    $options = wp_stripe_checkout_get_email_option();
    if(is_array($options)){
        $current_options = array_merge($empty_options, $options);
        $updated_options = array_merge($current_options, $new_options);
        update_option('wp_stripe_checkout_email_options', $updated_options);
    }
    else{
        $updated_options = array_merge($empty_options, $new_options);
        update_option('wp_stripe_checkout_email_options', $updated_options);
    }
}

function wp_stripe_checkout_get_empty_email_options_array(){
    $options = array();
    $options['email_from_name'] = '';
    $options['email_from_address'] = '';
    $options['purchase_email_enabled'] = '';
    $options['purchase_email_subject'] = '';
    $options['purchase_email_type'] = '';
    $options['purchase_email_body'] = '';
    $options['sale_notification_email_enabled'] = '';
    $options['sale_notification_email_recipient'] = '';
    $options['sale_notification_email_subject'] = '';
    $options['sale_notification_email_type'] = '';
    $options['sale_notification_email_body'] = '';
    return $options;
}

function wp_stripe_checkout_set_default_email_options(){
    $options = wp_stripe_checkout_get_email_option();
    $options['purchase_email_type'] = 'plain';
    $options['purchase_email_subject'] = __("Purchase Receipt", "wp-stripe-checkout");
    $purchage_email_body = __("Dear", "wp-stripe-checkout")." {first_name},\n\n";
    $purchage_email_body .= __("Thank you for your purchase. Your purchase details are shown below for your reference:", "wp-stripe-checkout")."\n\n";
    $purchage_email_body .= __("Transaction ID:", "wp-stripe-checkout")." {txn_id}\n";
    $purchage_email_body .= __("Product:", "wp-stripe-checkout")." {product_name}\n";
    $purchage_email_body .= __("Price:", "wp-stripe-checkout")." {currency_code} {price}";
    $options['purchase_email_body'] = $purchage_email_body;
    $options['sale_notification_email_recipient'] = get_bloginfo('admin_email');
    $options['sale_notification_email_subject'] = __("New Customer Order", "wp-stripe-checkout");
    $options['sale_notification_email_type'] = 'plain';
    $sale_notification_email_body = __("Hello", "wp-stripe-checkout")."\n\n";
    $sale_notification_email_body .= __("A purchase has been made.", "wp-stripe-checkout")."\n\n";
    $sale_notification_email_body .= __("Purchased by:", "wp-stripe-checkout")." {full_name}\n";
    $sale_notification_email_body .= __("Product sold:", "wp-stripe-checkout")." {product_name}\n";
    $sale_notification_email_body .= __("Amount:", "wp-stripe-checkout")." {currency_code} {price}\n\n";
    $sale_notification_email_body .= __("Thank you", "wp-stripe-checkout");       
    $options['sale_notification_email_body'] = $sale_notification_email_body;
    add_option('wp_stripe_checkout_email_options', $options);
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
