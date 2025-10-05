<?php
function fm_fetch_matches() {
    $league_id = sanitize_text_field($_POST['league_id']);

    $url = "https://api.football-data.org/v4/competitions/$league_id/matches?status=FINISHED&limit=5";

    $response = wp_remote_get($url, array(
        'headers' => array('X-Auth-Token' => MATCHES_TOKEN)
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('Failed to fetch data.');
    }

    $body = wp_remote_retrieve_body($response);
    wp_send_json_success(json_decode($body, true));
}
add_action('wp_ajax_fm_fetch_matches', 'fm_fetch_matches');
add_action('wp_ajax_nopriv_fm_fetch_matches', 'fm_fetch_matches');
