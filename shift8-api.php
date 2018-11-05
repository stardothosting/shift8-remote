<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SHIFT8_REMOTE_API_Request {

	static $actions = array();
	static $args = array();

	static function verify_request() {

		// Sanitize all POST input to ensure variables are escaped. Uses the built-in Wordpress function sanitize_text_field
		$sanitized_input = shift8_remote_sanitize($_POST);

		// Check the API Key
		if ( ! shift8_remote_get_api_keys() ) {

			echo json_encode( 'blank-api-key' );
			exit;

		} elseif ( isset( $sanitized_input['shift8_remote_verify_key'] ) ) {

			$verify = $sanitized_input['shift8_remote_verify_key'];
			$api_key = shift8_remote_get_api_keys();

			// Verify API key matches what is set in Wordpress options
			if ( $verify != $api_key ) {
				echo json_encode( 'bad-verify-key' );
				exit;
			}

			// This ensures that stale requests dont accidentally get executed after the fact.
			if ( (int) $sanitized_input['timestamp'] > time() + 360 || (int) $sanitized_input['timestamp'] < time() - 360 ) {
				echo json_encode( 'bad-timstamp' );
				exit;
			}

			// Iterate over actions and subsequent arguments
			self::$actions = $sanitized_input['actions'];
			self::$args = $sanitized_input;


		} else {
			exit;
		}

		return true;

	}

	static function get_actions() {
		return self::$actions;
	}

	static function get_args() {
		return self::$args;
	}

	static function get_arg( $arg ) {
		return ( isset( self::$args[$arg] ) ) ? self::$args[$arg] : null;
	}
}

SHIFT8_REMOTE_API_Request::verify_request();

// Disable error_reporting if debug mode is enabled so that JSON response isn't broken
if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG )
	error_reporting( 0 );

// Log in as admin
$users_query = new WP_User_Query( array(
    'role' => 'administrator',
    'orderby' => 'ID'
) );
wp_set_current_user(1);

if ($users_query->get_total()) {
    foreach ($users_query->get_results() as $user) {
        if (!$user) {
            continue;
        }
        $currentUser = wp_set_current_user($user->ID);
        break;
    }
    if (empty($currentUser)) {
        wp_set_current_user(1);
    }
}

include_once ( ABSPATH . 'wp-admin/includes/admin.php' );

$actions = array();

// Actual iteration over actions
foreach( SHIFT8_REMOTE_API_Request::get_actions() as $action ) {

	switch( $action ) {

		case 'get_plugin_version' :

			$actions[$action] = '1.0';

		break;

        case 'is_active' :

            $actions[$action] = 'yes';

        break;

		case 'get_filesystem_method' :
			$actions[$action] = get_filesystem_method();

		break;

		case 'get_supported_filesystem_methods' :

			$actions[$action] = array();

			if ( extension_loaded( 'ftp' ) || extension_loaded( 'sockets' ) || function_exists( 'fsockopen' ) )
				$actions[$action][] = 'ftp';

			if ( extension_loaded( 'ftp' ) )
				$actions[$action][] = 'ftps';

			if ( extension_loaded( 'ssh2' ) && function_exists( 'stream_get_contents' ) )
				$actions[$action][] = 'ssh';

		break;

		case 'get_wp_version' :

			global $wp_version;

			$actions[$action] = (string) $wp_version;

		break;

		case 'get_constants':

			$constants = array();
			if ( is_array( SHIFT8_REMOTE_API_Request::get_arg( 'constants' ) ) ) {

				foreach( SHIFT8_REMOTE_API_Request::get_arg( 'constants' ) as $constant ) {
					if ( defined( $constant ) )
						$constants[$constant] = constant( $constant );
					else
						$constants[$constant] = null;
				}

			}
			$actions[$action] = $constants;

		break;

		case 'upgrade_core' :

			$actions[$action] = _shift8_remote_upgrade_core();

		break;

		case 'get_plugins' :

			$actions[$action] = _shift8_remote_get_plugins();

		break;

		case 'update_plugin' :
		case 'upgrade_plugin' :

			$api_args = array(
					'zip_url'      => esc_url_raw( SHIFT8_REMOTE_API_Request::get_arg( 'zip_url' ) ),
				);
			$actions[$action] = _shift8_remote_update_plugin( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'plugin' ) ), $api_args );

		break;

        case 'validate_plugin' :
            $actions[$action] = _shift8_remote_validate( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'plugin' ) ) );
        break;

		case 'install_plugin' :

			$api_args = array(
					'version'      => sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'version' ) ),
				);
			$actions[$action] = _shift8_remote_install_plugin( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'plugin' ) ), $api_args );

		break;

		case 'activate_plugin' :

			$actions[$action] = _shift8_remote_activate_plugin( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'plugin' ) ) );

		break;

		case 'deactivate_plugin' :

			$actions[$action] = _shift8_remote_deactivate_plugin( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'plugin' ) ) );

		break;

		case 'uninstall_plugin' :

			$actions[$action] = _shift8_remote_uninstall_plugin( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'plugin' ) ) );

		break;

		case 'get_themes' :

			$actions[$action] = _shift8_remote_get_themes();

		break;

		case 'install_theme':

			$api_args = array(
					'version'      => sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'version' ) ),
				);
			$actions[$action] = _shift8_remote_install_theme( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'theme' ) ), $api_args );

		break;

		case 'activate_theme':

			$actions[$action] = _shift8_remote_activate_theme( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'theme' ) ) );

		break;

		case 'update_theme' :
		case 'upgrade_theme' : // 'upgrade' is deprecated

			$actions[$action] = _shift8_remote_update_theme( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'theme' ) ) );

		break;

		case 'delete_theme':

			$actions[$action] = _shift8_remote_delete_theme( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'theme' ) ) );

		break;

		case 'get_site_info' :

			$actions[$action]  = array(
				'site_url'	   => get_site_url(),
				'home_url'	   => get_home_url(),
				'admin_url'	   => get_admin_url(),
				'web_host'     => _shift8_remote_integration_get_web_host(),
				'summary'      => _shift8_remote_get_content_summary(),
			);

		break;

		case 'get_option':

			$actions[$action] = get_option( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'option_name' ) ) );

			break;

		case 'update_option':

			$actions[$action] = update_option( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'option_name' ) ), SHIFT8_REMOTE_API_Request::get_arg( 'option_value' ) );

		break;

		case 'delete_option':

			$actions[$action] = delete_option( sanitize_text_field( SHIFT8_REMOTE_API_Request::get_arg( 'option_name' ) ) );

		break;

		default :

			$actions[$action] = 'not-implemented';

		break;

	}

}

foreach ( $actions as $key => $action ) {

	if ( is_wp_error( $action ) ) {

		$actions[$key] = (object) array(
			'errors' => $action->errors
		);
	}
}

echo json_encode( $actions );

exit;
