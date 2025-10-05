<?php
namespace PortalAddons\Core;

class PostTypeRegistrar {
    protected static $postTypes = []; 
    public static $regiteredPostTypes = [];

    /**
     * Register multiple post types.
     * 
     * @param array $postTypes Array of post types definitions.
     *      Each item should be like:
     *      [
     *          'slug' => 'cars',
     *          'args' => [...], // register_post_type args
     *          'labels' => [...], // optional, will merge with defaults
     *      ]
     */
    public static function register(array $postTypes = []) {
        self::$postTypes = $postTypes;
        add_action('init', [__CLASS__, 'registerAll']);
    }

    public static function registerAll() {
        foreach (self::$postTypes as $postType) {
            $slug = $postType['slug'] ?? '';
            if (!$slug) {
                continue;
            }

            $labels = $postType['labels'] ?? [];
            $args = $postType['args'] ?? [];

            $defaultLabels = [
                'name'                  => ucfirst($slug) . 's',
                'singular_name'         => ucfirst($slug),
                'menu_name'             => ucfirst($slug) . 's',
                'name_admin_bar'        => ucfirst($slug),
                'archives'              => ucfirst($slug) . ' Archives',
                'attributes'            => ucfirst($slug) . ' Attributes',
                'parent_item_colon'     => 'Parent ' . ucfirst($slug) . ':',
                'all_items'             => 'All ' . ucfirst($slug) . 's',
                'add_new_item'          => 'Add New ' . ucfirst($slug),
                'add_new'               => 'Add New',
                'new_item'              => 'New ' . ucfirst($slug),
                'edit_item'             => 'Edit ' . ucfirst($slug),
                'update_item'           => 'Update ' . ucfirst($slug),
                'view_item'             => 'View ' . ucfirst($slug),
                'view_items'            => 'View ' . ucfirst($slug) . 's',
                'search_items'          => 'Search ' . ucfirst($slug),
                'not_found'             => 'Not found',
                'not_found_in_trash'    => 'Not found in Trash',
                'featured_image'        => 'Featured Image',
                'set_featured_image'    => 'Set featured image',
                'remove_featured_image' => 'Remove featured image',
                'use_featured_image'    => 'Use as featured image',
                'insert_into_item'      => 'Insert into ' . ucfirst($slug),
                'uploaded_to_this_item' => 'Uploaded to this ' . ucfirst($slug),
                'items_list'            => ucfirst($slug) . ' list',
                'items_list_navigation' => ucfirst($slug) . ' list navigation',
                'filter_items_list'     => 'Filter ' . ucfirst($slug) . ' list',
            ];

            $args['labels'] = array_merge($defaultLabels, $labels);
            
            $args = array_merge([
                'label'                 => ucfirst($slug),
                'public'                => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'menu_position'         => 5,
                'supports'              => ['title', 'editor', 'thumbnail'],
                'has_archive'           => true,
                'show_in_rest'          => true,
            ], $args);

            register_post_type($slug, $args);
            self::$regiteredPostTypes[] = $slug;
        }
    }

    public static function getRegisteredPostTypes()
    {
        return self::$regiteredPostTypes;
    }
}
