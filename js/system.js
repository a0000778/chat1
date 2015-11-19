system={
	'server': {
		'doconnect': false,
		'connectobj': null,
		'timeoutfunc': null,
		'failcount': 0,
		'losecount': 0,
		'connect': function (){
			if(system.server.connectobj){
				system.server.connectobj.$del();
			}
			if(system.server.failcount>5){
				clearTimeout(system.server.timeoutfunc);
				chat.givemsg('3', '與伺服器連線失敗！', '');
				chat.givemsg('3', '請檢查網路連線是否發生問題，或伺服器正在維護中！', '');
				chat.givemsg('3', '※部分防毒軟體對網頁掃描緩存過大將導致連線失敗！', '');
				return;
			}
			if(!system.server.doconnect){
				console.log('Connected!exit!');
				return;
			}
			chat.givemsg('3', '嘗試連線中...', '');
			system.server.timeoutfunc=setTimeout(function(){
				console.log('連線超時');
				system.server.failcount++;
				system.server.connect();
			}, 3000);
			system.server.connectobj=$('serverconnect').$add('iframe', {'src': 'server.php','lastmsg': chat.lastmsgid});
		},
		'close': function (){
			if(system.server.connectobj){
				system.server.connectobj.$del();
				system.server.connectobj=false;
				system.server.failcount=0;
				clearTimeout(system.server.timeoutfunc);
			}
		},
		'keep': function(t){
			clearTimeout(system.server.timeoutfunc);
			system.server.timeoutfunc=setTimeout(function(){
				console.log('連線中斷');
				chat.givemsg('3', '與伺服器連線中斷', '');
				if(system.server.losecount<5){
					system.server.connect();
				}else{
					chat.givemsg('3', '連線不穩定！請檢察網路環境以排除此問題！', '');
					chat.givemsg('3', '重新整理後可再度嘗試連線', '');
					system.server.connectobj.$del();
				}
			}, t*1000);
			system.server.failcount=0;
		},
		'clearserverscript': function(){
			setTimeout(function(){
				system.server.connectobj.$del();
				system.server.connectobj=$('serverconnect').$add('iframe', {'src': 'server.php','lastmsg': chat.lastmsgid});
			},100)
		}
	},
	'userstatus':{
		'logined': false,
		'username': ''
	},
	'reg': function(){
		var c=true;
		if(!$('reg_username').value.length || !$('reg_email').value.length || !$('reg_password1').value.length || !$('reg_password2').value.length){
			alert('請填寫所有欄位');
			c=false;
		}else if(!/^[a-zA-Z0-9._+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,5}$/i.test($('reg_email').value)){
			alert('E-mail格式錯誤');
			c=false;
		}else if($('reg_password1').value !== $('reg_password2').value){
			alert('兩次密碼不相同');
			c=false;
		}else if($('reg_username').value.bitlength>20){
			alert('帳號必需在20個字元內');
			c=false;
		}else if($('reg_email').value.length>50){
			alert('信箱必需在50個字元內');
			c=false;
		}
		if(c){
			var re=ajaxpost('server.php?action=register', {
				'username': $('reg_username').value,
				'password': sha256_digest($('reg_password1').value),
				'email': $('reg_email').value
			});
			if(re=='regok'){
				alert('註冊成功！請開啟信箱收取驗證信');
				$('reg_username').value='';
				$('reg_email').value='';
				$('reg_password1').value='';
				$('reg_password2').value='';
			}else if(re=='exist'){
				alert('該帳號已存在或E-mail已被使用');
			}else if(re=='logined'){
				alert('你已經登入，在登入狀態下不可嘗試註冊');
			}
		}
		return false;
	},
	'login': function(){
		if(!$('username').value.length || !$('password').value.length){
			alert('請輸入帳號密碼');
			return false;
		}
		var re=ajaxpost('server.php?action=login', {
			'username': $('username').value,
			'password': sha256_digest($('password').value)
		});
		$('username').value='';
		$('password').value='';
		$('reg_username').value='';
		$('reg_email').value='';
		$('reg_password1').value='';
		$('reg_password2').value='';
		if(re=='logined'){
			system.userstatus.logined=true;
			system.userstatus.username=$('username').value;
			system.server.doconnect=true;
			$('start_login').style.display='none';
			$('start_register').style.display='none';
			$('chat').style.display='';
			if(window.Notification && ['granted','denied'].indexOf(Notification.permission)<0){
				Notification.requestPermission(function(ans){
					if(ans=='granted'){
						chat.givemsg('3', '已經啟動桌面通知');
					}
				});
			}
			system.server.connect();
			system.getonlinelist();
			system.keepupdate.onlinelist=setInterval(system.getonlinelist, 30000);
			$('chat_send_msg').focus();
			winresize();
			return true;
		}else{
			alert('登入失敗');
		}
		return false;
	},
	'logout': function(){
		var re=ajaxget('server.php', {'action':'logout'});
		system.server.close();
		system.userstatus.logined=false;
		system.userstatus.username='';
		system.server.doconnect=false;
		clearInterval(system.keepupdate.onlinelist);
		$('start_login').style.display='';
		$('chat').style.display='none';
		$('chat_msg').innerHTML='';
		$('chat_send_to').length=1;
	},
	'desktopnotice': function (title, msgtext){
		if(window.Notification){
			if(Notification.permission=='granted'){
				var notice = new Notification(title, {
					'body': msgtext,
					'tag': 'chatmsg'
				});
				notice.addEventListener('show', function(){
					setTimeout(function() {
						notice.close();
					}, 10000);
				});
				notice.addEventListener('click', function(){
					window.focus();
					$('chat_send_msg').focus();
					this.close();
				});
			}
		}
	},
	'getonlinelist': function(){
		var list=ajaxget('server.php', {'action':'onlinelist'});
		$('onlinecount').innerHTML=list.substring(0,list.indexOf('|'));
		$('onlinelist').innerHTML=list.substring(list.indexOf('|',0)+1,list.length);;
	},
	'getchannellist': function(){
		var list=ajaxget('server.php', {'action':'channel','do':'list'});
		$('channellist').innerHTML=list;
	},
	'gotochannel': function(cid,cname){
		chat.givemsg('3', '切換至頻道 '+cname, '');
		ajaxget('server.php', {'action':'channel','do':'goto','cid':cid});
		system.server.close();
		system.server.connect();
		system.getonlinelist();
	},
	'keepupdate': {
		'onlinelist': null
	},
	'winfocus' : true
}
window.addEventListener('load', function(){
	if(location.protocol!='https:'){
		var ssl=localStorage.getItem('ssl');
		if(ssl===null){
			localStorage.setItem('ssl',confirm('聊天室已提供安全連線，要改用安全連線？'));
			ssl=localStorage.getItem('ssl');
		}
		if(ssl=='true'){
			location.protocol='https:';
			return;
		}
	}
	var loginstatus=ajaxget('server.php', {'action':'chicklogin'});
	if(loginstatus=='logined'){
		system.userstatus.logined=true;
		system.server.doconnect=true;
		$('start_login').style.display='none';
		$('start_register').style.display='none';
		$('chat').style.display='';
		if(window.Notification && ['granted','denied'].indexOf(Notification.permission)<0){
			Notification.requestPermission(function(ans){
				if(ans=='granted'){
					chat.givemsg('3', '已經啟動桌面通知');
				}
			});
		}
		system.server.connect();
		system.getonlinelist();
		system.keepupdate.onlinelist=setInterval(system.getonlinelist, 30000);
		$('chat_send_msg').focus();
		winresize();
	}else $('username').focus();
});
window.addEventListener('blur', function(){
	system.winfocus=false;
});
window.addEventListener('focus', function(e){
	system.winfocus=true;
	if(system.userstatus.logined) setTimeout(function (){$('chat_send_msg').focus()},50);
});