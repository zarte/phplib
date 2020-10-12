<?php
/**
 *
 * Created by PhpStorm
 * User: zjh
 * Date: 2018/6/1 0001
 * Version: 1.0
 */
namespace Hbydb\Hbydb;

class Base{


    private static $_instance1;
    private static $_instance2;
    protected $_iniconf;

    /**
     * 启用数据缓存
     *
     * @var bool
     */
    protected $openCache = false;

    /**
     * where操作需要加上and/or
     * 0 : 初始化两个都不加
     * 1 : 要加and
     * 2： 要加 or
     *
     * @var int
     */
    protected $whereNeedAddAndOrOr = 0;

    /**
     * 执行sql时绑定的参数
     *
     * @var array
     */
    protected $bindParams = [];

    /**
     * sql组装
     *
     * @var array
     */
    protected $sql = [
        'where' => '',
        'columns' => '',
        'limit' => '',
        'orderBy' => '',
        'groupBy' => '',
        'having' => '',
    ];

    /**
     * orm参数是否自动重置
     *
     * @var bool
     */
    protected $paramsAutoReset = true;

    /**
     * $paramsAutoReset = false 的时候是否清除table.避免快捷方法重复调用table();
     *
     * @var bool
     */
    protected $alwaysClearTable = false;

    /**
     * $paramsAutoReset = false 的时候是否清除查询的字段信息.主要用于按批获取数据不用多次调用columns();
     *
     * @var bool
     */
    protected $alwaysClearColumns = true;

    /**
     * 操作的表
     *
     * @var array
     */
    protected $table = [];

    /**
     * 是否内联 [表名 => 条件]
     *
     * @var array
     */
    protected $join = [];

    /**
     * 是否左联结 写法同内联
     *
     * @var array
     */
    protected $leftJoin = [];

    /**
     * 是否右联 写法同内联
     *
     * @var array
     */
    protected $rightJoin = [];

    /**
     * 表前缀方便外部读取
     *
     * @var string
     */
    public $tablePrefix;


    /**
     * UNION 写法同内联
     *
     * @var string
     */
    protected $union = '';


    /**
     * 定义操作的表
     *
     * @param string|array $table 表名 要取别名时使用 [不带前缀表名 => 别名]
     * @param mixed $tablePrefix 表前缀 不传则获取配置中配置的前缀
     *
     * @return $this
     */
    public function table($table = '', $tablePrefix = null)
    {
        $hasAlias = is_array($table) ? true : false;
        is_null($tablePrefix) && $tablePrefix = $this->tablePrefix;
        $tableName = $tablePrefix . ($hasAlias ? key($table) : $table);

        $this->table[count($this->table) . '_' . $tableName] = $hasAlias ? current($table) : null;
        return $this;
    }


    public function columns($columns = '*')
    {
        $result = '';
        if (is_array($columns)) {
            foreach ($columns as $key => $val) {
                $result .= ($result == '' ? '' : ', ') . (is_int($key) ? $val : ($key . " AS `{$val}`"));
            }
        } else {
            $args = func_get_args();
            while ($arg = current($args)) {
                $result .= ($result == '' ? '' : ', ') . $arg;
                next($args);
            }
        }
        $this->sql['columns'] == '' || ($this->sql['columns'] .= ' ,');
        $this->sql['columns'] .= $result;
        return $this;
    }

    /**
     * where条件组装 相等
     *
     */
    public function where($column, $value = '',$op = '=')
    {
        $this->conditionFactory($column, $value, $op);
        return $this;
    }
    /**
     * where条件组装 BETWEEN
     *
     * @param string $column 如 id  user.id (这边的user为表别名如表pre_user as user 这边用user而非带前缀的原表名)
     * @param string |int | array $value
     * @param string |int | null $value2
     *
     * @return $this
     */
    public function whereBetween($column, $value, $value2 = null)
    {
        if (is_null($value2)) {
            if (!is_array($value)) {
                throw new \InvalidArgumentException(Lang::get('_DB_PARAM_ERROR_WHERE_BETWEEN_'));
            }
            $val = $value;
        } else {
            $val = [$value, $value2];
        }
        $this->conditionFactory($column, $val, 'BETWEEN');
        return $this;
    }


