<?php

require_once(dirname(__FILE__)."/commons.php");

function isRunning($action){
	return isPidRunning(getTempPath($action["uid"],$action['id'].".pid"));
}

function getDataFiles($dataDir,$uidAndGroup,$isAdmin){
	
	$fileList = glob($dataDir.DIRECTORY_SEPARATOR.'*.json');
	$allowedFiles=[];
	foreach($fileList as $filename){
	    if(is_file($filename)){
	    	$candidate = readFileIfExists($filename);
	    	$action = json_decode($candidate,true);
	    	if(isset($action['paths'])){
		    	$tmpPaths = $action['paths'];
	    		$action['paths']=[];
		    	foreach($tmpPaths as $key=>$value){
		    		$action['paths'][]=$value;
		    	}
		    }
	    	if($action["uid"]==$uidAndGroup["uid"] || $isAdmin){
	    		$allowedFiles[]=[
	    			"file"=>$filename,
	    			"action"=>$action,
	    			"running"=>isRunning($action)];
	    	}
	    }
	}
	return $allowedFiles;
}

function findRealDevices($allowedFiles,$allDevices,$foundedFile){
	$foundedDevice = null;
	foreach($allowedFiles as $file){
		$localDevice=null;
		foreach($allDevices as $device){
			if($file["action"]["serial"]==$device["serial"]){
				$localDevice = $device;
				break;
			}
		}
		if($localDevice == null){
			$allDevices[]=[
				'serial'=>$file["action"]['serial'],
				'product'=>$file["action"]['product'],
				'manufacturer'=>$file["action"]['manufacturer'],
				'type'=>'disconnected',
				'paths'=>$file["action"]['paths']
			];
		}
		if($foundedFile==null){
			if(isset($_GET['id'])&& $file['action']['id']==$_GET['id']){
				$foundedFile=$file['action'];
				break;
			}else if(isset($_POST['id'])&& $file['action']['id']==$_POST['id']){
				$foundedFile=$file['action'];
				break;
			}
		}
	}
	if($foundedFile!=null){
		foreach($allDevices as $device){
			if($foundedFile["serial"]==$device["serial"]){
				$foundedDevice = $device;
				$foundedFile['product']=$device["product"];
				$foundedFile['manufacturer']=$device["manufacturer"];
				if(isset($device["paths"])){
					$foundedFile['paths']=$device["paths"];
				}
				break;
			}
		}
	}
	
	return [
		'file'=>$foundedFile,
		'device'=>$foundedDevice
	];
}

function runAction($action,$busDevice,$paths){
	
	
	systemLog("Manual Running ".$action["name"]." on ".$action["product"]."/".$action["manufacturer"]);
	klog("==================================");
	
	$processName = $action["id"];
	$pidPath = $GLOBALS['home']."/".$processName.".pid";
	$executableFile = $GLOBALS['home']."/".$processName.".sh";
	$logs = $GLOBALS['home']."/".$processName.".log";
	
	
	if(isPidRunning($pidPath)){
		systemLog("Already running ".$action["name"]." on ".$action["product"]."/".$action["manufacturer"]." pid:".$pid);
		klog("Already running ".$action["name"]." pid: ".$pid,LWARNING);
		http_response_code(409);
	    return;
	}
	
	$executionData = str_replace('"','\\"',$action["name"]." on ".$action["product"]."/".$action["manufacturer"]);
	$executableFileContent = 
		"#!/bin/bash\n".
		"echo $$ > ".$pidPath."\n".
		"echo \"Starting\"\n".
		"cd ".$GLOBALS['settings']['homes']."/".$GLOBALS['uid']."\n".
		$action["script"]."\n";	
		
	file_put_contents($executableFile,$executableFileContent);
	
	cleanLogs();
	runShellCommand("touch ".$logs,
		$GLOBALS['settings']['admin']);
	runShellCommand($GLOBALS['settings']['chmod']." 777 ".$executableFile,
		$GLOBALS['settings']['admin']);
	runShellCommand($GLOBALS['settings']['chmod']." 777 ".$logs,
		$GLOBALS['settings']['admin']);
	runShellCommand($GLOBALS['settings']['chmod']." 777 ".$pidPath,
		$GLOBALS['settings']['admin']);
		
	//Run as normal user
	runShellCommand($GLOBALS['settings']['screen'].' -dmS '.$processName.' -dm bash  -c "'.$executableFile.' > '.$logs.' 2>&1"',
		$GLOBALS['uid']);
}

function killScreen($screenId){
	$data = runShellCommand(
		$GLOBALS['settings']['screen']." -list|grep  ".$screenId."|cut -f2",
		$GLOBALS['settings']['admin'],true);
	$pid = trim(explode(".",$data)[0]);
	
	$fwChildPids = findChildProcesses($pid);
	$childPids = array_reverse($fwChildPids);
	
	for($i=0;$i< (sizeof($childPids)-1) ;$i++ ){
		$chPid =$childPids[$i];
		runShellCommand(
			"kill -STOP ".$chPid,
			$GLOBALS['settings']['admin'],true);
	}
	
	
	$fwChildPids = findChildProcesses($pid);
	$childPids = array_reverse($fwChildPids);
	
	for($i=0;$i< (sizeof($childPids)-1) ;$i++ ){
		$chPid =$childPids[$i];
		runShellCommand(
			"kill -9 ".$chPid,
			$GLOBALS['settings']['admin'],true);
	}
	klog("Killed screen ".$screenId);
	
}
