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
 * 当不存在时默认回调处理函数 
 */
class SetCallback{
	private $_callback;
	private $_timeout;
	/**
	 * 默认函数通过
	 * @param callable $callback $callback($id) 
	 * @param int $timeout 设置为false不存储到缓存中,null 为默认存储时间
	 */
	public function __construct(callable $callback,$timeout=NULL){
		$this->_callback=$callback;
		$this->_timeout=$timeout;
	}
	public function data($id){
	    return call_user_func($this->_callback,$id);
	}
	public function timeout(){
		return $this->_timeout;
	}
}