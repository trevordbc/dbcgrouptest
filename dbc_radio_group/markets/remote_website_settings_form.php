<?php
// Check user capabilities
if (!current_user_can('manage_options')) {
    return;
}

// Get the selected station's website URL
$selected_station_website = isset($_GET['station']) ? urldecode($_GET['station']) : '';

?>
<div class="wrap">
    <h1>Remote Website Settings</h1>
    <?php if ($selected_station_website): ?>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="station-title">Station Title</label></th>
                    <td><input type="text" name="station_title" id="station-title" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="station-website">Station Website</label></th>
                    <td><input type="text" name="station_website" id="station-website" class="regular-text"></td>
                </tr>
                <!-- Add other fields here -->
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
    <?php else: ?>
        <p>Please select a station from the <a href="<?php echo admin_url('admin.php?page=connector'); ?>">Connector</a> page.</p>
    <?php endif; ?>
</div>
<script>
    jQuery(document).ready(function ($) {
        var selected_station_website = '<?php echo esc_js($selected_station_website); ?>';

        if (selected_station_website) {
            var rest_api_url = selected_station_website + '/wp-json/wp/v2/settings';

            $.ajax({
                url: rest_api_url,
                method: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                },
                success: function (response) {
                    $('#station-title').val(response.title);
                    $('#station-website').val(response.url);
                },
                error: function () {
                    alert('Error fetching data from remote website. Please check the URL and make sure the REST API is enabled.');
                }
            });
        }
    });
</script>