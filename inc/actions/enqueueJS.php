<?php
// Enqueue JS
function fmEnqueueScripts() // fm as Football Matches
{
    wp_enqueue_script(
        'football-matches-js',
        PLUGIN_URL . 'assets/javascripts/football-matches.js',
        array('jquery'),
        '1.0',
        true
    );

    wp_localize_script('football-matches-js', 'fm_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'api_token' => MATCHES_TOKEN
    ));
}

function registerFormJs(){
    wp_enqueue_script('register-form-js', PLUGIN_URL . 'assets/javascripts/auth.js', array('jquery', 'sweetalert-js'), '1.0', true );
}

function sweetAlertJs(){
    wp_enqueue_script('sweetalert-js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), '11', true );
}

add_action('wp_enqueue_scripts', 'fmEnqueueScripts');
add_action('wp_enqueue_scripts', 'sweetAlertJs');
add_action('wp_enqueue_scripts', 'registerFormJs');
