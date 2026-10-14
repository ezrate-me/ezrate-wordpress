/**
 * EZRate Price Widgets — dashboard: show the fields of the chosen type, asset picker with order, colour pickers,
 * live preview (rendered by the server from the cached prices), copy shortcodes.
 */
( function ( $ ) {
	var cfg = window.ezrpwAdmin || {};
	var form = document.getElementById( 'ezrpwa-form' );

	// copy a shortcode on click
	document.addEventListener( 'click', function ( e ) {
		var c = e.target.closest && e.target.closest( '.ezrpwa-copy' );
		if ( ! c || ! navigator.clipboard ) {
			return;
		}
		navigator.clipboard.writeText( c.textContent ).then( function () {
			var old = c.textContent;
			c.classList.add( 'is-copied' );
			c.setAttribute( 'data-copied', cfg.copied || 'Copied' );
			setTimeout( function () {
				c.classList.remove( 'is-copied' );
				c.textContent = old;
			}, 1200 );
		} );
	} );
	document.querySelectorAll( '.ezrpwa-del' ).forEach( function ( a ) {
		a.addEventListener( 'click', function ( e ) {
			if ( ! window.confirm( cfg.confirm ) ) {
				e.preventDefault();
			}
		} );
	} );

	if ( ! form ) {
		return;
	}

	var type = function () {
		var r = form.querySelector( 'input[name="ezrpw[type]"]:checked' );
		return r ? r.value : 'ticker';
	};
	var position = function () {
		var r = form.querySelector( 'input[name="ezrpw[position]"]:checked' );
		return r ? r.value : 'inline';
	};
	function toggle() {
		var t = type();
		form.querySelectorAll( '[data-show]' ).forEach( function ( el ) {
			el.hidden = el.getAttribute( 'data-show' ).split( ' ' ).indexOf( t ) < 0;
		} );
		var fx = form.querySelector( '.ezrpwa-fixed-opts' );
		if ( fx ) {
			fx.hidden = position() === 'inline';
		}
	}

	// asset picker: selected list (order) + pool of chips
	var hidden = document.getElementById( 'ezrpwa-assets' );
	var sel = document.getElementById( 'ezrpwa-sel' );
	var pool = document.getElementById( 'ezrpwa-pool' );
	function syncAssets() {
		var list = [];
		sel.querySelectorAll( 'li' ).forEach( function ( li ) {
			list.push( li.getAttribute( 'data-s' ) );
		} );
		hidden.value = list.join( ',' );
		pool.querySelectorAll( '.ezrpwa-chip' ).forEach( function ( c ) {
			c.classList.toggle( 'is-on', list.indexOf( c.getAttribute( 'data-s' ) ) > -1 );
		} );
		schedule();
	}
	function item( sym, name, icon ) {
		var li = document.createElement( 'li' );
		li.setAttribute( 'data-s', sym );
		if ( icon ) {
			var img = document.createElement( 'img' );
			img.src = icon;
			img.alt = '';
			img.width = 18;
			img.height = 18;
			li.appendChild( img );
		}
		var s = document.createElement( 'span' );
		s.textContent = name;
		li.appendChild( s );
		[ [ 'ezrpwa-up', '↑', cfg.up ], [ 'ezrpwa-down', '↓', cfg.down ], [ 'ezrpwa-x', '×', cfg.remove ] ].forEach( function ( b ) {
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = b[ 0 ];
			btn.textContent = b[ 1 ];
			btn.setAttribute( 'aria-label', b[ 2 ] );
			li.appendChild( btn );
		} );
		return li;
	}
	if ( sel && pool ) {
		pool.addEventListener( 'click', function ( e ) {
			var c = e.target.closest( '.ezrpwa-chip' );
			if ( ! c ) {
				return;
			}
			var sym = c.getAttribute( 'data-s' );
			var have = sel.querySelector( 'li[data-s="' + sym + '"]' );
			if ( have ) {
				have.remove();
			} else if ( sel.children.length < 20 ) {
				sel.appendChild( item( sym, c.textContent.trim(), c.getAttribute( 'data-icon' ) ) );
			}
			syncAssets();
		} );
		sel.addEventListener( 'click', function ( e ) {
			var b = e.target.closest( 'button' );
			if ( ! b ) {
				return;
			}
			var li = b.closest( 'li' );
			if ( b.classList.contains( 'ezrpwa-x' ) ) {
				li.remove();
			} else if ( b.classList.contains( 'ezrpwa-up' ) && li.previousElementSibling ) {
				sel.insertBefore( li, li.previousElementSibling );
			} else if ( b.classList.contains( 'ezrpwa-down' ) && li.nextElementSibling ) {
				sel.insertBefore( li.nextElementSibling, li );
			}
			syncAssets();
		} );
		var q = document.getElementById( 'ezrpwa-q' );
		q.addEventListener( 'input', function () {
			var v = q.value.trim().toLowerCase();
			pool.querySelectorAll( '.ezrpwa-chip' ).forEach( function ( c ) {
				c.hidden = v !== '' && c.getAttribute( 'data-q' ).indexOf( v ) < 0;
			} );
		} );
		syncAssets();
	}

	// live preview
	var timer = 0;
	var preview = document.getElementById( 'ezrpwa-preview' );
	function schedule() {
		clearTimeout( timer );
		timer = setTimeout( refresh, 250 );
	}
	function refresh() {
		var data = new FormData( form );
		data.set( 'action', 'ezrpw_preview' );
		data.set( 'nonce', cfg.nonce );
		preview.classList.add( 'is-loading' );
		window.fetch( cfg.ajax, { method: 'POST', credentials: 'same-origin', body: data } )
			.then( function ( r ) {
				return r.json();
			} )
			.then( function ( j ) {
				if ( j && j.success ) {
					preview.innerHTML = j.data.html;
				}
			} )
			.finally( function () {
				preview.classList.remove( 'is-loading' );
			} );
	}

	form.addEventListener( 'input', function ( e ) {
		if ( e.target.type === 'range' ) {
			var o = e.target.parentNode.querySelector( 'output' );
			if ( o ) {
				o.textContent = e.target.value;
			}
		}
		if ( e.target.id !== 'ezrpwa-q' ) {
			schedule();
		}
	} );
	form.addEventListener( 'change', function () {
		toggle();
		schedule();
	} );
	if ( $ && $.fn.wpColorPicker ) {
		$( '.ezrpwa-color' ).wpColorPicker( {
			change: function () {
				setTimeout( schedule, 0 );
			},
			clear: function () {
				setTimeout( schedule, 0 );
			},
		} );
	}
	toggle();
} )( window.jQuery );
