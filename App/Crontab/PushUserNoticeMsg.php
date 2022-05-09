<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	推送消息 - 消费队列的推送消息
// +----------------------------------------------------------------------

namespace App\Crontab;

use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\EasySwoole\ServerManager;
use App\Utility\Ws\Category;

class PushUserNoticeMsg extends AbstractCronTask
{
    const PUSH_MSG_NOTICE_LIST = 'PUSH_MSG_NOTICE_LIST';
    const PUSH_MSG_SSET_USER_LOGIN = 'PUSH_MSG_SSET_USER_LOGIN';
    const PUSH_MSG_NOTICE_DELAY_LIST = 'PUSH_MSG_NOTICE_DELAY_LIST';

    protected $limit = 1000; //每次执行查询的条数
    protected $tableNumber = 128;
    protected $timeOut =  86400;

    public static function getRule(): string
    {
        return '*/6 * * * *';
    }

    public static function getTaskName(): string
    {
        return  'PushUserNoticeMsg';
    }

    function run( $taskId, $workerIndex)
    {
        $redis = \EasySwoole\RedisPool\RedisPool::defer('redis');
        $server = ServerManager::getInstance()->getSwooleServer();
        //消费队列
        $data = $redis->lRange(self::PUSH_MSG_NOTICE_LIST, 0, $this->limit );
        if (!empty($data) && is_array($data)){
            $pushLists = [];
            $lRemList = [];
            foreach ($data as $json){
                $msgPushInfo = json_decode($json,true);
                if(isset($msgPushInfo['to_uid']) && !empty($msgPushInfo['to_uid'])){
                    $delayList = [];
                    $diff_unix_times = time() - $msgPushInfo['create_time'];
                    if( $diff_unix_times > $this->timeOut  ){
                        $delayList[] = $json;
                    }else{
                        //查询消息是否已经存在
                        $lRemList[$msgPushInfo['to_uid']][] = $json;
                        $pushLists[$msgPushInfo['to_uid']]['uid'] = $msgPushInfo['to_uid'];
                        $pushLists[$msgPushInfo['to_uid']]['noce_ack'] = $msgPushInfo['noce_ack'];
                    }
                } else {
                    $redis->lRem(self::PUSH_MSG_NOTICE_LIST, 1, $json);
                }
            }

            if(isset($pushLists) && !empty($pushLists)){
                foreach ($pushLists as $value){
                    $pushMsg = [
                        "uid" => 0,
                        "msg_type" => 6,
                        "code" => 200,
                        "msg" => 'SUCCESS',
                        "body" => ['uid' => $value['uid'] ],
                        'noce_ack' => $value['noce_ack']
                    ];

                    $zUidLists = $redis->zRangeByScore(self::PUSH_MSG_SSET_USER_LOGIN,
                        (int)$value['uid'],(int)$value['uid'],
                        ['withScores' => true, 'limit' => array(0, 5)]
                    );

                    //收到评论数
                    $unReadFromCommentsNumbers  =  $redis->get(Category::_getUnReadFromCommentsKeyName($value['uid']));
                    //系统消息数
                    $unReadMessageNumbers = $redis->get(Category::_getUnReadMessageKeyName($value['uid']));

                    $unReadFromCommentsNumbers = !empty($unReadFromCommentsNumbers) ? intval($unReadFromCommentsNumbers) : 0;
                    $unReadMessageNumbers = !empty($unReadMessageNumbers) ? intval($unReadMessageNumbers) : 0;

                    $body = [
                        'unread_from_comments_numbers' =>  $unReadFromCommentsNumbers,
                        'unread_message_numbers' => $unReadMessageNumbers ,
                        'unread_all' => array_sum([$unReadFromCommentsNumbers,$unReadMessageNumbers])
                    ];

                    $unread_msg_data = [
                        'msg_type' => 14,
                        'code' => 200,
                        'msg' => 'SUCCESS',
                        'body'  => $body
                    ];

                    if(isset($zUidLists) && !empty($zUidLists)){

                        $fdList = array_keys($zUidLists);
                        foreach ($fdList as $fdKeys){
                            $ret = [];
                            $bool = $server->push($fdKeys,json_encode($pushMsg));
                            $server->push($fdKeys,json_encode($unread_msg_data));
                            //推送成功后，移除元素
                            if($bool){
                                $ret[] = $bool;
                            }
                        }
                        //推送成功后，移除元素
                        if(!empty($ret)){
                            foreach ($lRemList[$value['uid']] as $lVal) {
                                $redis->lRem(self::PUSH_MSG_NOTICE_LIST, 1, $lVal);
                            }
                        }
                    }
                }
            }


            //对超时数据进行处理
            if(isset($delayList) && !empty($delayList)){
                $delayString = implode(',',$delayList);
                $delayRst = $redis->lPush(self::PUSH_MSG_NOTICE_DELAY_LIST , $delayString);
                if($delayRst){
                    foreach ($delayList as $delValue){
                        $redis->lRem(self::PUSH_MSG_NOTICE_LIST, 1, $delValue);
                    }
                }
            }
        }
    }

    function onException(\Throwable $throwable,  $taskId,  $workerIndex)
    {
        echo $throwable->getMessage();
    }
}
