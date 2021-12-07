<?php

require_once(dirname(__FILE__)."/commons.php");

function makeQnapApiRequest($url,$data = null,$userId = null){
	 
	 
	klog("Retrieve SID: ",LTRACE);
	$sid = getSid();
	$ch = curl_init();
	if(strpos($url,"?")===false){
		$url=$url."?sid=".$sid;
	}else{
		$url=$url."&sid=".$sid;
	}
	$url = $GLOBALS['settings']['uiport']."/".ltrim($url,"/");
	
	klog("Curl ".$url." With data: ".$data,LTRACE);
	$headers = [];
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION,
	  function($curl, $header) use (&$headers)
	  {
	    $len = strlen($header);
	    $header = explode(':', $header, 2);
	    if (count($header) < 2) // ignore invalid headers
	      return $len;

	    $headers[strtolower(trim($header[0]))][] = trim($header[1]);

	    return $len;
	  }
	);
	$headers = [];
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded'));
		//NAS_USER='..'; 
	
	if($data!=null){
		curl_setopt($ch, CURLOPT_POSTFIELDS,
	            $data);
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec ($ch);
	if($server_output===false){
		handleCurlError($ch);
		curl_close ($ch);
		return null;
	}

	curl_close ($ch);
	return $server_output;
}

function makeQnapAuthenticatedApiRequest($url,$data = null,$userId = null){
	 
	 
	klog("Retrieve SID: ",LTRACE);
	$sid = getSid();
	$ch = curl_init();
	if(strpos($url,"?")===false){
		$url=$url."?sid=".$sid;
	}else{
		$url=$url."&sid=".$sid;
	}
	$url = $GLOBALS['settings']['uiport']."/".ltrim($url,"/");
	
	klog("Curl ".$url." With data: ".$data,LTRACE);
	$headers = [];
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION,
	  function($curl, $header) use (&$headers)
	  {
	    $len = strlen($header);
	    $header = explode(':', $header, 2);
	    if (count($header) < 2) // ignore invalid headers
	      return $len;

	    $headers[strtolower(trim($header[0]))][] = trim($header[1]);

	    return $len;
	  }
	);
	$headers = [];
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded'));
		//NAS_USER='..'; 
	
	if($data!=null){
		curl_setopt($ch, CURLOPT_POSTFIELDS,
	            $data);
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec ($ch);
	if($server_output===false){
		handleCurlError($ch);
		curl_close ($ch);
		return null;
	}

	curl_close ($ch);
	return $server_output;
}

function getSid(){
	$scriptsPath =dirname(__FILE__);
	return trim(shell_exec("/usr/local/bin/python ".$scriptsPath."/getsid.py"));
}

function beep(){
	klog("Request QNAP beep"); 
	$url ="/cgi-bin/sys/sysRequest.cgi?subfunc=notification&count=0&apply=where_are_you";
	$xml = makeQnapApiRequest($url);
	$xmlObject = parseXmlString($xml);
	if($xmlObject==null){
		$e = new \Exception();
		klog("StackTrace:\n".$e->getTraceAsString(),LERROR);
		klog("XML Error requested address: ".$url,LERROR);
		klog("XML Error requested beep: ".$xml,LERROR);
		return;
	}
}

function getVolume($sharedFolders){
	if($sharedFolders==null || sizeof($sharedFolders)==0){
		
		klog("Missing shared folders",LTRACE);	
		return null;
	}
	
	klog("Request QNAP Volumes match");	
	$url = "/cgi-bin/disk/disk_manage.cgi?store=external_get_all";
	$data = "func=external_get_all&todo=refresh&dc=0.6510751747659425";
	$xml = makeQnapApiRequest(
		$url,
		$data);
	$xmlObject = parseXmlString($xml);
	if($xmlObject==null || !property_exists($xmlObject,"Disk_Num")){
		$e = new \Exception();
		klog("StackTrace:\n".$e->getTraceAsString(),LERROR);
		klog("XML Error requested address: ".$url." Data: ".$data,LERROR);
		klog("XML Error requested getVolume: ".$xml,LERROR);
		return null;
	}
	
	if($xmlObject->Disk_Num=="0"){
		return null;
	}
	if($xmlObject->Disk_Num=="1"){
		foreach($sharedFolders as $sh){
			$pos = strpos($sh,"/share/".$xmlObject->Disk_Vol->Share_Folder);
			if($pos!==false && $pos==0){
				return $xmlObject->Disk_Vol->Disk_Selected;
			}
		}
		
	}else{
		foreach($xmlObject->Disk_Vol as $dv){
			foreach($sharedFolders as $sh){
				$pos = strpos($sh,"/share/".$dv->Share_Folder);
				if($pos!==false && $pos==0){
					return $dv->Disk_Selected;
				}
			}
		}		
	}
	return null;

}

function ejectVolume($volumeId){
	klog("Request QNAP Volumes eject");	
	$url = "/cgi-bin/disk/disk_manage.cgi";
	$data = "func=external_disk_remove&volumeID=".$volumeId;
	$xml = makeQnapApiRequest(
		$url,
		$data);
	$xmlObject = parseXmlString($xml);
	if($xmlObject==null || !property_exists($xmlObject,"eject_msg") || $xmlObject->eject_msg!="success"){
		$e = new \Exception();
		klog("StackTrace:\n".$e->getTraceAsString(),LERROR);
		klog("XML Error requested address: ".$url."  Data: ".$data,LERROR);
		klog("XML Error requested ejecting: ".$xml,LERROR);
	}
}