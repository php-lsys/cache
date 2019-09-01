<?php
/**
 * lsys cache
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
return array(
	"memcached"=>array(
		"driver"=>\LSYS\Cache\Memcached::class,
		'compression'=>false,
		//'config'=>"memcache.default",
	),
);