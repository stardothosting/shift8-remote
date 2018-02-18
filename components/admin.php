<?php

// Admin welcome page
if (!function_exists('shift8_main_page')) {
	function shift8_main_page() {
	?>
	<div class="wrap">
	<h2>Shift8 Plugins</h2>
	Shift8 is a Toronto based web development and design company. We specialize in Wordpress development and love to contribute back to the Wordpress community whenever we can! You can see more about us by visiting <a href="https://www.shift8web.ca" target="_new">our website</a>.
	</div>
	<?php
	}
}

// Admin settings page
function shift8_remote_settings_page() {
?>
<div class="wrap">
<h2>Shift8 Remote Settings</h2>
<?php if (is_admin()) { ?>
<form method="post" action="options.php">
    <?php settings_fields( 'shift8-remote-settings-group' ); ?>
    <?php do_settings_sections( 'shift8-remote-settings-group' ); ?>
    <?php
	$locations = get_theme_mod( 'nav_menu_locations' );
	if (!empty($locations)) {
		foreach ($locations as $locationId => $menuValue) {
			if (has_nav_menu($locationId)) {
				$shift8_remote_menu = $locationId;
			}
		}
	}

	// Set API key if empty
    if (empty(get_option('shift8_remote_api_key') )) {
        $encryption_key = shift8_remote_generate_api_key();
    } else {
        $encryption_key = esc_attr( get_option('shift8_remote_api_key') );
    }

	?>
    <table class="form-table shift8-remote-table">
	<tr valign="top">
    <td><span id="shift8-remote-notice">
    <?php 
    settings_errors('shift8_remote_api_key'); 
    ?>
    </span></td>
	</tr>
    <tr valign="top">
    <td><input size="64" id="shift8-remote-api-key" name="shift8_remote_api_key" value="<?php echo $encryption_key; ?>" type="hidden"/>
    Shift8 Remote API Key : <span id="shift8-api-key-display"><?php echo $encryption_key; ?></span></td>
    <td><button id="shift8-remote-api-button">Re-generate Encryption Key</button></td>
	</tr>
	</table>
    <?php submit_button(); ?>
	</form>
</div>

<?php 
	} // is_admin
}