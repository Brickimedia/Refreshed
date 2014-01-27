/* global $ */
var Refreshed = {
	heights: [],
	i: 0,
	user: false,
	header: false,
	left: false,
	right: false,

	getHeight: function( self ) {
		var id = self.attr( 'data-to' ).substring( 1 ),
			numid = self.attr( 'data-numid' ),
			to = $( document.getElementById( id ) ),
			heightTo = to.offset().top - 50;
		Refreshed.heights[numid] = heightTo;
		return heightTo;
	},

	moveBoxTo: function( height ) {
		var heightAbove = 0, idAbove = -1, goTo;

		Refreshed.heights.forEach( function( elem, index ) {
			if ( elem <= height ) {
				heightAbove = elem;
				idAbove = index;
			}
		});
		if ( idAbove == -1 ) {
			goTo = 0;
		} else if ( idAbove == Refreshed.heights.length - 1 ) {
			goTo = $('#refreshed-toc').height() - 28;
		} else {
			var idBelow = idAbove + 1,
				heightBelow = Refreshed.heights[idBelow],
				heightDiff = heightBelow - heightAbove,
				heightMeRelative = height - heightAbove,
				fractMe = heightMeRelative / heightDiff;

			var elemAbove = $( 'a[data-numid=' + idAbove + ']' ),
				elemAboveOffset = elemAbove.position().top,
				elemBelow = $( 'a[data-numid=' + idBelow + ']' ),
				elemBelowOffset = elemBelow.position().top,
				elemOffsetDiff = elemBelowOffset - elemAboveOffset;
			goTo = elemAboveOffset + ( elemOffsetDiff * fractMe );
		}

		$( '#toc-box' ).stop().animate( { 'top': goTo }, 200 );
	},

	overlap: function() {
		var bottom = $( '#leftbar-top' ).position().top + $( '#leftbar-top' ).outerHeight(),
			top3 = $( '#refreshed-toc' ).outerHeight(),
			overlap = $( window ).height() - top3 - bottom - 10;

		if ( overlap < 0 ) {
			var newHeight = top3 + overlap;

			if ( newHeight <= 50 ) {
				$( '#leftbar-bottom' ).css( 'visibility', 'hidden' );
			} else {
				$( '#leftbar-bottom' ).css( 'visibility', 'visible' );
			}

			$( '#leftbar-bottom' ).height( newHeight );
			$( '#leftbar-bottom' ).css( {
				'overflow-y': 'scroll',
				'bottom': '0',
				// @todo FIXME: why hard-code this in? This is not internationally compatible... - it moves the scrollbar to the left hand side
				'direction': 'rtl'
			} );

			$( window ).scroll( Refreshed.onScroll );

		} else if ( overlap < 16 ) {
			$( '#leftbar-bottom' ).css( {
				'overflow-y': 'hidden',
				'bottom': overlap + 'px',
				'direction': 'ltr'
			} );
		} else {
			$( '#leftbar-bottom' ).height( 'auto' );
			$( '#leftbar-bottom' ).css( {
				'visibility': 'visible',
				'overflow-y': 'auto',
				'bottom': '1em',
				'direction': 'ltr'
			} );

			$( window ).off( 'scroll', Refreshed.onScroll );
		}
	},

	toggleUser: function() {
		$( '#userinfo .headermenu' ).fadeToggle( 150 );
		$( '#userinfo .arrow' ).toggleClass( 'rotate' );
		Refreshed.user = !Refreshed.user;
	},

	toggleSite: function() {
		$( '#siteinfo .headermenu' ).fadeToggle( 150 );
		$( '#siteinfo .arrow' ).toggleClass( 'rotate' );
		Refreshed.header = !Refreshed.header;
	},

	toggleLeft: function() {
		if ( Refreshed.left ) {
			$( '#leftbar' ).animate({'left': '-12em'});
		} else {
			$( '#leftbar' ).animate({'left': '0em'});
		}
		$( '#leftbar .shower' ).fadeToggle();
		Refreshed.left = !Refreshed.left;
	},

	toggleRight: function() {
		if ( Refreshed.right ) {
			$( '#rightbar' ).animate({'right': '-12em'});
		} else {
			$( '#rightbar' ).animate({'right': '0em'});
		}
		$( '#rightbar .shower' ).fadeToggle();
		Refreshed.right = !Refreshed.right;
	},

	onScroll: function() {
		var pos = $( '#toc-box' ).position().top,
			height = $( '#leftbar-bottom' ).height(),
			goTo = pos - ( height / 2 );

		goTo = goTo + $( '#refreshed-toc a' ).height();

		$( '#leftbar-bottom' ).stop().animate( {'scrollTop': goTo}, 200 );
	}
};

$( document ).ready( function() {
	$( '#refreshed-toc a' ).each( function() {
		var href = $( this ).attr( 'href' );
		$( this ).attr({'data-to': href});
		$( this ).attr({'data-numid': Refreshed.i});
		Refreshed.i++;
	});

	$( '#refreshed-toc a' ).each( function() {
		Refreshed.getHeight( $( this ) );
	});

	$( '#refreshed-toc a' ).click( function() {
		event.preventDefault();
		var heightTo = Refreshed.getHeight( $(this ) );
		$( 'html, body' ).animate( {scrollTop: heightTo}, 800 );
		return false;
	});

	$( window ).scroll( function() {
		if ( $( '#refreshed-toc a' ).length !== 0 ) {
			Refreshed.moveBoxTo( $( this ).scrollTop() );
		}
	});

	$( window ).resize( function() {
		Refreshed.overlap();
		$( window ).scroll();
		$( '#refreshed-toc a' ).each( function() {
			Refreshed.getHeight( $( this ) );
		});
	});

	$( '#userinfo > a' ).click( function() {
		Refreshed.toggleUser();
	});

	$( '#siteinfo > a' ).click( function() {
		Refreshed.toggleSite();
	});

	$( '#leftbar .shower' ).click( function() {
		Refreshed.toggleLeft();
		if ( Refreshed.right ) {
			Refreshed.toggleRight();
		}
	});

	$( '#rightbar .shower' ).click( function() {
		Refreshed.toggleRight();
		if ( Refreshed.left ) {
			Refreshed.toggleLeft();
		}
	});

	$( '#contentwrapper' ).click( function() {
		if ( Refreshed.left ) {
			Refreshed.toggleLeft();
		}
		if ( Refreshed.right ) {
			Refreshed.toggleRight();
		}
		if ( Refreshed.user ) {
			Refreshed.toggleUser();
		}
		if ( Refreshed.header ) {
			Refreshed.toggleHeader();
		}
	});

	$( '#toolbox-link' ).click( function() {
		$( '#toolbox' ).fadeToggle();
		$( this ).children().toggleClass( 'rotate' );
	});
	$( '#toolbox-link' ).hover( function() {
		$( this ).children().toggleClass( 'no-show' );
	});

	$( '#smalltoolboxwrapper > a' ).click( function() {
		$( '#smalltoolbox' ).css({'overflow': 'auto'}).animate({'width': '100%'}).addClass("scrollshadow");
		$( this ).css({'display': 'none'});
	});
} );

$( window ).load( function() {
	Refreshed.overlap();
});
