<?php

//nosegraze tutorial https://www.nosegraze.com/customizer-settings-wordpress-theme/

class Generatepress_Child_Customizer {
	
	public function __construct() {
        add_action( 'customize_register', array( $this, 'register_customize_sections' ) );
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

    private function logo_colors_section( $wp_customize ) {
        $wp_customize->add_setting( 'logo', array(
            /*'default'           => '#444444',
            'sanitize_callback' => 'sanitize_hex_color'*/
        ) );
        $wp_customize->add_setting( 'custom_color1', array(
            /*'default'           => '#60ff21',*/
            'sanitize_callback' => 'sanitize_hex_color'
        ) );
        $wp_customize->add_setting( 'custom_color2', array(
            /*'default'           => '#ff2197',*/
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        //TODO give the people some instructions
        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'logo',
                array(
                    'label'      => __( 'Upload your logo', 'generatepress_child' ),
                    'section'    => 'logo_colors',
                    'settings'   => 'logo',
                    'priority' => 9
                    //'context'    => 'your_setting_context'
                )
            )
        );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'color1', array(
            'label'    => esc_html__( 'Color 1', 'generatepress_child' ),
            'section'  => 'logo_colors',
            'settings' => 'custom_color1',
            'priority' => 10
        ) ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'color2', array(
            'label'    => esc_html__( 'Color 2', 'generatepress_child' ),
            'section'  => 'logo_colors',
            'settings' => 'custom_color2',
            'priority' => 11
        ) ) );
       
        
        
        
    }

    /* Sanatizing is good and should be done? TODO sanitize image
    public function sanitize_checkbox( $input ) {
        return ( $input === true ) ? true : false;
    }
    */

}
