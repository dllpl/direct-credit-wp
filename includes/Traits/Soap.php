<?php
trait Soap
{
    /**
     * @param array $options
     * @return false|\SoapClient
     */
    public static function initClient(array $options)
    {
        if (isset($options['wsdl'], $options['location'], $options['login'], $options['password'])) {
            try {
                return new \SoapClient($options['wsdl'],
                    [
                        "soap_version" => SOAP_1_1,
                        "location" => $options['location'],
                        "login" => $options['login'],
                        "password" => $options['password'],
                        "trace" => 1
                    ]);
            } catch (\SoapFault $e) {
                return false;
            }
        } else {
            return false;
        }
    }
}