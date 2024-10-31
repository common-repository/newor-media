<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link https://newormedia.com
 * @since 1.0.0
 * @package Newor_Media
 *
 * @wordpress-plugin
 * Plugin Name: Newor Media
 * Plugin URI: https://newormedia.com
 * Description: Newor Media ad management
 * Version: 1.0.4
 * Author: Newor Media
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: newor-media
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'NEWOR_MEDIA_VERSION', '1.0.4' );

/**
 * Newor Media Base URL.
 */
define( 'NEWOR_MEDIA_BASE_URL', 'https://reports.newormedia.com');

/**
 * CDN URL.
 */
define( 'NEWOR_MEDIA_CDN_URL', 'https://cdn.thisiswaldo.com');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-newor-media-activator.php
 */
function activate_newor_media() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-newor-media-activator.php';
	Newor_Media_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-newor-media-deactivator.php
 */
function deactivate_newor_media() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-newor-media-deactivator.php';
	Newor_Media_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_newor_media' );
register_deactivation_hook( __FILE__, 'deactivate_newor_media' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-newor-media.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_newor_media() {

	$plugin = new Newor_Media();
	$plugin->run();

}
run_newor_media();
