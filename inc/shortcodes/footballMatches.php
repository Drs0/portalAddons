<?php
function fm_display_matches() {
    ob_start();
    $html = include(PLUGIN_PATH . 'templates/fetchMatchesTemplate.php');
    $html = ob_get_clean();
    return $html;
}

function fm_register_shortcode() {
    add_shortcode('football_matches', 'fm_display_matches');
}

add_action('init', 'fm_register_shortcode');
