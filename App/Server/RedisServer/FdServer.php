<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	文件描述符\Uid 操作处理
// +----------------------------------------------------------------------

namespace App\Server\RedisServer;

use App\Server\Server;
use \EasySwoole\EasySwoole\Logger;

class FdServer extends Server
{

    public function setSocketFd(int $fd, int $uid)
    {
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($fd, $uid) {
            $fdRet = $redis->hSet(self::PUSH_MSG_SOCKET_FD, $fd, $uid);
            $sRet = $redis->zAdd(self::PUSH_MSG_SSET_USER_LOGIN, $uid, $fd);
            $log = [self::PUSH_MSG_SOCKET_FD => (string)$fdRet, self::PUSH_MSG_SSET_USER_LOGIN => (string)$sRet];
            Logger::getInstance()->log('执行结果:' . json_encode($log), Logger::LOG_LEVEL_INFO, 'setSocketFd');
        }, self::REDIS_CONN_NAME);
    }


    public function getSocketUid(int $fd) :int
    {
        $fUid = \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($fd) {
            $fUid = $redis->hGet(self::PUSH_MSG_SOCKET_FD, $fd);
            $log = ['PUSH_MSG_SOCKET_FD' => $fUid];
            Logger::getInstance()->log('执行结果:' . json_encode($log), Logger::LOG_LEVEL_INFO, 'getSocketUid');
            return $fUid;
        }, self::REDIS_CONN_NAME);
        return intval($fUid) ?? 0;
    }

    public function recoverySocketFd(int $fd)
    {

        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($fd) {
            $zRemDel = $redis->zRem(self::PUSH_MSG_SSET_USER_LOGIN, $fd);
            $msgDel = $redis->hDel(self::PUSH_MSG_SOCKET_FD, $fd);
            $log = ['PUSH_MSG_SOCKET_FD' => (string)$msgDel, 'PUSH_MSG_SSET_USER_LOGIN' => (string)$zRemDel];
            Logger::getInstance()->log('执行结果:' . json_encode($log), Logger::LOG_LEVEL_INFO, 'recoverySocketFd');
        }, self::REDIS_CONN_NAME);

    }


}
