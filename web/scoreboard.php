<?php
include("inc/db.inc.php");
include("inc/judges.inc.php");

session_start();
if(!session_is_registered("smacoid")) header("Location: index.php?msg=require");
if(!isset($_GET["c"])) header("Location: contests.php");

$r = mysql_query("SELECT nome, UNIX_TIMESTAMP(inicio) AS inicio, UNIX_TIMESTAMP(fim) AS fim, judgeid, freeze FROM contests WHERE contestid = ".$_GET["c"]);
if(mysql_num_rows($r) == 0) header("Location: contests.php");
$contest = mysql_fetch_assoc($r);

$contest["freeze"] = $contest["fim"] - $contest["freeze"]*60;

if($contest["inicio"] >= time()) header("Location: contests.php");

$r = mysql_query("SELECT pid, problemid, name FROM problems WHERE contestid = ".$_GET["c"]." ORDER BY name ASC");
for($num_problems = 0; $row = mysql_fetch_assoc($r); $num_problems++)
	$problems[$num_problems] = $row;

// TODO: transformar join em função e verificar se user tem id associado no judge do contest
// join
if($_SESSION["smacoaccess"] > 0 && $contest["fim"] > time())
	mysql_query("INSERT INTO `participates` (`uid`, `contestid`) VALUES (".$_SESSION["smacoid"].", ".$_GET["c"].");");

// baixa as runs e faz tudo
$r = mysql_query("SELECT users.uid, nome FROM users, participates WHERE users.uid = participates.uid AND contestid = ".$_GET["c"]);
for($num_users = 0; $row = mysql_fetch_assoc($r); $num_users++) {
	$users[$num_users] = $row;
	$users[$num_users]["solved"] = 0;
	$users[$num_users]["penalty"] = 0;
	if($contest["fim"] < time())
		$t = mysql_query("SELECT pid, answer, UNIX_TIMESTAMP(date) AS date FROM runs WHERE uid=".$users[$num_users]["uid"]." AND date > FROM_UNIXTIME(".$contest["inicio"].") AND date < FROM_UNIXTIME(".$contest["fim"].") AND pid IN (SELECT pid FROM problems WHERE contestid=".$_GET["c"].") ORDER BY date ASC");
	else
		$t = mysql_query("SELECT pid, answer, UNIX_TIMESTAMP(date) AS date FROM runs WHERE uid=".$users[$num_users]["uid"]." AND date > FROM_UNIXTIME(".$contest["inicio"].") AND date <= FROM_UNIXTIME(".$contest["freeze"].") AND pid IN (SELECT pid FROM problems WHERE contestid=".$_GET["c"].") ORDER BY date ASC");
	// inicia tudo
	for($i = 0; $i < $num_problems; $i++) {
		$users[$num_users][$problems[$i]["pid"]]["ac"] = false;
		$users[$num_users][$problems[$i]["pid"]]["time"] = 0;
		$users[$num_users][$problems[$i]["pid"]]["runs"] = 0;
	}
	for($nr = 0; $trow = mysql_fetch_assoc($t); $nr++) {
		if(!$users[$num_users][$trow["pid"]]["ac"]) {
			$users[$num_users][$trow["pid"]]["runs"]++;
			if($trow["answer"] == "AC") {
				$users[$num_users][$trow["pid"]]["ac"] = true;
				$users[$num_users][$trow["pid"]]["time"] = round(($trow["date"] - $contest["inicio"])/60.0);

				// incrementa solved e penalidade
				$users[$num_users]["solved"]++;
				$users[$num_users]["penalty"] += ($users[$num_users][$trow["pid"]]["runs"]-1) * 20 + $users[$num_users][$trow["pid"]]["time"];
			}
		}
	}
}

function usercmp($a, $b) {
	if($a["solved"] > $b["solved"]) return -1;
	if($a["solved"] < $b["solved"]) return 1;
	if($a["penalty"] < $b["penalty"]) return -1;
	if($a["penalty"] > $b["penalty"]) return 1;
	return $a["nome"] > $b["nome"];
}
if($num_users > 0)
	usort($users, "usercmp");
?>
<html>
<head>
	<title>sudo make a contest</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<?php
if($contest["fim"] > time()) {
?>
	<meta http-equiv="refresh" content="30; url=" />
<?php
}
?>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<?php
include("header.php");
?>
	<div id="content">
		<h1><?php echo $contest["nome"]; ?></h1>
<?php
if($contest["fim"] < time()) {
	echo "		<p class=\"timeleft\">Fim do Contest.</p>\n";
	echo "		<p class=\"timeleft\"><a href=\"runlist.php?c=".$_GET["c"]."\">Runlist</a></p>\n";
} else {
	$left = $contest["fim"] - time();
	$min = floor($left/60);
	$sec = $left % 60;
	if($min > 0) echo "		<p class=\"timeleft\">".$min." minutos e ".$sec." segundos restantes.</p>\n";
	else echo "		<p class=\"timeleft\">".$sec." segundos restantes.</p>\n";
	if($contest["freeze"] <= time())
		echo "		<p class=\"frozen\">Placar Congelado!</p>\n"; // TODO: frozen
}
?>
		<table class="default">
			<thead>
				<tr>
					<td>&#35;</td>
					<td>Competidor</td>
<?php
for($i = 0; $i < $num_problems; $i++) {
	echo "					<td><a href=\"".judgeURL($contest["judgeid"], $problems[$i]["problemid"])."\"><img class=\"balloon\" src=\"img/balloon_".$i.".png\" />".$problems[$i]["name"]."</a></td>\n";
}
?>
					<td>ACs</td>
					<td>Pen</td>
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
		echo "					<td><a href=\"profile.php?u=".$users[$i]["uid"]."\">".$users[$i]["nome"]."</a></td>\n";
		for($j = 0; $j < $num_problems; $j++) {
			$t = $users[$i][$problems[$j]["pid"]];
			if($t["runs"] == 0)
				echo "					<td></td>\n";
			else if($t["ac"])
				echo "					<td class=\"accepted\">".$t["runs"]."/".$t["time"]."</td>\n";
			else
				echo "					<td class=\"wrong\">".$t["runs"]."/-</td>\n";
		}
		echo "					<td>".$users[$i]["solved"]."</td>\n";
		echo "					<td>".$users[$i]["penalty"]."</td>\n";
		echo "				</tr>\n";
	}
}
?>
			</tbody>
		</table>
	</div>
<?php
include("footer.php");
?>
</body>
</html>
