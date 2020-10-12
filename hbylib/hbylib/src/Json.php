<?php
/**
 * 单例模式获取数据库对象
 * Created by PhpStorm
 * User: author
 * Date: 2018/6/1 0001
 * Version: 1.0
 */
namespace Hbylib\Hbylib;

class Json{
    // 保存类实例在此属性中

    private static $instance;

    // 构造方法声明为private，防止直接创建对象

    private function __construct()

    {

        echo 'I am constructed';

    }

    public static function renderJson($code=200,$msg='',$data=null){

        if(!$msg){
            $msg = Config::get('codemsg');
            $msg = $msg[$code];
        }

       echo json_encode(array(
           'code'=>$code,
           'msg'=>$msg,
           'data'=>$data
       ));
        exit;
    }

    public static function renderPage($code=200,$total=0,$list=null,$limit=0){
        echo json_encode(array(
            'code'=>200,
            'total'=>$total,
            'list'=>$list,
            'limit'=>$limit
        ));
        exit;
    }

    // 阻止用户复制对象实例

    public function __clone()
    {
        trigger_error('Clone is not allowed.',E_USER_ERROR);
    }

}