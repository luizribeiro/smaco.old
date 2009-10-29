<?php
$tfiles = array(
	array("home.php"),
	array("contests.php"),
	array("ranking.php"),
	array("options.php"),
	array("help.php"));
$tnames = array(
	"Home",
	"Contests",
	"Ranking",
	"Opções",
	"Ajuda");

$self = $_SERVER["PHP_SELF"];
$parts = explode("/", $self);
$file = $parts[count($parts)-1];

echo "		<ul class=\"tab\">\n";
for($i = 0; $i < count($tfiles); $i++) {
	if(in_array($file, $tfiles[$i])) echo "			<li class=\"active\"><a href=\"".$tfiles[$i][0]."\"><span>".$tnames[$i]."</span></a></li>\n";
	else echo "			<li><a href=\"".$tfiles[$i][0]."\"><span>".$tnames[$i]."</span></a></li>\n";
}
echo "			<li id=\"logout\"><a href=\"logout.php\"><span>Logout</span></a></li>\n";
echo "		</ul>\n";
?>
