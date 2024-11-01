<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the encryption and decryption functions
require_once(plugin_dir_path(__FILE__) . 'encryption-functions.php');

// Create custom plugin settings menu
add_action('admin_menu', 'smtp_connector_for_wp_create_menu');

function smtp_connector_for_wp_create_menu()
{
    add_options_page('SMTP Connector Settings', 'SMTP Connector', 'manage_options', 'smtp-connector-for-wp', 'smtp_connector_for_wp_settings_page');
    add_action('admin_init', 'smtp_connector_for_wp_settings_register');
}

function smtp_connector_for_wp_settings_register()
{
    // Sanitization function
    function smtp_connector_for_wp_sanitize_option($input)
    {
        return sanitize_text_field($input);
    }

    // Validate and sanitize host, from_name, and username
    register_setting('smtp-connector-for-wp-settings-group', 'smtp_connector_for_wp_host', 'smtp_connector_for_wp_sanitize_option');
    register_setting('smtp-connector-for-wp-settings-group', 'smtp_connector_for_wp_username', 'smtp_connector_for_wp_sanitize_option');

    // Use encryption for password
    register_setting('smtp-connector-for-wp-settings-group', 'smtp_connector_for_wp_password', 'smtp_connector_for_wp_encrypt_password');

    register_setting('smtp-connector-for-wp-settings-group', 'smtp_connector_for_wp_from_name', 'smtp_connector_for_wp_sanitize_option');
    register_setting('smtp-connector-for-wp-settings-group', 'smtp_connector_for_wp_from_email', 'sanitize_email');
    register_setting('smtp-connector-for-wp-settings-group', 'smtp_connector_for_wp_security', 'smtp_connector_for_wp_sanitize_option');
    register_setting('smtp-connector-for-wp-settings-group', 'smtp_connector_for_wp_port', 'intval');
}

function smtp_connector_for_wp_encrypt_password($password)
{
    return smtp_connector_for_wp_encrypted_password($password);
}

