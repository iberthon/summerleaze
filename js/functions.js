//-----------------------------------------------------------------------------
// Copyright (c)2011, Summerleaze Computer Services
//-----------------------------------------------------------------------------

InfoMsg("Loading: FUNCTIONS.JS");

(function($) {
	$(document).ready(function() {
		/*
			call supersubs first, then superfish, so that subs are
			not display:none when measuring. Call before initialising
			containing tabs for same reason.
		*/
/*
		$("ul.menu").supersubs({
			minWidth:    5,   	// minimum width of sub-menus in em units
			maxWidth:    27,   	// maximum width of sub-menus in em units
			extraWidth:  1     	// extra width can ensure lines don't sometimes turn over due to slight rounding differences and font-family
		}).superfish({
				delay: 500,																		// half second delay on mouseout
	      animation:   {opacity:'show',height:'show'},  // fade-in and slide-down animation
	      speed:       'fast',                          // faster animation speed
	      autoArrows:  false                            // disable generation of arrow mark-up
		});

		$('#sidebar-footer .widget').each(function(i) {
			$(this).css('left', i*320 + 'px');
		});

		$('#sidebar-footer .widget').not(':last').hover(
	  	function () {
				$(this).animate({ height: "150px", top: "-125px" }, 750); //.css('border', '1px solid #ccc');
	  	},
	  	function () {
				 $(this).animate({ height: "25px", top: "0px" }, 750); //.css('border', '1px solid transparent');
	  	}
		);

		hiConfig = {
			sensitivity: 3, // number = sensitivity threshold (must be 1 or higher)
			interval: 200, // number = milliseconds for onMouseOver polling interval
			timeout: 200, // number = milliseconds delay before onMouseOut
			over: function() { $(this).animate({ height: "150px", top: "-125px" }, 750); },
      out: function()  { $(this).animate({ height: "25px", top: "0px" }, 750); }
    }
		$('#sidebar-footer .widget').not(':last').hoverIntent(hiConfig);

		var p = $('#sidebar-footer .widget:last').position();

		$('#sidebar-footer .widget:last').hover(
	  	function () {
				$(this).animate({ height: "325px", left: p.left - 280, top: "-300px", width: "600px" }, 750); //.css('border', '1px solid #ccc');
	  	},
	  	function () {
				$(this).animate({ height: "25px", left: p.left, top: "0px", width: "320px" }, 750); //.css('border', '1px solid transparent');
	  	}
		);

		hiConfig_last = {
			sensitivity: 3, // number = sensitivity threshold (must be 1 or higher)
			interval: 200, // number = milliseconds for onMouseOver polling interval
			timeout: 200, // number = milliseconds delay before onMouseOut
			over: function() { $(this).animate({ height: "325px", left: p.left - 280, top: "-300px", width: "600px" }, 750); },
      out: function()  { $(this).animate({ height: "25px", left: p.left, top: "0px", width: "320px" }, 750); }
    }
		$('#sidebar-footer .widget:last').hoverIntent(hiConfig_last);
*/

	});
})(jQuery);

function startSlider(el, op, sl) {
	el = typeof el !== 'undefined' ? el : '#banner';
	op = typeof op !== 'undefined' ? op : 0;
	// have we been passed an array of slides? if not see if the global var 'slides' has been defined...
	sl = typeof sl !== 'undefined' ? sl : (typeof slides !== 'undefined' ? slides : []);

	InfoMsg('Starting Cross-Slider...');

	jQuery(el).crossSlide({
		fade: 1,
		variant: true,
		easing: 'jswing'
	},
	sl,
	function(idx, img, idxOut, imgOut) {
		/*console.log('Slide...');*/
  	if (idxOut == undefined) {
    	// starting single image phase, put up caption
			if (img.alt.length > 0) {
				jQuery('.caption').html(img.alt);

				/*var fw = jQuery('.caption').parent().innerWidth();
				var w = jQuery('.caption').innerWidth();
				var l = (fw/2) - (w/2);*/
				/*console.log('FrameWidth: %d CaptionWidth: %d Left: %d', fw, w, l);*/
	    	/*jQuery('.caption').css('left', l).animate({ opacity: op });*/
	    	jQuery('.caption').animate({ opacity: op });
			}
			LogMsg('Slide: [%s ~ %s]', img.src, img.alt);
  	}
  	else {
    	// starting cross-fade phase, take out caption
    	jQuery('.caption').animate({ opacity: 0 });
    }
  });
	jQuery('.caption').css({ opacity: 0 });
	InfoMsg('Cross-Slider should now be running...');
}

function DebugMsg() {
	if (typeof(console) != 'undefined' && console.debug) {
    console.debug.apply(console, arguments);
	}
}

function ErrorMsg() {
	if (typeof(console) != 'undefined' && console.error) {
    console.error.apply(console, arguments);
	}
}

function InfoMsg() {
	if(typeof console != 'undefined' && console.info) {
		console.info.apply(console, arguments);
	}
}

function LogMsg() {
	if (typeof(console) != 'undefined' && console.log) {
    console.log.apply(console, arguments);
	}
}

function WarnMsg() {
	if (typeof(console) != 'undefined' && console.warn) {
    console.warn.apply(console, arguments);
	}
}

/*
function DebugMsg() {
	if (typeof(console) == 'function' && console.debug) {
    console.debug.apply(console, arguments);
	}
}

function ErrorMsg() {
	if (typeof(console) == 'function' && console.error) {
    console.error.apply(console, arguments);
	}
}

function InfoMsg() {
	if(typeof console == 'function' && console.info) {
		console.info.apply(console, arguments);
	}
}

function LogMsg() {
	if (typeof(console) == 'function' && console.log) {
    console.log.apply(console, arguments);
	}
}

function WarnMsg() {
	if (typeof(console) == 'function' && console.warn) {
    console.warn.apply(console, arguments);
	}
}
*/