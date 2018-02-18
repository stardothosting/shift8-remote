<?php

/**
 * Return an array of installed plugins
 *
 * @return array
 */
function _shift8_remote_get_plugins() {

    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

    // Get all plugins
    $plugins = get_plugins();

    // Get the list of active plugins
    $active  = get_option( 'active_plugins', array() );

    // Delete the transient so wp_update_plugins can get fresh data
    if ( function_exists( 'get_site_transient' ) )
        delete_site_transient( 'update_plugins' );

    else
        delete_transient( 'update_plugins' );

    // Force a plugin update check
    wp_update_plugins();

    // Different versions of wp store the updates in different places
    // TODO can we depreciate
    if( function_exists( 'get_site_transient' ) && $transient = get_site_transient( 'update_plugins' ) )
        $current = $transient;

    elseif( $transient = get_transient( 'update_plugins' ) )
        $current = $transient;

    else
        $current = get_option( 'update_plugins' );

    // Premium plugins that have adopted the ManageWP API report new plugins by this filter
    $manage_wp_updates = apply_filters( 'mwp_premium_update_notification', array() );

    foreach ( (array) $plugins as $plugin_file => $plugin ) {

        if ( is_plugin_active( $plugin_file ) )
            $plugins[$plugin_file]['active'] = true;
        else
            $plugins[$plugin_file]['active'] = false;

        $manage_wp_plugin_update = false;
        foreach( $manage_wp_updates as $manage_wp_update ) {

            if ( ! empty( $manage_wp_update['Name'] ) && $plugin['Name'] == $manage_wp_update['Name'] )
                $manage_wp_plugin_update = $manage_wp_update;

        }
        if ( $manage_wp_plugin_update ) {

            $plugins[$plugin_file]['latest_version'] = $manage_wp_plugin_update['new_version'];

        } else if ( isset( $current->response[$plugin_file] ) ) {

            $plugins[$plugin_file]['latest_version'] = $current->response[$plugin_file]->new_version;
            $plugins[$plugin_file]['latest_package'] = $current->response[$plugin_file]->package;
            $plugins[$plugin_file]['slug'] = $current->response[$plugin_file]->slug;

        } else {

            $plugins[$plugin_file]['latest_version'] = $plugin['Version'];

        }

    }

    return $plugins;
}

/**
 * Update a plugin
 *
 * @access private
 * @param $plugin_file
 * @param $args
 * @return array|WP_Error
 */
function _shift8_remote_update_plugin( $plugin_file, $args ) {
    global $shift8_remote_zip_update;

    if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS )
        return new WP_Error( 'disallow-file-mods', __( "File modification is disabled with the DISALLOW_FILE_MODS constant.", 'shift8-remote' ) );

    include_once ( ABSPATH . 'wp-admin/includes/admin.php' );
    require_once ( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
    require_once WPRP_PLUGIN_PATH . 'inc/class-shift8-remote-plugin-upgrader-skin.php';

    // check for filesystem access
    if ( ! _shift8_remote_check_filesystem_access() )
        return new WP_Error( 'filesystem-not-writable', __( 'The filesystem is not writable with the supplied credentials', 'shift8-remote' ) );

    $is_active         = is_plugin_active( $plugin_file );
    $is_active_network = is_plugin_active_for_network( $plugin_file );

    foreach( get_plugins() as $path => $maybe_plugin ) {

        if ( $path == $plugin_file ) {
            $plugin = $maybe_plugin;
            break;
        }

    }

    // Permit specifying a zip URL to update the plugin with
    if ( ! empty( $args['zip_url'] ) ) {

        $zip_url = $args['zip_url'];

    } else {

        // Check to see if this is a premium plugin that supports the ManageWP implementation
        $manage_wp_updates = apply_filters( 'mwp_premium_perform_update', array() );
        $manage_wp_plugin_update = false;
        foreach( $manage_wp_updates as $manage_wp_update ) {

            if ( ! empty( $manage_wp_update['Name'] )
                && $plugin['Name'] == $manage_wp_update['Name']
                && ! empty( $manage_wp_update['url'] ) ) {
                $zip_url = $manage_wp_update['url'];
                break;
            }

        }
    }

    $skin = new SHIFT8_REMOTE_Plugin_Upgrader_Skin();
    $upgrader = new Plugin_Upgrader( $skin );

    wp_update_plugins();

    // Do the upgrade
    ob_start();
    $result = $upgrader->upgrade( $plugin_file );
    $data = ob_get_contents();
    ob_clean();

    if ( isset($manage_wp_plugin_update) && $manage_wp_plugin_update )
        remove_filter( 'pre_site_transient_update_plugins', '_shift8_remote_forcably_filter_update_plugins' );

    // If the plugin was activited, we have to re-activate it
    // but if activate_plugin() fatals, then we'll just have to return 500
    if ( $is_active )
        activate_plugin( $plugin_file, '', $is_active_network, true );

    if ( ! empty( $skin->error ) ) {
        if (is_wp_error($skin->error)) {
            return $skin->error;
        }
        if ($skin->error == 'up_to_date') {
            return new WP_Error('up_to_date', __('Plugin already up to date.', 'shift8-remote'));
        }
        $msg = __('Unknown error updating plugin.', 'shift8-remote');
        if (is_string($skin->error)) {
            $msg = $skin->error;
        }
        return new WP_Error('plugin-upgrader-skin', $msg);
    } else if ( is_wp_error( $result ) ) {
        return $result;
    } else if ( ( ! $result && ! is_null( $result ) ) || $data ) {
        return new WP_Error('plugin-update', __('Unknown error updating plugin.', 'shift8-remote'));
    }

    $active_status = array(
        'was_active'            => $is_active,
        'was_active_network'    => $is_active_network,
        'is_active'             =>  is_plugin_active( $plugin_file ),
        'is_active_network'     =>  is_plugin_active_for_network( $plugin_file ),
    );
    return array( 'status' => 'success', 'active_status' => $active_status );
}

