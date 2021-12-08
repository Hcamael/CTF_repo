<?php


define("FILES_PATH",		"/var/www/html/tcloud/admin/files/");
define("SECRET_PATH",		"/home/webin/secret");
define("BINARY_FILES_PATH",	"/home/webin/webin");
define("DB_PATH",			"/var/www/db/sql.db");

function cbc_encrypt($data, $algo='aes-256-cbc')
{
  global $key,$iv;
  return openssl_encrypt($data,$algo,$key,OPENSSL_RAW_DATA,$iv);
}

function is_pin($p){
	if(preg_match('#[0-9a-z]{6}#',$p))
		return TRUE;
	return FALSE;
}

function sqlite(){
	return $db = new SQLite3(DB_PATH);
}

# CREATE TABLE users(id INTEGER PRIMARY KEY AUTOINCREMENT,username varchar(24) UNIQUE,password varchar(32),pin varchar(8),perm INT);
# CREATE TABLE files(id INTEGER PRIMARY KEY AUTOINCREMENT,username varchar(24),path varchar(256),timestamp DATETIME);
# CREATE TABLE passwords(id varchar(32) UNIQUE,data text);

?>
