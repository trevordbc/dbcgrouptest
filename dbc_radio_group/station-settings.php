<?php
// Include the Stripe PHP library
require_once(plugin_dir_path(__FILE__) . '/libraries/stripe-gateway/init.php'); // Adjust the path according to your project structure

// Set the Stripe API key from the saved settings
\Stripe\Stripe::setApiKey(get_option('stripe_api_key'));

// Update Roles Based On Subscription Status
function update_users_roles_based_on_stripe_status() {
    $args = array(
        'meta_key' => 'paygate_transaction_id',
        'meta_compare' => 'EXISTS',
    );

    $users = new WP_User_Query($args);

    if (!empty($users->results)) {
        foreach ($users->results as $user) {
            $transaction_id = get_user_meta($user->ID, 'paygate_transaction_id', true);
			$customer_id = get_stripe_customer_id($transaction_id);
            if ($customer_id) {
                update_user_meta($user->ID, 'stripe_customer_id', $customer_id);
            }
            $subscription_status = get_subscription_status($transaction_id);

            // Update user role based on subscription status
            if ($subscription_status === 'past_due') {
                $user->set_role('subscription_past_due');
            } elseif ($subscription_status === 'canceled') {
                $user->set_role('subscription_canceled');
            }
        }
    }
}

//Retrieve all custom post types.
function get_custom_post_types() {
    $args = array(
        'public'   => true,
        '_builtin' => false
    );
    return get_post_types($args, 'objects');
}

function station_settings_page() {
	// Retrieve the current WordPress site title, logo and favicon
	$current_site_title = get_bloginfo('name');
	$current_logo_id = get_theme_mod( 'custom_logo' );
	$current_logo = wp_get_attachment_image_url( $current_logo_id, 'full' );
	$current_favicon_id = get_option( 'site_icon' );
	$current_favicon = wp_get_attachment_image_url( $current_favicon_id, 'full' );
	
	// Retrieve the current settings from the database
	$current_title = get_option( 'dbc_station_website_title' );
	$current_description = get_option( 'dbc_station_website_description' );
	$current_call_letters = get_option( 'dbc_station_call_letters' );
	$current_frequency = get_option( 'dbc_station_frequency' );
	
	// Output the HTML for the settings page
	wp_enqueue_media();
	?>
	<div class="wrap">
		<h1>Station Settings</h1>
		<?php
			wp_enqueue_media();
			wp_enqueue_script('station-settings-js', plugin_dir_url(__FILE__) . 'station-settings/station-settings-page.js', array('jquery'), false, true);
			wp_enqueue_style('station-settings-css', plugin_dir_url(__FILE__) . 'station-settings/station-settings-page.css');
			include plugin_dir_path(__FILE__) . 'station-settings/station-settings-form.php';
		?>
		
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="dbc_station_clear_cache">
			<?php wp_nonce_field( 'dbc_station_clear_cache_nonce', 'dbc_station_clear_cache_nonce_field' ); ?>
			<p><input type="submit" name="submit" value="Clear Cache" class="button button-primary"></p>
		</form>
	</div>
	<?php
}

function update_wordpress_station_settings() {
    // Update WordPress title and description
    $website_title = get_option( 'dbc_station_website_title' );
    $website_description = get_option( 'dbc_station_website_description' );

    if ( $website_title ) {
        update_option( 'blogname', $website_title );
    }

    if ( $website_description ) {
        update_option( 'blogdescription', $website_description );
    }

    // Update WordPress logo
    $website_logo_id = get_option( 'dbc_station_website_logo_id' );

    if ( $website_logo_id ) {
        set_theme_mod( 'custom_logo', $website_logo_id );
    }

    // Update WordPress favicon
    $website_favicon_id = get_option( 'dbc_station_website_favicon_id' );

    if ( $website_favicon_id ) {
        update_option( 'site_icon', $website_favicon_id );
    }
}
add_action( 'init', 'update_wordpress_station_settings' );

function register_dbc_station_settings() {
    register_setting( 'dbc_station_settings_group', 'dbc_station_website_title' );
    register_setting( 'dbc_station_settings_group', 'dbc_station_website_description' );
    register_setting( 'dbc_station_settings_group', 'dbc_station_website_logo' );
    register_setting( 'dbc_station_settings_group', 'dbc_station_website_logo_id' ); // Add this line
    register_setting( 'dbc_station_settings_group', 'dbc_station_website_favicon' );
    register_setting( 'dbc_station_settings_group', 'dbc_station_website_favicon_id' ); // Add this line
    register_setting( 'dbc_station_settings_group', 'dbc_station_call_letters' );
    register_setting( 'dbc_station_settings_group', 'dbc_station_frequency' );
}
add_action( 'admin_init', 'register_dbc_station_settings' );

