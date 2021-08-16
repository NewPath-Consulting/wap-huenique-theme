//Handling for postMessage
( function( $ ) {
    wp.customize( 'logo', function( value ) {
        value.bind( function( newval ) {
            //Do stuff (newval variable contains your "new" setting data)
            alert(newval);
            $( 'h1' ).html( 'yeehaw' );
        } );
    } );
} )( jQuery );