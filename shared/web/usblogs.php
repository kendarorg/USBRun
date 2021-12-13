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
		
	<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="basic.css">
	<script src="jquery.js"></script>
		<script>
			window.lastContent="";
			window.lastIndex="";
			window.linesCount=100;
			window.linesCountMid=1000;
			window.linesCountMax=10000;
			window.data=[-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1];
			
			function changePage(index){
				document.getElementById("start").value=window.data[index];
				loadPage(window.data[index],false);
			}
			
			
			function setValue(index,maxIndex){
				
				for(var i=(index+1);i<maxIndex;i++){
					if(window.data[index]==window.data[i]){
						$("#bt"+index).hide();
						return;
					}
				}
				$("#bt"+index).show();
			}
			
			
			
			function loadPage(index,dopoll){
				index = parseInt(index);
				
				$("#bt0").html(index);
				
				$('#logsdownload').click(function(){
			    	window.location="download.php?id=scriptlog&log="+document.getElementById("id").value;
			    });
				
				if(dopoll)window.isPolling = true;
			   	$.ajax({
			   		url: "readscriptlog.php",
					type: "POST",
			   		data:{
			    		id:document.getElementById("id").value,
			    		name:'',
			    		script:'',
			    		serial:'',
			    		pollid:window.readscripttimes,
			    		start:index,
			    		count: window.linesCount
    				}, 
    				success: function(result){
    					resultData = JSON.parse(result);
    					allLines=resultData['lines'];
    					content = resultData['content'];
    					
    					if(window.lastContent != content){
	    					document.getElementById("testresult").innerHTML=content;
	    				}
	    				window.lastContent = content;
	    				console.log("INDEX: "+index);
	    				maxLines= Math.floor(allLines/window.linesCount)*window.linesCount;
	    				window.data[0]=0;
	    				window.data[1]=Math.floor((index-window.linesCount*3)/window.linesCountMax)*window.linesCountMax;
	    				window.data[2]=Math.floor((index-window.linesCount*3)/window.linesCountMid)*window.linesCountMid;
	    				window.data[3]=Math.floor((index-window.linesCount*3)/window.linesCount)*window.linesCount;
	    				window.data[4]=Math.floor((index-window.linesCount*2)/window.linesCount)*window.linesCount;
	    				window.data[5]=Math.floor((index-window.linesCount*1)/window.linesCount)*window.linesCount;
	    				window.data[6]=index;
	    				window.data[7]=Math.floor((index+window.linesCount*1)/window.linesCount)*window.linesCount;
	    				window.data[8]=Math.floor((index+window.linesCount*2)/window.linesCount)*window.linesCount;
	    				window.data[9]=Math.floor((index+window.linesCount*3)/window.linesCount)*window.linesCount;
	    				window.data[10]=Math.floor((index+window.linesCountMid)/window.linesCountMid)*window.linesCountMid;
	    				window.data[11]=Math.floor((index+window.linesCountMax)/window.linesCountMax)*window.linesCountMax;
	    				window.data[12]=maxLines;
	    				
	    				var prespan="<span class='navtext'>";
	    				var postspan="</span>"
						
						if(window.data[6]!=window.data[0]){
							$("#bt0").show();
							$("#bt0").html(prespan+"&nbsp"+window.data[0]+postspan);
						}else{
							$("#bt0").hide();
						}
	    				
	    				for(var i=1;i<6;i++){
							if(window.data[i]==window.data[i+1] || window.data[i]<0
								 || window.data[i]==window.data[0] || window.data[i]==window.data[6] || window.data[i]==window.data[12]){
								$("#bt"+i).hide();
							}else{
								$("#bt"+i).show();
								$("#bt"+i).html(prespan+"&nbsp;"+window.data[i]+postspan);
							}
						}
						
						
						$("#bt6").show();
						$("#bt6").html(prespan+"["+window.data[6]+"]"+postspan);
						for(var i=11;i>6;i--){
							if(window.data[i]==window.data[i-1] || window.data[i]>=maxLines
								 || window.data[i]==window.data[6] || window.data[i]==window.data[12]){
								$("#bt"+i).hide();
							}else{
								$("#bt"+i).show();
								$("#bt"+i).html(prespan+"&nbsp"+window.data[i]+postspan);
							}
						}
						
						if(window.data[6]!=window.data[12]){
							$("#bt12").show();
							$("#bt12").html(prespan+"&nbsp"+window.data[12]+postspan);
						}else{
							$("#bt12").hide();
						}
	    				
	    				
	    				
    					if(resultData['running']){
    						if($("#running").html()==""){
	    						$("#running").html("<img src='ajax-loader.gif'></img>");
	    					}
    					}else{
    						if(dopoll)window.clearInterval(document.interval);
    						if(dopoll)document.interval=null;
    						$("#running").html("");
    						return;
    					}
    					
    					
    					if(dopoll)window.isPolling = false;
    				}, 
    				error: function(jqXHR, textStatus, errorThrown) {
						console.log(JSON.stringify(jqXHR));
						console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
				    	if(dopoll)window.clearInterval(document.interval);
				    	if(dopoll)document.interval=null;
    					$("#running").html("");
    					//$("#pointtop").html("");
    					//$("#pointbot").html("");
    					if(dopoll)window.isPolling = false;
				  	}
				});
			}
			
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
						index = document.getElementById("start").value;
						loadPage(index,true);
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
		
		<button class='button tooltip fa fa-2x fa-refresh'  id='logs'   name='logs'><span class='tooltiptext'>Refresh Logs</span></button>
		
		<button class='button tooltip fa fa-2x fa-download'  id='logsdownload'   name='logsdownload'><span class='tooltiptext'>Download Logs</span></button>
		<button class='button tooltip fa fa-2x fa-pencil'  id='edit'   name='edit'><span class='tooltiptext'>Edit Script</span></button>
		<hr>
		<button class='navigatebutton fa fa-fast-backward' style="display:none;" id=bt0  name=bt0  onclick='changePage(0)'>&nbsp0</button>
		<button class='navigatebutton fa fa-backward' style="display:none;" id=bt1  name=bt1  onclick='changePage(1)'></button>
		<button class='navigatebutton fa fa-step-backward' style="display:none;" id=bt2  name=bt2  onclick='changePage(2)'></button>
		<button class='navigatebutton fa fa-caret-left' style="display:none;" id=bt3  name=bt3  onclick='changePage(3)'></button>
		<button class='navigatebutton fa fa-caret-left' style="display:none;" id=bt4  name=bt4  onclick='changePage(4)'></button>
		<button class='navigatebutton fa fa-caret-left' style="display:none;" id=bt5  name=bt5  onclick='changePage(5)'></button>
		<span class='navigatebutton' style="display:none;"   id=bt6  name=bt6  ></span>
		<button class='navigatebutton fa fa-caret-right' style="display:none;" id=bt7  name=bt7  onclick='changePage(7)'></button>
		<button class='navigatebutton fa fa-caret-right' style="display:none;" id=bt8  name=bt8  onclick='changePage(8)'></button>
		<button class='navigatebutton fa fa-caret-right' style="display:none;" id=bt9  name=bt9  onclick='changePage(9)'></button>
		<button class='navigatebutton fa fa-step-forward' style="display:none;" id=bt10 name=bt10 onclick='changePage(10)'></button>
		<button class='navigatebutton fa fa-forward' style="display:none;" id=bt11 name=bt11 onclick='changePage(11)'></button>
		<button class='navigatebutton fa fa-fast-forward'  style="display:none;" id=bt12 name=bt12 onclick='changePage(12)'></button>
		<span id="running" name="running"></span>
		<hr>	
		
		<div id="testresult" name="testresult"></div>
		<hr>
	</body>
</html>