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
					<td>Início</td>
					<td>Fim</td>
				</tr>
			</thead>
			<tbody>
<?php
$r = mysql_query("SELECT contestid, nome, judgeid, inicio, fim FROM contests ORDER BY inicio DESC");
if(mysql_num_rows($r) == 0) {
	echo "				<tr><td colspan=\"2\" style=\"text-align: center;\">Nenhum contest encontrado.</td></tr>\n";
} else {
	while($row = mysql_fetch_assoc($r)) {
		echo "				<tr>\n";
		echo "					<td><a href=\"scoreboard.php?c=".$row["contestid"]."\">".$row["nome"]."</a></td>\n";
		echo "					<td>".judgeName($row["judgeid"])."</td>\n";
		echo "					<td>".$row["inicio"]."</td>\n";
		echo "					<td>".$row["fim"]."</td>\n";
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
