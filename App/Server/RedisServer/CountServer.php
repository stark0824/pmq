<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	评论业务Code
// +----------------------------------------------------------------------

namespace App\Server\RedisServer;

use App\Server\Server;
use App\Utility\Ws\Category;

class CountServer extends Server
{
    public function commentsCounter(int $toUid, int $commentUid)
    {
        if ($toUid == $commentUid || empty($toUid) || empty($commentUid)) return false;

        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($toUid, $commentUid) {
            //收到评论数 +1
            $redis->incr(Category::_getUnReadFromCommentsKeyName($toUid));
            //更新消息未读数
            $redis->lPush(self::PUSH_UNREAD_NUMBER_All, $toUid);
            $redis->lPush(self::PUSH_UNREAD_NUMBER_All, $commentUid);
        }, self::REDIS_CONN_NAME);
    }


    public function noticeCounter($pushMsgArray)
    {

        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($pushMsgArray) {
            //收到评论数 +1
            $redis->incr(Category::_getUnReadMessageKeyName($pushMsgArray['to_uid']));
            //更新消息未读数
            $redis->lPush(self::PUSH_UNREAD_NUMBER_All, $pushMsgArray['to_uid']);
        }, self::REDIS_CONN_NAME);
    }


    public function getUnreadMessageDetails(int $toUid): array
    {
        $counterArr = \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($toUid) {
            //收到评论数
            $unReadFromCommentsNumbers = $redis->get(Category::_getUnReadFromCommentsKeyName($toUid));
            //系统消息数
            $unReadMessageNumbers = $redis->get(Category::_getUnReadMessageKeyName($toUid));

            $unReadFromCommentsNumbers = !empty($unReadFromCommentsNumbers) ? intval($unReadFromCommentsNumbers) : 0;
            $unReadMessageNumbers = !empty($unReadMessageNumbers) ? intval($unReadMessageNumbers) : 0;
            return [
                'unread_from_comments_numbers' => $unReadFromCommentsNumbers,
                'unread_message_numbers' => $unReadMessageNumbers,
                'unread_all' => array_sum([$unReadFromCommentsNumbers, $unReadMessageNumbers])
            ];
        }, self::REDIS_CONN_NAME);

        return $counterArr;
    }


    public function clearCommentUnread(int $toUid)
    {
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($toUid) {
            //收到评论数
            $redis->set(Category::_getUnReadFromCommentsKeyName($toUid), 0);
        }, self::REDIS_CONN_NAME);
    }

    public function clearMessageUnread(int $toUid)
    {
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($toUid) {
            //收到评论数
            $redis->set(Category::_getUnReadMessageKeyName($toUid), 0);
        }, self::REDIS_CONN_NAME);
    }

}
