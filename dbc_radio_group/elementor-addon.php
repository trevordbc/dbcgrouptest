<?php
/**
 * Plugin Name: Dick Broadcasting Company
 * Description: Radio Group Website Extensions
 * Version:     1.0.0
 * Author:      DBC Next
 * Author URI:  https://dbcnext.com
 * Text Domain: elementor-addon
 */

function register_dbc_radio_widgets() {
    require_once(__DIR__ . '/widgets/podcast-player-widget.php');
    require_once(__DIR__ . '/widgets/podcast-player-widget-advanced.php');

    $widgets_manager = \Elementor\Plugin::instance()->widgets_manager;

    $widgets_manager->register_widget_type(new \Elementor_Podcast_Player_Widget());
    $widgets_manager->register_widget_type(new \Elementor_Podcast_Player_Widget_Advanced());
}
add_action('elementor/widgets/widgets_registered', 'register_dbc_radio_widgets');

include_once (plugin_dir_path( __FILE__ ) . '/bios-post-type.php' );
include_once (plugin_dir_path( __FILE__ ) . '/syndicated-stations-post-type.php' );
include_once (plugin_dir_path( __FILE__ ) . '/station-settings.php' );


function podcast_enqueue_mediaelement() {
    wp_enqueue_style('wp-mediaelement');
    wp_enqueue_script('wp-mediaelement');
	
	if ( is_active_widget( false, false, 'elementor-podcast-player-widget-advanced', true ) ) {
        wp_enqueue_script('audio_player_script', plugin_dir_url(__FILE__) . 'javascript/audio_player.js', array('jquery'), '', true);
    	wp_enqueue_style('audio_player_style', plugin_dir_url(__FILE__) . 'css/audio_player.css');
    }

}
add_action('wp_enqueue_scripts', 'podcast_enqueue_mediaelement');

// Add this code to your plugin file
function enqueue_podcast_player_scripts() {
		wp_enqueue_script('podcast-player', plugin_dir_url(__FILE__) . 'javascript/audio_player - old.js', ['jquery'], '1.0.0', true);

		$ajax_data = [
			'ajaxurl' => admin_url('admin-ajax.php'),
		];
		wp_localize_script('podcast-player', 'PodcastPlayer', $ajax_data);
}
add_action('wp_enqueue_scripts', 'enqueue_podcast_player_scripts');

function load_more_podcasts() {
    // Check if the required parameters are set.
    if (!isset($_GET['rss_feed_url']) || !isset($_GET['offset']) || !isset($_GET['limit'])) {
        wp_send_json_error('Missing required parameters', 400);
    }

    // Retrieve and sanitize the parameters.
    $rss_feed_url = sanitize_text_field($_GET['rss_feed_url']);
    $offset = intval($_GET['offset']);
    $limit = intval($_GET['limit']);

    // Fetch the podcast data from the RSS feed using the provided parameters.
    $rss = fetch_feed($rss_feed_url);
    $podcast_data = [];

    if (!is_wp_error($rss)) {
        $max_items = $rss->get_item_quantity($limit);
        $rss_items = $rss->get_items($offset, $max_items);

        foreach ($rss_items as $item) {
            $title = $item->get_title();
            $url = esc_url($item->get_enclosure()->get_link());
            $duration = esc_html($item->get_enclosure()->get_duration());
			$guid = $item->get_id();

            $podcast_data[] = [
                'title' => $title,
                'url' => $url,
                'duration' => $duration,
				'guid' => $guid
            ];
        }
    }

    // Send the response.
    wp_send_json_success($podcast_data);
}
add_action('wp_ajax_load_more_podcasts', 'load_more_podcasts');
add_action('wp_ajax_nopriv_load_more_podcasts', 'load_more_podcasts');


function sgplayer_html() {
	echo '<div class="sgplayer-website-footer">';
	echo '<div class="sgplayer-embed" style="width:100%;height:120px;"></div>';
	echo '</div>';
	echo '</div>';
}
add_action( 'wp_footer', 'sgplayer_html' );

function sgplayer_enqueue_livestream() {
	// Get the value of the Call Letters option
	$call_letters = get_option('dbc_station_call_letters');
	$frequency = get_option('dbc_station_frequency');

	// Check if the value exists and display it in lowercase
	$call_letters_lower = !empty($call_letters) ? strtolower($call_letters) : 'NaN';
	$frequency_lower = !empty($frequency) ? strtolower($frequency) : 'NaN';
	
	wp_enqueue_script('sgplayer_script', plugin_dir_url(__FILE__) . '/sgplayer/javascript/player.js', array('jquery'), '', true);
	wp_enqueue_style('sgplayer_style', plugin_dir_url(__FILE__) . '/sgplayer/css/player.css');
	
	wp_localize_script('sgplayer_script', 'sgplayer_data', array(
        'call_letters_lower' => esc_html($call_letters_lower),
		'frequency_lower' => esc_html($frequency_lower),
    ));
	
	sgplayer_html();
}
add_action('wp_enqueue_scripts', 'sgplayer_enqueue_livestream');

function dbc_station_init_markets() {
    include plugin_dir_path(__FILE__) . 'markets/connector.php';
    include plugin_dir_path(__FILE__) . 'markets/enqueue_scripts.php';
    include plugin_dir_path(__FILE__) . 'markets/rest_api.php';

    // Check if a station is selected
    if (isset($_GET['station'])) {
        include plugin_dir_path(__FILE__) . 'markets/remote_website_settings_form.php';
    }
}

function dbc_station_market_admin_menu() {
    add_menu_page(
        'Markets', // Page title
        'Markets', // Menu title
        'manage_options', // Capability
        'dbc_station_market_page', // Menu slug
        'dbc_station_init_markets' // Function to call
    );
}

add_action('admin_menu', 'dbc_station_market_admin_menu');

function enqueue_load_fa() {
    wp_enqueue_style( 'load-fa', 'https://use.fontawesome.com/releases/v5.5.0/css/all.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_load_fa');