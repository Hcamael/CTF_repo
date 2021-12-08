<?php session_start(); ?>
<?php

require 'function.php';

switch(@$_GET['action']){
	case 'login':
		$username = $_POST['username'];
		$password = md5($_POST['password']);

		$db = sqlite();
		$statement = $db->prepare('SELECT * FROM users WHERE username = :u AND password = :p');
		$statement->bindValue(':u', $username);
		$statement->bindValue(':p', $password);
		$result = $statement->execute();
		$row = $result->fetchArray();


		if($row){
			$_SESSION['userid'] = (int) $row['id'];
			$_SESSION['username'] = $row['username'];
			$_SESSION['perm'] = (int) $row['perm'];
			$_SESSION['error'] = NULL;
			$_SESSION['notify'] = 'Login successfully!';
		} else {
			unset($_SESSION['userid'],$_SESSION['username'],$_SESSION['perm']);
			$_SESSION['error'] = "Wrong username/password!";
		}
		die(header("Location: index.php"));
		break;

	case 'register':
		$username = $_POST['username'];
		$password = md5($_POST['password']);
		$pin = $_POST['pin'];
		if(!is_pin($pin)){
			$_SESSION['error'] = "PIN is not valid";
			die(header("Location: index.php"));
		}
		$db = sqlite();
		$statement = $db->prepare('INSERT INTO users VALUES(NULL, :u, :p, :pin, 0)');
		$statement->bindValue(':u', $username);
		$statement->bindValue(':p', $password);
		$statement->bindValue(':pin', $pin);
		$result = $statement->execute();

		if($db->changes() == 1){
			$_SESSION['notify'] = 'Sign up successfully!';
		} else {
			$_SESSION['error'] = "Something went wrong!";
		}

		die(header("Location: index.php"));
		break;
		
	case 'logout':
		unset($_SESSION['userid'],$_SESSION['username'],$_SESSION['perm'],$_SESSION['p']);
		die(header("Location: index.php"));
		break;

	default:
		break;

}

?>
