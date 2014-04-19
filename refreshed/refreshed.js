/* global $ */
var Refreshed = {
	heights: [],
	offsets: [],
	tocHeight: 0,
	user: false,
	header: false,
	left: false,
	right: false,
	standardToolboxIsDocked: false,
	standardToolboxInitialOffset: $( '#standardtoolbox' ).offset().top,

	flyOutScrollHeader: function() {
		if ($( '#contentwrapper' ).height() > $( window ).height() - $( '#header' ).height() && !Refreshed.standardToolboxIsDocked && ( $( '#standardtoolbox' ).offset().top - $( 'body' ).scrollTop() - $( '#header' ).height() < 0 ) ) { // first condition: only move the scroll header if the article content is bigger than the page (i.e. preventing it from being triggered when a user "rubber band scrolls" in OS X for example)
			$( '#standardtoolboxscrolloverlay' ).animate({'top': $( '#header' ).height()});
			Refreshed.standardToolboxIsDocked = true;
			$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).fadeOut();
		} else if (Refreshed.standardToolboxIsDocked && $( 'body' ).scrollTop() +  $( '#header' ).height() <= Refreshed.standardToolboxInitialOffset) {
			Refreshed.standardToolboxIsDocked = false;
			$( '#standardtoolboxscrolloverlay' ).animate({'top': -$( '#standardtoolboxscrolloverlay' ).height()});
			$( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).fadeOut();
		}
	},
	
	generateScrollHeader: function() {
		$( '#standardtoolbox' ).clone().attr( 'id', 'standardtoolboxscrolloverlay' ).insertAfter( '#standardtoolbox' );
		$( '#standardtoolboxscrolloverlay' ).css({'top': -$( '#standardtoolboxscrolloverlay' ).height()});
		if ( $( '#standardtoolboxscrolloverlay' ).outerWidth() != $( '#content' ).outerWidth() ) { //if standardtoolboxoverlay hasn't has its width set by CSS calc
			$( '#standardtoolboxscrolloverlay' ).css({'width': $( '#content' ).outerWidth() - ( $( '#standardtoolboxscrolloverlay' ).outerWidth() - $( '#standardtoolboxscrolloverlay' ).width() )}); // set #standardtoolboxscrolloverlay's width to the width of #content minus #standardtoolboxscrolloverlay's padding (and border, which is 0)
		}
	},
	
	getHeight: function( self ) {
		var id = self.attr( 'data-to' ).substring( 1 ),
			numid = self.attr( 'data-numid' ),
			to = $( document.getElementById( id ) ),
			heightTo = to.offset().top - 50;
		Refreshed.heights[numid] = heightTo;
		Refreshed.offsets[numid] = self.position().top;
		return heightTo;
	},

	moveBoxTo: function( height ) {
		var heightAbove = 0, idAbove = -1, goTo;

		for ( var i = 0; Refreshed.heights[i] <= height; i++ ) {
			heightAbove = Refreshed.heights[i];
			idAbove = i;
		}

		if ( idAbove == -1 ) {
			goTo = 0;
		} else if ( idAbove == Refreshed.heights.length - 1 ) {
			goTo = Refreshed.tocHeight;
		} else {
			var idBelow = idAbove + 1,
				heightBelow = Refreshed.heights[idBelow],
				heightDiff = heightBelow - heightAbove,
				heightMeRelative = height - heightAbove,
				fractMe = heightMeRelative / heightDiff;

			var elemAboveOffset = Refreshed.offsets[idAbove],
				elemBelowOffset = Refreshed.offsets[idBelow],
				elemOffsetDiff = elemBelowOffset - elemAboveOffset;

			goTo = elemAboveOffset + ( elemOffsetDiff * fractMe );

			if ( goTo > Refreshed.tocHeight ) {
				goTo = Refreshed.tocHeight;
			}
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

	/**
	 * Show/hide the list of sites in header when the small arrow is clicked.
	 */
	toggleSite: function() {
		$( '#siteinfo .headermenu' ).fadeToggle( 150 );
		$( '#siteinfo .arrow' ).toggleClass( 'rotate' );
		$( '#siteinfo-main a.arrow-link' ).toggleClass( 'dropdown-highlighted' );
		$( '#siteinfo-main' ).toggleClass( 'dropdown-bg-highlighted' );
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
	Refreshed.generateScrollHeader();
	Refreshed.flyOutScrollHeader();
	
	$( '#refreshed-toc a' ).on( 'click', function() {
		event.preventDefault();
		var heightTo = Refreshed.getHeight( $( this ) );
		$( 'html, body' ).animate( {scrollTop: heightTo}, 800 );
		return false;
	});

	$( window ).scroll( function() {
		if ( $( '#refreshed-toc a' ).length !== 0 ) {
			Refreshed.moveBoxTo( $( this ).scrollTop() );
		}
		Refreshed.flyOutScrollHeader();
	});

	$( window ).resize( function() {
		$( '#refreshed-toc a' ).each( function() {
			Refreshed.getHeight( $( this ) );
		});
		Refreshed.overlap();
		$( window ).scroll();
	});

	$( '#userinfo > a' ).on( 'click', function() {
		Refreshed.toggleUser();
	});

	$( '#siteinfo a.arrow-link' ).on( 'click', function() {
		Refreshed.toggleSite();
	});

	$( '#leftbar .shower' ).on( 'click', function() {
		Refreshed.toggleLeft();
		if ( Refreshed.right ) {
			Refreshed.toggleRight();
		}
	});

	$( '#rightbar .shower' ).on( 'click', function() {
		Refreshed.toggleRight();
		if ( Refreshed.left ) {
			Refreshed.toggleLeft();
		}
	});

	$( '#contentwrapper' ).on( 'click', function() {
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
			Refreshed.toggleSite();
		}
	});

	$( '#maintitle > #standardtoolbox #toolbox-link' ).on({
		'click': function() {
			if ( !$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).is( ':visible' ) ) {
				$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).fadeIn();
			}
			$( this ).children().toggleClass( 'rotate' );
		},
		'hover': function() {
			$( this ).children().toggleClass( 'no-show' );
		}
	});
	
	$( '#standardtoolboxscrolloverlay #toolbox-link' ).on({
		'click': function() {
			if ( !$( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).is( ':visible' ) ) {
				$( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).fadeIn();
			}
			$( this ).children().toggleClass( 'rotate' );
		},
		'hover': function() {
			$( this ).children().toggleClass( 'no-show' );
		}
	});
	
	$(document).mouseup( function ( e ) {
		if ( $( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).is( ':visible' ) ) {
			if ( !$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).is( e.target ) && $( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).has( e.target ).length === 0 ) { // if the target of the click isn't the container and isn't a descendant of the container
				$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).fadeOut();
			}
		}
	});
	
	$(document).mouseup( function ( e ) {
		if ( $( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).is( ':visible' ) ) {
			if ( !$( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).is( e.target ) && $( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).has( e.target ).length === 0 ) { // if the target of the click isn't the container and isn't a descendant of the container
				$( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).fadeOut();
			}
		}
	});
	
	$( '#smalltoolboxwrapper > a' ).on( 'click', function() {
		$( '#smalltoolbox' ).css({'overflow': 'auto'}).animate({'width': '100%'}).addClass( 'scrollshadow' );
		$( this ).css({'display': 'none'});
	});

	$( '#icon-ca-watch, #icon-ca-unwatch' ).parent().click( function( e ) {
		//ajax for watch icons
		var action, api, $link, title, otherAction;

		e.preventDefault();
		e.stopPropagation();

		title = mw.config.get( 'wgRelevantPageName', mw.config.get( 'wgPageName' ) );
		mw.loader.load( ['mediawiki.notification'], null, true );
		action = mw.util.getParamValue( 'action', this.href );
		otherAction = action === 'watch' ? 'unwatch' : 'watch';
		$link = $( this );
		$( 'div', this ).attr( 'id', 'icon-ca-' + otherAction );
		$( this ).attr( 'href', this.href.replace( action, otherAction ) );

		api = new mw.Api();
		api[action]( title )
			.done( function ( watchResponse ) {
				mw.notify( $.parseHTML( watchResponse.message ), {
					tag: 'watch-self'
				} );

				$( '#wpWatchthis' ).prop( 'checked', watchResponse.watched !== undefined );
			} );
	});

} );

$( window ).load( function() {
	Refreshed.tocHeight = $( '#refreshed-toc' ).height() - 28;
	$( window ).resize();
});
