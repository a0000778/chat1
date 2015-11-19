<?php
if(!IN_CHAT || !$user) die();
header('Cache-Control: max-age=600, public, must-revalidate');
header('Expires: '.gmdate ("D, d M Y H:i:s", $time+600).' GMT');
switch($_GET['do']){
	case 'list':
		echo '<ul>';
		$q=$db->query('SELECT * FROM `channel`;');
		while($i=mysql_fetch_array($q)){
			echo '<li onClick="system.gotochannel('.$i['cid'].',\''.str_replace(array('\\', '\''), array('\\\\', '\\\''), htmlspecialchars($i['name'])).'\');switch_channellist();">'.htmlspecialchars($i['name']).'</li>';
		}
		echo '</ul>';
	break;
	case 'goto':
		if($q=$db->get_one('SELECT * FROM `channel` WHERE `cid`='.mysql_real_escape_string($_GET['cid']).';')){
			$db->insert('chatlog', array(
				'time' => time(),
				'fromuid' => $user['uid'],
				'fromusername' => $_POST['username'],
				'type' => 3,
				'message' => str_replace(array("\r", "\n"), array('', ''), htmlspecialchars($user['username']).' 進入頻道 '.htmlspecialchars($q['name']))
			));
			Cache::set('lastmsgid',$db->insert_id());
			$db->update('online_user',array('channel'=>$_GET['cid']),'`sid`=\''.$user['sid'].'\' AND `uid`='.$user['uid']);
		}
	break;
}
?>
