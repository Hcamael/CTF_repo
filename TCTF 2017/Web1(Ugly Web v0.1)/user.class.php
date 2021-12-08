<?php
class User{
	var $dbTable  = 'users';
	var $sessionVariable = 'userSessionValue';
	var $tbFields = array(
		'userID'=> 'userID', 
		'login' => 'username',
		'pass'  => 'password',
		'email' => 'email',
		'active'=> 'active'
	);
	
	var $displayErrors = false;
	var $userID;
	var $userData=array();

	var $remTime = 2592000;
	var $remCookieName = 'ckSavePass';
	var $remCookieDomain = '';

	function __construct() {
		global $mysqli;
		if( !isset( $_SESSION ) ) session_start();
		$this->dbConn = $mysqli;
		if ( !empty($_SESSION[$this->sessionVariable]) )
		{
			$this->loadUser( $_SESSION[$this->sessionVariable] );
		}
		if ( isset($_COOKIE[$this->remCookieName]) && !$this->is_loaded()){
			$u = unserialize(base64_decode($_COOKIE[$this->remCookieName]));
			$this->login($u['email'], $u['password']);
		}
	}

	function login($email, $password, $remember = false, $loadUser = true)
	{
		$email	= $this->escape($email);
		$originalPassword = $password;
		$password = md5($password);
		$res = $this->query("SELECT * FROM `{$this->dbTable}` 
		WHERE `{$this->tbFields['email']}` = '$email' AND `{$this->tbFields['pass']}` = '$password' LIMIT 1",__LINE__);
		if ( $res->num_rows == 0)
			return false;
		if ( $loadUser )
		{
			$this->userData = $res->fetch_array();
			$this->userID = $this->userData[$this->tbFields['userID']];
			$_SESSION[$this->sessionVariable] = $this->userID;
		}
		if ( $remember ){
			$cookie = base64_encode(serialize(array('email'=>$email,'password'=>$originalPassword)));
			$a = setcookie($this->remCookieName, 
			$cookie,time()+$this->remTime, $base_path, $this->remCookieDomain, false, true);
		}
		return true;
	}

	function logout($redirectTo = '')
	{
		$_SESSION[$this->sessionVariable] = '';
		$this->userData = '';
		if ( $redirectTo != '' && !headers_sent()){
			header('Location: '.$redirectTo );
			exit;//To ensure security
		}
	}

	function is($prop){
		return $this->get_property($prop)==1?true:false;
	}

	function get_property($property)
	{
		if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
		if (!isset($this->userData[$property])) $this->error('Unknown property <b>'.$property.'</b>', __LINE__);
		return $this->userData[$property];
	}

	function is_active()
	{
		return $this->userData[$this->tbFields['active']];
	}

	function is_loaded()
	{
		return empty($this->userID) ? false : true;
	}

	function activate()
	{
		if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
		if ( $this->is_active()) $this->error('Allready active account', __LINE__);
		$res = $this->query("UPDATE `{$this->dbTable}` SET {$this->tbFields['active']} = 1 AND `activationHash`=''
		WHERE `{$this->tbFields['userID']}` = '".$this->escape($this->userID)."' LIMIT 1");
		if ($res->affected_rows == 1)
		{
			$this->userData[$this->tbFields['active']] = true;
			return true;
		}
		return false;
	}

	function insertUser($data){
		if (!is_array($data)) $this->error('Data is not an array', __LINE__);
		$data[$this->tbFields['pass']] = md5($data[$this->tbFields['pass']]);
		foreach ($data as $k => $v ) $data[$k] = "'".$this->escape($v)."'";
		$this->query("INSERT INTO `{$this->dbTable}` (`".implode('`, `', array_keys($data))."`) VALUES (".implode(", ", $data).")");
		return $this->dbConn->insert_id;
	}

	function randomPass($length=10, $chrs = '1234567890qwertyuiopasdfghjklzxcvbnm'){
		for($i = 0; $i < $length; $i++) {
			$pwd .= $chrs{mt_rand(0, strlen($chrs)-1)};
		}
		return $pwd;
	}

	function query($sql, $line = 'Uknown')
	{
		$res = $this->dbConn->query($sql);
		if ( !$res )
			$this->error($this->dbConn->error, $line);
		return $res;
	}

	function loadUser($userID)
	{
		$res = $this->query("SELECT * FROM `{$this->dbTable}` WHERE `{$this->tbFields['userID']}` = '".$this->escape($userID)."' LIMIT 1");
		if ( $res->num_rows == 0 )
			return false;
		$this->userData = $res->fetch_array();
		$this->userID = $userID;
		$_SESSION[$this->sessionVariable] = $this->userID;
		return true;
	}

	function findUser($username)
	{
		$res = $this->query("SELECT * FROM `{$this->dbTable}` WHERE `{$this->tbFields['login']}` = '".$this->escape($username)."' LIMIT 1");
		if ( $res->num_rows == 0 )
			return false;
		return $res->fetch_array()['userID'];
	}

	function escape($str)
	{
		if (is_array($str))
		{
			$str = array_map([&$this, 'escape'], $str);
			return $str;
		}
		else if (is_string($str))
		{
			return $this->dbConn->real_escape_string($str);
		}
		else if (is_bool($str))
		{
			return ($str === false) ? 0 : 1;
		}
		else if ($str === null)
		{
			return 'NULL';
		}
		return $str;
	}

	function error($error, $line = '', $die = false) {
		if ( $this->displayErrors )
			echo '<b>Error: </b>'.$error.'<br /><b>Line: </b>'.($line==''?'Unknown':$line).'<br />';
		if ($die) exit;
		return false;
	}
}