<?php
require_once 'config.php';
$user = new User();
if (!empty($_POST['reset'])){
  $hash = md5($_POST['reset']);
  $password = md5($_POST['pwd']);
  $res = $user->query("SELECT * FROM `reset` WHERE `hash` = '$hash' LIMIT 1",__LINE__);
  if ( $rec = $res->fetch_array() ){
      $user->query("DELETE FROM `reset` WHERE `hash`='$hash'");
      if ($user->query("UPDATE `{$user->dbTable}` SET `password` = '$password' WHERE `userID` = {$rec['userID']} LIMIT 1", __LINE__))
        die('Password changed');
      else
        die('Unexpected error. Please contact an administrator');
    }
}

if($_GET['step'] == '2'){
?>
<h1>Verify</h1>
<p><form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" />
 code: <input type="text" name="reset" /><br /><br />
 new password: <input type="password" name="pwd" /><br /><br />
 <input type="hidden" name="csrftoken" value="<?php echo $csrftoken; ?>"/>
 <input type="submit" value="Submit" />
</form>
</p>
<?php
exit();
}

if (!empty($_POST['email'])){
  $res = $user->query("SELECT * FROM `{$user->dbTable}` WHERE `email`='".$user->escape($_POST['email'])."'");
  if($rec = $res->fetch_array()){
    $h = $user->randomPass(20);
    $hash = md5($h);
    $sql = "INSERT INTO `reset`(`userID`, `hash`) VALUES('{$rec['userID']}', '$hash');";
    $user->query($sql);
    $email = 'Reset password code is: '.$h;
    mail($_POST['email'], 'Reset your password', $email);
    header('Location: reset.php?step=2' );
    exit();
  }else{
    die('email not found');
  }
}

?>

<h1>Find password</h1>
<p><form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" />
 email: <input type="text" name="email" /><br /><br />
<input type="hidden" name="csrftoken" value="<?php echo $csrftoken; ?>"/>
 <input type="submit" value="Submit" />
</form>
</p>