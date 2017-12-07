jQuery.noConflict();
(function($) {
	$(function() {
		function initialize() {
			var mapOptions = {
				center: new google.maps.LatLng(-7.796378, 110.396805),
				zoom: 10,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			var map = new google.maps.Map($("#"+canvas_id)[0], mapOptions);
			setMarkers(map, markers);
		}
		
		function setMarkers(map, locations) {
			locations = $.parseJSON(locations);
			$.each(locations, function(i, location){
				var myLatLng = new google.maps.LatLng(location.latitude, location.longitude);
				var marker = new google.maps.Marker({
					position: myLatLng,
					map: map,
					title: location.name,
					zIndex: i,
					description: location.description
				});
				google.maps.event.addListener(marker, 'click', function(){
					$("#"+canvas_id).children('.map_info').remove();
					var obj = $('<div class="map_info"></div>');
					
					obj.html(obj.html(marker.description).text());
					obj.css('position', 'absolute');
					obj.css('top', '40px');
					obj.css('right', '10px');
					obj.css('padding', '10px');
					obj.css('height', '70%');
					obj.css('overflowY', 'scroll');
					obj.css('zIndex', 100);
					obj.css('backgroundColor', 'rgba(255,255,255,0.9)');
					obj.appendTo($("#"+canvas_id));
					
					var close_obj = $('<button class="close">&times;</button>');
					close_obj.bind('click', function(){
						$(this).parent().remove();
					})
					close_obj.css('position', 'absolute');
					close_obj.css('top', '7px');
					close_obj.css('right', '9px');
					close_obj.appendTo(obj);
				});
		  });
		}
		
		initialize();
	});
})(jQuery);