<?php
///etc/config/uLinux.conf
//[System]
//Web Access Port = 8080
require_once(dirname(dirname(__FILE__))."/scripts/commons.php");
require_once($GLOBALS['base']."/scripts/auth.php");

$uidAndGroup = findUserId();
$isAdmin = isAdmin($uidAndGroup);
if(!$isAdmin){
	die();
}

function enumerateGlobalSettings(){
	$result = [];
	foreach($GLOBALS['settings'] as $key=>$value){
		if($key=="mainerrorlog")continue;
		if($key=="datadir")continue;
		if($key=="usbdatadir")continue;
		if($key=="uiport")continue;
		$result[$key]=$value;
	}
	return $result;
}

?>
<html>
	<head>
		<script src="jquery.js"></script>
		<script>
		function loadValues(){
			return {
			<?php
			$first = true;
			foreach(enumerateGlobalSettings() as $key=>$value){
				if($first==false) echo ",";
				echo $key.":document.getElementById('".$key."').value";
				$first=false;
			}
			?>
			};
		}
		function doCheck(){
			$.ajax({
			    		url: "parameters.php",
	    				type: "POST",
			    		data:JSON.stringify({ 'action':'check','values':loadValues()}), 
			    		success: function(result){
			    			data = JSON.parse(result);
			    			<?php
							$first = true;
							foreach(enumerateGlobalSettings() as $key=>$value){
								?>
								$('#<?php echo $key;?>').css('background', '#33cc33');
								<?php
							}
							?>
			    			for(var i=0;i<data.length;i++){
			    				$('#'+data[i]).css('background', '#ff3300');
			    			}
			    		}
			    	});
		}
		$(document).ready(function(){
	<?php if($isAdmin){ ?>
			doCheck();
			$("#check").click(function(){
			    	doCheck();
			    });
			$("#update").click(function(){
			    $.ajax({
			    		url: "parameters.php",
	    				type: "POST",
			            data:JSON.stringify({ 'action':'update','values':loadValues()}), 
			    		success: function(result){
			    			data = JSON.parse(result);
			    			allOk = data.result=="ok";
			    			<?php
							$first = true;
							foreach(enumerateGlobalSettings() as $key=>$value){
								?>
								$('#<?php echo $key;?>').css('background', allOk?'#33cc33':'#ff3300');
								<?php
							}
							?>
			    			if(!allOk){
			    				alert("ERROR PLEASE CHECK AGAIN!");
			    			}
			    		}
			    	});
			    });
			
			$("#findall").click(function(){
			    $.ajax({
			    		url: "parameters.php",
	    				type: "POST",
			            data:JSON.stringify({ 'action':'findall'}), 
			    		success: function(result){
			    			data = JSON.parse(result);
			    			hasMultiple=false;
			    			for(var i=0;i<data.length;i++){
			    				sub = data[i];
			    				if(sub.size==0){
			    					$('#'+sub.command).css('background', '#ff3300');
			    					$('#'+sub.command).val("")
			    				}else if(sub.size==1){
			    					$('#'+sub.command).css('background', '#33cc33');
			    					$('#'+sub.command).val(sub.paths);
			    				}else{
			    					hasMultiple= true;
			    					$('#'+sub.command).css('background', '#FFFF00');
			    					$('#'+sub.command).val(sub.paths);
			    				}
			    			}
			    			if(hasMultiple){
			    				alert("Has some multiple choices!");
			    			}
			    		}
			    	});
			    });
	<?php } ?>
		});
	</script>
	</head>
	<body>
		<?php require_once("menu.php");?>
		<hr>
		<?php
		echo "<table><tr><td>Key</td><td>Value</td></tr>";
			
			foreach(enumerateGlobalSettings() as $key=>$value){
				echo "<tr><td>";
				echo $key; ?></td>
				<td><input type=text size="50" id=<?php echo $key;?>  name=<?php echo $key;?> value="<?php echo $value;?>"/></td>
				<?php
			}
			?>
		</table>
			<input type="button" id="check" name="check" value="Verify Values"/>
			<input type="button" id="update" name="update" value="Update Values"/>
			<input type="button" id="findall" name="findall" value="Find Executable Paths"/><br>
			
			<?php
			
			echo "<ul>";
			echo "<li>Verify runscript.php<br>";
			require_once($GLOBALS['base']."/scripts/runscript.php");
			echo "</li><li>Verify disks.php<br>";
			require_once($GLOBALS['base']."/scripts/disks.php");
			echo "</li><li>Verify apis.php<br>";
			require_once($GLOBALS['base']."/scripts/apis.php");
			echo "</li><li>Verify processes.php<br>";
			require_once($GLOBALS['base']."/scripts/processes.php");
			echo "</li><li>Verify usb.php<br>";
			require_once($GLOBALS['base']."/scripts/usb.php");
			echo "</li></ul>";
			
			klog("SERVER PARAMETERS ".json_encode($_SERVER),LTRACE);
		?>
	</body>
</html>