    public function whereGt($column, $value)
    {
        $this->conditionFactory($column, $value, '>');
        return $this;
    }
    public function whereLt($column, $value)
    {
        $this->conditionFactory($column, $value, '<');
        return $this;
    }
    public function whereLte($column, $value)
    {
        $this->conditionFactory($column, $value, '<=');
        return $this;
    }
    public function whereGte($column, $value)
    {
        $this->conditionFactory($column, $value, '>=');
        return $this;
    }
    public function whereIn($column, $value)
    {
        $this->conditionFactory($column, $value, 'IN');
        return $this;
    }

    public function whereNotIn($column, $value)
    {
        $this->conditionFactory($column, $value, 'NOT IN');
        return $this;
    }
    public function whereLike($column, $leftBlur = false, $value, $rightBlur = false)
    {
        $this->conditionFactory(
            $column,
            ($leftBlur ? '%' : '') . $this->filterLike($value) . ($rightBlur ? '%' : ''),
            'LIKE'
        );
        return $this;
    }


    /**
     * 增加 and条件操作符
     *
     * @return $this
     */
    public function _and()
    {
        $this->whereNeedAddAndOrOr = 1;
        return $this;
    }

    /**
     * 增加or条件操作符
     *
     * @return $this
     */
    public function _or()
    {
        $this->whereNeedAddAndOrOr = 2;
        return $this;
    }

    /**
     * where条件增加左括号
     *
     * @return $this
     */
    public function lBrackets()
    {
        if ($this->sql['where'] == '') {
            $this->sql['where'] = 'WHERE ';
        } else {
            if ($this->whereNeedAddAndOrOr === 1) {
                $this->sql['where'] .= ' AND ';
            } else if ($this->whereNeedAddAndOrOr === 2) {
                $this->sql['where'] .= ' OR ';
            }
        }
        $this->sql['where'] .= ' (';
        //移除下一次where操作默认加上AND
        $this->whereNeedAddAndOrOr = 0;
        return $this;
    }

    /**
     * where条件增加右括号
     *
     * @return $this
     */
    public function rBrackets()
    {
        $this->sql['where'] .= ') ';
        return $this;
    }
    /**
     * LIMIT
     *
     * @param int $offset 偏移量
     * @param int $limit 返回的条数
     *
     * @return $this
     */
    public function limit($offset = 0, $limit = 10)
    {
        $offset = intval($offset);
        $limit = intval($limit);
        $offset < 0 && $offset = 0;
        ($limit < 1 || $limit > 10000) && $limit = 100;
        $this->sql['limit'] = "LIMIT {$offset}, {$limit}";
        return $this;
    }

    /**
     * 分组
     *
     * @param string $column 要设置分组的字段名
     *
     * @return $this
     */
    public function groupBy($column)
    {
        if ($this->sql['groupBy'] == '') {
            $this->sql['groupBy'] = "GROUP BY {$column} ";
        } else {
            $this->sql['groupBy'] .= ",{$column} ";
        }
        return $this;
    }

