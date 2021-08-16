//rgb to hexadecimal color function
function rgb2hex(rgb){
    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgb && rgb.length === 4) ? "#" +
     ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
     ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
     ("0" + parseInt(rgb[3],10).toString(16)).slice(-2) :"" ;
}
//color thief running
function colorChange(image){
    var colorThief = new ColorThief();

    var cp = colorThief.getPalette(image, 4, 10);      

    var firstColor = "rgb("+cp[0][0]+","+cp[0][1]+","+cp[0][2]+")";
    var secondColor = "rgb("+cp[1][0]+","+cp[1][1]+","+cp[1][2]+")";

    var firstInHex = rgb2hex(firstColor); 
    var secondInHex = rgb2hex(secondColor); 

    //jQuery("#first").val(firstInHex);
    //jQuery("#second").val(secondInHex);


    alert('First in hex is' . firstInHex);
}

//Handling for postMessage of logo image
( function( $ ) {
    wp.customize( 'logo', function( value ) {
        value.bind( function( newval ) {
            alert(yeehaw);
            colorChange(newval);
            //Do stuff (newval variable contains your "new" setting data)
            
            ///kendratestsite/wp-content/uploads/2021/08/paris.jpg
        } );
    } );
} )( jQuery );