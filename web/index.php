<?php
if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
	header("Location: fail.php");

include("inc/db.inc.php");

session_start();

if(session_is_registered("smacoid")) header("Location: home.php");

if(isset($_POST["submit"])) {
	$login = strip_tags($_POST["login"]);
	$pass = md5(strip_tags($_POST["pass"]));
	$r = mysql_query(sprintf("SELECT uid, login, access FROM users WHERE login='%s' AND senha='%s'", mysql_real_escape_string($login), mysql_real_escape_string($pass)));
	if(mysql_num_rows($r) !== 1) $_GET["msg"] = "fail";
	else {
		$row = mysql_fetch_assoc($r);
		$_SESSION["smacoid"] = $row["uid"];
		$_SESSION["smacoaccess"] = $row["access"];
		header("Location: home.php");
	}
}
?>
<html>
<head>
	<title>sudo make a contest</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script type="text/javascript">
	<!--
		function loginFocus() {
			document.getElementById('login').focus();
		}
		window.onload = loginFocus;
	-->
	</script>
</head>
<body>
<?php
include("header.php");
?>
	<div id="content">
<?php
if(isset($_GET["msg"])) {
	if($_GET["msg"] == "fail")
		echo "<p class=\"error\">Login ou senha inválidos.</p>";
	else if($_GET["msg"] == "require")
		echo "<p class=\"error\">Para acessar esta área você precisa efetuar login.</p>";
	else if($_GET["msg"] == "logout")
		echo "<p class=\"notify\">Logout efetuado com sucesso.</p>";
}
?>

		<form name="login" method="post" action="index.php">
			<table class="form">
				<tr>
					<td>Login:</td>
					<td><input type="text" name="login" id="login" /></td>
				</tr>
				<tr>
					<td>Senha:</td>
					<td><input type="password" name="pass" id="pass" /></td>
				</tr>
				<tr>
					<td align="right" colspan="2"><input type="submit" name="submit" class="button" value="Ok" /></td>
				</tr>
			</table>
		</form>
	</div>
<?php
include("footer.php");
?>
</body>
</html>
