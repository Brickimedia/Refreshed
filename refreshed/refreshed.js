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
	sidebarOpen: false,

	flyOutScrollHeader: function() {
		if ( $( '#contentwrapper' ).height() > $( window ).height() - $( '#header' ).height() && !Refreshed.standardToolboxIsDocked && ( $( '#standardtoolbox' ).offset().top - $( 'body' ).scrollTop() - $( '#header' ).height() < 0 ) ) { // first condition: only move the scroll header if the article content is bigger than the page (i.e. preventing it from being triggered when a user "rubber band scrolls" in OS X for example)
			//$( '#standardtoolboxscrolloverlay' ).animate({'top': $( '#header' ).height()});
			$( '#standardtoolboxscrolloverlay' ).addClass( 'dropdown-open' );
			Refreshed.standardToolboxIsDocked = true;
			$( '#maintitle > #standardtoolbox #standardtoolboxdropdown' ).fadeOut();
		} else if ( Refreshed.standardToolboxIsDocked && $( 'body' ).scrollTop() +  $( '#header' ).height() <= Refreshed.standardToolboxInitialOffset ) {
			Refreshed.standardToolboxIsDocked = false;
			//$( '#standardtoolboxscrolloverlay' ).animate({'top': -$( '#standardtoolboxscrolloverlay' ).height()});
			$( '#standardtoolboxscrolloverlay' ).removeClass( 'dropdown-open' );
			$( '#standardtoolboxscrolloverlay #standardtoolboxdropdown' ).fadeOut();
		}
	},

	generateScrollHeader: function() {
		$( '#standardtoolbox' ).clone().attr( 'id', 'standardtoolboxscrolloverlay' ).insertAfter( '#standardtoolbox' );
		//$( '#standardtoolboxscrolloverlay' ).css({'top': -$( '#standardtoolboxscrolloverlay' ).height()});
		if ( $( '#standardtoolboxscrolloverlay' ).outerWidth() != $( '#content' ).outerWidth() ) { //if standardtoolboxoverlay hasn't has its width set by CSS calc
			$( '#standardtoolboxscrolloverlay' ).css({'width': $( '#content' ).outerWidth() - ( $( '#standardtoolboxscrolloverlay' ).outerWidth() - $( '#standardtoolboxscrolloverlay' ).width() )}); // set #standardtoolboxscrolloverlay's width to the width of #content minus #standardtoolboxscrolloverlay's padding (and border, which is 0)
		}
		Refreshed.scrollHeaderHasBeenGenerated = true;
	},

	resizeScrollHeader: function() {
		$( '#standardtoolboxscrolloverlay' ).css({'width': $( '#content' ).outerWidth() - ( $( '#standardtoolboxscrolloverlay' ).outerWidth() - $( '#standardtoolboxscrolloverlay' ).width() )}); // set #standardtoolboxscrolloverlay's width to the width of #content minus #standardtoolboxscrolloverlay's padding (and border, which is 0)
	}
};

