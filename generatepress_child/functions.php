<?php
/*// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

// END ENQUEUE PARENT ACTION
//Hide admin bar for all users except administrators
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
if (!current_user_can('administrator') && !is_admin()) {
       show_admin_bar(false);
       }
  } */
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */

/**
 * Include Customizer settings.
 */


require(get_stylesheet_directory() . '/customizer/class-generatepress-child-customizer.php');
add_action( 'wp_enqueue_scripts', function () { wp_enqueue_script( 'jquery' ); } );

function colorthief_scripts() {
    wp_register_script( 'color_thief', 
    get_stylesheet_directory_uri() . '/js/color-thief.min.js',
                       array(),
                       'scriptversion 1.5.8.1', 
                       true);
  
//enque scripts
    wp_enqueue_script('color_thief');

 
 
}


add_action( 'wp_enqueue_scripts', 'colorthief_scripts' );

//make customizer menu
new Generatepress_Child_Customizer();



/*---------------Do things with customizer--------------- */
//Generate CSS based on the Customizer settings.
function generatepress_child_customizer_css() {

	$css = '';

	$color1 = get_theme_mod( 'custom_color1', '#dddddd' ); //TODO, make default normal
    $color2 = get_theme_mod( 'custom_color2', '#4fe3d9' );
    //hardcoded for demo
	$css .= ' .main-navigation { background-color: ' . $color1 . '; }';
    $css .= ' .gb-button-wrapper a.gb-button-7ddd125e, .gb-button-wrapper a.gb-button-7ddd125e:visited { background-color: ' . $color2 . ' !important;}';
    $css .= ' .gb-button-wrapper a.gb-button-61aecbd3, .gb-button-wrapper a.gb-button-61aecbd3:visited { color: ' . $color2 . ' !important;}';
    $css .= ' .gb-container-352e6923 { background-image: linear-gradient(135deg, '.$color1.' 20%, '.$color2.' 80%) !important;';

	return $css;
}

function huenique_custom_palettes($palettes) {
    $color1 = get_theme_mod( 'custom_color1', '#c96e40' );
    $color2 = get_theme_mod( 'custom_color2', '#4fe3d9' );
    $palettes = array(
        '#000000',
        '#FFFFFF',
        '#F1C40F',
        '#666A86',
        '#000000',
        '#000000',
        $color1,
        $color2,
    );
    return $palettes;
}


/*---------------/Do things with customizer--------------- */

//when image is loaded,
//selector:
//#__wp-uploader-id-0 > div.media-frame-toolbar > div > div.media-toolbar-primary.search-form > button
/* JQuery  AJAX post
$.ajax({ 
	 method: "POST", 
	 url: "name-of-php-file.php", 
	 data: { data1: value1, data2: value2} 
					}).done(function(html){						 //function block runs if Ajax request was successful 
					}).fail(function(html){ 
	// function block runs if Ajax request failed 
}); 
*/
/*
add_action( 'init', 'color_to_php' );

function color_to_php() {
   wp_register_script( "my_voter_script", WP_PLUGIN_URL.'/my_plugin/my_voter_script.js', array('jquery') );
   wp_localize_script( 'my_voter_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        

   wp_enqueue_script( 'jquery' );
   wp_enqueue_script( 'my_voter_script' );

}

//cookie instead

$color_picket_target = get_theme_mod('logo');
//run color thief on image
if(isset($_POST['data1']) && isset($_POST['data2'])){ 
	//set_theme_mod('custom_color1', color);
    //set_theme_mod('custom_color2', color);
} 
*/




/*---------------Color Thief--------------- */
function colorthief_shortcode($atts, $content=null) {
    $a = shortcode_atts( array(
         'url' => '',
         'width' => '100%',
         'height' => '20px',
    ), $atts);
    $unique = substr($a['url'],-8,-4);
    wp_register_script('color-picker', plugins_url('js/color-picker.js', __FILE__), array('jquery'), true); 
    //$unique = rand(4,12);
	return '
    <img class="cthief" id="theimage'.$unique.'" src="'. $a['url'] .'" />
<div>
	<div>
		<input type="color" id="first" name="first"
			   value="#000000">
		<label for="first">Primary Theme Color</label>
	</div>
	<div>
		<input type="color" id="second" name="second"
			   value="#000000">
		<label for="second">Secondary Theme Color</label>
	</div>
	<div>
		<input type="color" id="third" name="third"
			   value="#000000">
		<label for="third">Tertiary Theme Color</label>
	</div>
	<div>
		<input type="color" id="fourth" name="fourth"
			   value="#000000">
		<label for="fourth">Quaternary Theme Color</label>
	</div>
</div>
<br>
<button type="button" id="button">Save Colors</button>

  <script type="text/javascript src="js/color-picker.js"></script>';
}
add_shortcode( 'colorthief', 'colorthief_shortcode' );

function huenique_logo_shortcode() {
    return '<style>
    .rainbow-text {
        font-family: Arial;
        font-weight: bold;
        font-size: 50px;
      background-color: #212121;
    
    }
    .rainbow-text .block-line > span {
        display: inline-block;
    }
    .block-line {
        background-image: linear-gradient(145deg,red,red,orange,yellow,#BFFF00,cyan,#6753FF,#5E00FF,purple);
        color: #0000;
        -webkit-background-clip: text;
        background-clip: text;
    }
    .normal-text {
        text-align: bold;
    font-weight: normal;
    font-size: 44px;
    color: #eeebf0;
    }
    
    </style>
    <div class="rainbow-text" style="text-align: center;">
        <span class="block-line">HUE</span><span class="normal-text">nique</span>
    </div>
    <br>';

}
add_shortcode( 'huenique_logo', 'huenique_logo_shortcode' );


/*---------------/color thief--------------- */


add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_style( 'generate-child' );
    wp_enqueue_style( 'generate-child' );
    wp_add_inline_style( 'generate-child', generatepress_child_customizer_css() );

}, 999 );
add_filter( 'generate_default_color_palettes', 'huenique_custom_palettes' );






