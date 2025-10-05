<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$registered_post_types = get_option('portal_addons_registered_post_types', []);

if (!empty($registered_post_types) && is_array($registered_post_types)) {
    foreach ($registered_post_types as $post_type) {
        $posts = get_posts([
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'fields'         => 'ids',
        ]);

        foreach ($posts as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
}

delete_option('portal_addons_registered_post_types');
