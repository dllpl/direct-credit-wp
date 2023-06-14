<?php

require_once plugin_dir_path(__FILE__) . '../../includes/Traits/Options.php';

class PublicController
{
    use Options;

    private $options;

    public function __construct()
    {
        $this->options = Options::getOptions();
        if($this->options['integration_method'] !== 'rest_js') {
            return false;
        }
    }

    public function scriptInit()
    {
        wp_enqueue_script('dc_script', plugins_url('../../public/js/dc_script.js', __FILE__), ['jquery'],
            filemtime(plugin_dir_path(__FILE__) . '../../public/js/dc_script.js'), 'in_footer');

        if ($this->options && is_array($this->options) && !empty($this->options['partnerID']) && !empty($this->options['price_id']) && !empty($this->options['phone_id'])
            && !empty($this->options['click_on_credit_id']) && !empty($this->options['name_product_id'])
        ) {
            $data = [
                'partnerID' => $this->options['partnerID'],
                'price_id' => $this->options['price_id'],
                'phone_id' => $this->options['phone_id'],
                'click_on_credit_id' => $this->options['click_on_credit_id'],
                'name_product_id' => $this->options['name_product_id'],
                'createOrderUri' => 'dc/v1/createOrder'
            ];
        } else {
            $data = ['error' => 'Задайте настройки плагина Директ Кредит, чтобы начать работу.'];
        }

        wp_add_inline_script('dc_script', 'const dcData = ' . wp_json_encode($data), 'before');
    }
}
