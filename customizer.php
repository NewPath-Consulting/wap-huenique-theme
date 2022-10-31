<?php

use WAWP\Log as Log;

//nosegraze tutorial https://www.nosegraze.com/customizer-settings-wordpress-theme/

class Generatepress_Child_Customizer {

    const LOGO_IMAGE_UPLOAD = 'wap_theme_logo';
    const CUSTOM_COLOR_PALETTE = 'wap_theme_custom_color_palette';
    /**
     * Option name for the flag indicating whether the user is using the logo
     * uploaded in `Site Identity`.
     */
    const WP_SITE_ID_LOGO_FLAG = 'wap_theme_wp_site_id_logo';

	public function __construct() {
        // add customizer controls
        add_action( 
            'customize_register', 
            array( $this, 'register_customize_sections' ), 
            30 
        );

        // enqueue customizer scripts
        add_action(
            'customize_controls_enqueue_scripts', 
            array( $this, 'customize_preview_js' )
        );

        // add filters to customize the generatepress global colors
        add_filter( 
            'generate_option_defaults', 
            array( $this, 'customize_global_colors' ) 
        );
        add_filter( 
            'option_generate_settings', 
            array( $this, 'customize_global_colors' ) 
        );

        // register REST route to obtain custom color palette
        add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );

    }
    
    /**
     * Add all sections and panels to the customizer.
     *
     * @param WP_Customize_Manager $wp_customize customizer object
     * @return void
     */
    public function register_customize_sections( $wp_customize ) {    
       
        // add image upload control to genpress colors section
        $this->logo_colors_section( $wp_customize );

        // custom_logo is the WP logo upload ID
        $wp_customize->get_control( 'custom_logo' )->transport = 'postMessage';

        // remove genpress color palettes we don't need
        $this->remove_generatepress_options( $wp_customize );

    }

    /**
     * Enqueues customizer preview script.
     *
     * @return void
     */
    public function customize_preview_js() {
        // enqueue customizer preview script
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

    /**
     * Updates GeneratePress global colors option with the custom color palette.
     * 
     * @see https://docs.generatepress.com/article/option_generate_settings/
     *
     * @param array $settings
     * @return array updated settings
     */
    public function customize_global_colors($settings) {

        // get custom palette in options table
        $palette = get_option(self::CUSTOM_COLOR_PALETTE);

        // set defaults
        $default_color = '#ffffff00';
        $default_accent = array(
            'slug' => 'accent',
            'name' => __('Accent', 'generatepress'),
            'color' => $default_color
        );
        $default_palette = array();
        for ($accent_num = 2; $accent_num <= 4; $accent_num++) {
            $default_palette[] = array(
                'slug' => 'accent-' . $accent_num,
                'name' => __(sprintf('Accent %s', $accent_num), 'generatepress'),
                'color' => $default_color
            );
        }

        // if global colors don't exist, just add default or color palette
        if (is_null($settings['global_colors'])) {
            if ($palette) {
                // set globals as saved color palette
                $settings['global_colors'] = $palette;
            } else {
                // push single accent onto front of palette array, then set it as globals
                array_unshift($default_palette, $default_accent);
                $settings['global_colors'] = $default_palette;
            }
        } else {
            // if globals do already exist, add custom palette with defaults

            // if custom palette exists
            if ($palette) {
                // loop through custom palette
                foreach ($palette as $color) {
                    // see if color is already in global palette
                    $key = array_search(
                        $color['slug'], 
                        array_column($settings['global_colors'], 'slug')
                    );
                
                    if ($key) {
                        // if it is, change the color
                        $settings['global_colors'][$key]['color'] = $color['color'];
                    } else {
                        // if not, append the new color to the global palette
                        $settings['global_colors'][] = $color;
                    }
                }
            } else {
                // if custom palette does not exist, add defaults

                // find default accent and change it to default color
                $accent_key = array_search(
                    'accent', 
                    array_column($settings['global_colors'], 'slug')
                );

                // if accent exists, change it. if not, add it as default
                if ($accent_key) {
                    $settings['global_colors'][$accent_key]['color'] = $default_color;
                } else {
                    $settings['global_colors'][] = $default_accent;
                }
                
                // add other accent colors with default color
                $settings['global_colors'] = array_merge(
                    $settings['global_colors'],
                    $default_palette
                );
            }
        }   

        // set logo if upload flag is on
        $logo_upload_flag = get_option(self::LOGO_DISPLAY_FLAG);
        $wp_site_id_flag = get_option(self::WP_SITE_ID_LOGO_FLAG);

        // if site ID logo is used, skip custom logo display
        if ($wp_site_id_flag) {
            return $settings;
        }
    
        // if flag is on AND site id logo is not being used, update
        if ($logo_upload_flag) {
            $logo = get_option( 'wap_theme_logo' );
            $settings['logo'] = $logo;
        } else {
            $settings['logo'] = '';
        }

        // update current logo if it hasn't been updated yet
        $current_logo = get_option( 'site_logo' );
        if ($current_logo != $settings['logo']) {
            update_option( 'site_logo', $settings['logo'] );
        }

        return $settings;
   
    }

    /**
     * Register REST routes for obtaining and updating custom logo and color
     * data.
     *
     * @return void
     */
    public function register_rest_route() {
        register_rest_route( 'wawp-theme/v1', '/custompalette', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'update_custom_palette'),
            'permissions_callback' => '__return_true'
        ) );

        register_rest_route( 'wawp-theme/v1', '/customlogo', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'update_custom_logo'),
            'permissions_callback' => '__return_true'
        ) );
    }

    /**
     * Callback for custom color palette REST route. Recieves updated custom
     * color palette from color picking script.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_custom_palette($request) {

        $palette = json_decode($request->get_body(), 1);

        $update = update_option(self::CUSTOM_COLOR_PALETTE, $palette);
        
        $response = new WP_REST_Response($update, 200);

        // Set headers.
        $response->set_headers([ 
            'Cache-Control' => 'must-revalidate, no-cache, no-store, private' 
        ]);

        return $response;
    }

    /**
     * Callback for custom logo REST route. Called when custom logo is changed
     * through the default WP setting or logo display flag is changed.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_custom_logo($request) {
        // TODO: how to indicate user has set a different custom logo?
        // set flag
        // make separate update_logo function to use here and in GP filter

        $logo_flag = json_decode($request->get_body(), 1);

        $logo_flag_value = $logo_flag[self::LOGO_DISPLAY_FLAG];
        $wp_site_id_flag = $logo_flag[self::WP_SITE_ID_LOGO_FLAG];

        $update = array();

        $update[self::LOGO_DISPLAY_FLAG] = update_option(
            self::LOGO_DISPLAY_FLAG, 
            $logo_flag_value
        );
        $update[self::WP_SITE_ID_LOGO_FLAG] = update_option(
            self::WP_SITE_ID_LOGO_FLAG, 
            $wp_site_id_flag
        );

        // $update = update_option(self::LOGO_DISPLAY_FLAG, $logo_flag[self::LOGO_DISPLAY_FLAG]);

        $response = new WP_REST_Response($update, 200);

        $response->set_headers([
            'Cache-Control' => 'must-revalidate, no-cache, no-store, private' 
        ]);

        return $response;

    }

    /**
     * Adds settings and controls for the logo upload and color pickers.
     *
     * @param WP_Customize_Manager $wp_customize
     * @return void
     */
    private function logo_colors_section( $wp_customize ) {
        $wp_customize->add_setting( self::LOGO_IMAGE_UPLOAD, array(
            'transport' => 'postMessage',
            'type' => 'option'
        ) );

        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'logo_control',
                array(
                    'label'      => __( 'Upload your logo', 'generatepress_child' ),
                    'description' => __( 'The main colors from your logo will be extracted and used to set your theme colors. <br><br> The colors below will be automatically generated when you upload an image, then you can further modify them or specific elements as desired.<br><br>For best results, make sure your logo has a transparent background (not solid white) and at least two colors.' ),
                    'section'    => 'generate_colors_section',
                    'settings'   => self::LOGO_IMAGE_UPLOAD,
                    'priority' => 9,
                    'transport' => 'postMessage',
                )
            )
        );

        $wp_customize->add_setting( self::LOGO_UPLOAD_FLAG, array(
            'transport' => 'postMessage',
            'type' => 'option'
        ) );

        $wp_customize->add_control( self::LOGO_UPLOAD_FLAG, array(
            'label'     => _( 'Display logo in website header' ),
            'type'      => 'checkbox',
            'section'   => 'generate_colors_section',
            'priority'  => 9
        ) );

    }

    /**
     * Removes color controls added by GeneratePress.
     *
     * @param WP_Customize_Manager $wp_customize
     * @return void
     */
    private function remove_generatepress_options( $wp_customize ) {
        // body section
        $wp_customize->remove_control( 'generate_body_colors_title' );
        $wp_customize->remove_setting( 'generate_settings[background_color]' );
        $wp_customize->remove_control( 'generate_settings[background_color]' );
        $wp_customize->remove_setting( 'generate_settings[text_color]' );
        $wp_customize->remove_control( 'generate_settings[text_color]' );
        $wp_customize->remove_control( 'generate_body_link_wrapper' );
        $wp_customize->remove_setting( 'generate_settings[link_color]' );
        $wp_customize->remove_control( 'generate_settings[link_color]' );
        $wp_customize->remove_setting( 'generate_settings[link_color_hover]' );
        $wp_customize->remove_control( 'generate_settings[link_color_hover]' );
        $wp_customize->remove_setting( 'generate_settings[link_color_visited]' );
        $wp_customize->remove_control( 'generate_settings[link_color_visited]' );

        // header section
        $wp_customize->remove_control( 'generate_header_colors_title' );
        $wp_customize->remove_setting( 'generate_settings[header_background_color]' );
        $wp_customize->remove_control( 'generate_settings[header_background_color]' );
        $wp_customize->remove_setting( 'generate_settings[header_text_color]' );
        $wp_customize->remove_control( 'generate_settings[header_text_color]' );
        $wp_customize->remove_control( 'generate_header_link_wrapper' );
        $wp_customize->remove_setting( 'generate_settings[header_link_color]' );
        $wp_customize->remove_control( 'generate_settings[header_link_color]' );
        $wp_customize->remove_setting( 'generate_settings[header_link_hover_color]' );
        $wp_customize->remove_control( 'generate_settings[header_link_hover_color]' );
        $wp_customize->remove_setting( 'generate_settings[site_title_color]' );
        $wp_customize->remove_control( 'generate_settings[site_title_color]' );
        $wp_customize->remove_setting( 'generate_settings[site_tagline_color]' );
        $wp_customize->remove_control( 'generate_settings[site_tagline_color]' );
        
        // buttons section
        $wp_customize->remove_control( 'generate_buttons_colors_title' );
        $wp_customize->remove_control( 'generate_buttons_background_wrapper' );
        $wp_customize->remove_setting( 'generate_settings[form_button_background_color]' );
        $wp_customize->remove_control( 'generate_settings[form_button_background_color]' );
        $wp_customize->remove_setting( 'generate_settings[form_button_background_color_hover]' );
        $wp_customize->remove_control( 'generate_settings[form_button_background_color_hover]' );
        $wp_customize->remove_control( 'generate_buttons_text_wrapper' );
        $wp_customize->remove_setting( 'generate_settings[form_button_text_color]' );
        $wp_customize->remove_control( 'generate_settings[form_button_text_color]' );
        $wp_customize->remove_setting( 'generate_settings[form_button_text_color_hover]' );
        $wp_customize->remove_control( 'generate_settings[form_button_text_color_hover]' );

        // forms section
        $wp_customize->remove_control( 'generate_forms_colors_title' );
        $wp_customize->remove_control( 'generate_forms_background_wrapper' );
        $wp_customize->remove_setting( 'generate_settings[form_background_color]' );
        $wp_customize->remove_control( 'generate_settings[form_background_color]' );
        $wp_customize->remove_setting( 'generate_settings[form_background_color_focus]' );
        $wp_customize->remove_control( 'generate_settings[form_background_color_focus]' );
        $wp_customize->remove_control( 'generate_forms_text_wrapper' );
        $wp_customize->remove_setting( 'generate_settings[form_text_color]' );
        $wp_customize->remove_control( 'generate_settings[form_text_color]' );
        $wp_customize->remove_setting( 'generate_settings[form_text_color_focus]' );
        $wp_customize->remove_control( 'generate_settings[form_text_color_focus]' );
        $wp_customize->remove_control( 'generate_forms_border_wrapper' );
        $wp_customize->remove_setting( 'generate_settings[form_border_color]' );
        $wp_customize->remove_control( 'generate_settings[form_border_color]' );
        $wp_customize->remove_setting( 'generate_settings[form_border_color_focus]' );
        $wp_customize->remove_control( 'generate_settings[form_border_color_focus]' );

    }  

    /* Sanatizing is good and should be done? TODO sanitize image
    public function sanitize_checkbox( $input ) {
        return ( $input === true ) ? true : false;
    }
    */

}
