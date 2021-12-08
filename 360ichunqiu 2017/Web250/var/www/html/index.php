<?php
include 'inc/function.php';
include 'inc/config.php';

$strsql="select * from notice"; 
$result=mysql_query($strsql,$conn);


?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"> 
	<title>通知列表</title>
	<link rel="stylesheet" href="css/bootstrap.min.css">  
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
</head>
<body>
<table class="table table-striped">
 <tr>
   <td><a href="add.php">添加通知</a>  |  <a href="options.php">系统设置</a></td>
 </tr>
</table>
<table class="table table-striped">
	<caption>通知列表</caption>
	<thead>
		<tr>
			<th>邮件</th>
            <th>标题</th>
			<th>内容</th>
            <th>操作</th>
		</tr>
	</thead>
	<tbody>
<?php
while ($row=mysql_fetch_assoc($result)) 
{ 

?>
		<tr>
			<td><?php echo $row['email'];?></td>
            <td><?php echo $row['title'];?></td>
            <td><?php echo $row['content'];?></td>
            <td><a href="send.php?id=<?php echo $row['id'];?>">发送</a></td>
			<td><a href="delete.php?id=<?php echo $row['id'];?>">删除</a></td>
		</tr>
<?php


} 
mysql_free_result($result); 
?>
	</tbody>
</table>

</body>
</html>