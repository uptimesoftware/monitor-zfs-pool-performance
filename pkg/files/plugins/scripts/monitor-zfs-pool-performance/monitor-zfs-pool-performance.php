<?php
require('rcs_function.php');

if(isset($_SERVER['UPTIME_UPTIME_HOSTNAME'])) {
	$host = $_SERVER['UPTIME_UPTIME_HOSTNAME'];
}
else {
	$host = $_SERVER['UPTIME_HOSTNAME'];
}
$port = $_SERVER['UPTIME_UPTIME_PORT'];
$pass = $_SERVER['UPTIME_UPTIME_ZPOOL_PASSWORD'];
$scri = $_SERVER['UPTIME_UPTIME_ZPOOL_SCRIPT'];

function size_string_to_mb($str) {
	switch ($str) {
		case (preg_match('/P$/', $str) ? true : false):
			// PB
			$multiplier = 0.000000001;
			break;
		case (preg_match('/T$/', $str) ? true : false):
			// TB
			$multiplier = 0.000001;
			break;
		case (preg_match('/G$/', $str) ? true : false):
			// GB
			$multiplier = 0.001;
			break;
		case (preg_match('/M$/', $str) ? true : false):
			// MB
			$multiplier = 1;
			break;
		case (preg_match('/K$/', $str) ? true : false):
			// KB
			$multiplier = 1000;
			break;
		default:
			// unknown; pass $str back unchanged
			return $str;
			break;
	}
	$size = substr($str, 0, -1);
	$size = $size * $multiplier;
	return $size;
}


/*if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $cmd = "..\..\..\scripts\agentcmd -s -p $port $host rexec $pass $scri";
} else {
    $cmd = "../../../scripts/agentcmd -s -p $port $host rexec $pass $scri";
}
$output = shell_exec($cmd);*/

$output = uptime_remote_custom_monitor($host, $port, $pass, $scri, '');

$out_arr_sin = preg_split("/\n/",$output);
//print_r($out_arr_sin);
//unset($out_arr_sin[0]);
//unset($out_arr_sin[1]);
$head = array();
$subhead = array();
$head = $out_arr_sin[0];
$subhead = $out_arr_sin[1];
$headarr = preg_split("/\s\s+/",trim($head));
$subheadarr = preg_split("/\s\s+/",trim($subhead));
array_filter($out_arr_sin);

$poolname='';
$raidname='';
$alloc='';
$free='';
$j=0; // zpool mirror counter
for($i=2;$i<count($out_arr_sin);$i++)
{
	if($out_arr_sin[$i] == '') { continue; }
	
	if(preg_match("/---/",$out_arr_sin[$i])) {
		$i++;
		$resarr = preg_split("/\s\s+/",trim($out_arr_sin[$i]));
		//print_r($resarr);
		$poolname = $resarr[0];
		continue;
	}
	elseif(preg_match("/logs/",$out_arr_sin[$i]) || preg_match("/cache/",$out_arr_sin[$i])) {
		continue;
	}
	elseif((preg_match("/mirror/",$out_arr_sin[$i])) || (preg_match("/raidz/",$out_arr_sin[$i]))) {
		$resarr = preg_split("/\s\s+/",trim($out_arr_sin[$i]));
		$raidname = $resarr[0];
		$alloc = $resarr[1];
		$free = $resarr[2];
	        continue;
	}
	else
	{
		$resarr = preg_split("/\s\s+/",trim($out_arr_sin[$i]));
		
		for($k=0;$k<count($headarr);$k++)
		{
			switch ($k) {
				case 0: // capacity header
					// alloc column
					if($resarr[1] != '-') {
						$alloc = $resarr[1];
					}
					$alloc = size_string_to_mb($alloc);
					
					// free column
					if($resarr[2] != '-') {
						$free = $resarr[2];
					}
					$free = size_string_to_mb($free);
					
					echo $resarr[0].".".$headarr[$k]."_".$subheadarr[1]." " .$alloc."\n";
					echo $resarr[0].".".$headarr[$k]."_".$subheadarr[2]." " .$free."\n";
					continue;
					break;
				case 1: // operations header
					$a = 3;
					echo $resarr[0].".".$headarr[$k]."_".$subheadarr[$a]." " .$resarr[$a]."\n";
					echo $resarr[0].".".$headarr[$k]."_".$subheadarr[$a+1]." " .$resarr[$a+1]."\n";
					break;
				case 2: // bandwidth header
					$a = 5;
					echo $resarr[0].".".$headarr[$k]."_".$subheadarr[$a]." " . size_string_to_mb($resarr[$a]) . "\n";
					echo $resarr[0].".".$headarr[$k]."_".$subheadarr[$a+1]." " . size_string_to_mb($resarr[$a+1]) . "\n";
					break;
				case 3: // latency header - unsupported
				default: 
					continue;
					break;
			}
		}	
	}
}
?>
