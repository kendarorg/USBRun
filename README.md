index.php:
	show jobs for user (if adminstrators show all with userid) with job name and enabled status
	add delete modifiy
job.php
	show all available devices with serial number manufacturer, product and paths connected in a combo
		wiht "apply" button
	if administrators can edit the user
	show the job name
	show the serial number selected
	show the textview with the command
	show the enabled flag
	
		

# USBRun Game
This is a USBRun game QPKG example. 

The following instructions can help you to build and use this QPKG.

### Setup QDK Environment
Before start to develop your QPKG or use this example, NAS should already setup the QDK environment:

In QTS Desktop, open **App Center**.

Search **QDK** and install the latest version.

### Build
Upload this project to one of your NAS folder.

Login to QNAP NAS and execute **qbuild** command to build this qpkg.

For example:
```
[admin@SW4-TS42 USBRun]$ pwd
/share/CACHEDEV1_DATA/QDK-Guide/example/QPKG/USBRun
[admin@SW4-TS42 USBRun]$ ls
arm-x19/          arm-x41/          build/            icons/            qpkg.cfg          x86/              x86_ce53xx/
arm-x31/          arm_64/           config/           package_routines  shared/           x86_64/
[admin@SW4-TS42 USBRun]$ qbuild
Creating archive with data files for arm-x19...
Creating archive with control files...
Creating QPKG package...
Creating archive with data files for arm-x31...
Creating archive with control files...
Creating QPKG package...
Creating archive with data files for arm-x41...
Creating archive with control files...
Creating QPKG package...
Creating archive with data files for arm_64...
Creating archive with control files...
Creating QPKG package...
Creating archive with data files for x86...
Creating archive with control files...
Creating QPKG package...
Creating archive with data files for x86_64...
Creating archive with control files...
Creating QPKG package...
Creating archive with data files for x86_ce53xx...
Creating archive with control files...
Creating QPKG package...
[admin@SW4-TS42 USBRun]$ ls build/
breakout_0.1_arm-x19.qpkg         breakout_0.1_arm-x31.qpkg.md5     breakout_0.1_arm_64.qpkg          breakout_0.1_x86.qpkg.md5         breakout_0.1_x86_ce53xx.qpkg
breakout_0.1_arm-x19.qpkg.md5     breakout_0.1_arm-x41.qpkg         breakout_0.1_arm_64.qpkg.md5      breakout_0.1_x86_64.qpkg          breakout_0.1_x86_ce53xx.qpkg.md5
breakout_0.1_arm-x31.qpkg         breakout_0.1_arm-x41.qpkg.md5     breakout_0.1_x86.qpkg             breakout_0.1_x86_64.qpkg.md5
[admin@SW4-TS42 USBRun]$
```

### Installation

After successfully build QPKG.

