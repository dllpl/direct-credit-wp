<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    Direct_Credit_WP
 * @subpackage Direct_Credit_WP/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Direct_Credit_WP
 * @subpackage Direct_Credit_WP/includes
 * @author     Nikita Ivanov (Nick Iv)
 */

class MainRestController extends WP_REST_Controller
{
    const NAMESPACE = 'dc/v1';

    public function registerRoutes()
    {
        /** Методы для внешних пользователей */
        register_rest_route(self::NAMESPACE, 'createOrder', [
            'methods' => 'POST',
            'callback' => [$this, 'createOrder'],
            'permission_callback' => '__return_true',
        ]);
        register_rest_route(self::NAMESPACE, 'checkStatus', [
            'args' => [
                'order_id' => [
                    'description' => __('Поле order_id обязательно к заполнению.'),
                    'type' => 'string',
                    'required' => true,
                ],
            ],
            [
                'methods' => 'GET',
                'callback' => [$this, 'checkStatus'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'checkStatus'],
                'permission_callback' => '__return_true',
            ],
        ]);

        /** Методы админки */
        register_rest_route(self::NAMESPACE, 'updateSettings', [
            'methods' => 'POST',
            'callback' => [$this, 'updateSettings'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);
    }


    public function createOrder(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . 'OrderController.php';

        $orderController = new OrderController();
        $orderController->createOrder($request);
        return OrderController::createOrder($request);
    }

    public function checkStatus(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . 'OrderController.php';

        $orderController = new OrderController();
        return $orderController->checkStatus($request);
    }

    public function updateSettings(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . '../../admin/OptionController.php';

        $optionPage = new OptionPage();
        return $optionPage->updateSettings($request);
    }
}
