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
                <label for="location">Location:</label> <br>
                <input type="text" id="location" name="location" value="<?php $options['location'] ?? null ?>"><br>

                <label for="wsdl">WSDL:</label> <br>
                <input type="text" id="wsdl" name="wsdl" value="<?php $options['wsdl'] ?? null ?>"><br>

                <label for="password">Password:</label> <br>
                <input type="password" id="password" name="password" value="<?php $options['password'] ?? null ?>"><br>

                <label for="login">Login:</label> <br>
                <input type="text" id="login" name="login" value="<?php $options['login'] ?? null ?>"><br>
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
                    fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-WP-Nonce': wpApiSettings.nonce
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                document.getElementById('error').innerText = data.data
                            } else {
                                document.getElementById('success').innerText = data.data
                            }
                        })
                        .catch(error => {
                            document.getElementById('error').innerText = error.data
                        })
                        .finally(() => {
                            setTimeout(() => {
                                document.getElementById('error').innerText = ''
                                document.getElementById('success').innerText = ''
                            }, 2000)
                        })
                });
            </script>
        </div>
        <?php
    }

    public function updateSettings()
    {
        return wp_send_json_success('Успешное обновление');
    }
}