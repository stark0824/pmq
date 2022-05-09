<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:2022/5/9
// +----------------------------------------------------------------------
// | Desc:测试环境 - Redis配置文件
// +----------------------------------------------------------------------

return [
    'host' => 'redis3',
    'port' => '6379',
    'POOL_MAX_NUM' => '6',
    'POOL_TIME_OUT' => '0.1',
    "minObjectNum" => 5, // 连接池最小连接数
    "maxObjectNum" => 20, // 连接池最大连接数
    "db" => 0 //选择的数据库
];
