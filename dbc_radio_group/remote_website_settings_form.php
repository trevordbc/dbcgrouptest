<?php

// Remote Website Settings Form
function dbc_station_remote_website_settings_form() {
    // Retrieve the remote settings
    $remote_settings = dbc_station_get_remote_settings($_GET['station'], $_GET['connector']);

    // If the settings are not retrieved successfully, show an error
    if (is_wp_error($remote_settings)) {
        echo 'Error: ' . $remote_settings->get_error_message();
        return;
    }

    // Pre-fill the form fields with the current settings
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('update_remote_settings'); ?>
        <input type="hidden" name="station_website" value="<?php echo esc_url($_GET['station']); ?>">
        <input type="hidden" name="connector_key" value="<?php echo esc_attr($_GET['connector']); ?>">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="site-title">Site Title</label></th>
                <td><input type="text" name="site_title" id="site-title" class="regular-text" value="<?php echo esc_attr($remote_settings['site_title']); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="site-description">Site Description</label></th>
                <td><input type="text" name="site_description" id="site-description" class="regular-text" value="<?php echo esc_attr($remote_settings['site_description']); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="site-logo">Site Logo</label></th>
                <td>
                    <input type="hidden" name="site_logo" id="site-logo" value="<?php echo esc_attr($remote_settings['site_logo']); ?>">
                    <input type="button" class="button" id="site-logo-button" value="Upload/Change Logo">
                    <img src="<?php echo esc_url($remote_settings['site_logo']); ?>" id="site-logo-preview" style="max-width: 200px; max-height: 200px;">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="site-favicon">Site Favicon</label></th>
                <td>
                    <input type="hidden" name="site_favicon" id="site-favicon" value="<?php echo esc_attr($remote_settings['site_favicon']); ?>">
                    <input type="button" class="button" id="site-favicon-button" value="Upload/Change Favicon">
                    <img src="<?php echo esc_url($remote_settings['site_favicon']); ?>" id="site-favicon-preview" style="max-width: 64px; max-height: 64px;">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="call-letters">Call Letters</label></th>
                <td><input type="text" name="call_letters" id="call-letters" class="regular-text" value="<?php echo esc_attr($remote_settings['call_letters']); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="frequency">Frequency</label></th>
                <td><input type="text" name="frequency" id="frequency" class="regular-text" value="<?php echo esc_attr($remote_settings['frequency']); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="primary-gradient-color-start">Primary Gradient Color Start</label></th>
                <td><input type="text" name="primary_gradient_color_start" id="primary-gradient-color-start" class="color-picker" value="<?php echo esc_attr($remote_settings['primary_gradient_color_start']); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="primary-gradient-color-end">Primary Gradient Color End</label></th>
                <td><input type="text" name="primary_gradient_color_end" id="primary-gradient-color-end" class="color-picker" value="<?php echo esc_attr($remote_settings['primary_gradient_color_end']); ?>"></td>
            </tr>
        </table>
        <?php submit_button('Update Remote Website Settings'); ?>
    </form>
    <?php
}