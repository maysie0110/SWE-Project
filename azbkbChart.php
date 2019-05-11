<?php
require './vendor/autoload.php';
require_once './Convertor2.php';
require './.config.php';
require_once './DbManager.php';

//connect to db
$con=new Database($HOST, $DBNAME, $DBUSERNAME, $DBPASSWORD);
//get data of Azbio and BKB tests from the dbmanager
$data=$con->AzbioBkbData();

$dataPoints1=[];
$linePoints1= array();

$x = array_keys($data[0]);
$y = array_values($data[0]);
    
$line = linear_regression($x, $y);

foreach ($data[0] as $key => $value) {
	
    array_push($dataPoints1, (array("x" => intval($key), "y" => doubleval($value))));
}
for( $i=0;$i<100;$i++)
{
	$answer=$line['slope']*$i+$line['intercept'];
	array_push($linePoints1, (array("x" => $i, "y" => doubleval($answer))));

}
$r=correlation($x,$y);

?>
<!DOCTYPE HTML>
<html>
<head>
<script>
window.onload = function () {

var chart = new CanvasJS.Chart("chartContainer", {
	animationEnabled: true,
	title:{
		text: "Scores Relation"
	},
	axisX: {
		title:"AzBio Scores"
	},
	axisY:{
		title: "BKB Scores"
	},
	legend:{
		cursor: "pointer",
		itemclick: toggleDataSeries
	},
	data: [
	{
		type: "scatter",
		name: "BKB Score",
		markerType: "square",
		showInLegend: true,
		dataPoints: <?php echo json_encode($dataPoints1); ?>,
        color: "Indigo"
	},
	{
		type: 'line',
		name: "BKB Score Line",
		
		showInLegend: true,
		dataPoints: <?php echo json_encode($linePoints1, JSON_NUMERIC_CHECK); ?>,
		showLine:true
	}],
});

chart.render();

function toggleDataSeries(e){
	if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
		e.dataSeries.visible = false;
	}
	else{
		e.dataSeries.visible = true;
	}
	chart.render();
}

}
</script>
</head>
<body>
<div id="chartContainer" style="height: 370px; width: 100%;"></div>
<div><h5><?php echo $r?></h5></div>
<!-- <div><?php 
// foreach ($dataPoints1 as $key=>$value){
// 	print_r($dataPoints1[$key]);
// }
?></div> -->
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html>