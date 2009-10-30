	<div id="header">
		<h1>sudo make a contest</h1>
<?php
$tfiles[] = array("home.php");
$tnames[] = "Home";

$tfiles[] = array("contests.php", "newcontest.php", "scoreboard.php");
$tnames[] = "Contests";

$tfiles[] = array("ranking.php", "userinfo.php");
$tnames[] = "Ranking";

$self = $_SERVER["PHP_SELF"];
$parts = explode("/", $self);
$file = $parts[count($parts)-1];

if($file == "index.php") {
	$tfiles = array();
	$tnames = array();
}

echo "		<ul class=\"tab\">\n";
for($i = 0; $i < count($tfiles); $i++) {
	if(in_array($file, $tfiles[$i])) echo "			<li class=\"active\"><a href=\"".$tfiles[$i][0]."\"><span>".$tnames[$i]."</span></a></li>\n";
	else echo "			<li><a href=\"".$tfiles[$i][0]."\"><span>".$tnames[$i]."</span></a></li>\n";
}
if($file != "index.php")
	echo "			<li id=\"logout\"><a href=\"logout.php\"><span>Logout</span></a></li>\n";
echo "		</ul>\n";
?>
	</div>
