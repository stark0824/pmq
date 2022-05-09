<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	WebSocketController层父类
// +----------------------------------------------------------------------

namespace App\WebSocketController;

use EasySwoole\Socket\AbstractInterface\Controller;
use App\Utility\Ws\Robot;
use App\Utility\Ws\Category;
use App\Utility\Ws\GetRequest;
use App\Utility\Ws\CheckRequest as checkRequest;

abstract class Base extends Controller
{
    protected $body = [];
    protected $request = [];
    protected $noce_ack = [];
    protected $redis = NULL;
    protected $checkAction = []; //免验证接口

    protected $msg_type = null;
    protected $token = '';
    protected $stamp = '';

    const PUSH_MSG_PULL_LISTS = 'PUSH_MSG_PULL_LISTS';
    const PUSH_MSG_READ_LISTS = 'PUSH_MSG_READ_LISTS';
    const PUSH_MSG_USER_LOGIN = 'PUSH_MSG_USER_LOGIN';
    const PUSH_MSG_SOCKET_FD = 'PUSH_MSG_SOCKET_FD';
    const PUSH_CUSTOMER_MSG_SOCKET_FD = 'PUSH_CUSTOMER_MSG_SOCKET_FD';
    const PUSH_MSG_SSET_USER_LOGIN = 'PUSH_MSG_SSET_USER_LOGIN';
    const PUSH_CUSTOMER_MSG_SSET_USER_LOGIN = 'PUSH_CUSTOMER_MSG_SSET_USER_LOGIN';
    const MYSQL_CONN_NAME = 'mysql-msg';
    const REDIS_CONN_NAME = 'redis';
    const MAX_LINK_NUMBER = 10000;
    const PUSH_MSG_OFFLINE_LISTS = 'PUSH_MSG_OFFLINE_LISTS';
    const PUSH_UNREAD_SERVER_All = 'PUSH_UNREAD_SERVER_All';

    protected function actionNotFound(?string $actionName)
    {
        $ret['code'] = 404;
        $ret['msg'] = 'action not found!';
        $this->response()->setMessage(json_encode($ret));
    }


    protected function onRequest(?string $actionName): bool
    {
        $this->body = $this->caller()->getArgs();
        $this->request = new GetRequest();
        $fd = $this->caller()->getClient()->getFd();
        //验证是否登陆
        if (!in_array($actionName, $this->checkAction)) {
            if (isset($this->body['to_uid']) && !empty($this->body['to_uid'])) {
                $result = \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($fd) {
                    $result = $redis->hExists(self::PUSH_MSG_SOCKET_FD, $fd);
                    return $result;
                }, self::REDIS_CONN_NAME);

                if (0 == $result || $result == false) {
                    $msgErrorRet['code'] = 403;
                    $msgErrorRet['msg'] = 'Please log in first';
                    $this->response()->setMessage(json_encode($msgErrorRet));
                    return false;
                }
            }
        }

        return true;
    }

    public function onException(\Throwable $throwable): void
    {
        //
    }

    /**
     * @return string 生成通信唯一的ack标识
     */
    protected function getNoceAck(): string
    {
        $ack = '32A41019-64CD-E15C-A3B0-2D1764B82E8E';
        return $ack;
    }


    protected function afterAction(?string $actionName)
    {
    }


    protected function messageBase($msgErrorRet)
    {
        if (!empty($msgErrorRet)) {
            $this->response()->setMessage(json_encode($msgErrorRet));
            unset($msgErrorRet);
            return true;
        }
    }


    protected function _getTableName(int $uid): string
    {
        $tableIndex = intval($uid % 128);
        return 'user_push_msg_' . $tableIndex;
    }


    protected function _getChatTableName(int $uid): string
    {
        $tableIndex = intval($uid % 10);
        return 'im_user_chat_record_' . $tableIndex;
    }



    protected function checkRequest(array $data)
    {
        $msgErrorRet = checkRequest::requestData($data, $this->body);
        if (!empty($msgErrorRet)) {
            return $this->response()->setMessage(json_encode($msgErrorRet));
        }
    }
}
