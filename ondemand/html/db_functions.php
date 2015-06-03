<?

function db_connect() {
	$my = mysql_connect("crunchberry","pe","adamRULES");
	mysql_select_db("pe_systems",$my);
	return $my;
}

function get_all_queries($conn,$user_id) {
	$query = sprintf("SELECT * FROM queries WHERE ds_id IN (SELECT id FROM data_sources WHERE user_id = %s)",$user_id);
	$result = mysql_query($query,$conn);
	if($result) {
		$queries = array();
		while($row = mysql_fetch_object($result)) {
			$queries[$row->id] = $row;
		}
		return $queries;
	} else return FALSE;
}

function get_query_result($conn,$user_id,$query_id) {
	$q = get_query_details($conn,$user_id,$query_id);
	if($q) {
		#if($q->host == "localhost") $q->host = "zapp";
		$_db = mysql_connect($q->host,$q->username,$q->password);
		mysql_select_db($q->default_db,$_db);
		$q_result = mysql_query($q->query);
		if($q_result) {
			$q_out = array();
			while($q_row = mysql_fetch_assoc($q_result)) {
				$q_out[] = $q_row;
			}
		}
		@mysql_close($_db);
		$q->result = $q_out;
		$q->headers = get_table_headers($q_out);
		return $q;
	} else return FALSE;
	
}

function get_query_details($conn,$user_id,$query_id) {
	$query = sprintf("SELECT s.*,q.* FROM queries q JOIN data_sources s ON (s.id=q.ds_id) WHERE q.id = %s AND s.user_id = %s",$query_id,$user_id);
	$result = mysql_query($query,$conn);
	if($result) return mysql_fetch_object($result);
	else return FALSE;
}

function get_table_headers($data) {
	if(sizeof($data) < 1) return FALSE;
	$headers = array_keys($data[0]);
	$h = array();
	foreach($headers as $header) {
		$h[] = array('short'=>$header,'long'=>str_replace('_',' ',ucfirst($header)));
	}
	return $h;
}

function get_ds_creds($conn,$ds_id) {
	$query = sprintf("SELECT * FROM data_sources WHERE id = %s",$ds_id);
	$result = @mysql_query($query,$conn);
	if($result) return mysql_fetch_object($result);
	else return FALSE;
}

function save_query($conn,$q_ds_id,$q_query,$q_title,$q_description) {
	$query = sprintf("SELECT add_query(%s,'%s','%s','%s') result",
			$q_ds_id,
			mysql_real_escape_string($q_query),
			mysql_real_escape_string($q_title),
			mysql_real_escape_string($q_description)
		);
	#$result = mysql_query($query,$conn) or die (mysql_errno($conn) . ": " . mysql_error($conn) . ": ". $query);
	$result = @mysql_query($query,$conn);
	$query_id = @mysql_result($result,0,'result');
	if(empty($query_id) || $query_id == 0) return FALSE;
	else return $query_id;
}
