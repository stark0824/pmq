<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	接收消息生产者Code
// +----------------------------------------------------------------------

namespace App\Server\RedisServer;

use App\Server\Server;
use \EasySwoole\EasySwoole\Logger;

class QueueServer extends Server
{

    /**
     * 接收主站的Curl请求，存入消息队列
     * @param $pushMsgData
     * @return bool
     */
    public function addPushCommentMessage(array $pushMsgData)
    {
        if (empty($pushMsgData)) return false;
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($pushMsgData) {
            $bool = $redis->lPush(self::PUSH_MSG_COMMENT_LISTS, json_encode($pushMsgData));
            Logger::getInstance()->log('PUSH_MSG_COMMENT_LISTS：' . (string)$bool, Logger::LOG_LEVEL_INFO, 'commentMessage');
        }, self::REDIS_CONN_NAME);
    }

    /**
     * 全站通知，不需要确认
     * @param string $message
     */
    public function addPushSystemNoticeMessage(string $message = '')
    {
        $pushMsg = ['msg' => $message];
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($pushMsg) {
            $bool = $redis->lPush(self::PUSH_MSG_NOTICE_SYSTEM, json_encode($pushMsg));
            Logger::getInstance()->log('PUSH_MSG_NOTICE_SYSTEM：' . $bool, Logger::LOG_LEVEL_INFO, 'systemMessage');
        }, self::REDIS_CONN_NAME);
    }


    /**
     * 接收主站的Curl请求，存入消息队列
     * @param $pushMsg
     */
    public function addPushUserNoticeMessage(array $pushMsg)
    {
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($pushMsg) {
            $bool = $redis->lPush(self::PUSH_MSG_NOTICE_LIST, json_encode($pushMsg));
            Logger::getInstance()->log('PUSH_MSG_NOTICE_LIST：' . (string)$bool, Logger::LOG_LEVEL_INFO, 'userMessage');
        }, self::REDIS_CONN_NAME);
    }

}
