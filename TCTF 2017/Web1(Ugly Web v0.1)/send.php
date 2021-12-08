<?php
include("config.php");
$user = new User();
if(!$user->is_loaded()){
	header('Location: login.php' );
	exit();
}


if (!empty($_POST['to']) && $user->findUser($_POST['to'])){

	if($user->findUser($_POST['to']) !== "1"){
		if(!$_SESSION['task']) die('please get your work!');
		if(substr(md5($_POST['task']), 0, 6) !== $_SESSION['task']) die('prove your work first!');
		$_SESSION['task'] = substr(md5(gencsrftoken()), 0, 6);
	}
	

	$m = new Message($user->get_property('username'), $_POST['to'], $_POST['msg']);
	$manager = new MessageManager();
	$mid = $manager->send($m);

	echo "Message sent";
	exit();
}
$_SESSION['task'] = substr(md5(gencsrftoken()), 0, 6);

?>

<h1>Send Message</h1>
<p>Try to find a string $str so that (substr(md5($str), 0, 6) === '<?php echo $_SESSION['task']; ?>'). </p>
<p><form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" />
 username: <input type="text" name="to" /><br /><br />
 message: <input type="text" name="msg" /><br /><br />
 String:  <input type="text" name="task" /><br /><br />
 <input type="hidden" name="csrftoken" value="<?php echo $csrftoken; ?>"/>
 <input type="submit" value="Send" />
</form>
</p>