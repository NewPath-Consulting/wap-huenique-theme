(function($) {

    // set transparent white as default color
    let default_color = '#ffffff00';

    // template data for accent colors
    let global_colors_accent_data = [
        { name : 'Accent', slug : 'accent', color : default_color },
        { name : 'Accent 2', slug : 'accent-2', color : default_color },
        { name : 'Accent 3', slug : 'accent-3', color : default_color },
        { name : 'Accent 4', slug : 'accent-4', color : default_color },
    ];

    const LOGO_DISPLAY_FLAG = 'wap_theme_logo_toggle';
    const WP_SITE_ID_LOGO_FLAG = 'wap_theme_wp_site_id_logo';

    let custom_logo_data = {
        'wap_theme_logo_toggle' : false,
        'wap_theme_wp_site_id_logo' : false
    };

    // runs when logo upload is updated
    wp.customize('wap_theme_logo', (value) => {
        value.bind(function(to) {
            // fetch new logo value and obtain the blob object
            fetch(to)
            .then(resp => resp.blob())
            .then(blobobject => {
                let image = parent.wp.customize.instance('wap_theme_logo').get();
                // if blob is not an image, ignore it
                if (!blobobject.type.includes('image') || image.length == 0) {
                    reset_color_pickers();
                    return;
                }

                var img = new Image();
                img.src = to;

                // wait for image to load, then get palette
                img.onload = () => {
                    let color_thief = new ColorThief();
                    let image_palette = color_thief.getPalette(img, 2);

                    // convert rgb to hex
                    let palette_hex = image_palette.map((rgb) =>
                        rgb_to_hex(rgb[0], rgb[1], rgb[2]) 
                    );

                    // convert hex to hsl
                    let palette_hsl = palette_hex.map((hex) => 
                        hex_to_hsl(hex)
                    );

                    // find accent colors and convert to hex
                    let accent3 = find_accent_color(palette_hsl[0]);
                    let accent4 = find_accent_color(palette_hsl[1]);
                    accent3 = hsl_to_hex(accent3);
                    accent4 = hsl_to_hex(accent4);

                    // construct custom accent color palette
                    let palette = {
                        'accent' : palette_hex[0],
                        'accent-2' : palette_hex[1],
                        'accent-3' : accent3,
                        'accent-4' : accent4
                    };

                    // get current global colors
                    let global_colors = parent.wp.customize.instance(
                        'generate_settings[global_colors]'
                    ).get();

                    // loop through the accent color data template
                    global_colors_accent_data.forEach((global_color) => {
                        // add color value to new global color object
                        global_color.color = palette[global_color.slug];

                        // look for accent color slug in saved global colors
                        global_colors.find((color, i) => {

                            // if accent value already exists, replace it
                            if (color.slug === global_color.slug) {
                                global_colors[i].color = global_color.color;
                                return true;
                            } 
                        });
                    });

                    // use css to change color of palettes before they are saved
                    update_global_palette_button_css();

                    // send palette to rest route
                    send_custom_palette(global_colors_accent_data)
                    .then((resp) => {
                        parent.wp.customize.previewer.refresh()
                    })
                    .catch((e) => console.error(e))

                }
            })
            .catch((e) => console.error(e))
        })
    })

    // triggered when user changes global color palette
    wp.customize('generate_settings[global_colors]', (value) => {

        value.bind((to) => {
            // send updated custom palette to rest route, then update previewer
            send_custom_palette(to)
            .then((resp) => {
                parent.wp.customize.previewer.refresh()
            })
            .catch((e) => console.error(e))
        })
    })

    // triggered when custom logo is updated through the customizer panel
    wp.customize('custom_logo', (value) => {

        value.bind((to) => {
            if (to) {
                // if image exists, enable site id logo flag, disable custom logo flag
                custom_logo_data[WP_SITE_ID_LOGO_FLAG] = true;
                custom_logo_data[LOGO_DISPLAY_FLAG] = false;

                // uncheck custom logo toggle
                parent.wp.customize(LOGO_DISPLAY_FLAG, field => field.set(false))

                send_custom_logo_data(custom_logo_data)
                .then((resp) => {
                    parent.wp.customize.previewer.refresh()
                })
                .catch((e) => console.error(e))
            }
        })

    })

    // triggered when custom logo checkbox is updated
    wp.customize(LOGO_DISPLAY_FLAG, (value) => {

        value.bind((to) => {
            // update new flag value, disable site id flag
            custom_logo_data[LOGO_DISPLAY_FLAG] = to;
            custom_logo_data[WP_SITE_ID_LOGO_FLAG] = false;
            send_custom_logo_data(custom_logo_data)
            .then((resp) => {
                parent.wp.customize.previewer.refresh()
            })
            .catch((e) => console.error(e))
        })

    })

    /**
     * Resets color picker controls by sending an empty palette to the WP REST
     * API. 
     */
    function reset_color_pickers() {

        remove_global_palette_button_css();

        // delete color from palette object
        global_colors_accent_data.forEach((global_color) => {
            global_color.color = default_color;
        })

        // send empty palette
        send_custom_palette(global_colors_accent_data)
        .then((resp) => {
            parent.wp.customize.previewer.refresh()
        })
        .catch(() => console.log('Error: could not connect to WordPress.'))

    }

    /**
     * Sends custom color palette to WordPress REST API route.
     * 
     * @param {array} palette 
     * @returns {Promise} 
     */
    const send_custom_palette = async(palette) => {
        const API_URL = '/wp-json/wawp-theme/v1/custompalette';

        const resp = await fetch(API_URL, {
            method : 'POST', 
            headers : {
                'Accept' : 'application/json',
                'Content-Type' : 'application/json'
            },
            body : JSON.stringify(palette)
        });

        return resp;
    }

    const send_custom_logo_data = async(logo_data) => {
        const API_URL = '/wp-json/wawp-theme/v1/customlogo';

        const resp = await fetch(API_URL, {
            method : 'POST',
            headers : {
                'Accept' : 'application/json',
                'Content-Type' : 'application/json'
            },
            body : JSON.stringify(logo_data) 
        });

        return resp;
    }

    /**
     * Uses jQuery to visually remove the custom color palette button controls
     * from the customizer.
     */
    function remove_global_palette_button_css() {
        global_colors_accent_data.forEach((global_color) => {
            // find palette button with corresponding slug
            let slug = global_color.slug;
            let palette_button = $('[aria-label="' + slug + '"]');

            // set accent color variables to transparent white
            palette_button.css({'color' : default_color});

            document.documentElement.style.setProperty('--' + slug, default_color);
        });
    }

    /**
     * Uses CSS to update the color of the custom color palette buttons in real
     * time, when the image is uploaded.
     */
    function update_global_palette_button_css() {
        // change customizer palette colors
        global_colors_accent_data.forEach((global_color) => {
            // find palette button with corresponding slug
            let slug = global_color.slug;
            let palette_button = $('[aria-label="' + slug + '"]');

            // change CSS variable and palette button color
            document.documentElement.style.setProperty('--' + slug, global_color.color);
            palette_button.css({'color' : global_color.color});
        });
    }

    /**
     * Converts RGB color to hex color.
     * 
     * @param {int} r 
     * @param {int} g 
     * @param {int} b 
     * @returns {string} hex color value
     */
    const rgb_to_hex = (r, g, b) => '#' + [r, g, b].map(x => {
        const hex = x.toString(16)
        return hex.length === 1 ? '0' + hex : hex
    }).join('')

    /**
     * Converts hex color value `H` to HSL (hue, saturation, lightness) color
     * space format.
     * 
     * @param {string} H hex value 
     * @returns {array} array of HSL values in the format [h, s, l]
     */
    function hex_to_hsl(H) {
        // Convert hex to RGB first
        let r = 0, g = 0, b = 0;
        if (H.length == 4) {
          r = "0x" + H[1] + H[1];
          g = "0x" + H[2] + H[2];
          b = "0x" + H[3] + H[3];
        } else if (H.length == 7) {
          r = "0x" + H[1] + H[2];
          g = "0x" + H[3] + H[4];
          b = "0x" + H[5] + H[6];
        }
        // Then to HSL
        r /= 255;
        g /= 255;
        b /= 255;
        let cmin = Math.min(r,g,b),
            cmax = Math.max(r,g,b),
            delta = cmax - cmin,
            h = 0,
            s = 0,
            l = 0;
      
        if (delta == 0)
          h = 0;
        else if (cmax == r)
          h = ((g - b) / delta) % 6;
        else if (cmax == g)
          h = (b - r) / delta + 2;
        else
          h = (r - g) / delta + 4;
      
        h = Math.round(h * 60);
      
        if (h < 0)
          h += 360;
      
        l = (cmax + cmin) / 2;
        s = delta == 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));
        s = +(s * 100).toFixed(1);
        l = +(l * 100).toFixed(1);

        return {
            'h': h,
            's': s,
            'l': l
        };

    }

    /**
     * Converts HSL to hex color value.
     * 
     * @param {int} h 
     * @param {int} s 
     * @param {int} l 
     * @returns {string} hex color value
     */
    function hsl_to_hex(hsl) {
        let h = hsl['h'];
        let s = hsl['s'];
        let l = hsl['l'];
        l /= 100;
        const a = s * Math.min(l, 1 - l) / 100;
        const f = n => {
          const k = (n + h / 30) % 12;
          const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
          return Math.round(255 * color).toString(16).padStart(2, '0');   // convert to Hex and prefix "0" if needed
        };
        return `#${f(0)}${f(8)}${f(4)}`;
    }

    /**
     * Finds an accent color based on `color`. Adjusts hue and luminosity of the
     * original color to find a suitable accent color.
     * 
     * @param {array} color in HSL
     * @returns {array} new accent color in HSL
     */
    function find_accent_color(color) {
        let accent = color;

        // if color is saturated enough, adjust the accent hue
        if (color['s'] >= 40) {
            // adjust hue
            accent['h'] += 15;

            // lighten or darken based on base color
            if (color['l'] > 70) {
                accent['l'] -= 20;
            } else if (color['l'] < 30) {
                accent['l'] += 20;
            } else {
                accent['l'] += 10;
            }

        } else {
            // lighten or darken
            if (color['l'] > 50) {
                accent['l'] -= 25;
            } else {
                accent['l'] += 25;
            }
        }

        // mod if new h or l is greater than maximum
        accent['h'] %= 360;
        accent['l'] %= 100;

        return accent;
    }

}) (jQuery);