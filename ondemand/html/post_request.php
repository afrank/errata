<?
require_once('user_functions.php');
require_once('db_functions.php');
require_once('frontend_functions.php');
require_once('virt_functions.php');

if(empty($_POST)) header("Location: index.php");

$email = $_POST["email"];
$branch = $_POST["branch"];

if(empty($email)) header("Location: index.php?errMsg=no_email");
if(empty($branch)) $branch = "master";

$conn = db_connect();

request_environment($conn,$email,$branch);

header("Location: index.php");
?>
