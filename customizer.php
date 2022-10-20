<?php

//nosegraze tutorial https://www.nosegraze.com/customizer-settings-wordpress-theme/

class Generatepress_Child_Customizer {

    private $custom_color_control_keys = array(
        'custom_color1' => array(
            'title' => 'Color 1',
            'description' => 'Your main theme color. This will be applied to the header, footer, and other major elements.'
        ),
        'custom_color2' => array(
            'title' => 'Color 2',
            'description' => 'Your secondary theme color. This will be applied to buttons, accents, and other elements.'
        ),
        'custom_color1a' => array(
            'title' => 'Color 1 Alternate',
            'description' => 'Alternate or hover color for elements that are color 1. This will be used occasionally.'
        ),
        'custom_color2a' => array(
            'title' => 'Color 2 Alternate',
            'description' => 'Alternate or hover color for elements that are color 1. This will be used occasionally.'
        )
    );

	public function __construct() {
        // add customizer controls
        add_action( 'customize_register', array( $this, 'register_customize_sections' ) );

        // enqueue customizer scripts
        add_action('customize_controls_enqueue_scripts', array($this, 
        'customize_preview_js'));

        add_filter('generate_option_defaults', array($this, 'customize_global_colors'));
        add_filter('option_generate_settings', array($this, 'customize_global_colors'));


    }
    
    /**
     * Add all sections and panels to the customizer.
     *
     * @param WP_Customize_Manager $wp_customize customizer object
     * @return void
     */
    public function register_customize_sections( $wp_customize ) {    
        // New panels
        $wp_customize->add_section( 'logo_colors', array(
            'title'    => __( 'Logo Colors (HUEnique)', 'generatepress_child' ),
            'priority' => 20
        ) );
        //Add settings to sections
        $this->logo_colors_section( $wp_customize );

    }

    /**
     * Enqueues customizer preview script.
     *
     * @return void
     */
    public function customize_preview_js() {
        wp_enqueue_script(
            'wap_customize_preview_js', 
            get_stylesheet_directory_uri() . '/js/customizer-preview.js', 
            array('customize-preview', 'jquery'), 
            date("h:i:s"),
            true
        );
        
        // enqueue colorthief library
        wp_enqueue_script(
            'color_thief',
            get_stylesheet_directory_uri() . '/js/color-thief.min.js',
            array(),
            date("h:i:s"),
            true
        );
    }


    public function customize_global_colors($settings) {
        $color1 = get_option('custom_color1');
        $color2 = get_option('custom_color2');
        $color1a = get_option('custom_color1a');
        $color2a = get_option('custom_color2a');

        // if custom colors haven't been set yet, return unchanged settings
        if (!$color1 || $color1 == '#ffffff') return $settings;

        // change first accent color
        foreach ($settings['global_colors'] as &$global_color) {
            if ($global_color['slug'] != 'accent') continue;

            $global_color['color'] = $color1;
        }

        if (!is_null($settings['global_colors'])) {
            $settings['global_colors'][] = array(
                'name' => sprintf(__('Accent %s', 'generatepress'), '2'),
                'slug' => 'accent-2',
                'color' => $color2
            );

            // if accent exists, change it. if not, add it
            if ($accent_key) {
                $settings['global_colors'][$accent_key]['color'] = $default_color;
            } else {
                $settings['global_colors'][] = array(
                    'slug' => 'accent',
                    'name' => __('Accent', 'generatepress_child'),
                    'color' => $default_color
                );
            }
            
            // add other accent colors with default color
            $settings['global_colors'] = array_merge(
                $settings['global_colors'],
                array(
                    array(
                        'slug' => 'accent-2',
                        'name' => __(sprintf('Accent %s', 2), 'generatepress_child'),
                        'color' => $default_color
                    ),
                    array(
                        'slug' => 'accent-3',
                        'name' => __(sprintf('Accent %s', 3), 'generatepress_child'),
                        'color' => $default_color
                    ),
                    array(
                        'slug' => 'accent-4',
                        'name' => __(sprintf('Accent %s', 4), 'generatepress_child'),
                        'color' => $default_color
                    ),
                )
            );
        }

        return $settings;
    }

    //TODO?: don't show up until logo processed
    //https://make.xwp.co/2016/07/24/dependently-contextual-customizer-controls/
    /**
     * Adds settings and controls for the logo upload and color pickers.
     *
     * @param WP_Customize_Manager $wp_customize
     * @return void
     */
    private function logo_colors_section( $wp_customize ) {
        $wp_customize->add_setting( 'logo', array(
            'transport' => 'postMessage',
            'type' => 'option'
        ) );

        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'logo_control',
                array(
                    'label'      => __( 'Upload your logo', 'generatepress_child' ),
                    'description' => __( 'The main colors from your logo will be extracted and used to set your theme colors. <br><br> The colors below will be automatically generated when you upload an image, then you can further modify them or specific elements as desired.<br><br>For best results, make sure your logo has a transparent background (not solid white) and at least two colors.', 'generatepress_child' ),
                    'section'    => 'generate_colors_section',
                    'settings'   => 'logo',
                    'priority' => 9,
                    'transport' => 'postMessage',
                )
            )
        );

        $this->render_color_picker_controls($wp_customize);
        
    }

    /**
     * Loops through color picker control data and adds a customizer color
     * control for each one.
     *
     * @param WP_Customize_Manager $wp_customize
     * @return void
     */
    private function render_color_picker_controls($wp_customize) {
        foreach ($this->custom_color_control_keys as $key => $data) {
            $wp_customize->add_setting($key, array(
                'sanitize_callback' => 'sanitize_hex_color',
                'type' => 'option',
                'transport' => 'postMessage'
            ));

            $wp_customize->add_control( new WP_Customize_Color_Control( 
                $wp_customize, 
                $key, 
                array(
                    'label'    => esc_html__( $data['title'], 'generatepress_child' ),
                    'section'  => 'logo_colors',
                    'settings' => $key,
                    'description' => __( $data['description'] ),
                ) 
            ) );

        }
    }

    

    /* Sanatizing is good and should be done? TODO sanitize image
    public function sanitize_checkbox( $input ) {
        return ( $input === true ) ? true : false;
    }
    */

}
