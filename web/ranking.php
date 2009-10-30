<?php
include("inc/db.inc.php");

session_start();
if(!session_is_registered("smacoid")) header("Location: index.php?msg=require");
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
		<table class="default">
			<thead>
				<tr>
					<td>&#35;</td>
					<td>Nome</td>
					<td>Score</td>
				</tr>
			</thead>
			<tbody>
<?php
$r = mysql_query("SELECT login, nome, score FROM users WHERE login != 'admin' ORDER BY score DESC");
$i = 1;
while($row = mysql_fetch_assoc($r)) {
	echo "				<tr>\n";
	echo "					<td>".$i.".</td>\n";
	echo "					<td>".$row["nome"]."</td>\n";
	echo "					<td>".$row["score"]."</td>\n";
	echo "				</tr>\n";
	$i++;
}
?>
			</tbody>
		</table>
	</div>
</body>
</html>
