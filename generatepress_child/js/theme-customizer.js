//Handling for postMessage of logo image
//import ColorThief from "color-thief";

//rgb to hexadecimal color function
function rgb2hex(rgb){
    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgb && rgb.length === 4) ? "#" +
    ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
    ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
    ("0" + parseInt(rgb[3],10).toString(16)).slice(-2) :"" ;
}

/* function colorChange(uploaded_image){

    var image_in = new Image;
    image_in.src = uploaded_image;
    image_in.onload = function(){
        const colorThief = new ColorThief();
        var pal = colorThief.getPalette(image_in, 2, 10);
        var firstColor = "rgb("+pal[0][0]+","+pal[0][1]+","+pal[0][2]+")";
        var secondColor = "rgb("+pal[1][0]+","+pal[1][1]+","+pal[1][2]+")"; 
        var firstInHex = rgb2hex(firstColor); 
        var secondInHex = rgb2hex(secondColor);
        console.log(firstInHex);
        console.log(secondInHex);
        return firstInHex;
    }

    //jQuery("#first").val(firstInHex);
    //jQuery("#second").val(secondInHex);
    
    //return image_in;
} */

( function( $ ) {
    wp.customize( 'logo', 'custom_color1', 'custom_color1a', function( value, custom_color1, custom_color1a) {
        value.bind( function( newval ) {
            var image_in = new Image;
            image_in.src = newval;
            image_in.onload = function(){
                const colorThief = new ColorThief();
                var pal = colorThief.getPalette(image_in, 2, 10);
                var firstColor = "rgb("+pal[0][0]+","+pal[0][1]+","+pal[0][2]+")";
                var secondColor = "rgb("+pal[1][0]+","+pal[1][1]+","+pal[1][2]+")"; 
                var firstInHex = rgb2hex(firstColor); 
                var secondInHex = rgb2hex(secondColor);
                console.log(firstInHex);
                console.log(secondInHex);
                
                
            }
            //$color1 = get_theme_mod( 'custom_color1', '#c96e40' );
            //$color2 = get_theme_mod( 'custom_color2', '#4fe3d9' );
            //alert('yeehaw');
        } );
        
    } );
} )( jQuery );


/*
//https://make.xwp.co/2016/07/24/dependently-contextual-customizer-controls/
//https://wordpress.stackexchange.com/questions/249912/add-remove-controls-dynamically-based-on-other-settings-in-customizer
var setupControl = function( control ) {
    var setActiveState, isDisplayed;
    isDisplayed = function() {
        return 'blank' !== setting.get();
    };
    setActiveState = function() {
        control.active.set( isDisplayed() );
    };
    setActiveState();
    setting.bind( setActiveState );
};
wp.customize.control( 'blogname', setupControl );
wp.customize.control( 'blogdescription', setupControl );
*/
