<?php
if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
	header("Location: fail.php");

include("inc/db.inc.php");
include("inc/util.inc.php");

session_start();

if(session_is_registered("smacoid")) header("Location: home.php");

if(isset($_POST["submit"])) {
	$login = strip_tags($_POST["login"]);
	$nome = strip_tags($_POST["nome"]);
	$email = strip_tags($_POST["email"]);
	if($_POST["pass"] != $_POST["confirm"]) $msg = "match";
	else if(!check_email_address($email)) $msg = "email";
	else if($_POST["invite"] != "macaco") $msg = "invite";
	else if(mysql_num_rows(mysql_query("SELECT login FROM users WHERE login='".$login."'")) > 0) $msg = "used";
	else {
		$pass = md5(strip_tags($_POST["pass"]));
		$r = mysql_query(sprintf("INSERT INTO users (login, senha, nome, email, access, score) VALUES ('%s', '%s', '%s', '%s', 20, 0);",
			mysql_real_escape_string($login),
			mysql_real_escape_string($pass),
			mysql_real_escape_string($nome),
			mysql_real_escape_string($email)
		));
		$msg = $r ? "ok" : "fail";
	}
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
<?php
if(isset($msg)) {
	if($msg == "match")
		echo "<p class=\"error\">Senha e confirmação não conferem.</p>";
	else if($msg == "email")
		echo "<p class=\"error\">Endereço de e-mail inválido.</p>";
	else if($msg == "invite")
		echo "<p class=\"error\">Invite code inválido.</p>";
	else if($msg == "used")
		echo "<p class=\"error\">Login em uso.</p>";
	else if($msg == "fail")
		echo "<p class=\"error\">Deu alguma merda. Tente de novo ou peça ajuda.</p>";
	else if($msg == "ok")
		echo "<p class=\"notify\">Conta criada com sucesso.</p>";
}

if(!isset($msg) || (isset($msg) && $msg != "ok")) {
?>
		<form name="cadastro" method="post" action="cadastro.php">
			<table class="form">
				<tr>
					<td>Nome:</td>
					<td><input type="text" name="nome" id="nome" value="<?php if(isset($_POST["nome"])) echo $_POST["nome"]; ?>" /></td>
				</tr>
				<tr>
					<td>E-mail:</td>
					<td><input type="text" name="email" id="email" value="<?php if(isset($_POST["email"])) echo $_POST["email"]; ?>" /></td>
				</tr>
				<tr>
					<td>Login:</td>
					<td><input type="text" name="login" id="login" value="<?php if(isset($_POST["login"])) echo $_POST["login"]; ?>" /></td>
				</tr>
				<tr>
					<td>Senha:</td>
					<td><input type="password" name="pass" id="pass" /></td>
				</tr>
				<tr>
					<td>Confirme:</td>
					<td><input type="password" name="confirm" id="confirm" /></td>
				</tr>
				<tr>
					<td>Invite:</td>
					<td><input type="text" name="invite" id="invite" /></td>
				</tr>
				<tr>
					<td align="right" colspan="2"><input type="submit" name="submit" class="button" value="Ok" /></td>
				</tr>
			</table>
		</form>
<?php
}
?>
	</div>
<?php
include("footer.php");
?>
</body>
</html>
