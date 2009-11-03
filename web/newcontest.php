<?php
include("inc/db.inc.php");

define("MAX_PROBLEMS", 12);

session_start();
if(!session_is_registered("smacoid")) header("Location: index.php?msg=require");
if($_SESSION["smacoaccess"] > 10) header("Location: home.php");

if(isset($_POST["submit"])) {
	$inicio = $_POST["dia"]." ".$_POST["horario"];
	$r = mysql_query("INSERT INTO `contests` (`nome`, `judgeid`, `inicio`, `fim`, `freeze`) VALUES ('".$_POST["nome"]."', ".$_POST["judgeid"].", '".$inicio."', ADDTIME('".$inicio."', '".date("H:i:s", 60*$_POST["duracao"])."'), '".$_POST["freeze"]."');");
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
<body>
<?php
include("header.php");
?>
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
					<td>Dia:</td>
					<td><input type="text" name="dia" value="2009-10-31" /></td>
				</tr>
				<tr>
					<td>Horário:</td>
					<td><input type="text" name="horario" value="14:00:00" /></td>
				</tr>
				<tr>
					<td>Duração (mins):</td>
					<td><input type="text" name="duracao" value="300" /></td>
				</tr>
				<tr>
					<td>Freeze (mins):</td>
					<td><input type="text" name="freeze" value="60" /></td>
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
<?php
include("footer.php");
?>
</body>
</html>
