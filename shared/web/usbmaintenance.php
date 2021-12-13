<?php
		
require_once(dirname(dirname(__FILE__))."/scripts/commons.php");
require_once($GLOBALS['base']."/scripts/auth.php");
require_once($GLOBALS['base']."/scripts/processes.php");
require_once($GLOBALS['base']."/scripts/usb.php");
require_once($GLOBALS['base']."/scripts/disks.php");

$uidAndGroup = findUserId();
$isAdmin = isAdmin($uidAndGroup);
if(!$isAdmin){
	die();
}

$foundedFile = null;

$dataDir = $GLOBALS['settings']['usbdatadir'];
$allowedFiles = getDataFiles($dataDir,$uidAndGroup,$isAdmin);
$allDevices = lsusb();
$allDevices = findDisks($allDevices);
$allDevices[] = [
	'serial'=>'Fake',
	'product'=>'Dummy',
	'manufacturer'=>'Dummy',
	'paths'=>[
		'/dummy'
	],
	'busnum'=>'-1',
	'devnum'=>'-1',
	'type'=>'dummy'
];
$allDevices = enrichDevices($allDevices,$allowedFiles);


$data = findRealDevices($allowedFiles,$allDevices,$foundedFile);
$foundedFile = $data['file'];
$foundedDevice = $data['device'];



$logs = $GLOBALS['home']."/".$foundedFile['id'].".log";
$pidPath = $GLOBALS['home']."/".$foundedFile['id'].".pid";
$isRunning = isPidRunning($pidPath);

