//Handling for postMessage
( function( $ ) {
    console.log(wp.customize.control('logo'));
    wp.customize( 'logo', function( value ) {
        value.bind( function( newval ) {
            //Do stuff (newval variable contains your "new" setting data)
            alert('yeah yeeahawwa');
            $( 'h1' ).text( 'yeehaw' );
            console.log("yeehaw");
        } );
    } );
} )( jQuery );