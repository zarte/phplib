<?php

/***
 * 配置文件获取类
 */
namespace Hbylib\Hbylib;

 class  Config{

    protected static $config;
    protected static $confilepath;

    static  function  init(){
        self::$confilepath = '../config/';
        //基础配置
        if (file_exists(self::$confilepath.'common.php')){
            self::$config =  require self::$confilepath.'common.php';
        }
    }

    static function  get($key =''){
        if($key){
            return self::$config[$key];
        }else{
            return self::$config;
        }
    }
     static function  set($key,$val){
         self::$config[$key] = $val;
        return true;
     }

     static function  load($file){
        if (file_exists(self::$confilepath.$file.'.php')){
            self::$config[$file] =  require self::$confilepath.$file.'.php';
        }
     }
}
