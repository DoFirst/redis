<?php

class M_Redis
{
    static private $_host;//host
    static private $_port;//端口
    static private $_redis;//连接对象
    static private $_db;//数据库
    static private $_auth = '';
    static private $_status = false;//连接状态


    //连接redis，并选择某一个数据库
    public static function connect($config = [])
    {
        if(self::$_status) return true;

        if (!extension_loaded('redis')) {
            die('缺失的redis扩展');
        }

        self::$_host = isset($config['host']) ? $config['host'] : '127.0.0.1';
        self::$_port = isset($config['port']) ? $config['port'] : 6379;
        self::$_auth = isset($config['auth']) ? $config['auth'] : '';
        self::$_db = isset($config['db']) ? $config['auth'] : 0;

        if(self::$_redis == null){
            self::$_redis = new redis();
        }

        if (self::$_redis->connect(self::$_host, self::$_port)) {
            self::$_status = true;
        }

        if(self::$_auth) self::$_redis->auth(self::$_auth);

        return self::$_status;

    }

    /**
     * 关闭连接
     */
    public static function close()
    {
        self::$_redis->close();
        self::$_status = false;
    }

    //*********************************************** 字符串操作函数 **********************************************
    /**
     * 判断key是否存在
     * @param $key
     * @return bool
     */
    public static function str_exists($key)
    {
        if (!$key) return false;
        if (!self::connect()) return false;
        $is_exists = self::$_redis->exists($key);
        return $is_exists;
    }

    /**
     * 设置字符串
     * @param $key
     * @param $value
     * @param int $time
     * @return bool
     */
    public static function str_set($key, $value, $time = 0)
    {
        if (!self::connect()) return false;
        return $time > 0 ? self::$_redis->setex($key, $value, $time) : self::$_redis->set($key, $value);
    }

    /**
     * 一次设置多个键值对：成功返回true
     * @param array $parameter
     * @return bool
     */
    public static function str_mset($parameter = [])
    {
        if (!self::connect()) return false;
        if (!is_array($parameter)) return false;
        return self::$_redis->mset($parameter);
    }


    /**
     * 设置字符串：只有在 key 不存在时设置 key 的值。
     * @param $key
     * @param $value
     * @return bool
     */
    public static function str_setnx($key, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->setnx($key, $value);
    }

    /**
     * 设置新值，返回旧值：若key不存在则设置值，返回false
     * @param $key
     * @param $value
     * @return bool
     */
    public static function str_getset($key, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->getSet($key, $value);
    }

    /**
     * 获取键值：成功返回String类型键值，若key不存在或不是String类型则返回false
     * @param $key
     * @return bool
     */
    public static function str_get($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->get($key);
    }

    /**
     * 从某个key所存储的字符串的指定偏移量开始，替换为另一指定字符串，成功返回替换后新字符串的长度。
     * @param $key
     * @param $offset
     * @param $value
     * @return bool
     */
    public static function str_setRange($key, $offset, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->setRange($key, $offset, $value);
    }

    /**
     * 获取存储在指定key中字符串的子字符串。
     * @param $key
     * @param $start
     * @param $end
     * @return bool
     */
    public static function str_getRange($key, $start, $end)
    {
        if (!self::connect()) return false;
        return self::$_redis->getRange($key, $start, $end);
    }

    /**
     * 一次获取多个key的值：返回一个键值对数组，其中不存在的key值为false
     * @param $parameter
     * @return bool
     */
    public static function str_mget($parameter)
    {
        if (!self::connect()) return false;
        if (!is_array($parameter)) return false;
        return self::$_redis->mget($parameter);
    }


    /**
     * 设置指定key的值及其过期时间，单位：秒。
     * 参数：键名，过期时间，键值。成功返回true。
     * @param $key
     * @param $time
     * @param $value
     * @return bool
     */
    public static function str_setex($key, $time, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->setex($key, $time, $value);
    }

