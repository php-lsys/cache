<?php
/**
 * lsys cache
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
return array(
	"file"=>array(
		"driver"=>\LSYS\Cache\File::class,
		'cache_dir'=>__DIR__.'/../cache'.DIRECTORY_SEPARATOR,
	),
);