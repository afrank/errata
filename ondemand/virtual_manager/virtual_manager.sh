#!/bin/bash

min_spare_web=3
min_spare_hbase=3

mysql_args="-upe -padamRULES -hcrunchberry -N pe_systems"

# 0. I have no idea why this is necessary, and I haven't had time to track down the bug.
echo "update virtual_instances set status = 1 where address is null and status = 2" | mysql $mysql_args

# 1. check for virtual instances that came up but haven't been validated as running yet
echo "SELECT name FROM virtual_instances WHERE status = 1" | mysql $mysql_args | while read vhost; do
	ip=$(./get_ip_address.py $vhost 2>/dev/null)
	if [[ "$ip" ]]; then
		echo "UPDATE virtual_instances SET updated_on=NOW(),status=3,address='$ip' WHERE name = '$vhost'" | mysql $mysql_args
		echo "Validated $vhost -> $ip"
	fi
done

# 2. check for environment creation capacity
l=($(echo "select if(hbase<web,hbase,web) capacity, web, hbase from (select sum(if(type='web',1,0)) web, sum(if(type='hbase',1,0)) hbase from virtual_instances v left join dev_environment_instances d on (v.id=d.instance_id) where d.instance_id is null and v.status = 3) foo where web is not null and hbase is not null" | mysql $mysql_args))
dev_capacity=${l[0]}
web_capacity=${l[1]}
hbase_capacity=${l[2]}

# 2. check for pending dev environment requests
if [[ ${dev_capacity:-0} -gt 0 ]]; then
	echo "SELECT * FROM dev_environment_requests WHERE status = 1 ORDER BY requested_on ASC LIMIT ${dev_capacity:-0}" | mysql $mysql_args | while read line; do
		l=($line)
		id=${l[0]}
		email=${l[1]}
		branch=${l[2]}
		next_web_name=$(echo "select name from virtual_instances v left join dev_environment_instances d on (d.instance_id=v.id) where d.instance_id is null and v.status = 3 and v.type='web' order by v.updated_on asc limit 1" | mysql $mysql_args)
		next_hbase_name=$(echo "select name from virtual_instances v left join dev_environment_instances d on (d.instance_id=v.id) where d.instance_id is null and v.status = 3 and v.type='hbase' order by v.updated_on asc limit 1" | mysql $mysql_args)
		if [[ "$next_web_name" && "$next_hbase_name" ]]; then
			echo ./setup_dev_cluster.sh $email $next_web_name $next_hbase_name
			./setup_dev_cluster.sh $email $next_web_name $next_hbase_name
			./build_dev_environment.sh $next_web_name $branch
		fi
		echo "UPDATE dev_environment_requests set status = 2 WHERE id = '$id'" | mysql $mysql_args
	done
fi

# 3. check for unserviced pending requests and add them to the min_spares, and subtract that from instances already coming up
unserviced=$(echo "SELECT count(1) FROM dev_environment_requests WHERE status = 1" | mysql $mysql_args)
#if [[ ${unserviced:-0} -eq 0 ]]; then
# there are no unserviced requests, so it's possible we already have more hosts than needed

spare_web=$(echo "select count(1) from virtual_instances v left join dev_environment_instances d on (d.instance_id=v.id) where d.instance_id is null and v.status = 3 and v.type='web'" | mysql $mysql_args)
spare_hbase=$(echo "select count(1) from virtual_instances v left join dev_environment_instances d on (d.instance_id=v.id) where d.instance_id is null and v.status = 3 and v.type='hbase'" | mysql $mysql_args)
((need_web=${min_spare_web:-0}+${unserviced:-0}-${spare_web:-0}))
[[ ${need_web:-0} -lt 0 ]] && need_web=0
((need_hbase=${min_spare_hbase:-0}+${unserviced:-0}-${spare_hbase:-0}))
[[ ${need_hbase:-0} -lt 0 ]] && need_hbase=0

#	((need_web=${min_spare_web:-0}-${spare_web:-0}))
#	[[ ${need_web:-0} -lt 0 ]] && need_web=0
#	((need_hbase=${min_spare_hbase:-0}-${spare_hbase:-0}))
#	[[ ${need_hbase:-0} -lt 0 ]] && need_hbase=0
#else
#	# there are unserviced requests, so we can assume there are no spares
#	((need_web=${min_spare_web:-0}+${unserviced:-0}))
#	((need_hbase=${min_spare_hbase:-0}+${unserviced:-0}))
#fi

pending_web=$(echo "SELECT count(1) from virtual_instances WHERE status IN (1,2) AND type = 'web'" | mysql $mysql_args)
pending_hbase=$(echo "SELECT count(1) from virtual_instances WHERE status IN (1,2) AND type = 'hbase'" | mysql $mysql_args)
((need_web-=${pending_web:-0}))
((need_hbase-=${pending_hbase:-0}))

if [[ ${need_web:-0} -gt 0 ]]; then
	for (( i=0; $i < $need_web; i++ )); do
		new_name=$(echo "select concat('web-',cur+1) new_name from (select cast(substring(name,locate('-',name)+1) as unsigned) cur from virtual_instances where type = 'web' order by 1 desc limit 1) foo" | mysql $mysql_args)
		echo "$i starting a web $new_name"
		nohup ./create_instance.py -t web -h $new_name &
		sleep 5
	done
fi

if [[ ${need_hbase:-0} -gt 0 ]]; then
	for (( i=0; $i < $need_hbase; i++ )); do
		new_name=$(echo "select concat('hbase-',cur+1) new_name from (select cast(substring(name,locate('-',name)+1) as unsigned) cur from virtual_instances where type = 'hbase' order by 1 desc limit 1) foo" | mysql $mysql_args)
		echo "$i starting an hbase $new_name"
		nohup ./create_instance.py -t hbase -h $new_name &
		sleep 5
	done
fi

# 4. check for expired environments, and shut them down
echo "SELECT v.name FROM 
		dev_environment_instances i 
		JOIN dev_environment d ON (d.id=i.dev_id) 
		JOIN virtual_instances v ON (v.id=i.instance_id) 
		WHERE d.expired_on <> '0000-00-00 00:00:00' 
		AND from_unixtime(unix_timestamp(d.expired_on) + (select value from settings where name = 'default_env_expiration_grace_sec')) < now();
" | mysql $mysql_args | while read name; do
	./delete_instance.py $name
	./check_instance.py $name 2>/dev/null
	if [[ $? -eq 1 ]]; then
		echo "DELETE FROM virtual_instances WHERE name = '$name'" | mysql $mysql_args
	fi
done
echo "DELETE FROM dev_environment 
	WHERE expired_on <> '0000-00-00 00:00:00' 
	AND from_unixtime(unix_timestamp(expired_on) + (select value from settings where name = 'default_env_expiration_grace_sec')) < now() 
	AND id not in (select dev_id from dev_environment_instances)" | mysql $mysql_args

echo "DELETE FROM dev_environment_instances WHERE instance_id NOT IN (SELECT id FROM virtual_instances)" | mysql $mysql_args

# 5. remove instances that haven't been assigned and are X old

