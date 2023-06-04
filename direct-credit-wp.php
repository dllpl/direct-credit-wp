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
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

add_action('rest_api_init', 'register_routes');

register_activation_hook(__FILE__, 'activate_direct_credit_wp');
register_uninstall_hook(__FILE__, 'uninstall_direct_credit_wp');

/** Активация плагина */
function activate_direct_credit_wp()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-direct-credit-wp-activator.php';
    Direct_Credit_WP_Activator::activate();
}

/** Регистрация REST API методов плагина */
function register_routes()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-direct-credit-wp.php';
    $controller = new MainRestController();
    $controller->registerRoutes();

}

/** Удаление плагина */
function uninstall_direct_credit_wp()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-direct-credit-wp-uninstall.php';
    Direct_Credit_WP_Uninstall::uninstall();
}
