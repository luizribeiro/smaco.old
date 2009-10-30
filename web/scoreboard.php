<?php
include("inc/db.inc.php");
include("inc/judges.inc.php");

session_start();
if(!session_is_registered("smacoid")) header("Location: index.php?msg=require");
if(!isset($_GET["c"])) header("Location: contests.php");

$r = mysql_query("SELECT nome, inicio, fim, judgeid FROM contests WHERE contestid = ".$_GET["c"]);
if(mysql_num_rows($r) == 0) header("Location: contests.php");
$contest = mysql_fetch_assoc($r);

$r = mysql_query("SELECT pid, problemid, name FROM problems WHERE contestid = ".$_GET["c"]." ORDER BY name ASC");
for($num_problems = 0; $row = mysql_fetch_assoc($r); $num_problems++)
	$problems[$num_problems] = $row;

// TODO: transformar join em função e verificar se user tem id associado no judge do contest
// join
if($_SESSION["smacoaccess"] > 0)
	mysql_query("INSERT INTO `participates` (`uid`, `contestid`) VALUES (".$_SESSION["smacoid"].", ".$_GET["c"].");");

$r = mysql_query("SELECT users.uid, nome FROM users, participates WHERE users.uid = participates.uid AND contestid = ".$_GET["c"]);
for($num_users = 0; $row = mysql_fetch_assoc($r); $num_users++) {
	$users[$num_users] = $row;
	$users[$num_users]["solved"] = 0;
	$users[$num_users]["penalty"] = 0;
	$t = mysql_query("SELECT pid, answer, date FROM runs WHERE uid=".$users[$num_users]["uid"]." AND date > '".$contest["inicio"]."' AND date < '".$contest["fim"]."'");
	for($nr = 0; $trow = mysql_fetch_assoc($t); $nr++) {
		$users[$num_users]["runs"][$nr] = $trow;
		// TODO: somar solved e penalty
	}
	$users[$num_users]["num_runs"] = $nr;
}
// TODO: ordenar users
?>
<html>
<head>
	<title>sudo make a contest</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body class="content">
	<div id="header">
		<h1>sudo make a contest</h1>
<?php
include("tabs.php");
?>
	</div>
	<div id="content">
		<h1><?php echo $contest["nome"]; ?></h1>
		<table class="default">
			<thead>
				<tr>
					<td>&#35;</td>
					<td>Competidor</td>
<?php
for($i = 0; $i < $num_problems; $i++) {
	echo "					<td><a href=\"".judgeURL($contest["judgeid"], $problems[$i]["problemid"])."\"><img src=\"img/balloon_".$i.".png\" />".$problems[$i]["name"]."</a></td>\n";
}
?>
					<td>ACs</td>
					<td>Pen.</td>
				</tr>
			</thead>
			<tbody>
<?php
if($num_users == 0) {
?>
				<tr>
					<td colspan="<?php echo 4+$num_problems; ?>" style="text-align: center;">Nenhum competidor encontrado.</td>
				</tr>
<?php
} else {
	for($i = 0; $i < $num_users; $i++) {
		echo "				<tr>\n";
		echo "					<td>".($i+1).".</td>\n";
		echo "					<td>".$users[$i]["nome"]."</td>\n";
		for($j = 0; $j < $num_problems; $j++) {
			echo "					<td></td>\n";
		}
		echo "					<td>0</td>\n";
		echo "					<td>0</td>\n";
		echo "				</tr>\n";
	}
}
?>
			</tbody>
		</table>
	</div>
</body>
</html>
