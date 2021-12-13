<span>
<img class=center src="USBRun.gif"/>
</span>
<?php

function loadState($isAdmin){
	$states =[
		'index.php?uuid='.guidv4()=>"Main"
	];
	if($isAdmin){
		$states['usbscripts.php?uuid='.guidv4()]="USB Scripts";
		$states['usbdevices.php?uuid='.guidv4()]="USB Devices";
		$states['setup.php?uuid='.guidv4()]="Setup";
	}
	
	$realFileName = basename($_SERVER["PHP_SELF"], '.php').".php"; 
	
	foreach($states as $state=>$title){
		$name = explode("?",$state);
		if($name[0]==$realFileName){
			echo "<span class='menubuttonselected'>".$title."</span>";
		}else{
			echo "<a class='menubutton' href='".$state."'>".$title."</a>";
		}
	}
	if($realFileName=="usbmaintenance.php"){
		echo "<span class='menubuttonselected'>Edit</span>";
	}
	if($realFileName=="usblogs.php"){
		echo "<span class='menubuttonselected'>Logs</span>";
	}
}

loadState($isAdmin);
?>
Copyright 2021-2022 <a target="_blank" rel="noopener noreferrer" href="https://www.kendar.org">Enrico Da Ros</a>