/**
 * Validate Plugin Update
 *
 * @param $plugin_file
 * @return array|WP_Error
 */
function _shift8_remote_validate($plugin_file)
{
    $plugin_status = false;
    foreach( get_plugins() as $path => $maybe_plugin ) {
        if ( $path == $plugin_file ) {
            $plugin_status = true;
            break;
        }
    }
    if (!$plugin_status) {
        return new WP_Error('plugin-missing', __('Plugin has gone missing.', 'shift8-remote'));
    }
    return array(
        'status' => 'success',
        'plugin_status' => is_plugin_active( $plugin_file )
    );
}

/**
 * Filter `update_plugins` to produce a response it will understand
 * so we can have the Upgrader skin handle the update
 */
function _shift8_remote_forcably_filter_update_plugins() {
	global $shift8_remote_zip_update;

	$current = new stdClass;
	$current->response = array();

	$plugin_file = $shift8_remote_zip_update['plugin_file'];
	$current->response[$plugin_file] = new stdClass;
	$current->response[$plugin_file]->package = $shift8_remote_zip_update['package'];

	return $current;
}

/**
 * Install a plugin on this site
 */
function _shift8_remote_install_plugin( $plugin, $args = array() ) {

	if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS )
		return new WP_Error( 'disallow-file-mods', __( "File modification is disabled with the DISALLOW_FILE_MODS constant.", 'shift8-remote' ) );

	include_once ABSPATH . 'wp-admin/includes/admin.php';
	include_once ABSPATH . 'wp-admin/includes/upgrade.php';
	include_once ABSPATH . 'wp-includes/update.php';
	require_once ( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
	require_once WPRP_PLUGIN_PATH . 'inc/class-shift8-remote-plugin-upgrader-skin.php';

	// Access the plugins_api() helper function
	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	$api_args = array(
		'slug' => $plugin,
		'fields' => array( 'sections' => false )
		);
	$api = plugins_api( 'plugin_information', $api_args );

	if ( is_wp_error( $api ) )
		return $api;

	$skin = new SHIFT8_REMOTE_Plugin_Upgrader_Skin();
	$upgrader = new Plugin_Upgrader( $skin );

	// The best way to get a download link for a specific version :(
	// Fortunately, we can depend on a relatively consistent naming pattern
	if ( ! empty( $args['version'] ) && 'stable' != $args['version'] )
		$api->download_link = str_replace( $api->version . '.zip', $args['version'] . '.zip', $api->download_link );

	$result = $upgrader->install( $api->download_link );
	if ( is_wp_error( $result ) )
		return $result;
	else if ( ! $result )
		return new WP_Error( 'plugin-install', __( 'Unknown error installing plugin.', 'shift8-remote' ) );

	return array( 'status' => 'success' );
}

function _shift8_remote_activate_plugin( $plugin ) {

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	$result = activate_plugin( $plugin );

	if ( is_wp_error( $result ) )
		return $result;

	return array( 'status' => 'success' );
}

/**
 * Deactivate a plugin on this site.
 */
function _shift8_remote_deactivate_plugin( $plugin ) {

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( is_plugin_active( $plugin ) )
		deactivate_plugins( $plugin );

	return array( 'status' => 'success' );
}

/**
 * Uninstall a plugin on this site.
 */
function _shift8_remote_uninstall_plugin( $plugin ) {
	global $wp_filesystem;

	if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS )
		return new WP_Error( 'disallow-file-mods', __( "File modification is disabled with the DISALLOW_FILE_MODS constant.", 'shift8-remote' ) );

	include_once ABSPATH . 'wp-admin/includes/admin.php';
	include_once ABSPATH . 'wp-admin/includes/upgrade.php';
	include_once ABSPATH . 'wp-includes/update.php';

	if ( ! _wpr_check_filesystem_access() || ! WP_Filesystem() )
		return new WP_Error( 'filesystem-not-writable', __( 'The filesystem is not writable with the supplied credentials', 'shift8-remote' ) );

	$plugins_dir = $wp_filesystem->wp_plugins_dir();
	if ( empty( $plugins_dir ) )
		return new WP_Error( 'missing-plugin-dir', __( 'Unable to locate WordPress Plugin directory.' , 'shift8-remote' ) );

	$plugins_dir = trailingslashit( $plugins_dir );

	if ( is_uninstallable_plugin( $plugin ) )
		uninstall_plugin( $plugin );

	$this_plugin_dir = trailingslashit( dirname( $plugins_dir . $plugin ) );
	// If plugin is in its own directory, recursively delete the directory.
	if ( strpos( $plugin, '/' ) && $this_plugin_dir != $plugins_dir ) //base check on if plugin includes directory separator AND that it's not the root plugin folder
		$deleted = $wp_filesystem->delete( $this_plugin_dir, true );
	else
		$deleted = $wp_filesystem->delete( $plugins_dir . $plugin );

	if ( $deleted ) {
		if ( $current = get_site_transient('update_plugins') ) {
			unset( $current->response[$plugin] );
			set_site_transient('update_plugins', $current);
		}
		return array( 'status' => 'success' );
	} else {
		return new WP_Error( 'plugin-uninstall', __( 'Plugin uninstalled, but not deleted.', 'shift8-remote' ) );
	}

}