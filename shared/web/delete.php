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

$logs = $GLOBALS['home']."/".$foundedFile['id'].".log";
$pidPath = $GLOBALS['home']."/".$foundedFile['id'].".pid";
$shPath = $GLOBALS['home']."/".$foundedFile['id'].".sh";

$jsonFile = $GLOBALS['settings']['usbdatadir']."/".$foundedFile['id'].".json";


if (file_exists( $logs )){
	runShellCommand("rm ".$logs,$GLOBALS['settings']['admin']);
}

if (file_exists( $shPath )){
	runShellCommand("rm ".$shPath,$GLOBALS['settings']['admin']);
}

if (file_exists( $pidPath )){
	runShellCommand("rm ".$pidPath,$GLOBALS['settings']['admin']);
}



runShellCommand("rm ".$jsonFile,$GLOBALS['settings']['admin']);