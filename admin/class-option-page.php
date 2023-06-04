<?php

class OptionPage
{
    private string $login;
    private string $password;
    private string $wsdl;
    private string $location;

    private $text;

    public function __construct()
    {
        if(!$this->getOptions()) {
            $this->text = 'Опций не найдено';
        } else {
            $this->text = [$this->login,$this->password,$this->wsdl,$this->location];
        }
    }

    public function addMenu()
    {
        add_options_page( 'Настройки плагина','Директ Кредит','manage_options','direct_credit_settings', [$this, 'settingsPage']);
    }

    public function settingsPage()
    {
        ?>
        <div class="wrap">
            <h2><?php echo get_admin_page_title() ?></h2>
            <form action="options.php" method="POST">
                <?php
                settings_fields( 'option_group' );
                do_settings_sections( 'primer_page' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function updateSettings()
    {

    }

    private function getOptions()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_options';

        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE id = 1"));

        if (count($rows)) {
            foreach ($rows as $row) {
                $this->login = $row->login;
                $this->password = $row->password;
                $this->wsdl = $row->wsdl;
                $this->location = $row->location;
            }
            return true;
        } else {
            return false;
        }
    }
}