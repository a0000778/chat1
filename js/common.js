HTMLElement.prototype.$add=function(tagname, attr, before){
	var ele=document.createElement(tagname);
	for(var k1 in attr){
		if(typeof attr[k1] === 'object'){
			for(var k2 in attr[k1]){
				ele[k1][k2]=attr[k1][k2];
			}
		}else{
			ele[k1]=attr[k1];
		}
	}
	if(before){
		this.insertBefore(ele,before);
	}else{
		this.appendChild(ele);
	}
	return ele;
}

HTMLElement.prototype.$del=function (){
	this.parentNode.removeChild(this);
}

isIE=(navigator.userAgent.toLowerCase().indexOf('msie')!=-1);

function $(select){
	return document.getElementById(select);
}

function htmlEncode(str){
	var s = '';
	if (str.length == 0) return "";
	s = str.replace(/"/g,'&quot;');
	s = s.replace(/'/g,'&#39;');
	s = s.replace(/>/g,'&gt;');
	s = s.replace(/</g,'&lt;');
	s = s.replace(/&/g,'&amp;');
	return s;
}

function htmlDecode(str){
	var s = '';
	if (str.length == 0) return '';
	s = str.replace(/&amp;/g, '&');
	s = s.replace(/&lt;/g, '<');
	s = s.replace(/&gt;/g, '>');
	s = s.replace(/&#39;/g, '\'');
	s = s.replace(/&quot;/g, '"');
	return s;
}

function formatmsg(msgtext){
	var msgtext;
	var urls=htmlDecode(msgtext).match(/(([\w]+:)\/\/)(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w]([-\d\w]{0,253}[\d\w]|[\d\w]{0,254})\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?/g);
	if(!urls) return msgtext;
	var tolink=function(url){
		return '<a href="'+url+'" target="_blank">'+url+'</a>';
	}
	var at=0;
	while(at<urls.length){
		msgtext=msgtext.replace(htmlEncode(urls[at]),tolink);
		at++;
	}
	return msgtext;
}


String.prototype.bitlength=function (){
	var c = this.match(/[^ -~]/g);
	return this.length + (c ? c.length:0);
}

function ajax(){
	var cajax = false;
	try{
		this.ajaxobj = new XMLHttpRequest();
		cajax = true;
	}catch(e){
		var tryajaxobj=['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
		for(var x=0;x<=tryajaxobj.length-1;x++){
			try{
				this.ajaxobj = new ActiveXObject(tryajaxobj[x]);
				x=tryajaxobj.length;
				cajax = true;
			}catch(e){ }
		}
	}
	if(cajax){
		this.send = function (method, url, data, wdoing, statefun){
			this.ajaxobj.open(method, url + (method=='get'? '?' + data:''), wdoing);
			if(method=='post') this.ajaxobj.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			if(statefun.length>0) this.ajaxobj.onreadystatechange = statefun;
			this.ajaxobj.send((method=='post'? data:null));
		}
		this.getre = function (){
			return this.ajaxobj.responseText;
		}
		this.statenum = function (){
			return this.ajaxobj.readyState;
		}
		this.state = function (){
			return this.ajaxobj.status;
		}
		return this;
	}else{
		return false;
	}
}

function ajaxget(url, data){
	var ajaxobj=ajax();
	if(!ajaxobj) return false;
	var textdata='';
	var s='';
	for(var k in data){
		textdata+=s+k+'='+encodeURIComponent(data[k]);
		s='&';
	}
	ajaxobj.send('get', url, textdata, false, '');
	return ajaxobj.getre();
}

function ajaxpost(url, data){
	var ajaxobj=ajax();
	if(!ajaxobj) return false;
	var textdata='';
	var s='';
	for(var k in data){
		textdata+=s+k+'='+encodeURIComponent(data[k]);
		s='&';
	}
	ajaxobj.send('post', url, textdata, false, '');
	return ajaxobj.getre();
}

function setcookie(name,value,time){
	var t=new Date();
	t.setTime(t.getTime()+time*1000);
	document.cookie=name+'='+escape(value)+(time? ';expires='+t.toGMTString():'');
}
function getcookie(name){
	var s=document.cookie.indexOf(name);
	if(s!=-1){
		var e=document.cookie.indexOf(';', s);
		return unescape(document.cookie.substring(s+name.length+1,(e==-1)? document.cookie.length:e)); 
	}
	return false;
}
