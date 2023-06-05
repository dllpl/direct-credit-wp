<?php
trait Options
{
    /**
     * @return array|false
     */
    public static function getOptions()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_options';

        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE id = 1"));

        if (count($rows)) {
            return [
                'login' => $rows[0]->login,
                'password' => $rows[0]->password,
                'wsdl' => $rows[0]->wsdl,
                'location' => $rows[0]->location
            ];
        } else {
            return false;
        }
    }
}