<?php
include("inc/db.inc.php");

session_start();
if(!session_is_registered("smacoid")) header("Location: index.php?msg=require");

if(isset($_POST["submit"])) {
	mysql_query("DELETE FROM userids WHERE uid=".$_SESSION["smacoid"]." AND judgeid=0");
	if(!empty($_POST["livearchive"])) {
		sscanf(mysql_real_escape_string(strip_tags($_POST["livearchive"])), "%d", &$id);
		mysql_query("INSERT INTO userids (uid, judgeid, id) VALUES (".$_SESSION["smacoid"].", 0, ".$id.")");
	}

	mysql_query("DELETE FROM userids WHERE uid=".$_SESSION["smacoid"]." AND judgeid=1");
	if(!empty($_POST["uva"])) {
		sscanf(mysql_real_escape_string(strip_tags($_POST["uva"])), "%d", &$id);
		mysql_query("INSERT INTO userids (uid, judgeid, id) VALUES (".$_SESSION["smacoid"].", 1, ".$id.")");
		echo mysql_error();
	}
}

if(mysql_num_rows($r = mysql_query("SELECT id FROM userids WHERE uid=".$_SESSION["smacoid"]." AND judgeid=0")) > 0) {
	$row = mysql_fetch_assoc($r);
	$livearchive = $row["id"];
} else {
	$livearchive = "";
}

if(mysql_num_rows($r = mysql_query("SELECT id FROM userids WHERE uid=".$_SESSION["smacoid"]." AND judgeid=1")) > 0) {
	$row = mysql_fetch_assoc($r);
	$uva = $row["id"];
} else {
	$uva = "";
}
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
		<form name="ids" method="post" action="options.php">
			<table class="form">
				<tr>
					<td>Live Archive ID:</td>
					<td><input type="text" name="livearchive" id="livearchive" value="<?php echo $livearchive; ?>" /></td>
				</tr>
				<tr>
					<td>UVa ID:</td>
					<td><input type="text" name="uva" id="uva" value="<?php echo $uva; ?>" /></td>
				</tr>
				<tr>
					<td align="right" colspan="2"><input type="submit" name="submit" class="button" value="Salvar" /></td>
				</tr>
			</table>
		</form>
	</div>
<?php
include("footer.php");
?>
</body>
</html>
