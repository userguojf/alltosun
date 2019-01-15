<?php

/**
 * 日志类
 */

// Be sure Monolog is installed via composer
//require 'vendor/autoload.php';


use Monolog\Logger;
use Monolog\Handler\RedisHandler;
use Monolog\Formatter\LogstashFormatter;
//use Predis\Client;

use Monolog\Handler\StreamHandler;


//安装项目划分

class AnLogger
{
    //日志根目录
    static $BASE_LOG_DIR = null;

    //日志的项目前缀
    static $LOG_NS = null;

    /**
     * @var array Logger
     */
    static $loggers = array();

    /**
     * @var Logger
     * 记录调试信息
     */
    static $debugLogger = null;

    /**
     * @var Logger
     * 记录程序信息
     */
    static $infoLogger = null;

    /**
     * @var Logger
     * 记录错误日志
     */
    static $errorLogger = null;


    /**
     * @var Logger
     * 记录API日志
     */
    static $apiLogger = null;

    static $extraLogger = null;

    static $redisHandler = null;

    static $UidProcessor = null;

    /**
     * @var Logger
     * 开发调试使用
     */
    static $devLogger = null;

    static function init($config)
    {
        static $isInited = false;
        if ($isInited) {
            return;
        }

        $isInited = true;

        new AnLogger($config);

        static::$extraLogger = self::getLogger('extra', array(
            'IntrospectionProcessor',
            'WebProcessor',
        ));
    }

    function __construct($config)
    {
        self::$BASE_LOG_DIR = $config['log_base'];
        self::$LOG_NS = $config['log_ns'];

        //使用 文件 和 redis->elastic 两种方式保存日志

        //使用Redis保存日志
        $redisConf = $config['redisHandler'];
        if (empty($redisConf)) {
            exit("empty redis config.!");
        }

        if (isset($redisConf['redisClient'])) {
            $redisClient = $redisConf['redisClient'];
        } else {
            $redisClient = new Redis();
            try {
                $redisClient->connect($redisConf['host'], $redisConf['port'], 5);
                if (!empty($redisConf['pwd'])) {
                    $redisClient->auth($redisConf['pwd']);
                }
                $redisClient->select(isset($redisConf['db']) ? $redisConf['db'] : 0);
            } catch (Exception $e) {
                error_log($e->getMessage() . ' ' . var_export($redisConf, true));
                return;
            }
        }


        $redisKey = strtolower($redisConf['redis_key']); //定义Redis的Key
        $redisHandler = new RedisHandler($redisClient, $redisKey);

        //AppName 会作为 logstash的@type 字段。
        //该字段同时作为Elastic的Index名的一部分
        $formatter = new LogstashFormatter($redisConf['elastic_type']);
        self::$redisHandler = $redisHandler->setFormatter($formatter);


        return;
    }

    static function getFilePath($logName)
    {
        return self::$BASE_LOG_DIR . '/' . date("Ym/Ymd-") . $logName . '.log';
    }

