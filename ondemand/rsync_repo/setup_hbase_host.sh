#!/bin/bash

hostname=$1
web_host=$2

[[ "$hostname" ]] || exit 0

template_addr=HBASE_IP
template_hostname=HBASE_HOSTNAME
template_web_addr=WEB_IP

addr=$(ifconfig eth0 | grep inet\ addr: | awk '{print $2}' | cut -d: -f2)

cat /etc/hosts | sed "s|$template_addr|$addr|g" | sed "s|$template_hostname|$hostname|g" | sed "s|$template_web_addr|$web_host|g" > /tmp/hosts
mv -f /tmp/hosts /etc/hosts
hostname $hostname
cat /etc/sysconfig/network | sed "s|$template_hostname|$hostname|g" > /tmp/network
mv -f /tmp/network /etc/sysconfig/network

# cat /usr/local/blacksmith/hadoop/mrconf/hibernate.cfg.xml | sed "s|WEB_IP|$web_host|g" > /tmp/hibernate.cfg.xml
# mv -f /tmp/hibernate.cfg.xml /usr/local/blacksmith/hadoop/mrconf/hibernate.cfg.xml

# cat /usr/local/blacksmith/hadoop/conf/hibernate.cfg.xml | sed "s|WEB_IP|$web_host|g" > /tmp/hibernate.cfg.xml
# mv -f /tmp/hibernate.cfg.xml /usr/local/blacksmith/hadoop/mrconf/hibernate.cfg.xml


# echo "create 'metrics-incremental-1', {NAME => 'v', DATA_BLOCK_ENCODING => 'NONE', BLOOMFILTER => 'NONE', REPLICATION_SCOPE => '0', VERSIONS => '1', COMPRESSION => 'LZO', MIN_VERSIONS => '0', TTL => '2147483647', KEEP_DELETED_CELLS => 'false', BLOCKSIZE => '65536', IN_MEMORY => 'false', ENCODE_ON_DISK => 'true', BLOCKCACHE => 'true'}" | /usr/local/blacksmith/hbase/bin/hbase shell

# echo "create 'metrics-incremental', {NAME => 'v', DATA_BLOCK_ENCODING => 'NONE', BLOOMFILTER => 'NONE', REPLICATION_SCOPE => '0', VERSIONS => '1', COMPRESSION => 'LZO', MIN_VERSIONS => '0', TTL => '2147483647', KEEP_DELETED_CELLS => 'false', BLOCKSIZE => '65536', IN_MEMORY => 'false', ENCODE_ON_DISK => 'true', BLOCKCACHE => 'true'}" | /usr/local/blacksmith/hbase/bin/hbase shell
