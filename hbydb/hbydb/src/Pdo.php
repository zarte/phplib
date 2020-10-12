<?php
/**
 *
 *  Pdo类
 * Created by PhpStorm
 * User: zjh
 * Date: 2018/6/1 0001
 * Version: 1.0
 */
namespace Hbydb\Hbydb;

class Pdo extends Base{


    private $_instance;


    public function __construct($config)
    {
        try {

            if($config['other']==1){
               //if(FALSE == (self::$_instance1 instanceof \PDO)) {
                    $this->tablePrefix = $config['pre'];
                   // echo "init<br/>";
                    //self::$_instance1 = new \PDO($config['dbtype'].":host=".$config['host'].";dbname=".$config['dbname'], $config['user'], $config['pass']); //初始化一个PDO对象
                    $this->_iniconf = $config;
              //  }
              //  $this->_instance =  self::$_instance1;
            }else{
              //  if(FALSE == (self::$_instance2 instanceof \PDO)) {
                    $this->tablePrefix = $config['pre'];
                  //  echo "init2<br/>";
                   // self::$_instance2= new \PDO($config['dbtype'].":host=".$config['host'].";dbname=".$config['dbname'], $config['user'], $config['pass']); //初始化一个PDO对象
                    $this->_iniconf = $config;
             //   }
              //  $this->_instance =  self::$_instance2;
            }

        } catch (PDOException $e) {
            die ("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function insertId($link = null)
    {
        is_null($link) && $link = $this->wlink;
        return $link->lastInsertId();
    }

    /**
     * @param null $offset
     * @param null $limit
     * @param bool $useMaster       是否主库
     * @return array|bool|null
     */
    public function select($offset = null, $limit = null, $useMaster = false){
        list($sql, $cacheKey) = $this->buildSql($offset, $limit, true);

        if ($this->openCache) {
          //  $cacheKey = md5($sql . json_encode($this->bindParams)) . implode('', $cacheKey);
            echo '缓存未实现';
            exit;
            return null;
        } else {
            $return = false;
        }

        if ($return === false) {

            $stmt = $this->prepare($sql, $useMaster ? $this->wlink : $this->rlink);

            $this->execute($stmt);
            $return = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->openCache && exit('缓存未实现');
        } else {
            $this->reset();
            $this->clearBindParams();
        }
        return $return;
    }


    public function query($sql,$optype = \PDO::FETCH_ASSOC){
        $smt = $this->wlink;
        $res = array();
        $sth = $smt->query($sql,$optype);
        if(!$sth){
            var_dump($sql);
            var_dump($sth);exit;
        }
        while($row = $sth->fetch()){
            $res[] = $row;
        }
        return $res;
    }

    public function dml($sql){
        $smt = $this->wlink;
        $count = $smt->exec($sql);
        if(!$count){
            $error = $smt->errorInfo();
            if($error[0]!='00000'){
                throw new \InvalidArgumentException('Pdo execute Sql error!'.$sql.'】,【Error:' . $error[2] . '】');
            }
        }
        return $count;
    }

    /**
     * @param $listnum
     * @return array|bool|null
     * p: 1为首页
     */
    public function pageselect($listnum=15){
        $listnum = intval($listnum);
        if(isset($_GET['p'])){
            $nowpage = intval($_GET['p']);
        }else{
            $nowpage = 1;
        }
        $offset = ($nowpage-1)*$listnum;
      return $this->select($offset, $listnum);
    }


    public function count($field = '*', $isMulti = false, $useMaster = false)
    {
        $count = $this->columns(["COUNT({$field})" => '__res__'])->select(null, null, $useMaster);
        return intval($count[0]['__res__']);
    }

    public function find( $useMaster = false){
        list($sql, $cacheKey) = $this->buildSql(0, 1, true);
        if ($this->openCache) {
            $cacheKey = md5($sql . json_encode($this->bindParams)) . implode('', $cacheKey);
            echo '缓存未实现';
            exit;
            return null;
        } else {
            $return = false;
        }

        if ($return === false) {
            $stmt = $this->prepare($sql, $useMaster ? $this->wlink : $this->rlink);
            $this->execute($stmt);
            $return = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if($return){
                $return = $return[0];
            }
            $this->openCache && Model::getInstance()->cache()->set($cacheKey, $return, $this->conf['cache_expire']);
        } else {

            $this->reset();
            $this->clearBindParams();
        }
        return $return;
    }

    /**
     * 根据key 新增 一条数据
     *
     * @param string $table
     * @param array $data eg: ['username'=>'admin', 'email'=>'linhechengbush@live.com']
     * @param mixed $tablePrefix 表前缀 不传则获取配置中配置的前缀
     *
     * @return bool|int
     */
    public function set( $data)
    {

        $tableAndCacheKey = $this->tableFactory();
        $tableName = $tableAndCacheKey[0];

        if (is_array($data)) {
            $s = $this->arrToCondition($data);
            $stmt = $this->prepare("INSERT INTO {$tableName} SET {$s}", $this->wlink);
            $res = $this->execute($stmt);
          // $this->setCacheVer($tableName);
            return $this->insertId();
        } else {
            return false;
        }
    }

    public function delete($key = '', $and = true, $tablePrefix = null)
    {
        is_null($tablePrefix) && $tablePrefix = $this->tablePrefix;
        $tableName = $condition = '';

        empty($key) || list($tableName, $condition) = $this->parseKey($key, $and, true, true);

        if (empty($tableName)) {
            $tableAndCacheKey = $this->tableFactory(false);
            $tableName = $tableAndCacheKey[0];
            $upCacheTables = $tableAndCacheKey[1];
        } else {
            $tableName = $tablePrefix . $tableName;
            $upCacheTables = [$tableName];
        }

        if (empty($tableName)) {
            throw new \InvalidArgumentException(Lang::get('_PARSE_SQL_ERROR_NO_TABLE_', 'delete'));
        }
        $whereCondition = $this->sql['where'];
        $whereCondition .= empty($condition) ? '' : (empty($whereCondition) ? 'WHERE ' : '') . $condition;
        if (empty($whereCondition)) {
            throw new \InvalidArgumentException(Lang::get('_PARSE_SQL_ERROR_NO_CONDITION_', 'delete'));
        }
        $stmt = $this->prepare("DELETE FROM {$tableName} {$whereCondition}", $this->wlink);
        $this->execute($stmt);

        foreach ($upCacheTables as $tb) {
         //   $this->setCacheVer($tb);
        }
        return $stmt->rowCount();
    }

    /**
     * 根据key更新一条数据
     *
     * @param string $key eg 'user-uid-$uid' 如果条件是通用whereXX()、表名是通过table()设定。这边可以直接传$data的数组
     * @param array | null $data eg: ['username'=>'admin', 'email'=>'linhechengbush@live.com']
     * @param bool $and 多个条件之间是否为and  true为and false为or
     * @param mixed $tablePrefix 表前缀 不传则获取配置中配置的前缀
     *
     * @return boolean
     */
    public function update($data = null)
    {
        $tableName = $condition = '';

        if (empty($tableName)) {
            $tableAndCacheKey = $this->tableFactory(false);
            $tableName = $tableAndCacheKey[0];
            $upCacheTables = $tableAndCacheKey[1];
        }

        if (empty($tableName)) {

        }
        $s = $this->arrToCondition($data);
        $whereCondition = $this->sql['where'];
        $whereCondition .= empty($condition) ? '' : (empty($whereCondition) ? 'WHERE ' : '') . $condition;
        if (empty($whereCondition)) {

        }
        $stmt = $this->prepare("UPDATE {$tableName} SET {$s} {$whereCondition}", $this->wlink);
        $this->execute($stmt);

        return $stmt->rowCount();
    }

    /**
     * 预处理语句
     *
     * @param string $sql 要预处理的sql语句
     * @param bool $resetParams
     *
     * @return \PDOStatement
     */

    public function prepare($sql, $link = null, $resetParams = true)
    {
        $resetParams && $this->reset();

        $sqlParams = [];
        foreach ($this->bindParams as $key => $val) {
            $sqlParams[] = ':param' . $key;
        }

        $this->currentSql = $sql;
        $sql = vsprintf($sql, $sqlParams);

        $stmt = $link->prepare($sql);//pdo默认情况prepare出错不抛出异常只返回Pdo::errorInfo
        if ($stmt === false) {
            $error = $link->errorInfo();
            throw new \InvalidArgumentException(
                'Pdo Prepare Sql error! ,【Sql : ' . $this->buildDebugSql() . '】,【Code:' . $link->errorCode() . '】, 【ErrorInfo!:' . $error[2] . '】 '
            );
        } else {
            foreach ($this->bindParams as $key => $val) {
                is_int($val) ? $stmt->bindValue(':param' . $key, $val, \PDO::PARAM_INT) : $stmt->bindValue(':param' . $key, $val, \PDO::PARAM_STR);
            }
            return $stmt;
        }
        return false;
    }

    /**
     * 构建sql
     *
     * @param null $offset 偏移量
     * @param null $limit 返回的条数
     * @param bool $isSelect 是否为select调用， 是则不重置查询参数并返回cacheKey/否则直接返回sql并重置查询参数
     *
     * @return string|array
     */
    public function buildSql($offset = null, $limit = null, $isSelect = false)
    {
        is_null($offset) || $this->limit($offset, $limit);

        $this->sql['columns'] == '' && ($this->sql['columns'] = '*');

        $columns = $this->sql['columns'];

        $tableAndCacheKey = $this->tableFactory();

        empty($this->sql['limit']) && ($this->sql['limit'] = "LIMIT 0, 100");

        $sql = "SELECT $columns FROM {$tableAndCacheKey[0]} " . $this->sql['where'] . $this->sql['groupBy'] . $this->sql['having']
            . $this->sql['orderBy'] . $this->union . $this->sql['limit'];

        if ($isSelect) {
            return [$sql, $tableAndCacheKey[1]];
        } else {
            $this->currentSql = $sql;
            $sql = $this->buildDebugSql();
            $this->reset();
            $this->clearBindParams();
            $this->currentSql = '';
            return " ({$sql}) ";
        }
    }


    /**
     * table组装工厂
     *
     * @param bool $isRead 是否为读操作
     *
     * @return array
     */
    private function tableFactory($isRead = true)
    {
        $table = $operator = '';
        $cacheKey = [];

        foreach ($this->table as $key => $val) {
            $realTable = $this->getRealTableName($key);

            $cacheKey[] = $isRead ? $this->getCacheVer($realTable) : $realTable;

            $on = null;
            if (isset($this->join[$key])) {
                $operator = ' INNER JOIN';
                $on = $this->join[$key];
            } elseif (isset($this->leftJoin[$key])) {
                $operator = ' LEFT JOIN';
                $on = $this->leftJoin[$key];
            } elseif (isset($this->rightJoin[$key])) {
                $operator = ' RIGHT JOIN';
                $on = $this->rightJoin[$key];
            } else {
                empty($table) || $operator = ' ,';
            }
            if (is_null($val)) {
                $table .= "{$operator} {$realTable}";
            } else {
                $table .= "{$operator} {$realTable} AS `{$val}`";
            }
            isset($this->forceIndex[$realTable]) && $table .= ' force index(' . $this->forceIndex[$realTable] . ') ';
            is_null($on) || $table .= " ON {$on}";
        }

        if (empty($table)) {
            throw new  \mysqli_sql_exception("操作表未定义");
        }
        return [$table, $cacheKey];
    }

    /**
     * 执行预处理语句
     *
     * @param object $stmt PDOStatement
     * @param bool $clearBindParams
     *
     * @return bool
     */
    public function execute($stmt, $clearBindParams = true)
    {
        //empty($param) && $param = $this->bindParams;
        $this->conf['log_slow_sql'] && $startQueryTimeStamp = microtime(true);
        if (!$stmt->execute()) {
            $error = $stmt->errorInfo();
            throw new \InvalidArgumentException('Pdo execute Sql error!,【Sql : ' . $this->buildDebugSql() . '】,【Error:' . $error[2] . '】');
        }

        $slow = 0;
        if ($this->conf['log_slow_sql']) {
            $queryTime = microtime(true) - $startQueryTimeStamp;
            if ($queryTime > $this->conf['log_slow_sql']) {
                if (Plugin::hook('cml.mysql_query_slow', ['sql' => $this->buildDebugSql(), 'query_time' => $queryTime]) !== false) {
                    Log::notice('slow_sql', ['sql' => $this->buildDebugSql(), 'query_time' => $queryTime]);
                }
                $slow = $queryTime;
            }
        }

        $this->currentSql = '';
        $clearBindParams && $this->clearBindParams();
        return true;
    }
    /**
     * 组装sql用于DEBUG
     *
     * @return string
     */
    private function buildDebugSql()
    {
        $bindParams = $this->bindParams;
        foreach ($bindParams as $key => $val) {
            $bindParams[$key] = str_replace('\\\\', '\\', addslashes($val));
        }
        return vsprintf(str_replace('%s', "'%s'", $this->currentSql), $bindParams);
    }
    /**
     * 获取处理后的表名
     *
     * @param $table
     * @return string
     */
    private function getRealTableName($table)
    {
        return substr($table, strpos($table, '_') + 1);
    }



}