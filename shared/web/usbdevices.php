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

$dataDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."data/usb";
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
	<body>
		<?php require_once("menu.php");?>
	<hr>
		<table  border="1px">
			<tr>
				<td>
					Name
				</td>
				<td>
					Type
				</td>
				<td>
					Serial
				</td>
				<td>
					Path
				</td>
			</tr>

<?php
foreach($allDevices as $device){
	echo "<tr>";
	echo "<td>".$device['manufacturer']."=>".$device['product']."</td>";
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
	</body>
</html>
	