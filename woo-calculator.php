<?php
namespace codeit\WooCommerce_Dimensions_Calculator;

use codeit\WP_Settings;
/**
 *         _       _       
 *        (_)     (_)      
 *   _ __  _ _ __  _  __ _ 
 *  | '_ \| | '_ \| |/ _` |
 *  | | | | | | | | | (_| |
 *  |_| |_|_|_| |_| |\__,_|
 *               _/ |      
 *              |__/   
 *
 * Version: 1.0.2
 * Plugin Name: Code IT - WooCommerce product dimensions calculator
 * Plugin URI: https://codeit.ninja
 * Description: Add a product calculator to your products which can calculate the amount of products a user needs for given dimensions
 * Author: Code IT Ninja
 * Author URI: https://codeit.ninja
 * Update URI: https://github.com/codeit-ninja/woo-product-dimensions
 * Text Domain: codeit
 * Domain Path: /languages
 * 
 * You are not allowed to sell or distribute this plugin without
 * the permission of its author
 * 
 * You can contact the author of this plugin at richard@codeit.ninja
 *
 * @package CodeIT\WPML_Translator
 */
include plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

class Woo_Calculator
{
    /**
     * Plugin options
     * 
     * @var WP_Settings\Options
     */
    protected WP_Settings\Options $options;

    public function __construct()
    {
        $plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ), false );

        new Updater( $plugin_data['Version'], 'https://raw.githubusercontent.com/codeit-ninja/wordpress-wpml-deepl-auto-translator/master/composer.json', plugin_basename( __DIR__ ) );
        /**
         * Register and create options page
         */
        $this->create_options();
        /**
         * Loads plugin script files
         *
         * @since 1.0.0
         */
        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
        /**
         * Add custom meta fields to product general options
         *
         * @since 1.0.0
         */
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_general_options_fields' ) );
        /**
         * Saves custom product meta fields we added above
         *
         * @since 1.0.0
         */
        add_action( 'woocommerce_process_product_meta',  array( $this, 'save_general_options_fields' ) );
        /**
         * Insert dimensions template
         *
         * @since 1.0.0
         */
        add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'insert_dimensions_template' ) );
        /**
         * Loads plugin text domain
         *
         * @since 1.0.0
         */
        add_action( 'init', array( $this, 'load_text_domain' ) );
        /**
         * Loads plugin text domain translator
         * for current locale
         *
         * @since 1.0.0
         */
        add_filter( 'load_textdomain_mofile', array( $this, 'load_plugin_textdomain' ), 10, 2 );
    }

    /**
     * Register and create options page
     *
     * All sections and fields are created here
     *
     * @return void
     */
    public function create_options()
    {
        $this->options = new WP_Settings\Options( 'Woo calculator', plugin_basename( __DIR__ ), plugin_basename( __DIR__ ), plugin_dir_path( __FILE__ ) );
        $this->options
            ->add_section('form-settings', 'Form settings')
            ->add_field( 'heading', __('Heading title', 'codeit'), 'text', 'form-settings', 'VOER BESTELMATEN IN (INCL. MARGE)', array( 'description' => __('This is displayed above the form.', 'codeit') ))
            ->add_field( 'measurement', __('Measuring unit', 'codeit'), 'text', 'form-settings', 'cm', array( 'description' => __('What unit we measuring in? Eg; cm, m, mm', 'codeit' ), 'placeholder' => 'mm, cm, m' ) )
            ->add_field( 'measurement-x', __('Measurement label x', 'codeit'), 'text', 'form-settings', 'Lengte', array( 'description' => __('Label above the \'x\' field', 'codeit' ), 'placeholder' => 'Length' ) )
            ->add_field( 'measurement-y', __('Measurement label y', 'codeit'), 'text', 'form-settings', 'Breedte', array( 'description' => __('Label above the \'y\' field', 'codeit' ), 'placeholder' => 'width' ) );
        $this->options->create();
    }

    /**
     * Inserts dimensions form into products template
     *
     * @return void
     */
    public function insert_dimensions_template()
    {
        global $post;

        if( ! $post || ! $post->ID ) return;
        if( ! get_post_meta( $post->ID, '_woo_calculator_show_form', true ) ) return;
        if( ! file_exists( $this->options->get_basedir() . '/templates/form.php' ) ) return;

        include $this->options->get_basedir() . '/templates/form.php';
    }

    /**
     * Load plugin scripts
     *
     * @return void
     */
    public function load_scripts(): void
    {
        wp_enqueue_style( 'woo-product-dimensions-styles', plugins_url() . '/woo-product-dimensions-master/src/css/style.css' );
        wp_enqueue_script( 'woo-product-dimensions-scripts', plugins_url() . '/woo-product-dimensions-master/src/js/main.js' );
    }

    /**
     * Add custom metadata to WooCommerce product
     *
     * Will be added in the 'general options' tab
     *
     * @return void
     */
    public function add_general_options_fields(): void
    {
        woocommerce_wp_checkbox( array(
            'id'    => '_woo_calculator_show_form',
            'label' => __('Show dimensions form?', 'codeit')
        ) );
    }

    /**
     * Save custom metadata fields to database
     *
     * @param int $post_id
     * @return void
     */
    public function save_general_options_fields( int $post_id ): void
    {
        $show_form_dimensions_field = $_POST['_woo_calculator_show_form'];

        if( $show_form_dimensions_field ) {
            update_post_meta( $post_id, '_woo_calculator_show_form', esc_attr( $show_form_dimensions_field ) );
        }
    }

    /**
     * Loads plugin text domain
     *
     * @return void
     */
    public function load_text_domain()
    {
        load_plugin_textdomain( 'codeit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Loads plugin translation file
     *
     * @param $mofile
     * @param $domain
     * @return string
     */
    public function load_plugin_textdomain( $mofile, $domain ): string
    {
        if ( 'codeit' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
            $locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
            $mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $locale . '.mo';
        }

        return $mofile;
    }
}

new Woo_Calculator();