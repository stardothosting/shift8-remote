<?php

// Encryption key generation
function shift8_remote_generate_api_key() {
    $cstrong = false;
    $encryption_key = bin2hex(openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'), $cstrong));
    // Fallback if no openssl
    if (!$cstrong) {
        $encryption_key = bin2hex(random_bytes(32));
    }
    return $encryption_key;
}

// Callback for key regeneration
function shift8_remote_ajax_process_request() {
    // first check if data is being sent and that it is the data we want
    $check_nonce = check_ajax_referer( 'shift8_remote_response_nonce', 'nonce');
    if ( $check_nonce == true) {
        $new_encryption_key = shift8_remote_generate_api_key();
        echo $new_encryption_key;
        die();
    } else {
        wp_send_json_error( array( 'error' => $custom_error ) );
    }
}
add_action('wp_ajax_shift8_remote_response', 'shift8_remote_ajax_process_request');

/**
 * Get the API calls and load the API
 *
 * @return null
 */
function shift8_remote_catch_api_call() {
    if ( empty( $_POST['shift8_remote_verify_key'] ) )
        return;

    require_once( SHIFT8_REMOTE_PLUGIN_PATH . '/components/plugins.php' );
    require_once( SHIFT8_REMOTE_PLUGIN_PATH . '/shift8-api.php' );
    exit;
}
add_action( 'init', 'shift8_remote_catch_api_call', 100 );

/**
 * Get the stored Shift8 Remote API key
 *
 * @return mixed
 */
function shift8_remote_get_api_keys() {
    $key = apply_filters( 'shift8_remote_api_keys', get_option( 'shift8_remote_api_key' ) );
    if ( ! empty( $key ) )
        return $key;
    else
        return null;
}

function _shift8_remote_upgrade_core()  {

    if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS )
        return new WP_Error( 'disallow-file-mods', __( "File modification is disabled with the DISALLOW_FILE_MODS constant.", 'shift8-remote' ) );

    include_once ( ABSPATH . 'wp-admin/includes/admin.php' );
    include_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
    include_once ( ABSPATH . 'wp-includes/update.php' );
    require_once ( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
    require_once SHIFT8_REMOTE_PLUGIN_PATH . 'inc/class-shift8-remote-core-upgrader-skin.php';

    // check for filesystem access
    if ( ! _shift8_remote_check_filesystem_access() )
        return new WP_Error( 'filesystem-not-writable', __( 'The filesystem is not writable with the supplied credentials', 'shift8-remote' ) );

    // force refresh
    wp_version_check();

    $updates = get_core_updates();

    if ( is_wp_error( $updates ) || ! $updates )
        return new WP_Error( 'no-update-available' );

    $update = reset( $updates );

    if ( ! $update )
        return new WP_Error( 'no-update-available' );

    $skin = new SHIFT8_REMOTE_Core_Upgrader_Skin();

    $upgrader = new Core_Upgrader( $skin );
    $result = $upgrader->upgrade($update);

    if ( is_wp_error( $result ) )
        return $result;

    global $wp_current_db_version, $wp_db_version;

    // we have to include version.php so $wp_db_version
    // will take the version of the updated version of wordpress
    require( ABSPATH . WPINC . '/version.php' );

    wp_upgrade();

    return true;
}

function _shift8_remote_check_filesystem_access() {

    ob_start();
    $success = request_filesystem_credentials( '' );
    ob_end_clean();

    return (bool) $success;
}

function _shift8_remote_set_filesystem_credentials( $credentials ) {

    if ( empty( $_POST['filesystem_details'] ) )
        return $credentials;

    $_credentials = array(
        'username' => $_POST['filesystem_details']['credentials']['username'],
        'password' => $_POST['filesystem_details']['credentials']['password'],
        'hostname' => $_POST['filesystem_details']['credentials']['hostname'],
        'connection_type' => $_POST['filesystem_details']['method']
    );

    // check whether the credentials can be used
    if ( ! WP_Filesystem( $_credentials ) ) {
        return $credentials;
    }

    return $_credentials;
}
add_filter( 'request_filesystem_credentials', '_shift8_remote_set_filesystem_credentials' );