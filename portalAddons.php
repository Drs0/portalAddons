<?php
/**
 * Plugin Name: Portal Addons
 * Description: A designed plugin for the Portal Network Theme
 * Version: 1.0
 * Author: DRS
 */


if ( ! defined( 'ABSPATH' ) ) exit;

if(!defined('PLUGIN_PATH')){
    define('PLUGIN_PATH', plugin_dir_path(__FILE__));
}

if (!defined('PLUGIN_URL')) {
    define('PLUGIN_URL', plugin_dir_url(__FILE__));
}

if(!defined('PLUGIN_TEMPLATE_PATH')){
    define('PLUGIN_TEMPLATE_PATH', PLUGIN_PATH . 'templates/');
}

if(!defined('MATCHES_TOKEN')){
    define('MATCHES_TOKEN','');
}

function portalAddonsLoadTextdomain() {
    load_plugin_textdomain('portal-addons', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'portalAddonsLoadTextdomain');

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="admin.php?page=portalAddonsSettings">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});

include PLUGIN_PATH . 'inc/hooks.php';

use PortalAddons\Core\Classes\Auth\portalAuthentication;

add_action('plugins_loaded', function () {
    new portalAuthentication();
});
