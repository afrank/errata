#!/bin/bash

hostname=$1
hadoop_addr=$2

[[ "$hostname" && "$hadoop_addr" ]] || exit 0

hadoop_template_addr=HBASE_IP
template_hostname=WEB_HOSTNAME
host_template_addr=WEB_IP

addr=$(ifconfig eth0 | grep inet\ addr: | awk '{print $2}' | cut -d: -f2)

cat /etc/hosts | sed "s|$template_hostname|$hostname|g" | sed "s|$hadoop_template_addr|$hadoop_addr|g" > /tmp/hosts
mv -f /tmp/hosts /etc/hosts
hostname $hostname
cat /etc/sysconfig/network | sed "s|$template_hostname|$hostname|g" > /tmp/network
mv -f /tmp/network /etc/sysconfig/network

echo "UPDATE blacksmithdb.setting SET value=REPLACE(value,'WEB_IP','$addr') WHERE value like '%WEB_IP%'" | mysql -uflurry -pflurry -hlocalhost

#for stuff in ad-stg1.corp.flurry.com:8100 ts-stg1.flurry.com:8116; do 
#	s=(${stuff//:/ })
#	echo "UPDATE blacksmithdb.setting SET value=REPLACE(value,'${s[0]}','${addr}:${s[1]}') WHERE value like '%${s[0]}%'" | mysql -uflurry -pflurry -hlocalhost
#done

# UPDATE blacksmithdb.setting SET value=REPLACE(value,'https://','http://') where name = 'AdPortalSecureRootUrl';

#echo "update blacksmithdb.setting set value = 'http://rtb-vip:8110/rest/company' where id = 511" | mysql -uflurry -pflurry -hlocalhost
#echo "update blacksmithdb.hbasetableref set writeActive=0 where id = 261" | mysql -uflurry -pflurry -hlocalhost

echo "delete from blacksmithdb.hbasetableref where name in ('metrics-compactionTest-1','metrics-compactionTest-2','users-backup','users-compactionTest','metrics-incremental-2','metrics-incremental-3','metrics-incremental-4','metrics-incremental-5-1','metrics-incremental-5-2','metrics-incremental-6-1','metrics-incremental-6-2','sessionUserAgentLookup-1','sessionIpLookup-1.1','metrics-incremental-1','metrics-incremental')" | mysql -uflurry -pflurry -hlocalhost

echo 'CREATE TEMPORARY TABLE temp_DuplicateChannelCampaignIds AS (SELECT a.channelcampaign_id as id from aad a where a.channelcampaign_id is not null group by a.channelcampaign_id having count(1) > 1); update aad set channelcampaign_id = null where channelcampaign_id in (select * from temp_DuplicateChannelCampaignIds); DROP TABLE temp_DuplicateChannelCampaignIds' | mysql -uflurry -pflurry -hlocalhost blacksmithdb

bash /usr/local/bin/run_me_first.sh
