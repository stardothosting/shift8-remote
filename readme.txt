=== Shift8 Remote Management ===
* Contributors: shift8
* Donate link: https://www.shift8web.ca
* Tags: api, management, wordpress, wordpress automation, manage wordpress, staging, wordpress deploy, wordpress build, build, deployment, deploy, manage multiple, multiple wordpress, wordpress api, api managements
* Requires at least: 3.0.1
* Tested up to: 4.9
* Stable tag: 1.03
* License: GPLv3
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

A wordpress plugin that implements an API framework for you to control and manage one or many Wordpress sites from a central location. The intention is to offer the ability to create your own web interface to interact with all of your Wordpress sites, allowing you to update core, update plugins, install plugins, deactivate plugins and many more actions. A web interface is the intended centralized platform however management systems like Ansible, Salt and Puppet can be used to interact with your Wordpress sites.

== Want to see the plugin in action? ==

You can view three example sites where this plugin is live :

- Example Site 1 : [Wordpress Hosting](https://www.stackstar.com "Wordpress Hosting")
- Example Site 2 : [Web Design in Toronto](https://www.shift8web.ca "Web Design in Toronto")
- Example Site 3 : [Dope Mail](https://dopemail.com "Buy Weed Online")

== Features ==

- Settings area to allow you to define the Jenkins push URL including the authentication key

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/shift8-remote` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the plugin settings page and define your settings

== Frequently Asked Questions ==

= I tested it on myself and its not working for me! =

You should monitor the Jenkins log to see if it is able to hit the site. Also monitor the server logs of your Wordpress site to identify if any problems (i.e. curl) are happening.

== Screenshots ==

1. Admin area

== Changelog ==

= 1.00 =
* Stable version created
