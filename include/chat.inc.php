<?php
if(!IN_CHAT || !$user) die();
header('Cache-Control: no-cache, no-store, max-age=0, private, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
switch($_POST['do']){
	case 'send'://一般發言
		if(isset($_POST['message']) && preg_match('/^[\x00-\xff]+$/', $_POST['message'])){
			update_actiontime();
			$db->insert('chatlog', array(
				'time' => $time,
				'fromuid' => $user['uid'],
				'fromusername' => $user['username'],
				'channel' => $user['channel'],
				'type' => 0,
				'message' => str_replace(array("\r", "\n"), array('', ''), $_POST['message'])
			));
			Cache::set('lastmsgid',$db->insert_id());
			echo 'OK';
		}else{
			echo 'Fail';
		}
	break;
	case 'psend'://密頻發言
		update_actiontime();
		if(isset($_POST['message'],$_POST['touid']) && preg_match('/^[\x00-\xff]+$/', $_POST['message']) && preg_match('/^[0-9]+$/',$_POST['touid'])){
			if($toname=$db->get_one('SELECT `user`.`username` FROM `online_user`,`user` WHERE `online_user`.`uid`=\''.mysql_real_escape_string($_POST['touid']).'\' AND `online_user`.`uid`=`user`.`uid`;')){
				update_actiontime();
				$db->insert('chatlog', array(
					'time' => $time,
					'fromuid' => $user['uid'],
					'fromusername' => $user['username'],
					'touid' => $_POST['touid'],
					'tousername' => $toname['username'],
					'type' => 1,
					'message' => str_replace(array("\r", "\n"), array('', ''), $_POST['message'])
				));
				Cache::set('lastmsgid',$db->insert_id());
				echo 'OK';
			}else{
				echo 'Offline';
			}
		}else{
			echo 'Fail';
		}
	break;
}
?>
