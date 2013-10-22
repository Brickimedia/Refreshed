/*refreshed.js*/
heights = [];

function getHeight(self){
	var id = self.attr('data-to').substring(1);
	var numid = self.attr('data-numid');
	var to = $(document.getElementById(id));
	var heightTo = to.offset().top - 50;
	heights[numid] = heightTo;
	return heightTo;
}

function moveBoxTo(height){
	var heightAbove = 0;
	var idAbove;
	heights.forEach(function(elem, index){
		if(elem <= height){
			heightAbove = elem;
			idAbove = index;
		}
	});
	var idBelow = idAbove + 1;
	var heightBelow = heights[idBelow];
	
	var heightDiff = heightBelow - heightAbove;
	var heightMeRelative = height - heightAbove;
	var fractMe = heightMeRelative / heightDiff;

	var elemAbove = $("a[data-numid="+idAbove+"]");
	var elemAboveOffset = elemAbove.position().top;
	var elemBelow = $("a[data-numid="+idBelow+"]");
	var elemBelowOffset = elemBelow.position().top;
	var elemOffsetDiff = elemBelowOffset - elemAboveOffset;
	var goTo = elemAboveOffset + (elemOffsetDiff * fractMe);
	
	$("#toc-box").stop().animate({'top': goTo}, 200);
}

i = 0;
$("#refreshed-toc a").each(function(){
	var href = $(this).attr('href');
	$(this).attr({'data-to': href});
	$(this).attr({'data-numid': i});
	i++;
});

$("#refreshed-toc a").each(function(){
	getHeight($(this));
});

$("#refreshed-toc a").click(function(){
	event.preventDefault();
	var heightTo = getHeight($(this));
	$("html, body").animate({scrollTop: heightTo}, 800);
	return false;
});

$(window).scroll(function(){
	if($(".toctext").length != 0){
		moveBoxTo($(this).scrollTop());
	}
});

var smaller = false;

onScroll = function(){
	var pos = $("#toc-box").position().top;
	var height = $("#leftbar-bottom").height();
	var goTo = pos - (height/2);
	goTo = goTo + $("#refreshed-toc a").height();
	
	$("#leftbar-bottom").scrollTop(goTo);
}

function overlap(){
	var bottom = $("#leftbar-top").position().top + $("#leftbar-top").outerHeight();
	var top3 = $("#refreshed-toc").outerHeight();
	var overlap = $(window).height() - top3 - bottom;
	overlap = overlap - 10;

	if(overlap < 0){
		var newheight = $("#leftbar-bottom div").outerHeight() + overlap;
		$("#leftbar-bottom").height(newheight);
		$("#leftbar-bottom").css({'overflow-y': 'scroll', 'bottom': '0', 'direction': 'rtl'});
	
		$(window).scroll(onScroll);
	} else {
		$("#leftbar-bottom").height('auto');
		$("#leftbar-bottom").css({'overflow-y': 'auto', 'bottom': '1em', 'direction': 'ltr'});
		
		$(window).off("scroll", onScroll);
	}
}

$(window).resize(overlap);
overlap();

var user = false;
var header = false;
var left = false;
var right = false;

function toggleUser(){
	$("#userinfo .headermenu").fadeToggle(150);
	$("#userinfo .arrow").toggleClass("rotate");
	user = !user;
}
function toggleSite(){
	$("#siteinfo .headermenu").fadeToggle(150);
	$("#siteinfo .arrow").toggleClass("rotate");
	header = !header;
}
function toggleLeft(){
	if(left){
		$("#leftbar").animate({'left': '-12em'});
	} else {
		$("#leftbar").animate({'left': '0em'});
	}
	$("#leftbar .shower").fadeToggle();
	left = !left;
}
function toggleRight(){
	if(right){
		$("#rightbar").animate({'right': '-12em'});
	} else {
		$("#rightbar").animate({'right': '0em'});
	}
	$("#rightbar .shower").fadeToggle();
	right = !right;
}

$("#userinfo > a").click(function(){
	toggleUser();
});

$("#siteinfo > a").click(function(){
	toggleSite();
});

$("#leftbar .shower").click(function(){
	toggleLeft();
	if(right){
		toggleRight();
	}
});

$("#rightbar .shower").click(function(){
	toggleRight();
	if(left){
		toggleLeft();
	}
});

$("#contentwrapper").click(function(){
	if(left){
		toggleLeft();
	} 
	if(right){
		toggleRight();
	} 
	if(user){
		toggleUser();
	}
	if(header){
		toggleHeader();
	}
});