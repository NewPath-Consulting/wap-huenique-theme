<?php
require_once __DIR__ . '/customizer.php';
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */

add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );
function enqueue_parent_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

add_action('after_setup_theme', 'wap_add_theme_support');
function wap_add_theme_support() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'automatic-feed-links' ); 
}

$customizer = new Generatepress_Child_Customizer();