    public static function str_delete(...$key)
    {
        if (!self::connect()) return false;
        return self::$_redis->delete(...$key);
    }

    /**
     * 以毫秒为单位设置指定key的值和过期时间。成功返回true。
     * @param $key
     * @param $time
     * @param $value
     * @return bool
     */
    public static function str_psetex($key, $time, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->psetex($key, $time, $value);
    }

    /**
     * setnx命令的批量操作。只有在给定所有key都不存在的时候才能设置成功，只要其中一个key存在，所有key都无法设置成功。
     * @param $parameter
     * @return bool
     */
    public static function str_msetnx($parameter)
    {
        if (!self::connect()) return false;
        if(!is_array($parameter)) return false;
        return self::$_redis->msetnx($parameter);
    }

    /**
     * 获取指定key存储的字符串的长度，key不存在返回0，不为字符串返回false。
     * @param $key
     * @return bool
     */
    public static function str_strlen($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->strlen($key);
    }


    /**
     * 将指定key存储的数字值增加1。若key不存在会先初始化为0再增加1，若key存储的不是整数值则返回false。成功返回key新值。
     * @param $key
     * @param $num
     * @return bool
     */
    public static function str_incr($key, $num=1)
    {
        if (!self::connect()) return false;
        return $num > 1 ? self::$_redis->incrBy($key, $num) : self::$_redis->incr($key);
    }

    /**
     * 给指定key存储的数字值增加指定浮点数增量。
     * @param $key
     * @param $float
     * @return bool
     */
    public static function str_incrByFloat($key, $float)
    {
        if (!self::connect()) return false;
        return self::$_redis->incrByFloat($key, $float);
    }

    /**
     * 将指定key存储的数字减去指定减量值， 默认为1。
     * @param $key
     * @param int $num
     * @return bool
     */
    public static function str_decr($key, $num = 1)
    {
        if (!self::connect()) return false;
        if($num != 1){
            return self::$_redis->decrBy($key, $num);
        }else{
            return self::$_redis->decr($key);
        }
    }

    /**
     * 为指定key追加值到原值末尾，若key不存在则相对于set()函数。
     * @param $key
     * @param $str
     * @return bool
     */
    public static function str_append($key, $str)
    {
        if (!self::connect()) return false;
        return self::$_redis->append($key, $str);
    }

    //**************************************** 字符串操作函数end ******************************************





    //**************************************** Hash操作函数 *****************************************
    /**
     * 为hash表中的字段赋值。成功返回1，失败返回0。若hash表不存在会先创建表再赋值，若字段已存在会覆盖旧值。
     * @param $key
     * @param $value
     * @return bool
     */
    public static function hash_hSet($key, $field, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->hSet($key, $field, $value);
    }

    /**
     * 获取hash表中指定字段的值。若hash表不存在则返回false。
     * @param $key
     * @param $field
     * @return bool
     */
    public static function hash_hGet($key, $field)
    {
        if (!self::connect()) return false;
        return self::$_redis->hGet($key, $field);
    }

    /**
     * 查看hash表的某个字段是否存在，存在返回true，否则返回false。
     * @param $key
     * @param $field
     * @return bool
     */
    public static function hash_hExists($key, $field)
    {
        if (!self::connect()) return false;
        return self::$_redis->hExists($key, $field);
    }

    /**
     * 删除hash表的一个字段，不支持删除多个字段。成功返回1，否则返回0。
     * @param $key
     * @param $field
     * @return bool
     */
    public static function hash_hDel($key, $field)
    {
        if (!self::connect()) return false;
        return self::$_redis->hDel($key, $field);
    }

    /**
     * 同时设置某个hash表的多个字段值。成功返回true。
     * @param $key
     * @param $parameter
     * @return bool
     */
    public static function hash_hMset($key, $parameter)
    {
        if (!self::connect()) return false;
        if(!is_array($parameter)) return false;
        return self::$_redis->hMset($key, $parameter);
    }

