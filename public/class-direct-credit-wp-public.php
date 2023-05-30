<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Direct_Credit_WP
 * @subpackage Direct_Credit_WP/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Direct_Credit_WP
 * @subpackage Direct_Credit_WP/public
 * @author     Nikita Ivanov (Nick Iv)
 */
class Direct_Credit_WP_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $direct_credit_wp    The ID of this plugin.
	 */
	private $direct_credit_wp;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $direct_credit_wp       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $direct_credit_wp, $version ) {

		$this->direct_credit_wp = $direct_credit_wp;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Direct_Credit_WP_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Direct_Credit_WP_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->direct_credit_wp, plugin_dir_url( __FILE__ ) . 'css/direct-credit-wp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Direct_Credit_WP_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Direct_Credit_WP_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->direct_credit_wp, plugin_dir_url( __FILE__ ) . 'js/direct-credit-wp-public.js', array( 'jquery' ), $this->version, false );

	}

    public function true_top_menu_page(){

        add_menu_page(
            'Настройки слайдера', // тайтл страницы
            'Слайдер', // текст ссылки в меню
            'manage_options', // права пользователя, необходимые для доступа к странице
            'true_slider', // ярлык страницы
            'true_slider_page_callback', // функция, которая выводит содержимое страницы
            'dashicons-images-alt2', // иконка, в данном случае из Dashicons
            20 // позиция в меню
        );

        function true_slider_page_callback() {
            echo 'привет';
        }
    }

}
