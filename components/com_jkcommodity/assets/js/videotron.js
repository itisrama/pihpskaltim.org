jQuery.noConflict();
(function($) {
	$(window).load(function() {
		var page = parseInt($('#pricePage').val());
		var data = $.parseJSON($('#priceData').val());
		var length = data.length;
		var table = $("#videotronTable tbody");

		setInterval(loadData, 10000);
		function loadData() {
			page++;
			if(page >= length) {
				page = 0;
				$('#pricePage').val(page);
			}
			items = data[page];

			$("tr", table).each(function(row) {
				var item = items[row] || [];
				$("td", this).each(function(column) {
					$("span", this).html(item[column] || '&nbsp;');
				});
			});
		}
		
		$('#runningText').marquee({
			duration: 20000,
			duplicated: true
		});

		refreshAt(8,0,0);
		refreshAt(13,0,0);
		refreshAt(17,0,0);
		refreshAt(21,0,0);
		function refreshAt(hours, minutes, seconds) {
			var now = new Date();
			var then = new Date();

			if(now.getHours() > hours ||
			 	(now.getHours() == hours && now.getMinutes() > minutes) ||
				now.getHours() == hours && now.getMinutes() == minutes && now.getSeconds() >= seconds) {
				then.setDate(now.getDate() + 1);
			}
			then.setHours(hours);
			then.setMinutes(minutes);
			then.setSeconds(seconds);

			var timeout = (then.getTime() - now.getTime());
			setTimeout(function() { window.location.reload(true); }, timeout);
		}
	});	
})(jQuery);