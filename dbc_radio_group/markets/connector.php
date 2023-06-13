<?php
// Check user capabilities
if (!current_user_can('manage_options')) {
    return;
}

// Generate a secure key for this station if not already set
$this_station_connector_key = get_option('dbc_station_connector_key');
if (!$this_station_connector_key) {
    $this_station_connector_key = dbc_station_generate_connector_key();
    update_option('dbc_station_connector_key', $this_station_connector_key);
}

// Process the form to add a new station if submitted
if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'add_station')) {
    $station_title = sanitize_text_field($_POST['station_title']);
    $station_website = esc_url_raw($_POST['station_website']);
    $station_connector = sanitize_text_field($_POST['station_connector']);

    if ($station_title && $station_website && $station_connector) {
        $other_stations = get_option('dbc_other_stations', array());
        $other_stations[] = array(
            'title' => $station_title,
            'website' => $station_website,
            'connector' => $station_connector,
        );
        update_option('dbc_other_stations', $other_stations);
    }
}

// Process the form to remove a station if submitted
if (isset($_POST['remove']) && wp_verify_nonce($_POST['_wpnonce'], 'remove_station')) {
    $station_website = esc_url_raw($_POST['station_website']);
    $other_stations = get_option('dbc_other_stations', array());
    $other_stations = array_filter($other_stations, function($station) use ($station_website) {
        return $station['website'] !== $station_website;
    });
    update_option('dbc_other_stations', $other_stations);
}

// Display the page content
?>
<div class="wrap">
    <h1>This Station's Connector</h1>
    <p><strong>Connector Key:</strong> <?php echo esc_html($this_station_connector_key); ?></p>
    <hr>
    <h2>Add A Station Website</h2>
    <form method="post" action="">
        <?php wp_nonce_field('add_station'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="station-title">Station Title</label></th>
                <td><input type="text" name="station_title" id="station-title" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="station-website">Station Website</label></th>
                <td><input type="text" name="station_website" id="station-website" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="station-connector">Station Connector</label></th>
                <td><input type="text" name="station_connector" id="station-connector" class="regular-text"></td>
            </tr>
        </table>
        <?php submit_button('Add Station'); ?>
    </form>
    <?php
    $other_stations = get_option('dbc_other_stations', array());
    if ($other_stations) {
        echo '<h3>Stations List</h3>';
        echo'<ul>';
        foreach ($other_stations as $station) {
            echo '<li>';
            echo '<a href="' . admin_url('admin.php?page=remote-website-settings&station=' . urlencode($station['website'])) . '">' . esc_html($station['title']) . '</a>';
            echo '<form method="post" action="" style="display:inline;">';
            wp_nonce_field('remove_station');
            echo '<input type="hidden" name="station_website" value="' . esc_attr($station['website']) . '">';
            echo '<input type="submit" name="remove" value="Remove" class="button-link-delete">';
            echo '</form>';
            echo '</li>';
        }
        echo '</ul>';
    }
    ?>
</div>