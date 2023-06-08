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

class Activator
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
        /** Создание таблиц опций и заявок */
        global $wpdb;
        $table_options = $wpdb->prefix . 'direct_credit_options';
        $table_orders = $wpdb->prefix . 'direct_credit_orders';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = ["
            CREATE TABLE $table_options (
            `id` INT NOT NULL AUTO_INCREMENT,
            `partnerID` VARCHAR(255) NOT NULL,
            `codeTT` VARCHAR(255) NOT NULL,
            `login` VARCHAR(255) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `wsdl` VARCHAR(255) NOT NULL,
            `location` VARCHAR(255) NOT NULL,
            `bitrix_webhook_url` VARCHAR(255) DEFAULT NULL,
            `email` VARCHAR(255) DEFAULT NULL,
            `created_at` DATETIME DEFAULT NOW(),
            PRIMARY KEY (`id`)
        ) $charset_collate
        ", "
            CREATE TABLE $table_orders (
            `id` INT NOT NULL AUTO_INCREMENT,
            `order_id` VARCHAR(255) DEFAULT NULL,
            `dc_token` VARCHAR(255) DEFAULT NULL,
            `address` VARCHAR(255) DEFAULT NULL,
            `dc_status` INT DEFAULT NULL,
            `json` JSON DEFAULT NULL,
            `created_at` DATETIME DEFAULT NOW(),
            PRIMARY KEY (`id`)
        ) $charset_collate"];

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
