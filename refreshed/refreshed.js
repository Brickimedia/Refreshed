/* global $ */
window.Refreshed = {
	toolboxDistanceFromTopWhenStatic: $( '.standard-toolbox' ).offset().top,
	toolboxIsFixed: false,
	usingIOS: false,
	thresholdForSmallCSS: 601,
	windowStartedSmall: false,
	thresholdForBigCSS: 1001,
	windowIsBig: false,
	windowIsSmall: false,
	widthOfSpecialSearchBar: 0,
	widthOfSpecialSearchPowerSearchBar: 0,
	headerSearchIsOpen: false,
	sidebarIsOpen: false,

	shouldToggleFixedToolbox: function() {
		if ( !Refreshed.toolboxIsFixed ) {
			// reassign this variable every time so it doesn't break if the
			// distance to the top changes somehow (e.g. sitenotice has animated height)
			// --better safe than sorry
			Refreshed.toolboxDistanceFromTopWhenStatic = $( '.standard-toolbox' ).offset().top;
		}
		distanceScrolled = $( window ).scrollTop();
		toolboxShouldBeToggled = ( Refreshed.toolboxDistanceFromTopWhenStatic - distanceScrolled <= $( '#header-wrapper' ).height() && !Refreshed.toolboxIsFixed ) || ( Refreshed.toolboxDistanceFromTopWhenStatic - distanceScrolled > $( '#header-wrapper' ).height() && Refreshed.toolboxIsFixed ); // true if 1) the page is scrolled enough for .standard-toolbox to be fixed and it isn't or 2) page isn't scrolled enough for it to be fixed and it is
		return toolboxShouldBeToggled;
	},

	toggleFixedToolbox: function() {
		$( '.standard-toolbox' ).toggleClass( 'fixed-toolbox' );
		if ( $( '.standard-toolbox' ).hasClass( 'fixed-toolbox' ) ) {
			Refreshed.toolboxIsFixed = true;
		} else {
			Refreshed.toolboxIsFixed = false;
		}
	},

	showHideOverflowingDropdowns: function() {
		$( '.page-item-has-children' ).each( function() {
			if ( $( this ).offset().top > $( '#header-wrapper' ).height() + $( '#header-wrapper' ).offset().top ) { // if the .page-item is beneath the bottom of the header (and so it's cut off by overflow:hidden)
				$( this ).children( '.children' ).css({'display': 'none'});
				$( this ).removeClass( 'header-button-active' );
				$( this ).children( '.header-button' ).children( '.arrow' ).removeClass( 'rotate' );
			}
		} );
	},

	toggleFade: function( trigger ) {
		$( trigger ).siblings( '.fadable' ).addBack( '.fadable' ).toggleClass( 'faded' );
		$( trigger ).children( '.arrow' ).toggleClass( 'rotate' );
		if ( $( trigger ).hasClass( 'header-button' ) ) {
			$( trigger ).toggleClass( 'header-button-active' );
		}
		$( trigger ).parent().toggleClass( 'open-fadable-parent' );
	},

	toggleHeaderSearch: function() {
		$( '.sidebar-shower' ).toggleClass( 'sidebar-shower-hidden' );
		$( '#fade-overlay' ).toggleClass( 'fade-overlay-active fade-overlay-triggered-by-search' ); // toggle the fade overlay
		if ( Refreshed.windowIsSmall ) {
			// On small, because .search-shower is replaced by .search-closer and
			// vice-versa instead of the buttons appearing active, they take on
			// the .header-button-active class when they shouldn't; this gets rid of it.
			// On medium there's only .search-shower, so it functions properly
			// and the class shouldn't be removed.
			$( '.search-shower' ).removeClass( 'header-button-active' );
			$( '.search-closer' ).removeClass( 'header-button-active' );
		}
		if ( Refreshed.headerSearchIsOpen ) {
			$( '#searchInput' ).val( '' ).blur();
		} else {
			$( '#searchInput' ).focus();
		}
		Refreshed.headerSearchIsOpen = !Refreshed.headerSearchIsOpen;
	},

	toggleSidebar: function() {
		$( '#sidebar-wrapper' ).toggleClass( 'sidebar-open' );
		$( '#fade-overlay' ).toggleClass( 'fade-overlay-active fade-overlay-triggered-by-sidebar' ); // toggle the fade overlay
		$( '.sidebar-shower' ).toggleClass( 'header-button-active' );
		Refreshed.sidebarIsOpen = !Refreshed.sidebarIsOpen;
	}
};

