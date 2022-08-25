(function($) {

    let color_picker_keys = [
        'custom_color1', 
        'custom_color2', 
        'custom_color1a', 
        'custom_color2a'
    ];

    wp.customize('logo', function(value) {
        value.bind(function(to) {
            fetch(to)
                .then(resp => resp.blob())
                .then(blobobject => {
                    console.log(blobobject);
                    // if blob is not an image ignore it
                    if (blobobject.type != 'image/png') return;
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



            // don't do anything if image doesn't exist
            // if (!$('img.attachment-thumb').length) return;
            

        })
    })

    function get_image_palette(img) {
        let color_thief = new ColorThief();
        var palette = color_thief.getPalette(img, 4);
        var palette_hex = palette.map((rgb) => rgbToHex(rgb));
    }

    const rgbToHex = (r, g, b) => '#' + [r, g, b].map(x => {
        const hex = x.toString(16)
        return hex.length === 1 ? '0' + hex : hex
    }).join('')

}) (jQuery);