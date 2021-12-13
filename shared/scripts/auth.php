<?php


require_once(dirname(__FILE__)."/commons.php");

function dieHorribly(){
	http_response_code(401);
	echo "Unauthorized";
	die();	
}

function findUserId(){
	if(!isset($_SERVER['HTTP_COOKIE'])){
		dieHorribly();
	}
	$cookie = explode(";",$_SERVER['HTTP_COOKIE']);
	
	foreach($cookie as $coval){
		$val = trim($coval);
		$ar = explode("=",$val);
		$key = trim(trim($ar[0]),"_");
		$value = trim($ar[1]);
		if($key=="NAS_USER"){
			$GLOBALS['uid']=$value;
			//$groupsResult = runShellCommand("groups ".$value,$GLOBALS['uid'],true);
			$cmd = "groups ".$GLOBALS['uid'];
			$groupsResult = trim(shell_exec($cmd ));
			$groups = preg_split("/[\s]+/",$groupsResult,-1,PREG_SPLIT_NO_EMPTY);
			//TODOK: $GLOBALS['home'] = getTempPath($GLOBALS['uid']);
			$GLOBALS['home'] = getTempPath($GLOBALS['uid']);
			
			if(strpos($_SERVER['PHP_SELF'],"index.php")!==false){
				klog("initialize permissions");
				if(!is_dir($GLOBALS['home'])){
					runShellCommand("mkdir -p ".$GLOBALS['home']." 2>&1",$GLOBALS['uid']);
					runShellCommand("chmod 777 ".$GLOBALS['home']." 2>&1",$GLOBALS['uid']);
					runShellCommand("chmod 777 ".getTempPath()." 2>&1",$GLOBALS['uid']);
				}
				runShellCommand("chmod 777 ".$GLOBALS['base']."/settings.php 2>&1");
				
				runShellCommand("mkdir -p ".$GLOBALS['settings']['usbdatadir']." 2>&1");
				runShellCommand("touch ".$GLOBALS['settings']['usbdatadir']."/keep 2>&1");
				runShellCommand("chmod 777 ".$GLOBALS['settings']['usbdatadir']." 2>&1");
				runShellCommand("chmod 777 ".$GLOBALS['settings']['usbdatadir']."/* 2>&1");
				runShellCommand("touch ".$GLOBALS['settings']['datadir']."/keep 2>&1");
				runShellCommand("chmod 777 ".$GLOBALS['settings']['datadir']." 2>&1");
				runShellCommand("chmod 777 ".$GLOBALS['settings']['mainerrorlog']." 2>&1");
			}
			
			
			return [
				"uid"=>$value,
				"groups"=>$groups
			];
		}
	}
	dieHorribly();
}

function isAdmin($uidData){
	foreach($uidData["groups"] as $group){
		if(strtolower($group) == $GLOBALS['settings']['admingroup']){
			return true;
		}
	}
	return false;
}

