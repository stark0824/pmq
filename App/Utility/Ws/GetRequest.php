<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	WebSocket获取参数类
// +----------------------------------------------------------------------

namespace App\Utility\Ws;

class GetRequest
{

    public function getToken( $token )
    {
        return $token ?? '';
    }

    public function getSyncStamp( $syncStamp )
    {
        return $syncStamp ?? 0;
    }

    public function getToUid( $toUid )
    {
        return $toUid ?? 0;
    }

    public function getNoceAck( $ack )
    {
        return $ack ?? '';
    }

}
