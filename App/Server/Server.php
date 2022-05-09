<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	服务层基类，用于定义公共处理部分
// +----------------------------------------------------------------------

namespace App\Server;

class Server {
    const REDIS_CONN_NAME = 'redis';
    const PUSH_MSG_SOCKET_FD = 'PUSH_MSG_SOCKET_FD';
    const PUSH_MSG_SSET_USER_LOGIN = 'PUSH_MSG_SSET_USER_LOGIN';
    const MYSQL_CONN_NAME = 'mysql-msg';
    const PUSH_UNREAD_NUMBER_All = 'PUSH_UNREAD_NUMBER_All';
    const PUSH_MSG_COMMENT_LISTS = 'PUSH_MSG_COMMENT_LISTS';
    const PUSH_MSG_NOTICE_SYSTEM = 'PUSH_MSG_NOTICE_SYSTEM';
}