Download the corresponding QPKG file in **build/** folder to your computer. (depends on the architecture of your QNAP NAS model)

In QTS Desktop, open **App Center**.

Then manual Install the QPKG.

Now you can test this example QPKG and start to develop your own.





=================
/dev/sdd1                15.3G     35.2M     15.3G   0% /share/external/DEV3301_1
overlay                   5.8T      3.7T      2.0T  64% /share/CACHEDEV1_DATA/Container/container-station-data/lib/docker/overlay2/03499536bb6e208e966622806793a7e6a8aa98144725d33b36d2082b100f8868/merged
shm                      64.0M         0     64.0M   0% /share/CACHEDEV1_DATA/Container/container-station-data/lib/docker/containers/7372d083cb743256e12b69c482cb8abc2444c078eae59af3ffb02acb1b571b2a/mounts/shm
[~] # lsusb
Bus 001 Device 005: ID 1004:61f9 LG Electronics, Inc.
Bus 001 Device 004: ID 051d:0002 American Power Conversion Uninterruptible Power Supply
Bus 001 Device 003: ID 05e3:0610 Genesys Logic, Inc.
Bus 001 Device 002: ID 1c05:2074
Bus 001 Device 006: ID 0781:540e SanDisk Corp. Cruzer Contour Flash Drive
Bus 001 Device 001: ID 1d6b:0002 Linux Foundation 2.0 root hub
Bus 002 Device 003: ID 05e3:0612 Genesys Logic, Inc.
Bus 002 Device 002: ID 1c05:3074
Bus 002 Device 001: ID 1d6b:0003 Linux Foundation 3.0 root hub
[~] # cat /proc/mounts|grep 3301
/dev/sdd1 /share/external/DEV3301_1 vfat rw,relatime,fmask=0111,dmask=0000,allow_utime=0022,codepage=437,iocharset=iso8859-1,shortname=mixed,utf8,errors=remount-ro 0 0
[~] # tune2fs -l /dev/sdd1
tune2fs 1.43.9 (8-Feb-2018)
tune2fs: Bad magic number in super-block while trying to open /dev/sdd1
/dev/sdd1 contains a vfat file system labelled 'ESD-USB'
[~] # blkid
/dev/sdd1: LABEL="ESD-USB" UUID="C08D-113D" TYPE="vfat"
[~] # fdisk -l
255 heads, 63 sectors/track, 24884 cylinders
Units = cylinders of 16065 * 512 = 8225280 bytes

Disk /dev/dm-13 doesn't contain a valid partition table

Disk /dev/sdd: 16.4 GB, 16441481728 bytes
255 heads, 63 sectors/track, 1998 cylinders
Units = cylinders of 16065 * 512 = 8225280 bytes

   Device Boot      Start         End      Blocks   Id  System
/dev/sdd1   *           1        1999    16054272    c  W95 FAT32 (LBA)
[~]lsusb -v
Bus 001 Device 006: ID 0781:540e SanDisk Corp. Cruzer Contour Flash Drive
Device Descriptor:
  bLength                18
  bDescriptorType         1
  bcdUSB               2.00
  bDeviceClass            0 (Defined at Interface level)
  bDeviceSubClass         0
  bDeviceProtocol         0
  bMaxPacketSize0        64
  idVendor           0x0781 SanDisk Corp.
  idProduct          0x540e Cruzer Contour Flash Drive
  bcdDevice            0.10
  iManufacturer           1 SanDisk Corporation
  iProduct                2 Cruzer Contour
  iSerial                 3 0000168307754383
  bNumConfigurations      1
  Configuration Descriptor:
    bLength                 9
    bDescriptorType         2
    wTotalLength           32
    bNumInterfaces          1
    bConfigurationValue     1
    iConfiguration          0
    bmAttributes         0x80
      (Bus Powered)
    MaxPower              200mA
    Interface Descriptor:
      bLength                 9
      bDescriptorType         4
      bInterfaceNumber        0
      bAlternateSetting       0
      bNumEndpoints           2
      bInterfaceClass         8 Mass Storage
      bInterfaceSubClass      6 SCSI
      bInterfaceProtocol     80 Bulk (Zip)
      iInterface              0
      Endpoint Descriptor:
        bLength                 7
        bDescriptorType         5
        bEndpointAddress     0x81  EP 1 IN
        bmAttributes            2
          Transfer Type            Bulk
          Synch Type               None
          Usage Type               Data
        wMaxPacketSize     0x0200  1x 512 bytes
        bInterval               0
      Endpoint Descriptor:
        bLength                 7
        bDescriptorType         5
        bEndpointAddress     0x01  EP 1 OUT
        bmAttributes            2
          Transfer Type            Bulk
          Synch Type               None
          Usage Type               Data
        wMaxPacketSize     0x0200  1x 512 bytes
        bInterval               1
Device Qualifier (for other device speed):
  bLength                10
  bDescriptorType         6
  bcdUSB               2.00
  bDeviceClass            0 (Defined at Interface level)
  bDeviceSubClass         0
  bDeviceProtocol         0
  bMaxPacketSize0        64
  bNumConfigurations      1
Device Status:     0x3100
  (Bus Powered)


REMOVABLE_DRIVES=""
for _device in /sys/block/*/device; do
    if echo $(readlink -f "$_device")|egrep -q "usb"; then
        _disk=$(echo "$_device")
        REMOVABLE_DRIVES="$REMOVABLE_DRIVES $_disk"
    fi
done
echo Removable drives found: "$REMOVABLE_DRIVES"


REMOVABLE_DRIVES=""
for _device in /sys/block/*/device; do
    if echo $(readlink -f "$_device")|egrep -q "usb"; then
        _disk=$(echo "$_device" | cut -f4 -d/)
        REMOVABLE_DRIVES="$REMOVABLE_DRIVES $_disk"
    fi
done
echo Removable drives found: "$REMOVABLE_DRIVES"





[~] # lsusb
Bus 001 Device 006: ID 0781:540e SanDisk Corp. Cruzer Contour Flash Drive
Bus 001 Device 001: ID 1d6b:0002 Linux Foundation 2.0 root hub
Bus 002 Device 003: ID 05e3:0612 Genesys Logic, Inc.
[~] # df
/dev/sdd1                15.3G     35.2M     15.3G   0% /share/external/DEV3301_1

[~] # udevadm info --name=/dev/sdd1 --attribute-walk|grep 540e
    ATTRS{idProduct}=="540e"
[~] # udevadm info --name=/dev/sdd1 --attribute-walk|grep 0781
    ATTRS{modalias}=="usb:v0781p540Ed0010dc00dsc00dp00ic08isc06ip50in00"
    ATTRS{idVendor}=="0781"
[~] #

======================================

# Find the usb disks
cat /proc/mounts|grep /dev/sd|cut -f1 "-d "
	/dev/sdd1
# Find the label of the disk
blkid |grep /dev/sdd1|cut -f2 "-d "|cut -f2 "-d="|sed 's/^"\(.*\)"$/\1/'
	ESD-USB
# Check the directory where was mounted
cat /proc/mounts|grep /dev/sdd1|cut -f2 "-d "
	/share/external/DEV3301_1
# Check the vendor and product of the usb drive, can add 
#		ATTRS{devnum}=="6"
# 		ATTRS{busnum}=="1"
#		ATTRS{serial}=="0000168307754383"
#		ATTRS{product}=="Cruzer Contour"
#		ATTRS{manufacturer}=="SanDisk Corporation"
udevadm info --name=/dev/sdd1 --attribute-walk|grep -m 2 -e 'idProduct\|idVendor'
    ATTRS{idProduct}=="540e"
    ATTRS{idVendor}=="0781"

# Retrieve the bus given bus/dev/product/vendor
lsusb|grep "0781:540e"
	Bus 001 Device 006: ID 0781:540e SanDisk Corp. Cruzer Contour Flash Drive
	
	
	
	
ls -la /share|grep "ESD-USB ->"|cut -f13 "-d "
	external/DEV3301_1/
	
	


https://stackoverflow.com/questions/9358568/reload-entire-page-with-ajax-request-and-changed-get-parameters