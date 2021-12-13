<?php
// cp -Rf /share/Public/Temp/USBRun/shared/* /share/CACHEDEV1_DATA/.qpkg/USBRun/
// echo "SUBSYSTEM==\"usb\", ACTION==\"add\", ENV{DEVTYPE}==\"usb_device\", RUN+=\"/share/CACHEDEV1_DATA/.qpkg/QNAP_HelloWorld/udevtrigger.sh '\$attr{busnum}' '\$attr{devnum}' '\$attr{manufacturer}' '\$attr{product}' '\$attr{serial}' || true\"" >  /lib/udev/rules.d/80-kendar.rules
// udevadm control --reload-rules
//	/share/CACHEDEV1_DATA/.qpkg/HybridBackup/rr2/test/support/hbs_support/rru

require_once(dirname(dirname(__FILE__))."/scripts/commons.php");
require_once($GLOBALS['base']."/scripts/auth.php");

$uidAndGroup = findUserId();
$isAdmin = isAdmin($uidAndGroup);


?>
<html>
	<head>
	<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="basic.css">
	<script src="jquery.js"></script>
</head>
	<body>
		<?php require_once("menu.php");?>
		<hr>
		<b>Help</b>
		<ul>
			<li>
				Scripts: The scripts management
				<ul>
					<li>
						Create/Edit/Delete/Kill Scripst
					</li>
					<li>
						Show the running scripts (with a wheeel) and their logs (they are updated in real-time)
					</li>
					<li>
						Backup the current scripts
					</li>
					<li>
						Restore single scripts
					</li>
					<li>
						Download the application log
					</li>
				</ul>
			</li>
			<li>
				Script Editor
				<ul>
					Can disable a script
				</ul>
				<ul>
					Can force the ejection after the script execution
				</ul>
			</li>
			<li>
				USB Devices
				<ul>
					<li>
						Show connected devices and the ones disconnected but used
					</li>
					<li>
						For the storage devices show the paths
					</li>
				</ul>
			</li>
			<li>
				Setup: 
				<ul>
					<li>
						Can configure and verify the paths of the applications used by the scripts and change accordingly
					</li>
					<li>
						Tests the application files for integrity
					</li>
				</ul>
			</li>
		</ul>
		<hr>
		<b>Notes</b>
		<ul>
			<li>
				All scripts are in sh shell
			</li>
			<li>
				Suggest to add a call to "date" at the beginning and end
			</li>
			<li>
				When in doubt set the log level in Setup screen to 1 (errors only) 2 (warnings too) 3 (info too) or 4 (everything) to check for errors
			</li>
			<li>
				Some command is kinda different on QNAP shell so the path could be something unexpected. Try to go with ssh and search where they are
			</li>
			<li>
				The scripts can be added only by administartors group users and are executed impersonating the user creating the script
			</li>
			<li>
				The devices listed are the one connected, with unique serial numbers, and the ones that has script attached
			</li>
			<li>
				For storage devices is suggested to add a check for the existance of the share. The path will be <u>"/shared/[File station path]"</u>  E.g. for an Android phone
				<pre>
	if [ -d "/share/LG K10/Card" ]; then
		# DO JOB
	fi
				</pre>
			</li>
		</ul>
		<hr>
	</body>
</html>