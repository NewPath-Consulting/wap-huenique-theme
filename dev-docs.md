# WildApricot Press Huenique Theme Developer Guide

#### By Natalie Brotherton

##### *Updated December 2022*

The WAP Huenique Theme is a theme that works with WAP and is a [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/) of [GeneratePress](https://generatepress.com/). 

Its primary feature is that it allows users to upload an image in the theme customizer and generates a color palette based off that image. 

Since it is a WordPress theme, much of the theme's functionality is written in PHP. Javascript is used for interacting with the color palette library ColorThief.

## Theme files

### `functions.php`
`functions.php` is the main file required by WordPress for themes. 
In this file we simply queue the main stylesheet `style.css` and create an instance of the customizer object, which will control all the customizer functionality.

### `customizer.php`
This is where most of the theme functionality is implemented.

This file declares a class `Generatepress_Child_Customizer` which handles creating settings controls for uploading an image as well as two REST routes with which the Javascript can communicate with the backend theme files. 

The class also uses GeneratePress hooks to change its default colors.  

You can read more about the customizer functionality in the Customizer section.


### Javascript files
The Javascript file `js/customizer-preview.js` controls all the functionality of actually choosing a color palette. This file is enqueued to WordPress in `Generatepress_Child_Customizer->customize_preview_js`. 

Notice one of the dependencies declared when enqueuing this script is `customize-preview`. This is because the WordPress Customizer API is split into two parts: the customizer preview and the controls. The script uses the preview part since it is modifying the customizer preview by changing its colors. However, it can still access and change the customizer controls since the controls use the `postMessage` method, which allows the API to communicate between the preview and the controls, according to the API cdocumentation. Read more about the WordPress Customizer API [here](https://developer.wordpress.org/themes/customize-api/the-customizer-javascript-api/).

The script also uses a library called [Color Thief](https://lokeshdhakar.com/projects/color-thief/) to extract a color palette from the uploaded image. This library is included in full in the theme in `js/color-thief.min.js`.

## How the theme works

1. ### Customizer
    
    The first thing the `Generatepress_Child_Customizer` class does is configure the theme customizer.

    This means adding settings and controls for the following:
    * Image upload
    * Logo upload toggle

    The image upload is what enables the user to upload their image from which a color palette will be extracted. The logo upload toggle allows users to toggle the image appearing in the website's header.

    The class adds these settings and controls to the GeneratePress colors section.

    It also removes some of the unneeded GP default color controls in `Generatepress_Child_Customizer->remove_generatepress_options`.

    You can read more about WP Theme Customizer objects [here](https://developer.wordpress.org/themes/customize-api/customizer-objects/).


2. ### The color palette

    Once a user uploads an image with the customizer setting, the customizer script will find a palette with Color Thief. The initial palette it finds is only two colors. 

    This is because since the intended use of the image upload is to upload a **logo**, two colors would be enough to cover the logo color palette since they are generally relatively minimal.

    The script expands the color palette by finding similar accent colors. It finds accent colors by converting the inital palette colors to HSL (hue, saturation, lightness) color format and tweaking those values based on their initial values. For example, if a color is already very light, the algorithm will make its accent darker. 

    We will then end up with a palette of four colors.

3. ### REST routes

    After the palette is created, the script sends it to PHP via a custom REST route, the full relative URL of which is `/wp-json/wawp-theme/v1/custompalette/`. When the palette is received by the REST route, it is saved in the WP options table where its value will be read in the GP hooks.

    The REST route is also used when a user modifies one of the custom palette colors. The modified values are just sent to PHP with the route and saved in the options table.

    Like the palette, 

4. ### GeneratePress Hooks

5. ### Using the custom colors