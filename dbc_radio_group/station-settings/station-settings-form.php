<form method="post" action="options.php" enctype="multipart/form-data">
    <?php settings_fields( 'dbc_station_settings_group' ); ?>
    <?php do_settings_sections( 'dbc_station_settings_page' ); ?>
    <div class="station-settings-container">
        <div class="station-settings-section">
			<h2>Website Settings</h2>

			<table class="form-table">
				<tr>
					<th scope="row"><label for="website-title">Website Title</label></th>
					<td><input type="text" name="dbc_station_website_title" id="website-title" value="<?php echo esc_attr( $current_site_title ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="website-description">Website Description</label></th>
					<td><textarea name="dbc_station_website_description" id="website-description" rows="5" cols="50"><?php echo esc_html( get_bloginfo('description') ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row" style="width: 50%;"><label for="website-logo">Website Logo</label></th>
					<th scope="row" style="width: 50%;"><label for="favicon-icon">Favicon Icon</label></th>
				</tr>
				<tr>
					<td style="width: 50%;">
						<?php if ( $current_logo ) : ?>
							<img id="logo-preview" src="<?php echo esc_url( $current_logo ); ?>" style="max-width: 100%; height: auto;" alt="Current logo"><br>
						<?php endif; ?>
						<input type="hidden" name="dbc_station_website_logo" id="logo-url" value="<?php echo esc_attr( $current_logo ); ?>">
						<input type="hidden" name="dbc_station_website_logo_id" id="logo-id" value="<?php echo esc_attr( $current_logo_id ); ?>">
						<button type="button" class="button button-secondary" id="upload-logo">Replace Logo</button>
					</td>
					<td style="width: 50%;">
						<?php if ( $current_favicon ) : ?>
							<img id="favicon-preview" src="<?php echo esc_url( $current_favicon ); ?>" style="max-width: 85px; height: auto;" alt="Current favicon"><br>
						<?php endif; ?>
						<input type="hidden" name="dbc_station_website_favicon" id="favicon-url" value="<?php echo esc_attr( $current_favicon ); ?>">
						<input type="hidden" name="dbc_station_website_favicon_id" id="favicon-id" value="<?php echo esc_attr( $current_favicon_id ); ?>">
						<button type="button" class="button button-secondary" id="upload-favicon">Replace Favicon</button>
						<input type="file" name="dbc_station_website_favicon_upload" id="favicon-upload" accept="image/*" style="display:none;">
					</td>
				</tr>
			</table>
		</div>
		<div class="station-settings-section">
			<h2>Station Settings</h2>

			<table class="form-table">
				<tr>
					<th scope="row"><label for="call-letters">Call Letters</label></th>
					<td><input type="text" name="dbc_station_call_letters" id="call-letters" value="<?php echo esc_attr( $current_call_letters ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="frequency">Frequency</label></th>
					<td><input type="text" name="dbc_station_frequency" id="frequency" value="<?php echo esc_attr( $current_frequency ); ?>" class="regular-text"></td>
				</tr>
			</table>
		</div>
		<div class="station-settings-section">
			<?php
				$custom_post_types = get_custom_post_types();
				if (!empty($custom_post_types)) {
			?>
					<h2>Custom Post Types</h2>
					<table class="form-table">
						<tbody>
							<?php foreach ($custom_post_types as $post_type) { ?>
								<?php
									$is_enabled = get_option('enabled_post_type_' . $post_type->name, 0);
									$checked = $is_enabled ? 'checked' : '';
								?>
								<tr>
									<th scope="row">
										<label for="enabled_post_type_<?php echo esc_attr($post_type->name); ?>"><?php echo esc_html($post_type->labels->name); ?></label>
									</th>
									<td>
										<input type="checkbox" name="enabled_post_type_<?php echo esc_attr($post_type->name); ?>" id="enabled_post_type_<?php echo esc_attr($post_type->name); ?>" value="1" <?php echo $checked; ?> />
										<span class="description">Enable/Disable <?php echo esc_html($post_type->labels->name); ?></span>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
			<?php } ?>
		</div>
		<div style="clear: both;"></div>
	</div>
			
	<?php submit_button(); ?>
</form>