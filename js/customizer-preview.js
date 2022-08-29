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
                    if (blobobject.type != 'image/png') {
                        reset_color_pickers();
                        return;
                    }

                    var img = new Image();
                    img.src = to;

                    // wait for image to load, then get palette
                    img.onload = () => {
                        let color_thief = new ColorThief();
                        let palette = color_thief.getPalette(img, 4);

                        // convert rgb to hex
                        let palette_hex = palette.map((rgb) =>
                            rgbToHex(rgb[0], rgb[1], rgb[2]) 
                        );

                        // set color picker controls to palette hex values
                        color_picker_keys.forEach((key, idx) => {
                            parent.wp.customize(key, 
                                field => field.set(palette_hex[idx])
                            )
                        });

                    }

                })
                .catch(() => 
                    console.log('There was an error downloading the image.')
                )

        })
    })

    function reset_color_pickers() {
        // set color picker controls to palette hex values
        color_picker_keys.forEach((key) => {
            parent.wp.customize(key, 
                field => field.set(default_value)
            )
        });
    }

    const rgbToHex = (r, g, b) => '#' + [r, g, b].map(x => {
        const hex = x.toString(16)
        return hex.length === 1 ? '0' + hex : hex
    }).join('')

}) (jQuery);