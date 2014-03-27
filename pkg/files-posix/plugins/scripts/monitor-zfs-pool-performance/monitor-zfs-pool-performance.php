<?php
$port = $_SERVER['UPTIME_UPTIME_PORT'];
$host = $_SERVER['UPTIME_UPTIME_HOSTNAME'];
$pass = $_SERVER['UPTIME_UPTIME_ZPOOL_PASSWORD'];
$scri = $_SERVER['UPTIME_UPTIME_ZPOOL_SCRIPT'];
if($host == ''){
    $host = $_SERVER['UPTIME_HOSTNAME'];
}
$cmd = "../agentcmd -p $port $host rexec $pass $scri";
$output = shell_exec($cmd);
$out_arr_sin = preg_split("/\n/",$output);
unset($out_arr_sin[0]);
unset($out_arr_sin[1]);
$head = array();
$subhead = array();
$head = $out_arr_sin[2];
$subhead = $out_arr_sin[3];
$headarr = preg_split("/\s\s+/",trim($head));
$subheadarr = preg_split("/\s\s+/",trim($subhead));
array_filter($out_arr_sin);
for($i=4;$i<count($out_arr_sin);$i++)
{
	if(preg_match("/---/",$out_arr_sin[$i]))
	continue;
	elseif(preg_match("/rpool/",$out_arr_sin[$i]))
        continue;
	else
	{
		$resarr = preg_split("/\s\s+/",trim($out_arr_sin[$i]));
		$a=1;
		for($k=0;$k<count($headarr);$k++)
		{
			if($k==0)
			$a=1;
			elseif($k==1)
			$a=3;
			elseif($k==2)
			$a=5;
			echo $resarr[0].".".$headarr[$k]."_".$subheadarr[$a]." " .$resarr[$a]."\n";
			echo $resarr[0].".".$headarr[$k]."_".$subheadarr[$a+1]." " .$resarr[$a+1]."\n";
			$a++;
		}	
	}
	
}
?>
