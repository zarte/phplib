<?php
/**
 * 单例类
 * Created by PhpStorm
 * User: zjh
 * Date: 2018/6/1 0001
 * Version: 1.0
 */
namespace Hbydb\Hbydb;

class App{

    // 保存类实例在此属性中

    private static $instance;

    private static $pdoobjarr;



    // 构造方法声明为private，防止直接创建对象

    private function __construct()

    {

        echo 'Iam constructed';

    }



    // singleton 方法

    public static function getPdo($dbconf)
    {
        //if(!isset(self::$pdoobjarr[$dbconf['other']])){
           // var_dump(2);
                //这里不控制
            self::$pdoobjarr[$dbconf['other']] = new Pdo($dbconf);
       // }

        return self::$pdoobjarr[$dbconf['other']];

    }



    // 阻止用户复制对象实例

    public function __clone()

    {

        trigger_error('Clone is not allowed.',E_USER_ERROR);

    }


}