function get_subscription_status($transaction_id) {
    try {
        $subscription = \Stripe\Subscription::retrieve($transaction_id);
        $status = $subscription->status;
        return $status;
    } catch (Exception $e) {
        return '';
    }
}

function get_stripe_customer_id($transaction_id) {
    try {
        $charge = \Stripe\Charge::retrieve($transaction_id);
        $customer_id = $charge->customer;
        return $customer_id;
    } catch (Exception $e) {
        return '';
    }
}

function paygate_page() {
    // Get the current page number
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    // Get the selected number of entries per page
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 25;

    // Update the $args in WP_User_Query based on the selected user role type
    $args = array(
        'meta_key' => 'paygate_transaction_id',
        'meta_compare' => 'EXISTS',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'number' => $per_page,
        'paged' => $paged,
    );

    $users = new WP_User_Query($args);

    // Pagination variables
    $total_users = $users->get_total();
    $num_pages = ceil($total_users / $per_page);
	
	if (isset($_POST['manual_update'])) {
        update_users_roles_based_on_stripe_status();
    }
	
	// Display manual update button
    echo '<div class="manual-update">';
    echo '<form method="post" action="">';
    echo '<input type="submit" name="manual_update" class="button" value="Manually Update User Roles">';
    echo '</form>';
    echo '</div>';

    // Display the table with user information
    echo '<table class="widefat">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Email</th>';
    echo '<th>Subscription Status</th>';
	echo '<th>Customer ID</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($users->results as $user) {
        // Get the user email
        $email = $user->user_email;

        // Get the transaction ID from the user meta
        $transaction_id = $user->paygate_transaction_id;
		
		//Get Customer ID
		$customer_id = get_user_meta($user->ID, 'stripe_customer_id', true);

        // Fetch the subscription status using the transaction ID
        $subscription_status = get_subscription_status($transaction_id);

        echo '<tr>';
        echo '<td>' . esc_html($email) . '</td>';
        echo '<td>' . esc_html($subscription_status) . '</td>';
		echo '<td>' . esc_html($customer_id) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Display pagination
    if ($num_pages > 1) {
        echo '<div class="tablenav">';
        echo '<div class="tablenav-pages">';
        echo paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => $num_pages,
            'current' => $paged,
        ));
        echo '</div>';
    }

    // Display the "Load more" button
    if ($paged < $num_pages) {
        echo '<div class="load-more">';
        echo '<a href="' . add_query_arg('paged', $paged + 1) . '" class="button">Load more</a>';
        echo '</div>';
    }

    echo '</div>'; // Close the .wrap div
}

// Associate User With Stripe Payment ID
function associate_transaction_id_to_user($entry, $form)
{
    // Check if it's the form you want to process (use saved Gravity Form ID)
    if ($form['id'] != get_option('gravity_form_id')) {
        return;
    }

    $email = rgar($entry, get_option('email_field_id')); // Use saved email field ID
    $transaction_id = rgar($entry, 'transaction_id');

    // Get the user by email
    $user = get_user_by('email', $email);

    if ($user instanceof WP_User) {
        $existing_transaction_id = get_user_meta($user->ID, 'paygate_transaction_id', true);

        if (empty($existing_transaction_id)) {
            update_user_meta($user->ID, 'paygate_transaction_id', $transaction_id);
        }
    }
}

add_action('gform_after_submission', 'associate_transaction_id_to_user', 10, 2);

