	<div id="header">
		<h1>sudo make a contest</h1>
<?php
$self = $_SERVER["PHP_SELF"];
$parts = explode("/", $self);
$file = $parts[count($parts)-1];

$tfiles[] = array("index.php", "home.php");
$tnames[] = "Home";

if(!session_is_registered("smacoid")) {
	$tfiles[] = array("cadastro.php");
	$tnames[] = "Cadastro";
} else {
	$tfiles[] = array("contests.php", "newcontest.php", "scoreboard.php", "runlist.php");
	$tnames[] = "Contests";

	$tfiles[] = array("ranking.php", "profile.php");
	$tnames[] = "Ranking";

	$tfiles[] = array("options.php");
	$tnames[] = "Opções";
}

echo "		<ul class=\"tab\">\n";
for($i = 0; $i < count($tfiles); $i++) {
	if(in_array($file, $tfiles[$i])) echo "			<li class=\"active\"><a href=\"".$tfiles[$i][0]."\"><span>".$tnames[$i]."</span></a></li>\n";
	else echo "			<li><a href=\"".$tfiles[$i][0]."\"><span>".$tnames[$i]."</span></a></li>\n";
}

if(session_is_registered("smacoid"))
	echo "			<li id=\"logout\"><a href=\"logout.php\"><span>Logout</span></a></li>\n";
echo "		</ul>\n";
?>
	</div>
