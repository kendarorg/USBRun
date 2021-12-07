<?php
	$states =[
		'index.php?uuid='.guidv4()=>"Main"
	];
	if($isAdmin){
		$states['usbscripts.php?uuid='.guidv4()]="USB Scripts";
		$states['usbdevices.php?uuid='.guidv4()]="USB Devices";
		$states['setup.php?uuid='.guidv4()]="Setup";
	
	
	}
	
	$realFileName = basename(__FILE__, '.php'); 
	
	foreach($states as $state=>$title){
		if($state==$realFileName){
			echo "<b>".$title."</b>&nbsp;&nbsp;&nbsp;";
		}else{
			echo "<a href='".$state."'>".$title."</a>&nbsp;&nbsp;&nbsp;";
		}
	}
?>