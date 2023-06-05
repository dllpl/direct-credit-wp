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
            <form action="wp-json/dc/v1/updateSettings" method="POST">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?php $options['location'] ?? null ?>"><br>

                <label for="wsdl">WSDL:</label>
                <input type="text" id="wsdl" name="wsdl" value="<?php $options['wsdl'] ?? null ?>"><br>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="<?php $options['password'] ?? null ?>"><br>

                <label for="login">Login:</label>
                <input type="text" id="login" name="login" value="<?php $options['login'] ?? null ?>"><br>
                <?php
                settings_fields('option_group');
                do_settings_sections('primer_page');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function updateSettings()
    {

    }
}