    /**
     * 同时获取某个hash表的多个字段值。其中不存在的字段值为false。
     * @param $key
     * @param $parameter
     * @return bool
     */
    public static function hash_hMget($key, $parameter)
    {
        if (!self::connect()) return false;
        if(!is_array($parameter)) return false;
        return self::$_redis->hMget($key, $parameter);
    }

    /**
     * 获取某个hash表所有的字段和值。
     * @param $key
     * @return bool
     */
    public static function hash_hGetAll($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->hGetAll($key);
    }

    /**
     * 获取某个hash表所有字段名。hash表不存在时返回空数组，key不为hash表时返回false。
     * @param $key
     * @return bool
     */
    public static function hash_hKeys($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->hKeys($key);
    }

    /**
     * 获取某个hash表所有字段值。
     * @param $keys
     * @return bool
     */
    public static function hash_hVals($keys)
    {
        if (!self::connect()) return false;
        return self::$_redis->hVals($keys);
    }

    /**
     * 为hash表中不存在的字段赋值。若hash表不存在则先创建，若字段已存在则不做任何操作。设置成功返回true，否则返回false。
     * @param $key
     * @param $field
     * @param $value
     * @return bool
     */
    public static function hash_hSetNx($key, $field, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->hSetNx($key, $field, $value);
    }

    /**
     * 获取某个hash表的字段数量。若hash表不存在返回0，若key不为hash表则返回false。
     * @param $key
     * @return bool
     */
    public static function hash_hLen($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->hLen($key);
    }

    /**
     * 为hash表中的指定字段加上指定增量值，若增量值为负数则相当于减法操作。若hash表不存在则先创建，若字段不存在则先初始化值为0再进行操作，若字段值为字符串则返回false。设置成功返回字段新值。
     * @param $key
     * @param $field
     * @param $num
     * @return bool
     */
    public static function hash_hIncrBy($key, $field, $num)
    {
        if (!self::connect()) return false;
        return self::$_redis->hIncrBy($key, $field, $num);
    }

    //**************************************** Hash操作函数end **************************************



    //**************************************** 列表操作函数 ******************************************
    /**
     * 从list头部插入一个值。
     * @param $key
     * @param $value
     * @return bool
     */
    public static function list_lPush($key, $value)
    {
         if (!self::connect()) return false;
         return self::$_redis->lPush($key, $value);
    }

    /**
     * 从list尾部插入一个值。
     * @param $key
     * @param $value
     * @return bool
     */
    public static function list_rPush($key, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->rPush($key, $value);
    }

    /**
     * 获取列表指定区间中的元素。0表示列表第一个元素，-1表示最后一个元素，-2表示倒数第二个元素。
     * @param $key
     * @param int $start
     * @param int $end
     * @return bool
     */
    public static function list_lrange($key, $start=0, $end=-1)
    {
        if (!self::connect()) return false;
        return self::$_redis->lrange($key, $start, $end);
    }

    /**
     * 将一个插入已存在的列表头部，列表不存在时操作无效。
     * @param $key
     * @param $value
     * @return bool
     */
    public static function list_lPushx($key, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->lPushx($key, $value);
    }

    /**
     * 将一个或多个值插入已存在的列表尾部，列表不存在时操作无效。
     * @param $key
     * @param $value
     * @return bool
     */
    public static function list_rPushx($key, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->rPushx($key, $value);
    }

    /**
     * 移除并返回列表的第一个元素，若key不存在或不是列表则返回false。
     * @param $key
     * @return bool
     */
    public static function list_lPop($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->lPop($key);
    }

    /**
     * 移除并返回列表的最后一个元素，若key不存在或不是列表则返回false。
     * @param $key
     * @return bool
     */
    public static function list_rPop($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->rPop($key);
    }

