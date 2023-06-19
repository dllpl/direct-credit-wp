<?php

require_once plugin_dir_path(__FILE__) . '../Traits/Options.php';
require_once plugin_dir_path(__FILE__) . '../Traits/Soap.php';

class OrderController
{
    use Options, Soap;

    private SoapClient $soapClient;
    private array $options;
    private string $table_name;

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

    /**
     * Метод создания заказа. Запускает остальные процессы.
     * @param $request
     * @return mixed
     */
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
            'order_id' => wp_generate_uuid4(),
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

        /** Отправляем в ДК */
        if ($res = $this->sendToDK($order_data)) {

            $dc_status = (int)$res->result->status;
            $dc_api_key = $res->result->apiKey;

            $this->insertOrderToBD([
                'order_id' => $order_data['order_id'],
                'dc_api_key' => $dc_api_key,
                'phone' => $order_data['phone'],
                'item_name' => $order_data['item_name'],
                'price' => $order_data['price'],
                'dc_status' => $dc_status,
                'json' => json_encode($order_data)
            ]);

            /** Если указана почта, отправляем заказ на почту */
            if (isset($this->options['email']) && !empty($this->options['email'])) {
                $this->sendToEmail($order_data, $dc_status);
            }

            /** Если указан вебхук Битрикс24, создаем лида */
            if (isset($this->options['bitrix_webhook_url']) && !empty($this->options['bitrix_webhook_url'])) {

                $lead_id = $this->bitrixCreateLead($order_data, $dc_status, $this->options['bitrix_webhook_url']);

                /** Если указан Идентификатор смарт-процесса, создаем */
                if (isset($this->options['bitrix_entity_type_id']) && !empty($this->options['bitrix_entity_type_id'])) {
                    $sp_id = $this->bitrixCreateSP($lead_id, $dc_status, (int)$this->options['bitrix_entity_type_id'], $this->options['bitrix_webhook_url'], $order_data);
                }

                $this->updateOrderToDB($order_data['order_id'], [
                    'bitrix_lead_id' => $lead_id,
                    'bitrix_sp_id' => $sp_id ?? null,
                    'updated_at' => wp_date('Y-m-d H:i:s')
                ]);
            }
            return wp_send_json_success($dc_api_key);
        } else {
            return wp_send_json_error('Ошибка при создании заявки в ДК');
        }
    }

    /**
     * Отправка в Директ Кредит и получение Api токена.
     * @param array $order_data
     * @return false|stdClass
     */
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
                    'id' => $order_data['order_id'],
                    'name' => $order_data['item_name'],
                    'price' => $order_data['price'],
                    'quantity' => 1,
                    'type' => 'Мототехника'
                ]
            ],
        ];

        try {
            return $this->soapClient->createOrder(['data' => $data]);
        } catch (SoapFault $e) {
            return false;
        }
    }

    /**
     * Создание лида в Битрикс24
     * @param array $order_data
     * @param int $dc_status
     * @param string $bitrix_webhook_url
     * @return false|mixed|void
     */
    private function bitrixCreateLead(array $order_data, int $dc_status, string $bitrix_webhook_url)
    {
        $action = '/crm.lead.add.json';

        $queryData = http_build_query([
            "fields" => [
                "TITLE" => "Кредит | " . $order_data['item_name'] . " | " . $order_data['address'],
                "NAME" => $order_data['firstName'],
                "SECOND_NAME" => $order_data['secondName'],
                "LAST_NAME" => $order_data['lastName'],
                "BIRTHDATE" => $order_data['birthdate'],
                "UF_CRM_MGO_CC_ENTRY_POINT" => $order_data['url'],
                "PHONE" => [
                    "n0" => [
                        "VALUE" => $order_data['phone'],
                        "TYPE_ID" => "PHONE",
                        "ID" => "241500",
                        "VALUE_TYPE" => "WORK"
                    ],
                ],
                "EMAIL" => [
                    "n0" => [
                        "VALUE" => $order_data['email'],
                        "TYPE_ID" => "EMAIL",
                        "ID" => "241502",
                        "VALUE_TYPE" => "WORK"
                    ]
                ],
                "UF_CRM_1676289780" => $order_data['metrikaClientId'],
                "UF_CRM_MGO_CC_TAG_ID" => "Кредит",
                "SOURCE_ID" => "UC_AITU1Z",
                "ADDRESS_CITY" => $order_data['address'],
                "UF_CRM_1683280552822" => $dc_status,
                "UF_CRM_1683536841182" => $order_data['order_id'],
                "UTM_SOURCE" => $order_data['utm']['utm_source'],
                "UTM_MEDIUM" => $order_data['utm']['utm_medium'],
                "UTM_CAMPAIGN" => $order_data['utm']['utm_campaign'],
                "UTM_TERM" => $order_data['utm']['utm_term'],
                "UTM_CONTENT" => $order_data['utm']['utm_content']
            ],
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $bitrix_webhook_url . $action,
            CURLOPT_POSTFIELDS => $queryData,
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result, 1);

        if (!array_key_exists('error', $result)) {
            return (is_array($result) && !empty($result["result"])) ? $result["result"] : false;
        }
    }

    /**
     * Создание смарт-процесса в Битрикс24
     * @param string $lead_id
     * @param int $dc_status
     * @param int $bitrix_entity_type_id
     * @param string $bitrix_webhook_url
     * @param array $order_data
     * @return false|mixed
     */
    private function bitrixCreateSP(string $lead_id, int $dc_status, int $bitrix_entity_type_id, string $bitrix_webhook_url, array $order_data)
    {
        $action = '/crm.item.add.json';

        $stage_id = $this->dc_bitrix_StageSwitcher($dc_status);

        $queryData = http_build_query([
            'entityTypeId' => $bitrix_entity_type_id,
            'fields' => [
                'OPPORTUNITY' => $order_data['price'],
                'STAGE_ID' => $stage_id,
                'PARENT_ID_1' => $lead_id,
            ]
        ]);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $bitrix_webhook_url . $action,
            CURLOPT_POSTFIELDS => $queryData,
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result, 1);

        if (!array_key_exists('error', $result)) {
            return (is_array($result) && !empty($result["result"]['item']['id'])) ? $result["result"]['item']['id'] : false;
        } else {
            return false;
        }
    }

    /**
     * Метод перемещения заявки по стадиям смарт-процесса.
     * @param int $sp_id
     * @param int $dc_status
     * @param int $bitrix_entity_type_id
     * @param string $bitrix_webhook_url
     * @return false|mixed
     */
    private function bitrixUpdateSP(int $sp_id, int $dc_status, int $bitrix_entity_type_id, string $bitrix_webhook_url)
    {
        $action = '/crm.item.update.json';

        $stage_id = $this->dc_bitrix_StageSwitcher($dc_status);

        $queryData = http_build_query([
            'entityTypeId' => $bitrix_entity_type_id,
            'id' => $sp_id,
            'fields' => ['STAGE_ID' => $stage_id]
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $bitrix_webhook_url . $action,
            CURLOPT_POSTFIELDS => $queryData,
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result, 1);

        if (!array_key_exists('error', $result)) {
            return (is_array($result) && !empty($result["result"]['item']['id'])) ? $result["result"]['item']['id'] : false;
        } else {
            return false;
        }
    }

    /**
     * Отправка на почту
     * @param array $order_data
     * @param int $dc_status
     * @return void
     */
    private function sendToEmail(array $order_data, int $dc_status)
    {
        $firstName = $order_data['firstName'];
        $secondName = $order_data['secondName'];
        $lastName = $order_data['lastName'];
        $phone = $order_data['phone'];
        $email = $order_data['email'];
        $creditResult = $dc_status;
        $order = $order_data['order'];
        $codeTT = $order_data['codeTT'];
        $name = $order_data['name'];
        $price = $order_data['price'];
        $address = $order_data['address'];
        $birthdate = $order_data['birthdate'];
        $metrikaClientId = $order_data['metrikaclientid'];
        $url = $order_data['url'];

        $body = "
		Данные клиента <br>
		ФИО: $firstName $secondName $lastName <br>
		Телефон: $phone <br>
		Почта: $email <br>
		Адрес: $address <br>
		Дата рождения: $birthdate <br>
		--------------------- <br>
		Информация по заявке <br>
		Результат: $creditResult <br>
		Номер заявки: $order <br>
		codeTT: $codeTT <br>
		url: $url <br>
		--------------------- <br>
		Информация о выборе <br>
		Название техники: $name <br>
		Стоимость: $price <br>
		-------------------- <br>
		Прочие данные <br>
		ID метрики: $metrikaClientId
	";

        $to = $this->options['email'];
        $subject = 'Заявка в кредит';
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail([$to], $subject, $body, $headers);
    }

    private function insertOrderToBD(array $data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_orders';
        $wpdb->insert($table_name, $data);
    }

    private function updateOrderToDB(string $order_id, array $data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_orders';
        $res = $wpdb->update($table_name, $data, ['order_id' => $order_id]);

//        if ($res === 0) {
//
//        }
//
//        if ($res > 0) {
//
//        }
//
//        if (!$res) {
//
//        }
    }

    private function getOrderFromDB(string $order_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_orders';
        $row = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE order_id = %s", trim($order_id)));

        if (count($row)) {
            return $row[0];
        } else {
            return false;
        }

    }

    public function checkStatus($request)
    {
        if ($order = $this->getOrderFromDB($request['order_id'])) {
            try {
                $res = $this->soapClient->checkStatus(['data' => [
                    'partnerID' => $this->options['partnerID'],
                    'order' => $request['order_id']
                ]]);

                $dc_status = (int)$res->result->status;
                $this->updateOrderToDB($request['order_id'], [
                    'dc_status' => $dc_status,
                    'updated_at' => wp_date('Y-m-d H:i:s')
                ]);

                if (isset($this->options['bitrix_entity_type_id'], $this->options['bitrix_webhook_url']) &&
                    !empty($this->options['bitrix_entity_type_id']) && !empty($this->options['bitrix_webhook_url'])
                ) {
                    $this->bitrixUpdateSP($order->bitrix_sp_id, $dc_status, $this->options['bitrix_entity_type_id'], $this->options['bitrix_webhook_url']);
                }

                return wp_send_json_success();
            } catch (SoapFault $ex) {
                return wp_send_json_error('Ошибка при вызове soap-метода checkStatus', 400);
            }
        } else {
            return wp_send_json_error('Не найден такой order_id', 404);
        }
    }

    private function dc_bitrix_StageSwitcher(int $dc_status)
    {
        switch ($dc_status) {
            case 0:
                return 'DT165_34:NEW';
            case 1:
                return 'DT165_34:PREPARATION';
            case 3:
                return 'DT165_34:CLIENT';
            case 5:
                return 'DT165_34:UC_CTIPLB';
            case 6:
                return 'DT165_34:2';
            case 7:
                return 'DT165_34:3';
            case 9:
                return 'DT165_34:4';
            case 10:
                return 'DT165_34:5';
            case 11:
                return 'DT165_34:6';
            case 12:
                return 'DT165_34:7';
            case 13:
                return 'DT165_34:SUCCESS';

            /** Отказные статусы */
            case 4:
                return 'DT165_34:FAIL';
            case 8:
                return 'DT165_34:1';
            case 14:
                return 'DT165_34:8';
            case 17:
                return 'DT165_34:9';
            case 18:
                return 'DT165_34:10';
        }
    }
}
