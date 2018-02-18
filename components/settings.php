<?php

// create custom plugin settings menu
add_action('admin_menu', 'shift8_remote_create_menu');
function shift8_remote_create_menu() {
        //create new top-level menu
        if ( empty ( $GLOBALS['admin_page_hooks']['shift8-settings'] ) ) {
                add_menu_page('Shift8 Settings', 'Shift8', 'administrator', 'shift8-settings', 'shift8_main_page' , 'dashicons-building' );
        }
        add_submenu_page('shift8-settings', 'Remote Settings', 'Remote Settings', 'manage_options', __FILE__.'/custom', 'shift8_remote_settings_page');
        //call register settings function
        add_action( 'admin_init', 'register_shift8_remote_settings' );
}

// Register admin settings
function register_shift8_remote_settings() {
    //register our settings
    register_setting( 'shift8-remote-settings-group', 'shift8_remote_api_key', 'shift8_remote_api_key_validate' );
}

function shift8_remote_api_key_validate($data){
	if(filter_var($data, FILTER_SANITIZE_STRING)) {
   		return $data;
   	} else {
   		add_settings_error(
            'shift8_remote_api',
            'shift8-remote-notice',
            'You did not enter a valid string for the API field',
            'error');
   	}
}

// Validate admin options
function shift8_remote_check_options() {
    // If enabled is not set
    if(empty(esc_attr(get_option('shift8_remote_api_key') ))) return false;
    return true;

}

/**
 * Delete the API key on activate and deactivate
 *
 * @return null
 */
function delete_shift8_remote_options() {
    delete_option( 'shift8_remote_api_key' );
}
// Plugin uninstall hook
register_uninstall_hook(SHIFT8_REMOTE_PLUGIN_BASE, 'delete_shift8_remote_options');