    /**
     * where 语句组装工厂
     *
     * @param string $column 如 id  user.id (这边的user为表别名如表pre_user as user 这边用user而非带前缀的原表名)
     * @param array|int|string $value 值
     * @param string $operator 操作符
     * @throws \Exception
     */
    public function conditionFactory($column, $value, $operator = '=')
    {
        if ($this->sql['where'] == '') $this->sql['where'] = 'WHERE ';
        if ($this->whereNeedAddAndOrOr === 1) {
            $this->sql['where'] .= ' AND ';
        } else if ($this->whereNeedAddAndOrOr === 2) {
            $this->sql['where'] .= ' OR ';
        }

        //下一次where操作默认加上AND
        $this->whereNeedAddAndOrOr = 1;

        if ($operator == 'IN' || $operator == 'NOT IN') {
            empty($value) && $value = [0];
            //这边可直接跳过不组装sql，但是为了给用户提示无条件 便于调试还是加上where field in(0)
            $inValue = '(';
            foreach ($value as $val) {
                $inValue .= '%s ,';
                $this->bindParams[] = $val;
            }
            $this->sql['where'] .= "{$column} {$operator} " . rtrim($inValue, ',') . ') ';
        } elseif ($operator == 'BETWEEN' || $operator == 'NOT BETWEEN') {
            $betweenValue = '%s AND %s ';
            $this->bindParams[] = $value[0];
            $this->bindParams[] = $value[1];
            $this->sql['where'] .= "{$column} {$operator} {$betweenValue}";
        } else if ($operator == 'IS NULL' || $operator == 'IS NOT NULL') {
            $this->sql['where'] .= "{$column} {$operator}";
        } else if($operator == 'ORIGINAL'){

            $this->sql['where'] .= "{$column}";
        }else {
            $this->bindParams[] = $value;
            $value = '%s';
            $this->sql['where'] .= "{$column} {$operator} {$value} ";
        }
    }



    /**
     * join内联结
     *
     * @param string|array $table 表名 要取别名时使用 [不带前缀表名 => 别名]
     * @param string $on 联结的条件 如：'c.cid = a.cid'
     * @param mixed $tablePrefix 表前缀
     *
     * @return $this
     */
    public function join($table, $on, $tablePrefix = null)
    {
        is_null($tablePrefix) && $tablePrefix = $this->tablePrefix;

        $this->table($table, $tablePrefix);
        $hasAlias = is_array($table) ? true : false;

        $tableName = $tablePrefix . ($hasAlias ? key($table) : $table);
        $this->join[count($this->table) - 1 . '_' . $tableName] = is_array($on) ? $this->parseOn($table, $on) : addslashes($on);
        return $this;
    }

    /**
     * leftJoin左联结
     *
     * @param string|array $table 表名 要取别名时使用 [不带前缀表名 => 别名]
     * @param string $on 联结的条件 如：'c.cid = a.cid'
     * @param mixed $tablePrefix 表前缀
     *
     * @return $this
     */
    public function leftJoin($table, $on, $tablePrefix = null)
    {
        is_null($tablePrefix) && $tablePrefix = $this->tablePrefix;

        $this->table($table, $tablePrefix);
        $hasAlias = is_array($table) ? true : false;

        $tableName = $tablePrefix . ($hasAlias ? key($table) : $table);
        $this->leftJoin[count($this->table) - 1 . '_' . $tableName] = is_array($on) ? $this->parseOn($table, $on) : addslashes($on);
        return $this;
    }

    /**
     * rightJoin右联结
     *
     * @param string|array $table 表名 要取别名时使用 [不带前缀表名 => 别名]
     * @param string $on 联结的条件 如：'c.cid = a.cid'
     * @param mixed $tablePrefix 表前缀
     *
     * @return $this
     */
    public function rightJoin($table, $on, $tablePrefix = null)
    {
        is_null($tablePrefix) && $tablePrefix = $this->tablePrefix;

        $this->table($table, $tablePrefix);
        $hasAlias = is_array($table) ? true : false;

        $tableName = $tablePrefix . ($hasAlias ? key($table) : $table);
        $this->rightJoin[count($this->table) - 1 . '_' . $tableName] = is_array($on) ? $this->parseOn($table, $on) : addslashes($on);
        return $this;
    }

    /**
     * union联结
     *
     * @param string|array $sql 要union的sql
     * @param bool $all 是否为union all
     *
     * @return $this
     */
    public function union($sql, $all = false)
    {
        if (is_array($sql)) {
            foreach ($sql as $s) {
                $this->union .= $all ? ' UNION ALL ' : ' UNION ';
                $this->union .= $this->filterUnionSql($s);
            }
        } else {
            $this->union .= $all ? ' UNION ALL ' : ' UNION ';
            $this->union .= $this->filterUnionSql($sql) . ' ';
        }
        return $this;
    }