    /**
     * 设置自定义Logger
     * @param $logName
     * @param array $opt
     * @param null $tags
     * @return Logger
     */
    static function getLogger($logName, $opt = array(), $tags = null)
    {
        $loggers = &self::$loggers;

        if (isset($loggers[$logName])) {
            return $loggers[$logName];
        }


        //自定义elastic type，和 _index 值
        if (isset($opt['ElasticType'])) {
            //AppName 会作为 logstash的@type 字段。
            //该字段同时作为Elastic的Index名的一部分
            $formatter = new LogstashFormatter(strtolower(self::$LOG_NS . '-' . $opt['ElasticType']));
            $redisHandler = clone self::$redisHandler;
            $redisHandler = $redisHandler->setFormatter($formatter);

            $logger = new Logger(self::$LOG_NS . '-' . $logName, array($redisHandler));
            $loggers[$logName] = $logger;

        } else {
            $logger = new Logger(self::$LOG_NS . '-' . $logName, array(self::$redisHandler));
            $loggers[$logName] = $logger;
        }

        //按需开启文件日志
        //$fileHander = new StreamHandler(self::getFilePath($logName));
        //$logger->pushHandler($fileHander);

        //添加 本次请求的唯一ID
        if (empty(self::$UidProcessor)) {
            self::$UidProcessor = new \Monolog\Processor\UidProcessor();
        }
        $logger->pushProcessor(self::$UidProcessor);

        //添加 程序的PID
        if (PHP_SAPI == 'cli') {
            $PidProcessor = new \Monolog\Processor\ProcessIdProcessor();
            $logger->pushProcessor($PidProcessor);
        }

        //添加 程序的调用信息
        if (in_array('IntrospectionProcessor', $opt)) {
            $skipClassesPartials = array(
                "AnLogger"
            );
            $debugProcessor = new \Monolog\Processor\IntrospectionProcessor(Logger::DEBUG, $skipClassesPartials);
            $logger->pushProcessor($debugProcessor);
        }

        //添加web 访问信息
        if (in_array('WebProcessor', $opt)) {
            $webProcessor = new \Monolog\Processor\WebProcessor();
            $logger->pushProcessor($webProcessor);
        }

        //定义Handler

        //$phpconsoleHandler = new \Monolog\Handler\PHPConsoleHandler();

        if (in_array('BrowserConsoleHandler', $opt)) {
            $consoleHandler = new \Monolog\Handler\BrowserConsoleHandler();
            $logger->pushHandler($consoleHandler);
        }

        if (in_array('ChromePHPHandler', $opt)) {
            $chromeconsoleHandler = new \Monolog\Handler\ChromePHPHandler();
            $logger->pushHandler($chromeconsoleHandler);
        }


        //添加Tags信息.可以用逗号，分号、空格分隔
        if (!empty($tags)) {
            if (is_string($tags)) {
                $tags = preg_split('/[\s,;]+/', $tags, PREG_SPLIT_NO_EMPTY);
            }

            $TagProcessor = new \Monolog\Processor\TagProcessor($tags);
            $logger->pushProcessor($TagProcessor);
        }

        return $loggers[$logName];

    }

    //重置$UidProcessor。主要用于常驻内存的进程
    static function resetUid()
    {
        $UidProcessor = self::$UidProcessor = new \Monolog\Processor\UidProcessor();

        if (empty(self::$loggers)) {
            return;
        }

        /**
         * @var Logger $logger
         */
        foreach (self::$loggers as &$logger) {
            if (empty($logger->getProcessors())) {
                continue;
            }

            $newProc = array();
            while ($logger->getProcessors()) {
                $oldProc = $logger->popProcessor();
                if ($oldProc instanceof \Monolog\Processor\UidProcessor) {
                    $newProc[] = $UidProcessor;
                } else {
                    $newProc[] = $oldProc;
                }
            }

            foreach ($newProc as $proc) {
                $logger->pushProcessor($proc);
            }
        }
    }


    //添加tag信息，多个tag用逗号分隔
    static function tags($level, $tagName)
    {
        static $tags;

        if (isset($tags[$level][$tagName])) {
            return $tags[$level][$tagName];
        } else {
            $newLogger = null;

            if ($level == 'debug') {
                $name = static::$debugLogger->getName();
                $newLogger = static::$debugLogger->withName($name);
            } elseif ($level == 'info') {
                $name = static::$infoLogger->getName();
                $newLogger = static::$infoLogger->withName($name);
            } elseif ($level == 'error') {
                $name = static::$errorLogger->getName();
                $newLogger = static::$errorLogger->withName($name);
            } elseif ($level == 'api') {
                $name = static::$apiLogger->getName();
                $newLogger = static::$apiLogger->withName($name);
            } elseif ($level == 'dev') {
                $name = static::$devLogger->getName();
                $newLogger = static::$devLogger->withName($name);
            }

            $tagArr = explode(',', $tagName);
            $TagProcessor = new \Monolog\Processor\TagProcessor($tagArr);
            $newLogger->pushProcessor($TagProcessor);
            $tags[$level][$tagName] = $newLogger;

            return $newLogger;
        }
    }

    //添加tag信息，多个tag用逗号分隔
    static function infoTags($tagName)
    {
        return self::tags('info', $tagName);
    }

    //添加tag信息，多个tag用逗号分隔
    static function debugTags($tagName)
    {
        return self::tags('debug', $tagName);
    }


    //添加tag信息，多个tag用逗号分隔
    static function apiTags($tagName)
    {
        return self::tags('api', $tagName);
    }


    //添加tag信息，多个tag用逗号分隔
    static function errorTags($tagName)
    {
        return self::tags('error', $tagName);
    }

    //添加tag信息，多个tag用逗号分隔
    static function devTags($tagName)
    {
        return self::tags('dev', $tagName);
    }


    static function debug($msg, $context = array())
    {
        if (!is_array($context)) {
            $context = array($context);
        }

        if (!self::$debugLogger) {
            self::$debugLogger = self::getLogger('debug', array(
                'IntrospectionProcessor',
                'WebProcessor',
            ));
        }

        return self::$debugLogger->debug($msg, $context);
    }

