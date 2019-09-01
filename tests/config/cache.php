<?php
return array(
	"redis"=>array(
		"driver"=>\LSYS\Cache\Redis::class,
	),
	"memcache"=>array(
		"driver"=>\LSYS\Cache\Memcache::class,
		'compression'=>false,
	),
	"memcached"=>array(
		"driver"=>\LSYS\Cache\Memcached::class,
		'compression'=>false,
	),
	"file"=>array(
		"driver"=>\LSYS\Cache\File::class,
		'cache_dir'=>__DIR__.'/../../src/cache'.DIRECTORY_SEPARATOR,
	),
);