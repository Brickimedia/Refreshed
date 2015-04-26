/* global $ */
window.Refreshed = {
	standardToolboxIsDocked: false,
	standardToolboxInitialOffset: $( '.standard-toolbox' ).offset().top,
	scrollHeaderHasBeenGenerated: false,
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

	flyOutScrollHeader: function() {
		if (
			$( '#content' ).height() > $( window ).height() - $( '#header' ).height() &&
			!Refreshed.standardToolboxIsDocked &&
			( $( '.static-toolbox' ).offset().top - $( document ).scrollTop() - $( '#header' ).height() < 0 )
		)
		{
			// first boolean in condition: only move the scroll header if the article
			// content is bigger than the page (i.e. preventing it from being
			// triggered when a user "rubber band scrolls" in OS X for example)
			//$( '.fixed-toolbox' ).animate({'top': $( '#header' ).height()});
			$( '.fixed-toolbox' ).addClass( 'dropdown-open' );
			Refreshed.standardToolboxIsDocked = true;
			$( '#firstHeading > .standard-toolbox .standard-toolbox-dropdown' ).fadeOut();
		} else if ( Refreshed.standardToolboxIsDocked && $( document ).scrollTop() +  $( '#header' ).height() <= Refreshed.standardToolboxInitialOffset ) {
			Refreshed.standardToolboxIsDocked = false;
			//$( '.fixed-toolbox' ).animate({'top': -$( '.fixed-toolbox' ).height()});
			$( '.fixed-toolbox' ).removeClass( 'dropdown-open' );
			$( '.fixed-toolbox .standard-toolbox-dropdown' ).fadeOut();
		}
	},

	generateScrollHeader: function() {
		$( '.static-toolbox' ).clone().removeClass( 'static-toolbox' ).addClass( 'fixed-toolbox' ).insertBefore( '#sidebar-wrapper' );
		//$( '.fixed-toolbox' ).css({'top': -$( '.fixed-toolbox' ).height()});
		if ( $( '.fixed-toolbox' ).outerWidth() != $( '#bodyContent' ).outerWidth() ) { // if standard-toolboxoverlay hasn't has its width set by CSS calc
			$( '.fixed-toolbox' ).css({'width': $( '#bodyContent' ).outerWidth() - ( $( '.fixed-toolbox' ).outerWidth() - $( '.fixed-toolbox' ).width() )}); // set .fixed-toolbox's width to the width of #bodyContentminus .fixed-toolbox's padding (and border, which is 0)
		}
		Refreshed.scrollHeaderHasBeenGenerated = true;
	},

	resizeScrollHeader: function() {
		// set .fixed-toolbox's width to the width of #bodyContentminus .fixed-toolbox's padding (and border, which is 0)
		$( '.fixed-toolbox' ).css({'width': $( '#bodyContent' ).outerWidth() - ( $( '.fixed-toolbox' ).outerWidth() - $( '.fixed-toolbox' ).width() )});
	},

	resizeSpecialSearchBar: function() {
		if ( !Refreshed.windowIsBig && !Refreshed.windowIsSmall ) { // if running medium.css
			// set width of search bar to 100% of #bodyContent- "__ of __ results" text - width of submit button - width - 1em in the search bar
			Refreshed.widthOfSpecialSearchBar = $( '#bodyContent' ).width() - $( '.results-info' ).outerWidth() - $( '#bodyContent #search input[type="submit"]' ).outerWidth() - parseFloat( $( '#searchText' ).css( 'font-size' ) );
			Refreshed.widthOfSpecialSearchPowerSearchBar = $( '#bodyContent' ).width() - $( '.results-info' ).outerWidth() - $( '#bodyContent #powersearch input[type="submit"]' ).outerWidth() - parseFloat( $( '#powerSearchText' ).css( 'font-size' ) );
		} else if ( Refreshed.windowIsSmall ) { // if running small.css
			//set width of search bar to 100% of #bodyContent - width of submit button - width - 1em in the search bar
			Refreshed.widthOfSpecialSearchBar = $( '#bodyContent' ).width() - $( '#bodyContent #search input[type="submit"]' ).outerWidth() - parseFloat( $( '#searchText' ).css( 'font-size' ) );
			Refreshed.widthOfSpecialSearchPowerSearchBar = $( '#bodyContent' ).width() - $( '#bodyContent #powersearch input[type="submit"]' ).outerWidth() - parseFloat( $( '#powerSearchText' ).css( 'font-size' ) );
		}
		$( '#searchText' ).css({'width': Refreshed.widthOfSpecialSearchBar});
		$( '#powerSearchText' ).css({'width': Refreshed.widthOfSpecialSearchPowerSearchBar});
	},

	showHideOverflowingDropdowns: function() {
		$( '.page-item-has-children' ).each(function( ) {
			if ( $( this ).offset().top > $( '#header' ).height() + $( '#header' ).offset().top ) { //if the .page-item is beneath the bottom of the header (and so it's cut off by overflow:hidden)
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

	Refreshed.resizeSpecialSearchBar();

	if ( !Refreshed.usingIOS && !Refreshed.windowStartedSmall ) {
		// only perform if not on an iOS device (animations triggered by scroll
		// cannot be played during scroll on iOS Safari) and if the window was
		// running small.css when loaded
		Refreshed.generateScrollHeader();
		Refreshed.flyOutScrollHeader();
	}

	$( window ).scroll( function() {
		Refreshed.flyOutScrollHeader();
	});

	$( window ).resize( function() {
		if (
			Refreshed.scrollHeaderHasBeenGenerated &&
			$( '.fixed-toolbox' ).outerWidth() != $( '#bodyContent' ).outerWidth()
		)
		{
			// only perform if the scroll header has already been generated and
			// it needs to be resized (not already done by CSS calc)
			Refreshed.resizeScrollHeader();
		}

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

		Refreshed.resizeSpecialSearchBar();
		Refreshed.showHideOverflowingDropdowns();
	});

	$( document ).on( 'tap', function( e ) {
		/**
		* Showing/hiding dropdown menus. Preconditions:
		* 1) the menu must have classes "fadable" and "faded" to start
		* 2) the button triggering the menu must have class "fade-trigger"
		* 3) the menu and the button must be siblings
		*/
		if ( $( e.target ).closest( '.fade-trigger' ).length ) {
			Refreshed.toggleFade( $( e.target ).closest( '.fade-trigger' ) );
		}

		$( '.fadable:not( .fade-trigger ):not( .faded )' ).each( function () { // targeting all dropdowns (i.e., fadable elements that aren't fadable themselves [since ones that are fadable are the #search-shower and #search-closer])
			if ( !$( e.target ).closest( $( this ).parent() ).length ) { // if starting from the event target (this, a child of this, or .fade-trigger) and doing up the DOM you do not run into this element's parent (so if this, a child of this, or .fade-trigger was not the target of the click)
				Refreshed.toggleFade( $( this ).siblings( '.fade-trigger' ) );
			}
		});
		if ( $( e.target ).closest( '.header-button' ).length ) {
			e.preventDefault(); // prevent zooming when pressing header buttons, events on header buttons from firing twice, etc.
		}

		// the following if statements control hiding the search header and dropdown
		if ( Refreshed.headerSearchIsOpen && !$( e.target ).closest( '#header .search' ).length && !$( e.target ).closest( '.header-suggestions' ).length ) { // we do this check instead of checking if the user pressed #fade-overlay because #fade-overlay can disappear if you resize, and then if you click off afterward you still want to hide the menu, etc. even if #fade-overlay is no longer visible
			Refreshed.toggleHeaderSearch();
		}

		if ( Refreshed.sidebarIsOpen && !$( e.target ).closest( '#sidebar-wrapper' ).length ) {
			Refreshed.toggleSidebar();
		}

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
	});

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


	/**
	 * Add "header-suggestions" class to first .suggestions element for CSS
	 * targeting. There is usually one .suggestions element, but on Special:Search
	 * there is one for the #header search bar and one for the #bodyContentsearch bar.
	 * We only want to target the one for the #header search bar.
	 */
	setTimeout( function () { // wait a bit so the .suggestions elements can be added in (if we don't wait we'll be targeting nothing and it won't work)...
		$( '.suggestions' ).first().addClass( 'header-suggestions' ); // add class to first .suggestions element
	}, 100 );

});

/* Fix for Echo in Refreshed */
if ( document.getElementById( 'echo' ) ) {
	$( '#pt-notifications' ).prependTo( '#echo' );
}

if ( $( '.mw-echo-notifications-badge' ).hasClass( 'mw-echo-unread-notifications' ) ) {
	$( '#pt-notifications-personaltools a' ).addClass( 'pt-notifications-personaltools-unread' );
}
