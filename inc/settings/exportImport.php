<?php
add_action('admin_menu', function() {
    add_menu_page(
        __('Portal Addons Settings', 'portal-addons'),
        __('Portal Addons', 'portal-addons'),
        'manage_options',
        'portalAddonsSettings',
        'portalAddonsSettingsPage',
        'dashicons-hammer',
        100
    );

    add_submenu_page(
        'portalAddonsSettings',
        __('Import/Export', 'portal-addons'),
        __('Import/Export', 'portal-addons'),
        'manage_options',
        'portalAddonsImportExport',
        'portalAddonsImportExportPage'
    );
});

function portalAddonsSettingsPage() {
    include PLUGIN_PATH . 'templates/settings/portalAddonsSettingsPage.php';
}

function portalAddonsImportExportPage() {
    include PLUGIN_PATH . 'templates/settings/portalAddonsImportExportPage.php';
}

add_action('admin_init', function() {
    if (isset($_POST['portalAddonsExport']) && check_admin_referer('portalAddonsExportNonce') && isset($_POST['post_type'])) {
        portalAddonsExportPostType($_POST['post_type']);
    }

    if (isset($_POST['portalAddonsImport']) && check_admin_referer('portalAddonsImportNonce')) {
        if (!empty($_FILES['importFile']['tmp_name']) && isset($POST['import_post_type'])) {
            portalAddonsImportPostType($_FILES['importFile']['tmp_name'], $_POST['import_post_type']);
        }
    }
});

function portalAddonsExportPostType($post_type) {
    $args = [
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'post_status'    => 'any',
    ];
    $postTypes = get_posts($args);

    if (empty($postTypes)) {
        wp_die('No postTypes found to export.');
    }

    $allMetaKeys = [];

    foreach ($postTypes as $car) {
        $postMeta = get_post_meta($car->ID);
        foreach ($postMeta as $metaKey => $metaValue) {
            if (!in_array($metaKey, $allMetaKeys)) {
                $allMetaKeys[] = $metaKey;
            }
        }
    }

    $headers = array_merge(
        ['ID', 'post_title', 'post_content', 'post_excerpt'],
        $allMetaKeys
    );

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=postTypes-export-' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, $headers);

    foreach ($postTypes as $car) {
        $postMeta = get_post_meta($car->ID);

        $row = [
            $car->ID,
            $car->post_title,
            $car->post_content,
            $car->post_excerpt,
        ];

        foreach ($allMetaKeys as $metaKey) {
            if (isset($postMeta[$metaKey])) {
                $value = maybe_serialize($postMeta[$metaKey]);
                if (is_array($postMeta[$metaKey])) {
                    $value = implode('|', array_map('maybe_serialize', $postMeta[$metaKey]));
                } else {
                    $value = maybe_serialize($postMeta[$metaKey][0]);
                }
                $row[] = $value;
            } else {
                $row[] = '';
            }
        }

        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

function portalAddonsImportPostType($post_type, $filePath) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>CSV file not found or not readable.</p></div>';
        });
        return;
    }

    $header = null;
    $data = [];

    if (($handle = fopen($filePath, 'r')) !== false) {
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            if (!$header) {
                $header = $row;
            } else {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }

    if (empty($data)) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>CSV file is empty or invalid format.</p></div>';
        });
        return;
    }

    foreach ($data as $item) {
        $newPost = [
            'post_title'   => isset($item['post_title']) ? sanitize_text_field($item['post_title']) : '',
            'post_content' => isset($item['post_content']) ? wp_kses_post($item['post_content']) : '',
            'post_excerpt' => isset($item['post_excerpt']) ? sanitize_text_field($item['post_excerpt']) : '',
            'post_status'  => 'publish',
            'post_type'    => $post_type,  // <-- Use the selected post type here!
        ];

        $postId = wp_insert_post($newPost);

        if (!is_wp_error($postId)) {
            foreach ($item as $key => $value) {
                if (in_array($key, ['post_title', 'post_content', 'post_excerpt'])) {
                    continue;
                }
                update_post_meta($postId, sanitize_key($key), sanitize_text_field($value));
            }
        }
    }

    add_action('admin_notices', function() {
        echo '<div class="notice notice-success"><p>CSV Import completed successfully.</p></div>';
    });
}