$uuid = "";
$name = "";
$serial = "";
$script = "ls -lrt";
$eject="false";
$disabled="true";
if($foundedFile==null){
	$uuid = guidv4();
	if(sizeof($allDevices)>0){
		$serial = $allDevices[0]['serial'];
	}
}else{
	$uuid = $foundedFile['id'];
	$name = $foundedFile['name'];
	$serial = $foundedFile['serial'];
	$script = $foundedFile['script'];
	if(isset($foundedFile['eject']   ))$eject   =$foundedFile['eject']?"true":"false";
	if(isset($foundedFile['disabled']))$disabled=$foundedFile['disabled']?"true":"false";
}
?>
<html>
	<head>
		
	<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="basic.css">
	<script src="jquery.js"></script>
	<script>
	function readLogsData(){
		$('#test').prop('disabled', true);
		$('#save').prop('disabled', true);
		$('#kill').prop('disabled', false);
		window.readscripttimes =0;
		window.setTimeout(function(){
			$("#running").html("<img src='ajax-loader.gif'></img>");
	      	document.interval = window.setInterval(function() {
	      		//poll the id
			   	$.ajax({
			   		url: "readscriptlog.php",
					type: "POST",
			   		data:{
			    		id:document.getElementById("id").value,
			    		name:document.getElementById("name").value,
			    		script:document.getElementById("script").value,
			    		serial:document.getElementById("serial").value,
			    		pollid:window.readscripttimes,
			    		start:-0,
			    		count:-30
    				}, 
    				success: function(result){
    					resultData = JSON.parse(result);
    					if(resultData['running']==false){
    						window.clearInterval(document.interval);
    						document.interval=null;
    						$("#running").html("");
    						$('#kill').prop('disabled', true);
							$('#test').prop('disabled', false);
							$('#save').prop('disabled', false);
							document.getElementById("testresult").innerHTML=resultData['content'];
    						return;
    					}
    					window.readscripttimes++;
    					//console.log("Reading log "+JSON.stringify(result));
    					document.getElementById("testresult").innerHTML=resultData['content'];
    					document.getElementById("start").value=resultData['end'];
    				}, 
    				error: function(jqXHR, textStatus, errorThrown) {
						console.log(JSON.stringify(jqXHR));
						console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
				    	window.clearInterval(document.interval);
				    	document.interval=null;
    					$("#running").html("");
    					$('#kill').prop('disabled', true);
						$('#test').prop('disabled', false);
						$('#save').prop('disabled', false);
				  	}
				});
			}, 5000);
		},1000);
	}
	$(document).ready(function(){
		document.getElementById("eject").checked=<?php echo $eject;?>;
		document.getElementById("disabledx").checked=<?php echo $disabled;?>;
		readLogsData();
		
		$("#logs").click(function(){
			window.location="usblogs.php?id=<?php echo $uuid;?>";
			});
		$("#save").click(function(){
		    $.ajax({
		    		url: "save.php",
    				type: "POST",
		    		data:{
			    		id:document.getElementById("id").value,
			    		name:document.getElementById("name").value,
			    		script:document.getElementById("script").value,
			    		serial:document.getElementById("serial").value,
			    		disabled:document.getElementById("disabledx").checked,
			    		eject:document.getElementById("eject").checked
		    		}, success: function(result){
		    			alert("Saved!");
		    			window.location="usbmaintenance.php?action=edit&id="+document.getElementById("id").value;
		    		}
		    	});
		    });
		
		$('#kill').prop('disabled', true);
		$("#kill").click(function(){
		    $.ajax({
		    		url: "kill.php",
    				type: "POST",
		    		data:{
			    		id:document.getElementById("id").value,
			    		name:document.getElementById("name").value,
		    		}, success: function(result){
		    			alert("Killed!");
		    		}
		    	});
		    });
		
		<?php
		if(isset($_GET['action'])&& $_GET['action']=="add"){
			?>$('#test').prop('disabled',true);<?php
		}
		
		?>
		
	  	$("#test").click(function(){
	  		if(document.interval!=null){
	  			alert("KILL FIRST");
	  			return;
	  		}
		    $.ajax({
		    		url: "startscript.php",
    				type: "POST",
		    		data:{
			    		id:document.getElementById("id").value,
			    		name:document.getElementById("name").value,
			    		script:document.getElementById("script").value,
			    		serial:document.getElementById("serial").value
			    	}, 
			    	success: function(result){
			    		$('#test').prop('disabled', true);
						$('#kill').prop('disabled', false);
			    		console.log("Script starting "+JSON.stringify(result));
			    		//Wait 1 sec
			    		readLogsData();
		    		}, 
    				error: function(jqXHR, textStatus, errorThrown) {
						console.log(JSON.stringify(jqXHR));
						console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
					}
		    	});
		    });
		    
		    <?php
			if(isset($_GET['action'])&& $_GET['action']=="edit" && $isRunning){
				?>readLogsData();<?php
			}
			?>
		    
	  	});
	</script>
	</head>
	<body>
		
		<?php require_once("menu.php");?>
		<hr>
		<button class='tooltip button fa fa-2x fa-file-text-o' id='logs' name='logs'><span class='tooltiptext'>Logs</span></button>
		<button class='tooltip button fa fa-2x fa-floppy-o' id='save' name='save'><span class='tooltiptext'>Save</span></button>
		<button class='tooltip button fa fa-2x fa-play'  id='test'  name='test' ><span class='tooltiptext'>Test</span></button>
		<button class='tooltip button fa fa-2x fa-stop'  id='kill'   name='kill'  ><span class='tooltiptext'>Kill</span></button>
		<span id="running" name="running"></span>
		<hr>
			<input type="hidden" id="start" name="start" value="0"/>
			<input type="hidden" id="id" name="id" value="<?php echo $uuid;?>"/>
			Id: <?php echo $uuid;?><br><br>
			Name: <input type="text" id="name" name="name" value="<?php echo $name;?>"/><br><br>
			Disabled: <input type="checkbox" id="disabledx" name="disabledx" value="Yes" /><br><br>
			Eject when done: <input type="checkbox" id="eject" name="eject"  value="Yes" /><br><br>
			Device: <select id="serial" name="serial">
				<?php
	foreach($allDevices as $device){
		$selected ="";
		if($device['serial']==$serial){
			$selected =" selected ";
		}
		echo "<option ".$selected." value='".($device['serial'])."'>";
		if(isset($device['type']) && $device['type']=='disconnected'){
			echo "*";
		}
		echo ($device['manufacturer']."/".$device['product']);
		if(isset($device['paths'])){
			echo ("[".implode("|",$device['paths'])."]");
		}
		echo "</option>";
	}
	?>
			</select><br><br>
			Script: <br>
			<b>#!/bin/sh</b><br>
			<textarea id="script" name="script" rows="5" cols="100"><?php echo $script;?></textarea><br><br>
	<hr>
		<div id="testresult" name="testresult"></div>
		<hr>
	</body>
</html>