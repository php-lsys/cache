<?php
/**
 * lsys cache
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS;
use function LSYS\Cache\__;
use LSYS\Cache\SetDefault;
abstract class Cache {
	/**
	 * @param string $name
	 * @return Cache
	 */
	public static function factory(Config $config) {
	    $name=$config->name();
		$driver=$config->get("driver",NULL);
		if (!class_exists($driver,true)||!in_array(__CLASS__,class_parents($driver))){
			throw new Exception( __('cache driver not defined in [:name on :driver] configuration',array(":name"=>$name,":driver"=>$driver)));
		}
		return  new $driver ( $config );
	}
	/**
	 * @var  Config
	 */
	protected $_config = array();
	
	/**
	 * Ensures singleton pattern is observed, loads the default expiry
	 *
	 * @param  array  $config  configuration
	*/
	protected function __construct(Config $config)
	{
		$this->_config=$config;
	}
	/**
	 * Overload the __clone() method to prevent cloning
	 *
	 * @return  void
	 * @throws  Exception
	 */
	final public function __clone()
	{
		throw new Exception(__('Cloning of Cache objects is forbidden'));
	}
	/**
	 * get default
	 * @param string $id
	 * @param mixed|callable $default
	 * @return mixed
	 */
	protected function _getDefault(string $id,$default){
	    if (!is_callable($default)) return $default;
	    $data=call_user_func($default,$id);
	    if ($data instanceof SetDefault) {
	        $timeout=$data->timeout();
	        $data=$data->data();
	        if ($data->isCache())$this->set($id,$data,$timeout);
	    }
		return $data;
	}
	/**
	 * Retrieve a cached value entry by id.
	 * $default is callable return SetDefault set default data to cache
	 * @param string $id
	 * @param mixed|callable $default 
	 * @return mixed
	 */
	abstract public function get(string $id, $default = NULL);
	/**
	 * Set a value to cache with id and lifetime
	 * @param string $id
	 * @param mixed $data
	 * @param number $lifetime
	 */
	abstract public function set(string $id, $data,?int $lifetime = NULL):bool;
	/**
	 * check id in cache?
	 * @param string $id
	 */
	abstract public function exist(string $id):bool;
	/**
	 * Delete a cache entry based on id
	 * @param string $id
	 */
	abstract public function delete(string $id):bool;
	/**
	 *  Delete all cache entries.
	 */
	abstract public function deleteAll():bool;
	
}