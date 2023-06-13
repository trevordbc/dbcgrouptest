<?php

// Register REST API route for remote station settings
function dbc_station_register_rest_routes() {
    register_rest_route('dbc-station/v1', '/settings', array(
        'methods' => 'GET',
        'callback' => 'dbc_station_get_settings',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'dbc_station_register_rest_routes');

// Get settings for remote stations
function dbc_station_get_settings(WP_REST_Request $request) {
    $connector_key = $request->get_param('connector_key');

    if (!$connector_key) {
        return new WP_Error('no_connector_key', 'No connector key provided', array('status' => 400));
    }

    $stored_connector_key = get_option('dbc_station_connector_key');

    if ($connector_key !== $stored_connector_key) {
        return new WP_Error('invalid_connector_key', 'Invalid connector key', array('status' => 403));
    }

    $settings = get_option('dbc_station_settings', array());

    return new WP_REST_Response($settings, 200);
}