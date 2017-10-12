#!/bin/sh
#2017 - sugitime

export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/sd/lib:/sd/usr/lib
export PATH=$PATH:/sd/usr/bin:/sd/usr/sbin

if [ "$1" = "install" ]; then
  cd /tmp
  wget https://github.com/TarlogicSecurity/hostapd-wpe-openwrt/raw/master/packages/ar71xx/generic/hostapd-wpe_2014-06-03.1-1_ar71xx.ipk
  opkg install ./hostapd-wpe_2014-06-03.1-1_ar71xx.ipk

  touch /etc/config/radius
  echo "config radius 'module'" > /etc/config/radius

  uci set radius.module.installed=1
  uci commit radius.module.installed
fi