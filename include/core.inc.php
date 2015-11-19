<?php
define('IN_CHAT', true);
require './config.php';
require './include/mysql.inc.php';
require './include/cache.class.php';
$time=time();
date_default_timezone_set('Asia/Taipei');
header("Content-type: text/html; charset=utf-8");
//取得PHP設定資料
$_PHP=array();
$_PHP['magic_quotes_qpc']=get_magic_quotes_gpc();
//PHP環境處理
if($_PHP['magic_quotes_qpc']){
	foreach($_GET as $k => $v){
		$_GET[$k]=stripslashes($v);
	}
	foreach($_POST as $k => $v){
		$_POST[$k]=stripslashes($v);
	}
}

if(!Cache::init($config['cache']))
	die('快取讀取失敗');

$db=new mysql;
if(!$db->init($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']))
	die('資料庫連線失敗');

$user=false;
if(isset($_COOKIE['uid']) && isset($_COOKIE['sid'])){
	if($query=$db->get_one('SELECT `online_user`.*, `user`.* FROM `online_user`, `user` WHERE `online_user`.`sid`=\''.mysql_real_escape_string($_COOKIE['sid']).'\' AND `online_user`.`uid`=`user`.`uid`;')){
		if($query['lastactiontime'] <= $time+$config['loginexpire'] && $query['uid'] && $query['uid'] == $_COOKIE['uid'] && $_SERVER['REMOTE_ADDR'] == $query['ip']){
			$user=array(
				'uid' => $query['uid'],
				'sid' => $query['sid'],
				'username' => $query['username'],
				'channel' => $query['channel'],
				'ip' => $query['ip'],
				'lastmsgid' => $query['lastmsgid']
			);
			setcookie('uid', $_COOKIE['uid'], $time+$config['loginexpire']);
			setcookie('sid', $_COOKIE['sid'], $time+$config['loginexpire']);
			$db->query('UPDATE FROM `online_user` SET `lastactiontime`='.$time.' WHERE `sid`=\''.$query['sid'].'\';');
		}
	}
}

function update_actiontime(){
	global $db;
	global $user;
	$db->update('online_user', array('lastactiontime' => time()), '`sid`=\''.$user['sid'].'\'');
}

function get_online_user($list=false){
	global $db;
	if(!$list){
		$query=$db->query('SELECT count(*) FROM `online_user`;');
		list($r)=mysql_fetch_row($query);
	}else{
		$query=$db->query('SELECT `online_user`.`uid`,`user`.`username`,`online_user`.`channel` AS "cid",`channel`.`name` AS "cname",`online_user`.`lastactiontime` FROM `online_user`,`user`,`channel` WHERE `online_user`.`uid`=`user`.`uid` AND `online_user`.`channel`=`channel`.`cid`;');
		$r=array();
		while($i=mysql_fetch_array($query)){
			$r[$i['uid']]=$i;
		}
	}
	return $r;
}
?>
