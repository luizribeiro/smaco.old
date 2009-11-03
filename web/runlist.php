<?php
include("inc/db.inc.php");
include("inc/judges.inc.php");

session_start();
if(!session_is_registered("smacoid")) header("Location: index.php?msg=require");
if(!isset($_GET["c"])) header("Location: contests.php");

$r = mysql_query("SELECT nome, UNIX_TIMESTAMP(inicio) AS inicio, UNIX_TIMESTAMP(fim) AS fim, judgeid FROM contests WHERE contestid = ".$_GET["c"]);
if(mysql_num_rows($r) == 0) header("Location: contests.php");
$contest = mysql_fetch_assoc($r);

if($contest["fim"] >= time()) header("Location: contests.php");

$r = mysql_query("SELECT pid, problemid, name FROM problems WHERE contestid = ".$_GET["c"]." ORDER BY name ASC");
for($num_problems = 0; $row = mysql_fetch_assoc($r); $num_problems++) {
	$problems[$row["pid"]] = $row;
	$problems[$row["pid"]]["number"] = $num_problems;
}

// baixa as runs e faz tudo
$r = mysql_query("SELECT users.uid, nome FROM users, participates WHERE users.uid = participates.uid AND contestid = ".$_GET["c"]);
while($row = mysql_fetch_assoc($r))
	$users[$row["uid"]] = $row["nome"];

$t = mysql_query("SELECT pid, uid, answer, runtime, language, UNIX_TIMESTAMP(date) AS date FROM runs WHERE date > FROM_UNIXTIME(".$contest["inicio"].") AND date < FROM_UNIXTIME(".$contest["fim"].") AND pid IN (SELECT pid FROM problems WHERE contestid = ".$_GET["c"].") ORDER BY date ASC");
?>
<html>
<head>
	<title>sudo make a contest</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<?php
include("header.php");
?>
	<div id="content">
		<h1><?php echo $contest["nome"]; ?></h1>
<?php
echo "		<p class=\"timeleft\">Fim do Contest.</p>\n";
echo "		<p class=\"timeleft\"><a href=\"scoreboard.php?c=".$_GET["c"]."\">Scoreboard</a></p>\n";
?>
		<table class="default">
			<thead>
				<tr>
					<td>Tempo</td>
					<td>Competidor</td>
					<td>Linguagem</td>
					<td>Runtime</td>
					<td>Problema</td>
					<td>Resposta</td>
				</tr>
			</thead>
			<tbody>
<?php
if(mysql_num_rows($t) == 0) {
?>
				<tr>
					<td colspan="6" style="text-align: center;">Nenhuma run encontrada.</td>
				</tr>
<?php
} else {
	while($run = mysql_fetch_assoc($t)) {
		$tempo = floor(($run["date"] - $contest["inicio"])/60);
		$competidor = $users[$run["uid"]];
		echo "				<tr>\n";
		echo "					<td>".$tempo."</td>\n";
		echo "					<td><a href=\"profile.php?u=".$run["uid"]."\">".$competidor."</a></td>\n";
		echo "					<td>".$run["language"]."</td>\n";
		echo "					<td>".number_format($run["runtime"], 3)."</td>\n";
		echo "					<td><a href=\"".judgeURL($contest["judgeid"], $problems[$run["pid"]]["problemid"])."\"><img src=\"img/balloon_".$problems[$run["pid"]]["number"].".png\" />".$problems[$run["pid"]]["name"]."</a></td>\n";
		if($run["answer"] == "AC")
			echo "					<td class=\"accepted\">".$run["answer"]."</td>\n";
		else
			echo "					<td class=\"wrong\">".$run["answer"]."</td>\n";
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
