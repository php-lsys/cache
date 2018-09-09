<?php
/**
 * lsys cache
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS;
use function LSYS\Cache\__;
use LSYS\Cache\SetCallback;
abstract class Cache {
	public static $expire=3600;
	/**
	 * @param string $name
	 * @return Cache
	 */
	public static function factory(Config $config) {
	    $name=$config->name();
		$driver=$config->get("driver",NULL);
		if (!class_exists($driver,true)||!in_array(__CLASS__,class_parents($driver))){
			throw new Exception( __('cache driver not defined in [:name on :driver] configuration',array("name"=>$name,"driver"=>$driver)));
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
	protected function _get_callback_default($id,$default){
		if ($default instanceof SetCallback){
		    $data=$default->data($id);
		    $timeout=$default->timeout();
		    if($timeout===null||is_int($timeout)) $this->set($id,$data,$default->timeout());
			return $data;
		}
		return $default;
	}
	/**
	 * Retrieve a cached value entry by id.
	 * @param string $id
	 * @param mixed|SetCallback $default
	 */
	abstract public function get($id, $default = NULL);
	/**
	 * Set a value to cache with id and lifetime
	 * @param string $id
	 * @param mixed $data
	 * @param number $lifetime
	 */
	abstract public function set($id, $data,$lifetime = 3600);
	/**
	 * check id in cache?
	 * @param string $id
	 */
	abstract public function exist($id);
	/**
	 * Delete a cache entry based on id
	 * @param string $id
	 */
	abstract public function delete($id);
	/**
	 *  Delete all cache entries.
	 */
	abstract public function delete_all();
	
}