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
        $firstName = $request['firstName'] ?? null;
        $secondName = $request['secondName'] ?? null;
        $lastName = $request['lastName'] ?? null;
        $phone = $request['phone'];
        $email = $request['email'] ?? '';
        $name = $request['name'];
        $price = $request['price'];

        $address = $request['address'] ?? null;
        $birthdate = $request['birthdate'] ?? null;
        $metrikaClientId = $request['metrikaclientid'] ?? null;
        $url = $request['url'] ?? null;
        $order = md5(uniqid(rand(), true));

        $referrer = $_SERVER['HTTP_REFERER'];
        $parts = parse_url($referrer);
        parse_str($parts['query'], $query);
        $utm_source = $query['utm_source'] ?? '';
        $utm_medium = $query['utm_medium'] ?? '';
        $utm_campaign = $query['utm_campaign'] ?? '';
        $utm_term = $query['utm_term'] ?? '';
        $utm_content = $query['utm_content'] ?? '';

        $data = [
            'partnerID' => $this->options['partnerID'],
            'order' => $order,
            'codeTT' => $this->options['codeTT'],
            'reserve' => 1,
            'personal' => [
                'phone' => $phone,
                'lastName' => $lastName,
                'firstName' => $firstName,
                'secondName' => $secondName,
                'email' => $email
            ],
            'goods' => [
                [
                    'id' => $order,
                    'name' => $name,
                    'price' => $price,
                    'quantity' => 1,
                    'type' => 'Мототехника'
                ]
            ],
        ];

        try {
            $response = $this->soapClient->createOrder(['data' => $data]);
            var_dump($response);
        }
        catch (SoapFault $e) {
            return wp_send_json_error($e);
        }


    }

    public function checkStatus($request)
    {

    }
}
