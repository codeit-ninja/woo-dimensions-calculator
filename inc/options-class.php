<?php
namespace codeit\WP_Settings;
/**
 * A better WP Settings API implementation OOP based
 * 
 *         _       _       
 *        (_)     (_)      
 *   _ __  _ _ __  _  __ _ 
 *  | '_ \| | '_ \| |/ _` |
 *  | | | | | | | | | (_| |
 *  |_| |_|_|_| |_| |\__,_|
 *               _/ |      
 *              |__/   
 *
 * @package CodeIT\WPML_Translator
 */
class Options {
    /**
     * Page name under which the settings are stored
     * 
     * @var string
     */
    protected string $page_name;
    /**
     * Unique plugin name to store settings under
     * 
     * @var string
     */
    protected string $plugin_name;

    /**
     * Basename of the plugin the options are registered for
     * 
     * @var string
     */
    protected string $plugin_basename;
    /**
     * Base directory of the plugin the options are registered for
     * 
     * @var string
     */
    protected string $plugin_basedir;
    /**
     * Name of the options page
     * 
     * @var string
     */
    protected string $options_page = 'codeit-plugins-settings';
    /**
     * Array of option section fields
     * 
     * @var array
     */
    protected array $sections = array();
    /**
     * Array of option fields
     * 
     * @var array
     */
    protected array $fields = array();
    /**
     * Array of option values
     * 
     * @var array
     */
    protected array $options = array();

    public function __construct( string $page_name, string $plugin_name, string $plugin_basename, string $plugin_basedir )
    {
        $this->page_name = $page_name;
        $this->plugin_name = $plugin_name;
        $this->plugin_basename = $plugin_basename;
        $this->plugin_basedir = $plugin_basedir;
        $this->options = get_option($this->plugin_name . '-settings', []);
    }

    /**
     * Call this function only once in your plugin
     * This script will hook into WordPress admin hooks
     * 
     * When calling it multiple times it will interfere with
     * already running instances
     */
    public function create(): Options
    {
        add_action('admin_menu', array( $this, 'add_options_page' ));
        add_action('admin_init', array( $this, 'init' ));

        return $this;
    }

    public function init(): void
    {
        register_setting( $this->plugin_name, $this->plugin_name . '-settings' );

        foreach( $this->sections as $id => $section ) {
            add_settings_section( $id, $section['title'], function () use ( $section ) { echo $section['description']; }, $this->options_page );
        }

        foreach( $this->fields as $id => $field ) {
            add_settings_field( $id, esc_html__($field['title'], 'codeit'), $field['callback'], $this->options_page, $field['section'], array_merge(array( 'id' => $id, 'section' => $field['section'] ), $field['args'] ) );

            if( isset( $field['default'] ) && $field['default'] && ! $this->get_option( $id, $field['section']  ) ) {
                $this->options[$field['section']][$id] = $field['default'];

                /**
                 * Store default values if set in case developer
                 * tries to retrieve option through the WP get_option function
                 */
                update_option( $this->plugin_name . '-settings', $this->options );
            }
        }
    }

    public function add_options_page(): void
    {
        add_options_page(
            $this->page_name, $this->page_name, 'manage_options', $this->options_page, fn() => include plugin_dir_path(__DIR__) . '/templates/settings.php'
        );
    }

    public function add_section( string $id, string $title, string $description = '' ): Options
    {
        $this->sections[$id] = [
            'title'     => $title,
            'description'  => $description
        ];

        return $this;
    }

    public function add_field( string $id, string $title, string $type = 'text', string $section = 'default', $default_value = null, array $args = array() ): Options
    {
        /**
         * Prevent overriding `id` field
         */
        unset($args['id']);

        $this->fields[$id] = [
            'type'      => $type,
            'title'     => $title,
            'section'   => $section,
            'args'      => $args,
            'default'   => $default_value
        ];

        if( 'text' === $type ) {
            $this->fields[$id]['callback'] = array( $this, 'render_text_field' );
        }

        if( 'checkbox' === $type ) {
            $this->fields[$id]['callback'] = array( $this, 'render_checkbox' );
        }

        if( 'dropdown' === $type ) {
            $this->fields[$id]['callback'] = array( $this, 'render_dropdown' );
        }

        return $this;
    }

    public function render_text_field( array $args ): void
    {
        echo "<input 
            id='". $this->plugin_name ."_". $args['id'] ."'
            name='". $this->plugin_name . '-settings['. $args['section'] .']['. $args['id'] ."]'
            type='text'
            class='regular-text ltr'
            value='{$this->options[$args['section']][$args['id']]}'";

            if( isset( $args['placeholder'] ) ) {
                echo "placeholder='". $args['placeholder'] . "'";
            }

        echo "/>";

        if( isset( $args['description'] ) ) {
            echo "<p class='description'>" . __($args['description'], 'codeit') . "</p>";
        }
    }

    /**
     * @param array $args
     *
     * @return void
     */
    public function render_checkbox( array $args ): void
    {
        $label_text = apply_filters('codeit_checkbox_label', $args);
        $checkbox_state = apply_filters('codeit_checkbox_state', $args);

        echo "<input 
            type='checkbox' 
            id='". $this->plugin_name ."_". $args['id'] ."'
            name='". $this->plugin_name . '-settings['. $args['section'] .']['. $args['id'] ."]'";

            echo $checkbox_state;

            if ( isset( $this->options[$args['section']][$args['id']] ) ) {
                echo ' checked';
            }

        echo '/>';
        echo '<label for="' . $this->plugin_name ."_". $args['id'] . '">'. esc_html__($label_text, 'codeit') .'</label>';

        if( isset( $args['description'] ) ) {
            echo "<p class='description'>" . __($args['description'], 'codeit') . "</p>";
        }
    }

    function render_dropdown( array $args ): void
    {
        ?>
        <label>
            <select name="<?php echo $this->plugin_name . '-settings['. $args['section'] .']['. $args['id'] .']'; ?>">
                <?php foreach ( $args['values'] as $value => $name ) : ?>
                    <option value="<?php echo $value ?>" <?php echo selected($this->get_option($args['id'], 'form-settings'), $value) ?>><?php echo $name ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php

        if( isset( $args['description'] ) ) {
            echo "<p class='description'>" . __($args['description'], 'codeit') . "</p>";
        }
    }

    public function get_options( string $section = null )
    {
        if ( ! $section ) {
            return $this->options;
        }

        return $this->options[$section];
    }

    /**
     * @param string $key
     * @param string|null $section
     *
     * @return string|null
     */
    public function get_option( string $key, string $section = null ): ?string
    {
        if( $section ) {
            return $this->options[$section][$key] ?? null;
        }

        return $this->options[$key] ?? null;
    }

    public function get_basename(): string
    {
        return $this->plugin_basename;
    }

    public function get_basedir(): string
    {
        return $this->plugin_basedir;
    }
}