<?php
include 'inc/function.php';
include 'inc/config.php';


if($_GET['action']== 'add')
{
  $email = $_POST['email'];
  $title = $_POST['title'];
  $content = $_POST['content'];
  
  $strsql="insert into notice(email,title,content) values('$email','$title','$content')"; 
  $result=mysql_query($strsql,$conn);
  die("<script>alert('保存成功！');history.go(-1);</script>");
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"> 
	<title>通知内容</title>
	<link rel="stylesheet" href="css/bootstrap.min.css">  
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
</head>
<body>
<form id="test" method="post" action="add.php?action=add">	
<table class="table table-striped">
	<caption>通知内容</caption>
	<tbody>
    	<tr>
            <td>邮箱</td>
			<td><input type="text" name="email"  id="email" class="form-control"></td>
		</tr>
    	<tr>
            <td>标题</td>
			<td><input type="text" name="title"  id="title" class="form-control"></td>
		</tr>
    	<tr>
            <td>内容</td>
			<td><textarea name="content"  id="content" style="width: 451px; height: 258px;" > </textarea></td>
		</tr>
    	<tr>
            <td colspan=2 ><input  class="btn btn-lg btn-primary btn-block"  type="submit" value="保存"> </input></td>
		</tr>
	</tbody>
</table>
</form>
</body>
</html>