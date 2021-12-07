<?php

require_once(dirname(__FILE__)."/commons.php");

function lsusb()
{
	$partial = runShellCommand($GLOBALS['settings']['lsusb'],$GLOBALS['settings']['admin']);
	
	$result = [];
	$devicesCount=[];
	foreach($partial as $item){
		$expl = explode(" ",$item);
		$lsl=[];
		//Bus 001 Device 005: ID 1004:61f9 LG Electronics, Inc.
		$lsl['busnum']=ltrim($expl[1],"0");
		$lsl['devnum']=rtrim(ltrim($expl[3],"0"),":");
		
		$pidVid = explode(":",$expl[5]);
		$lsl['vid']=$pidVid[0];
		$lsl['pid']=$pidVid[1];
		$tmp=getdatafromdevice($lsl);
		if($tmp['serial']=="INVALID")continue;
		$lsl['serial']=$tmp['serial'];
		$lsl['product']=$tmp['product'];
		$lsl['manufacturer']=$tmp['manufacturer'];
		if($lsl['serial']!=null && $lsl['serial']!=""){
			$result[] = $lsl;
		}
		if(!isset($devicesCount[$lsl['serial']])){
			$devicesCount[$lsl['serial']]=1;
		}else{
			$devicesCount[$lsl['serial']]++;
		}
	}
	$realResult=[];
	foreach($result as $res){
		if($devicesCount[$res['serial']]==1){
			$realResult[]=$res;
		}
	}
	return $realResult;
}

function getserialfromdevice($udevAdm){
	$result = getdatafromdevice($udevAdm);
	return $result['serial'];
}

function getdatafromdevice($udevAdm){
	$partial = runShellCommand($GLOBALS['settings']['lsusb'].
		' -s '.$udevAdm["busnum"].':'.$udevAdm["devnum"].' -v 2>&1'
		,$GLOBALS['settings']['admin']);
	
	$result = $udevAdm;
	if(sizeof($partial)>0 && $partial[0]=='Couldn\'t open device'){
		$result['serial']="INVALID";
		$result['product']="INVALID";
		$result['manufacturer']="INVALID";
		$result['type']="INVALID";
		return $result;
	}
	foreach($partial as $line){
		$partialLine = preg_split("/([\s]+)/",$line,-1,PREG_SPLIT_NO_EMPTY);
		$id = trim($partialLine[0]);
		$value = trim(implode(" ",array_slice($partialLine,2)));
		if($id=="iSerial" && !isset($result['serial'])){
			$result['serial']=$value;
		}else if($id=="iProduct" && !isset($result['product'])){
			$result['product']=$value;
		}else if($id=="iManufacturer" && !isset($result['manufacturer'])){
			$result['manufacturer']=$value;
		}
		$result['type']="genericusb";
	}
	return $result;
}

function enrichDevices($allDevices,$allowedFiles){
	foreach($allowedFiles as $file){
		$foundedDevice = false;
		foreach($allDevices as $device){
			if($file['action']['serial']==$device['serial']){
				$foundedDevice = true;
				break;
			}
		}
		if(!$foundedDevice){
			$device=[
				'serial'=>$file['action']['serial'],
				'product'=>$file['action']['product'],
				'manufacturer'=>$file['action']['manufacturer'],
				'type'=>'disconnected',
			];
			if(isset($file['action']['paths']) && sizeof($file['action']['paths'])>0){
				$device['paths']=$file['action']['paths'];
			}
			$allDevices[]=$device;
		}
	}
	return $allDevices;
}