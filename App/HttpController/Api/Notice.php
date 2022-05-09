<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	接收来自主站Http请求的通知Api
// +----------------------------------------------------------------------

namespace App\HttpController\Api;

use App\Server\MysqlServer\PushMsg;
use EasySwoole\Http\Message\Status;
use App\Utility\Http\ParamsCheck;
use App\Server\RedisServer\{CountServer,QueueServer};
use EasySwoole\EasySwoole\Logger;

class Notice extends Base
{
    /**
     *  接收个人通知的推送的消息
     */
    public function userMessage()
    {
        Logger::getInstance()->log('请求方法method:'.$this->method , Logger::LOG_LEVEL_INFO,'userMessage');

        $msgErrorRet = ParamsCheck::checkRequestMethod($this->method);
        if(!empty($msgErrorRet))  return $this->writeJson($msgErrorRet['code'],$msgErrorRet['result'],$msgErrorRet['msg']);

        $msgErrorRet = ParamsCheck::checkRequestData(['uid','msg'],$this->params);
        if(!empty($msgErrorRet))  return $this->writeJson($msgErrorRet['code'],$msgErrorRet['result'],$msgErrorRet['msg']);

        if ( !empty($this->params['msg']) && !empty($this->params['uid']) ) {

            $ack = $this->getNoceAck();
            $pushMsgArray = [
                'ack' => $ack ,
                'to_uid' => $this->params['uid'],
                'create_time' => time()
            ];

            //
            (new QueueServer())->addPushCommentMessage($pushMsgArray);

            (new CountServer())->noticeCounter($pushMsgArray);

            (new PushMsg())->addNotice($this->params['uid'],$ack);

            return $this->writeJson(Status::CODE_OK,[],Status::getReasonPhrase(Status::CODE_OK));
        } else {
            return $this->writeJson(Status::CODE_REQUESTED_RANGE_NOT_SATISFIABLE,[],Status::getReasonPhrase(Status::CODE_REQUESTED_RANGE_NOT_SATISFIABLE));
        }
    }

    /**
     * @return bool 15分钟之内只接收一次全站消息
     */
    public function systemMessage(){

        Logger::getInstance()->log('请求方法method:'.$this->method ,Logger::LOG_LEVEL_INFO,'systemMessage');
        $msgErrorRet = ParamsCheck::checkRequestMethod($this->method);
        if(!empty($msgErrorRet))  return $this->writeJson($msgErrorRet['code'],$msgErrorRet['result'],$msgErrorRet['msg']);

        if ( isset($this->params['msg']) && !empty($this->params['msg']) &&  ( $this->params['uid'] == 0 ) ){
            $msg =  (string)$this->params['msg'];
            //全站消息只发给站内在线用户，Mysql不做存储
            $listObj = new QueueServer();
            $listObj->addPushSystemNoticeMessage($msg);
            $status = Status::CODE_OK;
            $msg =  Status::getReasonPhrase(Status::CODE_OK);
        }else{
            $status = Status::CODE_REQUESTED_RANGE_NOT_SATISFIABLE;
            $msg =  Status::getReasonPhrase(Status::CODE_REQUESTED_RANGE_NOT_SATISFIABLE);
        }
        return $this->writeJson($status,[],$msg);
    }
}
