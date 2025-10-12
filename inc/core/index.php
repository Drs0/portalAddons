<?php
require_once PLUGIN_PATH . 'inc/core/postTypes/postTypeRegistrar.php';
use PortalAddons\Core\PostTypeRegistrar;

// Save to database
add_action('init', function () {
    if (!empty(PostTypeRegistrar::$registeredPostTypes)) {
        update_option('portal_addons_registered_post_types', PostTypeRegistrar::$regiteredPostTypes);
    }
});

// global $portalAddonsSettings;
// $portalAddonsSettings = [];

// /**
//  * Structure of each item (module) added by filters:
//  * [
//  *   'optionName'  => 'portalAddonsEnableCars',
//  *   'label'       => 'Enable Car Post Type',
//  *   'description' => 'Registers the Car custom post type.',
//  *   'logicPath'   => 'inc/core/postTypes/cars/carPostType.php',
//  *   'type'        => 'checkbox', // checkbox | text | number | select
//  *   'default'     => '',          // optional default value
//  *   'choices'     => [            // only for 'select' types
//  *        'option1' => 'Option 1',
//  *        'option2' => 'Option 2',
//  *   ],
//  * ]
//  */

// $portalAddonsSettings = apply_filters('portalAddonsSettings', $portalAddonsSettings);


// foreach($portalAddonsSettings as $portalAddonsSetting){
//     if(!get_option($portalAddonsSetting['optionName'])) return;
//     if(!file_exists(PLUGIN_PATH . $portalAddonsSetting['logicPath'])) return;
//     require_once PLUGIN_PATH . $portalAddonsSetting['logicPath'];
// }

if(get_option('portalAddonsEnableCars')){
    require_once PLUGIN_PATH . 'inc/core/postTypes/cars/carPostType.php';
}
