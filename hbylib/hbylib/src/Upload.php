<?php
/**
 * 文件上传类库
 * Created by PhpStorm
 * User: zjh
 * Date: 2018/6/1 0001
 * Version: 1.0
 */
namespace Hbylib\Hbylib;


class Upload{
    // 保存类实例在此属性中


    // 构造方法声明为private，防止直接创建对象

    private function __construct()

    {

        echo 'Iam constructed';

    }

    public static function uploadtmp($folder='tmp')
    {
        $files = $_FILES;
        foreach ($files as $key => $file) {
            $fileArray = $file;
        }
        $uploadFile = new UploadFile(array(
            'subDir' => true,
            'subDirType' => 'date'
        ));

        if(@!Config::get('fileupload_dir')){
            echo 'fileupload_dir is undefined!!';
            exit;
        }
        $path = Config::get('fileupload_dir') .'/'.$folder.'/';

        if ($uploadFile->upload($path)) {

            die('{"code":"0","filename":"'.$fileArray['name'].'","file" : "' . $uploadFile->getSuccessInfo()[0]['savename'] . '"}');

        }
        die('{"code":"-1","error" : 102, "message" : "' . $uploadFile->getErrorInfo() . '", "id" : "' . $_POST['id'] . '"}');

    }


    public static function uploadcom($folder='common')
    {
        $files = $_FILES;
        foreach ($files as $key => $file) {
            $fileArray = $file;
        }
        $uploadFile = new UploadFile(array(
            'subDir' => true,
            'subDirType' => 'date'
        ));

        if(@!Config::get('fileupload_dir')){
            echo 'fileupload_dir is undefined!!';
            exit;
        }
        $path = Config::get('fileupload_dir') .'/'.$folder.'/';

        if ($uploadFile->upload($path)) {

            die('{"code":"0","filename":"'.$fileArray['name'].'","file" : "' . $uploadFile->getSuccessInfo()[0]['savename'] . '"}');

        }
        die('{"code":"-1","error" : 102, "message" : "' . $uploadFile->getErrorInfo() . '", "id" : "' . $_POST['id'] . '"}');

    }



    // 阻止用户复制对象实例

    public function __clone()
    {
        trigger_error('Clone is not allowed.',E_USER_ERROR);
    }

}