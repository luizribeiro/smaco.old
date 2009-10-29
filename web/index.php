<?php
if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
	header("Location: fail.php");

include("inc/db.inc.php");

session_start();

if(session_is_registered("smacoid")) header("Location: home.php");

if(isset($_POST["submit"])) {
	$login = strip_tags($_POST["login"]);
	$pass = md5(strip_tags($_POST["pass"]));
	$r = mysql_query(sprintf("SELECT login FROM users WHERE login='%s' AND senha='%s'", mysql_real_escape_string($login), mysql_real_escape_string($pass)));
	if(mysql_num_rows($r) != 1) $_GET["msg"] = "fail";
	else {
		$row = mysql_fetch_assoc($r);
		$_SESSION["smacoid"] = $row["login"];
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
<body class="login">
	<div align="center">
		<h1>sudo make a contest</h1>
<?php
if(isset($_GET["msg"])) {
	if($_GET["msg"] == "fail")
		echo "<p class=\"error\">Login ou senha inválidos.</p>";
	else if($_GET["msg"] == "require")
		echo "<p class=\"error\">Para acessar esta área você precisa efetuar login.</p>";
	else if($_GET["msg"] == "logout")
		echo "<p>Logout efetuado com sucesso.</p>";
}
?>

		<form name="login" method="post" action="index.php">
			<table class="form">
				<tr>
					<td><b>Login:</b></td>
					<td><input type="text" name="login" id="login" /></td>
				</tr>
				<tr>
					<td><b>Senha:</b></td>
					<td><input type="password" name="pass" id="pass" /></td>
				</tr>
				<tr>
					<td align="right" colspan="2"><input type="submit" name="submit" class="button" value="Logar" /></td>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>
