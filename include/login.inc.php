<?php
if(!IN_CHAT) die();
header('Cache-Control: no-cache, no-store, max-age=0, private, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
$db->delete('ipban', '`lastfailtime`<='.($time-$config['ban']['bantime']));
if(!$user && isset($_POST['username'], $_POST['password'])){
	$ipban=$db->get_one('SELECT * FROM `ipban` WHERE ip=\''.$_SERVER['REMOTE_ADDR'].'\' AND `lastfailtime`>'.($time-$config['ban']['bantime']).';');
	if($ipban['count']<$config['ban']['failcount'] && $query=$db->get_one('SELECT * FROM `user` WHERE `username`=\''.mysql_real_escape_string($_POST['username']).'\' AND `password`=\''.mysql_real_escape_string($_POST['password']).'\' AND `action`=1;')){
		$db->delete('online_user', '`uid`=\''.$query['uid'].'\'');
		$db->insert('online_user', array(
			'sid' => hash('crc32', $query['uid'].'_'.$_SERVER['REMOTE_ADDR'].'_'.$time),
			'uid' => $query['uid'],
			'channel' => 1,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'logintime' => $time,
			'lastmsgid' => 0,
			'lastactiontime' => $time,
			'lastaction' => 'login'
		));
		$sid=$db->get_one('SELECT `sid` FROM `online_user` WHERE `uid`='.$query['uid'].';');
		$sid=$sid['sid'];
		$user=array(
			'uid' => $query['uid'],
			'sid' => $sid,
			'username' => $_POST['username'],
			'channel' => 1,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'lastmsgid' => 0
		);
		setcookie('uid', $query['uid'], $time+$config['loginexpire']);
		setcookie('sid', $sid, $time+$config['loginexpire']);
		$db->insert('chatlog', array(
			'time' => time(),
			'fromuid' => $user['uid'],
			'fromusername' => $_POST['username'],
			'type' => 3,
			'message' => str_replace(array("\r", "\n"), array('', ''), $_POST['username'].' 登入了聊天室')
		));
		Cache::set('lastmsgid',$db->insert_id());
		echo 'logined';
	}else{
		if($ipban && $ipban['count']<$config['ban']['failcount']){
			$db->update('ipban', array(
				'count' => $ipban['count']+1,
				'lastfailtime' => $time
			),'ip=\''.$_SERVER['REMOTE_ADDR'].'\'');
		}else if(!$ipban){
			$db->insert('ipban', array(
				'ip' => $_SERVER['REMOTE_ADDR'],
				'count' => 1,
				'lastfailtime' => $time
			));
		}
		echo 'failed';
	}
}else if($user){
	echo 'logined';
}
?>