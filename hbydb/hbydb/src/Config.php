<?php
/**
 *
 * Created by PhpStorm
 * User: zjh
 * Date: 2018/6/1 0001
 * Version: 1.0
 */
namespace Hbydb\Hbydb;

class Config{

    private static $config;
    public static function get($name){
        if(!self::$config){
          //  echo 'getconfig';
         //   echo "<br/>";
            self::$config = require BASE_PATH.'/config/database.php';
        }
        return self::$config[$name];
    }
}