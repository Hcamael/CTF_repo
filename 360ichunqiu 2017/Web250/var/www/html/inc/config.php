<?php 
error_reporting(0);

$server="localhost:3306"; 
$username="root"; 
$password=""; 
$database="example"; 

if(ini_get('register_globals')){
   foreach($_REQUEST as $k=>$v) unset(${$k});
}

if (!get_magic_quotes_gpc())
{
    if (!empty($_GET))
    {
        $_GET  = addslashes_deep($_GET);
    }
    if (!empty($_POST))
    {
        $_POST = addslashes_deep($_POST);
    }

    $_COOKIE   = addslashes_deep($_COOKIE);
    $_REQUEST  = addslashes_deep($_REQUEST);
}
session_start();

header("Content-Type:text/html; charset=utf-8");
$conn=mysql_connect($server, $username, $password); 
if(!mysql_select_db($database,$conn)){die("数据库连接失败！");};
mysql_query("SET NAMES utf8, character_set_client=binary, sql_mode='', interactive_timeout=3600 ;",$conn);

$timezone = getConfig('timezone');
if($timezone != "")
{
  putenv("TZ=$timezone");
}else{
  putenv("TZ=Asia/Shanghai");
}

if (!defined('NoNeedLogin'))
{
   CheckLogin();
}

?>
