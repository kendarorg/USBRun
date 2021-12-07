<?php

require_once(dirname(__FILE__)."/commons.php");
require_once($GLOBALS['base']."/scripts/apis.php");
require_once($GLOBALS['base']."/scripts/usb.php");
require_once($GLOBALS['base']."/scripts/disks.php");
require_once($GLOBALS['base']."/scripts/processes.php");





function runActionUdev($action,$busDevice,$paths){
	systemLog("Recognized ".$action["name"]." on ".$action["product"]."/".$action["manufacturer"]);
	klog("==================================");
	klog("Recognized ".$action["name"]." on ".$action["product"]."/".$action["manufacturer"]);

	$home = getTempPath($action["uid"]);
	if(!is_dir($home)){
		mkdir($home,777,true);
	}
	$processName = $action["id"];
	$pidPath = $home."/".$processName.".pid";
	$executableFile = $home."/".$processName.".sh";
	$logs = $home."/".$processName.".log";
	
	
	if(isPidRunning($pidPath)){
		$pid = readFileIfExists($pidPath);
		systemLog("Already running ".$action["name"]." on ".$action["product"]."/".$action["manufacturer"]." pid:".$pid);
		klog("Already running ".$action["name"]." pid: ".$pid,LWARNING);
	    return;
	}
	
	
	$executionData = str_replace('"','\\"',$action["name"]." on ".$action["product"]."/".$action["manufacturer"]);
	$executableFileContent = 
		"#!/bin/bash\necho $$ > ".$pidPath."\n".
		"cd ".$GLOBALS['settings']['homes']."/".$action["uid"]."\n".
		$action["script"]."\n";	
		
	klog("Preparing script ".$executableFileContent,LTRACE);
	
	file_put_contents($executableFile,$executableFileContent);
	chmod ($executableFile, 777 );	
	file_put_contents($logs,"");
	chmod ($logs, 777 );	
	file_put_contents($pidPath,"");
	chmod ($pidPath, 777 );
	
	systemLog("Starting ".$executionData);
	runShellCommand(
		$GLOBALS['settings']['screen'].' -dmS '.$processName.' -dm bash  -c "'.$executableFile.' >> '.$logs.' 2>&1"',
		$action["uid"]);
	
	chmod ($logs, 777 );
	
	while(isPidRunning($pidPath)){
		sleep(60);
	}
	
	if(isset($action["paths"])&& sizeof($action["paths"])>0 && isset($action["eject"]) && $action["eject"]){
		$volumeId = getVolume($action["paths"]);
		if($volumeId!=null){
			ejectVolume($volumeId);
			klog("Ejected ".implode(" ",$action["paths"]));
		}
		
	}
	beep();
	chmod ($executableFile, 777 );	
	chmod ($logs, 777 );	
	chmod ($pidPath, 777 );
	systemLog("Finishing ".$executionData);
}

function isMatchingUdev($jobData,$deviceData,$variable){
	if(isset($jobData[$variable]) && isset($deviceData[$variable])){
		return $jobData[$variable]==$deviceData[$variable];
	}
	return true;
}

function findActionForItemUdev($udevAdm,$paths){
	$dataDir = $GLOBALS['settings']['usbdatadir'];
	$fileList = glob($dataDir.DIRECTORY_SEPARATOR.'*.json');
	foreach($fileList as $filename){
	    if(is_file($filename)){
	    	$candidate = file_get_contents($filename);
	    	$data = json_decode($candidate,true);
	    	if(isset($data['paths'])){
		    	$tmpPaths = $data['paths'];
	    		$data['paths']=[];
		    	foreach($tmpPaths as $key=>$value){
		    		$data['paths'][]=$value;
		    	}
		    }
	    	if(isset($data['disabled'])&& $data['disabled']==true)continue;
	    	if($data['serial'] == $udevAdm['serial']){
	    		
	    		if(isset($data['paths'])){
	    			foreach($data['paths'] as $ph){
	    				if(!is_dir($ph)){
	    					klog("No path set ".$ph,LWARNING);
			    			return null;
			    		}
	    			}
	    		}
	    		
	    		return $data;
	    	}
	    }   
	}
	return null;
}

