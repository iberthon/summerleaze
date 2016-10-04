InfoMsg('Loading: start-slider.js');

function slide(src, from, to, time, alt) {
	this['src']  = src.length > 0 ? src : '';
	this['from'] = from.length > 0 ? from : '0% 0% 1x';
  this['to']   = to.length > 0 ? to : '0% 0% 1x';
  this['time'] = time.length > 0 ? time : 20;
	this['alt']  = alt.length > 0 ? alt : '';
}

(function($) {
	$(document).ready(function() {
		var slides = [];

		$('.sliders').children('img').each(function(index) {
		  src  = $(this).attr('src');
		  from = $(this).data('from');
		  to   = $(this).data('to');
		  time = $(this).data('time');
			alt  = $(this).attr('alt');

		  //console.log( index + ". SRC:" + src + ', FROM:' + from + ', TO:' + to + ', TIME:' + time + ', ALT:' + alt);
			slides.push(new slide(src, from, to, time, alt));
		});
		$('.sliders').empty().show();

		startSlider('.sliders', 1, slides);
	});

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
			console.log('Slide...');
	  	if (idxOut == undefined) {
	    	// starting single image phase, put up caption
				if (img.alt.length > 0) {
					jQuery('.slider-caption').html(img.alt);

					var fw = jQuery('.slider-caption').parent().innerWidth();
					var w = jQuery('.slider-caption').innerWidth();
					var l = (fw/2) - (w/2);
					
					//console.log('%s FrameWidth: %s CaptionWidth: %s Left: %s', jQuery('.slider-caption').parent().attr('class'), fw, w, l);
		    	
		    	jQuery('.slider-caption').css('left', l).animate({ opacity: op });
		    	//jQuery('.caption').animate({ opacity: op });
				}
				LogMsg('Slide: [%s ~ %s]', img.src, img.alt);
	  	}
	  	else {
	    	// starting cross-fade phase, take out caption
	    	jQuery('.slider-caption').animate({ opacity: 0 });
	    }
	  });
		jQuery('.slider-caption').css({ opacity: 0 });
		InfoMsg('Cross-Slider should now be running...');
	}

})(jQuery);
