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
	 public function tagSet($id, $data,array $tags,$lifetime = 3600);
	/**
	 * Retrieve a cached value entry by id.
	 * @param string $tag
	 */
	 public function tagGet($id,$default);
	/**
	 * Retrieve a cached value entry by tag.
	 * @param string $tag
	 */
	 public function tagFind($tag);
	/**
	 * Delete a cache entry based on tag
	 * @param string $tag
	 */
	 public function tagDelete($tag);
}