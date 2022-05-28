<?php
if (!defined('ABSPATH')) exit;

// Get access token
function swipego_get_access_token()
{
    return get_transient('swipego_access_token');
}

// Update access token
function swipego_update_access_token($access_token, $remember = false)
{

    $expires = DAY_IN_SECONDS;

    if ($remember) {
        $expires = DAY_IN_SECONDS * 7;
    }

    return set_transient('swipego_access_token', $access_token, $expires);
}

// Delete access token
function swipego_delete_access_token()
{

    if (swipego_is_plugin_activated('give/give.php')) {

        give_delete_option('swipego_business_id');
        give_delete_option('swipego_environment');
        give_delete_option('swipego_api_key');
        give_delete_option('swipego_signature_key');
        give_delete_option('swipego_webhook_name');
        give_delete_option('swipego_webhook_url');
        give_delete_option('swipego_webhook_enabled');
    }

    return delete_transient('swipego_access_token');
}

// Check if the user is logged into Swipe
function swipego_is_logged_in()
{
    return swipego_get_access_token() ? true : false;
}

// Check if the specified plugin is installed and activated
function swipego_is_plugin_activated($plugin_path)
{
    return in_array($plugin_path, apply_filters('active_plugins', get_option('active_plugins')));
}

// Check if the specified plugin is installed
function swipego_is_plugin_installed($plugin_path)
{
    $installed_plugins = get_plugins();
    return array_key_exists($plugin_path, $installed_plugins);
}
