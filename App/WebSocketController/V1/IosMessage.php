<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	v1 Controller层Demo
// +----------------------------------------------------------------------

namespace App\WebSocketController\V1;

use Swoole\Websocket\Server;
use App\WebSocketController\Base;
use App\Server\MainServer\UserServer;
use App\Server\RedisServer\FdServer;
use App\Utility\Ws\{Result,Category,LogRequest,CheckRequest as checkRequest};

class IosMessage extends Base
{
    protected $loginKey = ['token', 'syncstamp'];
    protected $commonKey = ['to_uid', 'syncstamp'];
    protected $receivedKey = ['to_uid', 'noce_ack', 'syncstamp'];


    /**
     * login Websocket用户认证
     */
    public function login()
    {
        $log = new LogRequest('login', Category::CLIENT_TYPE_IOS);
        $log->request($this->body);

        $msgErrorRet = checkRequest::requestData($this->loginKey, $this->body);
        if (!empty($msgErrorRet)) {
            $log->trackErrorLog($msgErrorRet);
            return $this->response()->setMessage(json_encode($msgErrorRet));
        }

        //对参数进行链式操作
        $token = $this->request->getToken($this->body['token']);
        $syncStamp = $this->request->getSyncStamp($this->body['stamp']);

        $uid = (new UserServer())->getUserId($token);

        $msgErrorRet = checkRequest::checkValue('uid', $uid);
        if (!empty($msgErrorRet)) {
            $log->trackErrorLog($msgErrorRet);
            return $this->response()->setMessage(json_encode($msgErrorRet));
        }

        //存入缓存
        $fd = $this->caller()->getClient()->getFd();
        (new FdServer())->setSocketFd($fd, $uid);

        $result = Result::getLoginResult(Category::CLIENT_TYPE_IOS, $uid, $syncStamp);
        $this->messageBase($result);
        return true;
    }
}
