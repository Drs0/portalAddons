<?php
namespace PortalAddons\Api;

class StatsApi {
    public static function registerRoutes() {
        register_rest_route('portal/v1', '/stats', [
            'methods'  => 'GET',
            'callback' => [__CLASS__, 'getStats'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function getStats() {
        // ... do logic here, return summary of all cars etc.
    }
}
