<?php

namespace App\WebSocketController\V3;

use Swoole\Websocket\Server;
use App\Utility\Http\OAuth;
use App\WebSocketController\Base;
use EasySwoole\ORM\DbManager;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Task\TaskManager;
use App\Utility\Ws\{Params, ParamsCheck, Result, Category, Robot};
use App\Models\{ImModel, ImChatModel};

class IosMessage extends Base
{
    /**
     * 自动回复列表
     */
    public function getReplyRobot()
    {
        [$toUid, $syncStamp] = Params::autoReplyRobotParams(Category::CLIENT_TYPE_IOS, $this->body['to_uid'], $this->body['syncstamp']);

        $msgErrorRet = ParamsCheck::checkTouidAndSyncstamp($toUid, $syncStamp);
        if (!empty($msgErrorRet)) return $this->response()->setMessage(json_encode($msgErrorRet));

        $replyJson = \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) {
            return $redis->get(Category::$reply);
        }, self::REDIS_CONN_NAME);

        $data = [];
        if (empty($replyJson)) {
            //超时或异常使用旧数据
            $data = $this->getFileReplyData();
        } else {
            $replyArr = json_decode($replyJson, true);
            if (!empty($replyArr) && is_array($replyArr)) {
                $data = $this->getCacheReplyData($replyArr);
            }
        }
        $result = Result::getRobotReplyListsResult(Category::CLIENT_TYPE_IOS, $data, $syncStamp);
        $this->messageBase($result);
    }

    /**
     * 根据问题编号，回复问题
     */
    public function autoReplyRobot()
    {
        [$toUid, $qNumber, $syncStamp] = Params::AutoRobotParams(Category::CLIENT_TYPE_IOS, $this->body['to_uid'], $this->body['q_number'], $this->body['syncstamp']);

        $msgErrorRet = ParamsCheck::checkTouidAndSyncstamp($toUid, $syncStamp);
        if (!empty($msgErrorRet)) return $this->response()->setMessage(json_encode($msgErrorRet));

        $msgErrorRet = ParamsCheck::checkQnumber($qNumber);
        if (!empty($msgErrorRet)) return $this->response()->setMessage(json_encode($msgErrorRet));

        $answer = Robot::getRobotArray($qNumber);

        $result = Result::getRobotReplyResult(Category::CLIENT_TYPE_IOS, $answer, $syncStamp);
        $this->messageBase($result);
        return true;
    }

    /**
     * 问题是否有更新
     */
    public function checkRobotTimeStamp()
    {
        $this->RobotTimeStampBase(Category::CLIENT_TYPE_IOS);
    }

    /**
     * 建立链接
     */
    public function openCustomer()
    {
        [$toUid, $token, $syncStamp] = Params::openCustomerParams(Category::CLIENT_TYPE_IOS, $this->body['to_uid'], $this->body['token'], $this->body['syncstamp']);

        //设置分布式锁,3s之内只能请求一次
        $lock = \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($toUid) {
            return $redis->get(Category::$openLock . $toUid);
        }, self::REDIS_CONN_NAME);

        if ($lock) {
            $msgErrorRet['code'] = 416;
            $msgErrorRet['msg'] = 'Please try again';
            return $this->response()->setMessage(json_encode($msgErrorRet));
        }

        //查询是否存在链接关系


        //如果上次接待客服在线，优先分配
        $imRelation = DbManager::getInstance()->invoke(function ($client) use ($toUid) {
            $model = ImModel::invoke($client);
            $where = ['to_uid' => $toUid, 'im_status' => 2, 'im_del' => 1];
            $imUserArray = $model->field(['virtual_uid', 'im_rid'])->where($where)->order('im_rid', 'desc')->limit(0, 1)->all()->toArray();
            $data = empty($imUserArray[0]) ? [] : $imUserArray[0];
            return $data;
        }, self::MYSQL_CONN_NAME);

        //遍历客服管理员的关系
        $customerNameDict = [];

        $onlineCustomer = [];
        //3.首次分配，进行随机分配
        $maxIndex = count($onlineCustomer) - 1;
        $index = rand(0, $maxIndex);
        $vUid = $onlineCustomer[$index];
        $customerName = $customerNameDict[$vUid]['customer_name'];
        $customerHead = $customerNameDict[$vUid]['customer_head'];
        $imData = [
            'virtual_uid' => $vUid,
            'to_uid' => $toUid,
            'create_time' => time()
        ];

        $imRid = DbManager::getInstance()->invoke(function ($client) use ($imData) {
            $model = ImModel::invoke($client, $imData);
            return $model->save();
        }, self::MYSQL_CONN_NAME);

        $redisRst = false;
        if ($imRid) {
            $imData['im_rid'] = $imRid;
            $imData['customer_name'] = $customerName;
            $imData['customer_head'] = $customerHead;
            $redisRst = \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($toUid, $imData) {
                return $redis->set(Category::$imUserRelationName . $toUid, json_encode($imData));
            }, self::REDIS_CONN_NAME);
        }

        //4.如果错误,对数据进行恢复

        if ($imRid && $redisRst) {
            $code = 200;
        } else {
            //Mysql和Redis不一致，修改Mysql状态,异步处理
            TaskManager::getInstance()->async(function () use ($imRid) {
                DbManager::getInstance()->invoke(function ($client) use ($imRid) {
                    $model = ImModel::invoke($client);
                    $model->where('im_rid', (int)$imRid)->update(['is_delete' => 2]);
                }, self::MYSQL_CONN_NAME);
            });
            $code = 500;
        }
        $result = Result::getOpenCustomerResult(Category::CLIENT_TYPE_IOS, $toUid, $customerName, $code, $syncStamp,$customerHead);
        $this->messageBase($result);
        return true;
    }

    /**
     * 关闭链接
     */
    public function closeCustomer()
    {
        //todo ...
    }

    /**
     * 给客服发送消息
     */
    public function pushCustomer()
    {
       //todo ...
    }
}
