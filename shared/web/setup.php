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
		

			<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="basic.css">
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
		function doCheck(showDialogs){
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
								$('#<?php echo $key;?>').css('background', '#d0ffc7');
								<?php
							}
							?>
			    			for(var i=0;i<data.length;i++){
			    				$('#'+data[i]).css('background', '#ffd1cc');
			    			}
			    			if(showDialogs){
				    			if(data.length>0){
				    				alert("Some invalid values");
				    			}else{
				    				alert("Verification successful");
			    				}
			    			}
			    		}
			    	});
		}
		$(document).ready(function(){
	<?php if($isAdmin){ ?>
			doCheck(false);
			$("#check").click(function(){
			    	doCheck(true);
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
								$('#<?php echo $key;?>').css('background', allOk?'#d0ffc7':'#ffd1cc');
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
			    					$('#'+sub.command).css('background', '#ffd1cc');
			    					$('#'+sub.command).val("")
			    				}else if(sub.size==1){
			    					$('#'+sub.command).css('background', '#d0ffc7');
			    					$('#'+sub.command).val(sub.paths);
			    				}else{
			    					hasMultiple= true;
			    					$('#'+sub.command).css('background', '#fffed4');
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
		echo "<table><tr><th>Key</th><th>Value</th></tr>";
			
			foreach(enumerateGlobalSettings() as $key=>$value){
				echo "<tr><td>";
				echo $key; ?></td>
				<td><input type=text size="50" id=<?php echo $key;?>  name=<?php echo $key;?> value="<?php echo $value;?>"/></td>
				<?php
			}
			?>
		</table>
		<br>
			<button class='button '  id='check'   name='check'>Verify Values<span class="fa fa-lg fap fa-check"></span></button>
			<button class='button '  id='update'   name='update'>Update Values<span class="fa fa-lg fap fa-wrench"></span></button>
			<button class='button '  id='findall'   name='findall'>Find Executable Paths<span class="fa fa-lg fap fa-search"></span></button>
			<br>
			<hr>
			<?php
			
			echo "<ul>";
			echo "<li>Verify runscript";
			require_once($GLOBALS['base']."/scripts/runscript.php");
			echo '<span class="fa fa-sm fap fa-check">';
			echo "</li><li>Verify disks";
			require_once($GLOBALS['base']."/scripts/disks.php");
			echo '<span class="fa fa-sm fap fa-check">';
			echo "</li><li>Verify apis";
			require_once($GLOBALS['base']."/scripts/apis.php");
			echo '<span class="fa fa-sm fap fa-check">';
			echo "</li><li>Verify processes";
			require_once($GLOBALS['base']."/scripts/processes.php");
			echo '<span class="fa fa-sm fap fa-check">';
			echo "</li><li>Verify usb";
			require_once($GLOBALS['base']."/scripts/usb.php");
			echo '<span class="fa fa-sm fap fa-check">';
			echo "</li></ul>";
			
			klog("SERVER PARAMETERS ".json_encode($_SERVER),LTRACE);
		?>
		<hr>
	</body>
</html>
