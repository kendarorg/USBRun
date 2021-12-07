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
$allowedFiles = getDataFiles($dataDir,$uidAndGroup,$isAdmin);

?>
<html>
	<head>
		<script src="jquery.js"></script>
		<script>
		$(document).ready(function(){
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
    							
    							if($(runningid).html()=="NA") $(runningid).html("<img src='ajax-loader.gif'></img>");
    						}else {
    							if(!$(killid).prop('disabled')) $(killid).prop('disabled',true);
    							if($(startid).prop('disabled')) $(startid).prop('disabled',false);
    							if($(deleteid).prop('disabled')) $(deleteid).prop('disabled',false);
			    				if($(runningid).html()!="NA") $(runningid).html("NA");
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
		<table  border="1px">
			<tr>
				<td>
					Id
				</td>
				<td>
					Owner
				</td>
				<td>
					Process
				</td>
				<td>
					State
				</td>
				<td>
					Actions
				</td>
			</tr>
		<?php foreach($allowedFiles as $file){
			$fairId = str_replace("-","",$file["action"]["id"]);
			$safeName = str_replace("'","\\'",$file["action"]["name"]);
			echo "<tr>";
			echo "<td>".$file["action"]["id"]."</td>";
			echo "<td>".$file["action"]["uid"]."</td>";
			echo "<td>".$file["action"]["name"]."</td>";
			echo "<td><span id='state".$fairId."' name='state".$fairId."'>NA</span></td>";
			echo "<td><input type='button' id='kill".$fairId."' name='kill".$fairId."' value='Kill'/>";
			echo "<input type='button' id='start".$fairId."' name='start".$fairId."' value='Start'/>";
			echo "<input type='button' id='delete".$fairId."' name='delete".$fairId."' value='Delete'/>";
			echo "<input type='button' id='edit".$fairId."' name='edit".$fairId."' value='Edit'/>";
			echo "<input type='button' id='logs".$fairId."' name='logs".$fairId."' value='Logs'/></td>";
			//echo "<a href='usbmaintenance.php?action=edit&id=".$file["action"]["id"]."'>Edit</a></td>";
			//echo "<td><a href='usblogs.php?id=".$file["action"]["id"]."'>Logs</a></td>";
			//echo "<td><a href='usblogs.php?id=".$file["action"]["id"]."'>Logs</a></td>";
			echo "</tr>";
		}?>
		</table>
		<hr>
		<input type='button' id='add' name='add' value='Add'/>&nbsp;
		<hr>
		<b>Upload script</b>
		<form id="formUpload" action="download.php" method="post" enctype="multipart/form-data">
			<input id="uploadScript" name="uploadScript" type="file" accept="application/json"  />
			<input type="submit" value="Upload">
		</form>
		<hr>
		<input type='button' id='backup' name='backup' value='Download Scripts'/>&nbsp;
		<input type='button' id='logs' name='logs' value='Download Main Logs'/>&nbsp;
		<input type='button' id='cleanlogs' name='cleanlogs' value='Clean Main Logs'/>
		
		
	</body>
</html>