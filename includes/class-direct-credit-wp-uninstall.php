<?php

/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 *
 * @package    Direct_Credit_WP
 * @subpackage Direct_Credit_WP/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Direct_Credit_WP
 * @subpackage Direct_Credit_WP/includes
 * @author     Nikita Ivanov (Nick Iv)
 */
class Direct_Credit_WP_Uninstall
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function uninstall()
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'direct_credit');
    }

}
