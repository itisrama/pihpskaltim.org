jQuery.noConflict();
(function($) {
	$(function() {
		$('#adminForm').submit(function(){
			var start = $('#filter_start_date').val().split('-');
			var end = $('#filter_end_date').val().split('-');
			var url = $('#filter_integration_url').val();
			var requests = [];
			var record = [];

			start = new Date(start[2],start[1]-1,start[0]);
			end = new Date(end[2],end[1]-1,end[0]);

			for (var d = start; d <= end; d.setDate(d.getDate() + 1)) {
				var today = [
					start.getFullYear(),
					("0"+(start.getMonth()+1)).slice(-2),
					("0"+start.getDate()).slice(-2)
				].join('-')
				
				$('#filter_province_ids :selected').each(function() {
					var curUrl = url;
					var province_id = $(this).val();
					var province = $(this).text();
					record[province_id] = 0;
					curUrl = curUrl.replace('{province_id}', province_id);
					curUrl = curUrl.replace('{date}', today);
					requests.push([province, today, curUrl, province_id]);
				});
			}

			$('#report').hide();
			$('#progress').hide();
			$('#alert').show();
			$('#report .result').empty();
			$('#adminForm .btn-primary').attr('disabled', true);

			function doNextAjax(i) {
				if (i < requests.length) {
					var percent = Math.round(i / requests.length * 100);
					var page = i;
					jQuery.ajax({
						url: requests[i][2]+'&record='+record[requests[i][3]],
						type: "GET",
						timeout: 100000,
						dataType: "json",
						cache: false,
						success: function(response) {
							if(i < 1) {
								$('#alert').hide();
								$('#report').show();
								$('#progress').show();
								$('#progress .percent').hide();
							}

							var status = null;
							switch(response.status) {
								case 'danger':
									status = '<i class="fa fa-times"></i>'
									break;
								case 'warning':
									status = '<i class="fa fa-exclamation-triangle"></i>'
									break;
								case 'success':
									status = '<i class="fa fa-check"></i>'
									break;
							}
							status = '<span class="label label-'+response.status+'">'+status+'</span>'
							record[requests[i][3]] = response.status == 'success' ? 1 : record[requests[i][3]];
							var row = $('#report .template tr').clone();
							$('.num', row).html(i+1);
							$('.province', row).html(requests[i][0]);
							$('.date', row).html(requests[i][1]);
							$('.status', row).html(status+' '+response.message);
							page++
							row.prependTo('#report .result');
							if(page > 25) {
								$('#report .result tr:last').remove();
							}
						},
						complete: function() {
							if(percent > 10) {
								$('#progress .percent').show();
							} else if(percent == 100) {
								percent--;
							}
							$('#progress .progress-bar').css('width', percent + '%');
							$('#progress .percent span').text(percent);
							doNextAjax(page);
						}
					});
				} else {
					var percent = 100;
					$('#progress .progress-bar').css('width', percent + '%');
					$('#progress .percent span').text(percent);
					$('#adminForm .btn-primary').removeAttr('disabled');
				}
			}
			doNextAjax(0);

			return false;
		});
	});

})(jQuery);