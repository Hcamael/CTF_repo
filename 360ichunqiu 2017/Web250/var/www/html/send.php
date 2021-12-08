<?php
include 'inc/function.php';
include 'inc/config.php';

$strsql="select * from notice where id=". intval($_GET["id"]); 
$result=mysql_query($strsql,$conn) or die("SQL执行出错");
$row = mysql_fetch_array($result);
if(empty($row))
{
   die("<script>alert('数据不存在！');history.go(-1);</script>");
}

$to = $row['email'];
$subject = $row['title'];
$message = $row['content'];
$from = getConfig('send_mail');
$headers = "From: $from";
mail($to,$subject,$message,$headers);

die("<script>alert('发送成功！');history.go(-1);</script>");
?>
