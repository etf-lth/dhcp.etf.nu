<?php
require 'dhcpd-parse.php';

$leases = new Leases;
$leases->readLease("/var/lib/dhcp/dhcpd.leases");

$clients = Array();

$clients[] = array("ip" => "192.168.10.1", "mac" => "", "name" => "Hotblack");
$clients[] = array("ip" => "192.168.10.3", "mac" => "00:01:E7:DD:20:01", "name" => "HPSwitch");
$clients[] = array("ip" => "192.168.10.81", "mac" => "82:CF:B4:01:52:AA", "name" => "Kiosk");
$clients[] = array("ip" => "192.168.10.200", "mac" => "00:01:E6:A5:BF:2C", "name" => "LaserJet4200");
$clients[] = array("ip" => "192.168.10.201", "mac" => "00:40:8C:B9:2E:FA", "name" => "AxisPTZ");
$clients[] = array("ip" => "192.168.10.202", "mac" => "00:40:8C:C5:BF:3B", "name" => "AxisMek");
$clients[] = array("ip" => "192.168.10.203", "mac" => "00:40:8C:46:AC:F1", "name" => "Axis2100");
$clients[] = array("ip" => "192.168.10.205", "mac" => "00:40:8C:99:49:76", "name" => "AxisSales");
$clients[] = array("ip" => "192.168.10.206", "mac" => "00:80:64:5C:C5:91", "name" => "MrCoffee");

$is_curl = strpos($_SERVER['HTTP_USER_AGENT'], "curl") !== false;

if (!$is_curl) {
echo "<!DOCTYPE html>".
	"<html lang=\"en\">".
	"<head>".
	"    <meta charset=\"utf-8\">".
	"    <title>DHCP</title>".
	"    <link href=\"bootstrap.min.css\" rel=\"stylesheet\">".
	"    <meta http-equiv=\"refresh\" content=\"30\">".
	"    <script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js\"></script>".
	"</head>".
	"<body>".
	"    <div class=\"container\">".
	"        <h1>DHCP Leases</h1>".
	"	 <p>".date('r')."</p>".
	"        <table class=\"table table-striped table-condensed\">".
	"            <tr><th>IP</th><th>Ping</th><th>MAC</th><th>Host</th><th>Start</th><th>End</th></tr>";
} else {
echo "\x1b[1m ETF DHCP\x1b[0m\n\n";
echo " ".date('r')."\n\n";
echo "\x1b[1m   IP\t\t\tMAC\t\t\tHost\t\t\tStart\t\t\tEnd\n";
echo "\x1b[0m";
}

while($lease = $leases->nextActive()){
        $client = Array();

        $client["ip"] = $lease["ip_addr"];
        $client["mac"] = @$lease["mac"];
        $client["starts"] = $lease["starts"];
        $client["ends"] = $lease["ends"];
        $client["name"] = isset($lease["hostname"]) ? $lease["hostname"] : "";

        @$clients[$lease["mac"]] = $client;
}

$local = 0;

foreach($clients as $client){
if ($is_curl) {
if($client["ip"] == $_SERVER["REMOTE_ADDR"])
  print '-> ';
else
  print '   ';

echo str_pad($client["ip"],16," ")."\t".
	str_pad($client["mac"],17," ")."\t".
	str_pad($client["name"],16," ")."\t";
if(isset($client["starts"]) and $client["starts"] != 0)
{
echo date("H:i j-M-Y", $client["starts"])."\t".date("H:i j-M-Y", $client["ends"]);
}
echo "\n";

} else {
	echo "<tr";

if($client["ip"] == $_SERVER["REMOTE_ADDR"])
{
  echo ' class="info"';
  $local = 1;
}
//echo "><td><a href=\"#\" onclick=\"ping(".substr($client["ip"],11).")\">".$client["ip"]."</a></td>".
echo "><td>".$client["ip"]."</td>".
	"<td><span class=\"ping\" data-ip=\"".substr($client["ip"],11)."\">...</span></td>".
	"<td>".$client["mac"]."</td>".
	"<td>".$client["name"]."</td>";
if(isset($client["starts"]) and $client["starts"] != 0)
{
echo "<td>".date("H:i j-M-Y", $client["starts"])."</td><td>".date("H:i j-M-Y", $client["ends"])."</td>";
} else {
echo "<td></td><td></td>";
}
echo "</tr>";
}
}

if (!$is_curl) {
echo "</table>";
echo "<p><b>protip:</b> you can also use <code>curl dhcp.etf.nu</code> in your terminal.</p>";
echo '<script>function ping(){$(this).load("/ping.php?ip="+$(this).data("ip"));}$(".ping").each(ping);</script>';
echo "</div></body></html>";
}
