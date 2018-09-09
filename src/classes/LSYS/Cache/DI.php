<?php
namespace LSYS\Cache;
/**
 * @method \LSYS\Cache cache($config=null)
 */
class DI extends \LSYS\DI{
    /**
     * @var string default config
     */
    public static $config = 'cache.file';
    /**
     * @return static
     */
    public static function get(){
        $di=parent::get();
        !isset($di->cache)&&$di->cache(new \LSYS\DI\ShareCallback(function($config=null){
            return $config?$config:self::$config;
        },function($config=null){
            $config=\LSYS\Config\DI::get()->config($config?$config:self::$config);
            return \LSYS\Cache::factory($config);
        }));
        return $di;
    }
}