//binds 2a to 1a
/* ( function( $ ) {
    wp.customize( 'custom_color1a', 'custom_color2a', 'logo', function( custom_color1a, custom_color2a, logo) {
        custom_color1a.bind( function( value ) {
            custom_color2a.set( value );
            console.log(logo.toString());
        } );
    } );
} )( jQuery ); */
function rgb2hex(rgb){
    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgb && rgb.length === 4) ? "#" +
    ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
    ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
    ("0" + parseInt(rgb[3],10).toString(16)).slice(-2) :"" ;
}

( function( $ ) {
    wp.customize( 'logo1', 'custom_color1a', 'custom_color2a', function( value, custom_color1a, custom_color2a) {
        value.bind( function( newval ) { //on logo upload?
            console.log(newval);
            var image_in = new Image;            
            image_in.onload = function(){
                const colorThief = new ColorThief();
                console.log("colorthief");
                var pal = colorThief.getPalette(image_in, 2, 10);
                var firstColor = "rgb("+pal[0][0]+","+pal[0][1]+","+pal[0][2]+")";
                var secondColor = "rgb("+pal[1][0]+","+pal[1][1]+","+pal[1][2]+")"; 
                var firstInHex = rgb2hex(firstColor); 
                var secondInHex = rgb2hex(secondColor);
                console.log(firstInHex);
                console.log(secondInHex);
                /* custom_color1a.set(firstInHex);
                custom_color2a.set(secondInHex); */
                
            }
            image_in.src = newval;
            custom_color1a.set('#000000');
            console.log('yeehaw');
            
            //$color1 = get_theme_mod( 'custom_color1', '#c96e40' );
            //$color2 = get_theme_mod( 'custom_color2', '#4fe3d9' );
            //alert('yeehaw');
        } );
        
    } );
} )( jQuery );