# Shift8 Remote Management
* Contributors: shift8
* Donate link: https://www.shift8web.ca
* Tags: api, management, wordpress, wordpress automation, manage wordpress, staging, wordpress deploy, wordpress build, build, deployment, deploy, manage multiple, multiple wordpress, wordpress api, api managements
* Requires at least: 3.0.1
* Tested up to: 4.9
* Stable tag: 1.00
* License: GPLv3
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

A wordpress plugin that implements an API framework for you to control and manage one or many Wordpress sites from a central location. The intention is to offer the ability to create your own web interface to interact with all of your Wordpress sites, allowing you to update core, update plugins, install plugins, deactivate plugins and many more actions. A web interface is the intended centralized platform however management systems like Ansible, Salt and Puppet can be used to interact with your Wordpress sites.

This plugin opens up your entire wordpress site to be accessed as an external API service. The idea behind this plugin is for you to access multiple wordpress sites to manage and execute actions such as updating plugins and Wordpress core as an API service. Anyone that has the API key will be able to execute these actions on your Wordpress site. 

A python script is available in the bin folder of this plugin to provide a simple example of how one could interact with this plugin in order to perform actions such as updating plugins or WP Core remotely with API calls, authenticated with the generated KEY of course.

Future versions of this plugins may interact with a web service hosted and managed by Shift8, however currently anyone can interact with this plugin using the example python script as a starting point.

## API Action Commands 

Find an overview of the API commands that can be executed through this plugin. You can execute these commands by creating your own web application to interface with this plugin, or create a quick python script (like the example script provided here in the bin folder).

# get_plugin_version
This will provide the current version of the plugin

# get_filesystem_method
This will provide the currently defined filesystem method used for modifying files in the Wordpress installation

# get_supported_filesystem_methods
This will provide a list of supported filesystem methods currently available for the Wordpress installation

# get_wp_version
This will return the currently running Wordpress version

# get_constants
This will return any defined constants

# upgrade_core
This will initiate a Wordpress core upgrade to the latest stable version

# get_plugins
This will return a list of currently installed Wordpress plugins

# update_plugin / upgrade_plugin
This will initiate a plugin update with the plugin to update specified as an additional argument

# validate_plugin / install_plugin / activate_plugin / deactivate_plugin / uninstall_plugin
This will validate / install / activate / deactivate or uninstall a plugin with the additional argument specified being the specific plugin to act on.

# get_themes
This will retun the currently installed themes

# install_theme / activate_theme / update_theme / upgrade_theme / delete_theme
This will install / activate / update / upgrade or delete a specific theme with the additional argument given specifying which theme.

# get_site_info
This will return the site url, home url, admin url, currently detected web host and site summary.

# get_option
This will return the value of a specific administrative option with the additional argument given specifying which option value to return

# update_option / delete_option
This will update or delete a specific value for an administrative option with the additional argument given specifying which option value to update

## Want to see the plugin in action?

You can view three example sites where this plugin is live :

- Example Site 1 : [Wordpress Hosting](https://www.stackstar.com "Wordpress Hosting")
- Example Site 2 : [Web Design in Toronto](https://www.shift8web.ca "Web Design in Toronto")
- Example Site 3 : [Dope Mail](https://dopemail.com "Buy Weed Online")

## Features

- Settings area to allow you to define the API key that authenticates and passes commands to the api
- Ability to list all plugins
- Ability to set wordpress options
- Ability to update all plugins
- Ability to update wordpress core
- Ability to install/deinstall/activate/deactivate plugins

## Installation 

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/shift8-remote` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the plugin settings page and define your settings

## Frequently Asked Questions 

### I tested it on myself and its not working for me! 

You should monitor the request log to see if it is able to hit the site. Also monitor the server logs of your Wordpress site to identify if any problems (i.e. curl) are happening.

## Screenshots 

1. Admin area

## Changelog 

### 1.00
* Stable version created
