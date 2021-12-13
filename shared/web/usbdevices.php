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
$allDevices = enrichDevices($allDevices,$allowedFiles);

//$allDevices = findRealDevices($allowedFiles,$allDevices,$foundedFile);
?>
<html>
	<head>
		
		<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="basic.css">
	</head>
	<body>
		<?php require_once("menu.php");?>
	<hr>
		<table>
			<tr>
				<th>
					Manufacturer
				</th>
				<th>
					Product
				</th>
				<th>
					Type
				</th>
				<th>
					Serial
				</th>
				<th>
					Path
				</th>
			</tr>

<?php
$i=0;
foreach($allDevices as $device){
	if($i%2==0){
		echo "<tr class='bcolor'>";
	}else{
		echo "<tr>";
	}
	$i++;
	echo "<td>".$device['manufacturer']."</td>";
	echo "<td>".$device['product']."</td>";
	echo "<td>".(isset($device['type'])?$device['type']:"")."</td>";
	echo "<td>".$device['serial']."</td>";
	echo "<td>";
	
	if(isset($device['paths'])){
		echo "<ul>";
		foreach($device['paths'] as $path){
			echo "<li>".$path."</li>";
		}
		echo "</ul>";
	}
	
	echo "</tr>";
}
?>
		</table>
		<hr>
	</body>
</html>
	