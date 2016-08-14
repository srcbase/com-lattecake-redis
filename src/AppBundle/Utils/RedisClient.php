<?php
/**
 * Created by PhpStorm.
 * User: LatteCake
 * Date: 16/7/28
 * Time: 下午12:07
 * File: RedisClient.php
 */

namespace AppBundle\Utils;


use AppBundle\Entity\Address;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class RedisClient
 * @package AppBundle\Utils
 */
class RedisClient
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var IP
     */
    private $IP;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    public $allFilter = ["DEL", "DUMP", "EXISTS", "EXPIRE", "EXPIREAT", "KEYS", "MIGRATE", "MOVE", "OBJECT", "PERSIST", "PEXPIRE", "PEXPIREAT", "PTTL",
        "RANDOMKEY", "RENAME", "RENAMENX", "RESTORE", "SORT", "TTL", "TYPE", "SCAN", "APPEND", "BITCOUNT", "BITOP", "BITFIELD", "DECR", "DECRBY", "GET",
        "GETBIT", "GETRANGE", "GETSET", "INCR", "INCRBY", "INCRBYFLOAT", "MGET", "MSET", "MSETNX", "PSETEX", "SET", "SETBIT", "SETEX", "SETNX", "SETRANGE",
        "STRLEN", "HDEL", "HEXISTS", "HGET", "HGETALL", "HINCRBY", "HINCRBYFLOAT", "HKEYS", "HLEN", "HMGET", "HMSET", "HSET", "HSETNX", "HVALS", "HSCAN",
        "HSTRLEN", "BLPOP", "BRPOP", "BRPOPLPUSH", "LINDEX", "LINSERT", "LLEN", "LPOP", "LPUSH", "LPUSHX", "LRANGE", "LREM", "LSET", "LTRIM", "RPOP",
        "RPOPLPUSH", "RPUSH", "RPUSHX", "SADD", "SCARD", "SDIFF", "SDIFFSTORE", "SINTER", "SINTERSTORE", "SISMEMBER", "SMEMBERS", "SMOVE", "SPOP",
        "SRANDMEMBER", "SREM", "SUNION", "SUNIONSTORE", "SSCAN", "ZADD", "ZCARD", "ZCOUNT", "ZINCRBY", "ZRANGE", "ZRANGEBYSCORE", "ZRANK", "ZREM",
        "ZREMRANGEBYRANK", "ZREMRANGEBYSCORE", "ZREVRANGE", "ZREVRANGEBYSCORE", "ZREVRANK", "ZSCORE", "ZUNIONSTORE", "ZINTERSTORE", "ZSCAN", "ZRANGEBYLEX",
        "ZLEXCOUNT", "ZREMRANGEBYLEX", "HyperLogLog", "PFADD", "PFCOUNT", "PFMERGE", "GEOADD", "GEOPOS", "GEODIST", "GEORADIUS", "GEORADIUSBYMEMBER",
        "GEOHASH", "PSUBSCRIBE", "PUBLISH", "PUBSUB", "PUNSUBSCRIBE", "SUBSCRIBE", "UNSUBSCRIBE",
        "DISCARD", "EXEC", "MULTI", "UNWATCH", "WATCH", "EVAL", "EVALSHA", "SCRIPT EXISTS", "SCRIPT FLUSH", "SCRIPT KILL", "SCRIPT LOAD",
        "AUTH", "ECHO", "PING", "QUIT", "SELECT",
        "BGREWRITEAOF", "BGSAVE", "CLIENT GETNAME", "CLIENT KILL", "CLIENT LIST", "CLIENT SETNAME", "CONFIG GET", "CONFIG RESETSTAT", "CONFIG REWRITE",
        "CONFIG SET", "DBSIZE", "DEBUG OBJECT", "DEBUG SEGFAULT", "FLUSHALL", "FLUSHDB", "INFO", "LASTSAVE", "MONITOR", "PSYNC", "SAVE", "SHUTDOWN",
        "SLAVEOF", "SLOWLOG", "SYNC", "TIME"];

    /**
     * @var array
     */
    private $filter = [
        'KEYS', 'GET', 'HGET', 'HGETALL', 'DEL', 'AUTH', 'SORT', 'ZADD', 'SADD', 'LPOP', 'HLEN', 'HMSET', 'TTL', 'TYPE', 'INCR', 'HDEL', 'EXISTS',
        'SELECT', 'HEXISTS', 'INCRBY', 'MSET', 'MSETNX', 'SETBIT', 'SETEX', 'SETNX', 'BLPOP', 'QUIT', 'TIME', 'ECHO', 'ZCARD', 'LPUSH', 'LPUSHX', 'RPOP',
        'EXPIRE', 'MIGRATE', 'GETBIT', 'GETRANGE', 'GETSET', 'MGET', 'HMGET', 'RESTORE', 'APPEND', 'HKEYS', 'HSET', 'HSETNX', 'HSTRLEN', 'BRPOP', 'BLPOP',
        'LINDEX', 'LINSERT', 'LLEN', 'LRANGE', 'LREM', 'LSET', 'RPOP', 'LTRIM', 'ZCARD', 'ZCOUNT', 'ZINCRBY', 'ZRANGE', 'PUBLISH', 'PUBSUB', 'EVAL',
        'INFO', 'SAVE'
    ];

    /**
     * RedisClient constructor.
     * @param IP $IP
     * @param Logger $logger
     */
    public function __construct(IP $IP, Logger $logger)
    {
        $this->redis = new \Redis();
        $this->IP = $IP;
        $this->logger = $logger;
    }

    /**
     * @param Address $address
     * @param int $db
     * @return \Redis|JsonResponse
     */
    public function checkConn(Address $address, $db = 0)
    {
        try {
            if (!$this->redis->connect($this->IP->resetIp($address->getIpAddress()), $address->getPort(), 5)) {
//            if (!$this->redis->connect('10.141.4.80', $address->getPort(), 5)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => '连接失败',
                    'errorCode' => 'REDIS_AUTH_00001'
                ], 400, ['Access-Control-Allow-Origin' => '*']);
            }
        } catch (\RedisException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'errorCode' => 'REDIS_AUTH_00001'
            ], 400, ['Access-Control-Allow-Origin' => '*']);
        }

        if ($address->getAuth()) {
            try {
                $this->redis->auth($address->getPassword());
            } catch (\RedisException $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errorCode' => 'REDIS_AUTH_00001'
                ], 400, ['Access-Control-Allow-Origin' => '*']);
            }
        }

        try {

            $this->redis->select($db);
        } catch (\RedisException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'errorCode' => 'REDIS_AUTH_00001'
            ], 400, ['Access-Control-Allow-Origin' => '*']);
        }

        return $this->redis;
    }

    /**
     * @param $filter
     * @return bool
     */
    public function inFilter($filter)
    {
        return in_array(strtoupper(trim($filter)), $this->filter);
    }

    /**
     * @param $key
     * @param array $args
     * @return array
     */
    public function __call($key, array $args)
    {
        $args = $args[0];
        unset($args[0]);
        $this->logger->info("__call function.", ['key' => $key, 'args' => $args]);

        $response = [];

        try {
            switch (count($args)) {
                case 1:
                    $response = $this->redis->$key($args[1]);
                    break;
                case 2:
                    $response = $this->redis->$key($args[1], $args[2]);
                    break;
                case 3:
                    $response = $this->redis->$key($args[1], $args[2], $args[3]);
                    break;
                case 4:
                    $response = $this->redis->$key($args[1], $args[2], $args[3], $args[4]);
                    break;
            }
        } catch (\RedisException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'errorCode' => 'REDIS_AUTH_00001'
            ], 400, ['Access-Control-Allow-Origin' => '*']);
        }

        $return = [];
        if (is_array($response)) {
            foreach ($response as $item) {
                $return[] = $item;
            }
        } else if (is_string($response)) {
            $return[] = utf8_decode($response);
        }
        $this->redis->close();
        return $return;
    }
}