<?php

require_once(dirname(__FILE__)."/commons.php");


function getmounts($path){
	return runShellCommand(
		$GLOBALS['settings']['cat'].' /proc/mounts|grep '.$path.'|cut -f1 "-d "',
		$GLOBALS['settings']['admin']);
}



function getgphotofs(){	
	return runShellCommand(
		$GLOBALS['settings']['cat'].' /proc/mounts|grep gphotofs|cut -f2 "-d "',
		$GLOBALS['settings']['admin']);
	
}

function isphotofsusbdeclaration($data){
	return strpos($data,"/share/mtp_external/usb.")!==false;
}

function getphotofsdriveexternal($shareMtpExternal){
	return str_replace("\\040"," ",str_replace("/share/mtp_external/","/share/",$shareMtpExternal));
}


function parsephotofssubdeclaration($data){
	$partial = explode(".",$data);
	$busDeclaration = [];
	$busDeclaration["devnum"]=ltrim($partial[2],"0");
	$busDeclaration["busnum"]=ltrim($partial[1],"0");
	return $busDeclaration;
}


function cleanudevadmline($line){
	$isAttr = strpos($line,"ATTRS");
	if($isAttr !== false && $isAttr==0){
		$line = substr($line,strlen('ATTRS{'));
		$line = str_replace('}=="','|',$line);
		$line = rtrim($line,'"');
		return explode("|",$line);
	}
	return null;
}

function getudevadm($usbDisk){
	$partial = runShellCommand(
		$GLOBALS['settings']['udevadm'].' info --name='.$usbDisk.' --attribute-walk  2>&1',
		$GLOBALS['settings']['admin']);
	if($partial[0]=="device node not found"){
		return null;
	}
	$result=[];
	foreach($partial as $line){
		$data = cleanudevadmline($line);
		if($data != null && !isset($result[$data[0]])){
			$result[$data[0]]=trim($data[1]);
		}
	}
	return $result;
}

function getdisklabelblkid($diskid){
	
	$partial = runShellCommand(
		$GLOBALS['settings']['blkid'].'|grep '.$diskid,
		$GLOBALS['settings']['admin']);
	 $label = explode('LABEL="',$partial[0])[1];
	 return explode('"',$label)[0];
}

function getdisklabel($diskid){
	
	$partial = runShellCommand(
		$GLOBALS['settings']['cat'].' /proc/mounts|grep '.$diskid.'|cut -f2 "-d "',
		$GLOBALS['settings']['admin']);
	 $partial = preg_split("/[\/]/",$partial[0],-1,PREG_SPLIT_NO_EMPTY);
	 $nameToFind = $partial[sizeof($partial)-1];
	 
	 
	$partial = runShellCommand(
		'ls -la /share|grep '.$nameToFind,
		$GLOBALS['settings']['admin']);
	if(sizeof($partial)==0){
		return null;
	}
	 
	 $partial = preg_split("/[\s]+/",$partial[0],-1,PREG_SPLIT_NO_EMPTY);
	 if(sizeof($partial)>3){
	 	return null;
	 }
	 return $partial[sizeof($partial)-3];
}

function findDisks($devices){
	
	//FIND USB DISKS
	$allUsbDisks = getmounts("/dev/sdd");	//cat /proc/mounts|grep /dev/sd|cut -f1 "-d "
	foreach($allUsbDisks as $usbDisk){
		$udevAdm = getudevadm($usbDisk);	//udevadm info --name=/dev/sdd1 --attribute-walk
		
		for($i=0;$i< sizeof($devices); $i++){	
			if($udevAdm['busnum'] == $devices[$i]['busnum'] && $udevAdm['devnum'] == $devices[$i]['devnum']){
				$label = getdisklabel($usbDisk);
				if($label!=null){
					$devices[$i]['paths']=[];
					$devices[$i]['paths'][] = "/share/".$label;
					$devices[$i]['type']="usbdrive";
					break;
				}
			}
		}
	}
	
	//FIND GOOGLE PHONES
	$gphotofs = getgphotofs();	//cat /proc/mounts|grep gphotofs

	for($i=0;$i<sizeof($gphotofs);$i++){
		if(isphotofsusbdeclaration($gphotofs[$i])){	///share/mtp_external/usb.001.008
			$busDevice = parsephotofssubdeclaration($gphotofs[$i]); //return [1,8]
			$i++;
			for($k=0;$k< sizeof($devices); $k++){	
				if($busDevice['busnum'] == $devices[$k]['busnum'] && $busDevice['devnum'] == $devices[$k]['devnum']){
					$devices[$k]['paths']=[];
					$devices[$k]['type']="androidphone";
					while($i< sizeof($gphotofs) && !isphotofsusbdeclaration($gphotofs[$i])){
						$devices[$k]['paths'][] = getphotofsdriveexternal($gphotofs[$i]);	///share/mtp_external/LG\040K10/Phone && /share/mtp_external/LG\040K10/Card
						$i++;
					}
					$i--;
					break;
				}
			}
		}
	}
	return $devices;
}
