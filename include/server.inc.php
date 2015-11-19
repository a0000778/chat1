<?php
if(!IN_CHAT || !$user) die();
class SERVER{
	static public $max_count_script=1000;
	static public $max_interval_sec=25;
	static public $keep_connect_sec=10;
	static private $script=array();
	static private $count_script=0;
	static private $last_script_time=0;
	static private $last_chatmsg_id=0;
	
	static function init(){
		global $db,$user,$config;
		//變數檢查
		if(self::$max_count_script<100) echo '警告：max_interval_sec小於100';
		if(self::$max_interval_sec>55) echo '警告：max_interval_sec大於55';
		if(self::$keep_connect_sec>55) echo '警告：keep_connect_sec大於600';
		if(self::$keep_connect_sec<5) echo '警告：keep_connect_sec小於5';
		//初始化伺服器程序
		self::$last_script_time=time();
		set_time_limit(0);
		if($user['lastmsgid']==0){
			$d=$db->get_one('SELECT * FROM `chatlog` ORDER BY `messageid` DESC LIMIT 1;');
			self::$last_chatmsg_id=$d['messageid'];
		}else{
			self::$last_chatmsg_id=$user['lastmsgid'];
		}
		//初使化用戶端
		header('Cache-Control: no-cache, no-store, max-age=0, private, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		echo str_repeat(" ", 4096);//防止瀏覽器緩存
		echo '<script language="javascript">window.parent.system.server.keep('.(self::$keep_connect_sec+1).');</script>';
		if(in_array($user['uid'],$config['admin'])){
			echo '<script language="javascript">window.parent.$(\'chat_send_to\').$add(\'option\',{\'value\':\'admin\',\'innerHTML\':\'管理指令\'});</script>';
		}
		ob_flush();
		SERVER::givemsg(self::$last_script_time,'3', '連線成功!', '','');
		$q=$db->get_one('SELECT * FROM `channel` WHERE `cid`='.$user['channel'].';');
		self::$script[]=array('action'=>'script','code'=>'window.parent.$(\'channel\').innerHTML=\''.$q['name'].'\';');
	}
	
	static function updatemsg(){
		global $user,$db;
		$lastmsgid=Cache::get('lastmsgid');
		if(!$lastmsgid || self::$last_chatmsg_id>=$lastmsgid) return;
		if($q=$db->query('SELECT * FROM `chatlog` WHERE `messageid`>'.self::$last_chatmsg_id.' AND (`channel`='.$user['channel'].' OR `fromuid`='.$user['uid'].' OR `touid`='.$user['uid'].' OR `type` IN (2,3));')){
			while($d=mysql_fetch_array($q)){
				self::$last_chatmsg_id=$d['messageid'];
				SERVER::givemsg($d['time'],$d['type'], $d['message'], $d['fromuid'], $d['fromusername'], $d['tousername']);
			}
			$db->update('online_user',array('lastmsgid'=>self::$last_chatmsg_id),'`sid`=\''.$user['sid'].'\' AND `uid`='.$user['uid']);
		}
	}
	
	static function keepconnect($f=false){
		if(self::$last_script_time>time()+self::$max_interval_sec || $f){
			self::$script[]=array('action'=>'keepconnect');
		}
	}
	
	static function givemsg($time,$type, $msg, $fromuid, $fromname, $toname=''){
		if(!in_array($type,array(0,1,2,3))) return false;
		self::$script[]=array(
			'action' => 'msg',
			'time' => $time,
			'type' => $type,
			'fromuid' => ($type!=3)? $fromuid:'',
			'fromname' => ($type!=3)? str_replace(array('\\', '\''), array('\\\\', '\\\''), htmlspecialchars($fromname)):'',
			'toname' => ($type!=1)? '':str_replace(array('\\', '\''), array('\\\\', '\\\''), htmlspecialchars($toname)),
			'message' => str_replace(array('\\', '\''), array('\\\\', '\\\''), htmlspecialchars($msg))
		);
	}
	
	static function output(){
		global $db;
		$c=count(self::$script);
		$time=time();
		if((!$c) && ($keep=$time%self::$keep_connect_sec)) return;
		self::$count_script+=$c;
		echo '<script language="javascript">';
		foreach(self::$script as $i){
			switch($i['action']){
				case 'msg':
					echo 'window.parent.chat.givemsg(\''.$i['type'].'\',\''.$i['message'].'\',\''.$i['fromuid'].'\',\''.$i['fromname'].'\',\''.$i['toname'].'\','.$i['time'].');';
				break;
				case 'keepconnect':
					echo 'window.parent.system.server.keepconnect('.(self::$max_interval_sec*1000+3000).');';
				break;
				case 'script':
					echo $i['code'];
				break;
			}
		}
		if(self::$count_script>=self::$max_count_script){
			echo 'window.parent.system.server.clearserverscript();';
		}
		if(!$keep){
			echo 'window.parent.system.server.keep('.(self::$keep_connect_sec+3).');';
		}
		echo '</script>';
		ob_flush();
		self::$script=array();
		self::$last_script_time=$time;
		$db->update('online_user', array(
			'lastmsgid' => self::$last_chatmsg_id
		), '`uid`=\''.$user['uid'].'\'');
	}
}
?>