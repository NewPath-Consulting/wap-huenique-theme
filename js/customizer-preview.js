(function($) {

    // keys corresponding to the color picker controls
    let color_picker_keys = [
        'custom_color1', 
        'custom_color2', 
        'custom_color1a', 
        'custom_color2a'
    ];

    let default_value = '#fff';

    // runs when logo upload is updated
    wp.customize('logo', (value) => {
        value.bind(function(to) {
            fetch(to)
                .then(resp => resp.blob())
                .then(blobobject => {
                    // if blob is not an image, ignore it
                    if (!blobobject.type.includes('image')) {
                        reset_color_pickers();
                        return;
                    }

                    var img = new Image();
                    img.src = to;

                    // wait for image to load, then get palette
                    img.onload = () => {
                        let color_thief = new ColorThief();
                        let palette = color_thief.getPalette(img, 2);

                        // convert rgb to hex
                        let palette_hex = palette.map((rgb) =>
                            rgbToHex(rgb[0], rgb[1], rgb[2]) 
                        );

                        // convert hex to hsl
                        let palette_hsl = palette_hex.map((hex) => 
                            hexToHSL(hex)
                        );

                        // find accent colors
                        palette_hsl = find_accent_colors(palette_hsl);

                        // convert hsl palette to hex
                        palette_hex = palette_hsl.map((color) => hslToHex(color));

                        // set color picker controls to palette hex values
                        color_picker_keys.forEach((key, idx) => {
                            parent.wp.customize(key, 
                                field => field.set(palette_hex[idx])
                            )
                        });

                        // parent.wp.customize('custom_logo', field => field.set(to));

                    }

                })
                .catch(() => 
                    console.log('There was an error downloading the image.')
                )

        })
    })

    /**
     * Resets color picker controls to white (default color)
     */
    function reset_color_pickers() {
        // set color picker controls to palette hex values
        color_picker_keys.forEach((key) => {
            parent.wp.customize(key, 
                field => field.set(default_value)
            )
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
     * @returns 
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


    function find_accent_colors(palette) {
        let primary = palette[0];
        let secondary = palette[1];

        palette.push(find_accent_color(primary));
        palette.push(find_accent_color(secondary));

        return palette;
    }

    function find_accent_color(color) {
        let accent = color;

        // if color is saturated enough, adjust the accent hue
        if (color['s'] >= 40) {
            accent['h'] += 10;

            if (color['l'] > 70) {
                accent['l'] -= 10;
            } else if (color['l'] < 30) {
                accent['l'] += 10;
            }

            return accent;
        }

        // lighten or darken
        if (color['l'] > 50) {
            accent['l'] -= 10;
        } else {
            accent['l'] += 10
        }

        return accent;
    }

    /**
     * 
     * @param {array} palette 
     * @param {*} primary_accent 
     */
    function find_secondary_accent_color(palette, primary_accent) {
        // find second most saturated color in the palette
        // if colors are too far apart, just slightly adjust the primary accent by 
        // hue and lightness

        let secondary_accent = {'h': 0, 's': 0, 'l': 0};

        palette.forEach((color) => {
            if (color['s'] > secondary_accent['s'] && color != primary_accent) {
                secondary_accent = color;
            }
        });

        // adjust primary to get secondary if hues are too far apart
        // if primary is light, make secondary dark
        // if primary is dark, make secondary light

        if (Math.abs(secondary_accent['h'] - primary_accent['h']) > 60) {
            secondary_accent = {
                'h': primary_accent['h'] + 10,
                's': primary_accent['s'],
                'l': primary_accent['l']
            };
        }

        return secondary_accent;

    }

}) (jQuery);