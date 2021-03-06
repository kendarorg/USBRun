<?php
set_time_limit(86400);
$GLOBALS['base']= dirname(dirname(__FILE__));
if(file_exists($GLOBALS['base']."/settings.php")){
	require_once($GLOBALS['base']."/settings.php");
}else{
	require_once($GLOBALS['base']."/settings.ori.php");
}

$GLOBALS['settings']['admingroup']="administrators";

$GLOBALS['settings']['mainerrorlog']=$GLOBALS['base']."/data/error.log";
$GLOBALS['settings']['datadir']=$GLOBALS['base']."/data";
$GLOBALS['settings']['usbdatadir']=$GLOBALS['base']."/data/usb";


setErrorLog();

const LERROR=1;
const LWARNING=2;
const LINFO=3;
const LTRACE=4;
//1 ERROR
//2 WARNING
//3 INFO
//4 ALL

function kanlog($level){
	$logLevel = $GLOBALS['settings']['loglevel']+0;
	return $level<=$logLevel;
}
function klog($value,$level=3){
	$logLevel = $GLOBALS['settings']['loglevel']+0;
	$id = "[ERROR]";
	if($level<=$logLevel){
		if($level==1)$id = "[ERROR]";
		if($level==2)$id = "[WARNING]";
		if($level==3)$id = "[INFO]";
		if($level==4)$id = "[TRACE]";
		
		if(!is_string($value)){
			$value = json_encode($value);
		}
		//error_log(var_export(debug_backtrace()[1],true));
		error_log($id." ".$value);
	}
}

function isPidRunning($pidPath){
	$pid = readFileIfExists($pidPath);
	if ($pid!=null && strlen($pid)>0){
		if (file_exists( "/proc/".$pid )){
			return true;
		}
	}
	$screenId = pathinfo($pidPath)['filename'];
	return runShellCommand(
		$GLOBALS['settings']['screen']." -list|grep  ".$screenId."|cut -f2",
		$GLOBALS['settings']['admin'],true)!="";
}
function amithis($user){
	$whoami = trim(shell_exec($GLOBALS['settings']['whoami']));
	return $whoami == $user;
}

function readFileIfExists(){
	$path = "/".ltrim(implode("/",func_get_args()),"/");
	
	if(!is_file($path)) return null;
	$cmd = $GLOBALS['settings']['cat']." ".$path;
	if(!amithis($GLOBALS['settings']['admin'])){
		$cmd = $GLOBALS['settings']['sudo']." -u ".$GLOBALS['settings']['admin']." ".$cmd;
	}
	
	return trim(shell_exec($cmd));
}

function getFileLines(){
	$args = func_get_args();
	$path = "/".ltrim(implode("/",$args),"/");
	if(!is_file($path)) return 0;
	$cmd = $GLOBALS['settings']['wc']." -l ".$path;
	if(!amithis($GLOBALS['settings']['admin'])){
		$cmd = $GLOBALS['settings']['sudo']." -u ".$GLOBALS['settings']['admin']." ".$cmd;
	}
	$result = trim(shell_exec($cmd));	
	return preg_split("/[\s]+/",$result,-1,PREG_SPLIT_NO_EMPTY)[0]+0;
}

function readPartialFileIfExists(){
	$args = func_get_args();
	$pathArray = array_slice($args,0,sizeof($args)-2);
	$from = $args[sizeof($args)-2]+0;
	$to = $args[sizeof($args)-1]+0;
	$path = "/".ltrim(implode("/",$pathArray),"/");
	
	$fileLines = getFileLines($path);
	
	if($to< 0){
		$from = max(0,$fileLines+$to);
		$to = $fileLines;
	}
	if($from >= $fileLines) return "";
	if($to >= $fileLines) {
		$to = $fileLines-1;
	}
	if(!is_file($path)) return null;
	$cmd = $GLOBALS['settings']['sed']." -n ".($from+1).",".($to+1)."p ".$path;
	if(!amithis($GLOBALS['settings']['admin'])){
		$cmd = $GLOBALS['settings']['sudo']." -u ".$GLOBALS['settings']['admin']." ".$cmd;
	}
	
	return trim(shell_exec($cmd));
}


function tailExists(){
	$args = func_get_args();
	$pathArray = array_slice($args,0,sizeof($args)-2);
	$from = $args[sizeof($args)-2]+0;
	$to = $args[sizeof($args)-1]+0;
	$path = "/".ltrim(implode("/",$pathArray),"/");
	
	$fileLines = getFileLines($path);
	if($from >= $fileLines) return "";
	if($to >= $fileLines) {
		$to = $fileLines-1;
	}
	if(!is_file($path)) return null;
	$cmd = $GLOBALS['settings']['sed']." -n ".($from+1).",".($to+1)."p ".$path;
	if(!amithis($GLOBALS['settings']['admin'])){
		$cmd = $GLOBALS['settings']['sudo']." -u ".$GLOBALS['settings']['admin']." ".$cmd;
	}
	
	return trim(shell_exec($cmd));
}


