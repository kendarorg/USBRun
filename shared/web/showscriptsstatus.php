<?php

require_once(dirname(dirname(__FILE__))."/scripts/commons.php");
require_once($GLOBALS['base']."/scripts/auth.php");
require_once($GLOBALS['base']."/scripts/processes.php");
require_once($GLOBALS['base']."/scripts/usb.php");


$uidAndGroup = findUserId();
$isAdmin = isAdmin($uidAndGroup);
if(!$isAdmin){
	die();
}



$dataDir = $GLOBALS['settings']['usbdatadir'];
$allowedFiles = getDataFiles($dataDir,$uidAndGroup,$isAdmin);

echo "[";
for($i=0;$i< sizeof($allowedFiles);$i++){
	$file = $allowedFiles[$i];
	if($i>0)echo ",";
	echo '{"id":"'.$file["action"]["id"].'","running":';
	if($file["running"]==true || $file["running"]=="true"){
		echo "true";
	}else{
		echo "false";
	}
	echo "}";
}
echo "]";
/*{
	"id":"id",
	"running":true
}*/