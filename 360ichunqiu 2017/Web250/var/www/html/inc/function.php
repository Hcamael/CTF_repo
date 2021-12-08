<?php

function addslashes_deep($value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}

function saveConfig($config){
   global $conn;
   foreach ($config as $key => $value) {
      $key = addslashes_deep($key);
      $strsql="select db_value from config where db_name='$key' limit 1"; 
      $result=mysql_query($strsql,$conn);
      $row = mysql_fetch_array($result);
      if(empty($row))
      {
         $strsql="insert into config(db_name,db_value) values('$key','$value')"; 
         $result=mysql_query($strsql,$conn);
      }else{
         $strsql="update config set db_value='$value' where db_name='$key'"; 
         $result=mysql_query($strsql,$conn);
      }
   }
}

function getConfig($key){
   global $conn;
   $strsql="select db_value from config where db_name='$key' limit 1"; 
   $result=mysql_query($strsql,$conn);
   $row = mysql_fetch_array($result);
   if(!empty($row))
   {
     return $row['db_value'];
   }
   return "";
}


function CheckLogin()
{
  if(!isset($_SESSION["username"]) || empty($_SESSION["username"]))
  {
      header("location:login.php");
      exit();
  }
  
}



?>