    /**
     * 移除并获取列表的第一个元素。如果列表没有元素则会阻塞列表直到等待超时或发现可弹出元素为止。
     * 参数：key，超时时间（单位：秒）
     * 返回值：[0=>key,1=>value]，超时返回[]
     * @param $key
     * @param $time
     * @return bool
     */
    public static function list_blPop($key, $time)
    {
        if (!self::connect()) return false;
        return self::$_redis->blPop($key, $time);
    }

    /**
     * 移除并获取列表的最后一个元素。如果列表没有元素则会阻塞列表直到等待超时或发现可弹出元素为止。
     * 参数：key，超时时间（单位：秒）
     * 返回值：[0=>key,1=>value]，超时返回[]
     * @param $key
     * @param $time
     * @return bool
     */
    public static function list_brPop($key, $time)
    {

        if (!self::connect()) return false;
        return self::$_redis->brPop($key, $time);
    }

    /**
     * 移除列表中最后一个元素，将其插入另一个列表头部，并返回这个元素。若源列表没有元素则返回false。
     * @param $key
     * @param $key2
     * @return bool
     */
    public static function list_rpoplpush($key,$key2)
    {
        if (!self::connect()) return false;
        return self::$_redis->rpoplpush($key,$key2);
    }

    /**
     * 移除列表中最后一个元素，将其插入另一个列表头部，并返回这个元素。如果列表没有元素则会阻塞列表直到等待超时或发现可弹出元素为止。
     * 参数：源列表，目标列表，超时时间（单位：秒）
     * 超时返回false
     * @param $key
     * @param $key2
     * @param $time
     * @return bool
     */
    public static function list_brpoplpush($key, $key2, $time)
    {
        if (!self::connect()) return false;
        return self::$_redis->brpoplpush($key, $key2, $time);
    }

    /**
     * 返回列表长度
     * @param $key
     * @return bool
     */
    public static function list_lLen($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->lLen($key);
    }

    /**
     * 通过索引获取列表中的元素。若索引超出列表范围则返回false。
     * @param $key
     * @param $index
     * @return bool
     */
    public static function list_lindex($key, $index)
    {
        if (!self::connect()) return false;
        return self::$_redis->lindex($key, $index);
    }

    /**
     * 通过索引设置列表中元素的值。若是索引超出范围，或对一个空列表进行lset操作，则返回false。
     * @param $key
     * @param $index
     * @param $value
     * @return bool
     */
    public static function list_lSet($key, $index, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->lSet($key, $index, $value);
    }

    /**
     * 在列表中指定元素前或后面插入元素。若指定元素不在列表中，或列表不存在时，不执行任何操作。
     * 参数：列表key，Redis::AFTER或Redis::BEFORE，基准元素，插入元素
     * 返回值：插入成功返回插入后列表元素个数，若基准元素不存在返回-1，若key不存在返回0，若key不是列表返回false。
     * @param $key
     * @param $off
     * @param $base
     * @param $value
     * @return bool
     */
    public static function list_lInsert($key, $off, $base, $value)
    {
        if (!self::connect()) return false;
        self::$_redis->lInsert($key, $off, $base, $value);
    }

    /**
     * 根据第三个参数count的值，移除列表中与参数value相等的元素。
     * count > 0 : 从表头开始向表尾搜索，移除与value相等的元素，数量为count。
     * count < 0 : 从表尾开始向表头搜索，移除与value相等的元素，数量为count的绝对值。
     * count = 0 : 移除表中所有与value相等的值。
     * 返回实际删除元素个数
     * @param $key
     * @param $value
     * @param $index
     * @return bool
     */
    public static function list_lrem($key, $value, $index)
    {
        if (!self::connect()) return false;
        self::$_redis->lrem($key, $value, $index);
    }

    /**
     * 对一个列表进行修剪，只保留指定区间的元素，其他元素都删除。成功返回true。
     * @param $key
     * @param $start
     * @param $end
     * @return bool
     */
    public static function list_ltrim($key, $start, $end)
    {
        if (!self::connect()) return false;
        return self::$_redis->ltrim($key, $start, $end);
    }
    //**************************************** 列表操作函数end ***************************************