function getTempPath(){
	$path = trim(implode("_",func_get_args()),"/");
	return rtrim(sys_get_temp_dir()."/".$path,"/");
}

function guidv4($data = null) {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function cleanLogs(){
	runShellCommand($GLOBALS['settings']['find']." ".getTempPath()." -mtime +5 -exec rm {} \; ",
		$GLOBALS['settings']['admin']);
}

function setErrorLog($path = null){
	if($path==null){
		$path = $GLOBALS['settings']['mainerrorlog'];
	}
	error_reporting(E_ALL);
	ini_set('error_reporting', E_ALL);
	ini_set('log_errors', 'On');
	ini_set('error_log', $path); 
}

function systemLog($data,$level=4){
	runShellCommand(
		$GLOBALS['settings']['writelog']." ".
		"\"[USBRun] ".str_replace('"',"'",$data)."\" ".$level,
		$GLOBALS['settings']['admin']);
}

function runShellCommandExec($cmd,$env=null){
	if($env!=null){
		$msg = "commons::runShellCommandExec ".$cmd." ENV NOT SUPPORTED";
		klog($msg,LERROR);
		throw new Exception();
	}
	return shell_exec($cmd);
	
	
}


function runShellCommandOpen($command,$env=null){
		$process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null,
            $env
        );

        if (!is_resource($process)) {
        	klog("Could not create a valid process: ".$cmd,LERROR);
        	return "";
        }

        // This will prevent to program from continuing until the processes is complete
        // Note: exitcode is created on the final loop here
        $status = proc_get_status($process);
        while($status['running']) {
            $status = proc_get_status($process);
        }

        $stdOutput = trim(stream_get_contents($pipes[1]));
        $stdError  = trim(stream_get_contents($pipes[2]));

        proc_close($process);
        
        if($status['exitcode']!=0){
        	
        	if(strlen($stdError)>0){
        		$e = new \Exception();
        		klog("Running ".$command." ExitCode ".$status['exitcode'],LERROR);
				klog("StackTrace:\n".$e->getTraceAsString(),LERROR);
	        	klog($stdError,LERROR);
	        }else{
        		klog("Running ".$command." ExitCode ".$status['exitcode']);
	        }
        }

        return $stdOutput;
}

function runShellCommand($command, $uid=null,$implode=false,$env = null){
	if($uid==null){
		$uid = $GLOBALS['uid'];
	}
	$final=[];
	
	
	
	$cmd = $command;
	if(!amithis($uid)){
		$cmd = $GLOBALS['settings']['sudo']." -u ".$uid." ".$cmd;
	}
	klog("commons::runShellCommand ".$cmd,LTRACE);
	
	$result = runShellCommandOpen($cmd,$env);
	
	if(kanlog(LTRACE))klog("result ".$result,LTRACE);
	
	$partial = preg_split("/[\n\r\f]+/",$result,-1,PREG_SPLIT_NO_EMPTY);
	for($i=0;$i< sizeof($partial);$i++){
		$partial[$i]=trim($partial[$i]);
		if(strlen($partial[$i])>0){
			$final[]=$partial[$i];
		}
	}
	if($implode){
		return implode("\n",$final);
	}
	return $final;
}

function handleCurlError(&$curl)
{
    $error = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    klog("Curl error [".$httpCode."] ".$error,LERROR);
    http_response_code($httpCode);
}

function parseXmlString($source){
	if(kanlog(LTRACE))klog($source,LTRACE);
	if($source == null){
		return $source;
	}
	$saved  = libxml_use_internal_errors(true);
	$xml=simplexml_load_string(trim($source));
	$errors = libxml_get_errors();
	libxml_use_internal_errors($saved);
	
	if (!$xml) {
		klog("Parsing xml: ".var_export($errors,true),LERROR);
		klog("Xml Content: ".$source,LERROR);
		return null;
	}
	return $xml;
}

function findChildProcesses($ppid){
	$result=[];
	$data = runShellCommand(
		"ps -o pid,ppid,comm,args|grep -E '[0-9]+\\s+".$ppid."\\s+.*' || true",
		$GLOBALS['settings']['admin'],false);
	
	foreach($data as $pidLine){
		$pidLineExploded = preg_split("/[\s]+/",$pidLine,-1,PREG_SPLIT_NO_EMPTY);
		$result[]=$pidLineExploded[0];
		foreach(findChildProcesses($pidLineExploded[0]) as $chpid){
			$result[]=$chpid;
		}
		
	}
	return $result;

}