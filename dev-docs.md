# WildApricot Press Huenique Theme Developer Guide

#### By Natalie Brotherton

##### *Updated December 2022*

The WAP Huenique Theme is a theme that works with WAP and is a [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/) of [GeneratePress](https://generatepress.com/). Thus, GeneratePress Premium must also be installed [^1].

Its primary feature is that it allows users to upload an image in the theme customizer and generates a color palette based off that image. 

Since it is a WordPress theme, much of the theme's functionality is written in PHP. Javascript is used for interacting with the color palette library ColorThief.

[^1]: Contact Alex to obtain a license for GP Premium.

## Theme files

### `functions.php`
`functions.php` is the main file required by WordPress for themes. 
In this file we simply queue the main stylesheet `style.css` and create an instance of the customizer object, which will control all the customizer functionality.

### `customizer.php`
This is where most of the theme functionality is implemented.

This file declares a class `Generatepress_Child_Customizer` which handles creating settings controls for uploading an image as well as two REST routes with which the Javascript can communicate with the backend theme files. 

The class also uses GeneratePress hooks to change its default colors.  

You can read more about the customizer functionality in the Customizer section.

### `style.css`
`style.css` is the main stylesheet of the plugin. It contains all the header information for themes required by WordPress.

It is currently empty since Huenique does not need to change styles of the plugin to function.


### Javascript files
The Javascript file `js/customizer-preview.js` controls all the functionality of actually choosing a color palette. This file is enqueued to WordPress in `Generatepress_Child_Customizer->customize_preview_js`. 

Notice one of the dependencies declared when enqueuing this script is `customize-preview`. This is because the WordPress Customizer API is split into two parts: the customizer preview and the controls. The script uses the preview part since it is modifying the customizer preview by changing its colors. However, it can still access and change the customizer controls since the controls use the `postMessage` method, which allows the API to communicate between the preview and the controls, according to the API cdocumentation. Read more about the WordPress Customizer API [here](https://developer.wordpress.org/themes/customize-api/the-customizer-javascript-api/).

The script also uses a library called [Color Thief](https://lokeshdhakar.com/projects/color-thief/) to extract a color palette from the uploaded image. This library is included in full in the theme in `js/color-thief.min.js`.

## How the theme works

### 1. Theme customizer
    
The first thing the `Generatepress_Child_Customizer` class does is configure the theme customizer.

This means adding settings and controls for the following:
* Image upload
* Logo upload toggle

The image upload is what enables the user to upload their image from which a color palette will be extracted. The logo upload toggle allows users to toggle the image appearing in the website's header.

The class adds these settings and controls to the GeneratePress colors section.

It also removes some of the unneeded GP default color controls in `Generatepress_Child_Customizer->remove_generatepress_options`.

You can read more about WP Theme Customizer objects [here](https://developer.wordpress.org/themes/customize-api/customizer-objects/).


### 2. The color palette

Once a user uploads an image with the customizer setting, the customizer script will find a palette with Color Thief. The initial palette it finds is only two colors. 

This is because since the intended use of the image upload is to upload a **logo**, two colors would be enough to cover the logo color palette since they are generally relatively minimal.

The script expands the color palette by finding similar accent colors. It finds accent colors by converting the inital palette colors to HSL (hue, saturation, lightness) color format and tweaking those values based on their initial values. For example, if a color is already very light, the algorithm will make its accent darker. 

We will then end up with a palette of four colors.

### 3. REST routes

After the palette is created, the script sends it to PHP via a custom REST route, the full relative URL of which is `/wp-json/wawp-theme/v1/custompalette/`. When the palette is received by the REST route, it is saved in the WP options table where its value will be read in the GP hooks.

The REST route is also used when a user modifies one of the custom palette colors. The modified values are just sent to PHP with the route and saved in the options table.

Like the palette, the logo upload option also communicates with PHP vis REST routes when it is updated.

Since the GP global colors are simply CSS variables, the script will also update the CSS variable values to the new color palette.

### 4. GeneratePress Hooks
This theme operates on the GeneratePress [global colors](https://docs.generatepress.com/article/global-colors-overview/). This color palette is available when editing any GP element in the page/post editor. 

In order for the theme to directly modify the global color palette with the custom colors, it needs to use the GP filters that configure the theme settings. 

One of these filters is [`option_generate_settings`](https://docs.generatepress.com/article/option_generate_settings/). It sets the options for the theme. The other filter is `generate_option_defaults`, which controls setting default options. Both filters are used to ensure the custom color values are saved to GP. Read more about the filters [here](https://wordpress.org/support/topic/override-defaults-php/#post-13756786).

### 5. Using the custom colors
As described below, the global color palette is displayed in the post editor. You can apply the colors by selecting a GP block in the editor, navigating to the "colors" option in the block settings, and choosing one of the custom palette colors. 

## How to use (from a developer's POV)
### 1. Installation
You can install this theme simply by directly cloning it in your theme directory (`wp-content/themes`) or installing a compressed archive of it via the WP theme install UI. 

The theme folder **must** be named `generatepress_child` so WP can detect it is a child theme for GeneratePress.
 
When activating this theme, make sure GP Premium is also installed and active. 
It is also recommended to install a theme from the GP library for an improved website UI.

### 2. Upload a logo
Navigate to the customizer via Appearance > Customizer on the admin settings. All the theme's settings are located in the "Colors" section.

For the most optimal palette, it is recommended that logos uploaded to the upload control have a transparent background rather than a solid white one and at least two colors in the logo.

You can upload different logos and watch the colors change in real time. Each time you upload one, the Javascript will send a request to the REST route with the new color information. 

### 3. Apply the colors
After installing GP for the first time, you will need to apply the custom colors to the GP blocks. This should only need to be done once when installing GP.

Navigate to any page in the menu links (since those will be the most accessible to/viewed by users) and enter the post editor.

As described in the previous section, you can select any color setting in the block editor ("Colors" or "Background Gradient" if enabled).

Every time the global colors change when a new logo is uploaded, the colors in the website UI will change (if they were set to use the GP global colors).