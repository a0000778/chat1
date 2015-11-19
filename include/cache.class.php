<?php
/*******************
PHP 通用函式庫
Cache v2.2
********************
Cache::init		載入Cache
Cache::get		取得Key的值
Cache::exists	檢查Key是否存在
Cache::set		設定Key的值
Cache::intinc	指定Key數值+$value，APC不推薦加上參數$ttl
Cache::incdec	指定Key數值-$value，APC不推薦加上參數$ttl

不支援Memcache，僅Memcached

Init Config
如果提供多種選擇，則按照Memcache,APC,XCache的優先順序，依支援狀況選用1種
array(
	'prefix' => '',//Key前置，預設為空
	'APC' => true,//選擇使用APC
	'Memcached' => array(
		'linkid' => '',//共用連線，若有設定此值則啟用
		'servers' => array(//格式同Memcached::addServers
			array('localhost',11211)
		)
	),
	'' => true
)
*******************/
class Cache{
	static private $useClass=null;
	static private $prefix='';
	
	static function init($config){
		if(self::$useClass!==null) return self::$useClass;
		if(isset($config['prefix'])) self::$prefix=$config['prefix'];
		foreach(array('Memcached','APC','XCache') as $test){
			$useClass='Cache_'.$test;
			if(isset($config[$test]) && $config[$test] && $useClass::init($config[$test]))
				return self::$useClass=$useClass;
		}
		return self::$useClass=false;
	}
	
	static function get($key){
		if(!self::$useClass) return false;
		$useClass=self::$useClass;
		return $useClass::get(self::$prefix.$key);
	}
	static function exists($key){
		if(!self::$useClass) return false;
		$useClass=self::$useClass;
		return $useClass::exists(self::$prefix.$key);
	}
	static function set($key,$value,$ttl=0){
		if(!self::$useClass) return false;
		$useClass=self::$useClass;
		return $useClass::set(self::$prefix.$key,$value,$ttl);
	}
	
	static function intinc($key,$value=1,$ttl=false){
		if(!self::$useClass) return false;
		$useClass=self::$useClass;
		return $useClass::intinc(self::$prefix.$key,$value,$ttl);
	}
	static function intdec($key,$value=1,$ttl=false){
		if(!self::$useClass) return false;
		$useClass=self::$useClass;
		return $useClass::intdec(self::$prefix.$key,$value,$ttl);
	}
}
class Cache_APC{
	static private $support=null;
	
	static function check_support(){
		if(self::$support!==null) return self::$support;
		return self::$support=function_exists('apc_store');
	}
	static function init(){
		return self::check_support();
	}
	static function get($key){
		return apc_fetch($key);
	}
	static function exists($key){
		return apc_exists($key);
	}
	static function set($key,$value,$ttl){
		return apc_store($key,$value,$ttl);
	}
	static function intinc($key,$value,$ttl){
		if($ttl!==false){
			$nowvalue=self::get($key);
			return self::set($key,(is_numeric($nowvalue)? $nowvalue+$value:$value),$ttl);
		}
		return apc_inc($key,$value);
	}
	static function intdec($key,$value,$ttl){
		if($ttl!==false){
			$nowvalue=self::get($key);
			return self::set($key,(is_numeric($nowvalue)? $nowvalue-$value:-$value),$ttl);
		}
		return apc_dec($key,$value);
	}
}
class Cache_Memcached{
	static private $support=null;
	static private $obj=null;
	static private $support_touch=true;
	
	static function check_support(){
		if(self::$support!==null) return self::$support;
		return self::$support=class_exists('Memcached');
	}
	static function init($config){
		if(!self::check_support()) return false;
		if(!isset($config['servers']) || !is_array($config['servers']) || count($config['servers'])<1) return false;
		if(isset($config['linkid'])) self::$obj=new Memcached($config['linkid']);
		else{
			self::$obj=new Memcached();
			self::$obj->setOption(Memcached::OPT_BINARY_PROTOCOL,true);
		}
		self::$obj->addServers($config['servers']);
		$status=self::$obj->getStats();
		if(is_array($status) && count($status)){
			foreach(self::$obj->getVersion() as $v){
				if(version_compare($v,'1.4.8','<')){
					self::$support_touch=false;
					return true;
				}
			}	
			return true;
		}
		return false;
	}
	static function get($key){
		return self::$obj->get($key);
	}
	static function exists($key){
		if(self::$obj->get($key)!==false) return true;
		return (self::$obj->getResultCode()!=Memcached::RES_NOTFOUND);
	}
	static function set($key,$value,$ttl){
		return self::$obj->set($key,$value,$ttl);
	}
	static function intinc($key,$value,$ttl){
		if($ttl!==false){
			if(self::$support_touch){
				if(!self::$obj->touch($key,$ttl)) return false;
			}else{
				if(($nowvalue=self::$obj->get($key))===false && self::$obj->getResultCode()==Memcached::RES_NOTFOUND) return false;
				$nowvalue+=$value;
				self::$obj->set($key,$nowvalue,$ttl);
				return $nowvalue;
			}
		}
		return self::$obj->increment($key,$value);
	}
	static function intdec($key,$value,$ttl){
		if($ttl!==false){
			if(self::$support_touch){
				if(!self::$obj->touch($key,$ttl)) return false;
			}else{
				if(($nowvalue=self::$obj->get($key))===false && self::$obj->getResultCode()==Memcached::RES_NOTFOUND) return false;
				$nowvalue-=$value;
				self::$obj->set($key,$nowvalue,$ttl);
				return $nowvalue;
			}
		}
		return self::$obj->decrement($key,$value);
	}
}
class Cache_XCache{
	static private $support=null;
	
	static function check_support(){
		if(self::$support!==null) return self::$support;
		return self::$support=function_exists('xcache_get');
	}
	static function init(){
		return self::check_support();
	}
	static function get($key){
		return xcache_get($key);
	}
	static function exists($key){
		return xcache_isset($key);
	}
	static function set($key,$value,$ttl){
		return xcache_set($key,$value,$ttl);
	}
	static function intinc($key,$value,$ttl){
		return xcache_inc($key,$value,$ttl);
	}
	static function intdec($key,$value,$ttl){
		return xcache_dec($key,$value,$ttl);
	}
}
?>
