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
$allowedFiles = getDataFiles($dataDir,$uidAndGroup,$isAdmin);

?>
<html>
	<head>
		
	<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="basic.css">
		<script src="jquery.js"></script>
		<script>
			
		function onChangeFile(elm){
			document.getElementById("filename").value = $(elm).val();
		}
		$(document).ready(function(){
			$('#fakeButton').click(function() {
			  $('#uploadScript').click();
			});
			window.isPolling = false;
			window.setInterval(function() {
				if(window.isPolling){
					return;
				}
				window.isPolling = true;
	      		//poll the id
			   	$.ajax({
			   		url: "showscriptsstatus.php",
					type: "GET",
    				success: function(result){
    					arrayOfRunning = JSON.parse(result);
    					
    					for (i = 0; i < arrayOfRunning.length; i++) {
    						running = arrayOfRunning[i];
    						id = running.id;
    						id = id.replace(/-/g, "");
    						killid = "#kill"+id;
    						runningid = "#state"+id;
    						deleteid = "#delete"+id;
    						startid = "#start"+id;
    						isDisabled = $(killid).prop('disabled');
    						if(running.running==true){
    							if($(killid).prop('disabled')) $(killid).prop('disabled',false);
    							if(!$(startid).prop('disabled')) $(startid).prop('disabled',true);
    							if(!$(deleteid).prop('disabled')) $(deleteid).prop('disabled',true);
    							
    							if($(runningid).html()=="&nbsp;") $(runningid).html("<img src='ajax-loader.gif'></img>");
    						}else {
    							if(!$(killid).prop('disabled')) $(killid).prop('disabled',true);
    							if($(startid).prop('disabled')) $(startid).prop('disabled',false);
    							if($(deleteid).prop('disabled')) $(deleteid).prop('disabled',false);
			    				if($(runningid).html()!="&nbsp;") $(runningid).html("&nbsp;");
    						}
						}
						window.isPolling = false;
    				},
    				error:function(){
    					window.isPolling = false;
    				}
				});
			}, 5000);
			
			
			$('#add').click(function(){
			    window.location="usbmaintenance.php?action=add";
			    });
			
			$('#backup').click(function(){
			    window.location="download.php?id=backup";
			    });
			
			
			$('#logs').click(function(){
			    window.location="download.php?id=logs";
			});	
			
			
			$('#cleanlogs').click(function(){
			    $.ajax({
			    		url: "download.php?id=cleanlogs",
	    				type: "GET",
						success: function(data){
							alert("Main logs cleaned!");
						}
			    	});
			});		
			    
			$("#formUpload").on('submit',(function(e) {
			 	e.preventDefault();
			  	$.ajax({
					url: "download.php",
					type: "POST",
					data:  new FormData(this),
					contentType: false,
					cache: false,
					processData:false,
					success: function(data){
						if(data=='invalid') {
							// invalid file format.
							alert("Invalid File !");
						}else{
							alert("File uploaded!");
						}
					},
					error: function(e){
						alert(e);
					}          
					});
			 }));
			
			<?php
				foreach($allowedFiles as $file){
					$fairId = str_replace("-","",$file["action"]["id"]);
					?>
			$('#kill<?php echo $fairId;?>').prop('disabled', true);
			$('#kill<?php echo $fairId;?>').click(function(){
			    $.ajax({
			    		url: "kill.php",
	    				type: "POST",
			    		data:{
				    		id:"<?php echo $file["action"]["id"];?>",
				    		name:"<?php echo str_replace("\"","\\\"",$file["action"]["id"]);?>"
			    		}, success: function(result){
			    			alert("Killed!");
			    			location.reload();
			    		}
			    	});
			    });
			
			$('#start<?php echo $fairId;?>').prop('disabled', true);
			$('#start<?php echo $fairId;?>').click(function(){
			    $.ajax({
		    		url: "startscript.php",
    				type: "POST",
		    		data:{
			    		id:"<?php echo $file["action"]["id"];?>",
			    		name:"<?php echo str_replace("\"","\\\"",$file["action"]["id"]);?>",
			    		script:"<?php echo str_replace("\n","\\n",str_replace("\"","\\\"",$file["action"]["script"]));?>",
			    		serial:"<?php echo $file["action"]["serial"];?>"
			    	}});
			    });
			
			$('#delete<?php echo $fairId;?>').prop('disabled', true);
			$('#delete<?php echo $fairId;?>').click(function(){
			    $.ajax({
			    		url: "delete.php",
	    				type: "POST",
			    		data:{
				    		id:"<?php echo $file["action"]["id"];?>"
			    		}, success: function(result){
			    			alert("Deleted!");
			    			window.location="usbscripts.php";
			    		}
			    	});
			    });	
			$('#edit<?php echo $fairId;?>').click(function(){
			    window.location="usbmaintenance.php?action=edit&id=<?php echo $file["action"]["id"];?>";
			    });
			
			$('#logs<?php echo $fairId;?>').click(function(){
			    window.location="usblogs.php?id=<?php echo $file["action"]["id"];?>";
			    });		
				<?php
			}
			?>
		});
		</script>
	</head>
	<body>
		<?php require_once("menu.php");?>
		<hr>
		<table>
			<tr>
				<th>
					Id
				</th>
				<th>
					Owner
				</th>
				<th>
					Process
				</th>
				<th>
					State
				</th>
				<th>
					Actions
				</th>
			</tr>
		<?php 
		$i=0;
		foreach($allowedFiles as $file){
			$fairId = str_replace("-","",$file["action"]["id"]);
			$safeName = str_replace("'","\\'",$file["action"]["name"]);
			if($i%2==0){
				echo "<tr class='bcolor'>";
			}else{
				echo "<tr>";
			}
			$i++;
			echo "<td>".$file["action"]["id"]."</td>";
			echo "<td>".$file["action"]["uid"]."</td>";
			echo "<td>".$file["action"]["name"]."</td>";
			echo "<td><span id='state".$fairId."' name='state".$fairId."'>&nbsp;</span></td>";
			echo "<td>";
			echo "<button class='tooltip button fa fa-2x fa-stop'  id='kill".$fairId."'   name='kill".$fairId."'  ><span class='tooltiptext'>Kill</span></button>";
			echo "<button class='tooltip button fa fa-2x fa-play'  id='start".$fairId."'  name='start".$fairId."' ><span class='tooltiptext'>Start</span></button>";
			echo "<button class='tooltip button fa fa-2x fa-trash-o' id='delete".$fairId."' name='delete".$fairId."'><span class='tooltiptext'>Delete</span></button>";
			echo "<button class='tooltip button fa fa-2x fa-pencil' id='edit".$fairId."' name='edit".$fairId."'><span class='tooltiptext'>Edit</span></button>";
			echo "<button class='tooltip button fa fa-2x fa-file-text-o' id='logs".$fairId."' name='logs".$fairId."'><span class='tooltiptext'>Logs</span></button>";
			//echo "<a href='usbmaintenance.php?action=edit&id=".$file["action"]["id"]."'>Edit</a></td>";
			//echo "<td><a href='usblogs.php?id=".$file["action"]["id"]."'>Logs</a></td>";
			//echo "<td><a href='usblogs.php?id=".$file["action"]["id"]."'>Logs</a></td>";
			echo "</tr>";
		}?>
		</table>
		<hr>
		<button class='button'  id='add'   name='add'  >Create New<span class='fa fap fa-lg fa-plus'></span></button>
		<hr>
		<form id="formUpload" action="download.php" method="post" enctype="multipart/form-data">
			<div class='tooltip button fa fa-2x fa-search'  id='fakeButton'   name='fakeButton'  ><span class='tooltiptext'>Browse...</span></div>
			<input id="uploadScript" name="uploadScript" type="file" accept="application/json" onchange="onChangeFile(this)" />
			<input type="text" id="filename" size="50" name="filename" readonly />
			<button type="submit" class='button '  id='upload'   name='upload'><span class="fa fa-lg fap fa-upload"></span></button>
		</form>
		<hr>
		<button class='button '  id='backup'   name='backup'>Download Scripts<span class="fa fa-lg fap fa-download"></span></button>
		<button class='button '  id='logs'   name='logs'>Download Logs<span class="fa fa-lg fap fa-download"></span></button>
		<button class='button '  id='cleanlogs'   name='cleanlogs'>Clean Logs<span class="fa fa-lg fap fa-eraser"></span></button>
		<hr>
	</body>
</html>