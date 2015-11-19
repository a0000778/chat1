<?php
if(!IN_CHAT) die();

$config=array();
$config['dbhost']='localhost';
$config['dbuser']='nchat';
$config['dbpass']='';
$config['dbname']='nchat';

$config['loginexpire']=86400;//登入有效時間

$config['admin']=explode(',','1');//管理員uid
$config['weburl']='http://localhost/';//網站網址(含/)
$config['email']='yourmail@localhost';//管理員E-mail

$config['ban']=array();
$config['ban']['bantime']=86400;//持續ban時間
$config['ban']['failcount']=5;//登入失敗次數

$config['cache']=array();
$config['cache']['prefix']='f2676585_';
$config['cache']['Memcached']=array();
$config['cache']['Memcached']['servers'][]=array('localhost',11211);
?>
