<?php
require_once 'config.php';
$user = new User();
if (!empty($_GET['activate'])){
  $hash = md5($_GET['activate']);
  $res = $user->query("SELECT `{$user->tbFields['active']}` FROM `{$user->dbTable}` WHERE `activationHash` = '$hash' LIMIT 1",__LINE__);
  if ( $rec = $res->fetch_array() ){
    if ( $rec[0] == 1 )
      echo 'Your account is already activated';
    else{
      if ($user->query("UPDATE `{$user->dbTable}` SET `{$user->tbFields['active']}` = 1 WHERE `activationHash` = '$hash' LIMIT 1", __LINE__))
        echo 'Account activated. You may login now';
      else
        echo 'Unexpected error. Please contact an administrator';
    }
  }else{
    echo 'User account does not exists';
  }
}
if (!empty($_POST['username'])){
  $h = $user->randomPass(100);
  $hash = md5($h);
  while( mysqli_num_rows($user->query("SELECT * FROM `{$user->dbTable}` WHERE `activationHash` = '$hash' LIMIT 1")) == 1)
      $hash = $user->randomPass(20);
  $data = array(
    'username' => $_POST['username'],
    'email' => $_POST['email'],
    'password' => $_POST['pwd'],
    'activationHash' => $hash,
    'active' => 0
  );
  $userID = $user->insertUser($data);
  if ($userID==0)
    echo 'User not registered';
  else {
    echo 'User registered with user id '.$userID. '. Activate your account using the instructions on your mail.';
  $email = 'Activate your user account by visiting : '. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] .'?activate='.$h;
  mail($_POST['email'], 'Activate your account', $email);
  }
}
?>
<h1>Register</h1>
<p><form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" />
 username: <input type="text" name="username" /><br /><br />
 password: <input type="password" name="pwd" /><br /><br />
 email: <input type="text" name="email" /><br /><br />
<input type="hidden" name="csrftoken" value="<?php echo $csrftoken; ?>"/>
 <input type="submit" value="Register user" />
</form>
</p>