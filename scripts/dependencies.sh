#!/bin/sh
#2017 - sugitime

export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/sd/lib:/sd/usr/lib
export PATH=$PATH:/sd/usr/bin:/sd/usr/sbin

if [ "$1" = "install" ]; then
  if [ "$2" = "internal" ]; then
	   opkg update
     opkg install hostapd
  elif [ "$2" = "sd" ]; then
    opkg update
    opkg install hostapd
  fi

  touch /etc/config/radius
  echo "config radius 'module'" > /etc/config/radius

  uci set radius.module.installed=1
  uci commit radius.module.installed

elif [ "$1" = "remove" ]; then
    opkg remove hostapd
    rm -rf /etc/config/radius
fi
