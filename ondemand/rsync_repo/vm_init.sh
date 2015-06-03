#!/bin/bash

echo root:root | chpasswd
echo flurry:flurry | chpasswd

rm -f /etc/udev/rules.d/70-persistent-net.rules

case $(hostname) in
	hm*|hbase*) rsync -av jeeves::repo/setup_hbase_host.sh /tmp/;;
	web*) rsync -av jeeves::repo/setup_web_host.sh /tmp/;;
esac

if [[ ! -e /etc/yum.repos.d/flurry-Percona.repo ]]; then
	rsync -av jeeves::repo/flurry-Percona.repo /etc/yum.repos.d/
	yum makecache
fi

[[ -e /usr/bin/mysql ]] || yum -y install mysql-client

[[ -e /usr/bin/mysqld_safe ]] && echo "INSERT IGNORE INTO schema_deltas (DELTA_NAME) VALUES ('FLURRY-9319'),('FLURRY-9470'),('FLURRY-6675')" | mysql -uflurry -pflurry -hlocalhost blacksmithdb

rsync -av jeeves::repo/push_metricagg_libs.sh /usr/local/bin/
rsync -av jeeves::repo/phone_home.sh /usr/local/bin/
. /usr/local/bin/phone_home.sh