function paygate_settings_page() {
    // Save settings
    if (isset($_POST['submit_paygate_settings'])) {
        update_option('stripe_api_key', sanitize_text_field($_POST['stripe_api_key']));
        update_option('gravity_form_id', intval($_POST['gravity_form_id']));
        update_option('email_field_id', intval($_POST['email_field_id']));
    }

    // Display settings form
    ?>
    <div class="wrap">
        <h2>Paygate Settings</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Stripe API Key</th>
                    <td>
                        <input type="text" name="stripe_api_key" value="<?php echo esc_attr(get_option('stripe_api_key')); ?>"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Gravity Form ID</th>
                    <td>
                        <input type="number" name="gravity_form_id" value="<?php echo intval(get_option('gravity_form_id')); ?>"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Email Field ID</th>
                    <td>
                        <input type="number" name="email_field_id" value="<?php echo intval(get_option('email_field_id')); ?>"/>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit_paygate_settings" class="button-primary" value="<?php _e('Save Changes') ?>"/>
            </p>
        </form>
    </div>
    <?php
}
function manage_billing_shortcode() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $user_roles = $user->roles;
        $allowed_roles = array('administrator', 'p1', 'market_manager');

        if (array_intersect($user_roles, $allowed_roles)) {
            $customer_id = get_user_meta($user->ID, 'stripe_customer_id', true);

            if ($customer_id) {
                $billing_link = generate_billing_update_link($customer_id);
                return '<a href="' . esc_url($billing_link) . '">Manage Billing</a>';
            }
        }
    } else {
		echo '<a href="/up1-login">Login</a> | <a href="/join-the-ultimate-p1-club">Register</a>';
	}

    return '';
}
function custom_login_shortcode() {
    if (is_user_logged_in()) {
        return 'You are already logged in.';
    }

    ob_start();
    ?>
    <div class="custom-login-form">
        <?php
        wp_login_form(
            array(
                'redirect' => site_url(),
                'form_id' => 'custom-login-form',
                'label_username' => __('Username', 'text-domain'),
                'label_password' => __('Password', 'text-domain'),
                'label_remember' => __('Remember Me', 'text-domain'),
                'label_log_in' => __('Log In', 'text-domain'),
                'remember' => true,
            )
        );
        ?>

        <div class="custom-login-links">
            <a href="<?php echo wp_lostpassword_url(); ?>">
                <?php _e('Forgot Your Password?', 'text-domain'); ?>
            </a>
        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('custom_login', 'custom_login_shortcode');
add_shortcode('manage_billing', 'manage_billing_shortcode');
function generate_billing_update_link($customer_id) {
    $stripe_account_id = 'acct_1DTFMFGmc1YwVKim'; // Replace with your Stripe account ID
    $return_url = 'https://2guysnamedchris.com/account'; // Replace with your desired return URL

    $link_params = array(
        'customer' => $customer_id,
        'return_url' => $return_url,
    );

    $url = "https://billing.stripe.com/session/update_billing?".http_build_query($link_params);

    return $url;
}

function station_settings_user_roles_callback() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $roles = wp_roles();
    ?>
    <div class="wrap">
        <h1>User Roles</h1>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th scope="col">Role</th>
                    <th scope="col">Capabilities</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles->role_objects as $key => $role): ?>
                    <tr>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=station_settings_edit_user_role&role_slug=' . esc_attr($key)); ?>"><?php echo esc_html($key); ?></a>
                            <?php if (!in_array($key, array('administrator', 'editor', 'author', 'contributor', 'subscriber'))): ?>
                                | <a href="<?php echo esc_url(admin_url('admin.php?page=delete_user_role&role_slug=' . urlencode($key))); ?>"><?php esc_html_e('Delete', 'textdomain'); ?></a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo implode(', ', array_keys($role->capabilities)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
function station_settings_delete_user_role_callback() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to perform this action.');
    }

    if (!isset($_GET['role_slug'])) {
        wp_die('Invalid role.');
    }

    $role_slug = sanitize_text_field($_GET['role_slug']);
    $roles = wp_roles();
    $role = $roles->get_role($role_slug);

    if (!$role) {
        wp_die('Invalid role.');
    }

    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        check_admin_referer('station_settings_delete_user_role_nonce');

        $new_role_slug = isset($_POST['new_role_slug']) ? sanitize_text_field($_POST['new_role_slug']) : '';
        $new_role = $roles->get_role($new_role_slug);

        if (!$new_role) {
            wp_die('Invalid new role.');
        }

        $users = get_users(array('role' => $role_slug));
        foreach ($users as $user) {
            $user->set_role($new_role_slug);
        }

        remove_role($role_slug);

        wp_redirect(add_query_arg(array('page' => 'station_settings_user_roles', 'status' => 'deleted'), admin_url('admin.php')));
        exit;
    }

    ?>
				<div class="wrap">
                    <h1><?php esc_html_e('Delete User Role', 'textdomain'); ?></h1>
                    <form method="post" action="">
                        <?php wp_nonce_field('station_settings_delete_user_role_nonce'); ?>
                        <p>
                            <?php esc_html_e('You are about to delete the following role:', 'textdomain'); ?><br>
                            <strong><?php echo esc_html($role->name); ?></strong>
                        </p>
                        <p>
                            <?php esc_html_e('Please select a new role for users who currently have this role:', 'textdomain'); ?><br>
                            <select name="new_role_slug">
                                <?php foreach ($roles->role_objects as $key => $role_object): ?>
                                    <?php if ($key != $role_slug): ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($role_object->name); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <input type="hidden" name="confirm_delete" value="yes">
                        <?php submit_button(__('Delete User Role', 'textdomain'), 'delete'); ?>
                    </form>
                </div>
	<?php
}
function get_admin_menu_items() {
		global $menu;

		$dashboard_pages = array();
		foreach ($menu as $item) {
			// $item[0] is the menu title, $item[2] is the menu slug
			$dashboard_pages[$item[2]] = $item[0];
		}

		$custom_post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects', 'and');
		foreach ($custom_post_types as $post_type) {
			$dashboard_pages['edit.php?post_type=' . $post_type->name] = $post_type->labels->name;
		}

		return $dashboard_pages;
	}

	function station_settings_edit_user_role_callback() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!isset($_GET['role_slug'])) {
        wp_die('Role slug is required.');
    }

    $role_slug = sanitize_text_field($_GET['role_slug']);
    $roles = wp_roles();
    $role = $roles->get_role($role_slug);

    if (!$role) {
        wp_die('Invalid role.');
    }

    $dashboard_pages = array(
        'read' => 'Dashboard',
        'edit_posts' => 'Posts',
        'upload_files' => 'Media',
        'edit_pages' => 'Pages',
        'edit_comments' => 'Comments',
        'manage_categories' => 'Categories',
        'manage_links' => 'Links',
        'edit_theme_options' => 'Appearance',
        'install_plugins' => 'Plugins',
        'manage_options' => 'Settings',
        'list_users' => 'Users',
    );

    $custom_post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects', 'and');
    if (is_array($custom_post_types) || is_object($custom_post_types)) {
        foreach ($custom_post_types as $post_type) {
            $dashboard_pages['edit.php?post_type=' . $post_type->name] = $post_type->labels->name;
        }
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Edit User Role: ', 'textdomain') . esc_html($role_slug); ?></h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="station_settings_update_user_role">
            <input type="hidden" name="role_slug" value="<?php echo esc_attr($role_slug); ?>">
            <?php wp_nonce_field('station_settings_update_user_role_nonce', 'station_settings_update_user_role_nonce_field'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="access_dashboard"><?php esc_html_e('Access Dashboard', 'textdomain'); ?></label></th>
                    <td>
                        <input type="checkbox" id="access_dashboard" name="access_dashboard" value="1" <?php checked($role->has_cap('read')); ?>>
                    </td>
                </tr>
                <?php foreach ($dashboard_pages as $capability => $title): ?>
                    <tr>
                        <th scope="row"><label for="permissions_<?php echo esc_attr($capability); ?>"><?php echo esc_html($title); ?></label></th>
                        <td>
                            <input type="checkbox" id="permissions_<?php echo esc_attr($capability); ?>" name="permissions[]" value="<?php echo esc_attr($capability); ?>" <?php checked($role->has_cap($capability)); ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php submit_button(__('Update User Role', 'textdomain')); ?>
        </form>
    </div>
    <?php
}
function station_settings_update_user_role() {
    if (!isset($_POST['station_settings_update_user_role_nonce_field']) || !wp_verify_nonce($_POST['station_settings_update_user_role_nonce_field'], 'station_settings_update_user_role_nonce')) {
        wp_die('Invalid nonce.');
    }

    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to perform this action.');
    }

    $role_slug = sanitize_text_field($_POST['role_slug']);
    $access_dashboard = isset($_POST['access_dashboard']) ? (bool)$_POST['access_dashboard'] : false;
    $permissions = isset($_POST['permissions']) ? array_map('sanitize_text_field', $_POST['permissions']) : array();

    $roles = wp_roles();
    $role = $roles->get_role($role_slug);

    if (!$role) {
        wp_die('Invalid role.');
    }

    if ($access_dashboard) {
        $role->add_cap('read');
    } else {
        $role->remove_cap('read');
    }

    $dashboard_pages = array(
        'read' => 'Dashboard',
        'edit_posts' => 'Posts',
        'upload_files' => 'Media',
        'edit_pages' => 'Pages',
        'edit_comments' => 'Comments',
        'manage_categories' => 'Categories',
        'manage_links' => 'Links',
        'edit_theme_options' => 'Appearance',
        'install_plugins' => 'Plugins',
        'manage_options' => 'Settings',
        'list_users' => 'Users',
    );

    $custom_post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects', 'and');
    if (is_array($custom_post_types) || is_object($custom_post_types)) {
        foreach ($custom_post_types as $post_type) {
            $dashboard_pages['edit.php?post_type=' . $post_type->name] = $post_type->labels->name;
        }
    }

    foreach ($dashboard_pages as $capability => $title) {
        if (in_array($capability, $permissions)) {
            $role->add_cap($capability);
        } else {
            $role->remove_cap($capability);
        }
    }

    // Redirect back to the "Edit User Role" page with a success message
    wp_redirect(add_query_arg(array('page' => 'station_settings_edit_user_role', 'role_slug' => $role_slug, 'status' => 'success'), admin_url('admin.php')));
    exit;
}
add_action('admin_post_station_settings_update_user_role', 'station_settings_update_user_role');

