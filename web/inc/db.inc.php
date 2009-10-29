<?php
include("config.inc.php");

$l = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
if(!$l) die("Deu merda conectando: ".mysql_error());
$db = mysql_select_db(MYSQL_DB, $l);
if(!$db) die("Deu merda selecionando DB: ".mysql_error());
?>
