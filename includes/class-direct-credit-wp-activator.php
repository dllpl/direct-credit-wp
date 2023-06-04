<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    Direct_Credit_WP
 * @subpackage Direct_Credit_WP/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Direct_Credit_WP
 * @subpackage Direct_Credit_WP/includes
 * @author     Nikita Ivanov (Nick Iv)
 */
class Direct_Credit_WP_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        /** Создание таблицы для опций */
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_options';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            `id` INT NOT NULL AUTO_INCREMENT,
            `login` VARCHAR(255) DEFAULT NULL,
            `password` VARCHAR(255) DEFAULT NULL,
            `wsdl` VARCHAR(255) DEFAULT NULL,
            `location` VARCHAR(255) DEFAULT NULL,
            `created_at` DATETIME DEFAULT NOW(),
            PRIMARY KEY (`id`)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        /** Создание таблицы для заявок */
        global $wpdb;
        $table_name = $wpdb->prefix . 'direct_credit_requests';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            `id` INT NOT NULL AUTO_INCREMENT,
            `order_id` VARCHAR(255) DEFAULT NULL,
            `dc_token` VARCHAR(255) DEFAULT NULL,
            `address` VARCHAR(255) DEFAULT NULL,
            `status` INT DEFAULT NULL,
            `json` VARCHAR(max) DEFAULT NULL,
            `created_at` DATETIME DEFAULT NOW(),
            PRIMARY KEY (`id`)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
