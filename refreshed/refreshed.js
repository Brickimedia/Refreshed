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
			Refreshed.toolboxDistanceFromTopWhenStatic = $( '.standard-toolbox' ).offset().top; // reassign this variable every time so it doesn't break if the distance to the top changes somehow (e.g. sitenotice has animated height)--better safe than sorry
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
		$( '.page-item-has-children' ).each(function( ) {
			if ( $( this ).offset().top > $( '#header-wrapper' ).height() + $( '#header-wrapper' ).offset().top ) { //if the .page-item is beneath the bottom of the header (and so it's cut off by overflow:hidden)
				$( this ).children( '.children' ).css({'display': 'none'});
				$( this ).removeClass( 'header-button-active' );
				$( this ).children( '.header-button' ).children( '.arrow' ).removeClass( 'rotate' );
			}
		});
	},

	toggleFade: function( trigger ) {
		$( trigger ).siblings( '.fadable' ).addBack( '.fadable' ).toggleClass( 'faded' );
		$( trigger ).children( '.arrow' ).toggleClass( 'rotate' );
		if ( $( trigger ).hasClass( 'header-button' ) ) {
			$( trigger ).toggleClass( 'header-button-active' );
		}
	},

	toggleHeaderSearch: function() {
		$( '.sidebar-shower' ).toggleClass( 'sidebar-shower-hidden' );
		$( '#fade-overlay' ).toggleClass( 'fade-overlay-active fade-overlay-below-header' ); // toggle the fade overlay
		if ( Refreshed.windowIsSmall ) { // On small, because #search-shower is replaced by #search-closer and vice-versa instead of the buttons appearing active, they take on the .header-button-active class when they shouldn't; this gets rid of it. On medium there's only #search-shower, so it functions properly and the class shouldn't be removed.
			$( '#search-shower' ).removeClass( 'header-button-active' );
			$( '#search-closer' ).removeClass( 'header-button-active' );
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
		$( '#fade-overlay' ).toggleClass( 'fade-overlay-active' ); // toggle the fade overlay
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
	});

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
	});
	
	// working code for dropdowns. Note: sinple code like this is much better than complicated like below :)
	$( "a.fade-trigger" ).click( function( e ) {
		Refreshed.toggleFade( $( e.target ).parent() );
	} );

	$( "a.sidebar-shower" ).click( function( e ) {
		Refreshed.toggleSidebar();
	} );

	$( document ).on( 'tap', function( e ) {
		/**
		* Showing/hiding dropdown menus. Preconditions:
		* 1) the menu must have classes "fadable" and "faded" to start
		* 2) the button triggering the menu must have class "fade-trigger"
		* 3) the menu and the button must be siblings
		*/
		/*if ( $( e.target ).closest( '.fade-trigger' ).length ) {
			console.log("fading1");
			Refreshed.toggleFade( $( e.target ).closest( '.fade-trigger' ) );
		}*/ // commented as work around is above

		$( '.fadable:not( .fade-trigger ):not( .faded )' ).each( function () { // targeting all dropdowns (i.e., fadable elements that aren't fadable themselves [since ones that are fadable are the #search-shower and #search-closer])
			if ( !$( e.target ).closest( $( this ).parent() ).length ) { // if starting from the event target (this, a child of this, or .fade-trigger) and doing up the DOM you do not run into this element's parent (so if this, a child of this, or .fade-trigger was not the target of the click)
				Refreshed.toggleFade( $( this ).siblings( '.fade-trigger' ) );
			}
		});
		if ( $( e.target ).closest( '.header-button:not([href])' ).length ) {
			e.preventDefault(); // prevent the standard click event on any .header-button without an href (so this doesn't apply to the wiki logo .header-button). Stops zooming when pressing header buttons, events on header buttons from firing twice, etc.
		}

		// the following if statements control hiding the search header and dropdown
		if ( Refreshed.headerSearchIsOpen && !$( e.target ).closest( '#header-wrapper .search' ).length && !$( e.target ).closest( '.header-suggestions' ).length ) { // we do this check instead of checking if the user pressed #fade-overlay because #fade-overlay can disappear if you resize, and then if you click off afterward you still want to hide the menu, etc. even if #fade-overlay is no longer visible
			Refreshed.toggleHeaderSearch();
		}
/*
		if ( Refreshed.sidebarIsOpen && !$( e.target ).closest( '#sidebar-wrapper' ).length ) {
			Refreshed.toggleSidebar();
		}
*/
		if ( $( e.target ).closest( '#search-shower' ).length ) {
			if ( Refreshed.usingIOS ) {
				$( window ).scrollTop( 0 ); // iOS tries to vertically center the search bar, scrolling to the top keeps the header at the top of the viewport
			}
			Refreshed.toggleHeaderSearch();
		} else if ( $( e.target ).closest( '#search-closer' ).length ) { // using if/else prevents header search from opening and closing on same tap
			Refreshed.toggleHeaderSearch();
		}

		// opening/closing sidebar on medium and small
		if ( $( e.target ).closest( '.sidebar-shower' ).length ) {
			Refreshed.toggleSidebar();
		}

		// expanding the small tools when the "more" (ellipsis) button is clicked
		if ( $( e.target ).closest( '#small-tool-more' ).length ) {
			$( '#small-tool-more' ).css({'display': 'none'});
			$( '.small-toolbox' ).addClass( 'small-toolbox-expanded scroll-shadow' );
		}
	} );


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
				});

				$( '#wpWatchthis' ).prop( 'checked', watchResponse.watched !== undefined );
			});
	});
	*/

	$( '#sidebar-wrapper' ).on( 'swipeleft', function( e ) {
		if ( Refreshed.sidebarIsOpen ) {
			e.preventDefault(); // prevent user from accidentally clicking link on swipe
			Refreshed.toggleSidebar();
		}
	});

	/**
	 * Add "header-suggestions" class to first .suggestions element for CSS
	 * targeting. There is usually one .suggestions element, but on Special:Search
	 * there is one for the #header-wrapper search bar and one for the #bodyContentsearch bar.
	 * We only want to target the one for the #header-wrapper search bar.
	 */
	setTimeout( function () { // wait a bit so the .suggestions elements can be added in (if we don't wait we'll be targeting nothing and it won't work)...
		$( '.suggestions' ).first().addClass( 'header-suggestions' ); // add class to first .suggestions element
	}, 100 );

});

/* Fix for Echo in Refreshed */
if ( document.getElementById( 'echo' ) ) {
	$( '#pt-notifications-alert' ).prependTo( '#echo' );
	$( '#pt-notifications-message' ).prependTo( '#echo' );
}

if ( $( '.mw-echo-notifications-badge' ).hasClass( 'mw-echo-unread-notifications' ) ) {
	$( '#pt-notifications-personaltools a' ).addClass( 'pt-notifications-personaltools-unread' );
}
