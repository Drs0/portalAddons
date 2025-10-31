<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$registeredPostTypes = get_option('portal_addons_registered_post_types', []);

if (!empty($registeredPostTypes) && is_array($registeredPostTypes)) {
    foreach ($registeredPostTypes as $postType) {
        $posts = get_posts([
            'post_type'      => $postType,
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'fields'         => 'ids',
        ]);

        foreach ($posts as $postId) {
            wp_delete_post($postId, true);
        }
    }
}

delete_option('portal_addons_registered_post_types');