$( document ).ready( function() {
	if ( navigator.userAgent.toLowerCase().match( /(iPad|iPhone|iPod)/i ) ) { // detect if on an iOS device
		Refreshed.usingIOS = true;
	}

	if ( $( window ).width() < Refreshed.thresholdForSmallCSS ) {
		Refreshed.windowStartedSmall = true;
	}

	// test if window is running big.css
	if ( $( window ).width() >= Refreshed.thresholdForBigCSS ) {
		Refreshed.windowIsBig = true;
	} else {
		Refreshed.windowIsBig = false;
	}

	// test if window is running small.css
	if ( $( window ).width() <= Refreshed.thresholdForSmallCSS ) {
		Refreshed.windowIsSmall = true;
	} else {
		Refreshed.windowIsSmall = false;
	}

	if ( Refreshed.shouldToggleFixedToolbox() ) {
		Refreshed.toggleFixedToolbox();
	}

	$( window ).scroll( function() {
		if ( Refreshed.shouldToggleFixedToolbox() ) {
			Refreshed.toggleFixedToolbox();
		}
	} );

	$( window ).resize( function() {
		if ( $( window ).width() >= Refreshed.thresholdForBigCSS ) {
			Refreshed.windowIsBig = true;
		} else {
			Refreshed.windowIsBig = false;
		}

		if ( $( window ).width() <= Refreshed.thresholdForSmallCSS ) {
			Refreshed.windowIsSmall = true;
		} else {
			Refreshed.windowIsSmall = false;
		}

		Refreshed.showHideOverflowingDropdowns();
	} );

	// working code for dropdowns. Note: simple code like this is much better than complicated like below :)
	$( 'a.header-button.fade-trigger' ).click( function( e ) {
		Refreshed.toggleFade( this );
	} );

	$( 'a.toolbox-link.fade-trigger' ).click( function( e ) {
		Refreshed.toggleFade( this );
	} );

	$( document ).click( function( e ) {
		$( '.open-fadable-parent' ).each( function () {
			// target each .open-fadable-parent (i.e., each parent of an open .fadable element)
			if ( !$( e.target ).closest( $( this ) ).length ) {
				// if starting from the element clicked and moving up the DOM, we don't
				// run into that the current .open-fadable-parent...
				Refreshed.toggleFade( $( this ).children( '.fade-trigger' ) );
			}
		});
	} );

	$( 'a.sidebar-shower' ).click( function( e ) {
		Refreshed.toggleSidebar();
	} );

	$( 'a.search-shower' ).click( function( e ) {
		Refreshed.toggleHeaderSearch();
	} );

	$( 'a.search-closer' ).click( function( e ) {
		Refreshed.toggleHeaderSearch();
	} );

	$( 'a#fade-overlay' ).click( function( e ) {
		// Unfortunately breaking this into two doesn't work. It might be
		// because the .fade-overlay-triggered-by-SOMETHING classes aren't
		// applied yet on $( document ).ready() (and this function is inside
		// the $( document ).ready() ), so as far as the code is concerned
		// elements with that class don't exist.
		if ( $( this ).hasClass( 'fade-overlay-triggered-by-sidebar' ) ) {
			Refreshed.toggleSidebar();
		} else if ( $( this ).hasClass( 'fade-overlay-triggered-by-search' ) ) {
			Refreshed.toggleHeaderSearch();
		}
	} );

	/* Useful to follow click propagation for debugging
	$( '*' ).click( function ( e ) {
		alert( '<' + this.nodeName + ' id="' + this.id + '" class="' + this.className + '">' );
	} );
	*/

	/* Temporarily disabled to fix front-end bugs
	$( '#icon-ca-watch, #icon-ca-unwatch' ).parent().tap( function( e ) {
		// AJAX for watch icons
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
	} );
	*/

	/*$( '#sidebar-wrapper' ).on( 'swipeleft', function( e ) {
		if ( Refreshed.sidebarIsOpen ) {
			e.preventDefault(); // prevent user from accidentally clicking link on swipe
			Refreshed.toggleSidebar();
		}
	} );*/
	setTimeout( function () { // wait a bit so the .suggestions elements can be added in (if we don't wait we'll be targeting nothing and it won't work)...
		$( '.suggestions' ).first().addClass( 'header-suggestions' ); // add class to first .suggestions element
	}, 100 );
} );

/* Fix for Echo in Refreshed */
if ( document.getElementById( 'echo' ) ) {
	$( '#pt-notifications-alert' ).prependTo( '#echo' );
	$( '#pt-notifications-message' ).prependTo( '#echo' );
}

if ( $( '.mw-echo-notifications-badge' ).hasClass( 'mw-echo-unread-notifications' ) ) {
	$( '#pt-notifications-personaltools a' ).addClass( 'pt-notifications-personaltools-unread' );
}