    protected function filterUnionSql($sql)
    {
        return str_ireplace([
            'insert', "update", "delete", "\/\*", "\.\.\/", "\.\/", "union", "into", "load_file", "outfile"
        ],
            ["", "", "", "", "", "", "", "", "", ""],
            $sql);
    }

    /**
     * 解析联结的on参数
     *
     * @param string $table 要联结的表名
     * @param array $on ['on条件1', 'on条件2' => true] on条件为数字索引时多条件默认为and为非数字引时 条件=>true为and 条件=>false为or
     *
     * @return string
     */
    protected function parseOn(&$table, $on)
    {
        if (empty($on)) {
            throw new \InvalidArgumentException(Lang::get('_DB_PARAM_ERROR_PARSE_ON_', $table));
        }
        $result = '';
        foreach ($on as $key => $val) {
            if (is_numeric($key)) {
                $result == '' || $result .= ' AND ';
                $result .= $val;
            } else {
                $result == '' || $result .= ($val === true ? ' AND ' : ' OR ');
                $result .= $key;
            }
        }
        return addslashes($result); //on条件是程序员自己写死的表字段名不存在注入以防万一还是过滤一下
    }

    public function paramsAutoReset($autoReset = true, $alwaysClearTable = true, $alwaysClearColumns = true)
    {
        $this->paramsAutoReset = $autoReset;
        $this->alwaysClearTable = $alwaysClearTable;
        $this->alwaysClearColumns = $alwaysClearColumns;
        return $this;
    }


    /**
     * orm参数重置
     *
     */
    protected function reset()
    {
        if (!$this->paramsAutoReset) {
            $this->alwaysClearColumns && $this->sql['columns'] = '';
            if ($this->alwaysClearTable) {
                $this->table = []; //操作的表
                $this->join = []; //是否内联
                $this->leftJoin = []; //是否左联结
                $this->rightJoin = []; //是否右联
            }
            return;
        }

        $this->sql = [  //sql组装
            'where' => '',
            'columns' => '',
            'limit' => '',
            'orderBy' => '',
            'groupBy' => '',
            'having' => '',
        ];

        $this->table = []; //操作的表
        $this->join = []; //是否内联
        $this->leftJoin = []; //是否左联结
        $this->rightJoin = []; //是否右联
        $this->whereNeedAddAndOrOr = 0;
    }

    /**
     * 清空绑定的参数
     *
     */
    protected function clearBindParams()
    {
        if ($this->paramsAutoReset) {
            $this->bindParams = [];
        }
    }

    /**
     * SQL语句条件组装
     *
     * @param array $arr 要组装的数组
     *
     * @return string
     */
    protected function arrToCondition($arr)
    {
        $s = $p = '';
        $params = [];
        foreach ($arr as $k => $v) {
            if (is_array($v)) { //自增或自减
                switch (key($v)) {
                    case 'inc':
                        $p = "`{$k}`= `{$k}`+" . abs(intval(current($v)));
                        break;
                    case 'dec':
                        $p = "`{$k}`= `{$k}`-" . abs(intval(current($v)));
                        break;
                    case 'func':
                        $func = strtoupper(key(current($v)));
                        $funcParams = current(current($v));
                        foreach ($funcParams as $key => $val) {
                            if (substr($val, 0, 1) !== '`') {
                                $funcParams[$key] = '%s';
                                $params[] = $val;
                            }
                        }
                        $p = "`{$k}`= {$func}(" . implode($funcParams, ',') . ')';
                        break;
                    default ://计算类型
                        $conKey = key($v);
                        if (!in_array(key(current($v)), ['+', '-', '*', '/', '%', '^', '&', '|', '<<', '>>', '~'])) {
                            throw new \InvalidArgumentException(Lang::get('_PARSE_UPDATE_SQL_PARAMS_ERROR_'));
                        }
                        $p = "`{$k}`= `{$conKey}`" . key(current($v)) . abs(intval(current(current($v))));
                        break;
                }
            } else {
                $p = "`{$k}`= %s";
                $params[] = $v;
            }

            $s .= (empty($s) ? '' : ',') . $p;
        }
        $this->bindParams = array_merge($params, $this->bindParams);
        return $s;
    }

