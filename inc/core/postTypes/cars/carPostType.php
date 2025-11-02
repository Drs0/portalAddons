<?php
namespace PortalAddons\Core\postTypes;

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

class CarLoanManager {
    private static $instance;

    public function __construct() {
        add_action('init', [$this, 'registerRewriteLoanRule']);
        add_action('add_meta_boxes', [$this, 'registerMetaBoxes']);
        add_action('save_post_car', [$this, 'saveCarMeta']);
        add_action('save_post_loan_request', [$this, 'saveLoanRequestMeta']);
        add_action('template_redirect', [$this, 'handleLoanFormSubmission']);
        add_action('manage_loan_request_posts_custom_column', [$this, 'renderLoanRequestColumns'], 10, 2);
        add_action('admin_post_update_loan_request_status', [$this, 'handleLoanStatusAction']);
        add_filter('query_vars', [$this, 'addQueryVars']);
        add_filter('the_content', [$this, 'displayLoanButton']);
        add_filter('manage_loan_request_posts_columns', [$this, 'addLoanRequestColumns']);
    }

    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function registerRewriteLoanRule() {
        add_rewrite_rule('^loan-car/([0-9]+)/?$', 'index.php?loan_car_id=$matches[1]', 'top');
    }

    public function registerMetaBoxes() {
        add_meta_box('carDetailsBox', 'Car Details', [$this, 'renderCarMetaBox'], 'car', 'normal', 'default');
        add_meta_box('loanRequestBox', 'Loan Request Details', [$this, 'renderLoanRequestMetaBox'], 'loan_request', 'normal', 'default');
    }

    public function renderCarMetaBox($post) {
        $price = get_post_meta($post->ID, 'price', true);
        $year = get_post_meta($post->ID, 'year', true);
        $mileage = get_post_meta($post->ID, 'mileage', true);
        $engine = get_post_meta($post->ID, 'engine', true);
        $loanAmount = get_post_meta($post->ID, 'loanAmount', true);
        $isLoaned = get_post_meta($post->ID, 'isLoaned', true);

        include PLUGIN_TEMPLATE_PATH . 'cars/carDetailsBoxForm.php';
    }

    public function saveCarMeta($postId) {
        if (array_key_exists('price', $_POST)) update_post_meta($postId, 'price', $_POST['price']);
        if (array_key_exists('year', $_POST)) update_post_meta($postId, 'year', $_POST['year']);
        if (array_key_exists('mileage', $_POST)) update_post_meta($postId, 'mileage', $_POST['mileage']);
        if (array_key_exists('engine', $_POST)) update_post_meta($postId, 'engine', $_POST['engine']);
        if (array_key_exists('loanAmount', $_POST)) update_post_meta($postId, 'loanAmount', $_POST['loanAmount']);
        update_post_meta($postId, 'isLoaned', isset($_POST['isLoaned']));
    }

    public function renderLoanRequestMetaBox($post) {
        $userId = get_post_meta($post->ID, 'userId', true);
        $carId = get_post_meta($post->ID, 'carId', true);
        $phone = get_post_meta($post->ID, 'phone', true);
        $start = get_post_meta($post->ID, 'loanStart', true);
        $end = get_post_meta($post->ID, 'loanEnd', true);
        $status = get_post_meta($post->ID, 'status', true) ?: 'pending';
        $notes = get_post_meta($post->ID, 'notes', true);
        $statuses = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];

