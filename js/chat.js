chat = {
	'oldmsgid': new Array(),//當前顯示的訊息列表
	'savenum': 100,//最高顯示的訊息列表
	'nextid': 0,//接續的訊息ID
	'lastmsgid': 0,
	'givemsg': function (type, msgtext, fromuid, from, to,time){//接收訊息
		var chatmsg = document.getElementById('chat_msg');
		var omsg = document.getElementById('chat_msg_' + this.nextid);//刪除舊資料
		if(omsg) omsg.$del();
		chatmsg.$add('div', {
			'id': 'chat_msg_' + this.nextid,
			'className': 'chat_msgbox '+(function (ty,fu,f,m,t){
				switch(ty){
					case '0': return 'public';
					case '1': return 'private';
					case '2': return 'public';
					case '3': return 'global';
				}
			})(type),
			'innerHTML': (function (ty,fu,f,m,t){
				switch(ty){
					case '0': return '<span onClick="chat.setpmsgto(' + fu + ',\'' + f + '\')">' + f + '</span> '+ new Date(time*1000).toLocaleTimeString() + '<br>' + m;
					case '1': return '<span onClick="chat.setpmsgto(' + fu + ',\'' + f + '\')">' + f + '</span> -> ' + t + ' ' + new Date(time*1000).toLocaleTimeString() + '<br>' + m;
					case '2': return '<span onClick="chat.setpmsgto(' + fu + ',\'' + f + '\')">' + f + '</span> ' + new Date(time*1000).toLocaleTimeString() + '<br>' + m;
					case '3': return m + '<br>';
				}
			})(type,fromuid,from,formatmsg(msgtext),to)
		});
		if(((window.screenTop<-30000 && window.screenLeft<-30000) || !system.winfocus) && type!='3'){
			system.desktopnotice('聊天室',htmlDecode(((type!='3') ? from+' '+(type=='1' ? '對你':'')+'說 ':'')+msgtext));
		}
		chatmsg.scrollTop=65535;
		if(this.nextid+1 >= this.savenum){
			this.nextid=0;
		}else{
			this.nextid++;
		}
	},
	'send': function (){//傳送訊息
		if(!system.userstatus.logined || $('chat_send_msg').value.length<=0) return false;
		if($('chat_send_to').value==0){
			var b=ajaxpost('server.php?action=chat', {
				'do':'send',
				'message':$('chat_send_msg').value
			});
		}else if($('chat_send_to').value=='admin'){
			this.admincommand();
			return false;chat_msgbox
		}else{
			var b=ajaxpost('server.php?action=chat', {
				'do':'psend',
				'touid':$('chat_send_to').value,
				'message':$('chat_send_msg').value
			});
		}
		if(b=='Fail'){
			chat.givemsg('3', '訊息包含非法字元！', '');
		}else if(b=='Offline'){
			chat.givemsg('3', '密語對像已離線！', '');
		}else if(b!='OK'){
			chat.givemsg('3', '不明錯誤導致發送失敗！請重試', '');
		}
		$('chat_send_msg').value='';
		$('chat_send_msg').focus();
		return false;
	},
	'setpmsgto': function (uid,username){
		if(!$('chat_send_to_'+uid)){
			$('chat_send_to').$add('option',{'id':'chat_send_to_'+uid,'value':uid,'innerHTML':username});
		}
		$('chat_send_to').value=uid;
	},
	'admincommand': function (){
		chat.givemsg('3', '指令：'+$('chat_send_msg').value, '');
		chat.givemsg('3', '返回：'+ajaxpost('server.php?action=admin', {'command':$('chat_send_msg').value}).replace(/(\r\n|\n\r|\r|\n)/g,'<br>'), '');
		$('chat_send_msg').value='';
		$('chat_send_msg').focus();
	}
}