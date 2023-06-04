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
    private string $login;
    private string $password;
    private string $wsdl;
    private string $location;

    private $soapClient;

    const NAMESPACE = 'dc/v1';

    public function __construct()
    {
        if ($this->getOptions()) {
            $this->soapClient = $this->soapInit();
        }
    }

    public function registerRoutes()
    {
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
        require_once plugin_dir_path(__FILE__) . './order/class-order.php';
        Order::createOrder($request, $this->soapClient);

    }

    public function checkStatus(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . './order/class-order.php';
        Order::checkStatus($request, $this->soapClient);
    }

    public function updateSettings(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . 'admin/class-option-page.php';
        $optionPage = new OptionPage();
        $optionPage->updateSettings($request);

    }

    private function soapInit()
    {
        try {
            return new SoapClient($this->wsdl,
                [
                    "soap_version" => SOAP_1_1,
                    "location" => $this->location,
                    "login" => $this->login,
                    "password" => $this->password,
                    "trace" => 1
                ]);
        } catch (SoapFault $e) {
            return false;
        }
    }

    private function getOptions()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_options';

        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE id = 1"));

        if (count($rows)) {
            foreach ($rows as $row) {
                $this->login = $row->login;
                $this->password = $row->password;
                $this->wsdl = $row->wsdl;
                $this->location = $row->location;
            }
            return true;
        } else {
            return false;
        }
    }

}
