<?php
namespace PortalAddons\Api;

use PortalAddons\Api\PostsApi;
use PortalAddons\Core\Classes\CacheManager;
use PortalAddons\Api\StatsApi;

class PortalApi {
    public static function init() {
        // Initialize all submodules
        add_action('rest_api_init', [PostsApi::class, 'registerRoutes']);
        add_action('rest_api_init', [CacheManager::class, 'registerRoutes']);

        // Global hooks for cache invalidation
        add_action('save_post', [CacheManager::class, 'handlePostUpdate'], 10, 3);
        add_action('deleted_post', [CacheManager::class, 'handlePostDelete'], 10, 1);

        // (Optional) Global stats
        add_action('rest_api_init', [StatsApi::class, 'registerRoutes']);
    }
}

PortalApi::init();
