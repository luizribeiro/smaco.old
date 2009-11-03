<?php
include("inc/db.inc.php");

session_start();
if(!session_is_registered("smacoid")) header("Location: index.php?msg=require");
$r = mysql_query("SELECT nome, email, score FROM users WHERE uid=".$_GET['u']);
//if(mysql_num_rows($r) == 0) header("Location: home.php");
$user = mysql_fetch_assoc($r);
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
		<h1><?php echo $user["nome"]; ?></h1>
		<div style="text-align: center;">
			<img src="charts/runs.php?u=<?php echo $_GET["u"]; ?>" />
			<img src="charts/langs.php?u=<?php echo $_GET["u"]; ?>" />
		</div>
	</div>
<?php
include("footer.php");
?>
</body>
</html>
