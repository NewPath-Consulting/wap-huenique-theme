<?php

//nosegraze tutorial https://www.nosegraze.com/customizer-settings-wordpress-theme/

class Generatepress_Child_Customizer {

    private $custom_color_control_keys = array(
        'custom_color1' => array(
            'title' => 'Color 1',
            'description' => 'Your main theme color. This will be applied to the header, footer, and other major elements.'
        ),
        'custom_color2' => array(
            'title' => 'Color 1',
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
        add_action('customize_controls_enqueue_scripts', array($this, 'customize_control_js'));

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
    }

    /**
     * Enqueues customizer control script.
     *
     * @return void
     */
    public function customize_control_js() {
        wp_enqueue_script(
            'wap_customize_controls_js', 
            get_stylesheet_directory_uri() . '/js/customizer-control.js', 
            array('customize-controls', 'jquery'), 
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
            'transport' => 'postMessage'
        ) );

        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'logo_control',
                array(
                    'label'      => __( 'Upload your logo', 'generatepress_child' ),
                    'description' => __( 'The main colors from your logo will be extracted and used to set your theme colors. <br><br> The colors below will be automatically generated when you upload an image, then you can further modify them or specific elements as desired' ),
                    'section'    => 'logo_colors',
                    'settings'   => 'logo',
                    'priority' => 9,
                    'transport' => 'postMessage',
                )
            )
        );

        $this->render_color_picker_controls($wp_customize);
        
    }

    private function render_color_picker_controls($wp_customize) {
        foreach ($this->custom_color_control_keys as $key => $data) {
            $wp_customize->add_setting($key, array(
                'sanitize_callback' => 'sanitize_hex_color',
                'transport' => 'postMessage'
            ));

            $wp_customize->add_control( new WP_Customize_Color_Control( 
                $wp_customize, 
                'color2a', 
                array(
                    'label'    => esc_html__( $data['title'], 'generatepress_child' ),
                    'section'  => 'logo_colors',
                    'settings' => $key,
                    'description' => __( $data['description'] ),
                    'priority' => 13
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
