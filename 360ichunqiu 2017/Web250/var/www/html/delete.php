<?php
include 'inc/function.php';
include 'inc/config.php';

$strsql="delete from notice where id=". intval($_GET["id"]); 
$result=mysql_query($strsql,$conn) or die("SQL执行出错");
die("<script>alert('删除成功！');history.go(-1);</script>");
?>
