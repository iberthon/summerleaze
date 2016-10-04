//-----------------------------------------------------------------------------
// Copyright (c)2012, Summerleaze Computer Services
//-----------------------------------------------------------------------------

(function($) {
	//This executes when the DOM is ready...
	$(document).ready(function() {
		//console.log('Running Deadline Script...');
		countdown();
	});
	
	// This executes when images have finished loading
	$(window).load(function() {
	});

})(jQuery);

function countdown() {
	var now = new Date()
	var txt = '';

	var data_deadline = jQuery('#countdown').attr('data-deadline');

	var x = data_deadline.split(/(\d+).(\d+).(\d+).(\d+).(\d+)/g);
	var deadline = new Date(x[1], x[2]-1, x[3], x[4], x[5], 0, 0);

	//console.log(data_deadline + ' - Deadline: ' + deadline + ' - Now: ' + now);

	if (deadline.getTime() > now.getTime()) {
		var how_long = deadline.getTime() - now.getTime();
		var one_min  = 60*1000;
		var one_hour = one_min*60;
		var one_day  = one_hour * 24;

		var d = Math.floor(how_long / one_day);
		var h = Math.floor((how_long % one_day) / one_hour);
		var m = Math.floor(((how_long % one_day) % one_hour) / one_min);
		var s = Math.floor((((how_long % one_day) % one_hour) % one_min) / 1000);

		var l = (d == 1 ? 'day' : 'days');
		txt += '<span class="box">' + d +'<span class="label"> ' + l + '</span></span> ';

		l = (h == 1 ? 'hour' : 'hours');
		txt += '<span class="box">' + h + ' <span class="label"> ' + l + '</span></span> ';

		l = (m == 1 ? 'minute' : 'minutes');
		txt += '<span class="box">' + m + '<span class="label"> ' + l + '</span></span> ';

		l = (s == 1 ? 'second' : 'seconds');
		txt += '<span class="box">' + s + ' <span class="label"> ' + l + '</span></span>';
	}
	else {
		txt = '<span class="box">' + deadline.toString() + '</span>';
	}
	jQuery('#countdown').html(txt);
	setTimeout("countdown()", 1000);
}
