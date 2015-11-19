<?php
if(!IN_CHAT) die();
header('Cache-Control: no-cache, no-store, max-age=0, private, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
if(isset($_GET['do'],$_GET['code']) && $_GET['do']=='mail'){
	if($d=$db->get_one('SELECT * FROM `mailuser` WHERE `hash`=\''.mysql_real_escape_string($_GET['code']).'\' AND `regtime`>='.($time-86400).';')){
		$db->insert('user', array(
			'username' => $d['username'],
			'password' => $d['password'],
			'email' => $d['email'],
			'action' => 1,
			'regtime' => $d['regtime']
		));
		$db->delete('mailuser','`hash`=\''.mysql_real_escape_string($_GET['code']).'\'');
		echo '<script language="javascript">
alert(\'註冊成功！\');
location.href=\'index.html\';
</script>';
	}else{
		echo '<script language="javascript">
alert(\'該驗證信不存在！\');
location.href=\'index.html\';
</script>';
	}
	$db->delete('mailuser', '`regtime`<='.($time-86400));
	die();
}
if(!$user){
	$u1=$db->get_one('SELECT * FROM `user` WHERE `username`=\''.mysql_real_escape_string($_POST['username']).'\' OR `email`=\''.mysql_real_escape_string($_POST['email']).'\';');
	$u2=$db->get_one('SELECT * FROM `mailuser` WHERE `username`=\''.mysql_real_escape_string($_POST['username']).'\' OR `email`=\''.mysql_real_escape_string($_POST['email']).'\';');
	if(!$u1 && !$u2){
		$hash=md5(md5($_POST['password1'].time()).$_POST['username']);
		$db->insert('mailuser', array(
			'hash' => $hash,
			'username' => $_POST['username'],
			'password' => $_POST['password'],
			'email' => $_POST['email'],
			'regtime' => $time
		));
		mail($_POST['email'],'聊天室驗證信',
		'聊天室驗證信'."\r\n".
		'請前往以下網址啟用帳號'."\r\n".
		$config['weburl'].'server.php?action=register&do=mail&code='.$hash,
		'From: '.$config['email']);
		echo 'regok';
	}else{
		echo 'exist';
	}
}else{
	echo 'logined';
}
?>