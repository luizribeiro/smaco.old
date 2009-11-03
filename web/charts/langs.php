<?php
error_reporting(0);

include("../inc/db.inc.php");
include("pData.php");
include("pChart.php");

$ans = array();
$cnt = array();

$q = mysql_query("SELECT language, COUNT(*) AS count FROM runs WHERE uid=".$_GET["u"]." GROUP BY language ORDER BY language ASC");
if(mysql_num_rows($q) == 0) {
	$ans = array("C++");
	$cnt = array(0);
} else {
	while($r = mysql_fetch_assoc($q)) {
		$ans[] = $r["language"];
		$cnt[] = $r["count"];
	}
}

$dataSet = new pData;
$dataSet->AddPoint($cnt, "Count");
$dataSet->AddPoint($ans, "Language");
$dataSet->AddAllSeries();
$dataSet->SetAbsciseLabelSerie("Language");

$chart = new pChart(300, 200);
$chart->loadColorPalette("langs.pal");
$chart->drawFilledRoundedRectangle(7,7,293,193,5,240,240,240);
$chart->drawRoundedRectangle(5,5,295,195,5,230,230,230);

$chart->drawFilledCircle(122,102,70,200,200,200);

$chart->setFontProperties(getcwd()."/verdana.ttf", 8);
$chart->drawBasicPieGraph($dataSet->GetData(),$dataSet->GetDataDescription(),120,100,70,PIE_PERCENTAGE,255,255,218);
$chart->drawPieLegend(230,15,$dataSet->GetData(),$dataSet->GetDataDescription(),250,250,250);

$chart->Stroke();
?>
