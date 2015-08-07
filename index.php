  <?php

/*
*   +------------------------------------------------------------------------------+
*       SERVER STATUS SCRIPT
*   +------------------------------------------------------------------------------+
*/
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
$time_start = microtime_float();

$_ip = gethostbyname(gethostname());

?>
<html>
<head>
<title>Server Status Page</title>
<link href='favicon.png' rel='icon' type='image/x-icon'/>
<style>
td,body
{
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8pt;
    color: #444444;
}
</style>
<body>
<br>
    <center>
<h2>[[Server]] -- [[IP Address</h2>
 <b><font size='2'>Web Services</font></b>
<table width='480' border='1' cellspacing='0' cellpadding='3' style='border-collapse:collapse; background-color:#ebebeb;' bordercolor='#333333' align='center'>
	<tr>
		<td><a href="http://host.subdomain.domain.com.au/transmission/web/">Transmission</a></td>
	</tr></table>
<br />
     <b><font size='2'>Service Status</font></b>
   </center>
<br>
<?

//configure script
$timeout = "1";

//set service checks
$port[0] = "80";       $service[0] = "Internet Connection";     $ip[0] ="google.com";
$port[1] = "80";       $service[1] = "Internal HTTP";                  $ip[1] ="";
$port[2] = "8080";       $service[2] = "External HTTP";     $ip[2] ="external IP";
$port[3] = "php5-fpm";     $service[3] = "PHP";                   $ip[3] ="";
$port[4] = "22"; 	$service[4] = "Internal SSH";			$ip[4] ="";
$port[5] = "2222";       $service[5] = "External SSH";                     $ip[5] ="External IP";
$port[6] = "named";	$service[6] = "DNS Server";			$ip[6] ="";
$port[7] = "noip2";        $service[7] = "No-IP.com DDNS Client";                    $ip[7] ="";
$port[8] = "ntpd";        $service[8] = "Network Time Protocol";                    $ip[8] ="";
$port[9] = "dhcpd";        $service[9] = "DHCP Server";                    $ip[9] ="";
$port[10] = "transmission";        $service[10] = "Transmission";                    $ip[10] ="";

// Getting the data

//count arrays
$ports = count($port);
#$ports = $ports + 1;
$count = 0;

//begin table for status
?>
<table width='480' border='1' cellspacing='0' cellpadding='3' style='border-collapse:collapse; background-color:#ebebeb;' bordercolor='#333333' align='center'>
<?php
while($count < $ports){
flush();
if(is_numeric($port[$count])){
     if($ip[$count]==""){
       $ip[$count] = "localhost";
     }

        $fp = @fsockopen("$ip[$count]", $port[$count], $errno, $errstr, $timeout);
        if (!$fp) {
            echo "<tr><td>$service[$count]</td><td style='text-align:center; background-color:#FFC6C6;'>Offline </td></tr>";
        } else {
            echo "<tr><td>$service[$count]</td><td style='text-align:center; background-color:#D9FFB3;'>Online</td></tr>";
            fclose($fp);
        }
    $count++;
@fclose($fp);
} 

else{
exec("pgrep $port[$count]", $output, $return);
if ($return == 0) {
        echo "<tr><td>$service[$count]</td><td style='text-align:center; background-color:#D9FFB3;'>Online</td></tr>";

} else {
	    echo "<tr><td>$service[$count]</td><td style='text-align:center; background-color:#FFC6C6;'>Offline </td></tr>";
}
    $count++;
}
}

// SERVER INFORMATION

?>
</table>
<br>
    <center>
     <div style=\"border-bottom:1px #999999 solid;width:480;\"><b>
       <font size='2'>Server Information</font></b>
     </div> 
   </center><BR>
   

<table width='480' border='1' cellspacing='0' cellpadding='3' style='border-collapse:collapse; background-color:#ebebeb;' bordercolor='#333333' align='center'>

<?php

//GET SERVER LOADS
$loadresult = @exec('uptime'); 
preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",$loadresult,$avgs);


//GET SERVER UPTIME
  $uptime = explode(' up ', $loadresult);
  $uptime = explode(',', $uptime[1]);
  $uptime = $uptime[0].', '.$uptime[1];

$data1 = "<tr><td>Server Load Averages </td><td>$avgs[1], $avgs[2], $avgs[3]</td>\n";
$data1 .= "<tr><td>Server Uptime        </td><td>$uptime                     </td></tr>\n";

//GET MEMORY DATA
  $free = `free -m | grep "buffers/cache" | awk '{print $4}'`;
  $totram = `free -m | grep Mem | awk '{print $2}'`;

$data1 .= "<tr><td>Server Memory	</td><td>$free MB out of $totram MB free</td></tr>\n";

$rootfs = `df -h | grep rootfs | awk '{ print $5}'`;
$mediafs = `df -h | grep sda1 | awk '{ print $5}'`;
$rootfssize = `df -h | grep rootfs | awk '{print $2}'`;
$mediafssize = `df -h | grep sda1 | awk '{print $2}'`;
$data1 .= "<tr><td>SD Card	</td><td>$rootfs used ( $rootfssize)</td></tr>\n";
  
//get ps data
  $ps = `ps aux | wc -l`;
  $ps = $ps--;

$data1 .= "<tr><td>Server processes	</td><td>$ps Current Processes</td></tr>\n";  
  
//Get network connection total
$numtcp = `netstat -nt | grep tcp | wc -l`;
$numudp = `netstat -nu | grep udp | wc -l`;

$data1 .= "<tr><td>Open Connections	</td><td>TCP: $numtcp\tUDP: $numudp</td></tr>\n";

$cputemp= `cat /sys/class/thermal/thermal_zone0/temp`;
$cputemp= round(($cputemp/1000),2);
$cpuFreq= `cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq | sed 's/.\{3\}$//'`;
$data1 .= "<tr><td>CPU Status	</td><td>$cputemp &deg;C @ $cpuFreq Mhz</td></tr>\n";
$gputemp= `/opt/vc/bin/vcgencmd measure_temp | sed 's/temp=//' | sed 's/.C//'`;
$gputemp= round(($gputemp), 2);
$data1 .= "<tr><td>GPU Temp	</td><td>$gputemp &deg;C</td></tr>\n";

// PHP Version
$phpver= phpversion();
$data1 .= "<tr><td>PHP Version     </td><td>$phpver</td></tr>\n";

echo $data1;
?>
</table>
<?php
$time_end= microtime_float();
$time = round($time_end - $time_start, 1);
$date = date('r');
echo "<br />";
echo "<center><small>Page generation time: $time seconds</small></center>";
echo "<center><small>Last generated: $date</small></center>";
?> 
