<?php

$fp = fopen(SECRET_PATH,"rb");
fseek($fp, 16);
$key = fread($fp, 32);
$iv = fread($fp, 16);
fclose($fp);

function index(){
  $db = sqlite();
  $statement = $db->prepare('SELECT count(*) FROM files WHERE username = :u');
  $statement->bindValue(':u', $_SESSION['username']);
  $result = $statement->execute();
  $f = $result->fetchArray()[0];

  $statement = $db->prepare('SELECT count(*) FROM passwords WHERE id = :id');
  $statement->bindValue(':id', md5($_SESSION['userid'].$_SESSION['username']));
  $result = $statement->execute();
  $p = $result->fetchArray()[0];

  echo <<<EOF
      <h1 class="page-header"><span class="btn btn-lg btn-warning">Dashboard</span></h1>
      <h2>You have {$f} files</h2>
      <h2>You have {$p} passwords</h2>
EOF;
}

function profile(){
  if($_SESSION['perm']>0) $role='Premium User'; else $role='User';
  echo <<<EOF
  <div class="col-sm-4">
  <h1 class="page-header"><span class="btn btn-lg btn-warning">Your profile</span></h1>

  <div class="well">
  <h4>Username: {$_SESSION['username']}</h4>
  <h4>ID: {$_SESSION['userid']}</h4>
  <h4>Permission: {$role}</h4>
  <h4>PIN: ******</h4>
  </div>
  </div>
EOF;
}

function files(){
  echo <<<EOF
  <h2 class="sub-header">Your files</h2>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Filename</th>
            <th>Time</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
EOF;
      $db = sqlite();
      $statement = $db->prepare('SELECT * FROM files WHERE username = :u');
      $statement->bindValue(':u', $_SESSION['username']);
      $result = $statement->execute();

      while($row = $result->fetchArray()){
        $f = basename($row['path']);
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$f}</td>
                <td>{$row['timestamp']}</td>
                <td><button class='btn btn-success' onclick='var pin=prompt(\"Enter your PIN\");document.location.href=\"?p=download&id={$row['id']}&pin=\"+pin;'>Download</button></td>
              </tr>\n";
      }
  echo <<<EOF
        </tbody>
      </table>
    </div>
  </div>
EOF;
}

function download(){
  $id = (int) $_GET['id'];
  $pin = $_GET['pin'];
  if(!is_pin($pin)){
    die('<div class="alert alert-danger" role="alert">PIN is not valid</div>');
  }

  $db = sqlite();
  $statement = $db->prepare('SELECT * FROM files WHERE id = :id and username = :u');
  $statement->bindValue(':id', $id);
  $statement->bindValue(':u', $_SESSION['username']);
  $result = $statement->execute();
  $row = $result->fetchArray();

  if(!$row){die(header("Location: index.php"));}

  $_SESSION['username'] = basename($_SESSION['username']);
  $dec_file = explode('_'.$_SESSION['username'],$row['path'])[0];
  $dec_file .= ".out";
  $basename = basename($dec_file);

  $params = array(
      'p' => $pin,
      'username' => $_SESSION['username'],
      'path' => $row['path'],
      'filename' => $dec_file
    );

  $params = json_encode($params);
  $cipher = bin2hex(cbc_encrypt($params));
  $cmd = "LD_LIBRARY_PATH=/usr/local/lib ".BINARY_FILES_PATH." download $cipher 2>&1";
  $shell = shell_exec($cmd);

  echo "<h1 class=\"page-header\"><span class=\"btn btn-lg btn-success\">$basename</span></h1>";
  if($shell == ""){
    if($_SESSION['perm'] > 0){ # for premium user only.
      $dec_file = FILES_PATH.$dec_file;
      if(file_exists($dec_file)) {
          echo "<pre>";
          chmod($dec_file,0400);
          readfile($dec_file);
          chmod($dec_file,0000);
          echo "</pre>";
        }
    } else {
      echo "This is DEMO version for pre-purchase user only.";
    }
  } else {
    die('<div class="alert alert-danger" role="alert">Something went wrong</div>');
  }
}

