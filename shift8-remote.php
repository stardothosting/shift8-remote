<?php
/**
 * Plugin Name: Shift8 Remote Update
 * Plugin URI: https://github.com/stardothosting/shift8-remote
 * Description: Plugin that allows you to trigger a plugin or core Wordpress update via an API call
 * Version: 1.00
 * Author: Shift8 Web 
 * Author URI: https://www.shift8web.ca
 * License: GPLv3
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SHIFT8_REMOTE_PLUGIN_SLUG', 'shift8-remote' );
define( 'SHIFT8_REMOTE_PLUGIN_BASE',  plugin_basename(__FILE__) );
define( 'SHIFT8_REMOTE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Don't activate on anything less than PHP 5.2.4
if ( version_compare( phpversion(), '5.2.4', '<' ) ) {

    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    deactivate_plugins( SHIFT8_REMOTE_PLUGIN_SLUG . '/shift8-remote.php' );
    add_action( 'admin_notices', function() {
    	?>
    <div class="error notice">
        <p><?php _e( 'Shift8 Remote requires PHP >= 5.2.4 to function correctly. Deactivating plugin.', 'shift8_remote' ); ?></p>
    </div>
    <?php
	});

}

require_once(plugin_dir_path(__FILE__).'components/admin.php' );
require_once(plugin_dir_path(__FILE__).'components/enqueuing.php' );
require_once(plugin_dir_path(__FILE__).'components/settings.php' );
require_once(plugin_dir_path(__FILE__).'components/functions.php' );


