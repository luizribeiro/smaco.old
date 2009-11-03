<?php
include("inc/db.inc.php");
include("inc/judges.inc.php");

session_start();
if(!session_is_registered("smacoid")) header("Location: index.php?msg=require");
?>
<html>
<head>
	<title>sudo make a contest</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script type="text/javascript">
	<!--
		function doConfirm(id) {
			return confirm("Você tem certeza que deseja entrar no contest?");
		}
	-->
	</script>
</head>
<body>
<?php
include("header.php");
?>
	<div id="content">
<?php
if($_SESSION["smacoaccess"] <= 10) {
?>
		<p align="center"><a href="newcontest.php">Novo Contest</a></p>
<?php
}
?>
		<table class="default">
			<thead>
				<tr>
					<td>Contest</td>
					<td>Judge</td>
					<td>Início (UTC)</td>
					<td>Fim (UTC)</td>
				</tr>
			</thead>
			<tbody>
<?php
$participates = array();
$r = mysql_query("SELECT contestid FROM participates WHERE uid = ".$_SESSION["smacoid"]);
while($row = mysql_fetch_assoc($r)) $participates[] = $row["contestid"];
$myjudges = array();
$r = mysql_query("SELECT judgeid FROM userids WHERE uid = ".$_SESSION["smacoid"]);
while($row = mysql_fetch_assoc($r)) $myjudges[] = $row["judgeid"];
$r = mysql_query("SELECT contestid, nome, judgeid, UNIX_TIMESTAMP(inicio) AS inicio, UNIX_TIMESTAMP(fim) AS fim FROM coming_contests ORDER BY inicio DESC");
$cnt = mysql_num_rows($r);
while($row = mysql_fetch_assoc($r)) {
	if($row["fim"] == 2000000000) $row["nome"] = "<img src=\"img/star.png\" class=\"star\" />".$row["nome"];
	echo "				<tr class=\"coming\">\n";
	echo "					<td>".$row["nome"]."</td>\n";
	echo "					<td>".judgeName($row["judgeid"])."</td>\n";
	$left = floor(($row["inicio"] - time())/60);
	if($left <= 60)
		echo "					<td>em ".$left." minutos</td>\n";
	else
		echo "					<td>".date("Y-m-d H:i:s", $row["inicio"])."</td>\n";
	if($row["fim"] == 2000000000)
		echo "					<td></td>\n";
	else
		echo "					<td>".date("Y-m-d H:i:s", $row["fim"])."</td>\n";
	echo "				</tr>\n";
}
$r = mysql_query("SELECT contestid, nome, judgeid, UNIX_TIMESTAMP(inicio) AS inicio, UNIX_TIMESTAMP(fim) AS fim FROM running_contests ORDER BY inicio DESC");
$cnt += mysql_num_rows($r);
while($row = mysql_fetch_assoc($r)) {
	if($row["fim"] == 2000000000) $row["nome"] = "<img src=\"img/star.png\" class=\"star\" />".$row["nome"];
	echo "				<tr class=\"running\">\n";
	if($_SESSION["smacoaccess"] > 0 && !in_array($row["judgeid"], $myjudges))
		echo "					<td>".$row["nome"]."</td>\n";
	else if($_SESSION["smacoaccess"] > 0 && !in_array($row["contestid"], $participates))
		echo "					<td><a href=\"scoreboard.php?c=".$row["contestid"]."\" onclick=\"return doConfirm();\">".$row["nome"]."</a></td>\n";
	else
		echo "					<td><a href=\"scoreboard.php?c=".$row["contestid"]."\">".$row["nome"]."</a></td>\n";
	echo "					<td>".judgeName($row["judgeid"])."</td>\n";
	echo "					<td>".date("Y-m-d H:i:s", $row["inicio"])."</td>\n";
	if($row["fim"] == 2000000000)
		echo "					<td></td>\n";
	else {
		$left = floor(($row["fim"] - time())/60);
		if($left == 0 || $left > 1) echo "					<td>em ".$left." minutos</td>\n";
		else echo "					<td>em ".$left." minuto</td>\n";
	}
	echo "				</tr>\n";
}
$r = mysql_query("SELECT contestid, nome, judgeid, UNIX_TIMESTAMP(inicio) AS inicio, UNIX_TIMESTAMP(fim) AS fim FROM past_contests ORDER BY inicio DESC");
$cnt += mysql_num_rows($r);
while($row = mysql_fetch_assoc($r)) {
	if($row["fim"] == 2000000000) $row["nome"] = "<img src=\"img/star.png\" class=\"star\" />".$row["nome"];
	echo "				<tr>\n";
	echo "					<td><a href=\"scoreboard.php?c=".$row["contestid"]."\">".$row["nome"]."</a></td>\n";
	echo "					<td>".judgeName($row["judgeid"])."</td>\n";
	echo "					<td>".date("Y-m-d H:i:s", $row["inicio"])."</td>\n";
	if($row["fim"] == 2000000000)
		echo "					<td></td>\n";
	else
		echo "					<td>".date("Y-m-d H:i:s", $row["fim"])."</td>\n";
	echo "				</tr>\n";
}
if($cnt == 0)
	echo "				<tr><td colspan=\"4\" style=\"text-align: center;\">Nenhum contest encontrado.</td></tr>\n";
?>
			</tbody>
		</table>
	</div>
<?php
include("footer.php");
?>
</body>
</html>
