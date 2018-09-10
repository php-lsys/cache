缓存层抽象封装
===

[![Build Status](https://travis-ci.com/php-lsys/cache.svg?branch=master)](https://travis-ci.com/php-lsys/cache)
[![Coverage Status](https://coveralls.io/repos/github/php-lsys/cache/badge.svg?branch=master)](https://coveralls.io/github/lsys/cache?branch=master)

> 封装此类库是为了让业务逻辑的KEY-VALUE操作脱离具体的某个缓存服务,如:redis或其他,提升代码兼容性

> 默认使用文件缓存,已实现以下包,根据实际情况使用

	"lsys/cache-memcache":"~1.0.0"
	"lsys/cache-memcached":"~1.0.0"
	"lsys/cache-redis":"~1.0.0"

使用示例:
```
//设置缓存使用配置,默认使用文件缓存
//不建议使用文件缓存,目前服务器很多性能都卡在磁盘IO上
\LSYS\Cache\DI::$config="cache.file";
```

```
$cache=\LSYS\Cache\DI::get()->cache();
// 设置缓存
var_dump($cache->set("a1231231df","b"));
// 获取缓存
var_dump($cache->get("a1231231df"));
// 判断缓存是否存在
var_dump($cache->exist("a1231231df"));
// 删除缓存
var_dump($cache->delete("a1231231df"));
// 删除所有缓存,注意:系统消耗大,不建议频繁调用
var_dump($cache->delete_all());
```

默认memcache 及redis 适配下已实现arithmetic及tags接口,可使用使用某key的自增减操作及按TAG操作缓存
关联数据设置缓存的时候会比较常用到,具体示例如下:
```
$cache=\LSYS\Cache\DI::get()->cache();
// 设置缓存,注意:通过tag_set设置数据通过get函数不可获取..
$tag=array('tag1');
var_dump($cache->tag_set("a1231231df","b",$tag));
// 获取缓存
var_dump($cache->tag_get("a1231231df"));
// 通过tag获取缓存
var_dump($cache->tag_find("tag1"));
// 通过tag删除某个缓存
var_dump($cache->tag_delete("tag1"));
```
