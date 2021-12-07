<?php

require_once(dirname(dirname(__FILE__))."/scripts/commons.php");
require_once($GLOBALS['base']."/scripts/auth.php");

$uidAndGroup = findUserId();
$isAdmin = isAdmin($uidAndGroup);

if(!$isAdmin){
	die();
}
$json = file_get_contents('php://input');
$data = json_decode($json,true);
$result=[];


function checkUiPort(){
	$url =parse_url($_SERVER['HTTP_REFERER']);
	
	$result = file_get_contents("/etc/config/uLinux.conf");
	$settings = preg_split("/[\n\r\f]+/",$result,-1,PREG_SPLIT_NO_EMPTY);
	$inMain = false;
	foreach($settings as $line){
		$trim = trim($line);
		if($trim=="[System]"){$inMain=true;}
		else if(substr($trim,0,1)=="["){break;}
		$pos = strpos($trim,"Web Access Port");
		if($pos!==false && $pos==0){
			$data =explode("=",$trim);
			return $url['scheme']."://".$url['host'].":".trim($data[1]);
		}
	}
	
	return null;
}

function doCheck($data){
	$result=[];
	foreach($data['values'] as $key=>$value){
		if(substr($value,0,1)=="/"){
			if($key=="admin") $result[]=$key;
			if($key=="admingroup") $result[]=$key;
			if($key=="loglevel")$result[]=$key;
			if(!is_file($value) && !is_dir($value)){
				//Optional
				if(substr($key,0,1)!="_"){
					$result[]=$key;
				}
			}//shell_exec($value." 2&>1");
		}
	}
	if(!in_array('loglevel',$result)){
		$loglevel =$data['values']['loglevel']+0; 
		if($loglevel<1 || $loglevel>4){
			$result[]='loglevel';
		}
	}
	if(sizeof($result)>0){
		return $result;
	}
		
	//TODOK
	//$cmd = $data['values']['sudo']." -u ".$data['values']['admin']." groups ".$data['values']['admin'];
	$cmd = "groups ".$data['values']['admin'];
	$groupsResult = trim(shell_exec($cmd ));
	$groups = preg_split("/[\s]+/",$groupsResult,-1,PREG_SPLIT_NO_EMPTY);
	$goodGroups = false;
	foreach($groups as $group){
		if($group==$data['values']['admingroup']){
			$goodGroups = true;
		}
	}
	if(!$goodGroups){
		if(!in_array('admingroup',$result))$result[]=$data['values']['admingroup'];
		if(!in_array('admin',$result))$result[]=$data['values']['admin'];
	}
	return $result;
}


function doUpdate($data){

	$result = doCheck($data);
	if(sizeof($result)>0){
		return [
			'result'=>'ko'
		];
	}
	$r="<?php\n";
	$r.="\$GLOBALS['settings']=[\n";

	foreach($data['values'] as $key=>$value){
		if($key=="mainerrorlog")continue;
		if($key=="datadir")continue;
		if($key=="usbdatadir")continue;
		if($key=="uiport")continue;
		$r.="\t'".$key."'=>'".$value."',\n";
	}
	$uiPort = checkUiPort();
	$r.="\t'uiport'=>'".$uiPort."',\n";

	$r.="\n];\n";

	//echo $r;
	file_put_contents($GLOBALS['base']."/settings.php",$r);
	return [
		'result'=>'ok',
		'data'=>$r
	];
}
		
function doFindAll(){
	$candidates = [
		'/sbin',
		'/usr/local/sbin',
		'/usr/sbin',
		'/usr/local/sudo/bin',
		'/usr/bin',
		'/usr/local/bin',
		'/bin'
	];
	$optionals = [
	];
	$toFind = [
		'udevadm'=>'',
		'blkid'=>'',
		'lsusb'=>'',
		'sudo'=>'',
		'screen'=>'',
		'python'=>'',
		'find'=>'',
		'cat'=>'',
		'chmod'=>'',
		'writelog'=>'',
		'whoami'=>''
	];
	$results=[];
	foreach($toFind as $command=>$empty){
		$path = $GLOBALS['settings'][$command];
		$pathOnly = dirname($path);
		$results[$command]=[];
		if(is_file($path)||is_dir($path)){
			$results[$command][$path]=true;
		}
		
		foreach($candidates as $candidate){
			$possibleDir = $candidate."/".$command;
			if(is_file($possibleDir)||is_dir($possibleDir)){
				$results[$command][$possibleDir]=true;
			}
		}
	}
	
	foreach($optionals as $command=>$default){
		$path = $GLOBALS['settings'][$command];
		$pathOnly = dirname($path);
		$results[$command]=[];
		if(is_file($path)||is_dir($path)){
			$results[$command][$path]=true;
		}
		
		foreach($candidates as $candidate){
			$possibleDir = $candidate."/".$command;
			if(is_file($possibleDir)||is_dir($possibleDir)){
				$results[$command][$possibleDir]=true;
			}
		}
		if(sizeof($results[$command])==0){
			$results[$command][$default]=true;
		}
	}
	$real = [];
	foreach($results as $key=>$value){
		$pp = [
			'command'=>$key,
			'paths'=>implode(" OR ",array_keys($value)),
			'size'=>sizeof($value)
		];
		$real[]=$pp;
	}
	return $real;
}
		
if(isset($data['action'])){
	if($data['action']=='check'){
		$result = doCheck($data);
	}else if($data['action']=='update'){
		$result = doUpdate($data);
	}else if($data['action']=='findall'){
		$result = doFindAll();
	}else{
		$result = ['result'=>'ko'];
	}
	echo json_encode($result);
}

