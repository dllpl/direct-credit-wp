<?php

/**
 * @since             1.0.0
 * @package           Direct_Credit_WP
 *
 * @wordpress-plugin
 * Plugin Name:       Direct Credit WP
 * Plugin URI:        https://github.com/dllpl/direct-credit-wp
 * Description:       Плагин интеграции формы кредитования от Директ Кредит для вашего сайта на WP
 * Version:           1.0.0
 * Author:            Nikita Ivanov (Nick Iv)
 * Author URI:        https://github.com/dllpl
 * License:           BSD 3-Clause License
 * License URI:       https://github.com/dllpl/direct-credit-wp/blob/main/LICENSE
 * Text Domain:       direct-credit-wp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Update it as you release new versions.
 */
define( 'DIRECT_CREDIT_WP_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-direct-credit-wp-activator.php
 */
function activate_direct_credit_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-direct-credit-wp-activator.php';
	Direct_Credit_WP_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-direct-credit-wp-deactivator.php
 */
function deactivate_direct_credit_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-direct-credit-wp-deactivator.php';
	Direct_Credit_WP_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_direct_credit_wp' );
register_deactivation_hook( __FILE__, 'deactivate_direct_credit_wp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-direct-credit-wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_direct_credit_wp() {

	$plugin = new Direct_Credit_WP();
	$plugin->run();

}
run_direct_credit_wp();
