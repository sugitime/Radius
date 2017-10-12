#!/bin/sh
#2017 - sugitime

export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/sd/lib:/sd/usr/lib
export PATH=$PATH:/sd/usr/bin:/sd/usr/sbin

if [ "$1" = "start" ]; then
	cd /tmp/
    airmon-ng check kill
    airmon-ng start $(uci get radius.module.interface)
	hostapd-wpe $(uci get radius.module.configfile) &> /tmp/hostapd-wpe.scan &

elif [ "$1" = "stop" ]; then
    killall hostapd-wpe
    airmon-ng check kill
fi
