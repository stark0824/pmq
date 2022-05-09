<?php
// +----------------------------------------------------------------------
// | Author:Stark
// +----------------------------------------------------------------------
// | Date:	2022/5/9
// +----------------------------------------------------------------------
// | Desc:	帮助中心
// +----------------------------------------------------------------------

namespace App\Utility\Ws;

class Robot
{

    static $robotArray = [
        'Q1' => [
            'category' => 1,
            'title' => 'IOS充值不到账怎么办？',
            'answer' => "您好，充值后不支持退款..."
        ],
    ];

    public static function getRobotArray(string $qNumber): string
    {
        $qNumber = trim($qNumber);
        $answer = '';
        if (empty($qNumber)) {
            $answer = '抱歉，鱼塘里找不到你的答案呢~';
        } else {
            $data = Robot::$robotArray[$qNumber];
            if (!empty($data['answer'])) {
                $answer = $data['answer'];
            }
        }

        return $answer;
    }
}
