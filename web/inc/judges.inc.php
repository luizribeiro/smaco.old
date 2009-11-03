<?php
function judgeURL($jid, $pid) {
	switch($jid) {
	case 0:
		return "http://acmicpc-live-archive.uva.es/nuevoportal/data/problem.php?p=".$pid;
	case 1:
		return "http://uva.onlinejudge.org/external/".floor($pid/100)."/".$pid.".html";
	default:
		return "#";
	}
}

function judgeName($jid) {
	switch($jid) {
	case 0:
		return "Live Archive";
	case 1:
		return "UVa";
	default:
		return "Unknown";
	}
}
?>
