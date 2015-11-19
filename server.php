<?php
require('./include/core.inc.php');

if(count($_GET) || count($_POST)){
	$guest_actions=array('login','register');
	$user_actions=array('login','chat','logout','onlinelist','channel','admin');
	if(($user && in_array($_GET['action'], $user_actions)) || (!$user && in_array($_GET['action'], $guest_actions))){
		require('./include/'.$_GET['action'].'.inc.php');
	}else if($_GET['action']=='chicklogin' && $user){
		die('logined');
	}else{
		die('越權存取！');
	}
	die('');
}else{
	if(!$user) die('越權存取！');
}

require './include/server.inc.php';
SERVER::init();
while(true){
	SERVER::updatemsg();
	SERVER::output();
	usleep(500000);
}
?>
