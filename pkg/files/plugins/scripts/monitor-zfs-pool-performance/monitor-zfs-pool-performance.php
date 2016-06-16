<?php
$port = $_SERVER['UPTIME_UPTIME_PORT'];
$host = $_SERVER['UPTIME_UPTIME_HOSTNAME'];
$pass = $_SERVER['UPTIME_UPTIME_ZPOOL_PASSWORD'];
$scri = $_SERVER['UPTIME_UPTIME_ZPOOL_SCRIPT'];
if($host == ''){
    $host = $_SERVER['UPTIME_HOSTNAME'];
}

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $cmd = "..\..\..\scripts\agentcmd -s -p $port $host rexec $pass $scri";
} else {
    $cmd = "../../../scripts/agentcmd -s -p $port $host rexec $pass $scri";
}
$output = shell_exec($cmd);
$out_arr_sin = preg_split("/\n/",$output);
unset($out_arr_sin[0]);
//unset($out_arr_sin[1]);
$head = array();
$subhead = array();
$head = $out_arr_sin[1];
$subhead = $out_arr_sin[2];
$headarr = preg_split("/\s\s+/",trim($head));
$subheadarr = preg_split("/\s\s+/",trim($subhead));
array_filter($out_arr_sin);

$poolname='';
$raidname='';
$alloc='';
$free='';
$j=0; // zpool mirror counter
for($i=3;$i<count($out_arr_sin);$i++)
{
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
					// free column
					if($resarr[2] != '-') {
						$free = $resarr[2];
					}
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
					echo $resarr[0].".".$headarr[$k]."_".$subheadarr[$a]." " .$resarr[$a]."\n";
					echo $resarr[0].".".$headarr[$k]."_".$subheadarr[$a+1]." " .$resarr[$a+1]."\n";
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
