<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	OAuth鉴权类库
// +----------------------------------------------------------------------

namespace App\Utility\Http;

use \EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\Config;

class OAuth
{
    const KEY = "***********************";
    /**
     * 验证token
     * @param $loginKey
     * @return mixed
     */
    public static function getUserInfo(string $loginKey): int
    {
        $urlConf = Config::getInstance()->getConf('url');
        $params = self::_formatQueryData($loginKey);
        $result = Curl::getUrl($urlConf['oauth_api'] . '?' . $params);
        Logger::getInstance()->log('log msg info', Logger::LOG_LEVEL_INFO, 'token');
        if ($result['code'] == 200 && $result['data']['uid']) {
            return (int)$result['data']['uid'];
        } else {
            return 0;
        }
    }

    public static function setToken(array $data): string
    {
        $token = '';
        //定义token算法
        return $token;
    }

    private static function _formatQueryData(string $loginKey): string
    {
        $params = '';
        //组装$params参数
        return $params;
    }
}

