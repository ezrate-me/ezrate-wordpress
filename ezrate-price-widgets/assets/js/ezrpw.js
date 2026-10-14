/**
 * EZRate Price Widgets — chart range buttons (day / week / month / all). No network requests: the prices are in the HTML.
 */
( function () {
	document.addEventListener( 'click', function ( e ) {
		var btn = e.target && e.target.closest ? e.target.closest( '.ezrpw-rg button[data-range]' ) : null;
		if ( ! btn ) {
			return;
		}
		var box = btn.closest( '.ezrpw-ch' );
		var range = btn.getAttribute( 'data-range' );
		box.querySelectorAll( '.ezrpw-rg button' ).forEach( function ( b ) {
			b.setAttribute( 'aria-pressed', b === btn ? 'true' : 'false' );
		} );
		box.querySelectorAll( '.ezrpw-pane' ).forEach( function ( p ) {
			p.hidden = p.getAttribute( 'data-range' ) !== range;
		} );
	} );
} )();
