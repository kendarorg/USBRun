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

$dataDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."data/usb";

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
	}else if($what=="logs"){
		
		$zip = new ZipArchive;
		$tmp_file = getTempPath()."/logs.zip";
		
		if (file_exists($tmp_file)) {
		   unlink($tmp_file);
		}
	    if ($zip->open($tmp_file,  ZipArchive::CREATE)) {
	    	if(!file_exists($GLOBALS['settings']['mainerrorlog'])){
	    		error_log("Downloading error log");
	    	}
	    	$zip->addFile($GLOBALS['settings']['mainerrorlog'], 'error.log');
	        
	        $zip->close();
	        header('Content-disposition: attachment; filename=logs.zip');
	        header('Content-type: application/zip');
	        readfile($tmp_file);
	   }
	}else if($what=="backup"){
		
		$zip = new ZipArchive;
		$tmp_file = getTempPath()."/backup.zip";
		
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