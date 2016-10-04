//-----------------------------------------------------------------------------
// Copyright (c)2011, Summerleaze Computer Services
//-----------------------------------------------------------------------------

(function($) {
	$(document).ready(function() {
		console.info("Loading: MAP-SCRIPT.JS");
		var theMap = $('.map');

		var lat = $('.map').data("lat");
		var lon = $('.map').data("lon");
		var mlat = $('.map').data("mlat");
		var mlon = $('.map').data("mlon");
		var mapTitle = $('.map').data("title");
		var mapId = $('.map').attr("id");
		var h = $('.map').data("height");
		var z = $('.map').data("zoom");
		var zoom_bounds = false;
		
		if(typeof(z) !== "number") {
			zoom_bounds = true;
			z = 12;
		}

		$('.map').height(h);

		var posMap = new google.maps.LatLng(lat, lon);
    var mapOptions = {
      zoom: z,
      center: posMap,
      mapTypeId: google.maps.MapTypeId.HYBRID
			//mapTypeId: google.maps.MapTypeId.SATELLITE
			//mapTypeId: google.maps.MapTypeId.ROADMAP
			//mapTypeId: google.maps.MapTypeId.HYBRID
			//mapTypeId: google.maps.MapTypeId.TERRAIN

    };
    var map = new google.maps.Map(document.getElementById(mapId), mapOptions);

		var markers = [];
		var bounds = new google.maps.LatLngBounds();
		
		var pins = $('.map').data("pins").split(';');
		for(var i=0; i<pins.length; i++) {
			if(pins[i].indexOf(':') != -1) {
				var pin_data = pins[i].split(':');
				var pin = pin_data[0].split(',');
				var title = pin_data[1];
			}
			else {
				var pin = pins[i].split(',');
				var title = mapTitle;
			}

			console.debug("pin #%d: lat:'%2.3f' lon:'%2.3f' title:'%s'", i, pin[0], pin[1], title);

			var letter = String.fromCharCode("A".charCodeAt(0) + i);
			var pos = new google.maps.LatLng(pin[0], pin[1]);
			var marker = new google.maps.Marker({
		    animation: google.maps.Animation.DROP,
		    icon: "https://maps.google.com/mapfiles/marker" + letter + ".png",
				map: map,
				position: pos,
				title: title
			});
			markers.push(marker);
		}

		// Centre the map on the markers
		if(zoom_bounds  === true) {
			for (var i = 0; i < markers.length; i++) {
				bounds.extend(markers[i].getPosition());
			}
			map.setCenter(bounds.getCenter());
			map.fitBounds(bounds);	
		}
		
	});
})(jQuery);

