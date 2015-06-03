#!/bin/bash

echo "Syncing latest metricagg libs from /usr/local/blacksmith/metricAggregator/lib"
su - flurry -c 'hadoop dfs -put /usr/local/blacksmith/metricAggregator/lib /lib_temp'
su - flurry -c 'hadoop dfs -mv /lib /lib_toDelete'
su - flurry -c 'hadoop dfs -mv /lib_temp /lib'
su - flurry -c 'hadoop dfs -rmr /lib_toDelete'
echo "Done."
