<?php
namespace PortalAddons\Core\Classes;

class CacheManager {
    public static function registerRoutes() {
        register_rest_route('portal/v1', '/purge-cache', [
            'methods'  => 'POST',
            'callback' => [__CLASS__, 'purgeCache'],
            'permission_callback' => fn() => current_user_can('manage_options'),
        ]);
    }

    public static function registerCacheKey($postType, $cacheKey) {
        $cacheList = get_option('portalStatsCacheKeys', []);
        $cacheList[$postType][] = $cacheKey;
        $cacheList[$postType] = array_unique($cacheList[$postType]);
        update_option('portalStatsCacheKeys', $cacheList);
    }

    public static function deleteCacheByPostType($postType) {
        $cacheList = get_option('portalStatsCacheKeys', []);
        if (empty($cacheList[$postType])) return;
        foreach ($cacheList[$postType] as $key) delete_transient($key);
        unset($cacheList[$postType]);
        update_option('portalStatsCacheKeys', $cacheList);
    }

    public static function handlePostUpdate($postId, $post, $update) {
        $postType = get_post_type($postId);
        if ($postType) self::deleteCacheByPostType($postType);
    }

    public static function handlePostDelete($postId) {
        $postType = get_post_type($postId);
        if ($postType) self::deleteCacheByPostType($postType);
    }

    public static function purgeCache($request) {
        $postType = sanitize_text_field($request->get_param('postType'));
        if (empty($postType)) {
            return new \WP_Error('missing_post_type', 'Missing post type parameter.', ['status' => 400]);
        }
        self::deleteCacheByPostType($postType);
        return rest_ensure_response(['success' => true, 'message' => "Cache purged for {$postType}"]);
    }
}
