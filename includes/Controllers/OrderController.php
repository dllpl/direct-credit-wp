<?php

require_once plugin_dir_path(__FILE__) . '../Traits/Options.php';
require_once plugin_dir_path(__FILE__) . '../Traits/Soap.php';

class OrderController
{
    use Options, Soap;

    private SoapClient $soapClient;
    private array $options;

    public function __construct()
    {
        $options = Options::getOptions();
        if ($options && is_array($options)) {
            $soapClient = Soap::initClient($options);
            if ($soapClient) {
                $this->soapClient = $soapClient;
                $this->options = $options;
            } else {
                return wp_send_json_error('Ошибка при инициализации Soap клиента. Проверьте правильность данных');
            }
        } else {
            return wp_send_json_error('Не указаны, либо не были найдены настройки Soap клиента');
        }
    }

    public function createOrder($request)
    {

        $referrer = $_SERVER['HTTP_REFERER'];
        $parts = parse_url($referrer);
        parse_str($parts['query'], $query);
        $utm_source = $query['utm_source'] ?? null;
        $utm_medium = $query['utm_medium'] ?? null;
        $utm_campaign = $query['utm_campaign'] ?? null;
        $utm_term = $query['utm_term'] ?? null;
        $utm_content = $query['utm_content'] ?? null;

        $order_data = [
            'order_id' => md5(uniqid(rand(), true)),
            'firstName' => $request['firstName'] ?? null,
            'secondName' => $request['secondName'] ?? null,
            'lastName' => $request['lastName'] ?? null,
            'phone' => $request['phone'],
            'email' => $request['email'] ?? null,
            'item_name' => $request['item_name'],
            'price' => $request['price'],

            'address' => $request['address'] ?? null,
            'birthdate' => $request['$birthdate'] ?? null,
            'metrikaClientId' => $request['metrikaclientid'] ?? null,
            'url' => $request['url'] ?? null,
            'utm' => [
                'utm_source' => $utm_source,
                'utm_medium' => $utm_medium,
                'utm_campaign' => $utm_campaign,
                'utm_term' => $utm_term,
                'utm_content' => $utm_content
            ]
        ];

        if ($res = $this->sendToDK($order_data)) {
            $this->insertToBD($order_data, (int)$res[0]['status'], $res[0]['apiKey']);
            $this->sendToBitrix($order_data, (int)$res[0]['status'], $res[0]['apiKey']);
        }
    }

    private function sendToDK(array $order_data)
    {
        $data = [
            'partnerID' => $this->options['partnerID'],
            'order' => $order_data['order_id'],
            'codeTT' => $this->options['codeTT'],
            'reserve' => 1,
            'personal' => [
                'phone' => $order_data['phone'],
                'lastName' => $order_data['lastName'],
                'firstName' => $order_data['firstName'],
                'secondName' => $order_data['secondName'],
                'email' => $order_data['email']
            ],
            'goods' => [
                [
                    'id' => $order_data['order'],
                    'name' => $order_data['item_name'],
                    'price' => $order_data['price'],
                    'quantity' => 1,
                    'type' => 'Мототехника'
                ]
            ],
        ];

        try {
            $response = $this->soapClient->createOrder(['data' => $data]);
            var_dump($response);
        } catch (SoapFault $e) {
            return wp_send_json_error($e);
        }
    }

    private function insertToBD(array $order_data, int $dc_status, string $dc_token)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_orders';
        $data = [
            'order_id' => $order_data['order_id'],
            'dc_token' => $dc_token,
            'phone' => $order_data['phone'],
            'item_name' => $order_data['item_name'],
            'price' => $order_data['price'],
            'dc_status' => $dc_status,
            'json' => json_encode($order_data)
        ];
        $wpdb->insert($table_name, $data);
    }

    private function sendToBitrix(array $order_data, int $dc_status, string $dc_token)
    {

    }

    private function updateOrder(string $order_id, $dc_status = null)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_options';

        $res = $wpdb->update($table_name,
            [
                'dc_status' => $dc_status,
                'updated_at' => date('Y-m-d H:i:s')],
            [
                'order_id' => $order_id
            ],
            ['%d'],
            ['%s']
        );

        if ($res === 0) {

        }

        if ($res > 0) {

        }

        if (!$res) {

        }
    }

    private function getOrder(string $order_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_orders';
        $row = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE order_id = %s", trim($order_id)));

        if (count($row)) {
            return $row;
        } else {
            return false;
        }

    }

    public function checkStatus($request)
    {

    }
}
