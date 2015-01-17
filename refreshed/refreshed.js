/* global $ */
window.Refreshed = {
	standardToolboxIsDocked: false,
	standardToolboxInitialOffset: $( '.standard-toolbox' ).offset().top,
	scrollHeaderHasBeenGenerated: false,
	usingIOS: false,
	thresholdForSmallCSS: 601,
	windowStartedSmall: false,
	thresholdForBigCSS: 1001,
	searchDropdownOpen: false,
	userToolsOpen: false,
	siteNavOpen: false,
	windowIsBig: false,
	windowIsSmall: false,
	widthOfSpecialSearchBar: 0,
	widthOfSpecialSearchPowerSearchBar: 0,
	sidebarOpen: false,

	flyOutScrollHeader: function() {
		if (
			$( '#content' ).height() > $( window ).height() - $( '#header' ).height() &&
			!Refreshed.standardToolboxIsDocked &&
			( $( '.static-toolbox' ).offset().top - $( document ).scrollTop() - $( '#header' ).height() < 0 )
		)
		{
			// first condition: only move the scroll header if the article
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
				$( this ).children( '.clickable-region' ).children( '.arrow' ).removeClass( 'rotate' );
			}
		} );
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
	} );

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
	} );

	/* tools dropdown attached to firstHeading */
	$( '#firstHeading > .standard-toolbox #toolbox-link' ).on( {
		'click': function() {
			if ( !$( '#firstHeading > .standard-toolbox .standard-toolbox-dropdown' ).is( ':visible' ) ) {
				$( '#firstHeading > .standard-toolbox .standard-toolbox-dropdown' ).fadeIn();
			}
			$( this ).children().toggleClass( 'rotate' );
		},
		'hover': function() {
			$( this ).children().toggleClass( 'no-show' );
		}
	} );

	$( document ).mouseup( function ( e ) {
		if ( $( '#firstHeading > .standard-toolbox .standard-toolbox-dropdown' ).is( ':visible' ) ) {
			if (
				!$( '#firstHeading > .standard-toolbox .standard-toolbox-dropdown' ).is( e.target ) &&
				$( '#firstHeading > .standard-toolbox .standard-toolbox-dropdown' ).has( e.target ).length === 0
			)
			{
				// if the target of the click isn't the container and isn't a descendant of the container
				$( '#firstHeading > .standard-toolbox .standard-toolbox-dropdown' ).fadeOut();
			}
		}
	} );

	/* tools dropdown on the "scroll header" */
	$( '.fixed-toolbox #toolbox-link' ).on( {
		'click': function() {
			if ( !$( '.fixed-toolbox .standard-toolbox-dropdown' ).is( ':visible' ) ) {
				$( '.fixed-toolbox .standard-toolbox-dropdown' ).fadeIn();
			}
			$( this ).children().toggleClass( 'rotate' );
		},
		'hover': function() {
			$( this ).children().toggleClass( 'no-show' );
		}
	} );

	$( document ).mouseup( function ( e ) {
		if ( $( '.fixed-toolbox .standard-toolbox-dropdown' ).is( ':visible' ) ) {
			if (
				!$( '.fixed-toolbox .standard-toolbox-dropdown' ).is( e.target ) &&
				$( '.fixed-toolbox .standard-toolbox-dropdown' ).has( e.target ).length === 0
			)
			{
				// if the target of the click isn't the container and isn't a descendant of the container
				$( '.fixed-toolbox .standard-toolbox-dropdown' ).fadeOut();
			}
		}
	} );

	$.fn.extend( {
		clickOrTouch: function( handler ) {
			return this.each( function() {
				var event = ( 'ontouchend' in document ) ? 'touchend' : 'mouseup';
				$( this ).on( event, handler );
			} );
		}
	} );

	/* search dropdown */
	$( '#search-shower' ).click( function() { // Unfortunately, touchend causes the search bar to lose focus on iPhones on iOS 7 and iPads on iOS 8 (haven't tested on Android), but it keeps its focus if you used the standard click event. The other menus, etc. use "touchOrClick" b/c the touchend event executes faster than the standard click on iOS (once again, haven't tested on Android).
			if ( !Refreshed.searchDropdownOpen ) {
				if ( Refreshed.usingIOS ) {
					$( window ).scrollTop( 0 ); // iOS tries to vertically center the search bar, scrolling to the top keeps the header at the top of the viewport
				}
				$( '#header .search' ).addClass( 'search-open' );
				$( '#sidebar-shower' ).addClass( 'sidebar-shower-hidden' );
				$( '#fade-overlay' ).addClass( 'fade-overlay-active fade-overlay-below-header' ); // toggle the fade overlay
				$( '#searchInput' ).focus();
				$( this ).toggleClass( 'header-button-active' );
				Refreshed.searchDropdownOpen = true;
			} else {
				// this only runs in "medium" mode (the search dropdown only
				// appears in "medium" and "small" mode, and "small" is covered
				// by the document.click function below)
				$( '#header .search' ).removeClass( 'search-open' );
				$( '#sidebar-shower' ).removeClass( 'sidebar-shower-hidden' );
				$( '#searchInput' ).blur().val( '' ); // deselect the search input and reset its contents (remove anything the user entered)
				$( '#fade-overlay' ).removeClass( 'fade-overlay-active fade-overlay-below-header' ); // toggle the fade overlay
				$( '#search-shower' ).removeClass( 'header-button-active' );
				Refreshed.searchDropdownOpen = false; // no delay needed because the spamming issue is only present on "small"
			}
	} );

	$( document ).click( function ( e ) { // if you use clickOrTouch, pressing the .suggestions element will cause the window to close on mobile (maybe the clickOrTouch section is executed before a plain click and thus this is run and .search is hidden before the broswer acknowledges the click event on .suggestions to load the searched-for page?)
		if ( Refreshed.searchDropdownOpen && $( window ).width() < Refreshed.thresholdForBigCSS ) { // window size must be checked because we only want to hide the search bar if we're not in "big" mode
			if (
				!$( '#header .search' ).is( e.target ) &&
				!$( '#search-shower' ).is( e.target ) &&
				!$( '.search input' ).is( e.target )
			)
			{
				// if the target of the click isn't the search container,
				// search button, or the search box itself (we can't set it to
				// all descendants of .search because #search-closer needs to
				// be able to close the search box)
				$( '#header .search' ).removeClass( 'search-open' );
				$( '#sidebar-shower' ).removeClass( 'sidebar-shower-hidden' );
				$( '#searchInput' ).blur().val( '' ); // deselect the search input and reset its contents (remove anything the user entered)
				$( '#fade-overlay' ).removeClass( 'fade-overlay-active fade-overlay-below-header' ); //toggle the fade overlay
				$( '#search-shower' ).removeClass( 'header-button-active' );
				// delay variable change for 375ms until after the animation is
				// complete so both animations don't run on one press
				setTimeout( function () {
					Refreshed.searchDropdownOpen = false;
				}, 375 );
			}
		}
	} );

	/* user tools dropdown */
	$( '#user-info > a' ).clickOrTouch( function() {
		if ( !$( '#user-info .header-menu' ).is( ':visible' ) ) {
			$( '#user-info .header-menu' ).fadeIn();
			$( this ).addClass( 'header-button-active' );
			$( '#user-info .arrow' ).addClass( 'rotate' );
			// delay the second clickOrTouch function (which performs fadeOut)
			// to stop fadeIn and fadeOut on one click (also prevents user from
			// spamming so it constantly fades in/out)
			setTimeout( function () {
				Refreshed.userToolsOpen = true;
			}, 300 );
		}
	} );

	$( document ).clickOrTouch( function ( e ) {
		if ( $( '#user-info .header-menu' ).is( ':visible' ) ) {
			if (
				Refreshed.userToolsOpen &&
				!$( '#user-info .header-menu' ).is( e.target ) &&
				$( '#user-info .header-menu' ).has( e.target ).length === 0
			)
			{
				// if the target of the click isn't the button, the container, or a descendant of the container
				$( '#user-info > a' ).removeClass( 'header-button-active' );
				$( '#user-info .header-menu' ).fadeOut();
				$( '#user-info .arrow' ).removeClass( 'rotate' );
				Refreshed.userToolsOpen = false;
			}
		}
	} );

	/* site navigation dropdown */
	$( '#site-info-main a.arrow-link' ).clickOrTouch( function() {
		if ( !$( '#site-info .header-menu' ).is( ':visible' ) ) {
			$( '#site-info .header-menu' ).fadeIn();
			$( '#site-info-main a.arrow-link' ).addClass( 'header-button-active' );
			$( '#site-info .arrow' ).addClass( 'rotate' );
			// delay the second clickOrTouch function (which performs fadeOut)
			// to stop fadeIn and fadeOut on one click (also prevents user from
			// spamming so it constantly fades in/out)
			setTimeout( function () {
				Refreshed.siteNavDropdown = true;
			}, 300 );
		}
	} );

	$( document ).clickOrTouch( function ( e ) {
		if ( $( '#site-info .header-menu' ).is( ':visible' ) ) {
			if (
				Refreshed.siteNavDropdown &&
				!$( '#site-info .header-menu' ).is( e.target ) &&
				$( '#site-info .header-menu' ).has( e.target ).length === 0
			)
			{
				// if the target of the click isn't the container and isn't a descendant of the container
				$( '#site-info .header-menu' ).fadeOut();
				$( '#site-info-main a.arrow-link' ).removeClass( 'header-button-active' );
				$( '#site-info .arrow' ).removeClass( 'rotate' );
				Refreshed.siteNavDropdown = false;
			}
		}
	} );

	/* mobile sidebar */
	$( '#sidebar-shower' ).clickOrTouch( function() {
		//if (!Refreshed.sidebarOpen) {
			$( '#sidebar-wrapper' ).toggleClass( 'sidebar-open' );
			$( '#sidebar-shower' ).toggleClass( 'sidebar-open' );
			$( '#fade-overlay' ).toggleClass( 'fade-overlay-active' ); // toggle the fade overlay
			Refreshed.sidebarOpen = !Refreshed.sidebarOpen;
			$( this ).toggleClass( 'header-button-active' );
		//}
	} );

	$( document ).clickOrTouch( function ( e ) {
		if ( Refreshed.sidebarOpen && $( '#fade-overlay').is( e.target ) ) { // if the sidebar is out and the target of the click is the fade-overlay
			$( '#sidebar-wrapper' ).removeClass( 'sidebar-open' );
			$( '#sidebar-shower' ).removeClass( 'sidebar-open' );
			$( '#fade-overlay' ).removeClass( 'fade-overlay-active' ); // deactivate the fade overlay
			Refreshed.sidebarOpen = false;
			$( '#sidebar-shower' ).removeClass( 'header-button-active' );
		}
	} );

	$( '#small-toolbox-wrapper > a' ).clickOrTouch( function() {
		$( '#small-toolbox' ).css({'overflow': 'auto'}).animate({'width': '100%'}).addClass( 'scroll-shadow' );
		$( this ).css({'display': 'none'});
	} );

	$( '#icon-ca-watch, #icon-ca-unwatch' ).parent().clickOrTouch( function( e ) {
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

	/* user tools dropdown */
	$( '.page-item-has-children .clickable-region' ).clickOrTouch( function( e ) {
		// hide all the other page item dropdowns if they are visible
		$( '.page-item-has-children .children' ).fadeOut( 200 );
		$( '.page-item-has-children' ).removeClass( 'header-button-active' );
		$( '.page-item-has-children .arrow' ).removeClass( 'rotate' );
		if ( !$( this ).siblings( '.children' ).is( ':visible' ) ) {
			$( this ).siblings( '.children' ).fadeIn( 200 );
			$( this ).parent().addClass( 'header-button-active' );
			$( this ).children( '.arrow' ).addClass( 'rotate' );
		}
	} );

	$( document ).clickOrTouch( function ( e ) {
		if ( $( '.page-item-has-children .children' ).is( ':visible' ) ) {
			if ( $( '.page-item-has-children' ).has( e.target ).length === 0 ) { // if the target of the click isn't the button, the container, or a descendant of the container
				$( '.page-item-has-children .children' ).fadeOut( 200 );
				$( '.page-item-has-children' ).removeClass( 'header-button-active' );
				$( '.page-item-has-children .arrow' ).removeClass( 'rotate' );
			}
		}
	} );

	/**
	 * add "header-suggestions" class to first .suggestions element for CSS
	 * targeting (there is usually one .suggestions element, but on Special:Search
	 * there is one for the #header search bar and one for the #bodyContentsearch bar)
	 */
	setTimeout( function () { // wait a bit so the .suggestions elements can be added in (if we don't wait we'll be targeting nothing and it won't work)...
		$( '.suggestions' ).first().addClass( 'header-suggestions' ); // add class to first .suggestions element
	}, 100 );

} );

/* Fix for Echo in Refreshed */
if ( document.getElementById( 'echo' ) ) {
	$( '#pt-notifications' ).prependTo( '#echo' );
}

if ( $( '.mw-echo-notifications-badge' ).hasClass( 'mw-echo-unread-notifications' ) ) {
	$( '#pt-notifications-personaltools a' ).addClass( 'pt-notifications-personaltools-unread' );
}
