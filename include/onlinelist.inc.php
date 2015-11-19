<?php
if(!IN_CHAT || !$user) die();
header('Cache-Control: no-cache, max-age=30, public, must-revalidate');
header('Expires: '.gmdate ("D, d M Y H:i:s", $time+30).' GMT');
if(isset($_GET['list']) && $_GET['list']==1){
	return get_online_user();
}else{
	$ol=get_online_user(true);
	$qtime=$time-300;
	echo count($ol).'|當前頻道：<ul>';
	$ocl=array();
	foreach($ol as $i){
		if($i['cid']!=$user['channel']){
			$ocl[]=$i;
		}else{
			echo '<li onClick="chat.setpmsgto('.$i['uid'].',\''.str_replace(array('\\', '\''), array('\\\\', '\\\''), htmlspecialchars($i['username'])).'\')">'.$i['username'].($i['lastactiontime']<$qtime ? '[閒置]':'').'</li>';
		}
	}
	echo '</ul>其他頻道：<ul>';
	foreach($ocl as $i){
		echo '<li onClick="chat.setpmsgto('.$i['uid'].',\''.str_replace(array('\\', '\''), array('\\\\', '\\\''), htmlspecialchars($i['username'])).'\')">'.$i['username'].($i['lastactiontime']<$qtime ? '[閒置]':'').'</li>';
	}
	echo '</ul>';
}
?>