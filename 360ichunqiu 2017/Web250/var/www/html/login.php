<?php
define('NoNeedLogin',true);
include 'inc/function.php';
include 'inc/config.php';

if($_GET['action']== 'login')
{
  $name = $_POST['name'];
  $password = $_POST['password'];
  $strsql="select * from admin where username='$name' and password='".md5($password)."' limit 1"; 
  $result=mysql_query($strsql,$conn);
  $row = mysql_fetch_array($result);
  if(empty($row))
  {
      die('用户名或密码错误!');
  }else{
      $_SESSION["username"] = $name;
      header("location: index.php");
      exit();
  }
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" href="favicon.ico">
		<title>login</title>
		<!-- Bootstrap core CSS -->
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<script src="js/jquery.min.js"></script>
        <style>
body {
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #eee;
}

.form-signin {
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .form-signin-heading,
.form-signin .checkbox {
  margin-bottom: 10px;
}
.form-signin .checkbox {
  font-weight: normal;
}
.form-signin .form-control {
  position: relative;
  height: auto;
  -webkit-box-sizing: border-box;
     -moz-box-sizing: border-box;
          box-sizing: border-box;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
        </style>
	</head>

	<body>

		<form   class="form-signin" id="test" method="post" enctype="multipart/form-data" action="login.php?action=login">		 
			<h2 class="form-signin-heading">登录</h2>
			<label for="inputEmail" class="sr-only">用户名</label>
			<input type="text" name="name"  id="name" class="form-control" placeholder="用户名" required autofocus>
			<label for="inputPassword" class="sr-only">密码</label>
			<input type="password"  name="password" id="password" class="form-control" placeholder="密码" required>
			<input  class="btn btn-lg btn-primary btn-block"  type="submit" value="登录"> </input>
		</form>

	</body>

</html>