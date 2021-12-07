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
$foundedFile['name']=$_POST['name'];

$logs = $GLOBALS['home']."/".$foundedFile['id'].".log";
$pidPath = $GLOBALS['home']."/".$foundedFile['id'].".pid";

if(!isPidRunning($pidPath)){
	klog("finished ".$foundedFile["name"]." pid: ".$pid, LWARNING);
    return;
}

killScreen($foundedFile['id']);
/*
$data = runShellCommand(
	$GLOBALS['settings']['screen']." -list|grep  ".$foundedFile['id']."|cut -f2",
	$GLOBALS['settings']['admin'],true);

$screenId = trim(explode(":",$data)[0]);
echo runShellCommand(
	$GLOBALS['settings']['screen']."  -XS ".$id." quit",
	$GLOBALS['settings']['admin'],true);*/