    /**
     * 根据表名获取cache版本号
     *
     * @param string $table
     *
     * @return mixed
     */
    public function getCacheVer($table)
    {
        if (!$this->openCache) {
            return '';
        }

        $version = Model::getInstance()->cache()->get($this->conf['mark'] . '_db_cache_version_' . $table);
        if (!$version) {
            $version = microtime(true);
            Model::getInstance()->cache()->set($this->conf['mark'] . '_db_cache_version_' . $table, $version, $this->conf['cache_expire']);
        }
        return $version;
    }

    public function __get($db)
    {
        if ($db == 'rlink') {
            return $this->wlink;
        } else if ($db == 'wlink') {
            if($this->_iniconf['other']==1){
                $this->tablePrefix = $this->_iniconf['pre'];
                // echo "init<br/>";
                if(FALSE == (self::$_instance1 instanceof \PDO)) {
                    self::$_instance1 = new \PDO($this->_iniconf['dbtype'] . ":host=" . $this->_iniconf['host'] . ";dbname=" . $this->_iniconf['dbname'], $this->_iniconf['user'], $this->_iniconf['pass'],array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); //初始化一个PDO对象
                }
                //$this->_iniconf = $config;
                $this->wlink = self::$_instance1;
            }else{
                $this->tablePrefix = $this->_iniconf['pre'];
                //  echo "init2<br/>";

                if(FALSE == (self::$_instance2 instanceof \PDO)){

                    self::$_instance2= new \PDO($this->_iniconf['dbtype'].":host=".$this->_iniconf['host'].";dbname=".$this->_iniconf['dbname'], $this->_iniconf['user'], $this->_iniconf['pass'],array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); //初始化一个PDO对象
                }
                $this->wlink = self::$_instance2;
            }
           //  self::$_instance2= new \PDO($config['dbtype'].":host=".$config['host'].";dbname=".$config['dbname'], $config['user'], $config['pass']); //初始化一个PDO对象

            return $this->wlink;
        }
    }

    /**
     * 排序
     *
     * @param string $column 要排序的字段
     * @param string $order 方向,默认为正序
     *
     * @return $this
     */
    public function orderBy($column, $order = 'ASC')
    {
        if ($this->sql['orderBy'] == '') {
            $this->sql['orderBy'] = " ORDER BY {$column} {$order} ";
        } else {
            $this->sql['orderBy'] .= ", {$column} {$order} ";
        }
        return $this;
    }


    /**
     * where 用户输入过滤
     *
     * @param string $val
     *
     * @return string
     */
    protected function filterLike($val)
    {
        return str_replace(['_', '%'], ['\_', '\%'], $val);
    }

    protected function parseKey($key, $and = true, $noCondition = false, $noTable = false)
    {
        $condition = '';
        $arr = explode('-', $key);
        $len = count($arr);
        for ($i = 1; $i < $len; $i += 2) {
            isset($arr[$i + 1]) && $condition .= ($condition ? ($and ? ' AND ' : ' OR ') : '') . "`{$arr[$i]}` = %s";
            $this->bindParams[] = $arr[$i + 1];
        }
        $table = strtolower($arr[0]);
        if (empty($table) && !$noTable) {
            throw new \InvalidArgumentException(Lang::get('_DB_PARAM_ERROR_PARSE_KEY_', $key, 'table'));
        }
        if (empty($condition) && !$noCondition) {
            throw new \InvalidArgumentException(Lang::get('_DB_PARAM_ERROR_PARSE_KEY_', $key, 'condition'));
        }
        empty($condition) || $condition = "($condition)";
        return [$table, $condition];
    }


}