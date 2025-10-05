<?php
namespace PortalNetwork\Core\Classes;

class portalAuthentication {

    public function __construct() {
        // Add rewrite rules and custom route handlers
        add_action('init', [$this, 'addRewriteRules']);
        add_filter('query_vars', [$this, 'addQueryVars']);
        add_action('template_redirect', [$this, 'handleCustomRoutes']);

        // Handle login and registration and lost password
        add_action('init', [$this, 'handleLogin']);
        add_action('init', [$this, 'handleRegistration']);
        add_action('init', [$this, 'handleLostPassword']);

        // Restrict admin area and admin bar
        add_action('admin_init', [$this, 'restrictAdminAccess'], 1);
        add_filter('show_admin_bar', [$this, 'maybeHideAdminBar']);
    }

    /**
     * Add custom rewrite rules
     */
    public function addRewriteRules() {
        add_rewrite_rule('^login/?$', 'index.php?portal_auth_action=login', 'top');
        add_rewrite_rule('^register/?$', 'index.php?portal_auth_action=register', 'top');
        add_rewrite_rule('^logout/?$', 'index.php?portal_auth_action=logout', 'top');
        add_rewrite_rule('^lost-password/?$', 'index.php?portal_auth_action=lostpassword', 'top');
    }

    /**
     * Register custom query variables
     */
    public function addQueryVars($vars) {
        $vars[] = 'portal_auth_action';
        return $vars;
    }

    /**
     * Render login/register pages based on rewrite
     */
    public function handleCustomRoutes() {
        $action = get_query_var('portal_auth_action');

        switch ($action) {
            case 'login':
                echo $this->renderLoginForm();
                exit;
            case 'register':
                echo $this->renderRegisterForm();
                exit;
            case 'logout':
                if (is_user_logged_in()) {
                    wp_logout();
                }
                wp_redirect(home_url('/login/'));
                exit;
            case 'lostpassword':
                echo $this->renderLostPasswordForm();
                exit;
        }
    }

    /**
     * Show login form
     */
    public function renderLoginForm() {
        if (is_user_logged_in()) {
            return '<p>You are already logged in. <a href="' . esc_url(wp_logout_url(home_url())) . '">Logout</a></p>';
        }

        ob_start();
        $templatePath = PLUGIN_TEMPLATE_PATH . 'auth/loginForm.php';
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            echo '<p>Login form template not found.</p>';
        }

        if (isset($_GET['login']) && $_GET['login'] === 'failed') {
            echo '<p style="color:red;">Login failed. Please try again.</p>';
        }

        return ob_get_clean();
    }

    /**
     * Show registration form
     */
    public function renderRegisterForm() {
        if (is_user_logged_in()) {
            return '<p>You are already registered and logged in. <a href="' . esc_url(wp_logout_url(home_url())) . '">Logout</a></p>';
        }

        ob_start(); 
        $templatePath = PLUGIN_TEMPLATE_PATH . 'auth/registerForm.php';
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            echo '<p>Registration form template not found.</p>';
        }
        return ob_get_clean();
    }

    /**
     * Handle login request
     */
    public function handleLogin() {
        if (
            isset($_POST['portalLoginSubmit']) &&
            wp_verify_nonce($_POST['portalLoginNonce'], 'portalFrontLogin')
        ) {
            $credentials = [
                'user_login'    => sanitize_user($_POST['portalUsername']),
                'user_password' => $_POST['portalPassword'],
                'remember'      => true,
            ];

            $user = wp_signon($credentials, is_ssl());

            if (is_wp_error($user)) {
                wp_redirect(add_query_arg('login', 'failed', wp_get_referer()));
                exit;
            } else {
                wp_redirect(home_url());
                exit;
            }
        }
    }

    /**
     * Handle registration request
     */
    public function handleRegistration() {
        if (
            isset($_POST['portalRegisterSubmit']) &&
            wp_verify_nonce($_POST['portalRegisterNonce'], 'portalFrontRegister')
        ) {
            $username = sanitize_user($_POST['portalRegUsername']);
            $email    = sanitize_email($_POST['portalRegEmail']);
            $password = $_POST['portalRegPassword'];

            if (username_exists($username) || email_exists($email)) {
                wp_die('Username or email already exists.');
            }

            $userId = wp_create_user($username, $password, $email);

            if (is_wp_error($userId)) {
                wp_die('Registration failed.');
            }

            // Set default role
            $user = get_user_by('id', $userId);
            $user->set_role('subscriber');

            // Auto-login after registration
            wp_set_current_user($userId);
            wp_set_auth_cookie($userId);

            wp_redirect(home_url());
            exit;
        }
    }

    /**
     * Restrict access to admin panel for non-admins
     */
    public function restrictAdminAccess() {
        if (
            is_admin() &&
            !defined('DOING_AJAX') &&
            !current_user_can('manage_options')
        ) {
            wp_redirect(home_url());
            exit;
        }
    }

    /**
     * Hide admin bar for non-admins
     */
    public function maybeHideAdminBar($show) {
        if (!current_user_can('manage_options')) {
            return false;
        }
        return $show;
    }

    /**
     * Show lost password form
     */
    public function renderLostPasswordForm() {
        ob_start();
        $templatePath = PLUGIN_TEMPLATE_PATH . 'auth/lostPasswordForm.php';
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            echo '<p>Lost password form template not found.</p>';
        }
        return ob_get_clean();
    }
    /**
     * Handle lost password request
     */
    public function handleLostPassword() {
        if (
            isset($_POST['portalLostPasswordSubmit']) &&
            wp_verify_nonce($_POST['portalLostPasswordNonce'], 'portalLostPassword')
        ) {
            $user_login = sanitize_text_field($_POST['user_login']);
            $user = get_user_by('login', $user_login);

            if (!$user && is_email($user_login)) {
                $user = get_user_by('email', $user_login);
            }

            if (!$user) {
                wp_die('User not found.');
            }

            $reset = retrieve_password($user->user_login);

            if (is_wp_error($reset)) {
                wp_die('Could not send reset email.');
            } else {
                wp_redirect(home_url('/login/?reset=success'));
                exit;
            }
        }
    }

}
