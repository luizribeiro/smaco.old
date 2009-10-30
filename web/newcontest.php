<?php
include("inc/db.inc.php");

define("MAX_PROBLEMS", 12);

session_start();
if(!session_is_registered("smacoid")) header("Location: index.php?msg=require");
if($_SESSION["smacoaccess"] > 10) header("Location: home.php");

if(isset($_POST["submit"])) {
	$r = mysql_query("INSERT INTO `contests` (`nome`, `judgeid`, `inicio`, `fim`) VALUES ('".$_POST["nome"]."', ".$_POST["judgeid"].", '".$_POST["inicio"]."', '".$_POST["fim"]."');");
	if($r) {
		$contestid = mysql_insert_id();
		for($i = 0; !empty($_POST["id".$i]) && $i < MAX_PROBLEMS; $i++)
			mysql_query("INSERT INTO `problems` (`contestid`, `problemid`, `name`) VALUES ('".$contestid."', '".$_POST["id".$i]."', '".chr($i + 65)."');");
		$msg = "Contest criado com sucesso.";
	} else {
		$msg = "Erro criando contest: ".mysql_error();
	}
}
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
		<h1>Novo Contest</h1>
<?php
if(isset($msg)) {
	echo "		<p>".$msg."</p>\n";
} else {
?>
		<form name="login" method="post" action="newcontest.php">
			<table class="form">
				<tr>
					<td colspan="2" style="text-align: center;"><b>Informações Gerais</b></td>
				</tr>
				<tr>
					<td>Nome:</td>
					<td><input type="text" name="nome" /></td>
				</tr>
				<tr>
					<td>Judge:</td>
					<td>
						<select name="judgeid">
							<option value="0">Live-Archive</option>
							<option value="1">UVa</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Início:</td>
					<td><input type="text" name="inicio" value="2009-10-30 02:00:00" /></td>
				</tr>
				<tr>
					<td>Fim:</td>
					<td><input type="text" name="fim" value="2009-10-30 07:00:00" /></td>
				</tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr>
					<td colspan="2" style="text-align: center;"><b>Problem IDs</b></td>
				</tr>
<?php
for($i = 0; $i < MAX_PROBLEMS; $i++) {
	echo "				<tr>\n";
	echo "					<td>".chr($i + 65).":</td>\n";
	echo "					<td><input type=\"text\" name=\"id".$i."\" /></td>\n";
	echo "				</tr>\n";
}
?>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr>
					<td align="right" colspan="2"><input type="submit" name="submit" class="button" value="Ok" /></td>
				</tr>
			</table>
		</form>
<?php
}
?>
	</div>
</body>
</html>
