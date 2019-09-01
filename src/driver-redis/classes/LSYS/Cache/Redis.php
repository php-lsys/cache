<?php
/**
 * lsys cache
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Cache;
use LSYS\Cache;
use LSYS\Config;
class Redis extends Cache implements Arithmetic,Tags {
	const CACHE_CEILING = 2592000;
	/**
	 * @var string
	 */
	public static $tag_prefix="__lsys_tag__";
	/**
	 * @var \LSYS\Redis
	 */
	protected $_redis;
	/**
	 * Constructs the memcache Cache object
	 *
	 * @param   array  $config  configuration
	 */
	protected function __construct(Config $config)
	{
		// Check for the memcache extention
		parent::__construct($config);
		$this->_redis =\LSYS\Redis\DI::get()->redis($config->get("config"));
		// Setup the flags
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::get()
	 */
	public function get($id, $default = NULL)
	{
	    $redis=$this->_redis->configConnect();
		if (!$redis->exists($id))return $this->_getCallbackDefault($id, $default);
		return $redis->get($id);
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::set()
	 */
	public function set($id,$data,$lifetime = 3600)
	{
	    $redis=$this->_redis->configConnect();
		if (is_array($data)||is_object($data))$data=serialize($data);
		return $redis->setex($id,$lifetime,$data);
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::delete()
	 */
	public function delete($id)
	{
	    $redis=$this->_redis->configConnect();
		$redis->del($id);
		return true;
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::deleteAll()
	 */
	public function deleteAll()
	{
	    $redis=$this->_redis->configConnect();
		$redis->flushDb();
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache\Arithmetic::increment()
	 */
	public function increment($id, $step = 1)
	{
	    $redis=$this->_redis->configConnect();
		if ($step==1) return $redis->incr($id);
		else  return $redis->incrBy($id, $step);
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache\Arithmetic::decrement()
	 */
	public function decrement($id, $step = 1)
	{
	    $redis=$this->_redis->configConnect();
		if ($step==1) return $redis->decr($id);
		else  return $redis->decrBy($id, $step);
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::exist()
	 */
	public function exist($id){
	    $redis=$this->_redis->configConnect();
		return !!$redis->exists($id);
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::tagSet()
	 */
	public function tagSet($id, $data,array $tags,$lifetime = 3600)
	{
	    $redis=$this->_redis->configConnect();
		$s=$redis->hmset($id,array(
			'v'=>$data,
			't'=>serialize($tags)
		));
		if ($s===false) return false;
		$tag_prefix=self::$tag_prefix;
		foreach($tags as $v){
			$_tag=$tag_prefix.$v;
			$tagval=$redis->sMembers($_tag);
			if(!in_array($id,$tagval))$redis->sAdd($_tag,$id);
			$ttl=$redis->ttl($_tag);
			if ($ttl>0&&$ttl<$lifetime)$redis->expire($_tag,$lifetime);
		}
		if (is_array($data)||is_object($data))$data=serialize($data);
		$redis->expire($id,$lifetime);
		return true;
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::tagGet()
	 */
	public function tagGet($id, $default = NULL)
	{
	    $redis=$this->_redis->configConnect();
		$data=$redis->hMget($id,array('v','t'));
		if (!isset($data['v'])||!isset($data['t'])){
			$redis->hDel($id,'v','t');
			return $default;
		}
		$tags=unserialize($data['t']);
		$val=$data['v'];
		if(is_array($tags)&&count($tags)>0){
			$tag_prefix=self::$tag_prefix;
			foreach($tags as $v){
				$tagval=$redis->sMembers($tag_prefix.$v);
				if(!in_array($id,$tagval)){
					$redis->hDel($id,'v','t');
					$val=$default;
					break;
				}
			}
		}
		// If the value wasn't found, normalise it
		if ($val === FALSE)
		{
			$val = (NULL === $default) ? NULL : $default;
		}
		// Return the value
		return $val;
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::tagGet()
	 */
	public function tagFind($tag){
	    $redis=$this->_redis->configConnect();
		$tag_prefix=self::$tag_prefix;
		$tagval=$redis->sMembers($tag_prefix.$tag);
		$val=array();
		foreach($tagval as $v){
			$data=$redis->hMget($v,array('v','t'));
			if (!isset($data['v'])||!isset($data['t'])){
				continue;
			}
			$tags=unserialize($data['t']);
			if (!in_array($tag, $tags))continue;
			$val[]=$data['v'];
		}
		return $val;
	}
	/**
	 * {@inheritDoc}
	 * @see \LSYS\Cache::tagDelete()
	 */
	public function tagDelete($tag){
	    $redis=$this->_redis->configConnect();
		$tag_prefix=self::$tag_prefix;
		$_tag=$tag_prefix.$tag;
		$tagval=$redis->sMembers($_tag);
		if(empty($tagval))return true;
		$redis->multi();
		foreach($tagval as $v){
			$redis->hDel($v,'v','t');
			$redis->srem($_tag,$v);
		}
		$redis->exec();
		return true;
	}
}