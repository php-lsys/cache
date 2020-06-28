<?php
/**
 * lsys cache
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @license    http://kohanaframework.org/license
 */
namespace LSYS\Cache;
/**
 * 当不存在时默认参数为回调返回此对象进行设置默认数据操作 
 */
class SetDefault{
	private $_data;
	private $_is_cache;
	private $_timeout;
	/**
	 * 默认函数通过
	 * @param mixed $data 默认结果 
	 * @param bool $is_cache 是否缓存此结果 
	 * @param int $timeout 缓存时间 null 为默认存储时间
	 */
	public function __construct($data,bool $is_cache=true,?int $timeout=NULL){
		$this->_data=$data;
		$this->_is_cache=$is_cache;
		$this->_timeout=$timeout;
	}
	/**
	 * 结果集
	 * @return mixed
	 */
	public function data(){
	    return $this->_data;
	}
	/**
	 * 是否缓存结果
	 * @return bool
	 */
	public function isCache():bool{
	    return $this->_is_cache;
	}
	/**
	 * 返回默认值设置的存储时间
	 * @return int|NULL
	 */
	public function timeout():?int{
		return $this->_timeout;
	}
}