function executeRunScript($arguments){

	sleep(60);
	$GLOBALS['uid']=shell_exec($GLOBALS['settings']['whoami']);
	$GLOBALS['home'] = getTempPath($GLOBALS['uid']);

	setErrorLog();

	$busnum=ltrim($arguments[1],"0");
	$devnum=ltrim($arguments[2],"0");
	$manufacturer=$arguments[3];
	$product=$arguments[4];
	$serial=$arguments[5];


	systemLog("Seeking ".$product."/".$manufacturer."/".$serial);
	klog("Seeking ".$product."/".$manufacturer."/".$serial,LTRACE);

	//FIND USB DISKS
	$allUsbDisks = getmounts("/dev/sdd");	//cat /proc/mounts|grep /dev/sd|cut -f1 "-d "
	foreach($allUsbDisks as $usbDisk){
		$udevAdm = getudevadm($usbDisk);	//udevadm info --name=/dev/sdd1 --attribute-walk
		klog("Udevadm ".var_export($udevAdm,true),LTRACE);
		if($udevAdm!=null && $udevAdm['devnum']==$devnum && 
			$udevAdm['busnum']==$busnum && $udevAdm['serial']==$serial){
			$udevAdm['manufacturer']=$manufacturer;
			$udevAdm['product']=$product;
			$udevAdm['type']="usbdrive";
			//The device is the one just inserted
			$paths = ["/share/".getdisklabelblkid($usbDisk)];	//cat /proc/mounts|grep /dev/sdd1|cut -f2 "-d "
			klog("Paths founded ".var_export($paths,true),LTRACE);
			$action = findActionForItemUdev($udevAdm,$paths);
			if($action!=null){
				runActionUdev($action,$udevAdm,$paths);
				die();
			}
		}
	}


	//FIND GOOGLE PHONES
	$gphotofs = getgphotofs();	//cat /proc/mounts|grep gphotofs

	for($i=0;$i<sizeof($gphotofs);$i++){
		if(isphotofsusbdeclaration($gphotofs[$i])){	///share/mtp_external/usb.001.008
			$busDevice = parsephotofssubdeclaration($gphotofs[$i]); //return [1,8]
			klog("BusDevice ".var_export($busDevice,true),LTRACE);
			$i++;
			if($busDevice['devnum']==$devnum && $busDevice['busnum']==$busnum){
				$busDevice['serial'] = getserialfromdevice($busDevice);	//lsusb -s 1:8 -v|grep iSerial
				$busDevice['manufacturer']=$manufacturer;
				$busDevice['product']=$product;
				$busDevice['type']="androidphone";
				$paths=[];
				while($i< sizeof($gphotofs) && !isphotofsusbdeclaration($gphotofs[$i])){
					$paths[] = getphotofsdriveexternal($gphotofs[$i]);	///share/mtp_external/LG\040K10/Phone && /share/mtp_external/LG\040K10/Card
					$i++;
				}
				$action = findActionForItemUdev($busDevice,$paths);
				if($action!=null){
					runActionUdev($action,$busDevice,$paths);
					die();
				}
				$i--;
			}
		}
	}

	//FIND GENERIC DEVICES
	$busDevice = [];
	$busDevice['devnum']=$devnum;
	$busDevice['busnum']=$busnum;
	$busDevice['manufacturer']=$manufacturer;
	$busDevice['product']=$product;
	$busDevice['serial']=$serial;
	$busDevice['type']="genericusb";
	$paths = [];
	$action = findActionForItemUdev($busDevice,$paths);
	if($action!=null){
		runActionUdev($action,$busDevice,$paths);
		die();
	}

	systemLog("No runner for ".$busDevice["product"]."/".$busDevice["manufacturer"]);
	klog("Nothing to run",LWARNING);

	cleanLogs();
}

if (isset($argc) && $argc==6) {
	executeRunScript($argv);
}