    static function info($msg, $context = array())
    {
        if (!self::$infoLogger) {
            self::$infoLogger = self::getLogger('info');
        }

        if (!is_array($context)) {
            $context = array($context);
        }
        return self::$infoLogger->info($msg, $context);
    }

    static function warn($msg, $context = array())
    {
        if (!self::$errorLogger) {
            self::$errorLogger = self::getLogger('error', array(
                'IntrospectionProcessor',
                'WebProcessor',
            ));
        }

        if (!is_array($context)) {
            $context = array($context);
        }
        return self::$errorLogger->warn($msg, $context);
    }

    static function error($msg, $context = array())
    {
        if (!self::$errorLogger) {
            self::$errorLogger = self::getLogger('error', array(
                'IntrospectionProcessor',
                'WebProcessor',
            ));
        }

        if (!is_array($context)) {
            $context = array($context);
        }
        return self::$errorLogger->error($msg, $context);
    }

    static function dev($msg, $context = array())
    {
        if (!self::$devLogger) {
            self::$devLogger = self::getLogger('dev', array(
                'ChromePHPHandler',
                'BrowserConsoleHandler',
            ));
        }

        if (!is_array($context)) {
            $context = array($context);
        }
        return self::$devLogger->debug($msg, $context);
    }

    static function api($msg, $context = array())
    {
        if (!self::$apiLogger) {
            self::$apiLogger = self::getLogger('api');
        }

        if (!is_array($context)) {
            $context = array($context);
        }

        return self::$apiLogger->info($msg, $context);
    }


    /**
     * @param $logName
     * @param $tags
     * @return Logger
     *
     * 自定义Logger的快捷方式
     */
    static function logger($logName, $tags = array())
    {
        return self::getLogger($logName, array(), $tags);
    }

    //开启Slog
    static function startSlog($condition)
    {
        //使用SESSION
        if (isset($condition['session'])) {

        }

        //使用Cookie
    }

    /**
     * 支持 slog 方式记录调试日志
     * @param $msg
     * @param $type
     */
    static function slog($msg)
    {
        if (class_exists("SocketLog")) {
            slog($msg);
        }
    }

    static function trace($msg)
    {
        if (class_exists("SocketLog")) {
            slog($msg, 'trace');
        }
    }


    static function test()
    {
        //先初始化Logger配置，一般在程序的配置中完成
        //AnLogger::init($config);

        //基础用法
        AnLogger::debug("debug");
        AnLogger::info("info");
        AnLogger::warn("warn");
        AnLogger::error("error");

        //添加Context信息
        AnLogger::info("info, add server", $_SERVER);
        AnLogger::info("info, more msg ", "more msg");

        AnLogger::api("api");

        //dev Logger用于程序调试
        AnLogger::dev("dev");

        //添加tags信息. 多个tag用 , 空格分隔
        AnLogger::infoTags('api')->info('info with tag');
        AnLogger::errorTags('api')->info('error with tag');
        AnLogger::debugTags('api,qcloud,weixin')->info('api with tags');

        //自Logger，如果需要带Tags，必须第一次使用的时候
        AnLogger::logger('alipay', 'alipay,pay')->info('alipay custom with tags');
        AnLogger::logger('alipay')->info('alipay custom also with tags');

        $myLogger = AnLogger::logger('newLogger', 'alipay,pay');
        $myLogger->info('newLogger custom');

        //自定义Elastic的Index
        $myLogger = AnLogger::getLogger('newDDLogger', array(
            'ElasticType' => 'dingding',
        ));
        $myLogger->info('newLogger elastic index -> ns:dingding');

        AnLogger::resetUid();
        AnLogger::info('AnLogger::resetUid');
        AnLogger::info('AnLogger::resetUid 22222');

        //slog的调用
        AnLogger::slog('slog');
        AnLogger::trace('slog trace');
    }
}


/*
调用信息

IntrospectionProcessor: Adds the line/file/class/method from which the log call originated.

WebProcessor: Adds the current request URI, request method and client IP to a log record.
ProcessIdProcessor: Adds the process id to a log record.
UidProcessor: Adds a unique identifier to a log record.
TagProcessor: Adds an array of predefined tags to a log record.

ErrorHandler: The Monolog\ErrorHandler class allows you to easily register a Logger instance as an exception handler, error handler or fatal error handler.
https://github.com/evaisse/monolog-request-id-processor
*/

