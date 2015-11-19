<?php
if(!IN_CHAT || !$user) die();
if(!in_array($user['uid'],$config['admin'])) die('越權存取！');

$splitc=explode(' ',$_POST['command']);
$command=array();
$inqmark=false;
$arglen=-1;
foreach($splitc as $v){
	if($inqmark){
		if(substr($v,-1,1)=='"' && substr($v,-2,1)!='\\'){
			$command[$arglen].=' '.substr($v,0,-1);
			$command[$arglen]=str_replace(array('\\"','\\\\'),array('"','\\'),$command[$arglen]);
			$inqmark=false;
		}else{
			$command[$arglen].=' '.$v;
		}
	}else if(substr($v,0,1) === '"'){
		if(substr($v,-1,1)=='"' && substr($v,-2,1)!='\\'){
			$command[]=str_replace(array('\\"','\\\\'),array('"','\\'),substr($v,1,-1));
		}else{
			$inqmark=true;
			$command[]=substr($v,1);
		}
		$arglen++;
	}else{
		$command[]=$v;
		$arglen++;
	}
}
if($inqmark) die('指令解碼失敗，請檢查語法');

switch($command[0]){
	case 'listbanip':
		echo '<br>=====當前IP封鎖清單=====<br>';
		$q=$db->query('SELECT `ip`,`lastfailtime` FROM `ipban` WHERE `count`>=5 AND `lastfailtime`>'.($time-$config['ban']['bantime']).(isset($command[1])? ' AND `ip`=\''.mysql_real_escape_string($command[1]).'\'':'').';');
		while($i=mysql_fetch_array($q)){
			echo $i['ip'].' 到期時間: '.date('Y/m/d A h:i:s',$i['lastfailtime']+$config['ban']['bantime']).'<br>';
		}
		echo '=====當前IP封鎖清單=====';
	break;
	case 'banip':
		if(isset($command[1])){
			$bantime=isset($command[2])? $time-$config['ban']['bantime']+$command[2]:$time;
			if($db->insert('ipban', array(
				'ip' => $command[1],
				'count' => 100,
				'lastfailtime' => $bantime
			))){
				echo '已封鎖IP '.$command[1].' 至 '.date('Y/m/d A h:i:s',$bantime+$config['ban']['bantime']);
				$q=$db->query('SELECT `user`.`username` FROM `online_user`,`user` WHERE `online_user`.`ip`=\''.mysql_real_escape_string($command[1]).'\' AND `online_user`.`uid`=`user`.`uid`;');
				while($i=mysql_fetch_array($q)){
					echo '關聯在線帳號 '.$i['username'].' 已被踢下線';
				}
				$db->update('online_user',array('lastaction'=>'kick'),'`online_user`.`ip`=\''.mysql_real_escape_string($command[1]).'\'');
			}else{
				echo '操作失敗';
			}
		}else
			echo '缺少參數 IP位址';
	break;
	case 'unbanip':
		if(isset($command[1])){
			if($db->delete('ipban', '`ip`=\''.mysql_real_escape_string($command[1]).'\' OR `lastfailtime`<='.($time-$config['ban']['bantime']))){
				echo '解除成功';
			}else{
				echo '解除失敗';
			}
		}else
			echo '缺少參數 IP位址';
	break;
	case 'listbanuser':
		echo '<br>=====當前帳號封鎖清單=====<br>';
		$q=$db->query('SELECT `username`,`regtime` FROM `user` WHERE `action`=0'.(isset($command[1])? ' AND `username`=\''.mysql_real_escape_string($command[1]).'\'':'').';');
		while($i=mysql_fetch_array($q)){
			echo $i['username'].' 註冊日期: '.date('Y/m/d A h:i:s',$i['regtime']).'<br>';
		}
		echo '=====當前帳號封鎖清單=====';
	break;
	case 'banuser':
		if(isset($command[1])){
			if($db->update('user', array(
				'action' => 0
			),'`username`=\''.mysql_real_escape_string($command[1]).'\'')){
				echo '已封鎖帳號 '.$command[1];
			}else{
				echo '操作失敗';
			}
		}else
			echo '缺少參數 帳號';
	break;
	case 'unbanuser':
		if(isset($command[1])){
			if($db->update('user', array(
				'action' => 1
			),'`username`=\''.mysql_real_escape_string($command[1]).'\'')){
				echo '已解除封鎖帳號 '.$command[1];
			}else{
				echo '操作失敗';
			}
		}else
			echo '缺少參數 帳號';
	break;
	case 'kick':
		//未實作
	break;
	case 'kickall':
		//未實作
	break;
	case 'smsg':
		if(isset($command[1]) && preg_match('/^[\x00-\xff]+$/', $command[1])){
			update_actiontime();
			$db->insert('chatlog', array(
				'time' => $time,
				'fromuid' => $user['uid'],
				'fromusername' => $user['username'],
				'type' => 3,
				'message' => str_replace(array("\r", "\n"), array('', ''), $command[1])
			));
			Cache::set('lastmsgid',$db->insert_id());
			echo 'OK';
		}else{
			echo 'Fail';
		}
	break;
	case 'addroom':
		if(isset($command[1])){
			if($db->insert('channel', array('name' => $command[1]))){
				echo '新增頻道 '.$command[1];
			}else{
				echo '操作失敗';
			}
		}else
			echo '缺少參數 頻道名稱';
	break;
	case 'editroom':
		//未實作
	break;
	case 'delroom':
		if(isset($command[1])){
			if($db->delete('channel', '`name`=\''.mysql_real_escape_string($command[1]).'\' AND `cid`!=1')){
				echo '刪除頻道 '.$command[1];
			}else{
				echo '刪除失敗';
			}
		}else
			echo '缺少參數 頻道名稱';
	break;
	case 'mvuser':
		//未實作
	break;
	case 'mvroomuser':
		//未實作
	break;
	case 'mvalluser':
		//未實作
	break;
	case 'help':
		echo <<<EOF

=====指令清單=====
listbanip [IP位址] : 查詢/列出IP封鎖清單
banip <IP位址> [持續秒數] : 封鎖1個IP，預設封鎖1天
unbanip <IP位址> : 解封1個IP
listbanuser [帳號] : 查詢/列出帳號封鎖清單
banuser <帳號> : 封鎖1個帳號
unbanuser <帳號> : 解封1個帳號
kick <帳號> : 踢除指定帳號下線
kickall : 將所有人踢下線
smsg <訊息內容> : 伺服器廣播
addroom <頻道名稱> : 新增頻道
editroom <舊頻道名稱> <新頻道名稱> : 頻道更名
delroom <頻道名稱> : 刪除頻道
mvuser <移至頻道> <目標帳號1> [目標帳號2] ... : 移動帳號至指定頻道
mvroomuser <從頻道> <至頻道> : 移動整個頻道的帳號至指定頻道
mvalluser <移至頻道> : 移動所有帳號至指定頻道
EOF;
	break;
	default:
		echo '未知命令，請嘗試使用 help 進行查詢';
}
?>
