<?php
/**
 * 单例模式获取数据库对象
 * Created by PhpStorm
 * User: zjh
 * Date: 2018/6/1 0001
 * Version: 1.0
 */
namespace Hbylib\Hbylib;

class Input{
    // 保存类实例在此属性中

    private static $instance;

    // 构造方法声明为private，防止直接创建对象

    private function __construct()

    {

        echo 'Iam constructed';

    }

    public static function getString($key){
        if(!isset($_GET[$key]))return false;
        return addslashes(htmlspecialchars(trim($_GET[$key])));
    }
    public static function getInt($key){
        if(!isset($_GET[$key]))return false;
        return intval($_GET[$key]);
    }


    public static function postString($key){
        if(!isset($_POST[$key]))return false;
        return addslashes(htmlspecialchars(trim($_POST[$key])));
    }
    public static function postInt($key){
        if(!isset($_POST[$key]))return false;
        return intval($_POST[$key]);
    }


    // 阻止用户复制对象实例

    public function __clone()
    {
        trigger_error('Clone is not allowed.',E_USER_ERROR);
    }

}