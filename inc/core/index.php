<?php
require_once PLUGIN_PATH . 'inc/core/postTypes/postTypeRegistrar.php';
use PortalAddons\Core\PostTypeRegistrar;
use PortalAddons\Core\Classes\PortalOption;

// Save to database
add_action('init', function () {
    if (!empty(PostTypeRegistrar::$registeredPostTypes)) {
        update_option('portal_addons_registered_post_types', PostTypeRegistrar::$regiteredPostTypes);
    }
});

PortalOption::activateOptions([
    'optionName' => 'portalAddonsEnableCars',
    'optionPath' => 'inc/core/postTypes/cars/carPostType.php'
]);
