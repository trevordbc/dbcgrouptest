<?php

function dbc_station_enqueue_scripts($hook) {
    if ('toplevel_page_markets' !== $hook && 'markets_page_connector' !== $hook && 'markets_page_remote-website-settings' !== $hook) {
        return;
    }

    // Enqueue the color picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    // Enqueue the media uploader
    wp_enqueue_media();

    // Enqueue custom admin script
    wp_register_script('dbc-station-admin-script', plugin_dir_url(__FILE__) . 'javascript/dbc-station-admin-script.js', array('jquery', 'wp-color-picker', 'media-upload'), false, true);

    // Localize script data
    $script_data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'ajax_nonce' => wp_create_nonce('upload_media'),
    );
    wp_localize_script('dbc-station-admin-script', 'dbc_station', $script_data);

    wp_enqueue_script('dbc-station-admin-script');
}
add_action('admin_enqueue_scripts', 'dbc_station_enqueue_scripts');