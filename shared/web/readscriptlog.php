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
//$pollid=$_POST['pollid'];


$result=[];

$startAt=0;
$count=100;
if(isset($_POST['start'])){
	$startAt = $_POST['start']+0;
}
if(isset($_POST['count'])){
	$count = $_POST['count']+0;
}

$logs = $GLOBALS['home']."/".$foundedFile['id'].".log";
$pidPath = $GLOBALS['home']."/".$foundedFile['id'].".pid";

$fileData = readPartialFileIfExists($logs,$startAt,$startAt+$count);
$running = isPidRunning($pidPath);
$fileLines = getFileLines($logs);
$counted = sizeof(explode("\n",$fileData));
$data = [
	'content'=>str_replace("\n","<br>\n",$fileData),
	'running'=>$running,
	'lines'=>$fileLines,
	'counted'=>$counted
];

echo json_encode($data);
die();