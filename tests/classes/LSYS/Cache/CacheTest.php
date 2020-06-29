<?php
namespace LSYS\Cache;
use PHPUnit\Framework\TestCase;
final class CacheTest extends TestCase
{
    
    public function testdi()
    {
        $this->assertTrue(\LSYS\Cache\DI::get()->cache()instanceof \LSYS\Cache);
    }
    public function testCache(){
        $this->runcachetest(\LSYS\Cache::factory(\LSYS\Config\DI::get()->config("cache.file")));
        $this->runcachetest(\LSYS\Cache::factory(\LSYS\Config\DI::get()->config("cache.redis")));
        $this->runcachetest(\LSYS\Cache::factory(\LSYS\Config\DI::get()->config("cache.memcache")));
        $this->runcachetest(\LSYS\Cache::factory(\LSYS\Config\DI::get()->config("cache.memcached")));
    }
    public function runcachetest($cache)
    {
        $this->assertTrue($cache->set("a1231231df","b"));
        $this->assertEquals($cache->get("a1231231df"),"b");
        $this->assertEquals($cache->get("a1231231dffasdf".uniqid(),"b"),"b");
        $this->assertEquals($cache->get("a1231231dffasdf".uniqid(),function(){
            return new SetDefault("data",true,1000);
        }),"data");
        $this->assertTrue($cache->exist("a1231231df"));
        $this->assertTrue($cache->delete("a1231231df"));
        $this->assertFalse($cache->exist("a1231231df"));
        $this->assertTrue($cache->set("a1231231df","b"));
        $cache->deleteAll();
        $this->assertFalse($cache->exist("a1231231df"));
    }
}