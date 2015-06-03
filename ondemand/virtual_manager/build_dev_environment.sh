#!/bin/bash

comment=RELEASE_$(date +'%s_%N')
token=flurry4EVR

job_host=jeeves.corp.flurry.com:8080
job=Build_Dev_Environment

branch=$2
config_branch=master
host=$1
deploy_schema=true
deploy_rtb_schema=true
restart_services=true
sync_libs=true

web_ip=$(echo "SELECT address FROM virtual_instances WHERE name = '$host'" | mysql -upe -padamRULES -hcrunchberry -N pe_systems)

if [[ ! "$web_ip" ]]; then
	echo "No web IP"
	exit 2
fi

echo "Running $job"
curl -s "http://$job_host/job/$job/buildWithParameters?comment=${comment}&token=${token}&branch=${branch}&config_branch=${config_branch}&host=${web_ip}&deploy_schema=${deploy_schema}&deploy_rtb_schema=${deploy_rtb_schema}&restart_services=${restart_services}&sync_libs=${sync_libs}"

exit 0
