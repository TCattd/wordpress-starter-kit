(function ($) {

	$( '.gform-moderation-hidden-toggle' ).on( 'click', function( e ) {
		e.preventDefault();

		// Toggle blurring/hiding the abusive text.
		const text = $( e.currentTarget ).next( '.gform-moderation-text' );
		text.toggleClass( 'gform-moderation-text__visible' ).toggleClass( 'gform-moderation-text__hidden' ).attr('aria-hidden', function (i, attr) {
			if( attr == 'true' ) {
				wp.a11y.speak( text.text() );
			}
			return attr == 'true' ? 'false' : 'true'
		});

		// Toggle the button icon
		$( this ).children( '.gform-icon--hidden' ).toggle();
		$( this ).children( '.gform-common-icon--eye' ).toggle();

		// Toggle the screen reader button text
		$( this ).children( '.gform-hidden-toggle__message' ).attr( 'aria-hidden', function( _, attr ) { return !( attr == 'true' ) } );
	} );


})(jQuery);
