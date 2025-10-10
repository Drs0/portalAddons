<?php
use PortalAddons\Core\PostTypeRegistrar;

PostTypeRegistrar::register([
    [
        'slug' => 'car',
        'args' => [
            'menu_icon' => 'dashicons-car',
            'supports'  => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        ],
        'labels' => [
            'name' => 'Cars',
            'singular_name' => 'Car',
        ],
    ],
    [
        'slug' => 'loan_request',
        'args' => [
            'menu_icon' => 'dashicons-clipboard',
            'supports'  => ['title', 'custom-fields'],
            'public'    => false,
            'show_ui'   => true,
        ],
        'labels' => [
            'name' => 'Car Loan Requests',
            'singular_name' => 'Car Loan Request',
        ],
    ],
]);

add_action('add_meta_boxes', function () {
    add_meta_box('carDetailsBox', 'Car Details', function ($post) {
        $price       = get_post_meta($post->ID, 'price', true);
        $year        = get_post_meta($post->ID, 'year', true);
        $mileage     = get_post_meta($post->ID, 'mileage', true);
        $engine      = get_post_meta($post->ID, 'engine', true);
        $loan_amount = get_post_meta($post->ID, 'loan_amount', true);
        $isLoaned    = get_post_meta($post->ID, 'isLoaned', true);
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

add_action('save_post_car', function ($post_id) {
    if (array_key_exists('price', $_POST)) update_post_meta($post_id, 'price', $_POST['price']);
    if (array_key_exists('year', $_POST)) update_post_meta($post_id, 'year', $_POST['year']);
    if (array_key_exists('mileage', $_POST)) update_post_meta($post_id, 'mileage', $_POST['mileage']);
    if (array_key_exists('engine', $_POST)) update_post_meta($post_id, 'engine', $_POST['engine']);
    if (array_key_exists('loan_amount', $_POST)) update_post_meta($post_id, 'loan_amount', $_POST['loan_amount']);
    update_post_meta($post_id, 'isLoaned', isset($_POST['isLoaned']));
});

function getCarDetails($carId) {
    $fields = ['price', 'year', 'mileage', 'engine', 'loan_amount', 'isLoaned'];
    $fields = apply_filters('carDetailFields', $fields);
    $meta   = [];

    foreach ($fields as $field) {
        $meta[$field] = get_post_meta($carId, $field, true);
    }

    $meta['title']     = get_the_title($carId);
    $meta['permalink'] = get_permalink($carId);

    $meta = apply_filters('carDetailsMeta', $meta, $carId);
    return $meta;
}

add_action('init', function () {
    add_rewrite_rule('^loan-car/([0-9]+)/?$', 'index.php?loan_car_id=$matches[1]', 'top');
});
add_filter('query_vars', fn($vars) => array_merge($vars, ['loan_car_id']));

function portalIsCarLoaned($car_id): bool {
    $now = current_time('Y-m-d');
    $args = [
        'post_type'  => 'loan_request',
        'post_status'=> 'publish',
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'car_id', 'value' => $car_id],
            ['key' => 'status', 'value' => 'approved'],
            ['key' => 'loan_start', 'value' => $now, 'compare' => '<='],
            ['key' => 'loan_end', 'value' => $now, 'compare' => '>='],
        ],
    ];
    return (new WP_Query($args))->have_posts();
}

add_action('template_redirect', function () {
    $loanCarId = get_query_var('loan_car_id');
    if (!$loanCarId) return;

    $car = get_post($loanCarId);
    if (!$car || $car->post_type !== 'car') wp_die('Car not found.');
    if (portalIsCarLoaned($loanCarId)) wp_die('Sorry, this car is currently loaned.');

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loanSubmit'])) {
        if (!is_user_logged_in()) wp_die('You must be logged in to request a loan.');
        $user = wp_get_current_user();

        $loanStart = sanitize_text_field($_POST['loanStart']);
        $loanEnd   = sanitize_text_field($_POST['loanEnd']);
        $loanNotes = sanitize_textarea_field($_POST['loanNotes']);

        $carMeta = getCarDetails($loanCarId);

        $requestId = wp_insert_post([
            'post_type'   => 'loan_request',
            'post_title'  => 'Loan Request - ' . $carMeta['title'],
            'post_status' => 'publish',
            'meta_input'  => [
                'userId'     => $user->ID,
                'carId'      => $loanCarId,
                'loanStart'  => $loanStart,
                'loanEnd'    => $loanEnd,
                'notes'      => $loanNotes,
                'status'     => 'pending',
                'price'      => $carMeta['price'],
                'loanAmount' => $carMeta['loanAmount'],
            ],
        ]);

        // Send email to admin
        $adminEmail = get_option('admin_email');
        $message = sprintf(
            "A new loan request has been submitted by %s for car \"%s\".\nStart: %s\nEnd: %s\nView Request: %s",
            $user->display_name,
            $carMeta['title'],
            $loanStart,
            $loanEnd,
            admin_url('post.php?post=' . $requestId . '&action=edit')
        );
        wp_mail($adminEmail, 'New Car Loan Request', $message);

        wp_redirect(add_query_arg('success', '1', get_permalink($loanCarId)));
        exit;
    }

    // Load template file with variables
    $args = [
        'carTitle' => $car->post_title,
        'carId'    => $loanCarId,
    ];

    $template = PLUGIN_TEMPLATE_PATH .'cars/carLoanForm.php' ;
    if (file_exists($template)) {
        include $template;
    } else {
        echo '<h1>' . esc_html($car->post_title) . '</h1>';
        echo '<p>Template "templates/cars/carLoanForm.php" not found.</p>';
    }

    exit;
});


add_filter('the_content', function ($content) {
    if (get_post_type() === 'car' && is_singular('car')) {
        $car_id = get_the_ID();
        if (!portalIsCarLoaned($car_id)) {
            $url = home_url("/loan-car/{$car_id}/");
            $content .= '<p><a href="' . esc_url($url) . '" class="button loan-button" style="background:#0073aa;color:white;padding:8px 14px;border-radius:4px;text-decoration:none;">🚗 Loan this Car</a></p>';
        } else {
            $content .= '<p><strong>🚫 This car is currently loaned.</strong></p>';
        }
    }
    return $content;
});

add_action('add_meta_boxes', function () {
    add_meta_box('loanRequestBox', 'Loan Request Details', function ($post) {
        $user_id = get_post_meta($post->ID, 'user_id', true);
        $car_id  = get_post_meta($post->ID, 'car_id', true);
        $start   = get_post_meta($post->ID, 'loan_start', true);
        $end     = get_post_meta($post->ID, 'loan_end', true);
        $status  = get_post_meta($post->ID, 'status', true) ?: 'pending';
        $notes   = get_post_meta($post->ID, 'notes', true);

        $statuses = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];
        ?>
        <p><strong>User:</strong> <?= esc_html(get_userdata($user_id)->display_name ?? 'Unknown'); ?></p>
        <p><strong>Car:</strong> <?= esc_html(get_the_title($car_id)); ?></p>
        <p><strong>Start:</strong> <?= esc_html($start); ?></p>
        <p><strong>End:</strong> <?= esc_html($end); ?></p>
        <p><strong>Notes:</strong> <?= nl2br(esc_html($notes)); ?></p>
        <hr>
        <p><label>Status:
            <select name="loan_status">
                <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= esc_attr($key); ?>" <?= selected($status, $key, false); ?>><?= esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </label></p>
        <?php
    }, 'loan_request', 'normal', 'default');
});

add_action('save_post_loan_request', function ($post_id) {
    if (isset($_POST['loan_status'])) {
        update_post_meta($post_id, 'status', sanitize_text_field($_POST['loan_status']));
    }
});
