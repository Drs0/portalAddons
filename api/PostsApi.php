<?php
namespace PortalAddons\Api;

use PortalAddons\Core\Classes\CacheManager;

class PostsApi {
    public static function registerRoutes() {
        register_rest_route('portal/v1', '/posts', [
            'methods'  => 'GET',
            'callback' => [__CLASS__, 'getPosts'],
            'permission_callback' => '__return_true',
            'args' => apply_filters('portalStatRouteArgs', [
                'postType' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'The post type slug.',
                ],
                'page' => [
                    'type' => 'integer',
                    'default' => 1,
                ],
                'perPage' => [
                    'type' => 'integer',
                    'default' => 20,
                ],
                'meta' => [
                    'type' => 'string',
                    'description' => 'Comma-separated meta keys.',
                ],
            ]),
        ]);
    }

    public static function getPosts($request) {
        $postType = sanitize_text_field($request->get_param('postType'));
        if (empty($postType) || !post_type_exists($postType)) {
            return new \WP_Error('invalid_post_type', 'Invalid post type', ['status' => 400]);
        }

        $page = max(1, intval($request->get_param('page') ?? 1));
        $perPage = min(100, intval($request->get_param('perPage') ?? 20));

        $metaParam = $request->get_param('meta');
        $metaKeys = !empty($metaParam)
            ? array_map('trim', explode(',', $metaParam))
            : apply_filters('portalStatsDefaultMetaKeys', [], $postType);

        $cacheKey = 'portal_stats_' . md5($postType . $page . $perPage . implode(',', $metaKeys));
        if ($cached = get_transient($cacheKey)) {
            return rest_ensure_response($cached);
        }

        $query = new \WP_Query([
            'post_type'      => $postType,
            'post_status'    => apply_filters('portalStatsApiStatus', 'publish'),
            'posts_per_page' => $perPage,
            'paged'          => $page,
        ]);

        $posts = [];
        if ($query->have_posts()) {
            $ids = wp_list_pluck($query->posts, 'ID');
            update_postmeta_cache($ids);
            foreach ($query->posts as $post) {
                $meta = [];
                foreach ($metaKeys as $key) {
                    $meta[$key] = get_post_meta($post->ID, $key, true);
                }
                $posts[] = [
                    'id'        => $post->ID,
                    'title'     => get_the_title($post),
                    'thumbnail' => get_the_post_thumbnail_url($post, 'medium'),
                    'meta'      => $meta,
                ];
            }
        }

        $response = ['success' => true, 'count' => count($posts), 'posts' => $posts];
        set_transient($cacheKey, $response, 5 * MINUTE_IN_SECONDS);
        CacheManager::registerCacheKey($postType, $cacheKey);
        return rest_ensure_response($response);
    }
}
