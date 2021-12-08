<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$base_path = '/';

$currentCookieParams = session_get_cookie_params(); 

session_set_cookie_params( 
    $currentCookieParams["lifetime"], 
    $base_path, 
    $currentCookieParams["domain"], 
    $currentCookieParams["secure"], 
    true 
); 

session_start();

$tctfflag = "***";

$DBHOST = 'localhost';
$DBUSER = '';
$DBPASS = '';
$DBNAME = '';

$mysqli = new mysqli($DBHOST, $DBUSER, $DBPASS, $DBNAME);

function gencsrftoken($length=10, $chrs = '1234567890qwertyuiopasdfghjklzxcvbnm'){
	$csrf = '';
	for($i = 0; $i < $length; $i++) {
		$csrf .= $chrs{mt_rand(0, strlen($chrs)-1)};
	}
	return $csrf;
}

$csrftoken = gencsrftoken();
setcookie('csrftoken', $csrftoken, time()+3600, $base_path);

if(!empty($_POST)){
	if($_POST['csrftoken'] !== $_COOKIE['csrftoken']) die("Stop csrf attack!");
}

if($_SESSION['userSessionValue'] === '1'){
	setcookie('flag', $tctfflag, time()+3600, $base_path, '', false, true);
}

include("user.class.php");
include("message.class.php");
