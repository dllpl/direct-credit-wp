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
            'args' => [
                'phone'=> [
                    'description' => __('Поле phone обязательно к заполнению в формате 79000000000.'),
                    'type' => 'string',
                    'pattern' => '^7+\d{10}$',
                    'required' => true,
                ],
                'item_name'=>[
                    'description' => __('Поле item_name обязательно к заполнению.'),
                    'type' => 'string',
                    'required' => true,
                ],
                'price' => [
                    'description' => __('Поле price обязательно к заполнению.'),
                    'type' => 'number',
                    'required' => true,
                ]
            ]
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
            'args' => [
                'location' => [
                    'description' => __('Поле location обязательно к заполнению.'),
                    'type' => 'string',
                    'minLength' => 3,
                    'required' => true,
                ],
                'wsdl' => [
                    'description' => __('Поле wsdl обязательно к заполнению.'),
                    'type' => 'string',
                    'minLength' => 3,
                    'required' => true,
                ],
                'password' => [
                    'description' => __('Поле password обязательно к заполнению.'),
                    'type' => 'string',
                    'minLength' => 3,
                    'required' => true,
                ],
                'login' => [
                    'description' => __('Поле login обязательно к заполнению.'),
                    'type' => 'string',
                    'minLength' => 3,
                    'required' => true,
                ],
                'partnerID' => [
                    'description' => __('Поле partnerID обязательно к заполнению.'),
                    'type' => 'string',
                    'minLength' => 3,
                    'required' => true,
                ],
                'codeTT' => [
                    'description' => __('Поле codeTT обязательно к заполнению.'),
                    'type' => 'string',
                    'minLength' => 3,
                    'required' => true,
                ],
                'bitrix_webhook_url' => [
                    'description' => __('Поле bitrix_webhook_url обязательно к заполнению.'),
                    'type' => 'string',
                    'minLength' => 3,
                    'default' => null
                ],
                'email' => [
                    'description' => __('Поле email обязательно к заполнению.'),
                    'type' => 'string',
                    'minLength' => 3,
                    'default' => null
                ]
            ],
        ]);
    }


    public function createOrder(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . 'OrderController.php';

        $orderController = new OrderController();
        return $orderController->createOrder($request);
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
