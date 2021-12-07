//TODOK

# QNAP USBRun

This package installs a new application (USBRun) able to run an arbitrary bash script
when connecting an arbitrary usb device.

The usb device recognition is based on the product, manufacturer and (if present) serial 
number of the usb device
	
## Main pages

 * Scripts: The scripts management
	* Create/Edit/Delete/Kill Scripst
	* Show the running scripts (with a wheeel) and their logs (they are updated in real-time)
	* Backup the current scripts
	* Restore single scripts
	* Download the application log
	* Script Editor
	* Can disable a script
	* Can force the ejection after the script execution
 * USB Devices
	* Show connected devices and the ones disconnected but used
	* For the storage devices show the paths
 * Setup:
	* Can configure and verify the paths of the applications used by the scripts and change accordingly
	* Tests the application files for integrity

## Build on your QNAP

### Setup QDK

In QTS Desktop, open **App Center**.

Search **QDK** and install the latest version.

### Build

Upload this project to one of your NAS folder.

Login via ssh on you QNAP NAS, go inside this package directory
and execute **qbuild** command to build this qpkg.

	$ cd /share/Public/USBRun
	$ qbuild
	Creating archive with data files for arm-x19...
	Creating archive with control files...
	Creating QPKG package...
	...
	Creating QPKG package...
	
Now inside the build directory you will find your package!

	$ ls build/
	USBRun_0.1_arm-x19.qpkg         
	...
	USBRun_0.1_x86_64.qpkg.md5
	
### Install

Then choose the one for your CPU, go to AppCenter and upload :)

## Standard usage

### Warnings

All scripts are in sh shell (as if they had the header "#!/bin/sh"). At every start 
and stop of a script i suggest to call a function logging the date like...

	date
	
When using scripts that are binded to the existence of a specific directory a check can
be added like the following:

	# Check if the folder exists 
	if [ -d "/share/AndroidPhone" ]; then
		#Do stuffs
	fi
	
The scripts can be added only by administartors group users and are executed impersonating the user creating the script

An error in the script (A command returning a <0 error level) will stop the script without notice.
You can place an ||true at the end of the commands to avoid this like

	ls -lrt||true
	
This will not block

### Create a script

By default the script will run when the device is recognized by the OS. If you
have multiple scripts for a single device remember to avoid disconnecting the 
device!

 * Insert a USB device in your QNAP
 * What for its appearance on the toolbar
 * Open USBRun and go on "USB Scripts" then "Create new"
 * There will be a combo box containing all USB devices, choose yours
 * Give the script a name 
 * Choose if the device must be disconnected after the script
 * Choose if the script is disabled (will not run on device insertion, but
 only when clicking play)
 * Then save the script
 
### Run a script

You can run a script 

 * Directly from the "USB Scripts"
 * On the scripts editing page
 * Automatically (if not disabled)
 
When a script is running you can check the logs clicking on the "paper" icon.
When you are on the editing page will be only shown a "tail" of the logs 
while clicking on the "paper" icon will transfer you to the full log of the
process selected (or the last run)

### Terminating

The script are terminated with the "STOP" button. This is the same as issuing
a KILL command
	
### Script debugging

To change the loggin level go on "Setup" screen and change to one of the following 

 * 1: errors only
 * 2: warnings and below
 * 3: info and below
 * 4: everything
 
May be some command you expect does not exists on qnap. OR has a non standard path
Try to go with ssh and search where they are

On the "Setup" page there is a list of the bash commands needed by the project. These
can be verified via "Verify Values"

On the scripts page you can download the scripts created (for backup) or download the
full log of the application (not the one for the script)