function smtp_connector_for_wp_settings_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.'));
    }
    
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'settings';
    ?>
    <div class="wrap">
        <h2><?php esc_html_e('SMTP Connector Settings', 'smtp_connector_for_wp'); ?></h2>
        <h2 class="nav-tab-wrapper">
            <a href="?page=smtp-connector-for-wp&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Settings', 'smtp_connector_for_wp'); ?></a>
            <a href="?page=smtp-connector-for-wp&tab=test" class="nav-tab <?php echo $active_tab == 'test' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('SMTP Test', 'smtp_connector_for_wp'); ?></a>
        </h2>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <!-- Sidebar Area -->
                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h3><span><?php esc_html_e('Must Check', 'smtp_connector_for_wp'); ?></span></h3>
                        <div class="inside">
                            <ul>
                                <li><a href="https://mpateldigital.com/" target="_blank"><?php esc_html_e('Official Site', 'smtp_connector_for_wp'); ?></a></li>
                                <li><a href="https://mpateldigital.com/contact-us" target="_blank"><?php esc_html_e('Support', 'smtp_connector_for_wp'); ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div id="post-body-content">
                    <?php if ($active_tab == 'settings') : ?>
                        <form method="post" action="options.php">
                            <?php settings_fields('smtp-connector-for-wp-settings-group'); ?>
                            <?php do_settings_sections('smtp-connector-for-wp-settings-group'); ?>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('SMTP Host', 'smtp_connector_for_wp'); ?></th>
                                    <td>
                                        <input type="text" name="smtp_connector_for_wp_host"
                                               value="<?php echo esc_attr(get_option('smtp_connector_for_wp_host')); ?>" required />
                                        <p class="description"><?php esc_html_e('Enter your SMTP host, e.g., smtp.gmail.com', 'smtp_connector_for_wp'); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('From Name', 'smtp_connector_for_wp'); ?></th>
                                    <td>
                                        <input type="text" placeholder="<?php esc_attr_e('Enter Website Name', 'smtp_connector_for_wp'); ?>"
                                               name="smtp_connector_for_wp_from_name"
                                               value="<?php echo esc_attr(get_option('smtp_connector_for_wp_from_name')); ?>" required />
                                        <p class="description"><?php esc_html_e('Enter the name you want to appear in the "From" field', 'smtp_connector_for_wp'); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('From Email', 'smtp_connector_for_wp'); ?></th>
                                    <td>
                                        <input type="email" placeholder="<?php esc_attr_e('e.g wordpress@example.com', 'smtp_connector_for_wp'); ?>"
                                               name="smtp_connector_for_wp_from_email"
                                               value="<?php echo esc_attr(get_option('smtp_connector_for_wp_from_email')); ?>" required />
                                        <p class="description"><?php esc_html_e('Enter the email address you want to appear in the "From" field', 'smtp_connector_for_wp'); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('SMTP Username', 'smtp_connector_for_wp'); ?></th>
                                    <td>
                                        <input type="text" name="smtp_connector_for_wp_username"
                                               value="<?php echo esc_attr(get_option('smtp_connector_for_wp_username')); ?>" required />
                                        <p class="description"><?php esc_html_e('Enter your SMTP username, usually your email address', 'smtp_connector_for_wp'); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('SMTP Password', 'smtp_connector_for_wp'); ?></th>
                                    <td>
                                        <input type="password" name="smtp_connector_for_wp_password"
                                               value="<?php echo esc_attr(smtp_connector_for_wp_decrypt_password(get_option('smtp_connector_for_wp_password'))); ?>" required />
                                        <span><?php esc_html_e('Use App Password for ', 'smtp_connector_for_wp'); ?><a target="_blank" href="https://myaccount.google.com/apppasswords">Gmail</a><?php esc_html_e(' and ', 'smtp_connector_for_wp'); ?><a target="_blank" href="https://help.zoho.com/portal/en/kb/bigin/channels/email/articles/generate-an-app-specific-password#To_generate_app_specific_password_for_Zoho_Mail">Zoho</a>.</span>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('Encryption Type', 'smtp_connector_for_wp'); ?></th>
                                    <td>
                                        <select name="smtp_connector_for_wp_security" required>
                                            <option value="tls" <?php selected(get_option('smtp_connector_for_wp_security'), 'tls'); ?>>TLS</option>
                                            <option value="ssl" <?php selected(get_option('smtp_connector_for_wp_security'), 'ssl'); ?>>SSL</option>
                                        </select>
                                        <p class="description"><?php esc_html_e('TLS is recommended. If you choose TLS then it should be set to 587. For SSL use port 465 instead.', 'smtp_connector_for_wp'); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('SMTP Port', 'smtp_connector_for_wp'); ?></th>
                                    <td><input type="number" placeholder="<?php esc_attr_e('587 Most in Cases like Gmail', 'smtp_connector_for_wp'); ?>"
                                               name="smtp_connector_for_wp_port"
                                               value="<?php echo esc_attr(get_option('smtp_connector_for_wp_port')); ?>" required />
                                        <p class="description"><?php esc_html_e('Use 587 most in cases. Gmail, Zoho, Sendinblue, SendGrid etc use this port.', 'smtp_connector_for_wp'); ?></p>
                                    </td>
                                </tr>
                            </table>
                            <?php wp_nonce_field('smtp_connector_for_wp_save_settings', 'smtp_connector_for_wp_nonce'); ?>
                            <?php submit_button(esc_html__('Save Settings', 'smtp_connector_for_wp')); ?>
                        </form>

                        <!-- Add the Reset Button -->
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="smtp_connector_for_wp_reset_settings">
                            <?php wp_nonce_field('smtp_connector_for_wp_reset_nonce', 'smtp_connector_for_wp_reset_nonce'); ?>
                            <?php submit_button(esc_html__('Reset Settings', 'smtp_connector_for_wp'), 'delete'); ?>
                        </form>
                        
                    <?php elseif ($active_tab == 'test') : ?>
                        <?php if (isset($_GET['smtp_test_result'])) : ?>
                            <div class="notice notice-<?php echo $_GET['smtp_test_result'] == 'success' ? 'success' : 'error'; ?> is-dismissible">
                                <p><?php echo $_GET['smtp_test_result'] == 'success' ? esc_html__('Success, email sent. Please check your email.', 'smtp_connector_for_wp') : esc_html__('Unable to send email. Please recheck SMTP information.', 'smtp_connector_for_wp'); ?></p>
                            </div>
                        <?php endif; ?>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="smtp_test_email">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php esc_html_e('Test Email Address:', 'smtp_connector_for_wp'); ?></th>
                                    <td>
                                        <input type="email" name="smtp_test_email" placeholder="<?php esc_attr_e('Enter test email address', 'smtp_connector_for_wp'); ?>" required>
                                    </td>
                                </tr>
                            </table>
                            <?php wp_nonce_field('smtp_test_nonce', 'smtp_test_nonce'); ?>
                            <?php submit_button(esc_html__('Send Test Email', 'smtp_connector_for_wp')); ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
