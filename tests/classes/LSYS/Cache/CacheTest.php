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
//        $this->runcachetest(\LSYS\Cache::factory(\LSYS\Config\DI::get()->config("cache.memcache")));
//        $this->runcachetest(\LSYS\Cache::factory(\LSYS\Config\DI::get()->config("cache.memcached")));
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
        
        if ($cache instanceof Tags) {
            $cache->tagSet("test_a",'aaa', array("a","b"));
            $this->assertTrue(in_array("aaa",$cache->tagFind("a")));
            $this->assertEquals($cache->tagGet("test_a"), 'aaa');
            $cache->tagDelete('a');
            $this->assertEmpty($cache->tagGet("test_a"));
        }
        
        if ($cache instanceof Arithmetic) {
            $eq1=$cache->increment("ttt");
            $this->assertTrue($eq1==1);
            $eq0=$cache->decrement('ttt');
            $this->assertTrue($eq0==0);
        }
        
    }
}