    //**************************************** 集合操作函数 ******************************************
    /**
     * 将一个元素加入集合，已经存在集合中的元素则忽略。若集合不存在则先创建，若key不是集合类型则返回false，若元素已存在返回0，插入成功返回1。
     * @param $key
     * @param $value
     * @return bool
     */
    public static function set_sAdd($key, $value)
    {
        if (!self::connect()) return false;
        return self::$_redis->sAdd($key, $value);
    }

    /**
     * 返回集合中所有的成员
     * @param $key
     * @return bool
     */
    public static function set_sMembers($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->sMembers($key);
    }

    /**
     * 判断指定元素是否是指定集合的成员，是返回true，否则返回false。
     * @param $key
     * @param $member
     * @return bool
     */
    public static function set_sismember($key, $member)
    {
        if (!self::connect()) return false;
        return self::$_redis->sismember($key, $member);
    }

    /**
     * 返回集合中元素的数量。
     * @param $key
     * @return bool
     */
    public static function set_scard($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->scard($key);
    }

    /**
     * 移除并返回集合中的一个随机元素。
     * @param $key
     * @return bool
     */
    public static function set_sPop($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->sPop($key);
    }

    /**
     * 返回集合中的一个或多个随机成员元素，返回元素的数量和情况由函数的第二个参数count决定：
     * 如果count为正数，且小于集合基数，那么命令返回一个包含count个元素的数组，数组中的元素各不相同。
     * 如果count大于等于集合基数，那么返回整个集合。
     * 如果count为负数，那么命令返回一个数组，数组中的元素可能会重复出现多次，而数组的长度为count的绝对值。
     * @param $key
     * @param $num
     * @return bool
     */
    public static function set_sRandMember($key, $num)
    {
        if (!self::connect()) return false;
        return self::$_redis->sRandMember($key, $num);
    }

    /**
     * 移除集合中指定的一个元素，忽略不存在的元素。删除成功返回1，否则返回0。
     * @param $key
     * @param $member
     * @return bool
     */
    public static function set_srem($key, $member)
    {
        if (!self::connect()) return false;
        return self::$_redis->srem($key, $member);
    }

    /**
     * 迭代集合中的元素。
     * 参数：key，迭代器变量，匹配模式，每次返回元素数量（默认为10个）
     * @param $key
     * @param $count
     * @param $mod
     * @param $num
     * @return bool
     */
    public static function set_sscan($key, $count, $mod, $num)
    {
        if (!self::connect()) return false;
        return self::$_redis->sscan($key, $count, $mod, $num);
    }

    /**
     * 将指定成员从一个源集合移动到一个目的集合。若源集合不存在或不包含指定元素则不做任何操作，返回false。
     * 参数：源集合，目标集合，移动元素
     * @param $sourceSet
     * @param $targetSet
     * @param $member
     * @return bool
     */
    public static function set_sMove($sourceSet, $targetSet, $member)
    {
        if (!self::connect()) return false;
        return self::$_redis->sMove($sourceSet, $targetSet, $member);
    }

    /**
     * 返回所有给定集合之间的差集，不存在的集合视为空集。
     * @param array ...$set
     * @return bool
     */
    public static function set_sDiff(...$set)
    {
        if (!self::connect()) return false;
        return self::$_redis->sDiff(...$set);
    }

    /**
     * 将所有给定集合之间的差集存储在指定的目的集合中。若目的集合已存在则覆盖它。返回差集元素个数。
     * 参数：第一个参数为目标集合，存储差集。
     * @param array ...$set
     * @return bool
     */
    public static function set_sDiffStore(...$set)
    {
        if (!self::connect()) return false;
        return self::$_redis->sDiffStore(...$set);
    }

    /**
     * 返回所有给定集合的交集，不存在的集合视为空集。
     * @param array ...$set
     * @return bool
     */
    public static function set_sInter(...$set)
    {
        if (!self::connect()) return false;
        return self::$_redis->sInter(...$set);
    }

