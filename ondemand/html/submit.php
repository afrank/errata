<?
require_once('user_functions.php');
require_once('db_functions.php');
require_once('frontend_functions.php'); 
require_once('virt_functions.php');

$conn = db_connect();

foreach($_GET as $key => $val) {
	switch($key) {
		case "renew":
			$q = "UPDATE dev_environment SET expired_on = DATE_ADD(NOW(), INTERVAL 1 WEEK) WHERE id = $val";
			mysql_query($q,$conn);
			break;
		case "expire":
			$q = "UPDATE dev_environment SET expired_on = DATE_SUB(NOW(), INTERVAL 10 SECOND) WHERE id = $val";
			mysql_query($q,$conn);
			break;
		case "unexpire":
			$q = "UPDATE dev_environment SET expired_on = DATE_ADD(NOW(), INTERVAL 10 YEAR) WHERE id = $val";
			mysql_query($q,$conn);
			break;
	}
}
header("Location: index.php");
?>