        include PLUGIN_TEMPLATE_PATH . 'cars/loanRequestBoxForm.php';
    }

    public function saveLoanRequestMeta($postId) {
        if (isset($_POST['loanStatus'])) {
            $status = sanitize_text_field($_POST['loanStatus']);
            update_post_meta($postId, 'status', $status);
            $carId = get_post_meta($postId, 'carId', true);

            if (!$carId) return; 
            
            update_post_meta($carId, 'isLoaned', $status === 'approved');

            if ($status === 'approved') {
                $loanStart = get_post_meta($postId, 'loanStart', true);
                $loanEnd   = get_post_meta($postId, 'loanEnd', true);
                $this->updateCarLoanStats($carId, $loanStart, $loanEnd);
            }
        }
    }

    public function addQueryVars($vars) {
        $vars[] = 'loan_car_id';
        return $vars;
    }

    public function portalIsCarLoaned($carId): bool {
        $tomorrow = date('Y-m-d', strtotime('+1 day', current_time('timestamp')));
        $args = [
            'post_type' => 'loan_request',
            'post_status' => 'publish',
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'carId', 'value' => $carId],
                ['key' => 'status', 'value' => 'approved'],
                ['key' => 'loanStart', 'value' => $tomorrow, 'compare' => '<='],
            ],
            'posts_per_page' => 1,
        ];

        $query = new \WP_Query($args);
        return $query->have_posts();
    }

    public function getCarDetails($carId) {
        $fields = ['price', 'year', 'mileage', 'engine', 'loanAmount', 'isLoaned'];
        $fields = apply_filters('carDetailFields', $fields);
        $meta = [];
        foreach ($fields as $field) {
            $meta[$field] = get_post_meta($carId, $field, true);
        }

        $meta['title'] = get_the_title($carId);

        return apply_filters('carDetailsMeta', $meta, $carId);
    }

    public function handleLoanFormSubmission() {
        $loanCarId = get_query_var('loan_car_id');
        if (!$loanCarId) return;

        $car = get_post($loanCarId);
        if (!$car || $car->post_type !== 'car') wp_die('Car not found.');
        if ($this->portalIsCarLoaned($loanCarId)) wp_die('Sorry, this car is currently loaned.');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loanSubmit'])) {
            $allowGuests = apply_filters('userShouldNotBeRegistered', true);
            if (!is_user_logged_in() && !$allowGuests) wp_die('You must be logged in to request a loan.');

                $loanFirstName = sanitize_text_field($_POST['loanFirstName'] ?? '');
                $loanLastName  = sanitize_text_field($_POST['loanLastName'] ?? '');
                $loanPhone     = sanitize_text_field($_POST['loanPhone'] ?? '');
                $loanStart     = sanitize_text_field($_POST['loanStart'] ?? '');
                $loanEnd       = sanitize_text_field($_POST['loanEnd'] ?? '');
                $loanNotes     = sanitize_textarea_field($_POST['loanNotes'] ?? '');

                if (empty($loanFirstName) || empty($loanLastName) || empty($loanPhone)) {
                    wp_die('Please fill all required fields.');
                }

                if ($loanEnd < $loanStart) {
                    wp_die('Loan end date cannot be earlier than start date.');
                }

                $carMeta = $this->getCarDetails($loanCarId);

                $user = is_user_logged_in() ? wp_get_current_user() : null;
                $userId = $user ? $user->ID : 0;
                $displayName = $user ? $user->display_name : $loanFirstName . ' ' . $loanLastName;

                $requestId = wp_insert_post([
                    'post_type' => 'loan_request',
                    'post_title' => 'Loan Request - ' . $carMeta['title'],
                    'post_status' => 'publish',
                    'meta_input' => [
                        'userId' => $userId,
                        'carId' => $loanCarId,
                        'loanStart' => $loanStart,
                        'loanEnd' => $loanEnd,
                        'notes' => $loanNotes,
                        'phone' => $loanPhone,
                        'firstName' => $loanFirstName,
                        'lastName' => $loanLastName,
                        'status' => 'pending',
                        'price' => $carMeta['price'],
                        'loanAmount' => $carMeta['loanAmount'],
                    ],
                ]);

                $adminEmail = get_option('admin_email');
                wp_mail($adminEmail, 'New Car Loan Request', sprintf(
                    "A new loan request has been submitted by %s for car \"%s\".\nStart: %s\nEnd: %s\nPhone: %s\nView Request: %s",
                    $displayName,
                    $carMeta['title'],
                    $loanStart,
                    $loanEnd,
                    $loanPhone,
                    admin_url('post.php?post=' . $requestId . '&action=edit')
                ));

                wp_redirect(add_query_arg('success', '1', get_permalink($loanCarId)));
                exit;
        }

        $template = PLUGIN_TEMPLATE_PATH . 'cars/carLoanForm.php';
        if (file_exists($template)) {
            $args = [
                'carTitle' => $car->post_title,
                'carId' => $loanCarId,
            ];
            include $template;
        } else {
            echo '<h1>' . esc_html($car->post_title) . '</h1>';
            echo '<p>Template "templates/cars/carLoanForm.php" not found.</p>';
        }

        exit;
    }

    public function displayLoanButton($content) {
        if (get_post_type() === 'car' && is_singular('car')) {
            $carId = get_the_ID();
            if (!$this->portalIsCarLoaned($carId)) {
                $url = home_url("/loan-car/{$carId}/");
                $content .= '<p class="portal-button"><a href="' . esc_url($url) . '" class="portal-button button-loan">Loan this Car</a></p>';
            } else {
                $content .= '<p><strong>This car is currently loaned.</strong></p>';
            }
        }
        return $content;
    }

    public function addLoanRequestColumns($columns) {
        $newColumns = [];
        foreach ($columns as $key => $label) {
            $newColumns[$key] = $label;
            if ($key === 'title') {
                $newColumns['request_user'] = 'Requested By';
                $newColumns['loan_actions'] = 'Actions';
            }
        }
        return $newColumns;
    }

    public function renderLoanRequestColumns($column, $postId) {
        if ($column === 'request_user') {
            $userId = get_post_meta($postId, 'userId', true);
            $firstName = get_post_meta($postId, 'firstName', true);
            $lastName  = get_post_meta($postId, 'lastName', true);

            if ($userId) {
                $user = get_userdata($userId);
                echo esc_html($user ? $user->display_name : 'Unknown');
            } elseif ($firstName || $lastName) {
                echo esc_html(trim("$firstName $lastName"));
            } else {
                echo '—';
            }
        }

        if ($column === 'loan_actions') {
            $status = get_post_meta($postId, 'status', true);
            $approveUrl = wp_nonce_url(admin_url('admin-post.php?action=update_loan_request_status&status=approved&post=' . $postId), 'update_loan_status');
            $rejectUrl = wp_nonce_url(admin_url('admin-post.php?action=update_loan_request_status&status=rejected&post=' . $postId), 'update_loan_status');

            if ($status === 'pending') {
                echo '<a href="' . esc_url($approveUrl) . '" class="button button-small">Approve</a> ';
                echo '<a href="' . esc_url($rejectUrl) . '" class="button button-small">Reject</a>';
            } else {
                echo '<strong>' . ucfirst($status) . '</strong>';
            }
        }
    }

    public function handleLoanStatusAction() {
        if (!current_user_can('edit_posts') || !check_admin_referer('update_loan_status')) {
            wp_die('Unauthorized action.');
        }

        $postId = intval($_GET['post']);
        $status = sanitize_text_field($_GET['status']);

        if ($postId && in_array($status, ['approved', 'rejected'], true)) {
            update_post_meta($postId, 'status', $status);

            $carId = get_post_meta($postId, 'carId', true);
            if ($carId) {
                update_post_meta($carId, 'isLoaned', $status === 'approved');
            }
        }

        wp_redirect(wp_get_referer());
        exit;
    }

    private function updateCarLoanStats($carId, $loanStart, $loanEnd){
        $loanCount = (int) get_post_meta($carId, 'loanCount', true);
        $loanCount++;

        update_post_meta($carId, 'loanCount', $loanCount);
        update_post_meta($carId, 'lastLoanDate', current_time('mysql'));

        if ($loanStart && $loanEnd) {
            $days = (strtotime($loanEnd) - strtotime($loanStart)) / DAY_IN_SECONDS;
            $days = max(0, $days);
            $totalDays = (float) get_post_meta($carId, 'totalLoanDays', true);
            update_post_meta($carId, 'totalLoanDays', $totalDays + $days);
        }
    }
    
}

new CarLoanManager();
