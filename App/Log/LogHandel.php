<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	自定义Log类，根据自己需要编写
// +----------------------------------------------------------------------

namespace App\Log;

use EasySwoole\Log\LoggerInterface;

class LogHandel implements LoggerInterface
{

    private $logDir;

    function __construct(string $logDir = null)
    {
        if(empty($logDir)){
            $logDir = EASYSWOOLE_ROOT.'/Log';
        }
        $this->logDir = $logDir;
    }

    function log(?string $msg,int $logLevel = self::LOG_LEVEL_INFO,string $category = 'debug'):string
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $filePath = $this->logDir."/log_".date('Ymd').".log";
        $str = "[{$date}][{$category}][{$levelStr}] : [{$msg}]\n";
        //运行环境进行处理
        if( PUSHENV == 0 || PUSHENV == 1 )  {
            file_put_contents($filePath,"{$str}",FILE_APPEND|LOCK_EX);
        }
        if(PUSHENV == 2 && ( in_array($logLevel,[ self::LOG_LEVEL_WARNING , self::LOG_LEVEL_ERROR ]) ) )  {
            file_put_contents($filePath,"{$str}",FILE_APPEND|LOCK_EX);
        }
        return $str;
    }

    function console(?string $msg,int $logLevel = self::LOG_LEVEL_INFO,string $category = 'console')
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $temp = "[{$date}][{$category}][{$levelStr}]:[{$msg}]\n";
        fwrite(STDOUT,$temp);
    }

    private function levelMap(int $level)
    {
        switch ($level)
        {
            case self::LOG_LEVEL_INFO:
                return 'info';
            case self::LOG_LEVEL_NOTICE:
                return 'notice';
            case self::LOG_LEVEL_WARNING:
                return 'warning';
            case self::LOG_LEVEL_ERROR:
                return 'error';
            default:
                return 'unknown';
        }
    }
}
