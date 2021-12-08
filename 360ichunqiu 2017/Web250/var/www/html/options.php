<?php
include 'inc/function.php';
include 'inc/config.php';

if($_GET['action']== 'save')
{
  $config = $_POST['config'];
  
  saveConfig($config);
  
  die("<script>alert('保存成功！');history.go(-1);</script>");
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"> 
	<title>系统设置</title>
	<link rel="stylesheet" href="css/bootstrap.min.css">  
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
</head>
<body>
<form id="test" method="post" action="?action=save">	
<table class="table table-striped">
	<caption>系统设置</caption>
	<tbody>
    	<tr>
            <td>系统路径</td>
			<td><input type="text" name="config[root_path]" value="<?php echo dirname(__FILE__);?>"  id="root_path" class="form-control"></td>
		</tr>
    	<tr>
            <td>发送邮箱</td>
			<td><input type="text" name="config[send_mail]" value="<?php echo getConfig('send_mail');?>"  id="send_mail" class="form-control"></td>
		</tr>
    	<tr>
            <td colspan=2 ><input  class="btn btn-lg btn-primary btn-block"  type="submit" value="保存"> </input></td>
		</tr>
	</tbody>
</table>
</form>
</body>
</html>