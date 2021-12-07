<?php

require_once(dirname(dirname(__FILE__))."/scripts/commons.php");
require_once($GLOBALS['base']."/scripts/auth.php");

$uidAndGroup = findUserId();
$isAdmin = isAdmin($uidAndGroup);
if(!$isAdmin){
	die();
}


?>
<html>
	<head>
	<script src="jquery.js"></script>
		<script>
			$(document).ready(function(){
				window.isPolling = false;
				$("#running").html("");
				
				window.readscripttimes =0;
	    		window.setTimeout(function(){
	    			$("#running").html("<img src='ajax-loader.gif'></img>");
			      	document.interval = window.setInterval(function() {
			      		if(window.isPolling){
							return;
						}
						window.isPolling = true;
			      		//poll the id
					   	$.ajax({
					   		url: "readscriptlog.php",
							type: "POST",
					   		data:{
					    		id:document.getElementById("id").value,
					    		name:'',
					    		script:'',
					    		serial:'',
					    		pollid:window.readscripttimes,
					    		start:document.getElementById("start").value
		    				}, 
		    				success: function(result){
		    					resultData = JSON.parse(result);
		    					if(resultData['status']=="FINISHED"){
		    						window.clearInterval(document.interval);
		    						document.interval=null;
		    						$("#running").html("");
		    						return;
		    					}
		    					
								
		    					window.readscripttimes++;
		    					//console.log("Reading log "+JSON.stringify(result));
		    					document.getElementById("testresult").innerHTML+=resultData['content'];
		    					document.getElementById("start").value=resultData['end'];
		    					window.isPolling = false;
		    				}, 
		    				error: function(jqXHR, textStatus, errorThrown) {
								console.log(JSON.stringify(jqXHR));
								console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
						    	window.clearInterval(document.interval);
						    	document.interval=null;
		    					$("#running").html("");
		    					window.isPolling = false;
						  	}
						});
					}, 2000);
				},1000);
				$("#logs").click(function(){
				    window.location="usblogs.php?id=<?php echo $_GET['id'];?>";
				    });
				$("#edit").click(function(){
				    window.location="usbmaintenance.php?action=edit&id=<?php echo $_GET['id'];?>";
				    });
			});
		</script>
	</head>
	<body>
		
		<input type="hidden" id="start" name="start" value="0"/>
		<input type="hidden" id="id" name="id" value="<?php echo $_GET['id'];?>"/>
		<?php require_once("menu.php");?>
		<hr>
		<span id="running" name="running"></span>
		<input type="button" id="logs" name="logs" value="Reload"/>
		<input type="button" id="edit" name="edit" value="Edit"/>
		<hr>	
		<div id="testresult" name="testresult"></div>
	</body>
</html>