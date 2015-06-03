#!/bin/bash

assignee=$1
web_host=$2
hbase_host=$3

if [[ ! ( "$assignee" && "$web_host" && "$hbase_host" ) ]]; then
	echo assignee web_host hbase_host
	exit 0
fi

web_ip=$(echo "SELECT address FROM virtual_instances WHERE name = '$web_host'" | mysql -upe -padamRULES -hcrunchberry -N pe_systems)
hbase_ip=$(echo "SELECT address FROM virtual_instances WHERE name = '$hbase_host'" | mysql -upe -padamRULES -hcrunchberry -N pe_systems)

rsync -av /data2/rsync_repo/setup_web_host.sh $web_ip:/tmp/
rsync -av /data2/rsync_repo/setup_hbase_host.sh $hbase_ip:/tmp/

ssh -n $web_ip "/tmp/setup_web_host.sh $web_host $hbase_ip"
ssh -n $hbase_ip "/tmp/setup_hbase_host.sh $hbase_host $web_ip"

# echo "INSERT INTO dev_environment (web_id,hbase_id,owner) VALUES ((SELECT id FROM virtual_instances WHERE name = '$web_host'),(SELECT id FROM virtual_instances WHERE name = '$hbase_host'),'$assignee')" | mysql -upe -padamRULES -hcrunchberry -N pe_systems

dev_id=$(echo "START TRANSACTION; INSERT INTO dev_environment (owner) VALUES ('$assignee'); SELECT LAST_INSERT_ID(id) FROM dev_environment ORDER BY 1 DESC LIMIT 1; COMMIT;" | mysql -upe -padamRULES -hcrunchberry -N pe_systems)

if [[ "$dev_id" ]]; then
	echo "INSERT INTO dev_environment_instances (dev_id,instance_id) VALUES ($dev_id,(SELECT id FROM virtual_instances WHERE name = '$hbase_host'))" | mysql -upe -padamRULES -hcrunchberry -N pe_systems
	echo "INSERT INTO dev_environment_instances (dev_id,instance_id) VALUES ($dev_id,(SELECT id FROM virtual_instances WHERE name = '$web_host'))" | mysql -upe -padamRULES -hcrunchberry -N pe_systems
	echo "UPDATE dev_environment SET expired_on = DATE_ADD(NOW(), INTERVAL 10 YEAR) WHERE id = $dev_id" | mysql -upe -padamRULES -hcrunchberry -N pe_systems
fi

ssh -n $hbase_ip "su - flurry -c start-all.sh"
sleep 5
ssh -n $hbase_ip "su - flurry -c start-all.sh"
sleep 5
ssh -n $hbase_ip "su - flurry -c start-hbase.sh"
sleep 5
ssh -n $hbase_ip "su - flurry -c start-hbase.sh"

ssh -n $web_ip "service flurry_kafkaZookeeper restart"
sleep 5
ssh -n $web_ip "service flurry_rtbZookeeper restart"
sleep 5
ssh -n $web_ip "service flurry_kafkaServer restart"

# ./check_vms.sh
