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
 * Version: 1.2.6
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
         * Loads plugin admin area script file
         *
         * @since 1.0.0
         */
        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
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
        /**
         * Init plugin updater
         *
         * This makes plugin updatable through the WordPress
         * plugins page and GitHub
         */
        new Updater( $plugin_data['Version'], plugin_basename( __FILE__ ), 'https://raw.githubusercontent.com/codeit-ninja/woo-dimensions-calculator/master/composer.json' );
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
            ->add_field(
                'heading',
                __('Heading title', 'codeit'),
                'text',
                'form-settings',
                'VOER BESTELMATEN IN (INCL. MARGE)',
                array( 'description' => __('This is displayed above the form.', 'codeit') ))
            ->add_field(
                'measurement-unit',
                __('Area unit of measurement', 'codeit'),
                'dropdown',
                'form-settings',
                'ft2',
                array('values' => [
                    'm2' => __('m2 (Square m)', 'codeit'),
                    'cm2' => __('cm2 (Square cm)', 'codeit'),
                    'mm2' => __('mm2 (Square mm)', 'codeit'),
                ])
            )
            ->add_field(
                'measurement-unit-input',
                __('Input measurement', 'codeit'),
                'dropdown',
                'form-settings',
                'cm',
                array(
                    'values' => [
                        'm' => __('Meter', 'codeit'),
                        'cm' => __('Centimeter', 'codeit'),
                        'mm' => __('Millimeter', 'codeit'),
                        'µm' => __('Micron', 'codeit'),
                    ],
                    'description' => __('The unit of measurement customers need to fill into the form.', 'codeit')
                )
            )
            ->add_field(
                'measurement-x',
                __('Measurement label x', 'codeit'),
                'text', 'form-settings',
                'Lengte',
                array( 'description' => __('Label above the \'x\' field', 'codeit' ), 'placeholder' => 'Length' ) )
            ->add_field(
                'measurement-y',
                __('Measurement label y', 'codeit'),
                'text',
                'form-settings',
                'Breedte',
                array( 'description' => __('Label above the \'y\' field', 'codeit' ), 'placeholder' => 'width' ) );
        $this->options->create();
    }

    /**
     * Inserts dimensions form into products template
     *
     * @return void
     */
    public function insert_dimensions_template(): void
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
        global $product;

        wp_enqueue_style( 'woo-product-dimensions-styles', plugins_url( '/src/css/style.css', __FILE__,  ) );
        wp_enqueue_script( 'woo-product-dimensions-scripts', plugins_url( '/src/js/main.js', __FILE__ ) );

        wp_add_inline_script(
            'woo-product-dimensions-scripts',
            '
                const ci_wc_settings = ' . json_encode( $this->options->get_options() ) . '
                const ci_wc_product = ' . json_encode( wc_get_product()->get_data() )
        );
    }

    /**
     * Load plugin admin scripts
     *
     * @param string $hook_suffix
     * @return void
     */
    public function load_admin_scripts( string $hook_suffix ): void
    {
        if( 'post.php' == $hook_suffix || 'post-new.php' == $hook_suffix ) {
            wp_enqueue_script( 'woo-product-dimensions-admin-script', plugins_url( '/src/js/admin.js', __FILE__ ) );
        }
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

        woocommerce_wp_text_input( array(
            'id'    => '_woo_calculator_square_meter_total',
            'label' => sprintf(__('How much %s per product?', 'codeit'), $this->options->get_option('measurement-unit', 'form-settings')),
            'desc_tip' => true,
            'description' => __('You can calculate this by multiplying the length times the width. (L x W)', 'codeit'),
            'type'  => 'number'
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
        $show_form_quantity_total_field = $_POST['_woo_calculator_square_meter_total'];

        if( $show_form_dimensions_field ) {
            update_post_meta( $post_id, '_woo_calculator_show_form', esc_attr( $show_form_dimensions_field ) );
        }

        if( $show_form_quantity_total_field ) {
            update_post_meta( $post_id, '_woo_calculator_square_meter_total', esc_attr( $show_form_quantity_total_field ) );
        }
    }

    /**
     * Loads plugin text domain
     *
     * @return void
     */
    public function load_text_domain(): void
    {
        load_plugin_textdomain( 'codeit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Loads plugin translation file
     *
     * @param $mofile
     * @param $domain
     *
     * @return string
     */
    public function load_plugin_textdomain( $mofile, $domain ): string
    {
        if ( 'codeit' === $domain && str_contains($mofile, WP_LANG_DIR . '/plugins/')) {
            $locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
            $mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/'. $domain .'-'. $locale .'.mo';
        }

        return $mofile;
    }
}

new Woo_Calculator();