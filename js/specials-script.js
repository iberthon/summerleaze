//-----------------------------------------------------------------------------
// Copyright (c)2011, Summerleaze Computer Services
//-----------------------------------------------------------------------------

(function($) {
	$(document).ready(function() {
		console.info('[Loaded specials-script.js]');
		
		// Position the 'fold' correctly
		var max_margin = 0;
		$('.boxed-content li').each(function() {
			var ribbon = $(this);

			var h = ribbon.find('h6').innerHeight();
			var m = ribbon.find('h6').css('margin-left');

			console.log('Height: %d Margin: %d', h, m);

			var x = h + parseInt(m);
			max_margin = (x > max_margin) ? x : max_margin;
			
			ribbon.find('div:first').height(x);
			ribbon.find('.content').css('margin-top', h + parseInt(m));
		});
		$('.boxed-content li .content').css('margin-top', max_margin+5);
		
	});
})(jQuery);
