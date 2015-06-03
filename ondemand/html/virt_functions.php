<?php

function build_virt_array($db) {
	$res = array();
	#$q = "SELECT e.id dev_id,i1.id web_id,i1.name web_host,i1.address web_ip,i2.id hbase_id,i2.name hbase_host,i2.address hbase_ip,e.owner,e.updated_on,e.expired_on FROM
	#	dev_environment e 
	#	JOIN virtual_instances i1 ON (i1.id=e.web_id) 
	#	JOIN virtual_instances i2 ON (i2.id=e.hbase_id)
	#";
	$q = "SELECT *,IF(expired_on < NOW(),1,0) is_expired FROM dev_environment";
	$result = mysql_query($q,$db);
	while($obj = mysql_fetch_object($result)) {
		$sq = "SELECT d.dev_id,v.id,v.updated_on,v.address,v.name,v.type,v.status from dev_environment_instances d JOIN virtual_instances v ON (d.instance_id=v.id) WHERE d.dev_id = ".$obj->id;
		$sresult = mysql_query($sq,$db);
		$instances = array();
		while($sobj = mysql_fetch_object($sresult)) {
			$instances[] = $sobj;
		}
		$res[] = array('environment'=>$obj,'instances'=>$instances);
	}
	return $res;
}

function build_request_queue($db) {
	$res = array();
	$q = "SELECT * FROM dev_environment_requests WHERE status = 1";
	$result = mysql_query($q,$db);
	while($obj = mysql_fetch_object($result)) {
		$res[] = $obj;
	}
	return $res;
}

function request_environment($db,$email,$branch) {
	if(empty($email) || empty($branch)) return FALSE;
	$q = "INSERT INTO dev_environment_requests (email,branch,status) VALUES ('$email','$branch',1)";
	mysql_query($q,$db);
	return TRUE;
}
