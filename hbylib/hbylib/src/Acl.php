<?php
/**
 * 验证相关
 * Created by PhpStorm
 * User: zjh
 * Date: 2018/6/1 0001
 * Version: 1.0
 */
namespace Hbylib\Hbylib;
use NoahBuscher\Macaw\Macaw;
use Admin\models\AclModel;

class Acl{

    // 构造方法声明为private，防止直接创建对象

    private static $cookiekey ='distinctid';

    private function __construct()

    {

        echo 'Iam constructed';

    }

    public static function getInfo(){
        if(!isset($_COOKIE[Config::get('acllogin').self::$cookiekey])){
            return false;
        }
        $str = $_COOKIE[Config::get('acllogin').self::$cookiekey];
        $info = Encry::decrypt($str);

        if($info){
            if(isset($info['time']) && $info['time']>(time()-3600)){
                return $info;
            }
        }
        setcookie(Config::get('acllogin').self::$cookiekey, '', time()-3600,'/');
        return false;
    }

    /**
     * @param $uid
     * @param string $data
     * @param int $expire  默认一天
     */
    public static function setLogin($uid,$data='',$expire=1440){

        $info = Encry::encrypt(array(
            'uid'=>$uid,
            'data'=>$data,
            'time'=>time()
        ));
        setcookie(Config::get('acllogin').self::$cookiekey, $info, time()+$expire*60, '/');
    }

    public static function loginOut(){
        setcookie(Config::get('acllogin').self::$cookiekey, '', time()-3600,'/');
    }


    /**
     * 检查对应的权限
     *
     * @param object|string $controller 传入控制器实例对象，用来判断当前访问的方法是不是要跳过权限检查。
     * 如当前访问的方法为web/User/list则传入new \web\Controller\User()获得的实例。最常用的是在基础控制器的init方法或构造方法里传入$this。
     * 传入字符串如web/User/list时会自动 new \web\Controller\User()获取实例用于判断
     *
     * @return int 返回1是通过检查，0是不能通过检查
     */
    public static function checkAcl($controller)
    {

        $authInfo = self::getInfo();
        if (!$authInfo) return false; //登录超时

        //当前登录用户是否为超级管理员
        if (self::isSuperUser()) {
            return true;
        }

        $checkAction =  Macaw::$curmethod;
        $checkUrl = Macaw::$curroutes;
        if (is_object($controller)) {
            //判断是否有标识 @noacl 不检查权限
            $reflection = new \ReflectionClass($controller);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if ($method->name == $checkAction) {
                    $annotation = $method->getDocComment();
                    if (strpos($annotation, '@noacl') !== false) {
                        return true;
                    }
                    $checkUrlArray = [];
                    if (preg_match('/@acljump([^\n]+)/i', $annotation, $aclJump)) {
                        if (isset($aclJump[1]) && $aclJump[1]) {
                            $aclJump[1] = explode('|', $aclJump[1]);
                            foreach ($aclJump[1] as $val) {
                                trim($val) && $checkUrlArray[] = ltrim(str_replace('\\', '/', trim($val)), '/');
                            }
                        }
                        empty($checkUrlArray) || $checkUrl = $checkUrlArray;
                    }

                }
            }
        }

        $AclModel = new AclModel();
        $res = $AclModel->checkUserAcl($authInfo['uid'],array(1),$checkUrl);


        return 1;
    }


    /**
     * 判断当前登录用户是否为超级管理员
     *
     * @return bool
     */
    public static function isSuperUser()
    {
        $authInfo = self::getInfo();
        if (!$authInfo) {//登录超时
            return false;
        }
        return 1 === intval($authInfo['uid']);
    }

    // 阻止用户复制对象实例

    public function __clone()
    {
        trigger_error('Clone is not allowed.',E_USER_ERROR);
    }

}