<?php
function judgeURL($jid, $pid) {
	switch($jid) {
	case 0:
		return "http://acmicpc-live-archive.uva.es/nuevoportal/data/problem.php?p=".$pid;
	default:
		return "#";
	}
}
?>
