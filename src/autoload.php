<?php
namespace LFPhp\ProtocolJSON;
include_once dirname(__DIR__).'/vendor/autoload.php';
spl_autoload_register(function($class){
	if(strpos($class, __NAMESPACE__) !== false){
		$cls = str_replace(__NAMESPACE__, '', $class);
		$cls = str_replace('\\', '/', trim('\\', $cls));
		$file = __DIR__."/$cls.php";
		if(!is_file($file)){
			throw new \Exception(__NAMESPACE__.' file not found:'.$file);
		}
		include_once $file;
	}
});