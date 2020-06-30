<?php
/**
 * lsys cache
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Cache;
interface Tags {
	/**
	 * Set a value to cache with tag,id and lifetime
	 * @param string $tag
	 */
    public function tagSet(string $id, $data,array $tags,?int $lifetime = NULL):bool;
	/**
	 * Retrieve a cached value entry by id.
	 * @param string $tag
	 * @return mixed
	 */
    public function tagGet(string $id,$default=null);
	/**
	 * Retrieve a cached value entry by tag.
	 * @param string $tag
	 * @return mixed
	 */
    public function tagFind(string $tag):array;
	/**
	 * Delete a cache entry based on tag
	 * @param string $tag
	 */
    public function tagDelete(string $tag):bool;
}