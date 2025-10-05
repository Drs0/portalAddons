<?php
use PortalAddons\Core\PostTypeRegistrar;

PostTypeRegistrar::register([
    [
        'slug' => 'car',
        'args' => [
            'menu_icon' => 'dashicons-car',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'taxonomies' => ['category', 'post_tag'],
        ],
        'labels' => [
            'name' => 'Cars',
            'singular_name' => 'Car'
        ],
    ],
]);

add_action('add_meta_boxes', function () {
    add_meta_box('carDetailsBox', 'Car Details', function ($post) {
        $price = get_post_meta($post->ID, 'price', true);
        $year = get_post_meta($post->ID, 'year', true);
        $mileage = get_post_meta($post->ID, 'mileage', true);
        $engine = get_post_meta($post->ID, 'engine', true);
        $loan_amount = get_post_meta($post->ID, 'loan_amount', true);
        $isLoaned = get_post_meta($post->ID, 'isLoaned', true);
        ?>
        <label>Price: <input type="number" name="price" value="<?= esc_attr($price) ?>"></label><br>
        <label>Year: <input type="number" name="year" value="<?= esc_attr($year) ?>"></label><br>
        <label>Mileage: <input type="number" name="mileage" value="<?= esc_attr($mileage) ?>"></label><br>
        <label>Engine: <input type="text" name="engine" value="<?= esc_attr($engine) ?>"></label><br>
        <label>Loan Amount: <input type="number" name="loan_amount" value="<?= esc_attr($loan_amount) ?>"></label><br>
        <label><input type="checkbox" name="isLoaned" value="1" <?= checked($isLoaned, true, false) ?>> Loaned</label>
        <?php
    }, 'car', 'normal', 'default');
});

function getCarDetails($carId) {
    $fields = ['price', 'year', 'mileage', 'engine', 'loan_amount', 'isLoaned'];
    $fields = apply_filters('carDetailFields', $fields);
    $meta   = [];

    foreach ($fields as $field) {
        $meta[$field] = get_post_meta($carId, $field, true);
    }
    $meta = apply_filters('carDetailsMeta', $meta, $carId);
    return $meta;
}

// Save meta box values
add_action('save_post_car', function ($post_id) {
    if (array_key_exists('price', $_POST)) update_post_meta($post_id, 'price', $_POST['price']);
    if (array_key_exists('year', $_POST)) update_post_meta($post_id, 'year', $_POST['year']);
    if (array_key_exists('mileage', $_POST)) update_post_meta($post_id, 'mileage', $_POST['mileage']);
    if (array_key_exists('engine', $_POST)) update_post_meta($post_id, 'engine', $_POST['engine']);
    if (array_key_exists('loan_amount', $_POST)) update_post_meta($post_id, 'loan_amount', $_POST['loan_amount']);
    update_post_meta($post_id, 'isLoaned', isset($_POST['isLoaned']));
});
