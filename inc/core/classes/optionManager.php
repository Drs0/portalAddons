<?php

namespace PortalAddons\Core\Classes;

class PortalOption
{

    public function __construct() {}

    /**
     * Register settings/options dynamically
     */
    public static function optionRegistrar(array $args = []) {
        if (empty($args['optionGroup']) || empty($args['options'])) {
            return;
        }

        $group     = $args['optionGroup'];
        $filter    = $args['filterName'] ?? 'portalAddonsSettings';

        foreach ($args['options'] as $option) {
            if (is_string($option)) {
                $option = [
                    'optionName' => $option,
                    'label'      => ucfirst(str_replace('_', ' ', $option)),
                    'type'       => 'text',
                    'default'    => '',
                ];
            }

            if (empty($option['optionName'])) continue;

            $name      = $option['optionName'];
            $sanitize  = $option['sanitize_callback'] ?? null;

            register_setting($group, $name, [
                'sanitize_callback' => $sanitize
            ]);

            add_filter($filter, function($settings) use ($option) {
                $settings[] = $option;
                return $settings;
            });
        }
    }



    /**
     * Register menu and/or submenu pages dynamically
     * example usage :
     * add_action('admin_menu', function() {
     *       PortalOption::menuPageRegistrar([
     *           'menu' => true,
     *           'pageTitle' => 'Portal Addons',
     *           'menuTitle' => 'Portal Addons',
     *           'capability' => 'manage_options',
     *           'menuSlug' => 'portal-addons-settings',
     *           'callback' => 'portalAddonsRenderSettingsPage',
     *           'iconUrl' => 'dashicons-admin-generic',
     *           'position' => 60,
     *           'subMenu' => [
     *               [
     *                   'pageTitle' => 'Car Settings',
     *                   'menuTitle' => 'Cars',
     *                   'menuSlug'  => 'portal-addons-cars',
     *                   'callback'  => 'portalAddonsRenderCarSettings',
     *               ]
     *           ]
     *       ]);
     *   });
     *  you may need a submenu without the parent this is an example for it
     * PortalOption::menuPageRegistrar([
     *          'menu' => false,
     *      'menuSlug' => 'tools.php', // parent slug for Tools page
     *   'subMenu' => [
     *       [
     *           'pageTitle' => __('Portal Tools', 'portal-addons'),
     *           'menuTitle' => __('Portal Tools', 'portal-addons'),
     *           'menuSlug'  => 'portalAddonsTools',
     *           'callback'  => 'portalAddonsToolsPage'
     *       ]
     *   ]
     *   ]);
     * 
     */
    public static function menuPageRegistrar($args){
        if (empty($args) || !is_array($args)) {
            return;
        }

        $pageTitle = $args['pageTitle'] ?? '';
        $menuTitle = $args['menuTitle'] ?? '';
        $capability = $args['capability'] ?? 'manage_options';
        $menuSlug = $args['menuSlug'] ?? '';
        $callback = $args['callback'] ?? '';
        $iconUrl = $args['iconUrl'] ?? '';
        $position = $args['position'] ?? null;

        if (!empty($args['menu'])) {
            add_menu_page(
                $pageTitle,
                $menuTitle,
                $capability,
                $menuSlug,
                $callback,
                $iconUrl,
                $position
            );

            add_submenu_page(
                $menuSlug,
                $pageTitle,
                $menuTitle,
                $capability,
                $menuSlug,
                $callback
            );
        }

        if (!empty($args['subMenu'])) {
            $subMenus = isset($args['subMenu'][0]) ? $args['subMenu'] : [$args['subMenu']];

            foreach ($subMenus as $submenu) {
                add_submenu_page(
                    $menuSlug,
                    $submenu['pageTitle'] ?? '',
                    $submenu['menuTitle'] ?? '',
                    $submenu['capability'] ?? $capability,
                    $submenu['menuSlug'] ?? '',
                    $submenu['callback'] ?? ''
                );
            }
        }
    }

    public static function renderOptionsHtml($args = []) {
        $filterName  = $args['filterName'] ?? 'portalAddonsSettings';
        $optionGroup = $args['optionGroup'] ?? 'portalAddonsSettings';
        $pageTitle   = $args['pageTitle'] ?? __('Portal Addons Settings', 'portal-addons');

        $settings = apply_filters($filterName, []);

        if (empty($settings)) {
            echo '<div class="wrap"><h2>' . esc_html($pageTitle) . '</h2>';
            echo '<p>' . esc_html__('No settings available.', 'portal-addons') . '</p></div>';
            return;
        }

        $template_path = PLUGIN_TEMPLATE_PATH . 'settings/portalAddonsSettingsPage.php';

        if (!file_exists($template_path)) {
            echo '<p style="color:red;">Template missing: ' . esc_html($template_path) . '</p>';
            return;
        }

        $data = [
            'pageTitle'   => $pageTitle,
            'optionGroup' => $optionGroup,
            'settings'    => $settings,
        ];

        self::loadTemplate($template_path, $data);
    }

    /**
     * Load a PHP template file and extract variables
     */
    private static function loadTemplate($path, $data = []) {
        if (!empty($data) && is_array($data)) {
            extract($data, EXTR_SKIP);
        }
        include $path;
    }

}
