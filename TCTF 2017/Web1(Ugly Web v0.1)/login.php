<?php
require_once 'config.php';
$user = new User();
if ( $_GET['logout'] == 1 ) 
	$user->logout('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
if ( !$user->is_loaded() )
{
	if ( isset($_POST['uname']) && isset($_POST['pwd'])){
	  if ( !$user->login($_POST['uname'],$_POST['pwd'],$_POST['remember'] )){
	    echo 'Wrong username and/or password';
	  }else{
	    header('Location: index.php');
	    exit();
	  }
	} ?>
	<h1>Login</h1>
	<p><form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" />
	  email: <input type="text" name="uname" /><br /><br />
  	  password: <input type="password" name="pwd" /><br /><br />
	  Remember me? <input type="checkbox" name="remember" value="1" /><br /><br />
	  <input type="hidden" name="csrftoken" value="<?php echo $csrftoken; ?>"/>
	  <input type="submit" value="login" />
	</form>
	</p>
	<?php
}else{
	header('Location: index.php');
	exit();
}
?>