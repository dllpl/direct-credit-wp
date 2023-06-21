<?php

require_once plugin_dir_path(__FILE__) . '../../includes/Traits/Options.php';

class PublicController
{
    use Options;

    private $options;

    public function __construct()
    {
        $this->options = Options::getOptions();
    }

    public function scriptInit()
    {
        wp_enqueue_script('dc_script', plugins_url('../../public/js/dc_script.js', __FILE__), ['jquery'],
            filemtime(plugin_dir_path(__FILE__) . '../../public/js/dc_script.js'), 'in_footer');

        wp_enqueue_style('dc_style', plugins_url('../../public/css/dc_style.css', __FILE__), [],
            filemtime(plugin_dir_path(__FILE__) . '../../public/css/dc_style.css'), 'all');

        if ($this->options && is_array($this->options)) {
            $data = [
                'partnerID' => $this->options['partnerID'],
                'finish_redirect_url' => $this->options['finish_redirect_url'],
                'createOrderUri' => '/wp-json/dc/v1/createOrder'
            ];
        } else {
            $data = ['error' => 'Задайте настройки плагина Директ Кредит, чтобы начать работу.'];
        }

        wp_add_inline_script('dc_script', 'const dcData = ' . wp_json_encode($data), 'before');
    }
}