$( document ).ready( function() {
	if ( navigator.userAgent.toLowerCase().match(/(iPad|iPhone|iPod)/i) ) { //detect if on iOS device
		Refreshed.usingIOS = true;
	}
	if ( $( window ).width() < Refreshed.thresholdForSmallCSS ) {
		Refreshed.windowStartedSmall = true;
	} else if ( $( window ).width() >= Refreshed.thresholdForBigCSS ) {
		Refreshed.windowIsBig = true;
	}
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
	$( '#searchshower' ).click( function() { //Unfortunately, touchend causes the search bar to lose focus in iOS (haven't tested on Android), but it keeps its focus if you use the standard click event. The other menus, etc. use "touchOrClick" b/c the touchend event seems to execute faster than the standard click on iOS (once again, haven't tested on Android).
			if ( !$( '#search' ).is( ':visible' ) ) {
				$( '#search' ).fadeIn();
				$( '#search input' ).focus();
				$( this ).toggleClass( 'dropdown-highlighted' );
				setTimeout(function () {
					Refreshed.searchDropdownOpen = true;
				}, 300); //delay the second clickOrTouch function (which performs fadeOut) to stop fadeIn and fadeOut on one click (also prevents user from spamming so it constantly fades in/out)
	}
	});

	$( document ).clickOrTouch( function ( e ) {
		if ( $( '#search' ).is( ':visible' ) && $( window ).width() < Refreshed.thresholdForBigCSS ) { // window size must be checked because we only want to hide the search bar if we're not in "big" mode
			if ( Refreshed.searchDropdownOpen && !$( '#search' ).is( e.target ) && $( '#search' ).has( e.target ).length === 0 ) { // if the target of the click isn't the container and isn't a descendant of the container
				$( '#search' ).fadeOut();
				$( '#search input' ).val( '' );
				$( '#searchshower' ).removeClass( 'dropdown-highlighted' );
				Refreshed.searchDropdownOpen = false;
			}
		}
	});

	/* user tools dropdown */
	/* touch (mobile) */
	$( '#userinfo > a' ).clickOrTouch( function() {
		if ( !$( '#userinfo .headermenu' ).is( ':visible' ) ) {
    	$( '#userinfo .headermenu' ).fadeIn();
      $( this ).addClass( 'dropdown-highlighted' );
      $( '#userinfo .arrow' ).addClass( 'rotate' );
  		setTimeout(function () {
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

	/* hover */
		$( '#userinfo' ).hover(
				function() {
					if (Refreshed.windowIsBig) {
						$( this ).children('.headermenu').fadeIn(200);
						$( this ).addClass( 'dropdown-highlighted' );
						$( '#userinfo .arrow' ).addClass( 'rotate' );
					}
				},
				function(){
					if (Refreshed.windowIsBig) {
						$(this).children('.headermenu').fadeOut(200);
						$( this ).removeClass( 'dropdown-highlighted' );
						$( '#userinfo .arrow' ).removeClass( 'rotate' );
					}
				}
		);

	/* site navigation dropdown */
	$( '#siteinfo-main a.arrow-link' ).clickOrTouch( function() {
			if ( !$( '#siteinfo .headermenu' ).is( ':visible' ) ) {
				$( '#siteinfo .headermenu' ).fadeIn();
				$( '#siteinfo-main a.arrow-link' ).toggleClass( 'sitedropdown-highlighted' );
				$( '#siteinfo-main' ).toggleClass( 'sitedropdown-bg-highlighted' );
				$( '#siteinfo .arrow' ).toggleClass( 'rotate' );
				setTimeout(function () {
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
			if (!Refreshed.sidebarOpen) {
				//$( 'body' ).animate({'margin-left': '12em'}, 200);
				$( 'html' ).addClass( 'sidebar-open' );
				//$( '#sidebarwrapper' ).animate({'left': '0'}, 200);
				Refreshed.sidebarOpen = true;
				$( this ).addClass( 'dropdown-highlighted' );
			} else {
				//$( 'body' ).animate({'margin-left': '0'}, 200);
				//$( '#sidebarwrapper' ).animate({'left': '-12em'}, 200);
				$( 'html' ).removeClass( 'sidebar-open' );
				Refreshed.sidebarOpen = false;
				$( this ).removeClass( 'dropdown-highlighted' );
			}
	});

	$( document ).clickOrTouch( function ( e ) {
		if ( Refreshed.sidebarOpen && !$( '#sidebarshower' ).is( e.target ) && !$( '#sidebarwrapper' ).is( e.target ) && $( '#sidebarwrapper' ).has( e.target ).length === 0 ) { // if the sidebar is out and the target of the click isn't the shower, the container, or a descendant of the container
			//$( 'body' ).animate({'margin-left': '0'}, 200);
			$( 'html' ).removeClass( 'sidebar-open' );
			//$( '#sidebarwrapper' ).animate({'left': '-12em'}, 200);
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

} );

/* For whatever reason, if this line is not here, you can't hide shown elements (i.e. user info, site info, etc.) by clicking outside of them. */
$( '#contentwrapper' ).clickOrTouch( function() {
})

/* Fix for Echo in Refreshed */
if ( document.getElementById( 'echo' ) ) {
	$('#pt-notifications').prependTo('#echo');
}

/* Header categories */
$('.page_item_has_children').hover(
    function() {
       $(this).children('.children').fadeIn(200);
    },
    function(){
       $(this).children('.children').fadeOut(200);
    }
);
