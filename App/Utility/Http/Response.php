<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	Http请求响应类库
// +----------------------------------------------------------------------

namespace App\Utility\Http;

use EasySwoole\Http\Message\Status;

class Response {

    /**
     * 请求范围不符合要求 code 416
     * @return array
     */
    public static function codeNotSatisfiable(){
        $msgErrorRet = [
            'code' => Status::CODE_REQUESTED_RANGE_NOT_SATISFIABLE,
            'msg' => Status::getReasonPhrase(Status::CODE_REQUESTED_RANGE_NOT_SATISFIABLE ),
        ];
        return $msgErrorRet;
    }

    /**
     * （服务器内部错误）服务器遇到错误，无法完成请求  code 500
     * @return array
     */
    public static function codeServerError(){
        $msgErrorRet = [
            'code' => Status::CODE_INTERNAL_SERVER_ERROR,
            'msg' => Status::getReasonPhrase(Status::CODE_INTERNAL_SERVER_ERROR ),
        ];
        return $msgErrorRet;
    }

    /**
     * 服务器拒绝请求 code 403
     * @return array
     */
    public static function codeForbiddenError(){
        $msgErrorRet = [
            'code' => Status::CODE_FORBIDDEN,
            'msg' => Status::getReasonPhrase(Status::CODE_FORBIDDEN ),
        ];
        return $msgErrorRet;
    }


    public static function codeAuthent(){
        $msgErrorRet = [
            'code' => Status::CODE_PROXY_AUTHENTICATION_REQUIRED,
            'msg' => Status::getReasonPhrase(Status::CODE_PROXY_AUTHENTICATION_REQUIRED ),
        ];
        return $msgErrorRet;
    }

    public static function codeBadGateway(){
        $msgErrorRet = [
            'code' => Status::CODE_BAD_GATEWAY,
            'msg' => Status::getReasonPhrase(Status::CODE_BAD_GATEWAY ),
        ];
        return $msgErrorRet;
    }
}
