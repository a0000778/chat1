<?php
if(!IN_CHAT || !$user) die();

if($db->delete('online_user', '`sid`=\''.$user['sid'].'\'')){
	setcookie('uid', '', 0);
	setcookie('sid', '', 0);
	$db->insert('chatlog', array(
		'time' => time(),
		'fromuid' => $user['uid'],
		'fromusername' => $user['username'],
		'type' => 3,
		'message' => str_replace(array("\r", "\n"), array('', ''), $user['username'].' 登出了聊天室')
	));
	Cache::set('lastmsgid',$db->insert_id());
	$user=false;
	echo 'logouted';
	ob_flush();
}
?>
