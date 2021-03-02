<?php
$ip = (int)$_GET['ip'];
if ($ip < 1 || $ip > 255) die;

$output = shell_exec('ping -c1 -W1 192.168.10.'.$ip);
preg_match('/time=([\.0-9]+)/', $output, $m);

if(empty($m)) {
	echo "<span class=\"badge bg-danger\">down</span>";
} else {
	if ((float)$m[1] > 10) {
		echo "<span class=\"badge bg-warning\">".$m[1]."</span>";
	} else {
		echo "<span class=\"badge bg-success\">".$m[1]."</span>";
	}
}
