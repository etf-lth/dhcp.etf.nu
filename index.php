<?php
require 'dhcpd-parse.php';
$leases = new Leases;
$leases->readLease("/var/lib/dhcp/dhcpd.leases");

$is_curl = strpos($_SERVER['HTTP_USER_AGENT'], "curl") !== false;

$clients = Array();
$clients[] = array("ip" => "192.168.10.1", "mac" => "", "name" => "Allspark");
$clients[] = array("ip" => "192.168.10.3", "mac" => "00:01:E7:DD:20:01", "name" => "HPSwitch");
$clients[] = array("ip" => "192.168.10.200", "mac" => "00:01:E6:A5:BF:2C", "name" => "LaserJet4200");
$clients[] = array("ip" => "192.168.10.201", "mac" => "00:40:8C:B9:2E:FA", "name" => "AxisPTZ");
$clients[] = array("ip" => "192.168.10.202", "mac" => "00:40:8C:C5:BF:3B", "name" => "AxisMek");
$clients[] = array("ip" => "192.168.10.203", "mac" => "00:40:8C:46:AC:F1", "name" => "Axis2100");
$clients[] = array("ip" => "192.168.10.205", "mac" => "00:40:8C:99:49:76", "name" => "AxisSales");
$clients[] = array("ip" => "192.168.10.206", "mac" => "00:80:64:5C:C5:91", "name" => "MrCoffee");
$clients[] = array("ip" => "192.168.10.210", "mac" => "82:CF:B4:01:52:AA", "name" => "Kiosk");
$clients[] = array("ip" => "192.168.10.222", "mac" => "BC:5F:F4:5A:A1:7B", "name" => "DESKTOP-RDP");

while($lease = $leases->nextActive()){
		$client = Array();

		$client["ip"] = $lease["ip_addr"];
		$client["mac"] = @$lease["mac"];
		$client["starts"] = $lease["starts"];
		$client["ends"] = $lease["ends"];
		$client["name"] = isset($lease["hostname"]) ? $lease["hostname"] : "";

		@$clients[$lease["mac"]] = $client;
}

foreach($clients as $client_id => $client){
	$output = shell_exec('ping -c1 -W1 '.$client["ip"]);
	preg_match('/time=([\.0-9]+)/', $output, $m);
	if(empty($m)) {
		$clients[$client_id]["ping"] = 0;
	} else {
		$clients[$client_id]["ping"] = $m[1];
	}
}

if ($is_curl) {
	echo "\x1b[1m ETF DHCP\x1b[0m\n\n";
	echo " ".date('r')."\n\n";
	echo "\x1b[1m   IP\t\t\tPING\t\tMAC\t\t\tHost\t\t\tStart\t\t\tEnd\n";
	echo "\x1b[0m";

	foreach($clients as $client){
		if($client["ip"] == $_SERVER["REMOTE_ADDR"]) {
			print '-> ';
		} else {
			print '   ';
		}
		
		echo str_pad($client["ip"],16," ")."\t".
			str_pad(empty($client["ping"]) ? ("down") : ($client["ping"]." ms"),12," ")."\t".
			str_pad($client["mac"],17," ")."\t".
			str_pad($client["name"],16," ")."\t";
		if(isset($client["starts"]) and $client["starts"] != 0)
		{
			echo date("H:i j-M-Y", $client["starts"])."\t".date("H:i j-M-Y", $client["ends"]);
		}
		echo "\n";
	}
	die;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="refresh" content="30">
	<title>DHCP</title>
	<link href="bootstrap.min.css" rel="stylesheet">
	<script src="jquery.min.js"></script>
</head>
<body>
	<div class="container">
		<h1 class="mt-3">DHCP Leases</h1>
		<p><?php echo date('r') ?></p>
		<div class="table-responsive">
		<table class="table table-striped table-condensed">
			<tr>
				<th>IP</th>
				<th>Ping</th>
				<th>MAC</th>
				<th>Host</th>
				<th>Start</th>
				<th>End</th>
			</tr>
		<?php
		foreach($clients as $client){
			echo "<tr";
			if($client["ip"] == $_SERVER["REMOTE_ADDR"])
			{
				echo ' class="bg-info"';
			}
			echo "><td>".$client["ip"]."</td>";

			if(empty($client["ping"])) {
			        echo "<td><span class=\"badge bg-danger\">down</span></td>";
			} else {
			        if ((float)$client["ping"] > 10) {
			                echo "<td><span class=\"badge bg-warning\">".$client["ping"]."</span></td>";
			        } else {
			                echo "<td><span class=\"badge bg-success\">".$client["ping"]."</span></td>";
			        }
			}

			echo "<td>".$client["mac"]."</td>".
				"<td>".$client["name"]."</td>";
			if(isset($client["starts"]) and $client["starts"] != 0)
			{
				echo "<td>".date("H:i j-M-Y", $client["starts"])."</td><td>".date("H:i j-M-Y", $client["ends"])."</td>";
			} else {
				echo "<td></td><td></td>";
			}
			echo "</tr>";
		}
		?>
		</table>
		</div>
		<p><b>protip:</b> you can also use <code>curl dhcp.etf.nu</code> in your terminal.</p>
	</div>
</body>
</html>
