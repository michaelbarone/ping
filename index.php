<?php
if(isset($_GET['ip']) && !$_GET['ip']==''){
	$ip = $_GET['ip'];
} elseif(isset($_GET['ips']) && !$_GET['ips']==''){
	$ip = $_GET['ips'];
} else {
	echo "please set ?ip=[local lan ip] in the request";
	exit;
}

$ips = array();
$ips = explode(",",$ip);
$returnarray = array();
foreach($ips as $thisip){
	$result = Ping($thisip);
	array_push($returnarray, $result);
}

header('Content-Type: application/json');
$returnarray = json_encode($returnarray);

echo $returnarray;
//echo ")]}',\n".$returnarray;

exit;


function Ping($ip) {
	$pingurl = $ip;
	$disallowed = array('http://', 'https://');
	foreach($disallowed as $d) {
		if(strpos($pingurl, $d) === 0) {
		   $thisip = strtok(str_replace($d, '', $pingurl),':');
		}
	}
	if(!isset($thisip)){ $thisip = $pingurl; }
	if(strpos($thisip, "/") != false) {
		$thisip = substr($thisip, 0, strpos($thisip, "/"));
	}
	$returnArray = array();
	$newping = array();
	$sent = 0;
	$lost = 0;
	$timeMax = 0;
	$timeAve = 0;
	$status = 1;
	if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
		$pingresult = exec("ping -n 2 -w 2 $thisip", $output, $status);
		if(isset($output[6])){
			$exoutput = explode(',',$output[6]);
			$sent = preg_replace('/\D/', '', $exoutput[0]);
			$lost = $sent - preg_replace('/\D/', '', $exoutput[1]);
		}
		if(isset($output[8])){
			$exoutput = explode(',',$output[8]);
			$timeMax = preg_replace('/\D/', '', $exoutput[1]);
			$timeAve = preg_replace('/\D/', '', $exoutput[2]);
		}
	} else {
		$pingresult = exec("/bin/ping -c2 -w2 $thisip", $output, $status);
		if(isset($output[5])){
			$exoutput = explode(',',$output[5]);
			$sent = preg_replace('/\D/', '', $exoutput[0]);
			$lost = $sent - preg_replace('/\D/', '', $exoutput[1]);
		}elseif(isset($output[3])){
			$exoutput = explode(',',$output[3]);
			$sent = preg_replace('/\D/', '', $exoutput[0]);
			$lost = $sent - preg_replace('/\D/', '', $exoutput[1]);
		}			
		if(isset($output[6])){
			$exoutput = explode('=',$output[6]);
			$exoutput = explode('/',$exoutput[1]);
			$timeMax = round($exoutput[2]);
			$timeAve = round($exoutput[1]);
		}			
	}
	$newping['sent']=$sent;
	$newping['lost']=$lost;
	$newping['timeMax']=$timeMax;
	$newping['timeAve']=$timeAve;
	$returnArray['stats']=$newping;
	$returnArray['lastUpdate']=time();
	$returnArray['ip']=$ip;
	if ($status == "0") {
		$returnArray['status']="on";
	} else {
		$returnArray['status']="off";
	}
	return $returnArray;
}
?>