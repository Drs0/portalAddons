<?php
// Enqueue Styles
function fmEnqueueStyles(){
    wp_enqueue_style('football-matches-style', PLUGIN_URL . 'assets/stylesheets/footballMatches.css');
}
add_action('wp_enqueue_scripts', 'fmEnqueueStyles');
