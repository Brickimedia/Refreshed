/* global $ */
var Refreshed = {
	standardToolboxIsDocked: false,
	standardToolboxInitialOffset: $( '#standardtoolbox' ).offset().top,
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
	pageItemDropdownOpen: false,

	flyOutScrollHeader: function() {
		if ( $( '#contentwrapper' ).height() > $( window ).height() - $( '#header' ).height() && !Refreshed.standardToolboxIsDocked && ( $( '#standardtoolbox' ).offset().top - $( document ).scrollTop() - $( '#header' ).height() < 0 ) ) { // first condition: only move the scroll header if the article content is bigger than the page (i.e. preventing it from being triggered when a user "rubber band scrolls" in OS X for example)
			//$( '#standardtoolboxscrolloverlay' ).animate({'top': $( '#header' ).height()});
			$( '#standardtoolboxscrolloverlay' ).addClass( 'dropdown-open' );
			Refreshed.standardToolboxIsDocked = true;
			$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).fadeOut();
		} else if ( Refreshed.standardToolboxIsDocked && $( document ).scrollTop() +  $( '#header' ).height() <= Refreshed.standardToolboxInitialOffset ) {
			Refreshed.standardToolboxIsDocked = false;
			//$( '#standardtoolboxscrolloverlay' ).animate({'top': -$( '#standardtoolboxscrolloverlay' ).height()});
			$( '#standardtoolboxscrolloverlay' ).removeClass( 'dropdown-open' );
			$( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).fadeOut();
		}
	},

	generateScrollHeader: function() {
		$( '#standardtoolbox' ).clone().attr( 'id', 'standardtoolboxscrolloverlay' ).insertBefore( '#sidebarwrapper' );
		//$( '#standardtoolboxscrolloverlay' ).css({'top': -$( '#standardtoolboxscrolloverlay' ).height()});
		if ( $( '#standardtoolboxscrolloverlay' ).outerWidth() != $( '#content' ).outerWidth() ) { //if standardtoolboxoverlay hasn't has its width set by CSS calc
			$( '#standardtoolboxscrolloverlay' ).css({'width': $( '#content' ).outerWidth() - ( $( '#standardtoolboxscrolloverlay' ).outerWidth() - $( '#standardtoolboxscrolloverlay' ).width() )}); // set #standardtoolboxscrolloverlay's width to the width of #content minus #standardtoolboxscrolloverlay's padding (and border, which is 0)
		}
		Refreshed.scrollHeaderHasBeenGenerated = true;
	},

	resizeScrollHeader: function() {
		$( '#standardtoolboxscrolloverlay' ).css({'width': $( '#content' ).outerWidth() - ( $( '#standardtoolboxscrolloverlay' ).outerWidth() - $( '#standardtoolboxscrolloverlay' ).width() )}); // set #standardtoolboxscrolloverlay's width to the width of #content minus #standardtoolboxscrolloverlay's padding (and border, which is 0)
	},

	resizeSpecialSearchBar: function() {
		if ( !Refreshed.windowIsBig && !Refreshed.windowIsSmall ) { //if running medium.css
			Refreshed.widthOfSpecialSearchBar = $( '#content' ).width() - $( '.results-info' ).outerWidth() - $( '#content #search input[type="submit"]' ).outerWidth() - parseFloat( $( '#searchText' ).css( 'font-size' ) ); //set width of search bar to 100% of #content - "__ of __ results" text - width of submit button - width - 1em in the search bar */
			Refreshed.widthOfSpecialSearchPowerSearchBar = $( '#content' ).width() - $( '.results-info' ).outerWidth() - $( '#content #powersearch input[type="submit"]' ).outerWidth() - parseFloat( $( '#powerSearchText' ).css( 'font-size' ) );
		} else if ( Refreshed.windowIsSmall ) { //if running small.css
			Refreshed.widthOfSpecialSearchBar = $( '#content' ).width() - $( '#content #search input[type="submit"]' ).outerWidth() - parseFloat( $( '#searchText' ).css( 'font-size' ) ); //set width of search bar to 100% of #content - width of submit button - width - 1em in the search bar */
			Refreshed.widthOfSpecialSearchPowerSearchBar = $( '#content' ).width() - $( '#content #powersearch input[type="submit"]' ).outerWidth() - parseFloat( $( '#powerSearchText' ).css( 'font-size' ) );
		}
		$( '#searchText' ).css({'width': Refreshed.widthOfSpecialSearchBar});
		$( '#powerSearchText' ).css({'width': Refreshed.widthOfSpecialSearchPowerSearchBar});
	}
};

