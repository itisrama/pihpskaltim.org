jQuery.noConflict();
(function($) {
	$(document).ready(function(){
		var md = new MobileDetect(window.navigator.userAgent);
		if (md.mobile()) {
			switch(md.os()) {
				case 'AndroidOS':
					window.location = 'https://play.app.goo.gl/?link=https://play.google.com/store/apps/details?id=com.gamatechno.pihpsnasional';
					break;
				case 'iOS':
					window.location = 'pihps://';
					setTimeout("window.location = 'http://apple.co/2vWtMuF';", 1000);
					break;
				default:
					window.location = root_url;
					break;
			}
		} else {
			window.location = root_url;
		}
	});
})(jQuery);