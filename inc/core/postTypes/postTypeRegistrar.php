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
        self::$postTypes = array_merge(self::$postTypes ?? [], $postTypes);
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
                'name'                  => ucfirst(str_replace('_', ' ', $slug)) . 's',
                'singular_name'         => ucfirst(str_replace('_', ' ', $slug)),
                'menu_name'             => ucfirst(str_replace('_', ' ', $slug)) . 's',
                'name_admin_bar'        => ucfirst(str_replace('_', ' ', $slug)),
                'archives'              => ucfirst(str_replace('_', ' ', $slug)) . ' Archives',
                'attributes'            => ucfirst(str_replace('_', ' ', $slug)) . ' Attributes',
                'parent_item_colon'     => 'Parent ' . ucfirst(str_replace('_', ' ', $slug)) . ':',
                'all_items'             => 'All ' . ucfirst(str_replace('_', ' ', $slug)) . 's',
                'add_new_item'          => 'Add New ' . ucfirst(str_replace('_', ' ', $slug)),
                'add_new'               => 'Add New',
                'new_item'              => 'New ' . ucfirst(str_replace('_', ' ', $slug)),
                'edit_item'             => 'Edit ' . ucfirst(str_replace('_', ' ', $slug)),
                'update_item'           => 'Update ' . ucfirst(str_replace('_', ' ', $slug)),
                'view_item'             => 'View ' . ucfirst(str_replace('_', ' ', $slug)),
                'view_items'            => 'View ' . ucfirst(str_replace('_', ' ', $slug)) . 's',
                'search_items'          => 'Search ' . ucfirst(str_replace('_', ' ', $slug)),
                'not_found'             => 'Not found',
                'not_found_in_trash'    => 'Not found in Trash',
                'featured_image'        => 'Featured Image',
                'set_featured_image'    => 'Set featured image',
                'remove_featured_image' => 'Remove featured image',
                'use_featured_image'    => 'Use as featured image',
                'insert_into_item'      => 'Insert into ' . ucfirst(str_replace('_', ' ', $slug)),
                'uploaded_to_this_item' => 'Uploaded to this ' . ucfirst(str_replace('_', ' ', $slug)),
                'items_list'            => ucfirst(str_replace('_', ' ', $slug)) . ' list',
                'items_list_navigation' => ucfirst(str_replace('_', ' ', $slug)) . ' list navigation',
                'filter_items_list'     => 'Filter ' . ucfirst(str_replace('_', ' ', $slug)) . ' list',
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
