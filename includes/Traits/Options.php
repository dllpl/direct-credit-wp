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

        $rows = $wpdb->get_results("SELECT * FROM " . $table_name . " ORDER BY id DESC LIMIT 1");

        if (count($rows)) {
            return [
                'login' => $rows[0]->login,
                'password' => $rows[0]->password,
                'wsdl' => $rows[0]->wsdl,
                'location' => $rows[0]->location,
                'email' => $rows[0]->email,
                'bitrix_webhook_url' => $rows[0]->bitrix_webhook_url,
                'bitrix_entity_type_id' => (int) $rows[0]->bitrix_entity_type_id,
                'codeTT'=> $rows[0]->codeTT,
                'partnerID' => $rows[0]->partnerID,
            ];
        } else {
            return false;
        }
    }
}
