<?php
/**
 *
 * Created by PhpStorm
 * User: zjh
 * Date: 2018/6/1 0001
 * Version: 1.0
 */
namespace Hbydb\Hbydb;


class Model{


    public  $dbdiever;
    public static $instance ;
    protected $table = null;
    protected $tablePrefix = null;

    public function db($dbconfigname="default")
    {
        $dbconf = Config::get($dbconfigname);
        switch ($dbconf['diver']){
            default:
                //self::$dbdiever  = App::getPdo($dbconf);
                if(isset($this->dbdiever)){

                }else{
                   // self::$dbdiever = new PDO($dbconf);
                    $this->dbdiever  = App::getPdo($dbconf);
                }

                break;
        }
       return $this->dbdiever;
    }

    /*
    public static function getInstance(){
        $dbconf = Config::get('default');
        return self::$instance = new PDO($dbconf);
    }

    */
    public function getFirst(){
        echo 'tt';
    }



    //快捷操作
    public function set($data, $tableName = null)
    {
        is_null($tableName) && $tableName = $this->table;
        return $this->db('default')->table($tableName)->set($data);
    }

    public function updateOneByColumn($val,$data,$column='id', $tableName = null)
    {
        is_null($tableName) && $tableName = $this->table;
        return $this->db('default')->table($tableName)->where($column,$val)->update($data);
    }

    public function getOneByColumn($val,$column='id', $tableName = null)
    {
        is_null($tableName) && $tableName = $this->table;
        return $this->db('default')->table($tableName)->where($column,$val)->find();
    }

    public function delOneByColumn($val,$column='id', $tableName = null)
    {
        is_null($tableName) && $tableName = $this->table;
        return $this->db('default')->table($tableName)->where($column,$val)->delete();
    }

}