<?php
use PortalAddons\Core\Classes\PortalOption;

add_action('admin_init', 'enableCarPostType');
function enableCarPostType(){
    $args = [
        'optionGroup' => 'portalAddonsSettings',
        'options' => [
            [
                'optionName'  => 'portalAddonsEnableCars',
                'label'       => __('Enable Car Post Type', 'portal-addons'),
                'type'        => 'checkbox',
                'default'     => 0,
            ],
        ]
    ];
    PortalOption::optionRegistrar($args);
}
