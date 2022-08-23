<?php

//nosegraze tutorial https://www.nosegraze.com/customizer-settings-wordpress-theme/

class Generatepress_Child_Customizer {
	
	public function __construct() {
        add_action( 'customize_register', array( $this, 'register_customize_sections' ) );

        add_action('customize_preview_init', array($this, 'customize_preview_js'));
        add_action('customize_controls_enqueue_scripts', array($this, 'customize_control_js'));

        // add_action( 'customize_preview_init', array( $this, 'my_preview_js') );
        // add_action( 'customize_controls_enqueue_scripts', array( $this, 'my_preview_js') );
        // add_action( 'customize_controls_enqueue_scripts', array( $this, 'my_link_js') );
    }
    //add all sections and panels to the Customizer
    public function register_customize_sections( $wp_customize ) {    
        // New panels
        $wp_customize->add_section( 'logo_colors', array(
            'title'    => __( 'Logo Colors (HUEnique)', 'generatepress_child' ),
            'priority' => 20
        ) );
        //Add settings to sections
        $this->logo_colors_section( $wp_customize );

    }

    public function customize_preview_js() {
        wp_enqueue_script(
            'wap_customize_preview_js', 
            get_stylesheet_directory_uri() . '/js/customizer-preview.js', 
            array('customize-preview', 'jquery'), 
            date("h:i:s"),
            true
        );
    }

    public function customize_control_js() {
        wp_enqueue_script(
            'wap_customize_controls_js', 
            get_stylesheet_directory_uri() . '/js/customizer-control.js', 
            array('customize-controls', 'jquery'), 
            date("h:i:s"),
            true
        );
    }

    public function my_preview_js() {
        //require(get_stylesheet_directory().'/js/color-thief.min.js');

        wp_enqueue_script( 'custom_css_preview', get_stylesheet_directory_uri().'/js/theme-customizer.js', array( 'customize-preview', 'jquery' ), date("h:i:s") );         
    }

    public function my_link_js() {
        wp_enqueue_script( 'custom_link_preview', get_stylesheet_directory_uri().'/js/link_test.js', array( 'customize-preview', 'jquery' ), date("h:i:s") );         

    }
    //TODO?: don't show up until logo processed
    //https://make.xwp.co/2016/07/24/dependently-contextual-customizer-controls/
    private function logo_colors_section( $wp_customize ) {
        $wp_customize->add_setting( 'logo', array(
            'transport' => 'postMessage'
        ) );
        $wp_customize->add_setting( 'custom_color1', array(
            /*'default'           => '#60ff21',*/
            'sanitize_callback' => 'sanitize_hex_color'
        ) );
        $wp_customize->add_setting( 'custom_color2', array(
            /*'default'           => '#ff2197',*/
            'sanitize_callback' => 'sanitize_hex_color'
        ) );
        $wp_customize->add_setting( 'custom_color1a', array(
            /*'default'           => '#60ff21',*/
            'sanitize_callback' => 'sanitize_hex_color'
        ) );
        $wp_customize->add_setting( 'custom_color2a', array(
            /*'default'           => '#ff2197',*/
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        //TODO postMessage would be faster
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
                    //'context'    => 'your_setting_context'
                )
            )
        );
        //$wp_customize->get_setting( 'logo' )->transport = 'postMessage';

        $wp_customize->add_control( new WP_Customize_Color_Control( 
            $wp_customize, 
            'color1', 
            array(
                'label'    => esc_html__( 'Color 1', 'generatepress_child' ),
                'section'  => 'logo_colors',
                'settings' => 'custom_color1',
                'description' => __( 'Your main theme color. This will be applied to the header, footer, and other major elements.' ),
                'priority' => 10
        
            )
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( 
            $wp_customize, 
            'color2', 
            array(
                'label'    => esc_html__( 'Color 2', 'generatepress_child' ),
                'section'  => 'logo_colors',
                'settings' => 'custom_color2',
                'description' => __( 'Your secondary theme color. This will be applied to buttons, accents, and other elements.' ),

                'priority' => 12
            )
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( 
            $wp_customize, 
            'color1a',
            array(
                'label'    => esc_html__( 'Color 1 Alternate', 'generatepress_child' ),
                'section'  => 'logo_colors',
                'settings' => 'custom_color1a',
                'description' => __( 'Alternate or hover color for elements that are color 1. This will be used occasionally.' ),
                'priority' => 11
            ) 
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( 
            $wp_customize, 
            'color2a', 
            array(
                'label'    => esc_html__( 'Color 2 Alternate', 'generatepress_child' ),
                'section'  => 'logo_colors',
                'settings' => 'custom_color2a',
                'description' => __( 'Alternate or hover color for elements that are color 1. This will be used occasionally.' ),
                'priority' => 13
            ) 
        ) );
       
        
    }

    

    /* Sanatizing is good and should be done? TODO sanitize image
    public function sanitize_checkbox( $input ) {
        return ( $input === true ) ? true : false;
    }
    */

}
