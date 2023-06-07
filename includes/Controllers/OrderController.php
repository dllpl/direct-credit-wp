<?php

require_once plugin_dir_path(__FILE__) . '../Traits/Options.php';
require_once plugin_dir_path(__FILE__) . '../Traits/Soap.php';

class OrderController
{
    use Options, Soap;

    private SoapClient $soapClient;

    public function __construct()
    {
        $options = Options::getOptions();
        if ($options && is_array($options)) {
            $soapClient = Soap::initClient($options);
            if ($soapClient) {
                $this->soapClient = $soapClient;
            } else {
                return wp_send_json_error('Ошибка при инициализации Soap клиента. Проверьте правильность данных');
            }
        } else {
            return wp_send_json_error('Не указаны, либо не были найдены настройки Soap клиента');
        }
    }

    public function createOrder($request)
    {

    }

    public function checkStatus($request)
    {

    }
}
