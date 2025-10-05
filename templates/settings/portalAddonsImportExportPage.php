<?php
$post_types = get_post_types(
    [
        'public'   => true,
        '_builtin' => false,
    ],
    'objects'
);
?>
<div class="wrap">
    <h1><?php _e('Import/Export Post Type', 'portal-addons'); ?></h1>

    <h2><?php _e('Export Post Type', 'portal-addons'); ?></h2>
    <form method="post" action="">
        <label for="post_type_selector"><?php _e('Select Post Type:', 'portal-addons'); ?></label>
        <select name="post_type" id="post_type_selector" required>
            <?php foreach ($post_types as $post_type) : ?>
                <option value="<?php echo esc_attr($post_type->name); ?>">
                    <?php echo esc_html($post_type->labels->singular_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php wp_nonce_field('portalAddonsExportNonce'); ?>
        <input type="submit" name="portalAddonsExport" class="button button-primary" value="<?php _e('Export Post Type', 'portal-addons'); ?>" />
    </form>

    <hr />

    <h2><?php _e('Import Post Type', 'portal-addons'); ?></h2>
    <form method="post" enctype="multipart/form-data" action="">
        <?php wp_nonce_field('portalAddonsImportNonce'); ?>
        <label for="import_post_type"><?php _e('Select Post Type to Import:', 'portal-addons'); ?></label>
        <select name="import_post_type" id="import_post_type" required>
            <?php foreach ($post_types as $post_type) : ?>
                <option value="<?php echo esc_attr($post_type->name); ?>">
                    <?php echo esc_html($post_type->labels->singular_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="file" name="importFile" accept=".csv,text/csv" required />
        <input type="submit" name="portalAddonsImport" class="button button-secondary" value="<?php _e('Import Post Type', 'portal-addons'); ?>" />
    </form>
</div>