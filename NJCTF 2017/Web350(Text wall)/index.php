<?php
//The flag is /var/www/PnK76P1IDfY5KrwsJrh1pL3c6XJ3fj7E_fl4g
$lists = [];
Class filelist{
    public function __toString()
    {
        return highlight_file('hiehiehie.txt', true).highlight_file($this->source, true);
    }
}
if(isset($_COOKIE['lists'])){
    $cookie = $_COOKIE['lists'];
    $hash = substr($cookie, 0, 40);
    $sha1 = substr($cookie, 40);
    if(sha1($sha1) === $hash){
        $lists = unserialize($sha1);
    }
}
if(isset($_POST['hiehiehie'])){
    $info = $_POST['hiehiehie'];
    $lists[] = $info;
    $sha1 = serialize($lists);
    $hash = sha1($sha1);
    setcookie('lists', $hash.$sha1);
    header('Location: '.$_SERVER['REQUEST_URI']);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Please Get Flag!!</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://apps.bdimg.com/libs/bootstrap/3.3.0/css/bootstrap.min.css">  
  <script src="http://apps.bdimg.com/libs/jquery/2.1.1/jquery.min.js"></script>
  <script src="http://apps.bdimg.com/libs/bootstrap/3.3.0/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <div class="jumbotron">
        <h1>Please Get Flag!!</h1>
    </div>
    <div class="row">
        <?php foreach($lists as $info):?>
            <div class="col-sm-4">
              <h3><?=$info?></h3>
            </div>
        <?php endforeach;?>
    </div>
    <form method="post" href=".">
        <input name="hiehiehie" value="hiehiehie">
        <input type="submit" value="submit">
    </form>
</div>
</body>
</html>
