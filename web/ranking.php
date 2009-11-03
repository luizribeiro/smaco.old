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
<body>
<?php
include("header.php");
?>
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
$r = mysql_query("SELECT uid, nome, score FROM users WHERE login != 'admin' ORDER BY score DESC");
$i = 1;
while($row = mysql_fetch_assoc($r)) {
	if($row["uid"] == $_SESSION["smacoid"])
		echo "				<tr class=\"highlight\">\n";
	else
		echo "				<tr>\n";
	echo "					<td>".$i.".</td>\n";
	echo "					<td><a href=\"profile.php?u=".$row["uid"]."\">".$row["nome"]."</a></td>\n";
	echo "					<td>".$row["score"]."</td>\n";
	echo "				</tr>\n";
	$i++;
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