    /**
     * 将所有给定集合的交集存储在指定的目的集合中。若目的集合已存在则覆盖它。返回交集元素个数。
     * 参数：第一个参数为目标集合，存储交集。
     * @param array ...$set
     * @return bool
     */
    public static function set_sInterStore(...$set)
    {
        if (!self::connect()) return false;
        return self::$_redis->sInterStore(...$set);
    }

    /**
     * 返回所有给定集合的并集，不存在的集合视为空集。
     * @param array ...$set
     * @return bool
     */
    public static function set_sUnion(...$set)
    {
        if (!self::connect()) return false;
        return self::$_redis->sUnion(...$set);
    }

    /**
     * 将所有给定集合的并集存储在指定的目的集合中。若目的集合已存在则覆盖它。返回并集元素个数。
     * 参数：第一个参数为目标集合，存储并集。
     * @param array ...$set
     * @return bool
     */
    public static function set_sUnionStore(...$set)
    {
        if (!self::connect()) return false;
        return self::$_redis->sUnionStore(...$set);
    }
    //**************************************** 集合操作函数end ***************************************



    //**************************************** 有序集合操作函数 ******************************************
    /**
     * 将一个或多个成员元素及其分数值加入到有序集当中。如果某个成员已经是有序集的成员，则更新这个成员的分数值，并通过重新插入这个成员元素，来保证该成员在正确的位置上。分数值可以是整数值或双精度浮点数。
     * @param $key
     * @param array ...$set
     * @return bool
     */
    public static function zset_zAdd($key, ...$set)
    {
        if (!self::connect()) return false;
        return self::$_redis->zAdd($key, ...$set);
    }

    /**
     * 通过索引区间返回有序集合成指定区间内的成员
     * @param $key
     * @param $start
     * @param $end
     * @param bool $withScores
     * @return bool
     */
    public static function zset_zRange($key, $start, $end, $withScores = true)
    {
        if (!self::connect()) return false;
        return self::$_redis->zRrange($key, $start, $end, $withScores);
    }

    /**
     * 返回有序集中指定区间内的成员。成员按分数值递减排序，分数值相同的则按字典序的逆序来排序。
     * @param $key
     * @param $start
     * @param $end
     * @param bool $withScores
     * @return bool
     */
    public static function zset_zReverseRange($key, $start, $end, $withScores = true)
    {
        if (!self::connect()) return false;
        return self::$_redis->zReverseRange($key, $start, $end, $withScores);
    }

    /**
     * 返回有序集中指定分数区间的成员列表，按分数值递增排序，分数值相同的则按字典序来排序。默认使用闭区间。
     * @param $key
     * @param $scoreStart
     * @param $scoreEnd
     * @param bool $withScores
     * @return bool
     */
    public static function zset_zRangeByScore($key, $scoreStart, $scoreEnd, $withScores = true)
    {
        if (!self::connect()) return false;
        return self::$_redis->zRangeByScore($key, $scoreStart, $scoreEnd, $withScores);
    }

    /**
     * 返回有序集中指定分数区间的成员列表，按分数值递减排序，分数值相同的则按字典序的逆序来排序。注意，区间表示的时候大值在前，小值在后，默认使用闭区间。
     * @param $key
     * @param $scoresStart
     * @param $scoreEnd
     * @param bool $widthScores
     * @return bool
     */
    public static function zset_zRevRangeByScore($key, $scoresStart, $scoreEnd, $widthScores = true)
    {
        if (!self::connect()) return false;
        return self::$_redis->zRevRangeByScore($key, $scoresStart, $scoreEnd, $widthScores);
    }

    /**
     * 迭代有序集合中的元素。
     * @param $key
     * @param $iterator
     * @param string $pattern
     * @param int $count
     * @return bool
     */
    public static function zset_zscan($key, $iterator, $pattern = '', $count = 0)
    {
        if (!self::connect()) return false;
        return self::$_redis->zscan($key, $iterator, $pattern, $count);
    }

