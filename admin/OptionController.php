<?php

require_once plugin_dir_path(__FILE__) . '../includes/Traits/Options.php';

class OptionPage
{
    use Options;

    private $options;

    public function __construct()
    {
        $options = Options::getOptions();
        if ($options) {
            $this->options = $options;
        } else {
            $options = null;
        }
    }

    public function addMenu()
    {
        add_options_page('Настройки плагина', 'Директ Кредит', 'manage_options', 'direct_credit_settings', [$this, 'settingsPage']);
    }

    public function settingsPage()
    {
        $options = $this->options;
        ?>
        <div class="wrap">
            <h2><?php echo get_admin_page_title() ?></h2>
            <form>
                <h3>Данные для авторизации Soap-клиента</h3>
                <label for="login">Login<span style="color: red">*</span></label> <br>
                <input type="text" id="login" name="login" value="<?php echo $options['login'] ?? null ?>" required><br>
                <label for="password">Password<span style="color: red">*</span></label> <br>
                <input type="password" id="password" name="password" value="<?php echo $options['password'] ?? null ?>" required><br>
                <label for="wsdl">WSDL (URL)<span style="color: red">*</span></label> <br>
                <input type="url" pattern="https://.*" id="wsdl" name="wsdl" value="<?php echo $options['wsdl'] ?? null ?>" required><br>
                <label for="location">Location (URL)<span style="color: red">*</span></label> <br>
                <input type="url" pattern="https://.*" placeholder="" id="location" name="location" value="<?php echo $options['location'] ?? null ?>" required><br>
                <hr>
                <h3>Данные партнера</h3>
                <label for="partnerID">partnerID<span style="color: red">*</span></label> <br>
                <input type="text" id="partnerID" name="partnerID" value="<?php echo $options['partnerID'] ?? null ?>" required><br>
                <label for="codeTT">codeTT<span style="color: red">*</span></label> <br>
                <input type="text" id="codeTT" name="codeTT" value="<?php echo $options['codeTT'] ?? null ?>" required><br>
                <hr>
                <h3>Способ работы плагина</h3>
                <div>
                    <input type="radio" id="integration_method_rest"
                           name="integration_method" value="rest" <?php echo $options['integration_method'] === 'rest' ? 'checked' : '' ?>>
                    <label for="contactChoice1">REST</label>
                    <input type="radio" id="integration_method_rest_js"
                           name="integration_method" value="rest_js" <?php echo $options['integration_method'] === 'rest_js' ? 'checked' : '' ?>>
                    <label for="contactChoice1">REST + JS</label>
                </div>
                <hr>
                <h3>Данные для работы скриптов (обязательно, если выбран вариант REST + JS)</h3>
                <label for="price_id">ID поля цены товара</label> <br>
                <input type="text" id="price_id" name="price_id" value="<?php echo $options['price_id'] ?? null ?>"><br>
                <label for="name_product_id">ID поля названия товара</label> <br>
                <input type="text" id="name_product_id" name="name_product_id" value="<?php echo $options['name_product_id'] ?? null ?>"><br>
                <label for="phone_id">ID поля указания телефона</label> <br>
                <input type="text" id="phone_id" name="phone_id" value="<?php echo $options['phone_id'] ?? null ?>"><br>
                <label for="click_on_credit_id">ID кнопки оформления кредита (кнопка действия)</label> <br>
                <input type="text" id="click_on_credit_id" name="click_on_credit_id" value="<?php echo $options['click_on_credit_id'] ?? null ?>"><br>
                <hr>
                <h3>Конечные точки (необязательные поля)</h3>
                <label for="email">Email (оставьте пустым, если не хотите использовать отправку на почту)</label> <br>
                <input type="email" id="email" name="email" value="<?php echo $options['email'] ?? null ?>"><br>
                <label for="bitrix_webhook_url">Битрикс24 API URL (оставьте пустым, если не хотите интегрировать с битрикс24)</label> <br>
                <input type="url" placeholder="Пример: https://{domain}.bitrix24.ru/rest/{user}/{key}" pattern="https://.*" id="bitrix_webhook_url" name="bitrix_webhook_url" value="<?php echo $options['bitrix_webhook_url'] ?? null ?>"><br>
                <label for="bitrix_entity_type_id">Идентификатор смарт-процесса (оставьте пустым, если не хотите создавать смарт-процесс)</label> <br>
                <input type="text" placeholder="Числовой идентификатор" id="bitrix_entity_type_id" name="bitrix_entity_type_id" value="<?php echo $options['bitrix_entity_type_id'] ?? null ?>"><br>

                <div style="color: red" id="error"></div>
                <div style="color: forestgreen" id="success"></div>
                <?php
                submit_button();
                ?>
            </form>
            <script>
                const form = document.querySelector('form');
                const url = '/wp-json/dc/v1/updateSettings';

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    const formData = new FormData(form);
                    const data = {};
                    for (const [key, value] of formData.entries()) {
                        if(value === '') continue
                        data[key] = value;
                    }
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce':  wpApiSettings.nonce,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                document.getElementById('error').innerText = data?.message ?? data.data
                            } else {
                                document.getElementById('success').innerText = data.data
                            }
                        })
                        .catch(error => {
                            document.getElementById('error').innerText = error.data.message
                        })
                        .finally(() => {
                            setTimeout(() => {
                                document.getElementById('error').innerText = ''
                                document.getElementById('success').innerText = ''
                            }, 5000)
                        })
                });
            </script>
            <style>
                input{width: 400px;}
            </style>
        </div>
        <?php
    }

    public function updateSettings($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_options';

        $data = [
            'wsdl' => $request['wsdl'],
            'login' => $request['login'],
            'password' => $request['password'],
            'location' => $request['location'],
            'email' => $request['email'] ?? null,
            'bitrix_webhook_url' => $request['bitrix_webhook_url'] ?? null,
            'bitrix_entity_type_id' => $request['bitrix_entity_type_id'] ?? null,
            'codeTT' => $request['codeTT'],
            'partnerID' => $request['partnerID'],

            'price_id' => $request['price_id'] ? str_replace('#', '', $request['price_id']) : null,
            'name_product_id' => $request['name_product_id']  ? str_replace('#', '', $request['name_product_id']) : null,
            'phone_id' => $request['phone_id'] ? str_replace('#', '', $request['phone_id']) : null,
            'integration_method' => $request['integration_method']
        ];

        $result = $wpdb->insert($table_name, $data);

        if ($result) {
            return wp_send_json_success('Успешное сохранение данных');
        } else {
            return wp_send_json_error('Ошибка при сохранении данных');
        }
    }
}
