var incomeDateFrom = "";
var incomeDateTo = "";
var expenseDateFrom = "";
var expenseDateTo = "";

// Load the Visualization API and the piechart package.
/** global: google */
google.charts.load('current', {'packages':['corechart']});

// Set a callback to run when the Google Visualization API is loaded.
/** global: google */
google.charts.setOnLoadCallback(drawChart);

var $chartData;
var $chartName;


var jsonData = $.ajax({
    url: "{{ url('ajax_expense') }}",
    dataType: "json",
    async: false
}).responseText;


$chartData = $.parseJSON(jsonData);
$chartName = 'Expenses';


var options = {
    height: 350,
    title: $chartName,
    hAxis: {title: 'Day',  titleTextStyle: {color: '#333'}},
    vAxis: {title: 'Money', minValue: 0},
    animation: {"startup": true, duration: 500,}
};

function drawChart() {

    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Date');
    data.addColumn('number', $chartName);
    data.addRows($chartData);

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
    chart.draw(data, options);
}

$(window).resize(function(){
    drawChart();
});