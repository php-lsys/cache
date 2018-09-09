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
	 public function tag_set($id, $data,array $tags,$lifetime = 3600);
	/**
	 * Retrieve a cached value entry by id.
	 * @param string $tag
	 */
	 public function tag_get($id,$default);
	/**
	 * Retrieve a cached value entry by tag.
	 * @param string $tag
	 */
	 public function tag_find($tag);
	/**
	 * Delete a cache entry based on tag
	 * @param string $tag
	 */
	 public function tag_delete($tag);
}