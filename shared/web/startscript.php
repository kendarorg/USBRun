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
$dataDir = $GLOBALS['settings']['usbdatadir'];
$allowedFiles = getDataFiles($dataDir,$uidAndGroup,$isAdmin);
$allDevices = lsusb();
$allDevices = findDisks($allDevices);

$data = findRealDevices($allowedFiles,$allDevices,$foundedFile);
$foundedFile = $data['file'];
$foundedDevice = $data['device'];
$paths =[];
if(isset($foundedDevice['paths'])){
	$paths = $foundedDevice['paths'];
}
runAction($foundedFile,$foundedDevice,$paths);


