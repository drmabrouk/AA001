<?php
/**
 * Plugin Name:       Champions Sports Academy - Daily Operations & Reporting Management System
 * Plugin URI:        https://championssportsacademy.com
 * Description:       Daily operational reports, financial summaries, staff/athlete attendance, activity logging, PDF report generation, and WhatsApp sharing system.
 * Version:           1.0.0
 * Author:            Champions Sports Academy
 * Author URI:        https://championssportsacademy.com
 * Text Domain:       champions-daily-operations
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'CSA_VERSION', '1.0.0' );
define( 'CSA_PLUGIN_FILE', __FILE__ );
define( 'CSA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CSA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CSA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Require composer autoloader if present
if ( file_exists( CSA_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once CSA_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * The code that runs during plugin activation.
 */
function activate_champions_daily_operations() {
	require_once CSA_PLUGIN_DIR . 'includes/class-activator.php';
	Champions_Daily_Operations_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_champions_daily_operations() {
	require_once CSA_PLUGIN_DIR . 'includes/class-deactivator.php';
	Champions_Daily_Operations_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_champions_daily_operations' );
register_deactivation_hook( __FILE__, 'deactivate_champions_daily_operations' );

/**
 * Core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once CSA_PLUGIN_DIR . 'includes/class-champions-daily-operations.php';

/**
 * Begins execution of the plugin.
 */
function run_champions_daily_operations() {
	$plugin = new Champions_Daily_Operations();
	$plugin->run();
}
run_champions_daily_operations();