$( document ).ready( function() {
	if ( navigator.userAgent.toLowerCase().match(/(iPad|iPhone|iPod)/i) ) { //detect if on iOS device
		Refreshed.usingIOS = true;
	}

	if ( $( window ).width() < Refreshed.thresholdForSmallCSS ) {
		Refreshed.windowStartedSmall = true;
	}

	//test if window is running big.css
	if ( $( window ).width() >= Refreshed.thresholdForBigCSS ) {
		Refreshed.windowIsBig = true;
	} else {
		Refreshed.windowIsBig = false;
	}

	//test if window is running small.css
	if ( $( window ).width() <= Refreshed.thresholdForSmallCSS ) {
		Refreshed.windowIsSmall = true;
	} else {
		Refreshed.windowIsSmall = false;
	}

	Refreshed.resizeSpecialSearchBar();

	if (!Refreshed.usingIOS && !Refreshed.windowStartedSmall ) { //only perform if not on an iOS device (animations triggered by scroll cannot be played during scroll on iOS Safari) and if the window was running small.css when loaded
		Refreshed.generateScrollHeader();
		Refreshed.flyOutScrollHeader();
	}

	$( window ).scroll( function() {
		Refreshed.flyOutScrollHeader();
	});

	$( window ).resize( function() {
		if ( Refreshed.scrollHeaderHasBeenGenerated && $( '#standardtoolboxscrolloverlay' ).outerWidth() != $( '#content' ).outerWidth() ) { //only perform if the scroll header has already been generated and it needs to be resized (not already done by CSS calc)
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
	});

	/* tools dropdown attached to maintitle */
	$( '#maintitle > #standardtoolbox #toolbox-link' ).on({'click': function() {
			if ( !$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).is( ':visible' ) ) {
				$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).fadeIn();
			}
			$( this ).children().toggleClass( 'rotate' );
		},
		'hover': function() {
			$( this ).children().toggleClass( 'no-show' );
		}
	});

	$( document ).mouseup( function ( e ) {
		if ( $( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).is( ':visible' ) ) {
			if ( !$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).is( e.target ) && $( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).has( e.target ).length === 0 ) { // if the target of the click isn't the container and isn't a descendant of the container
				$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).fadeOut();
			}
		}
	});

	/* tools dropdown on the "scroll header" */
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

	$( document ).mouseup( function ( e ) {
		if ( $( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).is( ':visible' ) ) {
			if ( !$( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).is( e.target ) && $( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).has( e.target ).length === 0 ) { // if the target of the click isn't the container and isn't a descendant of the container
				$( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).fadeOut();
			}
		}
	});

	$.fn.extend({
		clickOrTouch: function(handler) {
				return this.each(function() {
						var event = ('ontouchend' in document) ? 'touchend' : 'mouseup';
						$(this).on(event, handler);
				});
		}
	});

	/* search dropdown */
	$( '#searchshower' ).click( function() { //Unfortunately, touchend causes the search bar to lose focus on iPhones on iOS 7 and iPads on iOS 8 (haven't tested on Android), but it keeps its focus if you used the standard click event. The other menus, etc. use "touchOrClick" b/c the touchend event executes faster than the standard click on iOS (once again, haven't tested on Android).
			if ( !Refreshed.searchDropdownOpen ) {
				if ( Refreshed.usingIOS ) {
					$( window ).scrollTop(0); //iOS tries to vertically center the search bar, scrolling to the top keeps the header at the top of the viewport
				}
				$( '#header #search' ).addClass( 'search-open' );
				$( '#sidebarshower' ).addClass( 'sidebarshower-hidden' );
				$( '#fade-overlay' ).addClass( 'fade-overlay-active fade-overlay-below-header' ); //toggle the fade overlay
				$( '#searchInput' ).focus();
				$( this ).toggleClass( 'dropdown-highlighted' );
				Refreshed.searchDropdownOpen = true;
			} else { //this only runs in "medium" mode (the search dropdown only appears in "medium" and "small" mode, and "small" is covered by the document.click function below)
				$( '#header #search' ).removeClass( 'search-open' );
				$( '#sidebarshower' ).removeClass( 'sidebarshower-hidden' );
				$( '#searchInput' ).blur().val( '' ); //deselect the search input and reset its contents (remove anything the user entered)
				$( '#fade-overlay' ).removeClass( 'fade-overlay-active fade-overlay-below-header' ); //toggle the fade overlay
				$( '#searchshower' ).removeClass( 'dropdown-highlighted' );
				Refreshed.searchDropdownOpen = false; //no delay needed because the spamming issue is only present on "small"
			}
	});

	$( document ).click( function ( e ) { //if you use clickOrTouch, pressing the .suggestions element will cause the window to close on mobile (maybe the clickOrTouch section is executed before a plain click and thus this is run and #search is hidden before the broswer acknowledges the click event on .suggestions to load the searched-for page?)
		if ( Refreshed.searchDropdownOpen && $( window ).width() < Refreshed.thresholdForBigCSS ) { // window size must be checked because we only want to hide the search bar if we're not in "big" mode
			if ( !$( '#header #search' ).is( e.target ) && !$( '#searchshower' ).is( e.target ) && !$( '#search input' ).is( e.target ) ) { // if the target of the click isn't the search container, search button, or the search box itself (we can't set it to all descendants of #search because #searchcloser needs to be able to close the search box)
				$( '#header #search' ).removeClass( 'search-open' );
				$( '#sidebarshower' ).removeClass( 'sidebarshower-hidden' );
				$( '#searchInput' ).blur().val( '' ); //deselect the search input and reset its contents (remove anything the user entered)
				$( '#fade-overlay' ).removeClass( 'fade-overlay-active fade-overlay-below-header' ); //toggle the fade overlay
				$( '#searchshower' ).removeClass( 'dropdown-highlighted' );
				setTimeout(function () {
					Refreshed.searchDropdownOpen = false;
				}, 375); //delay variable change for 375ms until after the animation is complete so both animations don't run on one press
			}
		}
	});

	/* user tools dropdown */
	$( '#userinfo > a' ).clickOrTouch( function() {
		if ( !$( '#userinfo .headermenu' ).is( ':visible' ) ) {
    	$( '#userinfo .headermenu' ).fadeIn();
      $( this ).addClass( 'dropdown-highlighted' );
      $( '#userinfo .arrow' ).addClass( 'rotate' );
  		setTimeout( function () {
        Refreshed.userToolsOpen = true;
    	}, 300); //delay the second clickOrTouch function (which performs fadeOut) to stop fadeIn and fadeOut on one click (also prevents user from spamming so it constantly fades in/out)
		}
	});

	$( document ).clickOrTouch( function ( e ) {
		if ( $( '#userinfo .headermenu' ).is( ':visible' ) ) {
			if ( Refreshed.userToolsOpen && !$( '#userinfo .headermenu' ).is( e.target ) && $( '#userinfo .headermenu' ).has( e.target ).length === 0 ) { // if the target of the click isn't the button, the container, or a descendant of the container
				$( '#userinfo > a' ).removeClass( 'dropdown-highlighted' );
				$( '#userinfo .headermenu' ).fadeOut();
				$( '#userinfo .arrow' ).removeClass( 'rotate' );
				Refreshed.userToolsOpen = false;
			}
		}
	});

	/* site navigation dropdown */
	$( '#siteinfo-main a.arrow-link' ).clickOrTouch( function() {
			if ( !$( '#siteinfo .headermenu' ).is( ':visible' ) ) {
				$( '#siteinfo .headermenu' ).fadeIn();
				$( '#siteinfo-main a.arrow-link' ).addClass( 'sitedropdown-highlighted' );
				$( '#siteinfo-main' ).addClass( 'sitedropdown-bg-highlighted' );
				$( '#siteinfo .arrow' ).addClass( 'rotate' );
				setTimeout( function () {
					Refreshed.siteNavDropdown = true;
				}, 300); //delay the second clickOrTouch function (which performs fadeOut) to stop fadeIn and fadeOut on one click (also prevents user from spamming so it constantly fades in/out)
			}
	});

	$( document ).clickOrTouch( function ( e ) {
		if ( $( '#siteinfo .headermenu' ).is( ':visible' ) ) {
			if ( Refreshed.siteNavDropdown && !$( '#siteinfo .headermenu' ).is( e.target ) && $( '#siteinfo .headermenu' ).has( e.target ).length === 0 ) { // if the target of the click isn't the container and isn't a descendant of the container
				$( '#siteinfo .headermenu' ).fadeOut();
				$( '#siteinfo-main a.arrow-link' ).removeClass( 'sitedropdown-highlighted' );
				$( '#siteinfo-main' ).removeClass( 'sitedropdown-bg-highlighted' );
				$( '#siteinfo .arrow' ).removeClass( 'rotate' );
				Refreshed.siteNavDropdown = false;
			}
		}
	});

	/* mobile sidebar */
	$( '#sidebarshower' ).clickOrTouch( function() {
			//if (!Refreshed.sidebarOpen) {
				$( '#sidebarwrapper' ).toggleClass( 'sidebar-open' );
				$( '#sidebarshower' ).toggleClass( 'sidebar-open' );
				$( '#fade-overlay' ).toggleClass( 'fade-overlay-active' ); //toggle the fade overlay
				Refreshed.sidebarOpen = !Refreshed.sidebarOpen;
				$( this ).toggleClass( 'dropdown-highlighted' );
			//}
	});

	$( document ).clickOrTouch( function ( e ) {
		if ( Refreshed.sidebarOpen && $( '#fade-overlay').is( e.target ) ) { // if the sidebar is out and the target of the click is the fade-overlay
			$( '#sidebarwrapper' ).removeClass( 'sidebar-open' );
			$( '#sidebarshower' ).removeClass( 'sidebar-open' );
			$( '#fade-overlay' ).removeClass( 'fade-overlay-active' ); //deactivate the fade overlay
			Refreshed.sidebarOpen = false;
			$( '#sidebarshower' ).removeClass( 'dropdown-highlighted' );
		}
	});


	$( '#smalltoolboxwrapper > a' ).clickOrTouch( function() {
		$( '#smalltoolbox' ).css({'overflow': 'auto'}).animate({'width': '100%'}).addClass( 'scrollshadow' );
		$( this ).css({'display': 'none'});
	});


	$( '#icon-ca-watch, #icon-ca-unwatch' ).parent().clickOrTouch( function( e ) {
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

	/* user tools dropdown */
	$( '.page_item_has_children .clickableregion' ).clickOrTouch( function( e ) {
		//hide all the other page item dropdowns if they are visible
		$( '.page_item_has_children .children' ).fadeOut(200);
		$( '.page_item_has_children' ).removeClass( 'dropdown-highlighted' );
		$( '.page_item_has_children .arrow' ).removeClass( 'rotate' );
		if ( !$( this ).siblings( '.children' ).is( ':visible' ) ) {
			$( this ).siblings( '.children' ).fadeIn(200);
			$( this ).parent().addClass( 'dropdown-highlighted' );
			$( this ).children( '.arrow' ).addClass( 'rotate' );
			setTimeout( function () {
				Refreshed.pageItemDropdownOpen = true;
			}, 300); //delay the second clickOrTouch function (which performs fadeOut) to stop fadeIn and fadeOut on one click (also prevents user from spamming so it constantly fades in/out)
		}
	});

	$( document ).clickOrTouch( function ( e ) {
		if ( $( '.page_item_has_children .children' ).is( ':visible' ) ) {
			if ( $( '.page_item_has_children' ).has( e.target ).length === 0 ) { // if the target of the click isn't the button, the container, or a descendant of the container
				$( '.page_item_has_children .children' ).fadeOut(200);
				$( '.page_item_has_children' ).removeClass( 'dropdown-highlighted' );
				$( '.page_item_has_children .arrow' ).removeClass( 'rotate' );
				Refreshed.pageItemDropdownOpen = false;
			}
		}
	});

	/* add "header-suggestions" class to first .suggestions element for CSS targeting (there is usually one .suggestions element, but on Special:Search there is one for the #header search bar and one for the #content search bar) */
	setTimeout( function () { //wait a bit so the .suggestions elements can be added in (if we don't wait we'll be targeting nothing and it won't work)...
		$( '.suggestions' ).first().addClass( 'header-suggestions' ); //add class to first .suggestions element
	}, 100);

} );

/* For whatever reason, if this line is not here, you can't hide shown elements (i.e. user info, site info, etc.) by clicking outside of them. */
$( '#contentwrapper' ).clickOrTouch( function() {
});

/* Fix for Echo in Refreshed */
if ( document.getElementById( 'echo' ) ) {
	$('#pt-notifications').prependTo('#echo');
}

/* Header categories */
/*$('.page_item_has_children').hover(
    function() {
       $(this).children('.children').fadeIn(200);
    },
    function(){
       $(this).children('.children').fadeOut(200);
    }
);*/
