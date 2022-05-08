<?php

namespace Utils;

use Predis\Client;

class RedisMain {

    protected static $client = null;

    public function connect(){
        try{
            self::$client = new Client([
                    'scheme' => 'tcp',
                    'host'   => '127.0.0.1', // IP REDIS
                    'port'   => 6379,  // IP PORTA
                    /*'password' => getenv("RedisPass")*/]
            );
        } catch (\Exception $ex){
            self::$client = null;
        }
    }

    public static function setData($key, $value, $expire = 30){
        self::connect();
        if(!empty(self::$client)){
            self::$client->set($key, $value);
            self::$client->expire($key, $expire);
            return true;
        }
        return false;
    }

    public static function setArray($key, $value, $expire = 30){
        self::connect();
        if(!empty(self::$client)){
            self::$client->hmset($key, $value);
            self::$client->expire($key, $expire);
            return true;
        }
        return false;
    }

    public static function getData($key){
        self::connect();
        if(!empty($key)){
            if(!empty(self::$client)){
                if(self::$client->exists($key)){
                    return self::$client->get($key);
                }
            }
        }
        return null;
    }

    public static function getArray($key){
        self::connect();
        if(!empty($key)){
            if(!empty(self::$client)){
                if(self::$client->exists($key)){
                    return self::$client->hgetall($key);
                }
            }
        }
        return null;
    }
}
