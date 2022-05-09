<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	Mysql Models类  $connectionName和启动的名字保持一致！
// +----------------------------------------------------------------------

namespace App\Models;
use EasySwoole\ORM\AbstractModel;

/**
 * 推送Model模型
 * Class UserShop
 */
class PushMsgModel extends AbstractModel
{
    //选择连接的数据库
    protected $connectionName = 'mysql-msg';

    protected $tableName = 'user_push_msg_0';

}