function upload(){
  echo <<<EOF
  <div class="col-sm-8">
  <form action="?p=upload" method="post" enctype="multipart/form-data">
    <h2 class="form-signin-heading">Upload to TCLOUD</h2>
    <div class="form-group">
    <input type="file" name="files" id="InputFile">
    <label for="password">Trial password:
    <input type="password" name="password" placeholder=""> We have already sent it to our pre-purchase customers <3
    </label>
    <div class="checkbox">
    <label>
      <input type="checkbox" name="params[sharing]"> Open-link sharing
    </label>
    </div>
    <div class="checkbox">
    <label>
      <input type="checkbox" name="params[encrypt]" checked> Encrypt
    </label>
    </div>
    <button class="btn btn-lg btn-primary btn-block" type="submit">upload</button>
  </div>
EOF;

  if(!empty($_FILES['files'])){
    if($_FILES['files']['error'] != 0){
      die('<div class="alert alert-danger" role="alert">File uploaded unsuccessfully</div>');
    } else {

      $_SESSION['username'] = basename($_SESSION['username']); # //admin => admin
      $tmp_name = $_FILES['files']['tmp_name'];
      $path_info = pathinfo(substr($_FILES['files']['name'],0,128));
      $filename = $path_info['basename'];
      $ext = $path_info['extension'];

      if($ext != "txt") {
       die('<div class="alert alert-warning">This is DEMO version. "txt" extension is only allowed</div>');
      }

      $db = sqlite();
      $statement = $db->prepare('SELECT pin FROM users WHERE username = :u');
      $statement->bindValue(':u', $_SESSION['username']);
      $result = $statement->execute();
      $row = $result->fetchArray();

      $path = md5($_SESSION['username'].$_SESSION['userid'].$_SESSION['perm']);
      $out = $filename.'_'.$_SESSION['username'].'.'.time();

      $params = array(
          'p' => $row['pin'],
          'username' => $_SESSION['username'],
          'tmp_name' => $tmp_name,
          'path' => $path,
          'out' => $out
          );
      if(!is_array($_POST['params']))
        $_POST['params'] = array();

      foreach($_POST['params'] as $k => $v){
        if($v==='on') $_POST['params'][$k] = 1;
        else $_POST['params'][$k] = 0;
      }

      $params = array_merge($_POST['params'],$params);
      $params = json_encode($params);
      $cipher = bin2hex(cbc_encrypt($params));
      $password = escapeshellarg(substr($_POST['password'],0,16));

      $cmd = "(printf $password; echo)| LD_LIBRARY_PATH=/usr/local/lib ".BINARY_FILES_PATH." upload $cipher;";
      $shell = shell_exec($cmd);

      if($shell == "") {
        if($_SESSION['perm'] > 0){ # for pre-purchase user only.
          $statement = $db->prepare('INSERT INTO files VALUES(NULL, :u, :f, datetime(\'now\'))');
          $statement->bindValue(':u', $_SESSION['username']);
          $statement->bindValue(':f', $path.'/'.$out);
          $result = $statement->execute();
        }
        if($_POST['params']['sharing']){
          echo "<p>Here is the link: ".$path.'/'.$out.'</p>';
        }
        echo '<div class="alert alert-success" role="alert">File uploaded successfully</div>';
      } else {
        echo "<pre>$shell</pre>";
        echo '<div class="alert alert-danger" role="alert">File uploaded unsuccessfully</div>';
      }
    }
  }
  echo "</form></div>";
}

