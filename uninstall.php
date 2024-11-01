<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

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
