<?php
require './vendor/autoload.php';
require_once './Convertor2.php';
require './.config.php';
require_once './DbManager.php';

//connect to db
$con=new Database($HOST, $DBNAME, $DBUSERNAME, $DBPASSWORD);
//get data of Azbio and BKB tests from the dbmanager
$data=$con->AzbioCncData();

$dataPoints1 = array();
$dataPoints2 = array();
$linePoints1= array();
$linePoints2= array();


foreach ($data[0] as $key => $value) {
	
    array_push($dataPoints1, (array("x" => doubleval($key), "y" => intval($value))));
}
foreach ($data[1]  as $key => $value) {
    array_push($dataPoints2, (array("x" => doubleval($key), "y" => intval($value))));
}

$x = array_keys($data[0]);
$y = array_values($data[0]);
$y2 = array_values($data[1]);
    

$line = linear_regression($x, $y);
$line2 = linear_regression($x, $y2);

for( $i=0;$i<100;$i++)
{
	$answer=$line['slope']*$i+$line['intercept'];
	array_push($linePoints1, (array("x" => $i, "y" => intval($answer))));

	$answer2= $line2['slope']*$i +$line2['intercept'];
	array_push($linePoints2, (array("x" => $i, "y" => intval($answer2))));
}

$r=correlation($x,$y);
$r2=correlation($x,$y2);
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
		title:"Azbio Scores"
	},
	axisY:{
		title: "CNC Scores"
	},
	legend:{
		cursor: "pointer",
		itemclick: toggleDataSeries
	},
	data: [
	{
		type: "scatter",
		name: "Phonemes Correct",
		markerType: "square",
		showInLegend: true,
		dataPoints: <?php echo json_encode($dataPoints1); ?>
	},
	{
		type: "scatter",
		name: "Words with 3 Phonemes Correct",
		markerType: "triangle",
		showInLegend: true,
		dataPoints: <?php echo json_encode($dataPoints2); ?>
	},
	{
		type: 'line',
		name: "Phonemes Correct Line",
		
		showInLegend: true,
		
		dataPoints: <?php echo json_encode($linePoints1, JSON_NUMERIC_CHECK); ?>,
		showLine:true
	},
	
	{
		type: 'line',
		name: "Words with 3 Phonemes Correct Line",
		
		showInLegend: true,
		dataPoints: <?php echo json_encode($linePoints2, JSON_NUMERIC_CHECK); ?>,
		showLine:true
	}
	
	]
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
<div><h5><?php echo $r2?></h5></div>
<!-- 
<div><?php print_r(json_encode($linePoints1));?></div>
	<div>
	<?php 
// 	foreach ($dataPoints1 as $key=>$value){
// 	print_r($dataPoints1[$key]);
// }
?>
</div> -->
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html>