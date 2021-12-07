#!/bin/sh

currentDir=$(cd "${0%[/\\]*}" > /dev/null && pwd)
busnum=$1
devnum=$2
manufacturer=$3
product=$4
serial=$5

/sbin/write_log "[USBRun] Intercepted Usb Device $busnum $devnum" 4
cd $currentDir
/usr/local/apache/bin/php $currentDir/scripts/runscript.php "$busnum" "$devnum" "$manufacturer" "$product" "$serial" &
exit 0