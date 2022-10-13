(function($) {

    let global_colors_accent_data = [
        { name : 'Accent', slug : 'accent' },
        { name : 'Accent 2', slug : 'accent-2' },
        { name : 'Accent 3', slug : 'accent-3' },
        { name : 'Accent 4', slug : 'accent-4' },
    ];

    // runs when logo upload is updated
    wp.customize('logo', (value) => {
        value.bind(function(to) {
            fetch(to)
                .then(resp => resp.blob())
                .then(blobobject => {
                    let image = parent.wp.customize.instance('logo').get();
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
                            rgbToHex(rgb[0], rgb[1], rgb[2]) 
                        );

                        // convert hex to hsl
                        let palette_hsl = palette_hex.map((hex) => 
                            hexToHSL(hex)
                        );

                        // find accent colors and convert to hex
                        let accent3 = find_accent_color(palette_hsl[0]);
                        let accent4 = find_accent_color(palette_hsl[1]);

                        accent3 = hslToHex(accent3);
                        accent4 = hslToHex(accent4);

                        // construct custom accent color palette
                        let palette = {
                            'accent' : palette_hex[0],
                            'accent-2' : palette_hex[1],
                            'accent-3' : accent3,
                            'accent-4' : accent4
                        };

                        // get current global colors
                        let global_colors = parent.wp.customize.instance('generate_settings[global_colors]').get();

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
                        updateGlobalColorControlCSS();

                        // send palette to rest route
                        sendCustomPalette(global_colors_accent_data)
                        .then((resp) => console.log(resp.json()))
                        .catch(() => console.log('Error: could not connect to WordPress.'))

                        parent.wp.customize('generate_settings[global_colors]', field => field.set(global_colors))

                        // set custom logo if flag is enabled
                        let logo_upload_flag = parent.wp.customize.instance('logo_toggle').get();
                        if (logo_upload_flag) {
                            parent.wp.customize('custom_logo', field => field.set(to));
                            // update logo with jquery
                            $('.header-image').attr('src', to);
                        }

                    }

                })
                .catch(() => 
                    console.log('There was an error downloading the image.')
                )

        })
    })

    /**
     * Resets color picker controls by sending an empty palette to the WP REST
     * API. 
     */
    function reset_color_pickers() {

        removeCustomColorPaletteCSS();

        // delete color from palette object
        global_colors_accent_data.forEach((global_color) => {
            delete global_color.color;
        })

        // send empty palette
        sendCustomPalette(global_colors_accent_data)
        .then((resp) => console.log(resp.json()))
        .catch(() => console.log('Error: could not connect to WordPress.'))

    }

    /**
     * Sends custom color palette to WordPress REST API route.
     * 
     * @param {array} palette 
     * @returns {Promise} 
     */
    const sendCustomPalette = async(palette) => {
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

    /**
     * Uses jQuery to visually remove the custom color palette button controls
     * from the customizer.
     */
    function removeCustomColorPaletteCSS() {
        global_colors_accent_data.forEach((global_color) => {
            // find palette button with corresponding slug
            let slug = global_color.slug;
            let palette_button = $('[aria-label="' + slug + '"]');

            // set accent color variables to transparent white
            palette_button.css({'color' : '#ffffff00'});

            document.documentElement.style.setProperty('--' + slug, '#ffffff00');
        });
    }

    /**
     * Uses CSS to update the color of the custom color palette buttons in real
     * time, when the image is uploaded.
     */
    function updateGlobalColorControlCSS() {
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
    const rgbToHex = (r, g, b) => '#' + [r, g, b].map(x => {
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
    function hexToHSL(H) {
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
    function hslToHex(hsl) {
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