#!/bin/sh

CONF=/etc/config/qpkg.conf
QPKG_NAME="USBRun"
QPKG_ROOT=`/sbin/getcfg $QPKG_NAME Install_Path -f ${CONF}`
APACHE_ROOT=`/sbin/getcfg SHARE_DEF defWeb -d Qweb -f /etc/config/def_share.info`
export QNAP_QPKG=$QPKG_NAME

function UsbUdevAdd()
{
	commandToExec=$1
	echo "SUBSYSTEM==\"usb\", ACTION==\"add\", ENV{DEVTYPE}==\"usb_device\", RUN+=\"$commandToExec '\$attr{busnum}' '\$attr{devnum}' '\$attr{manufacturer}' '\$attr{product}' '\$attr{serial}' || true\"" >  /lib/udev/rules.d/80-usbrun.rules
	udevadm control --reload-rules
}

function UsbUdevRemove()
{
	rm -f /lib/udev/rules.d/80-usbrun.rules
	udevadm control --reload-rules
}


function DiskFindUsb()
{
	allDisks=""
	counter=0
	cat /proc/mounts|grep /dev/sd|cut -f1 "-d "| while read foundedDisk ; do
		if [[ "$Server_Name" -eq 1 ]]; then
			allDisks+="|"
		fi
	   	allDisks="$allDisks $foundedDisk"
	   	((i=i+1))
	done
	echo $allDisks
}

function fn_log()
{
    echo $1
    app_log $1
}



case "$1" in
  start)
    ENABLED=$(/sbin/getcfg $QPKG_NAME Enable -u -d FALSE -f $CONF)
    if [ "$ENABLED" != "TRUE" ]; then
        /bin/echo "$QPKG_NAME is disabled."
        fn_log "$QPKG_NAME is disabled."
        exit 1
    fi
    : ADD START ACTIONS HERE
    ln -s $QPKG_ROOT/web /home/Qhttpd/Web/$QPKG_NAME
    chmod +x $QPKG_ROOT/*.sh
    chmod +x $QPKG_ROOT/scripts/*.sh
    UsbUdevAdd "$QPKG_ROOT/udevtrigger.sh"
    ;;

  stop)
    : ADD STOP ACTIONS HERE
    rm /home/Qhttpd/Web/$QPKG_NAME
    UsbUdevRemove
    ;;

  restart)
    $0 stop
    $0 start
    ;;

  *)
    echo "Usage: $0 {start|stop|restart}"
    exit 1
esac

exit 0
