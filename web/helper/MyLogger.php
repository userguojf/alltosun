<?php

use Monolog\Logger;

class MyLogger
{


    /**
     * 数字彩
     * @return Logger
     */
    static function scriptLog()
    {
        return self::get('script', array(
            'ElasticType' => 'script',
        ));
    }

    /**
     * 接口日志
     * @return Logger
     */
    static function apiLog()
    {
        return self::get('api', array(
            'ElasticType' => 'api',
        ));
    }


    static function get($name, $opt)
    {
        static $logger = array();

        if (isset($logger[$name])) {
            return $logger[$name];
        }

        $l = AnLogger::getLogger($name, $opt);
        $logger[$name] = $l;
        return $l;
    }


    static function test()
    {

        self::apiLog()->info('shake test');
        self::debugLog()->error('debuglog test');

    }

    static function debugLog()
    {
        //自定义Elastic的Index
        $myLogger = self::get('debugLog', array(
            'ElasticType' => 'debug',
        ));
        return $myLogger;
    }

    /**
     * kafka日志
     * @param unknowtype
     * @return return_type
     * @author 王敬飞 (wangjf@alltosun.com)
     * @date 2018年3月22日下午4:51:23
     */
    static function kafkaLog()
    {
        //自定义Elastic的Index
        $myLogger = self::get('kafkaLog', array(
                'ElasticType' => 'kafka',
        ));
        return $myLogger;
    }
}
