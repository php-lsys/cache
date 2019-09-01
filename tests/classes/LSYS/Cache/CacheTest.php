<?php
namespace LSYS\Cache;
use PHPUnit\Framework\TestCase;
final class CacheTest extends TestCase
{
    
    public function testdi()
    {
        $this->assertTrue(\LSYS\Cache\DI::get()->cache()instanceof \LSYS\Cache);
    }
    public function testfile()
    {
        $cache=\LSYS\Cache::factory(\LSYS\Config\DI::get()->config("cache.file"));
        $this->assertTrue($cache->set("a1231231df","b"));
        $this->assertEquals($cache->get("a1231231df"),"b");
        $this->assertEquals($cache->get("a1231231dffasdf".uniqid(),"b"),"b");
        $this->assertEquals($cache->get("a1231231dffasdf".uniqid(),new SetCallback(function(){
            return "b";
        })),"b");
        $this->assertTrue($cache->exist("a1231231df"));
        $this->assertTrue($cache->delete("a1231231df"));
        $this->assertFalse($cache->exist("a1231231df"));
        $this->assertTrue($cache->set("a1231231df","b"));
        $cache->deleteAll();
        $this->assertFalse($cache->exist("a1231231df"));
    }
}