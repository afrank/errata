<?
require_once('user_functions.php');
require_once('db_functions.php');
require_once('frontend_functions.php'); 
require_once('virt_functions.php');

$page = $_SERVER["PHP_SELF"];

$topnav = array();
$topnav[] = array('link'=>'index.php','title'=>'Home');
$topnav[] = array('link'=>'history.php','title'=>'History');
$topnav[] = array('link'=>'settings.php','title'=>'Settings');

for($i=0;$i<sizeof($topnav);$i++) {
	if($topnav[$i]['link'] == $page) $topnav[$i]['active'] = 1;
	else $topnav[$i]['active'] = 0;
}

$conn = db_connect();

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Dev Environment Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <!-- link href='http://fonts.googleapis.com/css?family=Fascinate' rel='stylesheet' type='text/css' -->
    <link href="/ondemand/static/css/bootstrap.css" rel="stylesheet">
    <link href="/ondemand/static/css/docs.min.css" rel="stylesheet">
    <link href="/ondemand/static/css/nav.css" rel="stylesheet">
    <!-- link href="/ondemand/static/css/sqlsyntax.css" rel="stylesheet" -->
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link href="/ondemand/static/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header" style="margin-left:-140px;">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Dev Environment Dashboard</a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
              <? foreach($topnav as $nav) {
                        if($nav['active'] == 1) { ?>
              <li class="active"><a href="<?=$nav['link']?>"><?=$nav['title']?></a></li>
                        <? } else { ?>
              <li><a href="<?=$nav['link']?>"><?=$nav['title']?></a></li>
                        <? } ?>
              <? } ?>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
