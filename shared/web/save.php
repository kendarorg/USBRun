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
$foundedFile['disabled']=$_POST['disabled'].""=="true";
$foundedFile['eject']=$_POST['eject'].""=="true";

$dataDir = $GLOBALS['settings']['usbdatadir'];
$allowedFiles = getDataFiles($dataDir,$uidAndGroup,$isAdmin);
$allDevices = lsusb();
$allDevices = findDisks($allDevices);

$allDevices[] = [
	'serial'=>'Fake',
	'product'=>'Dummy',
	'manufacturer'=>'Dummy',
	'paths'=>[
		'/dummy'
	],
	'busnum'=>'-1',
	'devnum'=>'-1',
	'type'=>'dummy'
];

$data = findRealDevices($allowedFiles,$allDevices,$foundedFile);

$foundedFile = $data['file'];
$foundedDevice = $data['device'];

$foundedFile["uid"]= $uidAndGroup['uid'];
if(isset($foundedDevice['paths']) && !isset($foundedFile["paths"])){
	$foundedFile["paths"]= $data['paths'];
}
$foundedFile["product"]= $foundedDevice['product'];
$foundedFile["serial"]= $foundedDevice['serial'];
$foundedFile["manufacturer"]= $foundedDevice['manufacturer'];
//echo var_export($allDevices,true);
$tosave = json_encode($foundedFile);
file_put_contents($GLOBALS['home']."/".$foundedFile['id'].".json",$tosave);

runShellCommand(
	"mv ".$GLOBALS['home']."/".$foundedFile['id'].".json ".$dataDir,
	$GLOBALS['settings']['admin']);
echo $tosave;