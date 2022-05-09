<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	用户服务中心
// +----------------------------------------------------------------------

namespace App\Server\MainServer;

use App\Utility\Http\OAuth;
use App\Server\Server;

class UserServer extends Server
{
    /**
     * 根据Token，验证用户是否有权限
     * @param string $token
     * @return int
     */
    public function getUserId(string $token) :int
    {
        $uid = \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($token) {
            $uid = $redis->get($token);
            if (!isset($uid) || empty($uid)) {
                //远程验证token
                $uid = OAuth::getUserInfo($token);
                if (isset($uid) && !empty($uid) && intval($uid) > 0) {
                    //3650 + 随机时间 ，防止缓存雪崩
                    $expireTime = 3650 + rand(1, 3000);
                    $redis->setEx($token, $expireTime, $uid);
                }
                return $uid;
            } else {
                return $uid;
            }
        }, self::REDIS_CONN_NAME);
        return intval($uid);
    }

}
