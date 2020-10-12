<?php
/**
 * 通用方法
 * Created by PhpStorm
 * User: zjh
 * Date: 2018/6/1 0001
 * Version: 1.0
 */
namespace Hbylib\Hbylib;

class Common{

    // 构造方法声明为private，防止直接创建对象

    private function __construct()

    {

        echo 'Iam constructed';

    }

    public static function getIp(){
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
        return  $res;
    }

    /**
     * 重定向
     *
     * @param string $url 重写向的目标地址
     * @param int $time 等待时间
     *
     * @return void
     */
    public static function redirect($url, $time = 0)
    {
        if (!headers_sent()) {
            ($time === 0) && header("Location: {$url}");
            header("refresh:{$time};url={$url}");
            exit();
        } else {
            exit("<meta http-equiv='Refresh' content='{$time};URL={ }'>");
        }
    }


    // 阻止用户复制对象实例

    public function __clone()
    {
        trigger_error('Clone is not allowed.',E_USER_ERROR);
    }

}