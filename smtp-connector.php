<?php
/*
Plugin Name: SMTP Connector
Description: A 100% Free SMTP Plugin that Allows you to set a custom SMTP for sending emails in WordPress. Connect Gmail, MailGun, Amazon SES, SendinBlue, Zoho, and More to send Emails in WordPress.
Version: 1.2.3
Author: Mukesh Patel
Author URI: https://mpateldigital.com/
Plugin URI: https://mpateldigital.com/smtp-connector/
License: GPLv3
Text Domain: smtp-connector
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the encryption and decryption functions
require_once(plugin_dir_path(__FILE__) . 'includes/encryption-functions.php');

// Include settings page only if in admin
if (is_admin()) {
    require_once(plugin_dir_path(__FILE__) . 'includes/settings-page.php');
}

// Support links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'smtp_connector_for_wp_add_action_links');
function smtp_connector_for_wp_add_action_links($links)
{
    $plugin_shortcuts = array(
        '<a rel="noopener" title="' . esc_attr__('Hire for Technical Support', 'smtp_connector_for_wp') . '" href="https://mpateldigital.com/contact-us/" target="_blank" style="color: #d42e06;font-weight: 500;">' . __('Hire Me', 'smtp_connector_for_wp') . '</a>',
        '<a rel="noopener" title="' . esc_attr__('Show your support', 'smtp_connector_for_wp') . '" href="https://ko-fi.com/mukeshpatel" target="_blank" style="color:#080;">' . __('Buy developer a coffee', 'smtp_connector_for_wp') . '</a>'
    );
    return array_merge($links, $plugin_shortcuts);
}

/* Register activation hook. */
register_activation_hook(__FILE__, 'smtp_connector_for_wp_activation_hook');

/**
 * Runs only when the plugin is activated.
 * @since 1.0.0
 */
function smtp_connector_for_wp_activation_hook()
{
    /* Create transient data */
    set_transient('smtp-connector-for-wp-activation-notice', true, 5);
}

/* Add admin notice */
add_action('admin_notices', 'smtp_connector_for_wp_notice');

/**
 * Admin Notice on Activation of Plugin
 * @since 1.0.0
 */
function smtp_connector_for_wp_notice()
{
    /* Check for transient, if available display notice */
    if (get_transient('smtp-connector-for-wp-activation-notice')) {
        ?>
        <style>
            div#message.updated {
                display: none;
            }
        </style>
        <div class="updated notice is-dismissible">
            <p><?php esc_html_e('😊 Thank you for using Simple SMTP for WP. Please enter your SMTP details on Settings > SMTP Connector', 'smtp_connector_for_wp'); ?></p>
        </div>
        <?php
        delete_transient('smtp-connector-for-wp-activation-notice');
    }
}

// Check SMTP configuration and add a red notice if not properly configured
add_action('admin_notices', 'smtp_connector_for_wp_check_smtp_config');
function smtp_connector_for_wp_check_smtp_config()
{
    $required_fields = [
        'smtp_connector_for_wp_host',
        'smtp_connector_for_wp_username',
        'smtp_connector_for_wp_password',
        'smtp_connector_for_wp_from_email',
        'smtp_connector_for_wp_from_name',
        'smtp_connector_for_wp_port',
        'smtp_connector_for_wp_security'
    ];
    $is_configured = true;
    foreach ($required_fields as $field) {
        if (empty(get_option($field))) {
            $is_configured = false;
            break;
        }
    }

    if (!$is_configured) {
        ?>
        <div class="error notice is-dismissible">
            <p><?php esc_html_e('SMTP is not properly configured. Please enter your SMTP details on Settings > SMTP Connector.', 'smtp_connector_for_wp'); ?></p>
        </div>
        <?php
    }
}

// Hook into phpmailer_init
add_action('phpmailer_init', 'smtp_connector_for_wp_custom_phpmailer');

function smtp_connector_for_wp_custom_phpmailer($phpmailer)
{
    $phpmailer->isSMTP();
    $phpmailer->Host = sanitize_text_field(get_option('smtp_connector_for_wp_host'));
    $phpmailer->SMTPAuth = true;
    $phpmailer->FromName = sanitize_text_field(get_option('smtp_connector_for_wp_from_name'));
    $phpmailer->From = sanitize_email(get_option('smtp_connector_for_wp_from_email'));
    $phpmailer->Username = sanitize_text_field(get_option('smtp_connector_for_wp_username'));
    $phpmailer->Password = smtp_connector_for_wp_decrypt_password(get_option('smtp_connector_for_wp_password'));
    $phpmailer->SMTPSecure = sanitize_text_field(get_option('smtp_connector_for_wp_security'));
    $phpmailer->Port = intval(get_option('smtp_connector_for_wp_port'));
}

// Add settings link on plugin page
function smtp_connector_for_wp_settings_page_link($links)
{
    $settings_link = '<a href="options-general.php?page=smtp-connector-for-wp">' . esc_html__('Settings', 'smtp_connector_for_wp') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'smtp_connector_for_wp_settings_page_link');

// Handle SMTP test form submission
add_action('admin_post_smtp_test_email', 'smtp_connector_for_wp_handle_test_email');
function smtp_connector_for_wp_handle_test_email()
{
    if (isset($_POST['smtp_test_email']) && check_admin_referer('smtp_test_nonce', 'smtp_test_nonce')) {
        $test_email = sanitize_email($_POST['smtp_test_email']);

        // Attempt to send a test email
        $result = smtp_connector_for_wp_send_test_email($test_email);

        // Redirect back to settings page with result
        $redirect_url = add_query_arg([
            'page' => 'smtp-connector-for-wp',
            'tab' => 'test',
            'smtp_test_result' => $result ? 'success' : 'error'
        ], admin_url('options-general.php'));
        wp_redirect($redirect_url);
        exit;
    }
}

/**
 * Sends a test email to the specified email address using the configured SMTP settings.
 *
 * @param string $email The email address to send the test email to.
 * @return bool True on success, false on failure.
 */
function smtp_connector_for_wp_send_test_email($email)
{
    $subject = esc_html__('SMTP Test Email', 'smtp_connector_for_wp');
    $body = esc_html__('This is a test email sent using SMTP Connector plugin.', 'smtp_connector_for_wp');
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Attempt to send the email using wp_mail() function
    $result = wp_mail($email, $subject, $body, $headers);

    return $result;
}

// Handle SMTP settings reset
add_action('admin_post_smtp_connector_for_wp_reset_settings', 'smtp_connector_for_wp_reset_settings');
function smtp_connector_for_wp_reset_settings()
{
    if (check_admin_referer('smtp_connector_for_wp_reset_nonce', 'smtp_connector_for_wp_reset_nonce')) {
        // List of options to delete
        $options = [
            'smtp_connector_for_wp_host',
            'smtp_connector_for_wp_username',
            'smtp_connector_for_wp_password',
            'smtp_connector_for_wp_from_email',
            'smtp_connector_for_wp_from_name',
            'smtp_connector_for_wp_port',
            'smtp_connector_for_wp_security'
        ];

        // Delete options
        foreach ($options as $option) {
            delete_option($option);
        }

        // Redirect back to settings page
        $redirect_url = add_query_arg([
            'page' => 'smtp-connector-for-wp',
            'tab' => 'settings',
            'reset' => 'success'
        ], admin_url('options-general.php'));
        wp_redirect($redirect_url);
        exit;
    }
}
