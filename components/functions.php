<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Encryption key generation
function shift8_remote_generate_api_key() {
    $encryption_key = bin2hex(openssl_random_pseudo_bytes(32));
    return $encryption_key;
}

// Callback for key regeneration
function shift8_remote_ajax_process_request() {
    if (wp_verify_nonce($_GET['_wpnonce'], 'shift8-remote-process') && $_GET['action'] == 'shift8_remote_response') {
        $new_encryption_key = shift8_remote_generate_api_key();
        echo $new_encryption_key;
        die();
    } else {
        die();
    }
}
add_action('wp_ajax_shift8_remote_response', 'shift8_remote_ajax_process_request');

/**
 * Get the API calls and load the API
 *
 * @return null
 */
function shift8_remote_catch_api_call() {
    $sanitized_input = shift8_remote_sanitize($_POST);
    if ( empty( $sanitized_input['shift8_remote_verify_key'] ) )
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

    // Version.php is included so wp_db_version takes the version of the updated copy of WP
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
    // Sanitize this particular POST if supplied
    $sanitized_filesystem_details = shift8_remote_sanitize($_POST['filesystem_details']);
    
    if ( empty( $sanitized_filesystem_details ) )
        return $credentials;

    $_credentials = array(
        'username' => $sanitized_filesystem_details['credentials']['username'],
        'password' => $sanitized_filesystem_details['credentials']['password'],
        'hostname' => $sanitized_filesystem_details['credentials']['hostname'],
        'connection_type' => $sanitized_filesystem_details['method']
    );

    // check whether the credentials can be used
    if ( ! WP_Filesystem( $_credentials ) ) {
        return $credentials;
    }

    return $_credentials;
}
add_filter( 'request_filesystem_credentials', '_shift8_remote_set_filesystem_credentials' );


/**
 * Get the likely web host for this site.
 */
function _shift8_remote_integration_get_web_host() {

    // WP Engine
    if ( defined( 'WPE_APIKEY' ) && WPE_APIKEY )
        return 'wpengine';

    return 'unknown';
}

/**
 * Return an array of content summary information
 *
 * @return array
 */
function _shift8_remote_get_content_summary() {

    $num_posts           = wp_count_posts( 'post' );
    $num_pages           = wp_count_posts( 'page' );
    $num_categories      = count( get_categories( array( 'hide_empty' => 0 ) ) );
    $num_comments        = wp_count_comments();
    $num_themes          = count( wp_get_themes() );
    $num_plugins         = count( get_plugins() );
    $num_users           = count_users();

    $content_summary     = array(
        'post_count'          => ( ! empty( $num_posts->publish ) ) ? $num_posts->publish : 0,
        'page_count'          => ( ! empty( $num_pages->publish ) ) ? $num_pages->publish : 0,
        'category_count'      => $num_categories,
        'comment_count'       => ( ! empty( $num_comments->total_comments ) ) ? $num_comments->total_comments: 0,
        'theme_count'         => $num_themes,
        'plugin_count'        => $num_plugins,
        'user_count'          => ( ! empty( $num_users['total_users'] ) ) ? $num_users['total_users'] : 0
    );

    return $content_summary;
}

/**
 * Return an array of sanitized input (which should be an array as well)
 *
 * @return array
 */
function shift8_remote_sanitize( $arr ){
    $result = array();
    foreach ($arr as $key => $val){
        $result[$key] = (is_array($val) ? shift8_remote_sanitize($val) : sanitize_text_field($val));
    }
    return $result;
}