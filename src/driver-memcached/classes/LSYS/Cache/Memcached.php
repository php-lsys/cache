<?php
/**
 * lsys cache
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Cache;
use LSYS\Exception;
use LSYS\Cache;
use LSYS\Config;
class Memcached extends Cache implements Arithmetic,Tags {
	const CACHE_CEILING = 2592000;
	/**
	 * @var string
	 */
	public static $tag_prefix="__lsys_tag__";
	/**
	 * @var \LSYS\Memcached
	 */
	protected $_memcache;
	/**
	 * Constructs the memcache Cache object
	 *
	 * @param   array  $config  configuration
	 * @throws  Exception
	 */
	protected function __construct(Config $config)
	{
		// Check for the memcache extention
		parent::__construct($config);
		$this->_memcache = \LSYS\Memcached\DI::get()->memcached($config->get("config"));
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::get()
	 */
	public function get(string $id, $default = NULL)
	{
	    $memcache=$this->_memcache->configServers();
		$val=$memcache->get($id);
		// If the value wasn't found, normalise it
		if ($val === FALSE)
		{
		    $val=$this->_getDefault($id, $default);
			$val = (NULL === $val) ? NULL : $val;
		}
		// Return the value
		return $val;
	}

	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::set()
	 */
	public function set(string $id, $data,?int $lifetime = NULL):bool
	{
	    // If lifetime is NULL
	    if ($lifetime === NULL)
	    {
	        // Set to the default expiry
	        $lifetime = $this->_config->get("default_expire",3600);
	    }
	    $memcache=$this->_memcache->configServers();
		// If the lifetime is greater than the ceiling
		if ($lifetime > static::CACHE_CEILING)
		{
			// Set the lifetime to maximum cache time
			$lifetime = static::CACHE_CEILING + time();
		}
		// Else if the lifetime is greater than zero
		elseif ($lifetime > 0)
		{
			$lifetime += time();
		}
		// Else
		else
		{
			// Normalise the lifetime
			$lifetime = 0;
		}
		$status=$memcache->set($id,$data, $lifetime);
		return boolval($status);
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::delete()
	 */
	public function delete(string $id, $timeout = 0):bool
	{
	    $memcache=$this->_memcache->configServers();
		// Delete the id
	    $status=$memcache->delete($id, $timeout);
		return boolval($status);
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::deleteAll()
	 */
	public function deleteAll():bool
	{
	    $memcache=$this->_memcache->configServers();
		$result = $memcache->flush();
		return boolval($result);
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache\Arithmetic::increment()
	 */
	public function increment(string $id, int $step = 1):bool
	{
	    $memcache=$this->_memcache->configServers();
	    $result=$memcache->increment($id, $step);
		return boolval($result);
		
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache\Arithmetic::decrement()
	 */
	public function decrement(string $id,int  $step = 1):bool
	{
	    $memcache=$this->_memcache->configServers();
	    $result=$memcache->decrement($id, $step);
		return boolval($result);
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::exist()
	 */
	public function exist(string $id):bool{
	    $memcache=$this->_memcache->configServers();
		return $memcache->get($id)!==false;
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::tagSet()
	 */
	public function tagSet(string $id, $data,array $tags,?int $lifetime = NULL):bool
	{
	    // If lifetime is NULL
	    if ($lifetime === NULL)
	    {
	        // Set to the default expiry
	        $lifetime = $this->_config->get("default_expire",3600);
	    }
	    $memcache=$this->_memcache->configServers();
		// If the lifetime is greater than the ceiling
		if ($lifetime > static::CACHE_CEILING)
		{
			// Set the lifetime to maximum cache time
			$lifetime = static::CACHE_CEILING + time();
		}
		// Else if the lifetime is greater than zero
		elseif ($lifetime > 0)
		{
			$lifetime += time();
		}
		// Else
		else
		{
			// Normalise the lifetime
			$lifetime = 0;
		}
	
		$tag_prefix=self::$tag_prefix;
		$cval=array(
				$data,
				$tags
		);
		$ttime=3600*24*7;
		foreach($tags as $v){
			$tagval=$memcache->get($tag_prefix.$v);
			if(!is_array($tagval))$tagval=array();
			if(!in_array($id,$tagval)) array_push($tagval,$id);
			$tagval=array_flip(array_flip($tagval));
			$memcache->set($tag_prefix.$v,$tagval, $ttime);
		}
		$result=$memcache->set($id,$cval, $lifetime);
		return boolval($result);
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::tagGet()
	 */
	public function tagGet(string $id, $default = NULL)
	{
	    $memcache=$this->_memcache->configServers();
		$val=$memcache->get($id);
		if(!is_array($val)||!isset($val[0])||!isset($val[1])) $val=$this->_getDefault($id, $default);
		else{
			list($val,$tags)=$val;
			if(!is_array($tags)){
			    $val=$this->_getDefault($id, $default);
			}else if(count($tags)>0){
				$tag_prefix=self::$tag_prefix;
				foreach($tags as $v){
					$tagval=$memcache->get($tag_prefix.$v);
					if(!is_array($tagval)||!in_array($id,$tagval)){
						$memcache->delete($id);
						$val=$this->_getDefault($id, $default);
						break;
					}
				}
			}else $val=$this->_getDefault($id, $default);
		}
		return $val;
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::tagGet()
	 */
	public function tagFind(string $tag){
	    $memcache=$this->_memcache->configServers();
		$tag_prefix=self::$tag_prefix;
		$tagval=$memcache->get($tag_prefix.$tag);
		if(!is_array($tagval))$tagval=array();
		$val=array();
		foreach($tagval as $v){
			$sval=$memcache->get($v);
			if(!is_array($sval)||!isset($sval[0])||!isset($sval[1]))continue;
			array_push($val,$sval[0]);
		}
		return $val;
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::tagDelete()
	 */
	public function tagDelete(string $tag):bool{
	    $memcache=$this->_memcache->configServers();
		$tag_prefix=self::$tag_prefix;
		$tagval=$memcache->get($tag_prefix.$tag);
		if(!is_array($tagval))$tagval=array();
		foreach($tagval as $v){
			$memcache->delete($v);
		}
		return true;
	}
}