function get_password(){
  if(empty($_GET['pin']) || !is_pin($_GET['pin']) || empty($_GET['i']) || !is_numeric($_GET['i']))
    die(header("Location: index.php"));

  $db = sqlite();
  $id = md5($_SESSION['userid'].$_SESSION['username']);
  $statement = $db->prepare('SELECT * FROM passwords WHERE id = :id');
  $statement->bindValue(':id', $id);
  $result = $statement->execute();
  $data = $result->fetchArray()['data'];

  if($data){
    $data = json_decode($data,true);

    $p = $data['password_encrypted'][$_GET['i'] - 1];
    if($p){
      $p = "get ".bin2hex(json_encode(array("p" => $_GET['pin'],"password_encrypted" => $p)));
      $p = escapeshellarg($p);
      $cmd = "(printf $p; echo) | nc localhost 8081"; // Store password service is listening on 8081
      $shell = trim(shell_exec($cmd));

      if($shell != ""){
        $out = json_decode($shell,true);
        if($out)
          echo '<div class="form-group"><label>Here is your password: <input type="text" value="'.$out['password'].'"></label></div>';

      } else {
        echo '<div class="alert alert-danger" role="alert">Wrong PIN</div>';
      }
    } else {
      echo '<div class="alert alert-danger" role="alert">Wrong ID</div>';
    }

  } else {
    echo '<div class="alert alert-danger" role="alert">There is no password.</div>';
  }

}


function array_encode_hex($dat)
{
    if (is_string($dat))
        return bin2hex($dat);
    if (!is_array($dat))
        return $dat;
    $ret = array();
    foreach ($dat as $i => $d)
        $ret[$i] = array_encode_hex($d);
    return $ret;
}

function password(){

  $db = sqlite();
  $id = md5($_SESSION['userid'].$_SESSION['username']);
  $statement = $db->prepare('SELECT * FROM passwords WHERE id = :id');
  $statement->bindValue(':id', $id);
  $result = $statement->execute();
  $data = $result->fetchArray()['data'];


  if($data){
    $data = json_decode($data,true);
  }

  echo <<<EOF
  <div class="col-sm-6">
  <form action="?p=password" method="post">
    <h2 class="form-signin-heading">Save the password, save the soul</h2>
    <div class="form-group">
EOF;
  for($i = 1; $i <= 5; $i++){
    $a = "";
    $p = "";
    if($data && strlen($data['alias'][$i-1]) > 0){
      $a = $data['alias'][$i-1];
      if(strlen($data['password_encrypted'][$i-1])>0){
        $p = $data['password_encrypted'][$i-1];
      }
    }
    echo <<<EOF
    <label for="inputpassword{$i}">
      <input type="text" name="alias[]" value="$a" placeholder="Alias $i">
      <input type="password" name="password[]" value="$p" placeholder="Password $i">
      <span class="btn btn-success" onclick='var pin=prompt("Enter your PIN");document.location.href="?p=get_password&pin="+pin+"&i="+$i;'>GET</span>
    </label>
EOF;
  }
  echo <<<EOF
    <button class="btn btn-lg btn-primary btn-block" type="submit">Save</button>
    </div>
EOF;


  if(!empty($_POST['alias']) && count($_POST['alias']) <= 5  // max = 5 passwords
    && !empty($_POST['password']) && count($_POST['password']) <= 5){

    $alias = $_POST['alias'];
    $pwds = $_POST['password'];

    $statement = $db->prepare('SELECT pin FROM users WHERE id = :id');
    $statement->bindValue(':id', $_SESSION['userid']);
    $result = $statement->execute();
    $pin = $result->fetchArray()['pin'];

    $pwds = array_encode_hex($pwds); // Since password may include utf-8, we should hex encode it.

    $p = json_encode(array("p"=>$pin,"passwords"=>$pwds,"alias"=>$alias));

    $p = escapeshellarg("set ".bin2hex($p));

    $cmd = "(printf $p; echo) | nc localhost 8081"; // Store password service is listening on 8081
    $shell = trim(shell_exec($cmd));

    $out = json_decode($shell,true);

    if($out['alias'] && $out['password_encrypted']){
      if($data){
        $statement = $db->prepare('UPDATE passwords SET data=:data WHERE id=:id');
      } else {
        $statement = $db->prepare('INSERT INTO passwords VALUES(:id, :data)');
      }
      $statement->bindValue(':id', $id);
      $statement->bindValue(':data', $shell);
      $result = $statement->execute();

      if($result){
        echo '<div class="alert alert-success" role="alert">Save passwords successfully</div>';
      }
    }

  }
}




?>