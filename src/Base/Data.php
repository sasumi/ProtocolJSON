<?php
namespace LFPhp\ProtocolJSON\Base;

use ArrayAccess;
use JsonSerializable;
use LFPhp\ProtocolJSON\Exception\BaseException as Exception;
use function LFPhp\Func\dump;

/**
 * 数据对象原型
 * Class DataPrototype
 * @package Xiaoe\Common\Business
 */
class Data implements ArrayAccess, JsonSerializable {
	const OPT_ASSIGN_DATA_ALL = 0;
	const OPT_ASSIGN_DEFINED = 1;
	const OPT_ASSIGN_DEFINED_OR_THROW_EXCEPTION = 2;

	public function __construct(array $data = [], $opt_assign = self::OPT_ASSIGN_DATA_ALL){
		if($data){
			dump($opt_assign, 0);

			$properties = [];
			if($opt_assign == self::OPT_ASSIGN_DEFINED || $opt_assign == self::OPT_ASSIGN_DEFINED_OR_THROW_EXCEPTION){
				$properties = self::resolveProperties(get_called_class());
			}
			dump($properties, 1);
			foreach($data as $k => $val){
				if($properties && !isset($properties[$k])){
					if($opt_assign == self::OPT_ASSIGN_DEFINED_OR_THROW_EXCEPTION){
						throw new Exception("Property no support in data model:$k, ".get_called_class());
					}else{
						continue;
					}
				}
				$this->{$k} = $val;
			}
		}
	}

	private static function resolveProperties($class){
		$rfc = new \ReflectionClass($class);
		$properties = self::resolveComments($rfc->getDocComment());
		while($cls = $rfc->getParentClass()){
			$properties = array_merge($properties, self::resolveComments($cls->getDocComment()));
		}
	}

	private static function resolveComments($comment_str){
		$properties = [];
		if(preg_match_all("/@property\s([^\s]+)\s([^\s]+)/", $comment_str, $matches)){
			$properties = $matches[1];
		}
		return $properties;
	}

	public function offsetExists($offset){
		return isset($this->{$offset});
	}

	public function offsetGet($offset){
		return isset($this->{$offset});
	}

	public function offsetSet($offset, $value){
		$this->{$offset} = $value;
	}

	public function offsetUnset($offset){
		if(isset($this->{$offset})){
			unset($this->{$offset});
		}
	}

	public function jsonSerialize(){
		return $this->toArray();
	}

	public function __toString(){
		return $this->toJSON();
	}

	/**
	 * @param $name
	 * @param null $default
	 * @return mixed|null
	 */
	public function get($name, $default = null){
		return static::data_fetch($this, $name, $default);
	}

	/**
	 * @param $data
	 * @param $path
	 * @param null $default
	 * @param string $path_separator
	 * @return mixed|null
	 */
	private static function data_fetch($data, $path, $default = null, $path_separator = '.'){
		if(is_null($path)){
			return $data;
		}
		if(isset($data[$path])){
			return $data[$path];
		}
		foreach(explode($path_separator, $path) as $segment){
			if((!is_array($data) || !array_key_exists($segment, $data)) && (!$data instanceof ArrayAccess || !$data->offsetExists($segment))){
				return $default;
			}
			$data = $data[$segment];
		}
		return $data;
	}

	public function toArray(){
		$data = [];
		foreach($this as $k => $v){
			if(!is_scalar($v)){
				$data[$k] = $v->toArray();
			}else{
				$data[$k] = $v;
			}
		}
		return $data;
	}

	public function toJSON(){
		return json_encode($this->jsonSerialize(), JSON_UNESCAPED_UNICODE);
	}
}