    /**
     * 返回指定有序集的元素数量
     * @param $key
     * @return bool
     */
    public static function zset_zCard($key)
    {
        if (!self::connect()) return false;
        return self::$_redis->zCard($key);
    }

    /**
     * 返回有序集中指定分数区间的成员数量
     * @param $key
     * @param $start
     * @param $end
     * @return bool
     */
    public static function zset_zCount($key, $start, $end)
    {
        if (!self::connect()) return false;
        return self::$_redis->zCount($key, $start, $end);
    }

    /**
     * 返回有序集中指定成员的分数值
     * @param $key
     * @param $member
     * @return bool
     */
    public static function zset_zScore($key, $member)
    {
        if (!self::connect()) return false;
        return self::$_redis->zScore($key, $member);
    }

    /**
     * 返回有序集中指定成员的排名,(递增排序方式)
     * @param $key
     * @param $member
     * @return bool
     */
    public static function zset_zRank($key, $member)
    {
        if (!self::connect()) return false;
        return self::$_redis->zRank($key, $member);
    }

    /**
     * 返回有序集中指定成员的排名, (降序排列方式)
     * @param $key
     * @param $member
     * @return bool
     */
    public static function zset_RevRank($key, $member)
    {
        if (!self::connect()) return false;
        return self::$_redis->RevRank($key, $member);
    }

    /**
     * 移除有序集中的一个或多个成员，忽略不存在的成员。返回删除的元素个数。
     * @param $key
     * @param array ...$members
     * @return bool
     */
    public static function zset_zRem($key, ...$members)
    {
        if (!self::connect()) return false;
        return self::$_redis->zRem($key, ...$members);
    }

    /**
     * 移除有序集中指定排名区间的所有成员
     * @param $key
     * @param $start
     * @param $end
     * @return bool
     */
    public static function zset_zRemRangeByRank($key, $start, $end )
    {
        if (!self::connect()) return false;
        return self::$_redis->zRemRangeByRank($key, $start, $end);
    }

    /**
     * 移除有序集中指定分数值区间的所有成员
     * @param $key
     * @param $start
     * @param $end
     * @return bool
     */
    public static function zset_zRemRangeByScore($key, $start, $end)
    {
        if (!self::connect()) return false;
        return self::$_redis->zRemRangeByScore($key, $start, $end);
    }

    /**
     * 对有序集中指定成员的分数值增加指定增量值。若为负数则做减法，若有序集不存在则先创建，若有序集中没有对应成员则先添加，最后再操作。
     * @param $key
     * @param $value
     * @param $member
     * @return bool
     */
    public static function zset_zIncrBy($key, $value, $member )
    {
        if (!self::connect()) return false;
        return self::$_redis->zIncrBy($key, $value, $member);
    }

    /**
     * 计算给定一个或多个有序集的交集，并将其存储到一个目的有序集中。结果集中某个成员的分数值是所有给定集下该成员分数值之和。
     * @param array ...$set
     * @return bool
     */
    public static function zset_zinterstore(...$set)
    {
        if (!self::connect()) return false;
        return self::$_redis->zinterstore(...$set);
    }

    /**
     * 计算给定一个或多个有序集的并集，并将其存储到一个目的有序集中。结果集中某个成员的分数值是所有给定集下该成员分数值之和。
     * @param array ...$set
     * @return bool
     */
    public static function zset_zunionstore(...$set)
    {
        if (!self::connect()) return false;
        return self::$_redis->zunionstore(...$set);
    }
    //**************************************** 有序集合操作函数end ***************************************

    public static function getStatus()
    {
        return self::$_status;
    }

    public static function getDataBase()
    {
        return self::$_db;
    }

    public static function getHost()
    {
        return self::$_db;
    }

    public static function getAuth()
    {
        return self::$_auth;
    }

    public static function getPort()
    {
        return self::$_port;
    }

}