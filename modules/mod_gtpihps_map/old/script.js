function drawChart () {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Country');
 	data.addColumn('number', 'Value'); 
    data.addColumn({type:'string', role:'tooltip'})

    jQuery.each(map_data, function(k,i) {
    	data.addRows([[{v:i.iso_code,f:i.name},parseInt(i.value), i.tooltip]]);
    });

    var geochart = new google.visualization.GeoChart(document.getElementById('report-map'));
    var options = {
        region:"ID",
        resolution: 'provinces',
        keepAspectRatio: false,
        backgroundColor: '#B4D7FF',
        colorAxis: {minValue: 1, maxValue: 9,  colors: ['#27ae60', '#f1c40f', '#c0392b']}
    };
    
    geochart.draw(data, options);
}
google.load('visualization', '1', {packages:['geochart'], callback: drawChart});