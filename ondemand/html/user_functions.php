<?

function login($conn,$email,$password) {
	$query = sprintf("SELECT COALESCE(MAX(id),0) user_id FROM user WHERE email = '%s' AND password = '%s'",
			mysql_real_escape_string($email),
			$password);

	$result = mysql_query($query,$conn);

	$user_id = @mysql_result($result,0,'user_id');

	if(!empty($user_id) && $user_id > 0) {
		session_start();
		$session_key = md5($email).":".$password;
		$_SESSION["session_key"] = base64_encode($session_key);
		$_SESSION["user_id"] = $user_id;
		return TRUE;
	} else return FALSE;
}

function get_user($conn,$user_id) {
	$query = sprintf("SELECT * FROM user WHERE id = %s",$user_id);
	$result = mysql_query($query,$conn);
	if($result) return mysql_fetch_object($result);
	else return FALSE;
}

function get_datasources($conn,$user_id) {
	$query = sprintf("SELECT * FROM data_sources WHERE user_id = %s",$user_id);
	$result = mysql_query($query,$conn);
	if($result) {
		$ds = array();
		while($row = mysql_fetch_object($result)) {
			$ds[$row->id] = $row;
		}
		return $ds;
	} else return FALSE;
}

function save_ds($conn,$user_id,$ds_host,$ds_port=3306,$ds_username,$ds_password,$ds_default_db="") {
	if($ds_port <= 0) $ds_port = 3306;
	$query = sprintf("SELECT add_ds(%s,'%s',%s,'%s','%s','%s') result",
			$user_id,
			mysql_real_escape_string($ds_host),
			mysql_real_escape_string($ds_port),
			mysql_real_escape_string($ds_username),
			mysql_real_escape_string($ds_password),
			mysql_real_escape_string($ds_default_db)
		);
	$result = @mysql_query($query,$conn);
	$ds_id = @mysql_result($result,0,'result');
	if(empty($ds_id) || $ds_id == 0) return FALSE;
	else return $ds_id;
}

