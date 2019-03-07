<?php
include __DIR__."/Bootstarp.php";
//$cache=LSYS\Cache\DI::get()->cache();
$cache=\LSYS\Cache::factory(\LSYS\Config\DI::get()->config("cache.file"));
var_dump($cache->set("a1231231df","b"));
var_dump($cache->set("a1231231df1","b"));
var_dump($cache->get("a1231231df"));
var_dump($cache->exist("a1231231df"));
var_dump($cache->delete("a1231231df"));
var_dump($cache->deleteAll());



$cache->tagSet("test_a",'aaa', array("a","b"));

var_dump(in_array("aaa",$cache->tagFind("a")));
var_dump($cache->tagGet("test_a"));




