<?php
class Message{

	var $msg = "";
	var $from = "";
	var $to = "";
	var $id = -1;

	function __construct($from, $to, $msg, $id=-1) {
		global $mysqli;
		$this->from = $from;
		$this->to = $to;
		$this->msg = $msg;
		$this->id = $id;
	}

	function __toString(){
		return $this->msg;
	}

}

class MessageManager{
	function __construct() {
		global $mysqli;
		$this->dbConn = $mysqli;
	}

	function send($message){
		$sql = "INSERT INTO `message`(`from`, `to`, `msg`)VALUES('".$this->escape($message->from)."', '".$this->escape($message->to)."', '".$this->escape($message->msg)."')";
		$this->dbConn->query($sql);
		return $this->dbConn->insert_id;
	}

	function all($to){
		$sql = "SELECT * FROM `message` WHERE `read`=0 and `to`='".$this->escape($to)."'";
		$res = $this->dbConn->query($sql);
		$result = array();
		while($res && $message = $res->fetch_array()){
			$result[] = new Message($message['from'], $message['to'], $message['msg'], $message['id']);
		}
		return $result;

	}

	function one($to, $id){
		$sql = "SELECT * FROM `message` WHERE `read`=0 and `to`='".$this->escape($to)."' and `id`=".intval($id);
		$res = $this->dbConn->query($sql);
		$result = null;
		if($res && $message = $res->fetch_array()){
			$result = new Message($message['from'], $message['to'], $message['msg'], $message['id']);
		}
		return $result;

	}

	function read($id){
		$sql = "UPDATE `message` SET `read`=1 WHERE `id`=".intval($id);
		$res = $this->dbConn->query($sql);
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

}