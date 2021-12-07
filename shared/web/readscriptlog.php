<?php 

require_once(dirname(dirname(__FILE__))."/scripts/commons.php");
require_once($GLOBALS['base']."/scripts/auth.php");
require_once($GLOBALS['base']."/scripts/processes.php");
require_once($GLOBALS['base']."/scripts/usb.php");
require_once($GLOBALS['base']."/scripts/disks.php");


$uidAndGroup = findUserId();
$isAdmin = isAdmin($uidAndGroup);
if(!$isAdmin){
	die();
}
$foundedFile=[];
$foundedFile['id']=$_POST['id'];
$foundedFile['script']=$_POST['script'];
$foundedFile['serial']=$_POST['serial'];
$foundedFile['name']=$_POST['name'];
$pollid=$_POST['pollid'];


$result=[];

$startAt=0;
if(isset($_POST['start'])){
	$startAt = $_POST['start']+0;
}

$logs = $GLOBALS['home']."/".$foundedFile['id'].".log";
$pidPath = $GLOBALS['home']."/".$foundedFile['id'].".pid";

function getfilesize($path){
	$data = readFileIfExists($path);
	if($data==null) return 0;
	return strlen($data);
}

function getfilecontent($path,$startAt=0){
	$data = readFileIfExists($path);
	if($data==null) return "";
	if($startAt>= strlen($data)){
		return "";
	}
	return substr($data,$startAt);
}

$result['pollid']=$pollid;
if($pollid=="0" && file_exists($logs)){
	$result['status']='RUNNING';
	$result['start']=$startAt;
	$result['end']=$startAt;
	$content = getfilecontent($logs,$startAt);
	$result['end'] = $startAt+strlen($content);
	$result['content'] = str_replace("\n","<br>\n",getfilecontent($logs,$startAt));
}else if (!file_exists( $pidPath )){
	klog("finished ".$foundedFile["name"],LWARNING);
	http_response_code(404);
	$result['status']='FINISHED';
}else{
	$doLastRead = false;
	if(file_exists($logs)){
		$fileSize = getfilesize($logs);
		$result['fileSize']=$fileSize;
		$doLastRead = $fileSize != $startAt;
	}
	if (!isPidRunning($pidPath) && !$doLastRead){
		klog("finished ".$foundedFile["name"],LWARNING);
		http_response_code(404);
		$result['status']='FINISHED';
	}else{
		if(file_exists($logs)){
			$result['status']='RUNNING';
			$result['start']=$startAt;
			$result['end']=$startAt;
			$content = getfilecontent($logs,$startAt);
			$result['end'] = $startAt+strlen($content);
			$result['content'] = str_replace("\n","<br>\n",getfilecontent($logs,$startAt));
		}else{
			$result['status']='FINISHED';
		}
	}
}
echo json_encode($result);