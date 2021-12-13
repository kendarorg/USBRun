<?php

require_once(dirname(dirname(__FILE__))."/scripts/commons.php");
require_once($GLOBALS['base']."/scripts/auth.php");
require_once($GLOBALS['base']."/scripts/processes.php");
require_once($GLOBALS['base']."/scripts/usb.php");


$uidAndGroup = findUserId();
$isAdmin = isAdmin($uidAndGroup);

if(!$isAdmin){
	die();
}

$dataDir = $GLOBALS['settings']['usbdatadir'];

if(strtoupper($_SERVER['REQUEST_METHOD'])=="POST"){
	$img = $_FILES["uploadScript"]["name"];
	$tmp = $_FILES["uploadScript"]["tmp_name"];
	$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
	if($ext != "json"){
		return;
	}
	
	$candidate = readFileIfExists($tmp);
	$action = json_decode($candidate,true);
	
	$path = $dataDir."/".$action['id'].".json";
	if(is_file($path)){
		unlink($path);
	}
	move_uploaded_file($tmp,$path);
}else{
	$what = $_GET['id'];
	if($what=="cleanlogs"){
		if (file_exists($GLOBALS['settings']['mainerrorlog'])) {
			unlink($GLOBALS['settings']['mainerrorlog']);
		}
	}else if($what=="scriptlog"){
		
		$logs = $GLOBALS['home']."/".$_GET['log'].".log";

		$zip = new ZipArchive;
		$tmp_file = tempnam (getTempPath(),"logs");
		
		
	    if ($zip->open($tmp_file,  ZipArchive::CREATE)) {
	    	
	    	$data = readFileIfExists($logs);
	    	$zip->addFromString('data.log',$data);
		    
	        $zip->close();
	        header('Content-disposition: attachment; filename=logs.zip');
	        header('Content-type: application/zip');
	        readfile($tmp_file);
	   }
	}else if($what=="logs"){
		
		$zip = new ZipArchive;
		$tmp_file = tempnam (getTempPath(),"logs");
		
		if (file_exists($tmp_file)) {
		   unlink($tmp_file);
		}
	    if ($zip->open($tmp_file,  ZipArchive::CREATE)) {
	    	
	    	$data = readFileIfExists($GLOBALS['settings']['mainerrorlog']);
	    	$zip->addFromString('error.log',$data);
	        
	        $zip->close();
	        header('Content-disposition: attachment; filename=logs.zip');
	        header('Content-type: application/zip');
	        readfile($tmp_file);
	   }
	}else if($what=="backup"){
		
		$zip = new ZipArchive;
		$tmp_file = tempnam (getTempPath(),"logs");
		
		if (file_exists($tmp_file)) {
		   unlink($tmp_file);
		}
	    if ($zip->open($tmp_file,  ZipArchive::CREATE)) {
	    	
	    	$fileList = glob($dataDir.DIRECTORY_SEPARATOR.'*.json');
			$allowedFiles=[];
			foreach($fileList as $filename){
			    if(is_file($filename)){
		    		$zip->addFile($filename);
		    	}
		    }
	        
	        $zip->close();
	        header('Content-disposition: attachment; filename=backup.zip');
	        header('Content-type: application/zip');
	        readfile($tmp_file);
	   }
	}
}