function station_settings_add_user_role_submenu() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Add User Role', 'textdomain'); ?></h1>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('User role has been created successfully!', 'textdomain'); ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="station_settings_create_user_role">
            <?php wp_nonce_field('station_settings_create_user_role_nonce', 'station_settings_create_user_role_nonce_field'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="role_name"><?php esc_html_e('Role Name', 'textdomain'); ?></label></th>
                    <td><input type="text" id="role_name" name="role_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="role_slug"><?php esc_html_e('Role Slug', 'textdomain'); ?></label></th>
                    <td><input type="text" id="role_slug" name="role_slug" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="access_dashboard"><?php esc_html_e('Access Dashboard', 'textdomain'); ?></label></th>
                    <td>
                        <input type="checkbox" id="access_dashboard" name="access_dashboard" value="1">
                        <label for="access_dashboard"><?php esc_html_e('Allow access to the dashboard', 'textdomain'); ?></label>
                    </td>
                </tr>
                <tr class="dashboard-permissions" style="display: none;">
                    <th scope="row"><?php esc_html_e('Dashboard Permissions', 'textdomain'); ?></th>
                    <td>
                        <?php
                        global $menu;
                        foreach ($menu as $menu_item) {
                            if (!empty($menu_item[0])) {
                                echo '<label><input type="checkbox" name="permissions[]" value="' . esc_attr($menu_item[2]) . '"> ' . esc_html($menu_item[0]) . '</label><br>';
                            }
                        }
                        ?>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Create User Role', 'textdomain')); ?>
        </form>
    </div>
    <?php
}

