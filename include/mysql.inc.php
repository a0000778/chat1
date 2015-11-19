<?php
if(!IN_CHAT) die();

class mysql{
	public $dblink=false;
	function init($host, $user, $pass, $name){
		if($dblink) return true;
		if($this->dblink=mysql_connect($host, $user, $pass)){
			$this->query('SET NAMES UTF8;');
			if(mysql_select_db($name, $this->dblink))
				return true;
		}
		$this->dblink=false;
		return false;
	}
	
	function query($sql){
		return mysql_query($sql, $this->dblink);
	}
	
	function get_one($sql){
		return mysql_fetch_array($this->query($sql));
	}
	
	function insert($table, $data=array()){
		$p='';
		$d='';
		$s='';
		foreach($data as $k => $v){
			$p.=$s.'`'.$k.'`';
			$d.=$s.'"'.mysql_real_escape_string($v).'"';
			$s=', ';
		}
		return $this->query('INSERT INTO `'.$table.'` ('.$p.') VALUES ('.$d.');');
	}
	
	function insert_id(){
		return mysql_insert_id($this->dblink);
	}
	
	function update($table, $data=array(), $where){
		$d='';
		$s='';
		foreach($data as $k => $v){
			$d.=$s.'`'.$k.'` = "'.mysql_real_escape_string($v).'"';
			$s=', ';
		}
		return $this->query('UPDATE `'.$table.'` SET '.$d.' WHERE '.$where.';');
	}
	
	public function delete($table, $where){
		return mysql_query('DELETE FROM `'.$table.'` WHERE '.$where.';');
	}
}
?>