function station_settings_create_user_role() {
    if (!isset($_POST['station_settings_create_user_role_nonce_field']) || !wp_verify_nonce($_POST['station_settings_create_user_role_nonce_field'], 'station_settings_create_user_role_nonce')) {
        wp_die('Invalid nonce.');
    }

    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to perform this action.');
    }

    $role_name = sanitize_text_field($_POST['role_name']);
    $role_slug = sanitize_text_field($_POST['role_slug']);
    $access_dashboard = isset($_POST['access_dashboard']) ? (bool)$_POST['access_dashboard'] : false;
    $permissions = isset($_POST['permissions']) ? array_map('sanitize_text_field', $_POST['permissions']) : array();

    if (empty($role_name) || empty($role_slug)) {
        wp_die('Role name and role slug are required.');
    }

    $capabilities = array();
    if ($access_dashboard) {
        $capabilities['read'] = true;
        foreach ($permissions as $permission) {
            $capabilities[$permission] = true;
        }
    }

    add_role($role_slug, $role_name, $capabilities);

    // Redirect back to the "Add User Role" page with a success message
    wp_redirect(add_query_arg(array('page' => 'station_settings_add_user_role', 'status' => 'success'), admin_url('admin.php')));
    exit;
}
add_action('admin_post_station_settings_create_user_role', 'station_settings_create_user_role');

function custom_user_roles_menu() {
    add_menu_page('Station Settings', 'Station Settings', 'manage_options', 'station-settings', 'station_settings_page');
    add_submenu_page('station-settings', 'Paygate', 'Paygate', 'manage_options', 'paygate', 'paygate_page');
    add_submenu_page('station-settings', 'Paygate Settings', 'Paygate Settings', 'manage_options', 'paygate-settings', 'paygate_settings_page');
    add_submenu_page('station-settings', 'User Roles', 'User Roles', 'manage_options', 'station_settings_user_roles', 'station_settings_user_roles_callback');
    add_submenu_page('station-settings', 'Add User Role', 'Add User Role', 'manage_options', 'station_settings_add_user_role', 'station_settings_add_user_role_submenu' );
	add_submenu_page(null, 'Edit User Role', 'Edit User Role', 'manage_options', 'station_settings_edit_user_role', 'station_settings_edit_user_role_callback');
	add_submenu_page(null, 'Delete User Role', 'Delete User Role', 'manage_options', 'delete_user_role', 'station_settings_delete_user_role_callback');
}
add_action('admin_menu', 'custom_user_roles_menu');